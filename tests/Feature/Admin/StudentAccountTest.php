<?php

use App\Models\RecommendationLog;
use App\Models\SavedScholarship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function studentAccountPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test Student', 'email' => 'student@example.com',
        'password' => 'password', 'password_confirmation' => 'password',
    ], $overrides);
}

test('student management lists only students with search and ten item pagination', function () {
    $admin = makeAdmin();
    $hidden = User::factory()->create(['role' => 'admin', 'name' => 'Hidden Administrator', 'email' => 'hidden-admin@example.com']);
    User::factory()->count(11)->create(['role' => 'student']);
    $target = User::factory()->create(['role' => 'student', 'name' => 'Wong Student', 'email' => 'wong@college.edu.my']);

    $this->actingAs($admin)->get(route('admin.students.index'))
        ->assertOk()->assertViewHas('students', fn ($students) => $students->count() === 10 && $students->total() === 12);
    $this->get(route('admin.students.index', ['page' => 2]))
        ->assertOk()->assertViewHas('students', fn ($students) => $students->count() === 2);
    foreach (['Wong', 'college.edu.my'] as $search) {
        $this->get(route('admin.students.index', ['search' => $search]))
            ->assertOk()->assertViewHas('students', fn ($students) => $students->total() === 1 && $students->first()->is($target));
    }
    $this->get(route('admin.students.index', ['search' => $hidden->email]))
        ->assertOk()->assertSee('No matching students')
        ->assertViewHas('students', fn ($students) => $students->total() === 0);
});

test('admin creates a student without becoming that student or accepting role injection', function () {
    $admin = makeAdmin();
    $this->actingAs($admin)->get(route('admin.students.create'))->assertOk();
    $this->post(route('admin.students.store'), studentAccountPayload([
        'name' => '  王小明  ', 'email' => ' STUDENT@EXAMPLE.COM ', 'role' => 'admin',
        'email_verified_at' => now()->toDateTimeString(), 'household_income' => 999,
    ]))->assertSessionHasNoErrors();

    $student = User::where('email', 'student@example.com')->firstOrFail();
    expect($student->name)->toBe('王小明');
    expect($student->role)->toBe('student');
    expect($student->email_verified_at)->toBeNull();
    expect(Hash::check('password', $student->password))->toBeTrue();
    expect($student->studentProfile)->toBeNull();
    expect($student->academicResult)->toBeNull();
    $this->assertAuthenticatedAs($admin);
    $this->get(route('admin.students.show', $student))->assertOk()->assertSee('has not added a profile');
});

test('admin creation validates names full email domains duplicates and password confirmation', function () {
    $admin = makeAdmin();
    $existing = User::factory()->create(['email' => 'existing@example.com']);
    $this->actingAs($admin)->post(route('admin.students.store'), studentAccountPayload([
        'name' => 'Select * From username', 'email' => 'friend@nihao', 'password_confirmation' => 'wrong',
    ]))->assertSessionHasErrors(['name', 'email', 'password']);
    $this->post(route('admin.students.store'), studentAccountPayload(['email' => ' EXISTING@EXAMPLE.COM ']))
        ->assertSessionHasErrors('email');
    $this->assertDatabaseCount('users', 2);
});

test('admin sees student information without profile or academic edit controls', function () {
    seedIncomeCategories();
    $admin = makeAdmin();
    $student = makeStudent();
    $this->actingAs($admin)->get(route('admin.students.show', $student))
        ->assertOk()->assertSee('Academic Information')->assertSee('Engineering')->assertSee('3.50')
        ->assertDontSee('name="household_income"', false)->assertDontSee('name="cgpa"', false);
    $this->get(route('admin.students.edit', $student))->assertOk()
        ->assertSee('Leave both password fields blank')->assertDontSee('name="role"', false)
        ->assertDontSee('name="household_income"', false)->assertDontSee('name="cgpa"', false);
});

