<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.profile.edit'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('repeated failed logins do not block a subsequent valid login', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    foreach (range(1, 10) as $attempt) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $this->assertGuest();
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
})->with(['student' => User::ROLE_STUDENT, 'admin' => User::ROLE_ADMIN]);
