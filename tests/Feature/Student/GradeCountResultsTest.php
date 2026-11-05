<?php

use App\Models\AcademicResult;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use App\Support\AcademicQualifications;

beforeEach(fn () => seedIncomeCategories());

function gradeCountPayload(string $level = 'SPM', ?string $scale = 'spm', array $counts = ['A+' => 2, 'A' => 1, 'A-' => 1, 'B' => 2, 'C' => 1, 'G' => 1]): array
{
    return [
        'education_category' => AcademicQualifications::category($level), 'education_level' => $level,
        'grading_scale' => $scale, 'grade_counts' => $counts, 'study_status' => 'completed',
        'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University',
        'field_of_study' => 'Engineering', 'result_status' => 'official',
    ];
}

test('each supported grade format saves its official counts and reloads the selected qualification', function ($level, $scale, $counts) {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->patch(route('student.academic-result.update'), gradeCountPayload($level, $scale, $counts))
        ->assertSessionHasNoErrors()->assertRedirect(route('student.profile.edit'));
    $academic = $user->fresh()->academicResult;
    expect($academic->education_level)->toBe($level)->and($academic->grading_scale)->toBe($scale)
        ->and($academic->grade_counts)->toBe($counts)->and($academic->cgpa)->toBeNull()
        ->and($academic->year_of_study)->toBeNull()->and($academic->isReadyForRecommendations())->toBeTrue();
    $this->get(route('student.academic-result.edit'))->assertOk()->assertSee('Grade summary')
        ->assertSee('Final Results (Official)')->assertDontSee('name="spm_as"', false)
        ->assertViewHas('academicResult', fn ($result) => $result->grade_counts === $counts);
})->with([
    ['SPM', 'spm', ['A+' => 1, 'A-' => 2, 'G' => 3]],
    ['IGCSE', 'igcse_letters', ['A*' => 2, 'C' => 3, 'G' => 1, 'U' => 1]],
    ['IGCSE', 'igcse_9_1', ['9' => 1, '7' => 2, '4' => 2, '1' => 1, 'U' => 1]],
    ['O-Level', 'o_level', ['A*' => 2, 'A' => 2, 'E' => 1, 'U' => 1]],
    ['A-Level', 'a_level', ['A*' => 1, 'A' => 1, 'B' => 1]],
    ['UEC', 'uec', ['A1' => 2, 'A2' => 1, 'B3' => 1, 'B6' => 1, 'C7' => 1, 'F9' => 1]],
]);

test('SPM totals are derived from grades and supplied aggregate values cannot override them', function () {
    $user = User::factory()->create(['role' => 'student']);
    $payload = gradeCountPayload() + ['spm_as' => 12, 'spm_credits' => 12, 'cgpa' => 4, 'year_of_study' => 4, 'stam_rank' => 'Mumtaz'];
    $this->actingAs($user)->patch(route('student.academic-result.update'), $payload)->assertSessionHasNoErrors();
    $result = $user->fresh()->academicResult;
    expect($result->spm_as)->toBe(4)->and($result->spm_credits)->toBe(7)
        ->and($result->cgpa)->toBeNull()->and($result->year_of_study)->toBeNull()->and($result->stam_rank)->toBeNull();
});

test('official STAM ranks save without demanding a CGPA or grade counts', function ($rank) {
    $user = User::factory()->create(['role' => 'student']);
    $payload = gradeCountPayload('STAM', null, []) + ['stam_rank' => $rank, 'cgpa' => 4];
    $this->actingAs($user)->patch(route('student.academic-result.update'), $payload)->assertSessionHasNoErrors();
    $result = $user->fresh()->academicResult;
    expect($result->stam_rank)->toBe($rank)->and($result->isReadyForRecommendations())->toBeTrue()
        ->and($result->grade_counts)->toBeNull()->and($result->grading_scale)->toBeNull()->and($result->cgpa)->toBeNull();
})->with(AcademicQualifications::RANKS);

