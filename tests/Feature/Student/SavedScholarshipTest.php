<?php

use App\Models\SavedScholarship;
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

test('saved cards use current eligibility explanations without writing recommendation logs', function () {
    $user = makeStudent();
    $eligible = makeScholarship(['name' => 'Accessible Award']);
    $higherCgpa = makeScholarship(['name' => 'Higher CGPA Award'], ['min_cgpa' => 3.90]);
    foreach ([$eligible, $higherCgpa] as $scholarship) {
        SavedScholarship::create(['user_id' => $user->id, 'scholarship_id' => $scholarship->id]);
    }

    $response = $this->actingAs($user)->get(route('student.saved-scholarships.index'));
    $response->assertOk()->assertSee('View Explanation')->assertSee('Not Eligible');
    $response->assertViewHas('recommendations', fn ($items) => $items[$eligible->id]['status'] === 'Eligible'
        && $items[$higherCgpa->id]['status'] === 'Not Eligible');
    $response->assertSee(route('student.saved-scholarships.destroy', $higherCgpa), false);
    $this->assertDatabaseCount('recommendation_logs', 0);

    $user->academicResult->update(['cgpa' => 4.00]);
    $user->unsetRelation('academicResult');
    $this->get(route('student.saved-scholarships.index'))
        ->assertOk()
        ->assertViewHas('recommendations', fn ($items) => $items[$higherCgpa->id]['status'] === 'Eligible');
});

test('saved unavailable awards remain visible and removable without an eligibility claim', function () {
    $user = makeStudent();
    $expired = makeScholarship(['name' => 'Expired Award', 'deadline' => today()->subDay()]);
    $inactive = makeScholarship(['name' => 'Inactive Award', 'is_active' => false]);
    $unconfigured = makeScholarship(['name' => 'Unconfigured Award']);
    $unconfigured->rule()->delete();
    foreach ([$expired, $inactive, $unconfigured] as $scholarship) {
        SavedScholarship::create(['user_id' => $user->id, 'scholarship_id' => $scholarship->id]);
    }

    $response = $this->actingAs($user)->get(route('student.saved-scholarships.index'));
    $response->assertOk()->assertSee('Expired Award')->assertSee('Inactive Award')->assertSee('Unconfigured Award')
        ->assertSee('This scholarship has expired.')->assertSee('This scholarship is no longer active.')
        ->assertSee('Eligibility is unavailable')->assertDontSee('View Explanation')->assertDontSee('Apply Now');
    $response->assertViewHas('recommendations', fn ($items) => $items->isEmpty());
    foreach ([$expired, $inactive, $unconfigured] as $scholarship) {
        $response->assertSee(route('student.saved-scholarships.destroy', $scholarship), false);
    }
});

test('saved cards retain ten per page and exclude other students bookmarks', function () {
    $user = makeStudent();
    foreach (range(1, 11) as $number) {
        $scholarship = makeScholarship(['name' => 'Saved Award '.$number]);
        SavedScholarship::create(['user_id' => $user->id, 'scholarship_id' => $scholarship->id]);
    }
    $other = makeStudent();
    $privateAward = makeScholarship(['name' => 'Other Student Bookmark']);
    SavedScholarship::create(['user_id' => $other->id, 'scholarship_id' => $privateAward->id]);

    $this->actingAs($user)->get(route('student.saved-scholarships.index'))
        ->assertOk()->assertDontSee('Other Student Bookmark')
        ->assertViewHas('savedScholarships', fn ($items) => $items->count() === 10 && $items->total() === 11);
    $this->get(route('student.saved-scholarships.index', ['page' => 2]))
        ->assertOk()->assertDontSee('Other Student Bookmark')
        ->assertViewHas('savedScholarships', fn ($items) => $items->count() === 1 && $items->total() === 11);
});

test('removing a bookmark preserves the scholarship and other students bookmarks', function () {
    $user = makeStudent();
    $other = makeStudent();
    $scholarship = makeScholarship();
    foreach ([$user, $other] as $student) {
        SavedScholarship::create(['user_id' => $student->id, 'scholarship_id' => $scholarship->id]);
    }

    $this->actingAs($user)->delete(route('student.saved-scholarships.destroy', $scholarship))->assertRedirect();
    $this->assertDatabaseMissing('saved_scholarships', ['user_id' => $user->id, 'scholarship_id' => $scholarship->id]);
    $this->assertDatabaseHas('saved_scholarships', ['user_id' => $other->id, 'scholarship_id' => $scholarship->id]);
    $this->assertDatabaseHas('scholarships', ['id' => $scholarship->id]);
});
