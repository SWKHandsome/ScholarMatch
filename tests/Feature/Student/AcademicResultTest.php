<?php

use App\Models\AcademicResult;
use App\Models\User;

function academicPayload(array $overrides = []): array
{
    return array_merge([
        'education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2,
        'funding_study_level' => 'Undergraduate', 'cgpa' => 3.50, 'result_status' => 'official',
    ], $overrides);
}

test('academic form explains official results study status and funding level', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.academic-result.edit'))->assertOk()
        ->assertSee('Latest Study Level')->assertSee('Study Status')
        ->assertSee('Current Year of Study')->assertSee('Study Level Seeking Funding')
        ->assertSee('official results')->assertDontSee('Preliminary');
});

test('currently studying students can save their latest official CGPA', function () {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload())
        ->assertSessionHasNoErrors()->assertRedirect(route('student.dashboard'));
    $result = $user->fresh()->academicResult;
    expect($result->year_of_study)->toBe(2)->and($result->isReadyForRecommendations())->toBeTrue();
});

test('completed CGPA study clears the previous year and irrelevant SPM results', function () {
    $user = makeStudent();
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload([
        'study_status' => 'completed', 'year_of_study' => 3, 'spm_as' => 8, 'spm_credits' => 9,
    ]))->assertSessionHasNoErrors();
    $result = $user->fresh()->academicResult;
    expect($result->year_of_study)->toBeNull()->and($result->spm_as)->toBeNull()->and($result->spm_credits)->toBeNull();
});

test('SPM graduates can save official counts and choose a different funding level', function () {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload([
        'education_level' => 'SPM', 'study_status' => 'completed',
        'spm_as' => 8, 'spm_credits' => 9, 'funding_study_level' => 'Foundation',
    ]))->assertSessionHasNoErrors();
    $result = $user->fresh()->academicResult;
    expect($result->cgpa)->toBeNull()->and($result->year_of_study)->toBeNull();
    expect($result->isReadyForRecommendations())->toBeTrue();
});

test('invalid academic submissions cannot save', function ($changes, $field) {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload($changes))
        ->assertSessionHasErrors($field);
    expect($user->fresh()->academicResult)->toBeNull();
})->with([
    'pending' => [['result_status' => 'pending'], 'result_status'],
    'missing result' => [['cgpa' => null], 'cgpa'],
    'high CGPA' => [['cgpa' => 4.01], 'cgpa'],
    'negative CGPA' => [['cgpa' => -0.01], 'cgpa'],
    'missing year' => [['year_of_study' => null], 'year_of_study'],
    'invalid year' => [['year_of_study' => 0], 'year_of_study'],
    'missing status' => [['study_status' => null], 'study_status'],
    'missing target' => [['funding_study_level' => null], 'funding_study_level'],
    'invalid source' => [['education_level' => 'Unknown'], 'education_level'],
    'SPM studying' => [['education_level' => 'SPM', 'spm_as' => 8, 'spm_credits' => 9], 'study_status'],
    'SPM missing counts' => [['education_level' => 'SPM', 'study_status' => 'completed'], 'spm_as'],
    'SPM counts overlap' => [['education_level' => 'SPM', 'study_status' => 'completed', 'spm_as' => 8, 'spm_credits' => 7], 'spm_credits'],
    'SPM too many As' => [['education_level' => 'SPM', 'study_status' => 'completed', 'spm_as' => 13, 'spm_credits' => 13], 'spm_as'],
]);

test('real zero CGPA is stored as an official result rather than missing information', function () {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload(['cgpa' => 0]))
        ->assertSessionHasNoErrors();
    expect($user->fresh()->academicResult->cgpa)->toBe('0.00');
});

test('students update one academic record', function () {
    $user = makeStudent();
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload(['cgpa' => 3.90]))
        ->assertSessionHasNoErrors();
    expect(AcademicResult::where('user_id', $user->id)->count())->toBe(1);
});

test('academic result routes retain access control', function () {
    $this->get(route('student.academic-result.edit'))->assertRedirect(route('login'));
    $this->actingAs(makeAdmin())->get(route('student.academic-result.edit'))->assertForbidden();
});
