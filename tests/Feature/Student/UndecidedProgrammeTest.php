<?php

use App\Models\RecommendationLog;
use App\Models\StudyField;
use App\Services\ScholarshipRecommendationService;
use App\Support\ProgrammeChoices;
use App\Support\StudentOnboarding;
use App\Support\StudentRequirementExplanation;

beforeEach(fn () => seedIncomeCategories());

function undecidedProgrammePayload(array $overrides = []): array
{
    return array_merge([
        'education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2,
        'funding_study_level' => 'Undergraduate', 'cgpa' => 3.50, 'result_status' => 'official',
        'institution_type' => ProgrammeChoices::UNDECIDED, 'field_of_study' => ProgrammeChoices::UNDECIDED,
    ], $overrides);
}

test('undecided programme choices complete setup independently and survive reopening the form', function (array $choices, array $unchecked) {
    $student = makeStudent();
    $award = makeScholarship();
    $this->actingAs($student)->patch(route('student.academic-result.update'), undecidedProgrammePayload($choices))
        ->assertSessionHasNoErrors()->assertRedirect(route('student.dashboard'));
    $student = $student->fresh();
    expect($student->academicResult->isReadyForRecommendations())->toBeTrue();
    expect(StudentOnboarding::nextRoute($student))->toBeNull();
    foreach (['field_of_study', 'institution_type'] as $key) {
        expect($student->academicResult->$key)->toBe($choices[$key] ?? ProgrammeChoices::UNDECIDED);
    }
    $this->get(route('student.academic-result.edit'))->assertOk()
        ->assertSee('Haven&#039;t decided', false)->assertSee('value="__undecided__" selected', false);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($student);
    expect($result['error'])->toBeNull();
    $decision = $result['recommendations'][0];
    expect($decision['status'])->toBe('Eligible');
    expect($decision['requires_verification'])->toBeFalse();
    $checks = collect($decision['requirement_results'])->keyBy('key');
    foreach (['field_of_study', 'institution_type'] as $key) {
        expect($checks[$key]['outcome'])->toBe(in_array($key, $unchecked, true) ? 'not_checked' : 'met');
    }
    $this->get(route('student.dashboard'))->assertOk()->assertSee('1 eligible');
    $this->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Programme requirements not checked')->assertSee('You meet all checked automatic requirements.')
        ->assertSee('data-explanation-status="not-checked"', false)->assertDontSee('__undecided__');
})->with([
    'both undecided' => [[], ['field_of_study', 'institution_type']],
    'only field undecided' => [['institution_type' => 'Public University'], ['field_of_study']],
    'only institution undecided' => [['field_of_study' => 'Engineering'], ['institution_type']],
]);

test('blank programme choices and missing official results remain incomplete', function (array $changes, string $field) {
    $student = makeStudent();
    $this->actingAs($student)->patch(route('student.academic-result.update'), undecidedProgrammePayload($changes))
        ->assertSessionHasErrors($field);
    $academic = $student->academicResult;
    $academic->fill(undecidedProgrammePayload($changes));
    expect($academic->isReadyForRecommendations())->toBeFalse();
})->with([
    'blank field' => [['field_of_study' => null], 'field_of_study'],
    'blank institution' => [['institution_type' => null], 'institution_type'],
    'pending results' => [['result_status' => 'pending'], 'result_status'],
    'missing official CGPA' => [['cgpa' => null], 'cgpa'],
]);

test('known failures still reject an otherwise undecided student', function (array $academic, string $failed) {
    $student = makeStudent([], array_merge([
        'field_of_study' => ProgrammeChoices::UNDECIDED, 'institution_type' => ProgrammeChoices::UNDECIDED,
    ], $academic));
    makeScholarship();
    $decision = app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations'][0];
    expect($decision['status'])->toBe('Not Eligible');
    expect($decision['failed_requirements'])->toContain($failed);
    expect($decision['requires_verification'])->toBeFalse();
})->with([
    'CGPA failure' => [['cgpa' => 2.50], 'min_cgpa'],
    'decided field fails' => [['field_of_study' => 'Business'], 'field_of_study'],
    'decided institution fails' => [['institution_type' => 'Private University'], 'institution_type'],
]);

test('unrestricted criteria remain unrestricted rather than becoming unchecked', function () {
    $student = makeStudent([], ['field_of_study' => ProgrammeChoices::UNDECIDED, 'institution_type' => ProgrammeChoices::UNDECIDED]);
    $award = makeScholarship([], ['required_field_of_study' => null, 'required_institution_type' => null]);
    $decision = app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations'][0];
    $display = StudentRequirementExplanation::prepare($decision['requirement_results']);
    expect($decision['status'])->toBe('Eligible');
    expect($display['unchecked'])->toBeEmpty();
    foreach (['field_of_study', 'institution_type'] as $key) {
        expect(collect($decision['requirement_results'])->firstWhere('key', $key)['outcome'])->toBe('not_applicable');
    }
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertDontSee('Programme requirements not checked')->assertSee('No restriction');
});

