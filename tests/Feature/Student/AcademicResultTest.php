<?php

use App\Models\AcademicResult;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Support\Facades\DB;

function academicPayload(array $overrides = []): array
{
    return array_merge([
        'education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2,
        'funding_study_level' => 'Undergraduate', 'cgpa' => 3.50, 'result_status' => 'official',
        'institution_type' => 'Public University', 'field_of_study' => 'Engineering',
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
    'missing institution' => [['institution_type' => null], 'institution_type'],
    'invalid institution' => [['institution_type' => 'Unknown'], 'institution_type'],
    'missing field' => [['field_of_study' => null], 'field_of_study'],
    'invalid field' => [['field_of_study' => 'Unlisted Field'], 'field_of_study'],
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

test('programme details save with academic results and immediately change recommendation matching', function () {
    seedIncomeCategories();
    $user = makeStudent();
    makeScholarship();
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($user)['recommendations'][0]['status'])->toBe('Eligible');
    $this->actingAs($user)->patch(route('student.academic-result.update'), academicPayload([
        'institution_type' => 'Private University', 'field_of_study' => 'Business',
    ]))->assertSessionHasNoErrors();
    $academic = $user->fresh()->academicResult;
    expect($academic->institution_type)->toBe('Private University')->and($academic->field_of_study)->toBe('Business');
    $rec = $service->getRecommendations($user->fresh())['recommendations'][0];
    expect($rec['status'])->toBe('Not Eligible')->and($rec['failed_requirements'])->toContain('institution_type', 'field_of_study');
});

test('legacy programme details prefill only a new academic record without inventing results', function () {
    $user = makeStudent();
    $user->academicResult()->delete();
    DB::table('student_profiles')->where('user_id', $user->id)->update([
        'institution_type' => 'Private University', 'field_of_study' => 'Business',
    ]);
    $this->actingAs($user->fresh())->get(route('student.academic-result.edit'))->assertOk()->assertViewHas('academicResult', function ($academic) {
        return ! $academic->exists && $academic->education_level === null && $academic->cgpa === null
            && $academic->institution_type === 'Private University' && $academic->field_of_study === 'Business';
    });
    expect($user->fresh()->academicResult)->toBeNull();
});

test('academic result routes retain access control', function () {
    $this->get(route('student.academic-result.edit'))->assertRedirect(route('login'));
    $this->actingAs(makeAdmin())->get(route('student.academic-result.edit'))->assertForbidden();
});
