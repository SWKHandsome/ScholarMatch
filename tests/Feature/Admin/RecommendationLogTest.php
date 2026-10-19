<?php

use App\Models\RecommendationLog;
use App\Services\ScholarshipRecommendationService;

beforeEach(fn () => seedIncomeCategories());

function decisionLog(array $profile = [], array $scholarship = []): RecommendationLog
{
    $user = makeStudent($profile);
    makeScholarship($scholarship);
    $service = app(ScholarshipRecommendationService::class);
    $service->storeRecommendationLogs($user, $service->getRecommendations($user)['recommendations']);

    return RecommendationLog::where('user_id', $user->id)->first();
}

test('admin sees current decision explanations without scores', function () {
    $log = decisionLog();
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.show', $log))->assertOk()
        ->assertSee('Recommendation Log Details')->assertSee('Eligible')
        ->assertSee('Requirement Explanation')->assertSee('Nationality')
        ->assertDontSee('Score Breakdown')->assertDontSee('Match Score');
});

test('admin can filter decisions and verification flags', function () {
    decisionLog([], ['name' => 'Interview Award', 'special_requirements' => 'Interview']);
    decisionLog(['nationality' => 'Singaporean'], ['name' => 'Rejected Award']);
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.index', ['status' => 'eligible_verification']))
        ->assertOk()->assertViewHas('logs', fn ($logs) => $logs->every(fn ($log) => $log->status === 'Eligible' && $log->requires_verification));
    $this->get(route('admin.recommendation-logs.index', ['status' => 'Not Eligible']))
        ->assertOk()->assertViewHas('logs', fn ($logs) => $logs->every(fn ($log) => $log->status === 'Not Eligible'));
});

test('admin can search by student and scholarship', function () {
    $log = decisionLog([], ['name' => 'Future Leaders Award']);
    $log->user->update(['name' => 'Aisha Ahmad', 'email' => 'aisha@example.test']);
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.index', ['search' => 'aisha@example.test']))
        ->assertOk()->assertSee('Aisha Ahmad');
    $this->get(route('admin.recommendation-logs.index', ['search' => 'Future Leaders']))
        ->assertOk()->assertSee('Future Leaders Award');
});

test('failed requirement detail is explicit', function () {
    $log = decisionLog(['nationality' => 'Singaporean']);
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.show', $log))->assertOk()
        ->assertSee('Not Eligible')->assertSee('Failed Requirements')->assertSee('nationality');
});

test('previous evaluations are labelled rather than relabelled as current eligibility', function () {
    $log = decisionLog();
    $log->update(['evaluation_version' => 'legacy-score-v4', 'status' => 'Partially Eligible']);
    $this->actingAs(makeAdmin())->get(route('admin.recommendation-logs.show', $log))->assertOk()
        ->assertSee('Previous Evaluation')->assertSee('re-evaluation required')->assertDontSee('Partially Eligible');
    $this->get(route('admin.recommendation-logs.index', ['status' => 'Eligible']))
        ->assertOk()->assertViewHas('logs', fn ($logs) => $logs->isEmpty());
});

test('recommendation log routes retain admin access control', function () {
    $log = decisionLog();
    $this->get(route('admin.recommendation-logs.index'))->assertRedirect(route('login'));
    $this->actingAs(makeStudent())->get(route('admin.recommendation-logs.show', $log))->assertForbidden();
});
