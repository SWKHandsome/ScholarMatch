<?php

use App\Models\RecommendationLog;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;

beforeEach(fn () => seedIncomeCategories());

test('matching official results produce eligibility without a score', function () {
    $user = makeStudent();
    makeScholarship();
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user);
    expect($result['error'])->toBeNull();
    $rec = $result['recommendations'][0];
    expect($rec['status'])->toBe('Eligible')->and($rec)->not->toHaveKey('score');
    expect($rec['failed_requirements'])->toBe([]);
    expect($rec['requires_verification'])->toBeFalse();
});

test('each specified requirement can independently reject the application', function ($profile, $academic, $rules, $failure) {
    $user = makeStudent($profile, $academic);
    makeScholarship([], $rules);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Not Eligible');
    expect($rec['failed_requirements'])->toContain($failure);
})->with([
    'nationality' => [['nationality' => 'Singaporean'], [], [], 'nationality'],
    'category' => [['household_income' => 8000], [], [], 'income_category'],
    'income amount' => [[], [], ['max_household_income' => 2499.99], 'max_household_income'],
    'CGPA' => [[], ['cgpa' => 2.99], [], 'min_cgpa'],
    'field' => [['field_of_study' => 'Business'], [], [], 'field_of_study'],
    'institution' => [['institution_type' => 'Private University'], [], [], 'institution_type'],
    'funding level' => [[], ['funding_study_level' => 'Diploma'], [], 'funding_level'],
    'qualification' => [[], [], ['accepted_education_levels' => ['Diploma']], 'qualification'],
    'study status' => [[], [], ['required_study_status' => 'completed'], 'study_status'],
    'minimum year' => [[], [], ['required_study_status' => 'studying', 'min_year_of_study' => 3], 'min_year_of_study'],
    'maximum year' => [[], [], ['required_study_status' => 'studying', 'max_year_of_study' => 1], 'max_year_of_study'],
]);

test('SPM qualification and undergraduate funding are checked separately', function () {
    $user = makeStudent([], [
        'education_level' => 'SPM', 'study_status' => 'completed', 'year_of_study' => null,
        'spm_as' => 8, 'spm_credits' => 9, 'cgpa' => null,
    ]);
    makeScholarship([], ['min_spm_as' => 8, 'min_spm_credits' => 9, 'min_cgpa' => null]);
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0]['status'])->toBe('Eligible');
});

test('SPM As and credits both have to meet their configured minimums', function ($as, $credits, $failure) {
    $user = makeStudent([], ['education_level' => 'SPM', 'study_status' => 'completed',
        'year_of_study' => null, 'spm_as' => $as, 'spm_credits' => $credits, 'cgpa' => null]);
    makeScholarship([], ['min_spm_as' => 8, 'min_spm_credits' => 9, 'min_cgpa' => null]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Not Eligible')->and($rec['failed_requirements'])->toContain($failure);
})->with([[7, 9, 'min_spm_as'], [8, 8, 'min_spm_credits']]);

test('special requirements flag eligible results only', function ($special, $cgpa, $status, $flag) {
    $user = makeStudent([], ['cgpa' => $cgpa]);
    makeScholarship(['special_requirements' => $special]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe($status)->and($rec['requires_verification'])->toBe($flag);
})->with([
    ['Attend an interview.', 3.50, 'Eligible', true],
    ['Attend an interview.', 2.50, 'Not Eligible', false],
    [" \n\t ", 3.50, 'Eligible', false],
    [null, 3.50, 'Eligible', false],
]);

test('pending incomplete and legacy academic records block recommendations', function ($attributes) {
    $user = makeStudent([], $attributes);
    makeScholarship(['special_requirements' => 'Interview']);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user);
    expect($result['error'])->not->toBeNull()->and($result['recommendations'])->toBe([]);
})->with([
    [['result_status' => 'pending']],
    [['cgpa' => null]],
    [['funding_study_level' => null]],
    [['study_status' => null]],
    [['year_of_study' => null]],
    [['education_level' => 'SPM', 'study_status' => 'studying', 'spm_as' => 8, 'spm_credits' => 9]],
]);

test('missing profile or academic record blocks evaluation', function () {
    $service = app(ScholarshipRecommendationService::class);
    $user = User::factory()->create();
    expect($service->getRecommendations($user)['error'])->toContain('profile');
    $user = makeStudent();
    $user->academicResult()->delete();
    expect($service->getRecommendations($user->fresh())['error'])->toContain('academic result');
});

test('inactive expired and unconfigured scholarships cannot become eligible', function () {
    $user = makeStudent();
    makeScholarship(['is_active' => false]);
    makeScholarship(['deadline' => today()->subDay()->toDateString()]);
    $unconfigured = makeScholarship();
    $unconfigured->rule()->delete();
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user);
    expect($result['recommendations'])->toBe([])->and($result['unconfigured_count'])->toBe(1);
});

