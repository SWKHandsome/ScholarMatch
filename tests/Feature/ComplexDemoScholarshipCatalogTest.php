<?php

use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Database\Seeders\ScholarshipCatalog;
use Database\Seeders\ScholarshipCatalogSeeder;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 20));
    seedIncomeCategories();
    $this->seed(ScholarshipCatalogSeeder::class);
});

/** Independently specified applicants for the lecturer walkthroughs. */
function complexDemoApplicant(int $number): array
{
    $spm = ['education_level' => 'SPM', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => null];
    $completed = ['study_status' => 'completed', 'year_of_study' => null];
    $cases = [
        51 => [[], array_merge($spm, ['spm_as' => 4, 'spm_credits' => 5, 'institution_type' => 'Polytechnic']), false],
        52 => [[], ['education_level' => 'Diploma', 'year_of_study' => 2, 'cgpa' => 2.5, 'institution_type' => 'Community College', 'field_of_study' => 'Business'], true],
        53 => [['household_income' => 6000], array_merge($spm, ['spm_as' => 5, 'spm_credits' => 6, 'institution_type' => 'Private University', 'field_of_study' => 'Computer Science']), false],
        54 => [['nationality' => 'Non-Malaysian'], ['education_level' => 'Diploma', 'cgpa' => 3.25, 'institution_type' => 'Private University', 'field_of_study' => 'Business'], false],
        55 => [['household_income' => 6500], ['education_level' => 'Diploma', 'cgpa' => 3, 'institution_type' => 'Polytechnic'], false],
        56 => [['household_income' => 5500], array_merge($spm, ['spm_as' => 2, 'spm_credits' => 5, 'institution_type' => 'Private University', 'field_of_study' => 'Arts & Design']), true],
        57 => [['household_income' => 3169], ['education_level' => 'Diploma', 'cgpa' => 3.5, 'institution_type' => 'Private University', 'field_of_study' => 'Health Sciences'], true],
        58 => [['household_income' => 5000], ['education_level' => 'Diploma', 'year_of_study' => 3, 'cgpa' => 3.2, 'institution_type' => 'Polytechnic', 'field_of_study' => 'Hospitality & Tourism'], false],
        59 => [['household_income' => 20000], array_merge($spm, ['spm_as' => 7, 'spm_credits' => 7, 'institution_type' => 'Private University', 'field_of_study' => 'Accounting & Finance']), false],
        60 => [['household_income' => 2800], ['education_level' => 'Diploma', 'cgpa' => 3.6, 'institution_type' => 'Polytechnic'], true],
        61 => [['household_income' => 3000], array_merge($completed, ['education_level' => 'Diploma', 'cgpa' => 3.2]), false],
        62 => [['household_income' => 6000], array_merge($completed, ['education_level' => 'STPM', 'cgpa' => 3, 'institution_type' => 'Private University', 'field_of_study' => 'Business']), false],
        63 => [['nationality' => 'Non-Malaysian'], array_merge($completed, ['education_level' => 'Foundation', 'cgpa' => 3.5, 'institution_type' => 'Private University', 'field_of_study' => 'Computer Science']), false],
        64 => [['household_income' => 3169], array_merge($completed, ['education_level' => 'Matriculation', 'cgpa' => 3.75, 'field_of_study' => 'Medicine']), true],
        65 => [['household_income' => 5500], ['year_of_study' => 4, 'cgpa' => 3.5], true],
        66 => [['household_income' => 7000], array_merge($completed, ['education_level' => 'Diploma', 'cgpa' => 2.9, 'institution_type' => 'Private University', 'field_of_study' => 'Arts & Design']), true],
        67 => [[], ['cgpa' => 3.1, 'field_of_study' => 'Education'], true],
        68 => [['household_income' => 20000], array_merge($completed, ['education_level' => 'Diploma', 'cgpa' => 3.8]), false],
        69 => [['household_income' => 6300], ['cgpa' => 3, 'institution_type' => 'Other', 'field_of_study' => 'Computer Science'], true],
        70 => [['household_income' => 3000], ['cgpa' => 3.7], true],
    ];
    [$profile, $academic, $verification] = $cases[$number];

    return [$profile, array_merge(['funding_study_level' => $number <= 60 ? 'Diploma' : 'Undergraduate'], $academic), $verification];
}

test('additional demos split evenly between diploma and degree funding', function () {
    $new = collect(ScholarshipCatalog::entries())->slice(50);
    expect($new->pluck('catalog_key')->all())->toBe(array_map(fn ($number) => 'demo-'.$number, range(51, 70)))
        ->and($new->countBy('rules.required_study_level')->all())->toBe(['Diploma' => 10, 'Undergraduate' => 10]);
});

