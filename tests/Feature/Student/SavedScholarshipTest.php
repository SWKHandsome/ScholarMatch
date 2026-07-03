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

test('student can save a scholarship', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    $response = $this->actingAs($user)->post(route('student.saved-scholarships.store'), [
        'scholarship_id' => $scholarship->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(SavedScholarship::where('user_id', $user->id)
        ->where('scholarship_id', $scholarship->id)
        ->exists())->toBeTrue();
});

test('student can view saved scholarships list', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    SavedScholarship::create([
        'user_id' => $user->id,
        'scholarship_id' => $scholarship->id,
    ]);

    $response = $this->actingAs($user)->get(route('student.saved-scholarships.index'));

    $response->assertOk();
    $response->assertSee('Test Scholarship');
    $response->assertSee('Saved Scholarships');
});

test('student can remove saved scholarship', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    SavedScholarship::create([
        'user_id' => $user->id,
        'scholarship_id' => $scholarship->id,
    ]);

    $response = $this->actingAs($user)->delete(
        route('student.saved-scholarships.destroy', $scholarship)
    );

    $response->assertRedirect(route('student.saved-scholarships.index'));
    $response->assertSessionHas('success');

    expect(SavedScholarship::where('user_id', $user->id)
        ->where('scholarship_id', $scholarship->id)
        ->exists())->toBeFalse();
});

test('saved scholarships list is empty when none saved', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->get(route('student.saved-scholarships.index'));

    $response->assertOk();
    $response->assertSee('No Saved Scholarships');
});

test('student cannot save duplicate scholarship', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();

    SavedScholarship::create([
        'user_id' => $user->id,
        'scholarship_id' => $scholarship->id,
    ]);

    $response = $this->actingAs($user)->post(route('student.saved-scholarships.store'), [
        'scholarship_id' => $scholarship->id,
    ]);

    $response->assertSessionHasErrors();
});

test('saved scholarships requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $scholarship = makeScholarship();

    $response = $this->actingAs($admin)->get(route('student.saved-scholarships.index'));

    $response->assertForbidden();
});

test('saved scholarships redirects guest to login', function () {
    $response = $this->get(route('student.saved-scholarships.index'));

    $response->assertRedirect(route('login'));
});