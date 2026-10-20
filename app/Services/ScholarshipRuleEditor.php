<?php

namespace App\Services;

use App\Models\AcademicResult;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScholarshipRuleEditor
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'qualification_routes_submitted' => $request->boolean('rules_submitted') ? ['required', 'accepted'] : ['sometimes', 'accepted'],
            'required_nationality' => ['nullable', 'string', Rule::in(config('scholarship.nationalities'))],
            'required_study_level' => ['nullable', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'required_income_category' => ['nullable', Rule::in(['B40', 'M40', 'T20'])],
            'max_household_income' => ['nullable', 'numeric', 'min:0'],
            'required_fields_of_study' => ['nullable', 'array'],
            'required_fields_of_study.*' => [Rule::in(config('scholarship.fields_of_study')), 'distinct'],
            'required_institution_type' => ['nullable', Rule::in(config('scholarship.institution_types'))],
            'qualification_routes' => ['nullable', 'array', 'max:50'],
            'qualification_routes.*' => ['array:education_level,study_status,min_spm_as,min_spm_credits,min_cgpa,min_year_of_study,max_year_of_study'],
            'qualification_routes.*.education_level' => ['required', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'qualification_routes.*.study_status' => ['required', Rule::in(['studying', 'completed', 'any'])],
            'qualification_routes.*.min_spm_as' => ['nullable', 'integer', 'between:0,12'],
            'qualification_routes.*.min_spm_credits' => ['nullable', 'integer', 'between:0,12'],
            'qualification_routes.*.min_cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'qualification_routes.*.min_year_of_study' => ['nullable', 'integer', 'between:1,10'],
            'qualification_routes.*.max_year_of_study' => ['nullable', 'integer', 'between:1,10'],
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
                if (($route['education_level'] ?? null) === 'SPM' && ($route['study_status'] ?? null) !== 'completed') {
                    $validator->errors()->add("qualification_routes.$index.study_status", 'SPM requires Completed status and official results.');
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
            $studying = $route['study_status'] === 'studying';

            return [
                'education_level' => $route['education_level'], 'study_status' => $route['study_status'],
                'min_spm_as' => $spm ? ($route['min_spm_as'] ?? null) : null,
                'min_spm_credits' => $spm ? ($route['min_spm_credits'] ?? null) : null,
                'min_cgpa' => ! $spm ? ($route['min_cgpa'] ?? null) : null,
                'min_year_of_study' => $studying ? ($route['min_year_of_study'] ?? null) : null,
                'max_year_of_study' => $studying ? ($route['max_year_of_study'] ?? null) : null,
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
