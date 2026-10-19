<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Services\ScholarshipRecommendationService;
use App\Services\ScholarshipRuleEditor;
use Illuminate\Http\Request;

class ScholarshipRuleController extends Controller
{
    public function index(Scholarship $scholarship)
    {
        return view('admin.rules.index', ['scholarship' => $scholarship, 'rule' => $scholarship->rule]);
    }

    public function create(Scholarship $scholarship)
    {
        return view('admin.rules.create', ['scholarship' => $scholarship, 'rule' => $scholarship->rule ?? new ScholarshipRule]);
    }

    public function store(Request $request, Scholarship $scholarship, ScholarshipRuleEditor $editor)
    {
        $editor->save($scholarship, $editor->validate($request));
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.rules.index', $scholarship)
            ->with('success', 'Scholarship rules saved successfully.');
    }

    public function edit(Scholarship $scholarship, ScholarshipRule $rule)
    {
        abort_unless($rule->scholarship_id === $scholarship->id, 404);

        return view('admin.rules.edit', compact('scholarship', 'rule'));
    }

    public function update(Request $request, Scholarship $scholarship, ScholarshipRule $rule, ScholarshipRuleEditor $editor)
    {
        abort_unless($rule->scholarship_id === $scholarship->id, 404);
        $editor->save($scholarship, $editor->validate($request));
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.rules.index', $scholarship)
            ->with('success', 'Scholarship rules updated successfully.');
    }
}