test('unchecked details retain all supported fields and provider warnings remain independent', function () {
    $student = makeStudent([], ['field_of_study' => ProgrammeChoices::UNDECIDED, 'institution_type' => ProgrammeChoices::UNDECIDED]);
    $award = makeScholarship(['special_requirements' => 'Attend an interview.'], ['required_fields_of_study' => ['Engineering', 'Business']]);
    $decision = app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations'][0];
    expect($decision['status'])->toBe('Eligible');
    expect($decision['requires_verification'])->toBeTrue();
    $display = StudentRequirementExplanation::prepare($decision['requirement_results']);
    expect($display['unchecked'])->toHaveCount(2);
    expect(array_column($display['general'], 'key'))->not->toContain('field_of_study', 'institution_type');
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Not checked — choose one of these fields to meet this condition: Engineering, Business.')
        ->assertSee('Not checked — choose Public University to meet this condition.')
        ->assertSee('Attend an interview.');
});

test('deciding a programme reevaluates cached recommendations in both directions', function () {
    $student = makeStudent([], ['field_of_study' => ProgrammeChoices::UNDECIDED, 'institution_type' => ProgrammeChoices::UNDECIDED]);
    makeScholarship();
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($student)['recommendations'][0]['status'])->toBe('Eligible');
    foreach ([['Business', 'Not Eligible', 'not_met'], ['Engineering', 'Eligible', 'met'], [ProgrammeChoices::UNDECIDED, 'Eligible', 'not_checked']] as [$field, $status, $outcome]) {
        $this->actingAs($student)->patch(route('student.academic-result.update'), undecidedProgrammePayload(['field_of_study' => $field]))
            ->assertSessionHasNoErrors();
        $student = $student->fresh();
        $decision = $service->getRecommendations($student)['recommendations'][0];
        expect($decision['status'])->toBe($status);
        expect(collect($decision['requirement_results'])->firstWhere('key', 'field_of_study')['outcome'])->toBe($outcome);
    }
});

test('programme filters still use scholarship restrictions and logs distinguish unchecked conditions', function () {
    $student = makeStudent([], ['field_of_study' => ProgrammeChoices::UNDECIDED, 'institution_type' => ProgrammeChoices::UNDECIDED]);
    $award = makeScholarship();
    $other = makeScholarship(['name' => 'Business Award'], ['required_field_of_study' => 'Business', 'required_institution_type' => 'Private University']);
    $this->actingAs($student)->get(route('student.recommendations', ['field_of_study' => 'Engineering', 'institution_type' => 'Public University']))
        ->assertOk()->assertSee($award->name)->assertDontSee($other->name)->assertSee('Not checked — choose Engineering to meet this condition.');
    $log = RecommendationLog::where('user_id', $student->id)->where('scholarship_id', $award->id)->firstOrFail();
    expect($log->status)->toBe('Eligible');
    expect($log->isCurrentEvaluation())->toBeTrue();
    expect($log->requires_verification)->toBeFalse();
    expect(collect($log->requirement_results)->firstWhere('key', 'field_of_study')['actual'])->toBe(ProgrammeChoices::UNDECIDED_LABEL);
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.show', $log))->assertOk()
        ->assertSee('Not checked')->assertSee('Not checked — choose Engineering to meet this condition.')->assertDontSee('__undecided__');
    $this->get(route('admin.students.show', $student))->assertOk()->assertSee('Haven&#039;t decided', false)->assertDontSee('__undecided__');
});

test('undecided is a sample student choice rather than an administrator scholarship restriction', function () {
    $admin = makeAdmin();
    $this->actingAs($admin)->post(route('admin.study-fields.store'), ['name' => ProgrammeChoices::UNDECIDED, 'is_active' => 1])
        ->assertSessionHasErrorsIn('studyField', 'name');
    $this->post(route('admin.study-fields.store'), ['name' => ProgrammeChoices::UNDECIDED_LABEL, 'is_active' => 1])
        ->assertSessionHasErrorsIn('studyField', 'name');
    expect(StudyField::where('name', ProgrammeChoices::UNDECIDED)->exists())->toBeFalse();
    $payload = [
        'rules_submitted' => 1, 'qualification_routes_submitted' => 1, 'qualification_routes' => [],
        'required_fields_of_study' => ['Engineering'], 'required_institution_type' => 'Public University',
        'deadline' => today()->addMonth()->toDateString(),
        'sample' => array_diff_key(undecidedProgrammePayload(), ['result_status' => true]) + ['nationality' => 'Malaysian', 'household_income' => 2500],
    ];
    $this->postJson(route('admin.rule-preview'), $payload)->assertOk()->assertJsonPath('passed', true)
        ->assertJsonFragment(['key' => 'field_of_study', 'outcome' => 'not_checked']);
    $this->postJson(route('admin.rule-preview'), array_merge($payload, [
        'required_fields_of_study' => [ProgrammeChoices::UNDECIDED], 'required_institution_type' => ProgrammeChoices::UNDECIDED,
    ]))->assertUnprocessable()->assertJsonValidationErrors(['required_fields_of_study.0', 'required_institution_type']);
});
