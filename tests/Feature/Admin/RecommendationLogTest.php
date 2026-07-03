<?php

use App\Models\AcademicResult;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('admin can view recommendation logs index', function () {
    $admin = makeAdmin();
    $student = makeStudent();
    $scholarship = makeScholarship();

    RecommendationLog::create([
        'user_id' => $student->id,
        'scholarship_id' => $scholarship->id,
        'score' => 85,
        'status' => 'Eligible',
        'failed_hard_rules' => [],
        'explanation' => ['You meet the nationality requirement.'],
        'score_breakdown' => ['academic' => 40, 'field' => 25, 'institution' => 20, 'income' => 15],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.recommendation-logs.index'));

    $response->assertOk();
    $response->assertSee('Recommendation Logs');
    $response->assertSee('Test Scholarship');
    $response->assertSee('Eligible');
    $response->assertSee('85');
});

test('admin can view recommendation log detail', function () {
    $admin = makeAdmin();
    $student = makeStudent();
    $scholarship = makeScholarship();

    $log = RecommendationLog::create([
        'user_id' => $student->id,
        'scholarship_id' => $scholarship->id,
        'score' => 75,
        'status' => 'Partially Eligible',
        'failed_hard_rules' => [],
        'explanation' => ['You meet the nationality requirement.', 'Your field of study partially matches.'],
        'score_breakdown' => ['academic' => 40, 'field' => 15, 'institution' => 20, 'income' => 15],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.recommendation-logs.show', $log));

    $response->assertOk();
    $response->assertSee('Recommendation Log Details');
    $response->assertSee('Test Scholarship');
    $response->assertSee('Partially Eligible');
    $response->assertSee('75');
    $response->assertSee('Score Breakdown');
    $response->assertSee('Academic');
    $response->assertSee('40');
    $response->assertSee('Field');
    $response->assertSee('15');
    $response->assertSee('Explanation');
    $response->assertSee('You meet the nationality requirement');
});

test('recommendation log detail shows failed hard rules', function () {
    $admin = makeAdmin();
    $student = makeStudent(['nationality' => 'Singaporean']);
    $scholarship = makeScholarship();

    $log = RecommendationLog::create([
        'user_id' => $student->id,
        'scholarship_id' => $scholarship->id,
        'score' => 0,
        'status' => 'Not Suitable',
        'failed_hard_rules' => ['nationality'],
        'explanation' => ['This scholarship is only open to Malaysian students.'],
        'score_breakdown' => ['academic' => 0, 'field' => 0, 'institution' => 0, 'income' => 0],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.recommendation-logs.show', $log));

    $response->assertOk();
    $response->assertSee('Not Suitable');
    $response->assertSee('Failed Hard Rules');
    $response->assertSee('nationality');
});

test('recommendation logs require admin role', function () {
    $student = makeStudent();

    $response = $this->actingAs($student)->get(route('admin.recommendation-logs.index'));

    $response->assertForbidden();
});

test('recommendation logs redirect guest to login', function () {
    $response = $this->get(route('admin.recommendation-logs.index'));

    $response->assertRedirect(route('login'));
});

test('recommendation log detail requires admin role', function () {
    $student = makeStudent();
    $scholarship = makeScholarship();

    $log = RecommendationLog::create([
        'user_id' => $student->id,
        'scholarship_id' => $scholarship->id,
        'score' => 85,
        'status' => 'Eligible',
        'failed_hard_rules' => [],
        'explanation' => [],
        'score_breakdown' => [],
    ]);

    $otherStudent = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($otherStudent)->get(route('admin.recommendation-logs.show', $log));

    $response->assertForbidden();
});