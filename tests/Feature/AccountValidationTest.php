<?php

use App\Models\User;

test('registration rejects names outside the simple name format', function (mixed $name) {
    $this->post('/register', [
        'name' => $name, 'email' => 'student@example.com',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('name');

    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
})->with(['Select * From username', 'Student123', '<script>alert</script>', '---', '   ', str_repeat('A', 101), [['array']]]);

test('registration accepts normal international names and normalizes email', function (string $name) {
    $this->post('/register', [
        'name' => '  '.$name.'  ', 'email' => '  Student+FYP@Example.COM  ',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors()->assertRedirect(route('student.profile.edit'));

    $student = User::firstOrFail();
    expect($student->name)->toBe($name);
    expect($student->email)->toBe('student+fyp@example.com');
    expect($student->role)->toBe('student');
    $this->get(route('student.profile.edit'))->assertOk()->assertSee($name);
})->with(['Soo Wei Kang', '王小明', "O'Connor", 'Raj A/L Kumar', 'Nur-Aisyah', 'Dr. Tan', '李']);

test('registration requires a complete email domain', function (string $email) {
    $this->post('/register', [
        'name' => 'Test Student', 'email' => $email,
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 0);
})->with(['friend@nihao', 'friend@gmail', 'friend@@gmail.com', 'friend@-bad.com', 'friend@gmail..com', 'friend@domain.123']);

test('common personal and college email formats work without DNS lookups', function (string $email) {
    $this->post('/register', [
        'name' => 'Test Student', 'email' => $email,
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => $email]);
})->with(['student@gmail.com', 'student@yahoo.com', 'student@sc.edu.my', 'student@nihao.com', 'student@example.test']);

test('normalized duplicate emails are rejected', function () {
    User::factory()->create(['email' => 'student@example.com']);

    $this->post('/register', [
        'name' => 'Test Student', 'email' => ' STUDENT@EXAMPLE.COM ',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 1);
});

test('login normalizes email but preserves the existing password policy', function () {
    $student = User::factory()->create(['email' => 'student@example.com', 'role' => 'student']);

    $this->post('/login', ['email' => ' STUDENT@EXAMPLE.COM ', 'password' => 'password'])
        ->assertSessionHasNoErrors()->assertRedirect(route('student.profile.edit'));

    $this->assertAuthenticatedAs($student);
});

test('account profile updates cannot bypass name and email validation', function () {
    $student = User::factory()->create(['name' => 'Original Student', 'role' => 'student']);

    $this->actingAs($student)->patch('/profile', [
        'name' => 'Select * From username', 'email' => 'friend@nihao',
    ])->assertSessionHasErrors(['name', 'email']);

    expect($student->fresh()->name)->toBe('Original Student');
});
