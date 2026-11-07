<?php

use App\Models\AcademicResult;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use App\Support\AcademicQualifications;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => seedIncomeCategories());

function finalStpmPayload(array $changes = []): array
{
    return array_replace([
        'education_level' => 'STPM', 'study_status' => 'completed', 'grade_counts' => ['A' => 4, 'C' => 1],
        'stpm_pa_grade' => 'C', 'stpm_cgpa_confirmed' => '1', 'result_status' => 'official',
        'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University', 'field_of_study' => 'Engineering',
    ], $changes);
}

test('completed STPM CGPA includes Pengajian Am once and the best three other subjects', function ($counts, $pa, $expected) {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), finalStpmPayload([
        'grade_counts' => $counts, 'stpm_pa_grade' => $pa,
    ]))->assertSessionHasNoErrors();
    $academic = $user->fresh()->academicResult;
    expect($academic->cgpa)->toBe($expected)->and(collect($academic->grade_counts)->sortKeys()->all())->toBe(collect($counts)->sortKeys()->all())
        ->and($academic->stpm_pa_grade)->toBe($pa)->and($academic->stpm_cgpa_confirmed)->toBeTrue()
        ->and($academic->isReadyForRecommendations())->toBeTrue();
    $this->get(route('student.academic-result.edit'))->assertOk()->assertSee('Calculated Overall CGPA')
        ->assertSee('Pengajian Am Grade')->assertViewHas('academicResult', fn ($result) => $result->cgpa === $expected);
})->with([
    'MPM four-subject example' => [['B+' => 1, 'B' => 1, 'A-' => 1, 'A' => 1], 'B+', '3.50'],
    'weak PA cannot be dropped for a fourth A' => [['A' => 4, 'C' => 1], 'C', '3.50'],
    'same counts with PA A gives four As' => [['A' => 4, 'C' => 1], 'A', '4.00'],
    'half-up rounding uses integer hundredths' => [['A' => 2, 'A-' => 2], 'A', '3.84'],
    'lower extra subject ignored' => [['A' => 1, 'B' => 3, 'D' => 1], 'B', '3.25'],
    'zero remains a real calculated result' => [['F' => 4], 'F', '0.00'],
    'five identical grades count only four subjects' => [['C' => 5], 'C', '2.00'],
]);

test('a client supplied CGPA cannot override the server calculation or eligibility decision', function () {
    $user = makeStudent();
    $award = makeScholarship();
    $award->rule->qualificationRoutes()->create(['education_level' => 'STPM', 'study_status' => 'completed', 'min_cgpa' => 3.75]);
    $this->actingAs($user)->patch(route('student.academic-result.update'), finalStpmPayload(['cgpa' => 4]))->assertSessionHasNoErrors();
    expect($user->fresh()->academicResult->cgpa)->toBe('3.50');
    $decision = app(ScholarshipRecommendationService::class)->getRecommendations($user->fresh());
    expect($decision['error'])->toBeNull()->and($decision['recommendations'][0]['status'])->toBe('Not Eligible');
});

test('invalid completed STPM calculation data cannot replace saved results', function ($changes, $field) {
    $user = makeStudent();
    $this->actingAs($user)->patch(route('student.academic-result.update'), finalStpmPayload($changes))->assertSessionHasErrors($field);
    expect($user->fresh()->academicResult->education_level)->toBe('Undergraduate');
})->with([
    'missing PA' => [['stpm_pa_grade' => null], 'stpm_pa_grade'],
    'unknown PA' => [['stpm_pa_grade' => 'A*'], 'stpm_pa_grade'],
    'incomplete-result code' => [['stpm_pa_grade' => 'W'], 'stpm_pa_grade'],
    'array PA' => [['stpm_pa_grade' => ['C']], 'stpm_pa_grade'],
    'PA grade not in counts' => [['stpm_pa_grade' => 'B'], 'stpm_pa_grade'],
    'confirmation absent' => [['stpm_cgpa_confirmed' => null], 'stpm_cgpa_confirmed'],
    'confirmation rejected' => [['stpm_cgpa_confirmed' => '0'], 'stpm_cgpa_confirmed'],
    'too few final subjects' => [['grade_counts' => ['A' => 2, 'C' => 1]], 'grade_counts'],
    'too many final subjects' => [['grade_counts' => ['A' => 5, 'C' => 1]], 'grade_counts'],
    'mixed scales' => [['grade_counts' => ['A*' => 3, 'C' => 1]], 'grade_counts'],
    'fractional count' => [['grade_counts' => ['A' => 3.5, 'C' => 1]], 'grade_counts'],
    'pending results' => [['result_status' => 'pending'], 'result_status'],
]);