test('deadline day stays open and a cached result expires on the next day', function () {
    $this->travelTo(now()->setTime(23, 50));
    $user = makeStudent();
    makeScholarship(['deadline' => today()->toDateString()]);
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'])->toHaveCount(1);
    $this->travel(20)->minutes();
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'])->toBe([]);
});

test('threshold equality and zero minimums pass', function () {
    $user = makeStudent([], ['cgpa' => 3.00]);
    makeScholarship([], ['max_household_income' => 2500, 'min_year_of_study' => 2, 'max_year_of_study' => 2]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Eligible');
    $user = makeStudent([], ['cgpa' => 0]);
    makeScholarship(['name' => 'Zero Threshold'], ['min_cgpa' => 0]);
    $recs = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'];
    expect(collect($recs)->firstWhere('scholarship_data.name', 'Zero Threshold')['status'])->toBe('Eligible');
});

test('matching any supported field passes and unspecified criteria are not applicable', function () {
    $user = makeStudent();
    makeScholarship([], ['required_fields_of_study' => ['Business', 'Engineering'], 'required_income_category' => null]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Eligible');
    expect(collect($rec['requirement_results'])->firstWhere('key', 'income_category')['outcome'])->toBe('not_applicable');
});

test('all failures remain visible instead of stopping at the first failure', function () {
    $user = makeStudent(['field_of_study' => 'Business', 'institution_type' => 'Private University'], ['cgpa' => 2]);
    makeScholarship();
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['failed_requirements'])->toContain('field_of_study', 'institution_type', 'min_cgpa');
});

test('eligibility groups are ordered by deadline with deterministic ties', function () {
    $user = makeStudent();
    makeScholarship(['name' => 'Rejected Soon', 'deadline' => today()->addDay()->toDateString()], ['min_cgpa' => 4]);
    makeScholarship(['name' => 'Later', 'deadline' => now()->addDays(4)->toDateString()]);
    makeScholarship(['name' => 'Earlier', 'deadline' => now()->addDays(2)->toDateString()]);
    $names = array_column(array_column(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'], 'scholarship_data'), 'name');
    expect($names)->toBe(['Earlier', 'Later', 'Rejected Soon']);
});

test('same-second academic changes do not return stale cached results', function () {
    $user = makeStudent();
    makeScholarship();
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($user)['recommendations'][0]['status'])->toBe('Eligible');
    $user->academicResult->update(['cgpa' => 2]);
    expect($service->getRecommendations($user->fresh())['recommendations'][0]['status'])->toBe('Not Eligible');
});

test('current income classification overrides the stored category', function () {
    $user = makeStudent(['income_category' => 'T20']);
    makeScholarship();
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0]['status'])->toBe('Eligible');
});

test('a broad nationality does not become a false failure against a specific country requirement', function () {
    $user = makeStudent(['nationality' => 'Non-Malaysian']);
    makeScholarship([], ['required_nationality' => 'Singaporean']);
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($user)['error'])->toContain('specific nationality');
    $user->studentProfile->update(['nationality' => 'Singaporean']);
    expect($service->getRecommendations($user->fresh())['recommendations'][0]['status'])->toBe('Eligible');
});

