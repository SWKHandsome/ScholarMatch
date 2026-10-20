<?php

use App\Models\AcademicResult;
use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Database\Seeders\ScholarshipCatalog;
use Database\Seeders\ScholarshipCatalogSeeder;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 20));
    seedIncomeCategories();
    $this->seed(ScholarshipCatalogSeeder::class);
});

function demoDecision(string $key, array $profile = [], array $academic = []): array
{
    $student = makeStudent($profile, $academic);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($student);
    expect($result['error'])->toBeNull()->and($result['recommendations'])->toHaveCount(70);

    return collect($result['recommendations'])->firstWhere('scholarship_id', Scholarship::where('catalog_key', $key)->value('id'));
}

test('demo set covers the supported qualifications categories fields institutions and award types', function () {
    $entries = collect(ScholarshipCatalog::entries());
    expect($entries->pluck('rules.qualification_routes')->flatten(1)->pluck('education_level')->unique()->sort()->values()->all())
        ->toBe(collect(AcademicResult::EDUCATION_LEVELS)->sort()->values()->all());
    expect($entries->pluck('rules.required_income_category')->filter()->unique()->sort()->values()->all())->toBe(['B40', 'M40', 'T20']);
    expect($entries->pluck('rules.required_institution_type')->filter()->unique()->sort()->values()->all())
        ->toBe(collect(config('scholarship.institution_types'))->sort()->values()->all());
    expect($entries->pluck('rules.required_fields_of_study')->flatten()->unique()->sort()->values()->all())
        ->toBe(collect(config('scholarship.fields_of_study'))->sort()->values()->all());
    expect($entries->pluck('award_type')->unique())->toHaveCount(6);
    expect($entries->pluck('rules.qualification_routes')->flatten(1)->pluck('study_status')->unique()->sort()->values()->all())
        ->toBe(['any', 'completed', 'studying']);
});

test('SPM demo checks both the A count and credit count', function ($as, $credits, $status, $failure) {
    $rec = demoDecision('demo-15', [], [
        'education_level' => 'SPM', 'study_status' => 'completed', 'year_of_study' => null,
        'spm_as' => $as, 'spm_credits' => $credits, 'cgpa' => null,
        'funding_study_level' => 'Foundation', 'institution_type' => 'Foundation College',
    ]);
    expect($rec['status'])->toBe($status);
    if ($failure) {
        expect($rec['failed_requirements'])->toContain($failure);
    }
})->with([[5, 7, 'Not Eligible', 'min_spm_credits'], [4, 8, 'Not Eligible', 'min_spm_as'], [5, 8, 'Eligible', null]]);

test('demo progression thresholds depend on qualification', function ($level, $cgpa, $status) {
    $rec = demoDecision('demo-28', [], ['education_level' => $level, 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => $cgpa]);
    expect($rec['status'])->toBe($status);
})->with([['Foundation', 3.49, 'Not Eligible'], ['Foundation', 3.5, 'Eligible'], ['Diploma', 3, 'Eligible'], ['Diploma', 2.99, 'Not Eligible']]);

test('demo alternatives require one complete route and do not mix conditions', function ($level, $status, $year, $cgpa, $decision) {
    $rec = demoDecision('demo-26', [], ['education_level' => $level, 'study_status' => $status, 'year_of_study' => $year, 'cgpa' => $cgpa]);
    expect($rec['status'])->toBe($decision);
})->with([
    ['Diploma', 'completed', null, 3, 'Eligible'],
    ['Undergraduate', 'studying', 3, 3.25, 'Eligible'],
    ['Undergraduate', 'studying', 3, 3.24, 'Not Eligible'],
    ['Undergraduate', 'studying', 4, 3.25, 'Not Eligible'],
    ['Undergraduate', 'completed', null, 4, 'Not Eligible'],
    ['Foundation', 'completed', null, 4, 'Not Eligible'],
]);

test('demo current year boundaries are inclusive', function ($year, $status) {
    $rec = demoDecision('demo-22', [], ['year_of_study' => $year, 'cgpa' => 3]);
    expect($rec['status'])->toBe($status);
})->with([[1, 'Not Eligible'], [2, 'Eligible'], [3, 'Eligible'], [4, 'Not Eligible']]);

test('demo household income ceilings include the exact limit', function ($income, $status) {
    $rec = demoDecision('demo-09', ['household_income' => $income]);
    expect($rec['status'])->toBe($status);
})->with([[7000, 'Eligible'], [7000.01, 'Not Eligible']]);

