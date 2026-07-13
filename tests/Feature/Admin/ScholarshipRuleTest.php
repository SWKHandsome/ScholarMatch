<?php

use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('admin can view scholarship rules index', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->get(route('admin.scholarships.rules.index', $scholarship));

    $response->assertOk();
    $response->assertSee('Scholarship Rules');
    $response->assertSee('Test Scholarship');
});

test('admin can view create scholarship rules page', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->get(route('admin.scholarships.rules.create', $scholarship));

    $response->assertOk();
    $response->assertSee('Add Rules for: Test Scholarship');
    $response->assertSee('Required Nationality');
    $response->assertSee('Required Study Level');
    $response->assertSee('Required Income Category');
    $response->assertSee('Max Household Income');
    $response->assertSee('Min SPM As');
    $response->assertSee('Min SPM Credits');
    $response->assertSee('Min CGPA');
    $response->assertSee('Required Field of Study');
    $response->assertSee('Required Institution Type');
    $response->assertSee('Income Rule Type');
    $response->assertSee('Study Level Rule Type');
    $response->assertSee('Field of Study Rule Type');
    $response->assertSee('Institution Type Rule Type');
});

test('admin can create scholarship rules', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.rules.store', $scholarship), [
        'required_nationality' => 'Malaysian',
        'required_study_level' => 'Undergraduate',
        'required_income_category' => 'B40',
        'max_household_income' => 3169,
        'min_spm_as' => 5,
        'min_spm_credits' => 3,
        'min_cgpa' => 3.0,
        'required_field_of_study' => 'Engineering',
        'required_institution_type' => 'Public University',
        'income_rule_type' => 'hard',
        'study_level_rule_type' => 'hard',
        'field_rule_type' => 'soft',
        'institution_rule_type' => 'soft',
    ]);

    $response->assertRedirect(route('admin.scholarships.rules.index', $scholarship));
    $response->assertSessionHas('success');

    $rule = ScholarshipRule::where('scholarship_id', $scholarship->id)->first();
    expect($rule)->not->toBeNull();
    expect($rule->required_nationality)->toBe('Malaysian');
    expect($rule->income_rule_type)->toBe('hard');
    expect($rule->study_level_rule_type)->toBe('hard');
});

test('admin can view edit scholarship rules page', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();
    $rule = $scholarship->rule;

    $response = $this->actingAs($admin)->get(route('admin.scholarships.rules.edit', [$scholarship, $rule]));

    $response->assertOk();
    $response->assertSee('Edit Scholarship Rules');
    $response->assertSee('Test Scholarship');
    $response->assertSee('Malaysian');
    $response->assertSee('B40');
});

test('admin can update scholarship rules', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();
    $rule = $scholarship->rule;

    $response = $this->actingAs($admin)->put(route('admin.scholarships.rules.update', [$scholarship, $rule]), [
        'required_nationality' => 'Malaysian',
        'required_study_level' => 'Diploma',
        'required_income_category' => 'M40',
        'max_household_income' => 6339,
        'min_spm_as' => 3,
        'min_spm_credits' => 3,
        'min_cgpa' => 2.5,
        'required_field_of_study' => 'Business',
        'required_institution_type' => 'Private University',
        'income_rule_type' => 'soft',
        'study_level_rule_type' => 'soft',
        'field_rule_type' => 'hard',
        'institution_rule_type' => 'hard',
    ]);

    $response->assertRedirect(route('admin.scholarships.rules.index', $scholarship));
    $response->assertSessionHas('success');

    $rule = $scholarship->fresh()->rule;
    expect($rule->required_study_level)->toBe('Diploma');
    expect($rule->required_income_category)->toBe('M40');
    expect($rule->field_rule_type)->toBe('hard');
});

test('scholarship rules validation requires rule types', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.rules.store', $scholarship), [
        'required_nationality' => 'Malaysian',
        'income_rule_type' => '',
        'study_level_rule_type' => '',
        'field_rule_type' => '',
        'institution_rule_type' => '',
    ]);

    $response->assertSessionHasErrors([
        'income_rule_type',
        'study_level_rule_type',
        'field_rule_type',
        'institution_rule_type',
    ]);
});

test('scholarship rules validation requires valid rule types', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.rules.store', $scholarship), [
        'required_nationality' => 'Malaysian',
        'income_rule_type' => 'invalid',
        'study_level_rule_type' => 'hard',
        'field_rule_type' => 'soft',
        'institution_rule_type' => 'none',
    ]);

    $response->assertSessionHasErrors('income_rule_type');
});

test('scholarship rules require admin role', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $scholarship = makeScholarship();

    $response = $this->actingAs($student)->get(route('admin.scholarships.rules.index', $scholarship));

    $response->assertForbidden();
});

test('scholarship rules redirect guest to login', function () {
    $scholarship = makeScholarship();

    $response = $this->get(route('admin.scholarships.rules.index', $scholarship));

    $response->assertRedirect(route('login'));
});