test('invalid or mixed grade result submissions do not replace a saved record', function ($changes, $field) {
    $user = makeStudent();
    $this->actingAs($user)->patch(route('student.academic-result.update'), array_replace(gradeCountPayload(), $changes))
        ->assertSessionHasErrors($field);
    expect($user->fresh()->academicResult->education_level)->toBe('Undergraduate');
})->with([
    'negative count' => [['grade_counts' => ['A' => -1]], 'grade_counts'],
    'fraction' => [['grade_counts' => ['A' => 1.5]], 'grade_counts'],
    'boolean count' => [['grade_counts' => ['A' => true]], 'grade_counts'],
    'empty counts' => [['grade_counts' => []], 'grade_counts'],
    'all zero' => [['grade_counts' => ['A' => 0, 'G' => 0]], 'grade_counts'],
    'nested count' => [['grade_counts' => ['A' => ['invalid']]], 'grade_counts'],
    'too many total subjects' => [['grade_counts' => ['A' => 7, 'B' => 6]], 'grade_counts'],
    'unknown grade' => [['grade_counts' => ['A**' => 1]], 'grade_counts'],
    'wrong category' => [['education_category' => 'undergraduate'], 'education_category'],
    'unsupported category' => [['education_category' => 'postgraduate'], 'education_category'],
    'unsupported qualification' => [['education_level' => 'AS-Level'], 'education_level'],
    'qualification array' => [['education_level' => ['SPM']], 'education_level'],
    'scale array' => [['grading_scale' => ['spm']], 'grading_scale'],
    'wrong scale' => [['grading_scale' => 'uec'], 'grading_scale'],
    'pending results' => [['result_status' => 'pending'], 'result_status'],
    'forecast results' => [['result_status' => 'forecast'], 'result_status'],
    'exam still studying' => [['study_status' => 'studying'], 'study_status'],
    'missing IGCSE scale' => [['education_category' => 'secondary', 'education_level' => 'IGCSE', 'grading_scale' => null], 'grading_scale'],
    'mixed IGCSE scales' => [['education_level' => 'IGCSE', 'grading_scale' => 'igcse_9_1', 'grade_counts' => ['9' => 2, 'A*' => 1]], 'grade_counts'],
    'missing STAM rank' => [['education_category' => 'pathway', 'education_level' => 'STAM'], 'stam_rank'],
    'unsupported STAM rank' => [['education_category' => 'pathway', 'education_level' => 'STAM', 'stam_rank' => 'A'], 'stam_rank'],
]);

test('switching to CGPA clears previous grade data while retaining a separate funding choice', function () {
    $user = makeStudent([], ['education_level' => 'UEC', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => null, 'grading_scale' => 'uec', 'grade_counts' => ['A1' => 5]]);
    $this->actingAs($user)->patch(route('student.academic-result.update'), [
        'education_category' => 'pathway', 'education_level' => 'Diploma', 'study_status' => 'studying', 'year_of_study' => 1,
        'cgpa' => 3.4, 'grading_scale' => 'uec', 'grade_counts' => ['A1' => 5], 'stam_rank' => 'Mumtaz',
        'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University',
        'field_of_study' => 'Engineering', 'result_status' => 'official',
    ])->assertSessionHasNoErrors();
    $academic = $user->fresh()->academicResult;
    expect($academic->grade_counts)->toBeNull()->and($academic->grading_scale)->toBeNull()->and($academic->stam_rank)->toBeNull()
        ->and($academic->cgpa)->toBe('3.40')->and($academic->funding_study_level)->toBe('Undergraduate')
        ->and(AcademicResult::where('user_id', $user->id)->count())->toBe(1);
});

test('legacy SPM totals remain visible and require an actual breakdown before matching', function () {
    $user = makeStudent([], ['education_level' => 'SPM', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => null, 'spm_as' => 8, 'spm_credits' => 9, 'grade_counts' => null]);
    $this->actingAs($user)->get(route('student.academic-result.edit'))->assertOk()->assertSee('previous SPM totals are preserved')
        ->assertViewHas('academicResult', fn ($academic) => $academic->grade_counts === null && $academic->spm_as === 8);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user);
    expect($result['error'])->toContain('final grade breakdown')->and($result['recommendations'])->toBe([]);
});
