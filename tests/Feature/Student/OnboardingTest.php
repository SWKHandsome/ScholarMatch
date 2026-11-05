<?php

use App\Models\User;

beforeEach(fn () => seedIncomeCategories());

function onboardingProfileData(array $overrides = []): array
{
    return array_merge(['nationality' => 'Malaysian', 'state' => 'Kuala Lumpur', 'household_income' => 0, 'number_of_dependents' => 0], $overrides);
}

test('new registration starts profile setup and ignores an earlier intended destination', function () {
    $this->withSession(['url.intended' => route('student.recommendations')])->post(route('register'), [
        'name' => 'New Student', 'email' => 'new@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!',
    ])->assertRedirect(route('student.profile.edit'))->assertSessionMissing('url.intended');
    $this->get(route('student.profile.edit'))->assertOk()->assertSee('Complete Student Profile')->assertSee('Save and Continue')->assertSee('Student setup steps');
});

test('login sends a student with no personal profile there before academics', function () {
    $student = makeStudent();
    $student->studentProfile->delete();
    $this->withSession(['url.intended' => route('student.recommendations')])->post(route('login'), ['email' => $student->email, 'password' => 'password'])
        ->assertRedirect(route('student.profile.edit'))->assertSessionMissing('url.intended');
});

test('a partially filled or invalid personal profile is not complete', function ($attributes) {
    $student = makeStudent($attributes);
    $this->post(route('login'), ['email' => $student->email, 'password' => 'password'])->assertRedirect(route('student.profile.edit'));
})->with([
    'missing state' => [['state' => '']],
    'invalid dependents' => [['number_of_dependents' => -1]],
    'unsupported nationality' => [['nationality' => 'Legacy nationality']],
]);

test('returning students resume academic setup without saving a completed personal profile again', function ($attributes) {
    $student = makeStudent([], $attributes);
    $this->post(route('login'), ['email' => $student->email, 'password' => 'password'])->assertRedirect(route('student.academic-result.edit'));
    $this->get(route('student.academic-result.edit'))->assertOk()->assertSee('Student setup steps')->assertSee('Academic Result');
})->with(['pending results' => [['result_status' => 'pending']], 'missing programme' => [['field_of_study' => null]]]);

test('students with a saved personal profile but no academic record go directly to academics', function () {
    $student = makeStudent();
    $student->academicResult->delete();
    $this->post(route('login'), ['email' => $student->email, 'password' => 'password'])->assertRedirect(route('student.academic-result.edit'));
});

test('zero income and zero dependents count as completed personal information', function () {
    $student = makeStudent(['household_income' => 0, 'number_of_dependents' => 0]);
    expect($student->studentProfile->isComplete())->toBeTrue();
    $this->post(route('login'), ['email' => $student->email, 'password' => 'password'])->assertRedirect(route('dashboard', absolute: false));
});

test('complete students keep normal intended navigation and manual editing behaviour', function () {
    $student = makeStudent();
    $this->withSession(['url.intended' => route('student.saved-scholarships.index')])->post(route('login'), ['email' => $student->email, 'password' => 'password'])
        ->assertRedirect(route('student.saved-scholarships.index'));
    $this->get(route('student.profile.edit'))->assertOk()->assertSee('Save Changes')->assertDontSee('Student setup steps');
    $this->patch(route('student.profile.update'), onboardingProfileData())->assertRedirect(route('student.dashboard'));
});

test('administrator login keeps its existing dashboard destination', function () {
    $admin = makeAdmin();
    $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
});

test('setup saves personal information then academic information and reaches dashboard', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $this->actingAs($student)->get(route('dashboard'))->assertRedirect(route('student.profile.edit'));
    $this->patch(route('student.profile.update'), onboardingProfileData())->assertRedirect(route('student.academic-result.edit'));
    $this->get(route('dashboard'))->assertRedirect(route('student.academic-result.edit'));
    $this->patch(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 1, 'cgpa' => 3.20,
        'funding_study_level' => 'Undergraduate', 'institution_type' => 'Public University', 'field_of_study' => 'Business', 'result_status' => 'official',
    ])->assertRedirect(route('student.dashboard'));
    $this->get(route('dashboard'))->assertRedirect(route('student.dashboard'));
});

test('invalid submissions stay on their form with setup progress and entered values', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $this->actingAs($student)->from(route('student.profile.edit'))->patch(route('student.profile.update'), onboardingProfileData(['state' => 'Putrajaya', 'nationality' => '']))
        ->assertRedirect(route('student.profile.edit'))->assertSessionHasErrors('nationality');
    $this->get(route('student.profile.edit'))->assertOk()->assertSee('value="Putrajaya" selected', false)->assertSee('Student setup steps');
});

test('legacy territory names remain selected without being rewritten until profile save', function ($territory) {
    $student = makeStudent(['state' => 'Wilayah Persekutuan '.$territory]);
    $this->actingAs($student)->get(route('student.profile.edit'))->assertOk()
        ->assertSee('value="'.$territory.'" selected', false)->assertDontSee('Wilayah Persekutuan');
    expect($student->studentProfile->fresh()->state)->toBe('Wilayah Persekutuan '.$territory);
    $this->patch(route('student.profile.update'), onboardingProfileData(['state' => 'Wilayah Persekutuan '.$territory]))->assertRedirect(route('student.dashboard'));
    expect($student->studentProfile->fresh()->state)->toBe($territory);
})->with(['Kuala Lumpur', 'Labuan', 'Putrajaya']);

test('an existing completed academic record is retained when personal setup is saved', function () {
    $student = makeStudent();
    $original = $student->academicResult->getAttributes();
    $student->studentProfile->delete();
    $this->actingAs($student)->patch(route('student.profile.update'), onboardingProfileData())->assertRedirect(route('student.dashboard'));
    expect($student->academicResult->fresh()->getAttributes())->toBe($original);
});
