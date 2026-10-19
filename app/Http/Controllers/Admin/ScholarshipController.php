<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Services\ScholarshipRecommendationService;
use App\Services\ScholarshipRuleEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $scholarships = Scholarship::with('rule')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.scholarships.index', compact('scholarships'));
    }

    public function create()
    {
        return view('admin.scholarships.create', ['rule' => new ScholarshipRule]);
    }

    public function store(Request $request, ScholarshipRuleEditor $editor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'special_requirements' => ['nullable', 'string', 'max:5000'],
            'award_type' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'application_link' => ['required', 'url'],
            'is_active' => ['boolean'],
        ]);

        $rules = $request->boolean('rules_submitted') ? $editor->validate($request) : null;
        $validated['is_active'] = $request->boolean('is_active');
        DB::transaction(function () use ($validated, $rules, $editor) {
            $scholarship = Scholarship::create($validated);
            if ($rules !== null) {
                $editor->save($scholarship, $rules);
            }
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship created successfully.');
    }

    public function edit(Scholarship $scholarship)
    {
        return view('admin.scholarships.edit', ['scholarship' => $scholarship, 'rule' => $scholarship->rule ?? new ScholarshipRule]);
    }

    public function update(Request $request, Scholarship $scholarship, ScholarshipRuleEditor $editor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'special_requirements' => ['nullable', 'string', 'max:5000'],
            'award_type' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'application_link' => ['required', 'url'],
            'is_active' => ['boolean'],
        ]);

        $rules = $request->boolean('rules_submitted') ? $editor->validate($request) : null;
        $validated['is_active'] = $request->boolean('is_active');
        DB::transaction(function () use ($scholarship, $validated, $rules, $editor) {
            $scholarship->update($validated);
            if ($rules !== null) {
                $editor->save($scholarship, $rules);
            }
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship updated successfully.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship deleted successfully.');
    }
}
