<?php

use App\Models\AcademicResult;

beforeEach(fn () => seedIncomeCategories());

function fundingAcademicPayload(array $overrides = []): array
{
    return array_merge([
        'education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2,
        'cgpa' => 3.5, 'result_status' => 'official', 'funding_study_level' => 'Undergraduate',
        'institution_type' => 'Public University', 'field_of_study' => 'Engineering',
    ], $overrides);
}

function fundingSelectValues(string $html, string $id): array
{
    preg_match('/<select\b[^>]*\bid="'.preg_quote($id, '/').'"[^>]*>(.*?)<\/select>/s', $html, $select);
    expect($select)->not->toBeEmpty();
    preg_match_all('/<option\b[^>]*\bvalue="([^"]*)"/', $select[1], $options);

    return array_values(array_filter($options[1], fn ($value) => $value !== ''));
}

test('all funding dropdowns exclude secondary levels and retain every pathway and degree option', function () {
    $student = makeStudent();
    makeScholarship();
    $studentForm = $this->actingAs($student)->get(route('student.academic-result.edit'))->assertOk();
    $filters = $this->get(route('student.recommendations'))->assertOk();
    $adminForm = $this->actingAs(makeAdmin())->get(route('admin.scholarships.create'))->assertOk();
    expect(fundingSelectValues($studentForm->getContent(), 'funding_study_level'))->toBe(AcademicResult::FUNDING_LEVELS);
    expect(fundingSelectValues($filters->getContent(), 'study_level'))->toBe(AcademicResult::FUNDING_LEVELS);
    expect(fundingSelectValues($adminForm->getContent(), 'required_study_level'))->toBe(AcademicResult::FUNDING_LEVELS);
    expect(fundingSelectValues($adminForm->getContent(), 'preview-funded'))->toBe(AcademicResult::FUNDING_LEVELS);
});

test('secondary funding choices are rejected in student admin preview and filter requests', function (string $level) {
    $student = makeStudent();
    $award = makeScholarship();
    $this->actingAs($student)->patch(route('student.academic-result.update'), fundingAcademicPayload(['funding_study_level' => $level]))
        ->assertSessionHasErrors('funding_study_level');
    expect($student->academicResult->fresh()->funding_study_level)->toBe('Undergraduate');
    $this->get(route('student.recommendations', ['study_level' => $level]))->assertSessionHasErrors('study_level');
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $award), [
        'qualification_routes_submitted' => 1, 'required_study_level' => $level,
    ])->assertSessionHasErrors('required_study_level');
    expect($award->rule->fresh()->required_study_level)->toBe('Undergraduate');
    $sample = fundingAcademicPayload(['funding_study_level' => $level, 'nationality' => 'Malaysian', 'household_income' => 2500]);
    unset($sample['result_status']);
    $this->postJson(route('admin.rule-preview'), ['qualification_routes_submitted' => 1, 'sample' => $sample])
        ->assertUnprocessable()->assertJsonValidationErrors('funding_study_level');
})->with(['SPM', 'IGCSE', 'O-Level']);

test('secondary results and qualification routes remain supported with pathway funding', function (string $level, string $scale) {
    $student = makeStudent();
    $payload = fundingAcademicPayload([
        'education_level' => $level, 'study_status' => 'completed', 'grading_scale' => $scale,
        'grade_counts' => ['A' => 5], 'funding_study_level' => 'Foundation',
    ]);
    $this->actingAs($student)->patch(route('student.academic-result.update'), $payload)->assertSessionHasNoErrors();
    expect($student->academicResult->fresh()->education_level)->toBe($level);
    $award = makeScholarship();
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $award), [
        'qualification_routes_submitted' => 1, 'required_study_level' => 'Foundation',
        'qualification_routes' => [['education_level' => $level, 'study_status' => 'completed', 'grading_scale' => $scale]],
    ])->assertSessionHasNoErrors();
    expect($award->rule->fresh()->qualificationRoutes->first()->education_level)->toBe($level);
})->with([['SPM', 'spm'], ['IGCSE', 'igcse_letters'], ['O-Level', 'o_level']]);

test('previous secondary funding values survive viewing and must be deliberately replaced when editing', function () {
    $student = makeStudent([], ['funding_study_level' => 'SPM']);
    $award = makeScholarship([], ['required_study_level' => 'SPM']);
    expect($student->academicResult->isReadyForRecommendations())->toBeTrue();
    $this->actingAs($student)->get(route('student.academic-result.edit'))->assertOk()->assertSee('Your saved funding level is SPM');
    expect($student->academicResult->fresh()->funding_study_level)->toBe('SPM');
    $form = $this->actingAs(makeAdmin())->get(route('admin.scholarships.edit', $award))->assertOk();
    $form->assertSee('Select a supported level (previously SPM)');
    expect($award->rule->fresh()->required_study_level)->toBe('SPM');
    $this->post(route('admin.scholarships.rules.store', $award), ['qualification_routes_submitted' => 1, 'required_study_level' => '__select_funding_level__'])
        ->assertSessionHasErrors('required_study_level');
    expect($award->rule->fresh()->required_study_level)->toBe('SPM');
    $this->post(route('admin.scholarships.rules.store', $award), ['qualification_routes_submitted' => 1, 'required_study_level' => 'Diploma'])
        ->assertSessionHasNoErrors();
    expect($award->rule->fresh()->required_study_level)->toBe('Diploma');
    $this->actingAs($student)->patch(route('student.academic-result.update'), fundingAcademicPayload(['funding_study_level' => 'Diploma']))
        ->assertSessionHasNoErrors();
    expect($student->academicResult->fresh()->funding_study_level)->toBe('Diploma');
});
