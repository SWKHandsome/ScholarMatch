<?php

use App\Models\IncomeCategory;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('income edits invalidate decisions and reclassify stored student categories', function () {
    seedIncomeCategories();
    $student = makeStudent(['household_income' => 2500]);
    makeScholarship();
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($student)['recommendations'][0]['status'])->toBe('Eligible');
    $category = IncomeCategory::where('name', 'B40')->firstOrFail();
    $this->actingAs(makeAdmin())->put(route('admin.income-categories.update', $category), [
        'name' => 'B40', 'min_income' => 2600, 'max_income' => 3169,
    ])->assertSessionHasNoErrors();
    expect($service->getRecommendations($student->fresh())['error'])->toContain('income cannot be classified');
});

test('admin can view income categories index', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->get(route('admin.income-categories.index'));

    $response->assertOk();
    $response->assertSee('Income Categories');
    $response->assertSee('B40');
    $response->assertSee('M40');
    $response->assertSee('T20');
});

test('income categories index shows seeded thresholds', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->get(route('admin.income-categories.index'));

    $response->assertOk();
    $response->assertSee('3,169');
    $response->assertSee('6,339');
});

test('income categories index requires admin role', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('admin.income-categories.index'));

    $response->assertForbidden();
});

test('income categories redirect guest to login', function () {
    $response = $this->get(route('admin.income-categories.index'));

    $response->assertRedirect(route('login'));
});

test('income category classify method works correctly', function () {
    expect(IncomeCategory::classifyIncome(2000))->toBe('B40');
    expect(IncomeCategory::classifyIncome(3169))->toBe('B40');
    expect(IncomeCategory::classifyIncome(3170))->toBe('M40');
    expect(IncomeCategory::classifyIncome(5000))->toBe('M40');
    expect(IncomeCategory::classifyIncome(6339))->toBe('M40');
    expect(IncomeCategory::classifyIncome(6340))->toBe('T20');
    expect(IncomeCategory::classifyIncome(10000))->toBe('T20');
});
