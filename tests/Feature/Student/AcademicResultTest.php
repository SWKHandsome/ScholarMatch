<?php

use App\Models\AcademicResult;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('student can view academic result edit page', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->get(route('student.academic-result.edit'));

    $response->assertOk();
    $response->assertSee('Academic Result');
    $response->assertSee('SPM');
    $response->assertSee('STPM');
    $response->assertSee('Foundation');
    $response->assertSee('Matriculation');
    $response->assertSee('Diploma');
    $response->assertSee('Undergraduate');
});

test('student can create SPM academic result with As and credits', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'SPM',
        'spm_as' => 8,
        'spm_credits' => 6,
        'cgpa' => null,
        'result_status' => 'official',
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $result = AcademicResult::where('user_id', $user->id)->first();
    expect($result)->not->toBeNull();
    expect($result->education_level)->toBe('SPM');
    expect($result->spm_as)->toBe(8);
    expect($result->spm_credits)->toBe(6);
    expect($result->cgpa)->toBeNull();
    expect($result->result_status)->toBe('official');
});

test('student can create undergraduate academic result with CGPA', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.75,
        'result_status' => 'official',
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $result = AcademicResult::where('user_id', $user->id)->first();
    expect($result)->not->toBeNull();
    expect($result->education_level)->toBe('Undergraduate');
    expect($result->cgpa)->toBe(3.75);
    expect($result->spm_as)->toBeNull();
    expect($result->spm_credits)->toBeNull();
});

test('student can create academic result with pending status', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Foundation',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.80,
        'result_status' => 'pending',
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $result = AcademicResult::where('user_id', $user->id)->first();
    expect($result->result_status)->toBe('pending');
});

test('student can update existing academic result', function () {
    $user = makeStudent();

    AcademicResult::create([
        'user_id' => $user->id,
        'education_level' => 'SPM',
        'spm_as' => 5,
        'spm_credits' => 4,
        'cgpa' => null,
        'result_status' => 'official',
    ]);

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.50,
        'result_status' => 'official',
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $result = $user->fresh()->academicResult;
    expect($result->education_level)->toBe('Undergraduate');
    expect($result->cgpa)->toBe(3.50);
    expect($result->spm_as)->toBeNull();
});

test('academic result validation requires education level', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => '',
        'spm_as' => 8,
        'spm_credits' => 6,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors('education_level');
});

test('SPM requires SPM As or credits', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'SPM',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => null,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors();
});

test('non-SPM requires CGPA when status is official', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => null,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors('cgpa');
});

test('non-SPM allows missing CGPA when status is pending', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => null,
        'result_status' => 'pending',
    ]);

    $response->assertRedirect(route('student.academic-result.edit'));
    $response->assertSessionHas('success');

    $result = AcademicResult::where('user_id', $user->id)->first();
    expect($result->result_status)->toBe('pending');
});

test('CGPA must be between 0 and 4', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'Undergraduate',
        'cgpa' => 5.0,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors('cgpa');
});

test('SPM As must be between 0 and 12', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'SPM',
        'spm_as' => 13,
        'spm_credits' => 6,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors('spm_as');
});

test('SPM credits must be between 0 and 12', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)->post(route('student.academic-result.update'), [
        'education_level' => 'SPM',
        'spm_as' => 8,
        'spm_credits' => 13,
        'result_status' => 'official',
    ]);

    $response->assertSessionHasErrors('spm_credits');
});

test('academic result requires student role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get(route('student.academic-result.edit'));

    $response->assertForbidden();
});

test('academic result redirects guest to login', function () {
    $response = $this->get(route('student.academic-result.edit'));

    $response->assertRedirect(route('login'));
});