test('admin edits account details while preserving password profile academics and role', function () {
    $admin = makeAdmin();
    $student = makeStudent();
    $originalPassword = $student->password;
    $profile = $student->studentProfile->getAttributes();
    $academic = $student->academicResult->getAttributes();

    $this->actingAs($admin)->patch(route('admin.students.update', $student), [
        'name' => 'Updated Student', 'email' => $student->email,
        'password' => '', 'password_confirmation' => '', 'role' => 'admin',
        'household_income' => 999999, 'cgpa' => 0, 'studentProfile' => ['state' => 'Changed'],
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.students.show', $student));

    $student->refresh();
    expect($student->name)->toBe('Updated Student');
    expect($student->password)->toBe($originalPassword);
    expect($student->role)->toBe('student');
    expect($student->studentProfile->getAttributes())->toBe($profile);
    expect($student->academicResult->getAttributes())->toBe($academic);
});

test('admin credential changes revoke only the target student sessions and reset tokens', function () {
    $admin = makeAdmin();
    $student = User::factory()->create(['role' => 'student']);
    $other = User::factory()->create(['role' => 'student']);
    $remember = $student->remember_token;
    foreach ([$student, $other] as $user) {
        DB::table('sessions')->insert(['id' => 'session-'.$user->id, 'user_id' => $user->id, 'payload' => 'fixture', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'fixture', 'created_at' => now()]);
    }

    $this->actingAs($admin)->patch(route('admin.students.update', $student), studentAccountPayload([
        'email' => ' NEW@EXAMPLE.COM ', 'password' => 'changed-password', 'password_confirmation' => 'changed-password',
    ]))->assertSessionHasNoErrors();

    $updated = $student->fresh();
    expect($updated->email)->toBe('new@example.com');
    expect($updated->email_verified_at)->toBeNull();
    expect($updated->remember_token)->not->toBe($remember);
    expect(Hash::check('changed-password', $updated->password))->toBeTrue();
    $this->assertDatabaseMissing('sessions', ['user_id' => $student->id]);
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $student->email]);
    $this->assertDatabaseHas('sessions', ['user_id' => $other->id]);
    $this->assertDatabaseHas('password_reset_tokens', ['email' => $other->email]);
});

test('admin deletion cascades student data and preserves scholarships and other students', function () {
    $admin = makeAdmin();
    $student = makeStudent();
    $other = makeStudent();
    $award = makeScholarship();
    foreach ([$student, $other] as $user) {
        SavedScholarship::create(['user_id' => $user->id, 'scholarship_id' => $award->id]);
        RecommendationLog::create(['user_id' => $user->id, 'scholarship_id' => $award->id, 'status' => 'Eligible', 'explanation' => []]);
        DB::table('sessions')->insert(['id' => 'session-'.$user->id, 'user_id' => $user->id, 'payload' => 'fixture', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'fixture', 'created_at' => now()]);
    }

    $this->actingAs($admin)->delete(route('admin.students.destroy', $student))
        ->assertRedirect(route('admin.students.index'));

    $this->assertDatabaseMissing('users', ['id' => $student->id]);
    foreach (['student_profiles', 'academic_results', 'saved_scholarships', 'recommendation_logs', 'sessions'] as $table) {
        $this->assertDatabaseMissing($table, ['user_id' => $student->id]);
        $this->assertDatabaseHas($table, ['user_id' => $other->id]);
    }
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $student->email]);
    $this->assertDatabaseHas('scholarships', ['id' => $award->id]);
    $this->assertAuthenticatedAs($admin);
});

test('student routes cannot view update or delete administrator accounts', function () {
    $admin = makeAdmin();
    $target = makeAdmin();
    $this->actingAs($admin);
    foreach (['show', 'edit'] as $action) {
        $this->get(route('admin.students.'.$action, $target))->assertNotFound();
    }
    $this->patch(route('admin.students.update', $target), studentAccountPayload())->assertNotFound();
    $this->delete(route('admin.students.destroy', $target))->assertNotFound();
    expect($target->fresh())->not->toBeNull();
});

test('guests and students cannot access student account management', function () {
    $student = makeStudent();
    $requests = [
        ['get', 'index'], ['get', 'create'], ['post', 'store'], ['get', 'show'],
        ['get', 'edit'], ['patch', 'update'], ['delete', 'destroy'],
    ];
    foreach ($requests as [$method, $action]) {
        $this->{$method}(route('admin.students.'.$action, in_array($action, ['show', 'edit', 'update', 'destroy']) ? $student : []), studentAccountPayload())
            ->assertRedirect(route('login'));
    }
    $this->actingAs($student);
    foreach ($requests as [$method, $action]) {
        $this->{$method}(route('admin.students.'.$action, in_array($action, ['show', 'edit', 'update', 'destroy']) ? $student : []), studentAccountPayload())
            ->assertForbidden();
    }
    expect($student->fresh())->not->toBeNull();
});
