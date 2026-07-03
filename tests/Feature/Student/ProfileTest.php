<?php

use App\Models\IncomeCategory;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\IncomeCategorySeeder::class);
});

test('student can view profile edit page', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->get(route('student.profile.edit'));

    $response->assertOk();
    $response->assertSee('Edit Profile');
});

test('student can create profile with all fields', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ]);

    $response->assertRedirect(route('student.profile.edit'));
    $response->assertSessionHas('success');

    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull();
    expect($profile->nationality)->toBe('Malaysian');
    expect($profile->state)->toBe('Selangor');
    expect($profile->household_income)->toBe(2500);
    expect($profile->number_of_dependents)->toBe(3);
    expect($profile->institution_type)->toBe('Public University');
    expect($profile->field_of_study)->toBe('Engineering');
    expect($profile->income_category)->toBe('B40');
});

test('profile creation auto-classifies income category', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Kuala Lumpur',
        'household_income' => 5000,
        'number_of_dependents' => 2,
        'institution_type' => 'Private University',
        'field_of_study' => 'Business',
    ]);

    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile->income_category)->toBe('M40');
});

test('profile creation classifies T20 for high income', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Penang',
        'household_income' => 10000,
        'number_of_dependents' => 1,
        'institution_type' => 'Public University',
        'field_of_study' => 'Medicine',
    ]);

    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile->income_category)->toBe('T20');
});

test('student can update existing profile', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    StudentProfile::create([
        'user_id' => $user->id,
        'nationality' => 'Malaysian',
        'state' => 'Johor',
        'household_income' => 3000,
        'number_of_dependents' => 4,
        'income_category' => 'B40',
        'institution_type' => 'Public University',
        'field_of_study' => 'Science',
    ]);

    $response = $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Kuala Lumpur',
        'household_income' => 4000,
        'number_of_dependents' => 2,
        'institution_type' => 'Private University',
        'field_of_study' => 'Engineering',
    ]);

    $response->assertRedirect(route('student.profile.edit'));
    $response->assertSessionHas('success');

    $profile = $user->fresh()->studentProfile;
    expect($profile->state)->toBe('Kuala Lumpur');
    expect($profile->household_income)->toBe(4000);
    expect($profile->income_category)->toBe('M40');
    expect($profile->field_of_study)->toBe('Engineering');
});

test('profile validation requires all fields', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => '',
        'state' => '',
        'household_income' => '',
        'number_of_dependents' => '',
        'institution_type' => '',
        'field_of_study' => '',
    ]);

    $response->assertSessionHasErrors([
        'nationality',
        'state',
        'household_income',
        'number_of_dependents',
        'institution_type',
        'field_of_study',
    ]);
});

test('profile validation requires numeric household income', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 'abc',
        'number_of_dependents' => 3,
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ]);

    $response->assertSessionHasErrors('household_income');
});

test('profile validation requires non-negative dependents', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->post(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => -1,
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ]);

    $response->assertSessionHasErrors('number_of_dependents');
});

test('profile requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get(route('student.profile.edit'));

    $response->assertForbidden();
});

test('profile redirects guest to login', function () {
    $response = $this->get(route('student.profile.edit'));

    $response->assertRedirect(route('login'));
});