<?php

namespace App\Services;

use App\Models\IncomeCategory;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ScholarshipRecommendationService
{
    public const ALGORITHM_VERSION = 'decisions-v9-undecided-programme';

    private const CATALOG_VERSION_CACHE_KEY = 'recommendation_catalog_version';

    public function getRecommendations(User $user): array
    {
        $profile = $user->studentProfile;
        $academic = $user->academicResult;
        if (! $profile) {
            return ['error' => 'Please complete your student profile first.', 'recommendations' => []];
        }
        if (! in_array($profile->nationality, config('scholarship.nationalities'), true)) {
            return ['error' => 'Please update your student profile and select Malaysian or Non-Malaysian nationality.', 'recommendations' => []];
        }
        if (! $academic) {
            return ['error' => 'Please enter your academic result first.', 'recommendations' => []];
        }
        if (! $academic->isReadyForRecommendations()) {
            return [
                'error' => 'Complete your academic information and programme seeking funding using official results. Select an institution type and field of study, or choose Haven\'t decided for either. Exam-based qualifications need a final grade breakdown or STAM pangkat; CGPA-based qualifications need an official CGPA. STPM requires both official overall CGPA and subject grade counts.',
                'recommendations' => [],
            ];
        }
        $incomeCategory = IncomeCategory::classifyIncome((float) $profile->household_income);
        if ($incomeCategory === null) {
            return ['error' => 'Your income cannot be classified. Please ask the administrator to review the income ranges.', 'recommendations' => []];
        }

        // The calendar day prevents yesterday's open deadlines surviving in cache.
        $version = Cache::get(self::CATALOG_VERSION_CACHE_KEY, 'initial');
        $key = 'recommendations_'.self::ALGORITHM_VERSION.'_'.$user->id.'_'.hash('sha256', json_encode([
            $profile->getAttributes(), $academic->getAttributes(), $version, today()->toDateString(),
        ], JSON_THROW_ON_ERROR));

        return Cache::remember($key, 1800, function () use ($profile, $academic, $incomeCategory) {
            $scholarships = Scholarship::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()->toDateString()))
                ->with('rule.qualificationRoutes')->get();
            $recommendations = [];
            $unconfigured = 0;
            foreach ($scholarships as $scholarship) {
                if (! $scholarship->rule) {
                    $unconfigured++;

                    continue;
                }
                $rule = $scholarship->rule;
                if ($rule->required_nationality && ! in_array($rule->required_nationality, config('scholarship.nationalities'), true)) {
                    $unconfigured++;

                    continue;
                }
                $recommendations[] = app(ScholarshipEligibilityEvaluator::class)->evaluate($scholarship, $profile, $academic, $incomeCategory);
            }
            usort($recommendations, fn ($a, $b) => (($a['status'] === 'Eligible' ? 0 : 1) <=> ($b['status'] === 'Eligible' ? 0 : 1))
                ?: (($a['scholarship_data']['deadline'] ?? '9999-12-31') <=> ($b['scholarship_data']['deadline'] ?? '9999-12-31'))
                ?: strcasecmp($a['scholarship_data']['name'], $b['scholarship_data']['name'])
                ?: ($a['scholarship_id'] <=> $b['scholarship_id']));

            return ['error' => null, 'recommendations' => $recommendations, 'unconfigured_count' => $unconfigured];
        });
    }

    public static function invalidateCatalogCache(): void
    {
        Cache::forever(self::CATALOG_VERSION_CACHE_KEY, bin2hex(random_bytes(12)));
    }

    public function loadScholarshipFromRecommendation(array $recommendation): ?Scholarship
    {
        return Scholarship::with('rule.qualificationRoutes')->find($recommendation['scholarship_id'] ?? null);
    }

    public function storeRecommendationLogs(User $user, array $recommendations): void
    {
        foreach ($recommendations as $recommendation) {
            RecommendationLog::updateOrCreate(
                ['user_id' => $user->id, 'scholarship_id' => $recommendation['scholarship_id']],
                array_merge(array_intersect_key($recommendation, array_flip([
                    'status', 'requires_verification', 'special_requirements', 'failed_requirements',
                    'requirement_results', 'explanation',
                ])), ['evaluation_version' => self::ALGORITHM_VERSION])
            );
        }
    }
}
