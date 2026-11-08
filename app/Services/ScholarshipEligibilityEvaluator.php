<?php

namespace App\Services;

use App\Models\AcademicResult;
use App\Models\Scholarship;
use App\Models\StudentProfile;
use App\Support\AcademicQualifications;
use App\Support\ProgrammeChoices;

class ScholarshipEligibilityEvaluator
{
    public function evaluate(Scholarship $scholarship, StudentProfile $profile, AcademicResult $academic, string $incomeCategory): array
    {
        $rule = $scholarship->rule;
        $checks = [];
        $add = function (string $key, string $label, mixed $required, mixed $actual, bool $met, string $message, bool $applicable = true, bool $checked = true) use (&$checks) {
            $checks[] = [
                'key' => $key, 'label' => $label, 'required' => $required, 'actual' => $actual,
                'outcome' => ! $applicable ? 'not_applicable' : (! $checked ? 'not_checked' : ($met ? 'met' : 'not_met')),
                'message' => $applicable ? $message : 'No '.$label.' requirement is specified.',
            ];
        };
        $nationalityMatches = ! $rule->required_nationality
            || $profile->nationality === $rule->required_nationality;
        $add('nationality', 'Nationality', $rule->required_nationality, $profile->nationality,
            $nationalityMatches, $nationalityMatches ? 'You meet the nationality requirement.'
                : "This scholarship requires {$rule->required_nationality} nationality.", (bool) $rule->required_nationality);
        $add('funding_level', 'Funded study level', $rule->required_study_level, $academic->funding_study_level,
            $academic->funding_study_level === $rule->required_study_level,
            'The scholarship funds '.AcademicQualifications::label($rule->required_study_level).'; you selected '.AcademicQualifications::label($academic->funding_study_level).'.',
            (bool) $rule->required_study_level);
        $checks = array_merge($checks, app(AcademicRouteEvaluator::class)->evaluate($rule->academicRoutes(), $academic));
        $add('income_category', 'Income category', $rule->required_income_category, $incomeCategory,
            $incomeCategory === $rule->required_income_category,
            "Required income category: {$rule->required_income_category}; your category: {$incomeCategory}.",
            (bool) $rule->required_income_category);
        $add('max_household_income', 'Maximum household income', $rule->max_household_income, $profile->household_income,
            (float) $profile->household_income <= (float) $rule->max_household_income,
            "Maximum household income: RM {$rule->max_household_income}; your income: RM {$profile->household_income}.",
            $rule->max_household_income !== null);
        $fields = $rule->supportedFieldsOfStudy();
        $fieldUndecided = $academic->field_of_study === ProgrammeChoices::UNDECIDED;
        $institutionUndecided = $academic->institution_type === ProgrammeChoices::UNDECIDED;
        $fieldGuidance = count($fields) === 1
            ? 'Not checked — choose '.implode(', ', $fields).' to meet this condition.'
            : 'Not checked — choose one of these fields to meet this condition: '.implode(', ', $fields).'.';
        $add('field_of_study', 'Field of study', $fields, ProgrammeChoices::label($academic->field_of_study),
            in_array($academic->field_of_study, $fields, true),
            $fieldUndecided ? $fieldGuidance : 'Supported fields: '.implode(', ', $fields)."; your field: {$academic->field_of_study}.", ! empty($fields), ! $fieldUndecided);
        $add('institution_type', 'Institution type', $rule->required_institution_type, ProgrammeChoices::label($academic->institution_type),
            $academic->institution_type === $rule->required_institution_type,
            $institutionUndecided ? "Not checked — choose {$rule->required_institution_type} to meet this condition."
                : "Required institution: {$rule->required_institution_type}; your institution: {$academic->institution_type}.",
            (bool) $rule->required_institution_type, ! $institutionUndecided);

        if ($scholarship->deadline !== null) {
            $add('deadline', 'Application deadline', $scholarship->deadline->toDateString(), today()->toDateString(),
                ! $scholarship->isExpired(), $scholarship->isDemo()
                    ? 'The fictional demo deadline has not passed.'
                    : 'The recorded closing date has not passed. Confirm the current application window with the provider.');
        } else {
            // A rolling or automatic award has no single calendar closing date.
            $checks[] = [
                'key' => 'deadline', 'label' => 'Application deadline',
                'required' => $scholarship->deadlineLabel(), 'actual' => null, 'outcome' => 'not_applicable',
                'message' => $scholarship->application_notes ?: 'The closing date needs confirmation with the provider.',
            ];
        }
        $failed = array_values(array_map(fn ($check) => $check['key'],
            array_filter($checks, fn ($check) => $check['outcome'] === 'not_met')));
        $eligible = empty($failed);
        $special = trim((string) $scholarship->special_requirements);
        if ($scholarship->deadline === null && ! in_array($scholarship->deadline_type, ['rolling', 'automatic'], true)) {
            $special = trim($special."\n\nApplication dates must be confirmed with the provider.");
        }

        return [
            'scholarship_id' => $scholarship->id,
            'scholarship_data' => [
                'id' => $scholarship->id, 'name' => $scholarship->name,
                'description' => $scholarship->description, 'provider_name' => $scholarship->provider,
                'award_type' => $scholarship->award_type, 'deadline' => $scholarship->deadline?->toDateString(),
                'deadline_label' => $scholarship->deadlineLabel(),
                'deadline_type' => $scholarship->deadline_type,
                'application_link' => $scholarship->application_link,
                'application_notes' => $scholarship->application_notes,
                'is_demo' => $scholarship->isDemo(),
            ],
            'status' => $eligible ? 'Eligible' : 'Not Eligible',
            'requires_verification' => $eligible && $special !== '',
            'special_requirements' => $special,
            'failed_requirements' => $failed,
            'requirement_results' => $checks,
            'explanation' => array_column($checks, 'message'),
        ];
    }
}
