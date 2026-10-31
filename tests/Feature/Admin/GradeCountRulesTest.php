<?php

use App\Services\ScholarshipRecommendationService;

beforeEach(fn () => seedIncomeCategories());

function saveGradeRoutes(array $routes, array $scholarshipAttributes = [])
{
    $scholarship = makeScholarship($scholarshipAttributes);
    test()->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$scholarship, $scholarship->rule]), [
        'required_study_level' => 'Undergraduate', 'qualification_routes' => $routes,
    ])->assertSessionHasNoErrors();

    return $scholarship->fresh(['rule.qualificationRoutes']);
}

function gradeDecision($scholarship, string $level, ?string $scale, array $counts = [], ?string $rank = null): array
{
    $user = makeStudent([], ['education_level' => $level, 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => null, 'grading_scale' => $scale, 'grade_counts' => $counts ?: null, 'stam_rank' => $rank]);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user);
    expect($result['error'])->toBeNull();

    return collect($result['recommendations'])->firstWhere('scholarship_id', $scholarship->id);
}

test('AAB checks inclusive A star grades with AND inside the box and ignores unrelated alternatives', function ($counts, $expected) {
    $scholarship = saveGradeRoutes([
        ['education_level' => 'A-Level', 'study_status' => 'completed', 'grade_requirements' => [
            ['grade' => 'A', 'min_subjects' => 2], ['grade' => 'B', 'min_subjects' => 3],
        ]],
        ['education_level' => 'UEC', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'A1', 'min_subjects' => 5]]],
    ]);
    $decision = gradeDecision($scholarship, 'A-Level', 'a_level', $counts);
    expect($decision['status'])->toBe($expected);
})->with([
    'A star AB meets AAB' => [['A*' => 1, 'A' => 1, 'B' => 1], 'Eligible'],
    'ABB fails first condition' => [['A' => 1, 'B' => 2], 'Not Eligible'],
    'AAC fails third subject threshold' => [['A' => 2, 'C' => 1], 'Not Eligible'],
    'extra failing subject does not undo best qualifying grades' => [['A' => 2, 'B' => 1, 'U' => 1], 'Eligible'],
]);

test('IGCSE scales match exactly or use a separate alternative box', function () {
    $scholarship = saveGradeRoutes([
        ['education_level' => 'IGCSE', 'study_status' => 'completed', 'grading_scale' => 'igcse_letters', 'grade_requirements' => [['grade' => 'C', 'min_subjects' => 5]]],
    ]);
    expect(gradeDecision($scholarship, 'IGCSE', 'igcse_9_1', ['9' => 5])['status'])->toBe('Not Eligible');
    $scholarship->rule->qualificationRoutes()->create([
        'education_level' => 'IGCSE', 'study_status' => 'completed', 'grading_scale' => 'igcse_9_1',
        'grade_requirements' => [['grade' => '6', 'min_subjects' => 5]],
    ]);
    expect(gradeDecision($scholarship, 'IGCSE', 'igcse_9_1', ['9' => 2, '6' => 3])['status'])->toBe('Eligible');
});

test('an IGCSE route without grade restrictions accepts either scale', function () {
    $scholarship = saveGradeRoutes([['education_level' => 'IGCSE', 'study_status' => 'completed']]);
    expect(gradeDecision($scholarship, 'IGCSE', 'igcse_letters', ['C' => 5])['status'])->toBe('Eligible');
    expect(gradeDecision($scholarship, 'IGCSE', 'igcse_9_1', ['5' => 5])['status'])->toBe('Eligible');
});

test('UEC thresholds distinguish A1 from A2 and compare grade order correctly', function ($threshold, $counts, $expected) {
    $scholarship = saveGradeRoutes([['education_level' => 'UEC', 'study_status' => 'completed',
        'grade_requirements' => [['grade' => $threshold, 'min_subjects' => 5]]]]);
    expect(gradeDecision($scholarship, 'UEC', 'uec', $counts)['status'])->toBe($expected);
})->with([
    ['A1', ['A2' => 5], 'Not Eligible'],
    ['A2', ['A1' => 2, 'A2' => 3], 'Eligible'],
    ['B6', ['A1' => 1, 'B3' => 2, 'B6' => 2, 'C7' => 2], 'Eligible'],
    ['B6', ['A1' => 1, 'B6' => 3, 'C7' => 1], 'Not Eligible'],
]);

