<?php

use App\Models\AcademicResult;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\ScholarshipQualificationRoute;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;

beforeEach(fn () => seedIncomeCategories());

function rulePreviewPayload(array $counts = ['A' => 1, 'B' => 2]): array
{
    return [
        'rules_submitted' => 1, 'qualification_routes_submitted' => 1,
        'qualification_routes' => [['education_level' => 'A-Level', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 1], ['grade' => 'B', 'min_subjects' => 3]]]],
        'deadline' => today()->addMonth()->toDateString(),
        'sample' => ['education_level' => 'A-Level', 'study_status' => 'completed', 'grading_scale' => 'a_level', 'grade_counts' => $counts,
            'nationality' => 'Malaysian', 'household_income' => 2500, 'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University', 'field_of_study' => 'Engineering'],
    ];
}

test('draft ABB preview uses cumulative grade thresholds with no persistence', function ($counts, $passed) {
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), rulePreviewPayload($counts))->assertOk()->assertJsonPath('passed', $passed);
    expect(User::count())->toBe(1);
    foreach ([Scholarship::class, ScholarshipRule::class, ScholarshipQualificationRoute::class, AcademicResult::class, StudentProfile::class, RecommendationLog::class] as $model) {
        expect($model::count())->toBe(0);
    }
})->with(['AAB' => [['A' => 2, 'B' => 1], true], 'ABB' => [['A' => 1, 'B' => 2], true], 'BBB' => [['B' => 3], false], 'AAC' => [['A' => 2, 'C' => 1], false]]);

test('draft preview applies general restrictions and provider checks', function () {
    $data = rulePreviewPayload();
    $data['required_fields_of_study'] = ['Business'];
    $data['special_requirements'] = 'Interview required.';
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), $data)->assertOk()->assertJsonPath('passed', false)
        ->assertJsonPath('provider_checks', 'Interview required.');
});

test('draft preview uses OR between routes while rejecting invalid draft requirements', function () {
    $data = rulePreviewPayload();
    array_unshift($data['qualification_routes'], ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3]);
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), $data)->assertOk()->assertJsonPath('passed', true);
    $data['qualification_routes'][1]['grade_requirements'][0]['grade'] = 'INVALID';
    $this->postJson(route('admin.rule-preview'), $data)->assertUnprocessable()->assertJsonValidationErrors('qualification_routes.1.grade_requirements.0.grade');
});

test('completed STPM sample calculates PA plus best three and ignores submitted CGPA', function () {
    $data = rulePreviewPayload();
    $data['qualification_routes'] = [['education_level' => 'STPM', 'study_status' => 'completed', 'min_cgpa' => 3.75]];
    $data['sample'] = array_merge($data['sample'], ['education_level' => 'STPM', 'grading_scale' => 'stpm', 'grade_counts' => ['A' => 4, 'C' => 1], 'stpm_pa_grade' => 'C', 'cgpa' => 4]);
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), $data)->assertOk()->assertJsonPath('passed', false)->assertJsonPath('sample_cgpa', '3.50');
    $data['sample']['stpm_pa_grade'] = 'B';
    $this->postJson(route('admin.rule-preview'), $data)->assertUnprocessable()->assertJsonValidationErrors('stpm_pa_grade');
});

test('sample grade validation rejects mixed scales and excessive counts', function () {
    $data = rulePreviewPayload(['A1' => 3]);
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), $data)->assertUnprocessable()->assertJsonValidationErrors('grade_counts');
    $data['sample']['grade_counts'] = ['A' => 21];
    $this->postJson(route('admin.rule-preview'), $data)->assertUnprocessable()->assertJsonValidationErrors('grade_counts');
});

test('admin preview does not save changes to an existing scholarship', function () {
    $award = makeScholarship(['special_requirements' => 'Original requirement.']);
    $before = $award->fresh()->getAttributes();
    $rule = $award->rule->getAttributes();
    $data = rulePreviewPayload();
    $data['id'] = $award->id;
    $data['special_requirements'] = 'Unsaved changed requirement.';
    $this->actingAs(makeAdmin())->postJson(route('admin.rule-preview'), $data)->assertOk();
    expect($award->fresh()->getAttributes())->toBe($before);
    expect($award->rule->fresh()->getAttributes())->toBe($rule);
});

test('rule preview is protected by authentication and administrator role', function () {
    $this->postJson(route('admin.rule-preview'), rulePreviewPayload())->assertUnauthorized();
    $this->actingAs(makeStudent())->postJson(route('admin.rule-preview'), rulePreviewPayload())->assertForbidden();
});