test('demo income categories remain separate from a maximum-income restriction', function ($income, $key, $cgpa, $status) {
    $rec = demoDecision($key, ['household_income' => $income], ['education_level' => 'Foundation', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => $cgpa]);
    expect($rec['status'])->toBe($status);
})->with([
    [3169, 'demo-05', 3, 'Eligible'], [3170, 'demo-05', 3, 'Not Eligible'],
    [3170, 'demo-07', 3, 'Eligible'], [6339, 'demo-07', 3, 'Eligible'], [6340, 'demo-07', 3, 'Not Eligible'],
    [6340, 'demo-08', 3.75, 'Eligible'],
]);

test('demo multinational and multi-field restrictions give precise failures', function ($key, $profile, $academic, $status, $failure) {
    $rec = demoDecision($key, $profile, $academic);
    expect($rec['status'])->toBe($status);
    if ($failure) {
        expect($rec['failed_requirements'])->toContain($failure);
    }
})->with([
    ['demo-03', ['nationality' => 'Non-Malaysian'], [], 'Not Eligible', 'nationality'],
    ['demo-04', ['nationality' => 'Non-Malaysian'], ['institution_type' => 'Private University'], 'Eligible', null],
    ['demo-35', [], ['field_of_study' => 'Computer Science'], 'Eligible', null],
    ['demo-35', [], ['field_of_study' => 'Engineering'], 'Eligible', null],
    ['demo-35', [], ['field_of_study' => 'Business'], 'Not Eligible', 'field_of_study'],
    ['demo-29', [], ['institution_type' => 'Private University'], 'Not Eligible', 'institution_type'],
    ['demo-17', [], ['education_level' => 'STPM', 'cgpa' => 3, 'year_of_study' => 1, 'funding_study_level' => 'Undergraduate'], 'Not Eligible', 'funding_level'],
]);

test('combined demo distinguishes eligible verification and independent failed requirements', function ($profile, $academic, $status, $failure) {
    $rec = demoDecision('demo-50', array_merge(['household_income' => 3000], $profile), array_merge(['cgpa' => 3.7], $academic));
    expect($rec['status'])->toBe($status)->and($rec['requires_verification'])->toBe($status === 'Eligible');
    if ($failure) {
        expect($rec['failed_requirements'])->toContain($failure);
    }
})->with([
    [[], [], 'Eligible', null], [[], ['cgpa' => 3.69], 'Not Eligible', 'min_cgpa'],
    [['household_income' => 3001], [], 'Not Eligible', 'max_household_income'],
    [[], ['year_of_study' => 4], 'Not Eligible', 'max_year_of_study'],
]);

test('demo pages display fiction and application buttons for presentations without verified sources', function () {
    $student = makeStudent();
    $ordinary = Scholarship::where('catalog_key', 'demo-01')->firstOrFail();
    $interview = Scholarship::where('catalog_key', 'demo-02')->firstOrFail();
    $recommendations = collect(app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations']);
    expect($recommendations->firstWhere('scholarship_id', $ordinary->id)['requires_verification'])->toBeFalse()
        ->and($recommendations->firstWhere('scholarship_id', $interview->id)['requires_verification'])->toBeTrue();
    $this->actingAs($student)->get(route('student.recommendations.show', $ordinary))->assertOk()
        ->assertSee('Fictional demo scholarship')->assertSee('Apply Now')->assertDontSee('Official scholarship source')
        ->assertSee('href="'.$ordinary->application_link.'"', false);
    $this->get(route('student.recommendations', ['search' => 'Demo 01:']))->assertOk()->assertSee('Apply Now')
        ->assertSee('href="'.$ordinary->application_link.'"', false);
    $student->savedScholarships()->create(['scholarship_id' => $ordinary->id]);
    $this->get(route('student.saved-scholarships.index'))->assertOk()->assertSee('Apply')
        ->assertSee('href="'.$ordinary->application_link.'"', false);
});

test('fictional catalogue cannot claim official source verification', function () {
    $entries = ScholarshipCatalog::entries();
    $entries[0]['source_url'] = 'https://example.com/official';
    expect(fn () => ScholarshipCatalog::validateReplacement($entries, '2026-10-20'))->toThrow(InvalidArgumentException::class);
});