test('complex demo applicants qualify only for the correct funded study level', function ($number) {
    [$profile, $academic, $verification] = complexDemoApplicant($number);
    $student = makeStudent($profile, $academic);
    $scholarship = Scholarship::where('catalog_key', 'demo-'.$number)->firstOrFail();
    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($student);
    $decision = collect($result['recommendations'])->firstWhere('scholarship_id', $scholarship->id);
    expect($result['error'])->toBeNull()->and($result['recommendations'])->toHaveCount(70)
        ->and($decision['status'])->toBe('Eligible')->and($decision['requires_verification'])->toBe($verification);

    // Change only the programme seeking funding; latest qualification and all other criteria stay valid.
    $student->academicResult->update(['funding_study_level' => $number <= 60 ? 'Undergraduate' : 'Diploma']);
    $decision = collect($service->getRecommendations($student->fresh())['recommendations'])->firstWhere('scholarship_id', $scholarship->id);
    expect($decision['status'])->toBe('Not Eligible')->and($decision['failed_requirements'])->toBe(['funding_level']);
})->with(range(51, 70));

test('complex demos enforce complete alternatives and combined boundary conditions', function ($number, $profileChanges, $academicChanges, $status, $failure) {
    [$profile, $academic] = complexDemoApplicant($number);
    $student = makeStudent(array_merge($profile, $profileChanges), array_merge($academic, $academicChanges));
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($student);
    expect($result['error'])->toBeNull();
    $decision = collect($result['recommendations'])->firstWhere('scholarship_id', Scholarship::where('catalog_key', 'demo-'.$number)->value('id'));
    expect($decision['status'])->toBe($status);
    if ($failure) {
        expect($decision['failed_requirements'])->toContain($failure);
    }
})->with([
    'SPM As below cutoff' => [51, [], ['spm_as' => 3], 'Not Eligible', 'min_spm_as'],
    'SPM credits below cutoff' => [51, [], ['spm_credits' => 4], 'Not Eligible', 'min_spm_credits'],
    'income just above ceiling in same category' => [51, ['household_income' => 3000.01], [], 'Not Eligible', 'max_household_income'],
    'nationality restriction' => [51, ['nationality' => 'Non-Malaysian'], [], 'Not Eligible', 'nationality'],
    'field restriction' => [51, [], ['field_of_study' => 'Business'], 'Not Eligible', 'field_of_study'],
    'institution restriction' => [51, [], ['institution_type' => 'Private University'], 'Not Eligible', 'institution_type'],
    'STPM alternative for diploma funding' => [51, [], ['education_level' => 'STPM', 'cgpa' => 2.5, 'spm_as' => null, 'spm_credits' => null], 'Eligible', null],
    'current diploma cannot exceed supported year' => [52, [], ['year_of_study' => 3], 'Not Eligible', 'max_year_of_study'],
    'completed foundation alternative' => [53, [], ['education_level' => 'Foundation', 'cgpa' => 3, 'spm_as' => null, 'spm_credits' => null], 'Eligible', null],
    'diploma CGPA cannot borrow the foundation threshold' => [53, [], ['education_level' => 'Diploma', 'study_status' => 'studying', 'year_of_study' => 2, 'cgpa' => 3.24, 'spm_as' => null, 'spm_credits' => null], 'Not Eligible', 'min_cgpa'],
    'final year excludes earlier years' => [58, [], ['year_of_study' => 2], 'Not Eligible', 'min_year_of_study'],
    'combined diploma CGPA just below threshold' => [60, [], ['cgpa' => 3.59], 'Not Eligible', 'min_cgpa'],
    'degree bridge needs completed diploma' => [61, [], ['study_status' => 'studying', 'year_of_study' => 2], 'Not Eligible', 'study_status'],
    'degree bridge accepts exact foundation threshold' => [61, [], ['education_level' => 'Foundation', 'cgpa' => 3.6], 'Eligible', null],
    'foundation cannot use lower diploma threshold' => [61, [], ['education_level' => 'Foundation', 'cgpa' => 3.2], 'Not Eligible', 'min_cgpa'],
    'degree final year upper limit' => [65, [], ['year_of_study' => 5], 'Not Eligible', 'max_year_of_study'],
    'arts diploma has its own lower threshold' => [66, [], ['cgpa' => 2.89], 'Not Eligible', 'min_cgpa'],
    'arts foundation does not use diploma cutoff' => [66, [], ['education_level' => 'Foundation', 'cgpa' => 2.9], 'Not Eligible', 'min_cgpa'],
    'T20 award still requires category' => [68, ['household_income' => 6339], [], 'Not Eligible', 'income_category'],
    'degree challenge accepts completed diploma alternative' => [70, [], ['education_level' => 'Diploma', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => 3.5], 'Eligible', null],
    'degree challenge current student threshold' => [70, [], ['cgpa' => 3.69], 'Not Eligible', 'min_cgpa'],
    'degree challenge current student year' => [70, [], ['year_of_study' => 4], 'Not Eligible', 'max_year_of_study'],
]);
