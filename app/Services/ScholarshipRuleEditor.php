<?php

namespace App\Services;

use App\Models\AcademicResult;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudyField;
use App\Support\AcademicQualifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScholarshipRuleEditor
{
    public function validate(Request $request, array $retainedFields = []): array
    {
        $validator = Validator::make($request->all(), [
            'qualification_routes_submitted' => $request->boolean('rules_submitted') ? ['required', 'accepted'] : ['sometimes', 'accepted'],
            'required_nationality' => ['nullable', 'string', Rule::in(config('scholarship.nationalities'))],
            'required_study_level' => ['nullable', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'required_income_category' => ['nullable', Rule::in(['B40', 'M40', 'T20'])],
            'max_household_income' => ['nullable', 'numeric', 'min:0'],
            'required_fields_of_study' => ['nullable', 'array'],
            'required_fields_of_study.*' => ['string', Rule::in(StudyField::selectableNames($retainedFields)), 'distinct'],
            'required_institution_type' => ['nullable', Rule::in(config('scholarship.institution_types'))],
            'qualification_routes' => ['nullable', 'array', 'max:50'],
            'qualification_routes.*' => ['array:education_level,study_status,min_spm_as,min_spm_credits,min_cgpa,min_year_of_study,max_year_of_study,grading_scale,grade_requirements,min_stam_rank,uec_average,stpm_average,uec_total'],
            'qualification_routes.*.education_level' => ['required', 'string', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'qualification_routes.*.study_status' => ['required', 'string', Rule::in(['studying', 'completed', 'any'])],
            'qualification_routes.*.min_spm_as' => ['nullable', 'integer', 'between:0,12'],
            'qualification_routes.*.min_spm_credits' => ['nullable', 'integer', 'between:0,12'],
            'qualification_routes.*.min_cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'qualification_routes.*.min_year_of_study' => ['nullable', 'integer', 'between:1,10'],
            'qualification_routes.*.max_year_of_study' => ['nullable', 'integer', 'between:1,10'],
            'qualification_routes.*.grading_scale' => ['nullable', 'string', Rule::in(array_keys(AcademicQualifications::scales()))],
            'qualification_routes.*.grade_requirements' => ['nullable', 'array', 'max:20'],
            'qualification_routes.*.grade_requirements.*' => ['array:grade,min_subjects'],
            'qualification_routes.*.grade_requirements.*.grade' => ['required', 'string', 'max:16'],
            'qualification_routes.*.grade_requirements.*.min_subjects' => ['required', 'integer', 'between:0,20'],
            'qualification_routes.*.min_stam_rank' => ['nullable', 'string', Rule::in(AcademicQualifications::RANKS)],
            'qualification_routes.*.uec_average' => ['nullable', 'array:subjects,max_average,minimum_grade'],
            'qualification_routes.*.uec_average.subjects' => ['required_with:qualification_routes.*.uec_average', 'integer', 'between:1,20'],
            'qualification_routes.*.uec_average.max_average' => ['required_with:qualification_routes.*.uec_average', 'numeric', 'between:1,9', 'decimal:0,2'],
            'qualification_routes.*.uec_average.minimum_grade' => ['required_with:qualification_routes.*.uec_average', 'string', Rule::in(AcademicQualifications::scales()['uec']['grades'])],
            'qualification_routes.*.stpm_average' => ['nullable', 'array:subjects,min_average,minimum_grade'],
            'qualification_routes.*.stpm_average.subjects' => ['required_with:qualification_routes.*.stpm_average', 'integer', 'between:1,5'],
            'qualification_routes.*.stpm_average.min_average' => ['required_with:qualification_routes.*.stpm_average', 'numeric', 'between:0,4', 'decimal:0,2'],
            'qualification_routes.*.stpm_average.minimum_grade' => ['required_with:qualification_routes.*.stpm_average', 'string', Rule::in(AcademicQualifications::scales()['stpm']['grades'])],
            'qualification_routes.*.uec_total' => ['nullable', 'array:subjects,max_total,minimum_grade'],
            'qualification_routes.*.uec_total.subjects' => ['required_with:qualification_routes.*.uec_total', 'integer', 'between:1,20'],
            'qualification_routes.*.uec_total.max_total' => ['required_with:qualification_routes.*.uec_total', 'integer', 'between:1,180'],
            'qualification_routes.*.uec_total.minimum_grade' => ['required_with:qualification_routes.*.uec_total', 'string', Rule::in(AcademicQualifications::scales()['uec']['grades'])],
        ], [
            'qualification_routes_submitted.required' => 'The qualification editor did not load. Reload this page with JavaScript enabled before saving.',
            'qualification_routes_submitted.accepted' => 'The qualification editor did not load. Reload this page with JavaScript enabled before saving.',
        ]);
        $labels = [
            'education_level' => 'qualification level', 'study_status' => 'study status',
            'min_spm_as' => 'minimum SPM As', 'min_spm_credits' => 'minimum SPM credits',
            'min_cgpa' => 'minimum CGPA', 'min_year_of_study' => 'minimum year', 'max_year_of_study' => 'maximum year',
        ];
        $attributes = [];
        $position = 0;
        foreach ((array) $request->input('qualification_routes', []) as $index => $route) {
            $position++;
            foreach ($labels as $field => $label) {
                $attributes["qualification_routes.$index.$field"] = "qualification $position $label";
            }
        }
        $validator->setAttributeNames($attributes);
        $validator->after(function ($validator) use ($request) {
            foreach ((array) $request->input('qualification_routes', []) as $index => $route) {
                if (! is_array($route)) {
                    continue;
                }
                $level = is_string($route['education_level'] ?? null) ? $route['education_level'] : '';
                if (in_array($level, AcademicQualifications::LEVELS, true) && ! AcademicQualifications::isCgpa($level) && ($route['study_status'] ?? null) !== 'completed') {
                    $validator->errors()->add("qualification_routes.$index.study_status", 'Exam-based qualifications require Completed status and official final results.');
                }
                foreach (['stpm_average' => 'STPM', 'uec_total' => 'UEC'] as $field => $requiredLevel) {
                    if (! empty($route[$field]) && $level !== $requiredLevel) {
                        $validator->errors()->add("qualification_routes.$index.$field", "This calculation is only supported for $requiredLevel.");
                    }
                }
                $total = $route['uec_total'] ?? null;
                if (is_array($total) && is_numeric($total['subjects'] ?? null) && is_numeric($total['max_total'] ?? null)
                    && ($total['max_total'] < $total['subjects'] || $total['max_total'] > $total['subjects'] * 9)) {
                    $validator->errors()->add("qualification_routes.$index.uec_total.max_total", 'The total must be between 1 and 9 points per selected subject.');
                }
                if (AcademicQualifications::isGradeBased($level)) {
                    $scale = $route['grading_scale'] ?? AcademicQualifications::defaultScale($level);
                    if ($scale === '') {
                        $scale = AcademicQualifications::defaultScale($level);
                    }
                    $conditions = is_array($route['grade_requirements'] ?? null) ? $route['grade_requirements'] : [];
                    if (($scale !== null && ! in_array($scale, AcademicQualifications::allowedScales($level), true)) || ($conditions && ! $scale)) {
                        $validator->errors()->add("qualification_routes.$index.grading_scale", 'Select the grading scale used by this qualification before adding grade conditions.');
                    }
                    $definition = is_string($scale) ? (AcademicQualifications::scales()[$scale] ?? null) : null;
                    $seen = [];
                    foreach ($conditions as $position => $condition) {
                        if (! is_array($condition)) {
                            continue;
                        }
                        $grade = $condition['grade'] ?? null;
                        if ($definition && (! is_string($grade) || ! in_array($grade, $definition['grades'], true))) {
                            $validator->errors()->add("qualification_routes.$index.grade_requirements.$position.grade", 'Choose a grade from this qualification’s grading scale.');
                        }
                        if (is_string($grade) && in_array($grade, $seen, true)) {
                            $validator->errors()->add("qualification_routes.$index.grade_requirements.$position.grade", 'Use one condition per grade threshold within this qualification box.');
                        }
                        $seen[] = $grade;
                        if ($definition && is_numeric($condition['min_subjects'] ?? null) && $condition['min_subjects'] > $definition['max_subjects']) {
                            $validator->errors()->add("qualification_routes.$index.grade_requirements.$position.min_subjects", 'The subject count exceeds this qualification’s supported limit.');
                        }
                    }
                }
                $min = $route['min_year_of_study'] ?? null;
                $max = $route['max_year_of_study'] ?? null;
                if (($route['study_status'] ?? null) === 'studying' && $min !== null && $min !== '' && $max !== null && $max !== ''
                    && is_numeric($min) && is_numeric($max) && (float) $min > (float) $max) {
                    $validator->errors()->add("qualification_routes.$index.max_year_of_study", 'Maximum year must be greater than or equal to minimum year.');
                }
            }
        });
        $data = $validator->validate();
        unset($data['qualification_routes_submitted']);
        $data['qualification_routes'] = array_values(array_map(function ($route) {
            $spm = $route['education_level'] === 'SPM';
            $cgpa = AcademicQualifications::isCgpa($route['education_level']);
            $gradeBased = AcademicQualifications::isGradeBased($route['education_level']);
            $studying = $cgpa && $route['study_status'] === 'studying';
            $conditions = $gradeBased ? array_values(array_map(fn ($condition) => [
                'grade' => $condition['grade'], 'min_subjects' => (int) $condition['min_subjects'],
            ], $route['grade_requirements'] ?? [])) : [];
            $average = $route['education_level'] === 'UEC' ? ($route['uec_average'] ?? null) : null;
            $stpmAverage = $route['education_level'] === 'STPM' ? ($route['stpm_average'] ?? null) : null;
            $uecTotal = $route['education_level'] === 'UEC' ? ($route['uec_total'] ?? null) : null;

            return [
                'education_level' => $route['education_level'], 'study_status' => $route['study_status'],
                'min_spm_as' => $spm ? ($route['min_spm_as'] ?? null) : null,
                'min_spm_credits' => $spm ? ($route['min_spm_credits'] ?? null) : null,
                'min_cgpa' => $cgpa ? ($route['min_cgpa'] ?? null) : null,
                'min_year_of_study' => $studying ? ($route['min_year_of_study'] ?? null) : null,
                'max_year_of_study' => $studying ? ($route['max_year_of_study'] ?? null) : null,
                'grading_scale' => $gradeBased ? (($route['grading_scale'] ?? null) ?: AcademicQualifications::defaultScale($route['education_level'])) : null,
                'grade_requirements' => $conditions ?: null,
                'min_stam_rank' => $route['education_level'] === 'STAM' ? ($route['min_stam_rank'] ?? null) : null,
                'uec_average' => $average ? ['subjects' => (int) $average['subjects'], 'max_average' => (float) $average['max_average'], 'minimum_grade' => $average['minimum_grade']] : null,
                'stpm_average' => $stpmAverage ? ['subjects' => (int) $stpmAverage['subjects'], 'min_average' => (float) $stpmAverage['min_average'], 'minimum_grade' => $stpmAverage['minimum_grade']] : null,
                'uec_total' => $uecTotal ? ['subjects' => (int) $uecTotal['subjects'], 'max_total' => (int) $uecTotal['max_total'], 'minimum_grade' => $uecTotal['minimum_grade']] : null,
            ];
        }, $data['qualification_routes'] ?? []));
        $data['required_fields_of_study'] = $data['required_fields_of_study'] ?? [];

        return $data;
    }

    public function save(Scholarship $scholarship, array $data): ScholarshipRule
    {
        return DB::transaction(function () use ($scholarship, $data) {
            $routes = $data['qualification_routes'];
            unset($data['qualification_routes']);
            // Old academic columns remain for rollback/import compatibility, not new editing.
            $rule = $scholarship->rule()->updateOrCreate(['scholarship_id' => $scholarship->id], array_merge($data, [
                'required_field_of_study' => null, 'accepted_education_levels' => null,
                'required_study_status' => null, 'min_year_of_study' => null, 'max_year_of_study' => null,
                'min_spm_as' => null, 'min_spm_credits' => null, 'min_cgpa' => null,
            ]));
            $rule->qualificationRoutes()->delete();
            $rule->qualificationRoutes()->createMany($routes);

            return $rule;
        });
    }
}