test('studying STPM still uses manual official CGPA and clears obsolete calculation fields', function () {
    $user = makeStudent([], ['education_level' => 'STPM', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => 3.5, 'grade_counts' => ['A' => 4, 'C' => 1], 'grading_scale' => 'stpm',
        'stpm_pa_grade' => 'C', 'stpm_cgpa_confirmed' => true]);
    $this->actingAs($user)->patch(route('student.academic-result.update'), finalStpmPayload([
        'study_status' => 'studying', 'year_of_study' => 2, 'cgpa' => 3.2,
    ]))->assertSessionHasNoErrors();
    $academic = $user->fresh()->academicResult;
    expect($academic->cgpa)->toBe('3.20')->and($academic->year_of_study)->toBe(2)
        ->and($academic->stpm_pa_grade)->toBeNull()->and($academic->stpm_cgpa_confirmed)->toBeFalse()
        ->and($academic->isReadyForRecommendations())->toBeTrue();
    $this->patch(route('student.academic-result.update'), finalStpmPayload([
        'study_status' => 'studying', 'year_of_study' => 2, 'cgpa' => null,
    ]))->assertSessionHasErrors('cgpa');
});

test('switching qualification clears Pengajian Am and the calculation confirmation', function () {
    $user = makeStudent([], ['education_level' => 'STPM', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => 3.5, 'grading_scale' => 'stpm', 'grade_counts' => ['A' => 4, 'C' => 1],
        'stpm_pa_grade' => 'C', 'stpm_cgpa_confirmed' => true]);
    $this->actingAs($user)->patch(route('student.academic-result.update'), finalStpmPayload(['education_level' => 'Diploma', 'cgpa' => 3.4]))->assertSessionHasNoErrors();
    $academic = $user->fresh()->academicResult;
    expect($academic->cgpa)->toBe('3.40')->and($academic->grade_counts)->toBeNull()
        ->and($academic->stpm_pa_grade)->toBeNull()->and($academic->stpm_cgpa_confirmed)->toBeFalse();
});

test('legacy completed official STPM records stay intact until the student confirms a new calculation', function () {
    $user = makeStudent([], ['education_level' => 'STPM', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => 3.5, 'grading_scale' => 'stpm', 'grade_counts' => ['A' => 2, 'B' => 2]]);
    expect($user->academicResult->isReadyForRecommendations())->toBeTrue();
    $this->actingAs($user)->get(route('student.academic-result.edit'))->assertOk()->assertSee('saved official CGPA of 3.50 is preserved');
    $this->patch(route('student.academic-result.update'), finalStpmPayload(['stpm_pa_grade' => null]))->assertSessionHasErrors('stpm_pa_grade');
    expect($user->fresh()->academicResult->cgpa)->toBe('3.50')->and($user->fresh()->academicResult->stpm_pa_grade)->toBeNull();
});

test('new calculation records need confirmation and a matching saved CGPA before recommendations', function () {
    $academic = new AcademicResult(['education_level' => 'STPM', 'study_status' => 'completed', 'funding_study_level' => 'Undergraduate',
        'institution_type' => 'Public University', 'field_of_study' => 'Engineering', 'result_status' => 'official',
        'grade_counts' => ['A' => 4, 'C' => 1], 'grading_scale' => 'stpm', 'stpm_pa_grade' => 'C', 'cgpa' => 4]);
    expect($academic->isReadyForRecommendations())->toBeFalse();
    $academic->stpm_cgpa_confirmed = true;
    expect($academic->isReadyForRecommendations())->toBeFalse();
    $academic->cgpa = AcademicQualifications::stpmCgpa($academic->grade_counts, $academic->stpm_pa_grade);
    expect($academic->isReadyForRecommendations())->toBeTrue();
});

test('calculation migration preserves prior results without inventing a Pengajian Am grade', function () {
    $migration = require database_path('migrations/2026_10_31_020000_add_stpm_cgpa_calculation.php');
    $migration->down();
    $user = makeStudent([], ['education_level' => 'STPM', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => 3.5]);
    $before = (array) DB::table('academic_results')->where('user_id', $user->id)->first();
    $migration->up();
    $after = (array) DB::table('academic_results')->where('user_id', $user->id)->first();
    expect($after)->toMatchArray($before)->and($after['stpm_pa_grade'])->toBeNull()->and((bool) $after['stpm_cgpa_confirmed'])->toBeFalse();
    $user->academicResult->refresh()->update(['stpm_pa_grade' => 'B', 'stpm_cgpa_confirmed' => true]);
    expect(fn () => $migration->down())->toThrow(RuntimeException::class, 'Preserve Pengajian Am grades');
});
