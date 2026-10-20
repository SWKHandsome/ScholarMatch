<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RecommendationController extends Controller
{
    public function __construct(
        protected ScholarshipRecommendationService $recommendationService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'award_type' => ['nullable', 'string', 'max:255'],
            'study_level' => ['nullable', 'string', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'field_of_study' => ['nullable', 'string', Rule::in(config('scholarship.fields_of_study'))],
            'institution_type' => ['nullable', 'string', Rule::in(config('scholarship.institution_types'))],
            'decision' => ['nullable', 'string', Rule::in(['Eligible', 'Not Eligible'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $filters = [];
        foreach (['search', 'award_type', 'study_level', 'field_of_study', 'institution_type'] as $key) {
            $filters[$key] = trim($validated[$key] ?? '');
        }
        $decision = $validated['decision'] ?? 'Eligible';
        $user = Auth::user();
        $result = $this->recommendationService->getRecommendations($user);

        if (empty($result['error'])) {
            $this->recommendationService->storeRecommendationLogs($user, $result['recommendations'] ?? []);
        }

        $allRecommendations = collect($result['recommendations']);
        $filtered = $allRecommendations->filter(function (array $recommendation) use ($filters) {
            $data = $recommendation['scholarship_data'];
            if ($filters['search'] !== '' && ! Str::contains($data['name'].' '.$data['provider_name'], $filters['search'], ignoreCase: true)) {
                return false;
            }
            if ($filters['award_type'] !== '' && $data['award_type'] !== $filters['award_type']) {
                return false;
            }

            $checks = collect($recommendation['requirement_results'])->keyBy('key');
            foreach (['study_level' => 'funding_level', 'field_of_study' => 'field_of_study', 'institution_type' => 'institution_type'] as $filter => $key) {
                $check = $checks->get($key);
                // An unrestricted scholarship also supports the selected programme choice.
                if ($filters[$filter] !== '' && $check['outcome'] !== 'not_applicable'
                    && ! in_array($filters[$filter], (array) $check['required'], true)) {
                    return false;
                }
            }

            return true;
        });
        $items = $filtered->where('status', $decision)->values();
        $page = min((int) ($validated['page'] ?? 1), max(1, (int) ceil($items->count() / 10)));
        $query = array_filter(array_merge($filters, ['decision' => $decision]), fn ($value) => $value !== '');
        $recommendations = new LengthAwarePaginator($items->forPage($page, 10)->values(), $items->count(), 10, $page, [
            'path' => route('student.recommendations'), 'query' => $query, 'fragment' => 'recommendation-results',
        ]);

        return view('student.recommendations.index', [
            'error' => $result['error'],
            'recommendations' => $recommendations,
            'totalRecommendations' => $allRecommendations->count(),
            'decisionCounts' => $filtered->countBy('status'),
            'decision' => $decision,
            'filters' => $filters,
            'awardTypes' => $allRecommendations->pluck('scholarship_data.award_type')->push($filters['award_type'])->filter()->unique()->sort()->values(),
            'educationLevels' => AcademicResult::EDUCATION_LEVELS,
            'unconfiguredCount' => $result['unconfigured_count'] ?? 0,
        ]);
    }

    public function show(int $scholarship)
    {
        $user = Auth::user();
        $scholarship = Scholarship::with('rule')->find($scholarship);

        if (! $scholarship) {
            return redirect()->route('student.recommendations')
                ->with('warning', 'This scholarship is no longer available. Your recommendations have been refreshed.');
        }

        // Always use the current evaluation so changed rules and failed requirements are accurate.
        $result = $this->recommendationService->getRecommendations($user);
        $recommendation = collect($result['recommendations'] ?? [])
            ->first(fn ($item) => ($item['scholarship_id'] ?? null) === $scholarship->id);

        if ($recommendation) {
            $recommendation['scholarship'] = $scholarship;
        }

        if (! $recommendation) {
            return redirect()->route('student.recommendations')
                ->with('warning', $result['error'] ?? 'This scholarship is closed or is awaiting requirement configuration.');
        }

        $isSaved = $user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists();

        return view('student.recommendations.show', compact('recommendation', 'isSaved'));
    }
}
