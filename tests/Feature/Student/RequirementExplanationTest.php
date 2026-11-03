<?php

use App\Services\ScholarshipRecommendationService;
use App\Support\StudentRequirementExplanation;

beforeEach(fn () => seedIncomeCategories());

function detailExplanationFixture(array $counts = ['A' => 3], array $profile = []): array
{
    $student = makeStudent($profile, ['education_level' => 'A-Level', 'study_status' => 'completed', 'year_of_study' => null,
        'grading_scale' => 'a_level', 'grade_counts' => $counts, 'cgpa' => null]);
    $award = makeScholarship([], ['required_nationality' => null, 'required_income_category' => null,
        'max_household_income' => null, 'required_field_of_study' => null, 'required_institution_type' => null]);
    $award->rule->qualificationRoutes()->createMany([
        ['education_level' => 'UEC', 'study_status' => 'completed', 'grading_scale' => 'uec', 'grade_requirements' => [['grade' => 'A2', 'min_subjects' => 2]]],
        ['education_level' => 'A-Level', 'study_status' => 'completed', 'grading_scale' => 'a_level', 'grade_requirements' => [['grade' => 'A', 'min_subjects' => 1], ['grade' => 'B', 'min_subjects' => 3]]],
    ]);

    return [$student, $award];
}

test('student explanation uses the evaluator selected route without treating failed alternatives as rejection', function () {
    [$student, $award] = detailExplanationFixture();
    $decision = app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations'][0];
    $display = StudentRequirementExplanation::prepare($decision['requirement_results']);
    expect($decision['status'])->toBe('Eligible');
    expect($display['failed'])->toBeEmpty();
    expect($display['selected']['label'])->toContain('A-Level');
    expect($display['alternatives'])->toHaveCount(1);
    expect($display['alternatives'][0]['passed'])->toBeFalse();
    expect(array_column($display['academic'], 'key'))->toBe(['qualification', 'min_grade_count_0', 'min_grade_count_1']);
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Your accepted qualification route:')->assertSee('View other accepted qualification routes (1)')
        ->assertDontSee('Not Eligible - Failed Requirements')->assertSee('View criteria with no restrictions');
});

test('failed academic and general checks appear first without hiding applicable restrictions', function () {
    [$student, $award] = detailExplanationFixture(['B' => 3]);
    $award->rule->update(['required_nationality' => 'Non-Malaysian']);
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSeeInOrder(['Not Eligible - Failed Requirements', 'Nationality', 'At least 1 subject at A or above', 'Academic requirements'])
        ->assertSee('Non-Malaysian')->assertSee('data-explanation-status="failed"', false)->assertDontSee('Apply Now');
});

test('unconfirmed dates and escaped special conditions are provider checks rather than unrestricted criteria', function () {
    [$student, $award] = detailExplanationFixture();
    $award->update(['deadline' => null, 'deadline_type' => 'unconfirmed', 'special_requirements' => '<script>verify()</script>']);
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Check with the provider')->assertSee('Confirm the application deadline')->assertSee('Special conditions')
        ->assertSee('&lt;script&gt;verify()&lt;/script&gt;', false)->assertDontSee('<script>verify()', false);
});

test('rolling application timing remains visible with special conditions without a false deadline warning', function () {
    [$student, $award] = detailExplanationFixture();
    $award->update(['deadline' => null, 'deadline_type' => 'rolling', 'special_requirements' => 'Interview', 'application_notes' => 'Apply throughout the year.']);
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Application timing:')->assertSee('Rolling applications')->assertSee('Apply throughout the year.')
        ->assertSee('Interview')->assertDontSee('Confirm the application deadline');
});

test('comparison formatting preserves zero values and clearly marks income currency', function () {
    $row = StudentRequirementExplanation::row(['key' => 'max_household_income', 'label' => 'Maximum household income', 'required' => 0, 'actual' => 0, 'outcome' => 'met']);
    expect($row['required'])->toBe('RM 0.00');
    expect($row['actual'])->toBe('RM 0.00');
});
