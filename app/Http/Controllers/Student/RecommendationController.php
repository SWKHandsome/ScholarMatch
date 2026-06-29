<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\RecommendationLog;
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

    public function show(Scholarship $scholarship)
    {
        $user = Auth::user();

        // Try to get from RecommendationLog (historical, faster)
        $log = RecommendationLog::where('user_id', $user->id)
            ->where('scholarship_id', $scholarship->id)
            ->first();

        if ($log) {
            $recommendation = [
                'scholarship' => $scholarship,
                'score' => $log->score,
                'status' => $log->status,
                'failed_hard_rules' => $log->failed_hard_rules ?? [],
                'explanation' => $log->explanation ?? [],
                'score_breakdown' => $log->score_breakdown ?? [],
                'is_preliminary' => $user->academicResult?->result_status === 'pending',
            ];
        } else {
            // Fallback: run engine for this single scholarship
            $result = $this->recommendationService->getRecommendations($user);
            $recommendation = collect($result['recommendations'] ?? [])
                ->firstWhere('scholarship.id', $scholarship->id);
        }

        if (!$recommendation) {
            abort(404, 'Recommendation not found');
        }

        $isSaved = $user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists();

        return view('student.recommendations.show', compact('recommendation', 'isSaved'));
    }
}