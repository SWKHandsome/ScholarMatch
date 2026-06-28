<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationLog;
use Illuminate\Http\Request;

class RecommendationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = RecommendationLog::with(['user', 'scholarship']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scholarship_id')) {
            $query->where('scholarship_id', $request->scholarship_id);
        }

        $logs = $query->latest()->paginate(20);
        $scholarships = \App\Models\Scholarship::orderBy('name')->get();

        return view('admin.recommendation-logs.index', compact('logs', 'scholarships'));
    }
}