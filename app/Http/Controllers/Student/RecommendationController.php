<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function __construct(
        protected ScholarshipRecommendationService $recommendationService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $result = $this->recommendationService->getRecommendations($user);

        if (empty($result['error'])) {
            $this->recommendationService->storeRecommendationLogs($user, $result['recommendations'] ?? []);
        }

        return view('student.recommendations.index', [
            'error' => $result['error'],
            'recommendations' => $result['recommendations'],
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

        if (!$recommendation) {
            abort(404, 'Recommendation not found');
        }

        $isSaved = $user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists();

        return view('student.recommendations.show', compact('recommendation', 'isSaved'));
    }
}
