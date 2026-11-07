<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;

class RecommendationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = RecommendationLog::with(['user', 'scholarship']);

        if ($request->filled('status')) {
            if ($request->status === 'legacy') {
                $query->where(fn ($q) => $q->whereNull('evaluation_version')
                    ->orWhere('evaluation_version', '!=', ScholarshipRecommendationService::ALGORITHM_VERSION));
            } else {
                $query->where('evaluation_version', ScholarshipRecommendationService::ALGORITHM_VERSION);
                if ($request->status === 'eligible_verification') {
                    $query->where('status', 'Eligible')->where('requires_verification', true);
                } else {
                    $query->where('status', $request->status);
                }
            }
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
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        $logs = $query->latest('updated_at')->paginate(20);
        $scholarships = Scholarship::orderBy('name')->get();

        return view('admin.recommendation-logs.index', compact('logs', 'scholarships'));
    }

    public function show(RecommendationLog $recommendationLog)
    {
        $recommendationLog->load(['user', 'scholarship']);

        return view('admin.recommendation-logs.show', compact('recommendationLog'));
    }
}
