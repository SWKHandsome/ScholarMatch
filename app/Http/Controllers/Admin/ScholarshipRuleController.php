<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScholarshipRuleController extends Controller
{
    public function index(Scholarship $scholarship)
    {
        $rule = $scholarship->rule;
        return view('admin.rules.index', compact('scholarship', 'rule'));
    }

    public function create(Scholarship $scholarship)
    {
        return view('admin.rules.create', compact('scholarship'));
    }

    public function store(Request $request, Scholarship $scholarship)
    {
        $validated = $request->validate([
            'required_nationality' => ['nullable', 'string', 'max:255'],
            'required_study_level' => ['nullable', 'string', 'max:255'],
            'required_income_category' => ['nullable', 'string', 'max:255'],
            'max_household_income' => ['nullable', 'numeric', 'min:0'],
            'min_spm_as' => ['nullable', 'integer', 'min:0', 'max:12'],
            'min_spm_credits' => ['nullable', 'integer', 'min:0', 'max:12'],
            'min_cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'required_field_of_study' => ['nullable', Rule::in(config('scholarship.fields_of_study'))],
            'required_fields_of_study' => ['nullable', 'array'],
            'required_fields_of_study.*' => [Rule::in(config('scholarship.fields_of_study'))],
            'required_institution_type' => ['nullable', 'string', 'max:255'],
            'income_rule_type' => ['required', 'in:hard,soft,none'],
            'study_level_rule_type' => ['required', 'in:hard,soft,none'],
            'field_rule_type' => ['required', 'in:hard,soft,none'],
            'institution_rule_type' => ['required', 'in:hard,soft,none'],
        ]);

        $validated = $this->normaliseSupportedFields($request, $validated);
        $validated['scholarship_id'] = $scholarship->id;
        ScholarshipRule::updateOrCreate(
            ['scholarship_id' => $scholarship->id],
            $validated
        );
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.rules.index', $scholarship)
            ->with('success', 'Scholarship rules saved successfully.');
    }

    public function edit(Scholarship $scholarship, ScholarshipRule $rule)
    {
        return view('admin.rules.edit', compact('scholarship', 'rule'));
    }

    public function update(Request $request, Scholarship $scholarship, ScholarshipRule $rule)
    {
        $validated = $request->validate([
            'required_nationality' => ['nullable', 'string', 'max:255'],
            'required_study_level' => ['nullable', 'string', 'max:255'],
            'required_income_category' => ['nullable', 'string', 'max:255'],
            'max_household_income' => ['nullable', 'numeric', 'min:0'],
            'min_spm_as' => ['nullable', 'integer', 'min:0', 'max:12'],
            'min_spm_credits' => ['nullable', 'integer', 'min:0', 'max:12'],
            'min_cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'required_field_of_study' => ['nullable', Rule::in(config('scholarship.fields_of_study'))],
            'required_fields_of_study' => ['nullable', 'array'],
            'required_fields_of_study.*' => [Rule::in(config('scholarship.fields_of_study'))],
            'required_institution_type' => ['nullable', 'string', 'max:255'],
            'income_rule_type' => ['required', 'in:hard,soft,none'],
            'study_level_rule_type' => ['required', 'in:hard,soft,none'],
            'field_rule_type' => ['required', 'in:hard,soft,none'],
            'institution_rule_type' => ['required', 'in:hard,soft,none'],
        ]);

        $validated = $this->normaliseSupportedFields($request, $validated);
        $rule = $scholarship->rule ?? new ScholarshipRule(['scholarship_id' => $scholarship->id]);
        $rule->fill($validated);
        $rule->save();
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect()->route('admin.scholarships.rules.index', $scholarship)
            ->with('success', 'Scholarship rules updated successfully.');
    }

    private function normaliseSupportedFields(Request $request, array $validated): array
    {
        if ($request->boolean('field_selection_submitted')) {
            $validated['required_fields_of_study'] = $validated['required_fields_of_study'] ?? [];
            $validated['required_field_of_study'] = null;
        }

        return $validated;
    }
}
