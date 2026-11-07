<?php

use App\Models\User;
use App\Services\AcademicRouteEvaluator;
use App\Services\ScholarshipRecommendationService;
use App\Support\AcademicQualifications;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => seedIncomeCategories());

function subjectRouteDecision(array $routes, array $counts, float $cgpa = 3.00): array
{
    $student = makeStudent([], ['education_level' => 'STPM', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => $cgpa, 'grading_scale' => 'stpm', 'grade_counts' => $counts]);

    return app(AcademicRouteEvaluator::class)->evaluate($routes, $student->academicResult);
}

test('STPM saves subject counts with calculated completed CGPA or manual studying CGPA', function ($status, $year) {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->patch(route('student.academic-result.update'), [
        'education_level' => 'STPM', 'education_category' => 'pathway', 'study_status' => $status,
        'year_of_study' => $year, 'grade_counts' => ['A' => 1, 'B' => 2, 'C' => 1], 'cgpa' => 3.00,
        'stpm_pa_grade' => $status === 'completed' ? 'A' : null, 'stpm_cgpa_confirmed' => $status === 'completed' ? '1' : null,
        'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University', 'field_of_study' => 'Engineering', 'result_status' => 'official',
    ])->assertSessionHasNoErrors();
    $academic = $student->fresh()->academicResult;
    expect($academic->grade_counts)->toBe(['A' => 1, 'B' => 2, 'C' => 1])->and($academic->cgpa)->toBe('3.00')
        ->and($academic->grading_scale)->toBe('stpm')->and($academic->year_of_study)->toBe($year)->and($academic->isReadyForRecommendations())->toBeTrue();
    $this->get(route('student.academic-result.edit'))->assertOk()->assertSee('printed on your official results');
})->with([['completed', null], ['studying', 2]]);

test('legacy STPM CGPA is preserved while actual subject counts are requested', function () {
    $student = makeStudent([], ['education_level' => 'STPM', 'grade_counts' => null, 'cgpa' => 3.5]);
    $this->actingAs($student)->get(route('student.academic-result.edit'))->assertOk()->assertSee('official STPM CGPA is preserved');
    expect($student->academicResult->isReadyForRecommendations())->toBeFalse();
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($student)['error'])->toContain('STPM requires both');
    expect($student->fresh()->academicResult->cgpa)->toBe('3.50');
});

test('STPM one A distinguishes strict A from A minus accepted by a provider', function ($threshold, $expected) {
    $checks = subjectRouteDecision([['education_level' => 'STPM', 'study_status' => 'completed',
        'grade_requirements' => [['grade' => $threshold, 'min_subjects' => 1]]]], ['A-' => 1, 'B' => 3]);
    expect($checks[0]['outcome'])->toBe($expected);
})->with([['A', 'not_met'], ['A-', 'met']]);

test('STPM best three averages differ from three subjects each at B and from overall CGPA', function () {
    $average = ['education_level' => 'STPM', 'study_status' => 'completed',
        'stpm_average' => ['subjects' => 3, 'minimum_grade' => 'F', 'min_average' => 3.00]];
    $each = ['education_level' => 'STPM', 'study_status' => 'completed', 'grade_requirements' => [['grade' => 'B', 'min_subjects' => 3]]];
    $counts = ['A' => 1, 'B' => 1, 'C' => 2];
    $checks = subjectRouteDecision([$average], $counts, 2.75);
    expect($checks[0]['outcome'])->toBe('met');
    expect(collect($checks)->firstWhere('key', 'min_stpm_average')['actual'])->toBe('3.0000');
    expect(subjectRouteDecision([$each], $counts, 2.75)[0]['outcome'])->toBe('not_met');
    expect(subjectRouteDecision([array_merge($average, ['min_cgpa' => 3.00])], $counts, 2.75)[0]['outcome'])->toBe('not_met');
    expect(subjectRouteDecision([$each, $average], $counts, 2.75)[0]['outcome'])->toBe('met');
});

test('STPM average uses actual best subject points without rounding into a pass', function ($counts, $minimum, $grade, $outcome) {
    $checks = subjectRouteDecision([['education_level' => 'STPM', 'study_status' => 'completed',
        'stpm_average' => ['subjects' => 3, 'minimum_grade' => $grade, 'min_average' => $minimum]]], $counts);
    expect($checks[0]['outcome'])->toBe($outcome);
})->with([
    [['B' => 3, 'F' => 1], 3.00, 'F', 'met'],
    [['B' => 2, 'B-' => 1, 'F' => 1], 3.00, 'F', 'not_met'],
    [['A-' => 3, 'F' => 1], 3.67, 'F', 'met'],
    [['A-' => 3, 'F' => 1], 3.68, 'F', 'not_met'],
    [['A' => 1, 'B' => 1, 'C' => 2], 3.00, 'B', 'not_met'],
    [['A' => 2], 0.00, 'F', 'not_met'],
]);

test('UEC total eleven is compared directly across exactly the best five qualifying subjects', function ($counts, $outcome) {
    $student = makeStudent([], ['education_level' => 'UEC', 'study_status' => 'completed', 'year_of_study' => null,
        'cgpa' => null, 'grading_scale' => 'uec', 'grade_counts' => $counts]);
    $checks = app(AcademicRouteEvaluator::class)->evaluate([['education_level' => 'UEC', 'study_status' => 'completed',
        'uec_total' => ['subjects' => 5, 'minimum_grade' => 'B6', 'max_total' => 11]]], $student->academicResult);
    expect($checks[0]['outcome'])->toBe($outcome);
    expect(collect($checks)->firstWhere('key', 'max_uec_total'))->not->toBeNull();
})->with([
    [['A1' => 2, 'A2' => 1, 'B3' => 1, 'B4' => 1, 'F9' => 2], 'met'],
    [['A1' => 1, 'A2' => 1, 'B3' => 3], 'not_met'],
    [['A1' => 5, 'B6' => 1], 'met'],
    [['A1' => 4, 'C7' => 1], 'not_met'],
]);