test('UEC best subject averages use only enough eligible subjects and lower averages pass', function ($counts, $maximum, $expected) {
    $scholarship = saveGradeRoutes([['education_level' => 'UEC', 'study_status' => 'completed',
        'uec_average' => ['subjects' => 5, 'minimum_grade' => 'B6', 'max_average' => $maximum]]]);
    $result = gradeDecision($scholarship, 'UEC', 'uec', $counts);
    expect($result['status'])->toBe($expected);
    expect(collect($result['requirement_results'])->firstWhere('key', 'max_uec_average'))->not->toBeNull();
})->with([
    'threshold equality and ignore lower extra grades' => [['A1' => 1, 'A2' => 1, 'B3' => 1, 'B4' => 1, 'B6' => 1, 'C7' => 2], 3.2, 'Eligible'],
    'threshold not rounded into a pass' => [['A1' => 1, 'A2' => 1, 'B3' => 1, 'B4' => 1, 'B6' => 1], 3.19, 'Not Eligible'],
    'insufficient eligible subjects' => [['A1' => 2, 'B6' => 2, 'C7' => 2], 6, 'Not Eligible'],
    'best five selected from stronger extra subjects' => [['A1' => 6, 'B6' => 2], 1, 'Eligible'],
]);

test('STAM minimum pangkat uses the correct rank direction', function ($rank, $expected) {
    $scholarship = saveGradeRoutes([['education_level' => 'STAM', 'study_status' => 'completed', 'min_stam_rank' => 'Jayyid']]);
    expect(gradeDecision($scholarship, 'STAM', null, [], $rank)['status'])->toBe($expected);
})->with([
    ['Mumtaz', 'Eligible'], ['Jayyid Jiddan', 'Eligible'], ['Jayyid', 'Eligible'], ['Maqbul', 'Not Eligible'], ['Rasib', 'Not Eligible'],
]);

test('subject conditions stay in special requirements and never appear as an automatic subject check', function () {
    $scholarship = saveGradeRoutes([['education_level' => 'O-Level', 'study_status' => 'completed',
        'grade_requirements' => [['grade' => 'C', 'min_subjects' => 5]]]], ['special_requirements' => 'Mathematics at least C; verify with the provider.']);
    $eligible = gradeDecision($scholarship, 'O-Level', 'o_level', ['B' => 5]);
    expect($eligible['status'])->toBe('Eligible')->and($eligible['requires_verification'])->toBeTrue()
        ->and(array_column($eligible['requirement_results'], 'key'))->not->toContain('Mathematics');
    $failed = gradeDecision($scholarship, 'O-Level', 'o_level', ['B' => 4, 'D' => 1]);
    expect($failed['status'])->toBe('Not Eligible')->and($failed['requires_verification'])->toBeFalse();
});

test('invalid qualification-specific rules are rejected', function ($route, $field) {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$scholarship, $scholarship->rule]), ['qualification_routes' => [$route]])
        ->assertSessionHasErrors('qualification_routes.0.'.$field);
})->with([
    [['education_level' => 'A-Level', 'study_status' => 'studying'], 'study_status'],
    [['education_level' => 'UEC', 'study_status' => 'any'], 'study_status'],
    [['education_level' => 'STAM', 'study_status' => 'completed', 'min_stam_rank' => 'A'], 'min_stam_rank'],
    [['education_level' => 'IGCSE', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 2]]], 'grading_scale'],
    [['education_level' => 'UEC', 'study_status' => 'completed', 'grading_scale' => 'spm'], 'grading_scale'],
    [['education_level' => 'IGCSE', 'study_status' => 'completed', 'grading_scale' => 'igcse_9_1', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 2]]], 'grade_requirements.0.grade'],
    [['education_level' => 'SPM', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 13]]], 'grade_requirements.0.min_subjects'],
    [['education_level' => 'A-Level', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 2], ['grade' => 'A', 'min_subjects' => 3]]], 'grade_requirements.1.grade'],
    [['education_level' => 'UEC', 'study_status' => 'completed', 'uec_average' => ['subjects' => 5, 'minimum_grade' => 'B6']], 'uec_average.max_average'],
    [['education_level' => 'UEC', 'study_status' => 'completed', 'uec_average' => ['subjects' => 5, 'minimum_grade' => 'B6', 'max_average' => 3.333]], 'uec_average.max_average'],
]);

test('new rules clear irrelevant fields and are rendered on rule and student detail pages', function () {
    $scholarship = saveGradeRoutes([['education_level' => 'STAM', 'study_status' => 'completed', 'min_stam_rank' => 'Jayyid',
        'min_cgpa' => 4, 'min_spm_as' => 12, 'grading_scale' => 'uec', 'min_year_of_study' => 4,
        'grade_requirements' => [['grade' => 'A1', 'min_subjects' => 2]]]]);
    $route = $scholarship->rule->qualificationRoutes->first();
    expect($route->min_cgpa)->toBeNull()->and($route->min_spm_as)->toBeNull()->and($route->grading_scale)->toBeNull()
        ->and($route->min_year_of_study)->toBeNull()->and($route->grade_requirements)->toBeNull();
    $this->get(route('admin.scholarships.rules.index', $scholarship))->assertOk()->assertSee('Minimum STAM Pangkat')->assertSee('Jayyid');
    $student = makeStudent([], ['education_level' => 'STAM', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => null, 'stam_rank' => 'Mumtaz']);
    $this->actingAs($student)->get(route('student.recommendations.show', $scholarship))->assertOk()->assertSee('STAM overall pangkat')->assertSee('Mumtaz');
});
