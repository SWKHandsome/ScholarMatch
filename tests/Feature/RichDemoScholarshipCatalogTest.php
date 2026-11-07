<?php

use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use App\Services\ScholarshipRuleEditor;
use App\Support\AcademicQualifications;
use Database\Seeders\ScholarshipCatalog;
use Database\Seeders\ScholarshipCatalogSeeder;
use Illuminate\Http\Request;

beforeEach(function () {
    seedIncomeCategories();
    $this->seed(ScholarshipCatalogSeeder::class);
});

function richApplicant(int $number): array
{
    $completed = ['study_status' => 'completed', 'year_of_study' => null];
    $private = ['institution_type' => 'Private University'];
    $cases = [
        1 => [[], $private + $completed + ['education_level' => 'Diploma', 'cgpa' => 3.25], true],
        2 => [[], $private + $completed + ['education_level' => 'SPM', 'spm_as' => 5, 'spm_credits' => 5, 'funding_study_level' => 'Diploma', 'field_of_study' => 'Business'], true],
        3 => [['household_income' => 4000], ['cgpa' => 3.4], true],
        4 => [[], $private + $completed + ['education_level' => 'Foundation', 'cgpa' => 3.75], false],
        5 => [[], $completed + ['education_level' => 'SPM', 'spm_as' => 6, 'spm_credits' => 6, 'funding_study_level' => 'Foundation', 'institution_type' => 'Foundation College', 'field_of_study' => 'Science'], false],
        6 => [[], $completed + ['education_level' => 'Diploma', 'cgpa' => 3.2], true],
        7 => [['nationality' => 'Non-Malaysian'], $private + $completed + ['education_level' => 'Diploma', 'cgpa' => 3.3, 'field_of_study' => 'Business'], false],
        8 => [[], ['education_level' => 'Diploma', 'cgpa' => 3.0, 'funding_study_level' => 'Diploma', 'institution_type' => 'Polytechnic'], false],
        9 => [[], ['education_level' => 'Diploma', 'cgpa' => 2.5, 'funding_study_level' => 'Diploma', 'institution_type' => 'Community College', 'field_of_study' => 'Business'], true],
        10 => [[], $completed + ['education_level' => 'Matriculation', 'cgpa' => 3.5, 'field_of_study' => 'Science'], false],
        11 => [[], $private + $completed + ['education_level' => 'UEC', 'grading_scale' => 'uec', 'grade_counts' => ['A1' => 2, 'A2' => 1, 'B3' => 1, 'B4' => 1]], true],
        12 => [[], $completed + ['education_level' => 'STAM', 'stam_rank' => 'Jayyid Jiddan', 'field_of_study' => 'Education'], true],
        13 => [[], $private + $completed + ['education_level' => 'Diploma', 'cgpa' => 2.9, 'field_of_study' => 'Arts & Design'], true],
        14 => [[], ['cgpa' => 3.7, 'field_of_study' => 'Medicine'], true],
        15 => [[], $private + ['cgpa' => 3.4, 'year_of_study' => 3, 'field_of_study' => 'Business'], false],
        16 => [[], $private + $completed + ['education_level' => 'SPM', 'spm_as' => 2, 'spm_credits' => 5, 'funding_study_level' => 'Diploma', 'field_of_study' => 'Hospitality & Tourism'], true],
        17 => [[], ['cgpa' => 3.1, 'institution_type' => 'Other', 'field_of_study' => 'Natural Sciences'], true],
        18 => [[], $private + ['education_level' => 'Diploma', 'cgpa' => 2.0, 'funding_study_level' => 'Diploma'], true],
        19 => [[], ['cgpa' => 3.5], true],
        20 => [[], $private + $completed + ['education_level' => 'Foundation', 'cgpa' => 3.75, 'field_of_study' => 'Law'], false],
    ];

    return $cases[$number];
}

function richDecision(int $number, array $profile = [], array $academic = []): array
{
    [$baseProfile, $baseAcademic] = richApplicant($number);
    $student = makeStudent(array_merge($baseProfile, $profile), array_merge($baseAcademic, $academic));
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($student);
    expect($result['error'])->toBeNull()->and($result['recommendations'])->toHaveCount(20);

    return collect($result['recommendations'])->firstWhere('scholarship_id', Scholarship::where('catalog_key', sprintf('demo-rich-%02d', $number))->value('id'));
}

