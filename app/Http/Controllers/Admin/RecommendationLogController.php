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

    public function show(RecommendationLog $recommendationLog)
    {
        $recommendationLog->load(['user', 'scholarship']);

        return response()->json([
            'id' => $recommendationLog->id,
            'student' => [
                'id' => $recommendationLog->user->id,
                'name' => $recommendationLog->user->name,
                'email' => $recommendationLog->user->email,
            ],
            'scholarship' => [
                'id' => $recommendationLog->scholarship->id,
                'name' => $recommendationLog->scholarship->name,
                'provider' => $recommendationLog->scholarship->provider,
                'award_type' => $recommendationLog->scholarship->award_type,
                'deadline' => $recommendationLog->scholarship->deadline?->format('M d, Y'),
                'application_link' => $recommendationLog->scholarship->application_link,
            ],
            'score' => $recommendationLog->score,
            'status' => $recommendationLog->status,
            'failed_hard_rules' => $recommendationLog->failed_hard_rules ?? [],
            'explanation' => $recommendationLog->explanation ?? [],
            'score_breakdown' => $recommendationLog->score_breakdown ?? [],
            'created_at' => $recommendationLog->created_at->format('M d, Y H:i'),
        ]);
    }
}