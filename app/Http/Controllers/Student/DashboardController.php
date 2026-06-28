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
        $academicComplete = $academicResult !== null;

        $savedCount = SavedScholarship::where('user_id', $user->id)->count();

        $recentRecommendations = [];
        if ($profileComplete && $academicComplete) {
            $result = $this->recommendationService->getRecommendations($user);
            $recentRecommendations = array_slice($result['recommendations'] ?? [], 0, 5);
        }

        return view('student.dashboard', compact(
            'profileComplete',
            'academicComplete',
            'savedCount',
            'recentRecommendations'
        ));
    }
}