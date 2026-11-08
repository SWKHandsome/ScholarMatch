<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipQualificationRoute;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\StudyField;
use App\Services\AcademicResultInput;
use App\Services\ScholarshipEligibilityEvaluator;
use App\Services\ScholarshipRuleEditor;
use App\Support\StudentRequirementExplanation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RulePreviewController extends Controller
{
    public function __invoke(Request $request, ScholarshipRuleEditor $editor, AcademicResultInput $academicInput, ScholarshipEligibilityEvaluator $evaluator)
    {
        $draftRules = $editor->validate($request, StudyField::names());
        $data = $request->validate([
            'sample' => ['required', 'array:education_level,study_status,year_of_study,funding_study_level,institution_type,field_of_study,grading_scale,grade_counts,stam_rank,cgpa,stpm_pa_grade,nationality,household_income'],
            'sample.nationality' => ['required', Rule::in(config('scholarship.nationalities'))],
            'sample.household_income' => ['required', 'numeric', 'between:0,999999999'],
            'deadline' => ['nullable', 'date'],
            'deadline_type' => ['nullable', Rule::in(array_keys(Scholarship::DEADLINE_TYPES))],
            'special_requirements' => ['nullable', 'string', 'max:5000'],
            'application_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        // Reuse the official form's data-shape validation on unsaved synthetic models.
        $sample = $data['sample'];
        $validatedAcademic = $academicInput->validate(new Request(array_merge($sample, ['result_status' => 'official', 'stpm_cgpa_confirmed' => '1'])), is_string($sample['field_of_study'] ?? null) ? $sample['field_of_study'] : null);
        $academic = new AcademicResult($validatedAcademic);
        $category = IncomeCategory::classifyIncome((float) $sample['household_income']);
        if ($category === null) {
            throw ValidationException::withMessages(['sample.household_income' => 'The sample income cannot be classified. Review the configured income ranges.']);
        }
        $profile = new StudentProfile(['nationality' => $sample['nationality'], 'household_income' => $sample['household_income']]);
        $routes = $draftRules['qualification_routes'];
        unset($draftRules['qualification_routes']);
        $rule = new ScholarshipRule($draftRules);
        $rule->setRelation('qualificationRoutes', collect(array_map(fn ($route) => new ScholarshipQualificationRoute($route), $routes)));
        $metadata = Arr::only($data, ['deadline', 'deadline_type', 'special_requirements', 'application_notes']);
        $award = new Scholarship(array_merge($metadata, ['name' => 'Unsaved rule preview', 'deadline_type' => $data['deadline_type'] ?? (! empty($data['deadline']) ? 'fixed' : 'unconfirmed')]));
        $award->setRelation('rule', $rule);
        $evaluation = $evaluator->evaluate($award, $profile, $academic, $category);
        $display = StudentRequirementExplanation::prepare($evaluation['requirement_results']);

        return response()->json([
            'passed' => $evaluation['status'] === 'Eligible',
            'message' => $evaluation['status'] === 'Eligible' ? 'Passes checked automatic requirements' : 'Does not pass all checked automatic requirements',
            'checks' => array_merge($display['failed'], $display['unchecked'], $display['academic'], $display['general']),
            'provider_checks' => $evaluation['special_requirements'],
            'sample_cgpa' => $academic->cgpa,
            'selected_route' => $display['selected']['label'] ?? 'No qualification restriction',
        ]);
    }
}
