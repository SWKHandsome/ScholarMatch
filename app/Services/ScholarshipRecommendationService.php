<?php

namespace App\Services;

use App\Models\IncomeCategory;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ScholarshipRecommendationService
{
    public const ALGORITHM_VERSION = 'decisions-v5-local-award-windows';

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
                'error' => 'Complete your academic information and programme seeking funding, including institution type and field of study, using official results. SPM requires completed exams; other levels require an official CGPA.',
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
                $checks = [];
                $add = function (string $key, string $label, mixed $required, mixed $actual, bool $met, string $message, bool $applicable = true) use (&$checks) {
                    $checks[] = [
                        'key' => $key, 'label' => $label, 'required' => $required, 'actual' => $actual,
                        'outcome' => ! $applicable ? 'not_applicable' : ($met ? 'met' : 'not_met'),
                        'message' => $applicable ? $message : 'No '.$label.' requirement is specified.',
                    ];
                };
                $nationalityMatches = ! $rule->required_nationality
                    || $profile->nationality === $rule->required_nationality;
                $add('nationality', 'Nationality', $rule->required_nationality, $profile->nationality,
                    $nationalityMatches, $nationalityMatches ? 'You meet the nationality requirement.'
                        : "This scholarship requires {$rule->required_nationality} nationality.", (bool) $rule->required_nationality);
                $add('funding_level', 'Funded study level', $rule->required_study_level, $academic->funding_study_level,
                    $academic->funding_study_level === $rule->required_study_level,
                    "The scholarship funds {$rule->required_study_level}; you selected {$academic->funding_study_level}.",
                    (bool) $rule->required_study_level);
                $checks = array_merge($checks, app(AcademicRouteEvaluator::class)->evaluate($rule->academicRoutes(), $academic));
                $add('income_category', 'Income category', $rule->required_income_category, $incomeCategory,
                    $incomeCategory === $rule->required_income_category,
                    "Required income category: {$rule->required_income_category}; your category: {$incomeCategory}.",
                    (bool) $rule->required_income_category);
                $add('max_household_income', 'Maximum household income', $rule->max_household_income, $profile->household_income,
                    (float) $profile->household_income <= (float) $rule->max_household_income,
                    "Maximum household income: RM {$rule->max_household_income}; your income: RM {$profile->household_income}.",
                    $rule->max_household_income !== null);
                $fields = $rule->supportedFieldsOfStudy();
                $add('field_of_study', 'Field of study', $fields, $academic->field_of_study,
                    in_array($academic->field_of_study, $fields, true),
                    'Supported fields: '.implode(', ', $fields)."; your field: {$academic->field_of_study}.", ! empty($fields));
                $add('institution_type', 'Institution type', $rule->required_institution_type, $academic->institution_type,
                    $academic->institution_type === $rule->required_institution_type,
                    "Required institution: {$rule->required_institution_type}; your institution: {$academic->institution_type}.",
                    (bool) $rule->required_institution_type);

                if ($scholarship->deadline !== null) {
                    $add('deadline', 'Application deadline', $scholarship->deadline->toDateString(), today()->toDateString(),
                        ! $scholarship->isExpired(), $scholarship->isDemo()
                            ? 'The fictional demo deadline has not passed.'
                            : 'The recorded closing date has not passed. Confirm the current application window with the provider.');
                } else {
                    // A rolling or automatic award has no single calendar closing date.
                    $checks[] = [
                        'key' => 'deadline', 'label' => 'Application deadline',
                        'required' => $scholarship->deadlineLabel(), 'actual' => null, 'outcome' => 'not_applicable',
                        'message' => $scholarship->application_notes ?: 'The closing date needs confirmation with the provider.',
                    ];
                }
                $failed = array_values(array_map(fn ($check) => $check['key'],
                    array_filter($checks, fn ($check) => $check['outcome'] === 'not_met')));
                $eligible = empty($failed);
                $special = trim((string) $scholarship->special_requirements);
                if ($scholarship->deadline === null && ! in_array($scholarship->deadline_type, ['rolling', 'automatic'], true)) {
                    $special = trim($special."\n\nApplication dates must be confirmed with the provider.");
                }
                $recommendations[] = [
                    'scholarship_id' => $scholarship->id,
                    'scholarship_data' => [
                        'id' => $scholarship->id, 'name' => $scholarship->name,
                        'description' => $scholarship->description, 'provider_name' => $scholarship->provider,
                        'award_type' => $scholarship->award_type, 'deadline' => $scholarship->deadline?->toDateString(),
                        'deadline_label' => $scholarship->deadlineLabel(),
                        'deadline_type' => $scholarship->deadline_type,
                        'application_link' => $scholarship->application_link,
                        'application_notes' => $scholarship->application_notes,
                        'is_demo' => $scholarship->isDemo(),
                    ],
                    'status' => $eligible ? 'Eligible' : 'Not Eligible',
                    'requires_verification' => $eligible && $special !== '',
                    'special_requirements' => $special,
                    'failed_requirements' => $failed,
                    'requirement_results' => $checks,
                    'explanation' => array_column($checks, 'message'),
                ];
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