test('logs store decisions and structured explanations without score fields in the result', function () {
    $user = makeStudent();
    makeScholarship(['special_requirements' => 'Interview']);
    $service = app(ScholarshipRecommendationService::class);
    $service->storeRecommendationLogs($user, $service->getRecommendations($user)['recommendations']);
    $service->storeRecommendationLogs($user, $service->getRecommendations($user)['recommendations']);
    expect(RecommendationLog::count())->toBe(1);
    $log = RecommendationLog::first();
    expect($log->status)->toBe('Eligible')->and($log->requires_verification)->toBeTrue();
    expect($log->requirement_results)->not->toBeEmpty()->and($log->isCurrentEvaluation())->toBeTrue();
});

test('independent qualification boxes accept either diploma graduates or current undergraduates', function ($academic, $expected) {
    $user = makeStudent([], $academic);
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany([
        ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3.00],
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.30, 'min_year_of_study' => 1, 'max_year_of_study' => 3],
    ]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe($expected);
    $routeCheck = collect($rec['requirement_results'])->firstWhere('key', 'academic_route');
    expect($routeCheck['routes'])->toHaveCount(2);
    if ($expected === 'Eligible') {
        expect($rec['failed_requirements'])->toBe([]);
    }
})->with([
    [['education_level' => 'Diploma', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => 3.00], 'Eligible'],
    [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 1, 'cgpa' => 3.30], 'Eligible'],
    [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 3, 'cgpa' => 3.30], 'Eligible'],
    [['education_level' => 'Diploma', 'study_status' => 'studying', 'year_of_study' => 2, 'cgpa' => 3.80], 'Not Eligible'],
    [['education_level' => 'Undergraduate', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => 3.80], 'Not Eligible'],
    [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 4, 'cgpa' => 3.80], 'Not Eligible'],
    [['education_level' => 'Diploma', 'study_status' => 'completed', 'year_of_study' => null, 'cgpa' => 2.99], 'Not Eligible'],
    [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2, 'cgpa' => 3.29], 'Not Eligible'],
]);

test('a later route with the same qualification may pass without earlier failures rejecting it', function () {
    $user = makeStudent([], ['cgpa' => 3.20]);
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany([
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.50],
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.00, 'min_year_of_study' => 2],
    ]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Eligible')->and($rec['failed_requirements'])->toBe([]);
});

test('requirements from different boxes cannot be combined to invent a passing route', function () {
    $user = makeStudent([], ['cgpa' => 3.00, 'year_of_study' => 1]);
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany([
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.50, 'min_year_of_study' => 1],
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 2.50, 'min_year_of_study' => 2],
    ]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Not Eligible')->and($rec['failed_requirements'])->toContain('academic_route');
});

test('general conditions still reject a student who passes a qualification box', function () {
    $user = makeStudent(['nationality' => 'Singaporean']);
    $scholarship = makeScholarship(['special_requirements' => 'Interview']);
    $scholarship->rule->qualificationRoutes()->create(['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 0]);
    $rec = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0];
    expect($rec['status'])->toBe('Not Eligible')->and($rec['failed_requirements'])->toContain('nationality');
    expect($rec['requires_verification'])->toBeFalse();
});

test('SPM route checks both minimums and ignores a different CGPA route', function ($as, $credits, $expected) {
    $user = makeStudent([], ['education_level' => 'SPM', 'study_status' => 'completed', 'year_of_study' => null,
        'spm_as' => $as, 'spm_credits' => $credits, 'cgpa' => null]);
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany([
        ['education_level' => 'SPM', 'study_status' => 'completed', 'min_spm_as' => 8, 'min_spm_credits' => 9],
        ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3.9],
    ]);
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0]['status'])->toBe($expected);
})->with([[8, 9, 'Eligible'], [7, 9, 'Not Eligible'], [8, 8, 'Not Eligible']]);
