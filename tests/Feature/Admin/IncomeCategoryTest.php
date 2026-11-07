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
    expect($student->fresh()->studentProfile->income_category)->toBeNull();
});

test('admin threshold changes reclassify existing profiles and refresh student range text', function () {
    $student = makeStudent(['household_income' => 3500, 'income_category' => 'M40']);
    $this->actingAs(makeAdmin())->put(route('admin.income-categories.update', IncomeCategory::where('name', 'B40')->firstOrFail()), [
        'name' => 'B40', 'min_income' => 0, 'max_income' => 4000,
    ])->assertSessionHasNoErrors();

    expect($student->fresh()->studentProfile->income_category)->toBe('B40');
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Your income category: B40')->assertSee('RM 0.00 – RM 4,000.00/month')
        ->assertDontSee('RM 3,169')->assertDontSee('DOSM 2022');
});

test('adding a category classifies existing incomes in a previously uncovered range', function () {
    $student = makeStudent(['household_income' => 3169.5, 'income_category' => null]);
    $this->actingAs(makeAdmin())->post(route('admin.income-categories.store'), [
        'name' => 'Bridge', 'min_income' => 3169.01, 'max_income' => 3169.99,
    ])->assertSessionHasNoErrors();
    expect($student->fresh()->studentProfile->income_category)->toBe('Bridge');
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Your income category: Bridge')->assertSee('RM 3,169.01 – RM 3,169.99/month');
});

test('deleting or renaming a category updates stored student classifications', function () {
    $student = makeStudent(['household_income' => 5000, 'income_category' => 'M40']);
    $category = IncomeCategory::where('name', 'M40')->firstOrFail();
    $this->actingAs(makeAdmin())->put(route('admin.income-categories.update', $category), [
        'name' => 'Middle income', 'min_income' => 3170, 'max_income' => 6339,
    ])->assertSessionHasNoErrors();
    expect($student->fresh()->studentProfile->income_category)->toBe('Middle income');
    $this->delete(route('admin.income-categories.destroy', $category))->assertSessionHasNoErrors();
    expect($student->fresh()->studentProfile->income_category)->toBeNull();
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Income category unavailable')->assertSee('No category matches this amount.');
});

test('invalid range edits leave categories and student classifications untouched', function ($changes, $field) {
    $student = makeStudent();
    $category = IncomeCategory::where('name', 'B40')->firstOrFail();
    $this->actingAs(makeAdmin())->put(route('admin.income-categories.update', $category), array_merge([
        'name' => 'B40', 'min_income' => 0, 'max_income' => 3169,
    ], $changes))->assertSessionHasErrors($field);
    expect($category->fresh()->min_income)->toBe('0.00')->and($category->fresh()->max_income)->toBe('3169.00')
        ->and($student->fresh()->studentProfile->income_category)->toBe('B40');
})->with([
    [['min_income' => 4000], 'max_income'],
    [['max_income' => 3169.999], 'max_income'],
    [['min_income' => -1], 'min_income'],
]);

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
