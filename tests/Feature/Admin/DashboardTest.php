<?php

use App\Models\AcademicResult;
use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('admin dashboard shows stats', function () {
    $admin = makeAdmin();
    makeStudent();
    makeScholarship();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Total Students');
    $response->assertSee('Total Scholarships');
    $response->assertSee('Active Scholarships');
    $response->assertSee('Total Recommendations');
});

test('admin dashboard requires admin role', function () {
    $student = makeStudent();

    $response = $this->actingAs($student)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('admin dashboard redirects guest to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});