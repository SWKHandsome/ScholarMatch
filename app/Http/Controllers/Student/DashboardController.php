<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedScholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ScholarshipRecommendationService $recommendationService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->studentProfile;
        $academicResult = $user->academicResult;

        $profileComplete = $profile !== null;
        $academicComplete = $academicResult?->isReadyForRecommendations() ?? false;

        $savedCount = SavedScholarship::where('user_id', $user->id)->count();

        $recentRecommendations = [];
        $eligibleCount = 0;
        if ($profileComplete && $academicComplete) {
            $result = $this->recommendationService->getRecommendations($user);
            $allRecommendations = $result['recommendations'] ?? [];
            $eligibleCount = count(array_filter($allRecommendations, fn (array $recommendation) => $recommendation['status'] === 'Eligible'));
            $recentRecommendations = array_slice($allRecommendations, 0, 5);
        }

        return view('student.dashboard', compact(
            'profileComplete',
            'academicComplete',
            'savedCount',
            'recentRecommendations',
            'eligibleCount'
        ));
    }
}
