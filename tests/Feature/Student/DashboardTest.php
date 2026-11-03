<?php

use App\Models\SavedScholarship;
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

test('dashboard displays a success message from the session', function () {
    $user = makeStudent();

    $response = $this->actingAs($user)
        ->withSession(['success' => 'Academic result updated successfully.'])
        ->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Academic result updated successfully.');
    $response->assertDontSee("{{ session('success') }}", false);
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
    $response->assertSee('Recent Recommendations');
    $response->assertSee('Test Scholarship');
});

test('dashboard marks failed requirement explanations as failed', function () {
    $user = makeStudent([], ['field_of_study' => 'Business', 'education_level' => 'Diploma']);
    makeScholarship();

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Not Eligible');
    $response->assertSee('Supported fields: Engineering; your field: Business.');
    $response->assertSee('data-explanation-status="failed"', false);
});

test('dashboard shows no matches message when no scholarships match', function () {
    $user = makeStudent([
        'nationality' => 'Non-Malaysian',
    ]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('No Scholarships Matched');
});

test('dashboard treats legacy official results without study status as incomplete', function () {
    $user = makeStudent([], ['study_status' => null]);
    $this->actingAs($user)->get(route('student.dashboard'))->assertOk()
        ->assertSee('Incomplete')->assertSee('Add Academic Result');
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

test('dashboard counts all eligible awards independently of the five mixed preview entries', function (int $eligible, int $ineligible) {
    $student = makeStudent();
    for ($index = 0; $index < $eligible; $index++) {
        makeScholarship(['name' => 'Eligible award '.$index, 'special_requirements' => $index === 0 ? 'Interview required.' : null]);
    }
    for ($index = 0; $index < $ineligible; $index++) {
        makeScholarship(['name' => 'Ineligible award '.$index], ['min_cgpa' => 4.00]);
    }

    $this->actingAs($student)->get(route('student.dashboard'))->assertOk()
        ->assertViewHas('eligibleCount', $eligible)
        ->assertViewHas('recentRecommendations', fn ($items) => count($items) === min(5, $eligible + $ineligible))
        ->assertSee($eligible.' eligible')->assertDontSee('5 found');
})->with([
    'four eligible plus rejected awards' => [4, 3],
    'more than five eligible awards' => [7, 2],
    'only rejected awards' => [0, 6],
    'no awards' => [0, 0],
]);

test('dashboard eligible count updates when academic results change while preview size stays five', function () {
    $student = makeStudent();
    for ($index = 0; $index < 4; $index++) {
        makeScholarship(['name' => 'Initial eligible '.$index]);
    }
    makeScholarship(['name' => 'Higher CGPA award'], ['min_cgpa' => 3.75]);
    makeScholarship(['name' => 'Rejected award'], ['min_cgpa' => 4.00]);

    $this->actingAs($student)->get(route('student.dashboard'))->assertOk()->assertViewHas('eligibleCount', 4);
    $student->academicResult->update(['cgpa' => 3.80]);
    $student->unsetRelation('academicResult');
    $this->get(route('student.dashboard'))->assertOk()->assertViewHas('eligibleCount', 5)
        ->assertViewHas('recentRecommendations', fn ($items) => count($items) === 5);
});

test('dashboard redirects guest to login', function () {
    $response = $this->get(route('student.dashboard'));

    $response->assertRedirect(route('login'));
});
