<?php

use App\Models\IncomeCategory;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\IncomeCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(IncomeCategorySeeder::class);
});

test('student can view profile edit page', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->get(route('student.profile.edit'));

    $response->assertOk();
    $response->assertSee('Complete Student Profile');
});

test('zero income uses the current range rather than an outdated stored category', function () {
    $student = makeStudent(['household_income' => 0, 'income_category' => 'T20']);
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Your income category: B40')->assertSee('Current range: RM 0.00 – RM 3,169.00/month');
    // Merely viewing a profile does not rewrite it.
    expect($student->fresh()->studentProfile->income_category)->toBe('T20');
});

test('profile helper reflects retained input after validation errors', function () {
    $student = makeStudent(['household_income' => 2500]);
    $this->actingAs($student)->from(route('student.profile.edit'))->patch(route('student.profile.update'), [
        'nationality' => '', 'state' => 'Johor', 'household_income' => 5000, 'number_of_dependents' => 0,
    ])->assertSessionHasErrors('nationality');
    $this->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Your income category: M40')->assertSee('Current range: RM 3,170.00 – RM 6,339.00/month');
    expect($student->fresh()->studentProfile->income_category)->toBe('B40');
});

test('income gaps and missing configuration have explicit feedback instead of stale labels', function () {
    $student = makeStudent(['household_income' => 3169.5]);
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('Income category unavailable')->assertSee('No category matches this amount.');
    IncomeCategory::query()->delete();
    $this->get(route('student.profile.edit'))->assertOk()->assertSee('No income ranges are configured.');
});

test('income input rejects precision that would change its classification when stored', function () {
    $student = makeStudent();
    $this->actingAs($student)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian', 'state' => 'Johor', 'household_income' => '3169.001', 'number_of_dependents' => 0,
    ])->assertSessionHasErrors('household_income');
    expect($student->fresh()->studentProfile->household_income)->toBe('2500.00');
});

test('student can create a personal profile without academic programme fields', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull();
    expect($profile->nationality)->toBe('Malaysian');
    expect($profile->state)->toBe('Selangor');
    expect((float) $profile->household_income)->toBe(2500.0);
    expect($profile->number_of_dependents)->toBe(3);
    expect($profile->institution_type)->toBeNull();
    expect($profile->field_of_study)->toBeNull();
    expect($profile->income_category)->toBe('B40');
});

test('profile creation auto-classifies income category', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Kuala Lumpur',
        'household_income' => 5000,
        'number_of_dependents' => 2,
    ]);

    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile->income_category)->toBe('M40');
});

test('profile creation classifies T20 for high income', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Penang',
        'household_income' => 10000,
        'number_of_dependents' => 1,
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
    ]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Kuala Lumpur',
        'household_income' => 4000,
        'number_of_dependents' => 2,
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $profile = $user->fresh()->studentProfile;
    expect($profile->state)->toBe('Kuala Lumpur');
    expect((float) $profile->household_income)->toBe(4000.0);
    expect($profile->income_category)->toBe('M40');
});

test('profile validation requires all fields', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => '',
        'state' => '',
        'household_income' => '',
        'number_of_dependents' => '',
    ]);

    $response->assertSessionHasErrors([
        'nationality',
        'state',
        'household_income',
        'number_of_dependents',
    ]);
});

test('profile validation requires numeric household income', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 'abc',
        'number_of_dependents' => 3,
    ]);

    $response->assertSessionHasErrors('household_income');
});

test('profile nationality must be selected from the available options', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Unlisted Nationality',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
    ]);

    $response->assertSessionHasErrors('nationality');
});

test('profile updates cannot change academic programme details', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
        'institution_type' => 'Private University',
        'field_of_study' => 'Unlisted Field',
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->academicResult->institution_type)->toBe('Public University');
    expect($user->fresh()->academicResult->field_of_study)->toBe('Engineering');
    expect($user->fresh()->studentProfile->field_of_study)->toBeNull();
});

test('profile validation requires non-negative dependents', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($user)->patch(route('student.profile.update'), [
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => -1,
    ]);

    $response->assertSessionHasErrors('number_of_dependents');
});

test('student can select non Malaysian nationality and only binary choices are offered', function () {
    $user = makeStudent();
    $payload = $user->studentProfile->only(['nationality', 'state', 'household_income', 'number_of_dependents']);
    $payload['nationality'] = 'Non-Malaysian';
    $this->actingAs($user)->patch(route('student.profile.update'), $payload)->assertSessionHasNoErrors();
    expect($user->fresh()->studentProfile->nationality)->toBe('Non-Malaysian');
    $this->get(route('student.profile.edit'))->assertOk()->assertSee('Malaysian')->assertSee('Non-Malaysian')
        ->assertDontSee('Singaporean')->assertDontSee('Indonesian')->assertDontSee('Bruneian');
    expect(config('scholarship.nationalities'))->toBe(['Malaysian', 'Non-Malaysian']);
});

test('former country and other nationality choices are rejected when saving a profile', function ($nationality) {
    $user = makeStudent();
    $payload = $user->studentProfile->only(['nationality', 'state', 'household_income', 'number_of_dependents']);
    $payload['nationality'] = $nationality;
    $this->actingAs($user)->patch(route('student.profile.update'), $payload)->assertSessionHasErrors('nationality');
    expect($user->fresh()->studentProfile->nationality)->toBe('Malaysian');
})->with(['Singaporean', 'Indonesian', 'Bruneian', 'Other']);

test('profile requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get(route('student.profile.edit'));

    $response->assertForbidden();
});

test('profile redirects guest to login', function () {
    $response = $this->get(route('student.profile.edit'));

    $response->assertRedirect(route('login'));
});
