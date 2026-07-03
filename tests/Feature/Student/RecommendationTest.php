<?php

use App\Models\AcademicResult;
use App\Models\SavedScholarship;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('recommendations index shows error when profile incomplete', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);
    AcademicResult::create([
        'user_id' => $user->id,
        'education_level' => 'Undergraduate',
        'cgpa' => 3.50,
        'result_status' => 'official',
    ]);

    $response = $this->actingAs($user)->get(route('student.recommendations'));

    $response->assertOk();
    $response->assertSee('Please complete your student profile first');
});

test('recommendations index shows error when academic result missing', function () {
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

    $response = $this->actingAs($user)->get(route('student.recommendations'));

    $response->assertOk();
    $response->assertSee('Please enter your academic result first');
});

test('recommendations index shows eligible scholarships for matching student', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    $response = $this->actingAs($user)->get(route('student.recommendations'));

    $response->assertOk();
    $response->assertSee('Test Scholarship');
    $response->assertSee('Eligible');
    $response->assertSee('100');
    $response->assertSee('View Details');
    $response->assertSee('Apply Now');
    $response->assertSee('Save');
});

test('recommendations index shows not suitable for non-matching student', function () {
    $user = makeStudent(['nationality' => 'Singaporean']);
    makeScholarship();

    $response = $this->actingAs($user)->get(route('student.recommendations'));

    $response->assertOk();
    $response->assertSee('Not Suitable');
    $response->assertSee('nationality');
});

test('recommendations index shows preliminary badge for pending results', function () {
    $user = makeStudent([], ['result_status' => 'pending']);
    makeScholarship();

    $response = $this->actingAs($user)->get(route('student.recommendations'));

    $response->assertOk();
    $response->assertSee('Preliminary');
});

test('recommendations detail page shows full scholarship details', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    $response = $this->actingAs($user)->get(route('student.recommendations.show', $scholarship));

    $response->assertOk();
    $response->assertSee('Test Scholarship');
    $response->assertSee('Test Provider');
    $response->assertSee('Full Scholarship');
    $response->assertSee('Eligible');
    $response->assertSee('100');
    $response->assertSee('Score Breakdown');
    $response->assertSee('Academic');
    $response->assertSee('Field');
    $response->assertSee('Institution');
    $response->assertSee('Income');
    $response->assertSee('Explanation');
    $response->assertSee('Apply Now');
    $response->assertSee('Save');
});

test('recommendations detail shows not suitable details for failed hard rule', function () {
    $user = makeStudent(['household_income' => 8000, 'income_category' => 'T20']);
    $scholarship = makeScholarship([], ['income_rule_type' => 'hard']);

    $response = $this->actingAs($user)->get(route('student.recommendations.show', $scholarship));

    $response->assertOk();
    $response->assertSee('Not Suitable');
    $response->assertSee('income_category');
});

test('recommendations detail requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->get(route('student.recommendations.show', $scholarship));

    $response->assertForbidden();
});

test('recommendations detail redirects guest to login', function () {
    $scholarship = makeScholarship();

    $response = $this->get(route('student.recommendations.show', $scholarship));

    $response->assertRedirect(route('login'));
});