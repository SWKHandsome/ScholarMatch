<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\RecommendationLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_scholarships' => Scholarship::count(),
            'active_scholarships' => Scholarship::where('is_active', true)->count(),
            'total_recommendations' => RecommendationLog::count(),
            'recent_recommendations' => RecommendationLog::with(['user', 'scholarship'])
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}