test('point rules persist through admin forms and appear on the rule page', function () {
    $award = makeScholarship();
    $routes = [
        ['education_level' => 'STPM', 'study_status' => 'completed', 'min_cgpa' => 3.0,
            'grade_requirements' => [['grade' => 'A-', 'min_subjects' => 1]],
            'stpm_average' => ['subjects' => 3, 'minimum_grade' => 'F', 'min_average' => 3.0]],
        ['education_level' => 'UEC', 'study_status' => 'completed',
            'uec_total' => ['subjects' => 5, 'minimum_grade' => 'F9', 'max_total' => 11]],
    ];
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$award, $award->rule]), ['qualification_routes' => $routes])->assertSessionHasNoErrors();
    $stored = $award->fresh()->rule->academicRoutes();
    expect($stored[0]['stpm_average'])->toBe(['subjects' => 3, 'min_average' => 3, 'minimum_grade' => 'F'])
        ->and($stored[1]['uec_total'])->toBe(['subjects' => 5, 'max_total' => 11, 'minimum_grade' => 'F9']);
    $this->get(route('admin.scholarships.rules.index', $award))->assertOk()->assertSee('STPM Best-subject Average')->assertSee('UEC Best-subject Total');
    $this->get(route('admin.scholarships.edit', $award))->assertOk()->assertSee('Requirement Type')->assertSee('Add Another Requirement');
    $this->put(route('admin.scholarships.rules.update', [$award, $award->fresh()->rule]), ['qualification_routes' => [['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3]]])->assertSessionHasNoErrors();
    $route = $award->fresh()->rule->qualificationRoutes->first();
    expect($route->stpm_average)->toBeNull()->and($route->uec_total)->toBeNull()->and($route->grade_requirements)->toBeNull();
});

test('invalid point rules cannot save', function ($level, $field, $settings, $error) {
    $award = makeScholarship();
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$award, $award->rule]), [
        'qualification_routes' => [['education_level' => $level, 'study_status' => 'completed', $field => $settings]],
    ])->assertSessionHasErrors('qualification_routes.0.'.$error);
})->with([
    ['STPM', 'stpm_average', ['subjects' => 6, 'minimum_grade' => 'F', 'min_average' => 3], 'stpm_average.subjects'],
    ['STPM', 'stpm_average', ['subjects' => 3, 'minimum_grade' => 'F', 'min_average' => 4.01], 'stpm_average.min_average'],
    ['STPM', 'stpm_average', ['subjects' => 3, 'minimum_grade' => 'F', 'min_average' => 3.333], 'stpm_average.min_average'],
    ['STPM', 'stpm_average', ['subjects' => 3, 'min_average' => 3], 'stpm_average.minimum_grade'],
    ['STPM', 'grade_requirements', [['grade' => 'A*', 'min_subjects' => 1]], 'grade_requirements.0.grade'],
    ['STPM', 'grade_requirements', [['grade' => 'A', 'min_subjects' => 6]], 'grade_requirements.0.min_subjects'],
    ['UEC', 'uec_total', ['subjects' => 5, 'minimum_grade' => 'F9', 'max_total' => 11.5], 'uec_total.max_total'],
    ['UEC', 'uec_total', ['subjects' => 5, 'minimum_grade' => 'F9', 'max_total' => 4], 'uec_total.max_total'],
    ['UEC', 'uec_total', ['subjects' => 5, 'minimum_grade' => 'F9', 'max_total' => 46], 'uec_total.max_total'],
    ['UEC', 'uec_total', ['subjects' => 5, 'minimum_grade' => 'A', 'max_total' => 11], 'uec_total.minimum_grade'],
    ['A-Level', 'stpm_average', ['subjects' => 3, 'minimum_grade' => 'F', 'min_average' => 3], 'stpm_average'],
    ['STPM', 'uec_total', ['subjects' => 5, 'minimum_grade' => 'F9', 'max_total' => 11], 'uec_total'],
]);

test('point migration preserves old routes and prevents lossy rollback', function () {
    $migration = require database_path('migrations/2026_10_31_010000_add_subject_point_requirements.php');
    $migration->down();
    $award = makeScholarship();
    $route = $award->rule->qualificationRoutes()->create(['education_level' => 'UEC', 'study_status' => 'completed',
        'uec_average' => ['subjects' => 5, 'minimum_grade' => 'B6', 'max_average' => 4]]);
    $before = (array) DB::table('scholarship_qualification_routes')->find($route->id);
    $migration->up();
    expect((array) DB::table('scholarship_qualification_routes')->find($route->id))->toMatchArray($before);
    $route->refresh()->update(['uec_total' => ['subjects' => 5, 'minimum_grade' => 'B6', 'max_total' => 11]]);
    expect(fn () => $migration->down())->toThrow(RuntimeException::class, 'Preserve STPM average');
    expect($route->fresh()->uec_total['max_total'])->toBe(11);
});

test('official STPM points use all supported grade definitions', function () {
    expect(AcademicQualifications::stpmPoints())->toBe(['A' => 4.0, 'A-' => 3.67, 'B+' => 3.33, 'B' => 3.0,
        'B-' => 2.67, 'C+' => 2.33, 'C' => 2.0, 'C-' => 1.67, 'D+' => 1.33, 'D' => 1.0, 'F' => 0.0]);
});
