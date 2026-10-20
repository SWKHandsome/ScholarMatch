<?php

use App\Models\RecommendationLog;
use App\Models\User;

beforeEach(fn () => seedIncomeCategories());

test('matching results show eligibility and no scoring components', function () {
    $user = makeStudent();
    makeScholarship();
    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()
        ->assertSee('Test Scholarship')->assertSee('Eligible')->assertSee('View Details')
        ->assertSee('Apply Now')->assertDontSee('Score Breakdown')->assertDontSee('Partially Eligible');
});

test('special verification is an accessible expandable warning within eligible results', function () {
    $user = makeStudent();
    makeScholarship(['special_requirements' => 'Attend an interview.']);
    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()
        ->assertSee('Attend an interview.')->assertSee('Special requirements need verification')
        ->assertSee('<details', false)->assertSee('<summary', false);
    expect(RecommendationLog::first()->requires_verification)->toBeTrue();
});

test('failed requirements prevent eligibility and suppress the special warning', function () {
    $user = makeStudent([], ['cgpa' => 2]);
    $scholarship = makeScholarship(['special_requirements' => 'Interview']);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))->assertOk()
        ->assertSee('Not Eligible - Failed Requirements')->assertSee('data-explanation-status="failed"', false)
        ->assertDontSee('Special requirements need verification')->assertDontSee('Apply Now');
});

test('detail shows the current evaluation instead of an old log', function () {
    $user = makeStudent(['nationality' => 'Non-Malaysian']);
    $scholarship = makeScholarship();
    RecommendationLog::create(['user_id' => $user->id, 'scholarship_id' => $scholarship->id,
        'status' => 'Eligible', 'explanation' => ['Old result']]);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))->assertOk()
        ->assertSee('Not Eligible')->assertDontSee('Old result')->assertSee('Requirement Explanation');
});

test('pending and legacy academic records show an update prompt not eligibility', function () {
    $user = makeStudent([], ['result_status' => 'pending']);
    makeScholarship();
    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()
        ->assertSee('Complete your academic information')->assertDontSee('Test Scholarship');
});

test('missing student information produces a clear completion prompt', function () {
    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()->assertSee('Please complete your student profile');
    $user = makeStudent();
    $user->academicResult()->delete();
    $this->actingAs($user->fresh())->get(route('student.recommendations'))->assertOk()->assertSee('Please enter your academic result');
});

test('unrestricted criteria are explicitly not applicable on the detail page', function () {
    $user = makeStudent();
    $scholarship = makeScholarship([], ['required_income_category' => null, 'max_household_income' => null]);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))
        ->assertOk()->assertSee('Not Applicable')->assertDontSee('Match score');
});

test('special requirement text is escaped instead of interpreted as markup', function () {
    $user = makeStudent();
    $scholarship = makeScholarship(['special_requirements' => '<script>alert("x")</script>']);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))
        ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(', false);
});

test('missing and closed scholarships redirect to current recommendations', function () {
    $user = makeStudent();
    $this->actingAs($user)->get(route('student.recommendations.show', 999999))
        ->assertRedirect(route('student.recommendations'));
    $scholarship = makeScholarship(['deadline' => today()->subDay()->toDateString()]);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))
        ->assertRedirect(route('student.recommendations'));
});

test('recommendation routes retain role protection', function () {
    $scholarship = makeScholarship();
    $this->get(route('student.recommendations.show', $scholarship))->assertRedirect(route('login'));
    $this->actingAs(makeAdmin())->get(route('student.recommendations.show', $scholarship))->assertForbidden();
});
