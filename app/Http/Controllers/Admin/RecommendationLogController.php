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

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('scholarship', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('scholarship_id')) {
            $query->where('scholarship_id', $request->scholarship_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(20);
        $scholarships = \App\Models\Scholarship::orderBy('name')->get();

        return view('admin.recommendation-logs.index', compact('logs', 'scholarships'));
    }

    public function show(RecommendationLog $recommendationLog)
    {
        $recommendationLog->load(['user', 'scholarship']);

        return view('admin.recommendation-logs.show', compact('recommendationLog'));
    }
}
