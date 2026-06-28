<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
}