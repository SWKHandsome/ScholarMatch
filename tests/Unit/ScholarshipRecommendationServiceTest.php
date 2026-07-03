<?php

use App\Models\AcademicResult;
use App\Models\IncomeCategory;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

function makeUnitScholarship(array $attributes = [], array $ruleAttributes = []): array
{
    $scholarship = \App\Models\Scholarship::create(array_merge([
        'name' => 'Test Scholarship',
        'provider' => 'Test Provider',
        'description' => 'Test description',
        'award_type' => 'Full Scholarship',
        'deadline' => now()->addMonth()->toDateString(),
        'application_link' => 'https://example.com',
        'is_active' => true,
    ], $attributes));

    $rule = \App\Models\ScholarshipRule::create(array_merge([
        'scholarship_id' => $scholarship->id,
        'required_nationality' => 'Malaysian',
        'required_study_level' => 'Undergraduate',
        'required_income_category' => 'B40',
        'max_household_income' => 3169.00,
        'min_spm_as' => null,
        'min_spm_credits' => null,
        'min_cgpa' => 3.00,
        'required_field_of_study' => 'Engineering',
        'required_institution_type' => 'Public University',
        'income_rule_type' => 'soft',
        'study_level_rule_type' => 'hard',
        'field_rule_type' => 'soft',
        'institution_rule_type' => 'soft',
        'rule_payload' => null,
    ], $ruleAttributes));

    return [$scholarship->fresh(['rule']), $rule->fresh()];
}

function makeUnitStudent(array $profileAttributes = [], array $academicAttributes = []): User
{
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    \App\Models\StudentProfile::create(array_merge([
        'user_id' => $user->id,
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
        'income_category' => 'B40',
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ], $profileAttributes));

    \App\Models\AcademicResult::create(array_merge([
        'user_id' => $user->id,
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.50,
        'result_status' => 'official',
    ], $academicAttributes));

    return $user->fresh(['studentProfile', 'academicResult']);
}

test('eligible recommendation is scored and persisted', function () {
    [$scholarship] = makeUnitScholarship();
    $user = makeUnitStudent();

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);
    $service->storeRecommendationLogs($user, $result['recommendations']);

    expect($result['error'])->toBeNull();
    expect($result['recommendations'])->toHaveCount(1);
    expect($result['recommendations'][0]['status'])->toBe('Eligible');
    expect($result['recommendations'][0]['score'])->toBe(100);

    $log = RecommendationLog::first();
    expect($log)->not->toBeNull();
    expect($log->scholarship_id)->toBe($scholarship->id);
    expect($log->status)->toBe('Eligible');
    expect($log->score)->toBe(100);
});

test('expired scholarship is not suitable', function () {
    makeUnitScholarship(['deadline' => now()->subDay()->toDateString()]);
    $user = makeUnitStudent();

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['recommendations'][0]['status'])->toBe('Not Suitable');
    expect($result['recommendations'][0]['failed_hard_rules'])->toContain('deadline');
});

test('t20 student fails b40 hard rule', function () {
    makeUnitScholarship([], ['income_rule_type' => 'hard']);
    $user = makeUnitStudent([
        'household_income' => 8000,
        'income_category' => 'T20',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['recommendations'][0]['status'])->toBe('Not Suitable');
    expect($result['recommendations'][0]['failed_hard_rules'])->toContain('income_category');
});

test('non malaysian student fails nationality hard rule', function () {
    makeUnitScholarship([], ['income_rule_type' => 'hard']);
    $user = makeUnitStudent([
        'nationality' => 'Singaporean',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['recommendations'][0]['status'])->toBe('Not Suitable');
    expect($result['recommendations'][0]['failed_hard_rules'])->toContain('nationality');
});

test('pending result returns preliminary guidance', function () {
    makeUnitScholarship();
    $user = makeUnitStudent([], ['result_status' => 'pending']);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['recommendations'][0]['is_preliminary'])->toBeTrue();
    expect(collect($result['recommendations'][0]['explanation'])->contains(fn($e) => str_contains($e, 'Preliminary')))->toBeTrue();
});

test('missing academic result blocks recommendation generation', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    StudentProfile::create([
        'user_id' => $user->id,
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
        'income_category' => 'B40',
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['error'])->toBe('Please enter your academic result first.');
    expect($result['recommendations'])->toBe([]);
});

test('missing student profile blocks recommendation generation', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    AcademicResult::create([
        'user_id' => $user->id,
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.50,
        'result_status' => 'official',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['error'])->toBe('Please complete your student profile first.');
    expect($result['recommendations'])->toBe([]);
});

test('score breakdown totals to final score', function () {
    makeUnitScholarship();
    $user = makeUnitStudent([
        'field_of_study' => 'Business',
        'institution_type' => 'Private University',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    $recommendation = $result['recommendations'][0];

    expect(array_sum($recommendation['score_breakdown']))->toBe($recommendation['score']);
});

test('soft scoring lowers the result for partial match', function () {
    makeUnitScholarship([], [
        'income_rule_type' => 'soft',
        'field_rule_type' => 'soft',
        'institution_rule_type' => 'soft',
    ]);
    $user = makeUnitStudent([
        'field_of_study' => 'Business',
        'institution_type' => 'Public University',
    ]);

    $service = app(ScholarshipRecommendationService::class);
    $result = $service->getRecommendations($user);

    expect($result['recommendations'][0]['status'])->toBe('Partially Eligible');
    expect($result['recommendations'][0]['score'])->toBe(75);
});

test('matching cgpa receives academic score', function () {
    [$scholarship, $rule] = makeUnitScholarship(['deadline' => now()->addMonth()->toDateString()]);
    $user = makeUnitStudent([], ['cgpa' => 3.80]);

    $service = app(ScholarshipRecommendationService::class);
    $academicScore = (new ReflectionClass($service))->getMethod('calculateSoftScore');
    $academicScore->setAccessible(true);
    $result = $academicScore->invoke($service, $user->studentProfile, $user->academicResult, $rule);

    expect($result['breakdown']['academic'])->toBe(40);
});

test('income category classification uses seeded thresholds', function () {
    expect(IncomeCategory::classifyIncome(2000))->toBe('B40');
    expect(IncomeCategory::classifyIncome(5000))->toBe('M40');
    expect(IncomeCategory::classifyIncome(8000))->toBe('T20');
});