test('every rich demo accepts its independently specified example applicant and checks funded level', function (int $number) {
    $decision = richDecision($number);
    expect($decision['status'])->toBe('Eligible')->and($decision['requires_verification'])->toBe(richApplicant($number)[2]);
    [, $academic] = richApplicant($number);
    $wrong = ($academic['funding_study_level'] ?? 'Undergraduate') === 'Undergraduate' ? 'Diploma' : 'Undergraduate';
    $decision = richDecision($number, [], ['funding_study_level' => $wrong]);
    expect($decision['status'])->toBe('Not Eligible')->and($decision['failed_requirements'])->toContain('funding_level');
})->with(range(1, 20));

test('rich rules are valid through the actual admin editor and cover all supported qualifications', function () {
    $entries = collect(ScholarshipCatalog::entries());
    foreach ($entries as $entry) {
        $data = $entry['rules'] + ['rules_submitted' => 1, 'qualification_routes_submitted' => 1];
        expect(app(ScholarshipRuleEditor::class)->validate(new Request($data)))->toBeArray();
        expect(count($entry['rules']['qualification_routes']))->toBeGreaterThanOrEqual(1);
    }
    expect($entries->pluck('rules.qualification_routes')->flatten(1)->pluck('education_level')->unique()->sort()->values()->all())
        ->toBe(collect(AcademicQualifications::LEVELS)->sort()->values()->all());
    expect($entries->pluck('award_type')->unique())->toHaveCount(6);
});

test('rich demos enforce boundaries and keep alternative routes independent', function ($number, $profile, $academic, $failure) {
    $decision = richDecision($number, $profile, $academic);
    expect($decision['status'])->toBe('Not Eligible')->and($decision['failed_requirements'])->toContain($failure);
})->with([
    [1, [], ['cgpa' => 3.24], 'min_cgpa'],
    [1, [], ['education_level' => 'Foundation', 'cgpa' => 4], 'qualification'],
    [1, [], ['study_status' => 'studying', 'year_of_study' => 2], 'study_status'],
    [2, [], ['spm_as' => 4], 'min_spm_as'],
    [2, [], ['spm_credits' => 4, 'spm_as' => 4], 'min_spm_credits'],
    [3, ['household_income' => 4000.01], [], 'max_household_income'],
    [3, [], ['cgpa' => 3.39], 'min_cgpa'],
    [3, [], ['year_of_study' => 1], 'min_year_of_study'],
    [3, [], ['year_of_study' => 4], 'max_year_of_study'],
    [4, [], ['cgpa' => 3.6], 'min_cgpa'],
    [6, ['household_income' => 3000.01], [], 'max_household_income'],
    [7, ['nationality' => 'Malaysian'], [], 'nationality'],
    [8, [], ['year_of_study' => 1], 'min_year_of_study'],
    [11, [], ['grade_counts' => ['A1' => 1, 'A2' => 1, 'B3' => 3]], 'max_uec_total'],
    [12, [], ['stam_rank' => 'Jayyid'], 'min_stam_rank'],
    [13, [], ['education_level' => 'Foundation', 'cgpa' => 2.9], 'min_cgpa'],
    [14, [], ['year_of_study' => 5], 'max_year_of_study'],
    [15, [], ['year_of_study' => 2], 'min_year_of_study'],
    [17, [], ['institution_type' => 'Public University'], 'institution_type'],
    [19, [], ['field_of_study' => 'Business'], 'field_of_study'],
]);

test('Meridian ABB uses cumulative thresholds and stronger grades count', function ($counts, $status) {
    $decision = richDecision(1, [], ['education_level' => 'A-Level', 'grading_scale' => 'a_level', 'grade_counts' => $counts]);
    expect($decision['status'])->toBe($status);
})->with([
    [['A' => 1, 'B' => 2], 'Eligible'], [['A' => 2, 'B' => 1], 'Eligible'],
    [['B' => 3], 'Not Eligible'], [['A' => 1, 'B' => 1, 'C' => 1], 'Not Eligible'],
]);

test('rich demo pages show fictional labels and retain placeholder presentation links', function () {
    [$profile, $academic] = richApplicant(4);
    $student = makeStudent($profile, $academic);
    $award = Scholarship::where('catalog_key', 'demo-rich-04')->firstOrFail();
    $this->actingAs($student)->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('Fictional demo scholarship')->assertSee('Apply Now')->assertDontSee('Official scholarship source')
        ->assertSee($award->application_link);
    $student->savedScholarships()->create(['scholarship_id' => $award->id]);
    $this->get(route('student.saved-scholarships.index'))->assertOk()->assertSee($award->application_link);
});

test('fictional catalogue cannot claim official verification', function () {
    $entries = ScholarshipCatalog::entries();
    $entries[0]['source_url'] = 'https://example.com/official';
    expect(fn () => ScholarshipCatalog::validateReplacement($entries, '2026-11-07'))->toThrow(InvalidArgumentException::class);
});
