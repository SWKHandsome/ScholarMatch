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

test('dashboard shows incomplete profile and academic when missing', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Incomplete');
    $response->assertSee('Complete Profile');
    $response->assertSee('Add Academic Result');
});

test('dashboard shows complete profile and academic when both exist', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Complete');
    $response->assertSee('Update Profile');
    $response->assertSee('Update Result');
});

test('dashboard shows saved scholarships count', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    SavedScholarship::create([
        'user_id' => $user->id,
        'scholarship_id' => $scholarship->id,
    ]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('1');
    $response->assertSee('Saved Scholarships');
});

test('dashboard shows recent recommendations when profile and academic complete', function () {
    $user = makeStudent();
    makeScholarship();

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Top Recommendations');
    $response->assertSee('Test Scholarship');
});

test('dashboard shows no matches message when no scholarships match', function () {
    $user = makeStudent([
        'nationality' => 'Singaporean',
    ]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('No Scholarships Matched');
});

test('dashboard shows quick action links', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Update Profile');
    $response->assertSee('Update Academic Result');
    $response->assertSee(route('student.profile.edit'));
    $response->assertSee(route('student.academic-result.edit'));
});

test('dashboard requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get(route('student.dashboard'));

    $response->assertForbidden();
});

test('dashboard redirects guest to login', function () {
    $response = $this->get(route('student.dashboard'));

    $response->assertRedirect(route('login'));
});