<?php

use App\Models\RecommendationLog;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Support\Facades\DB;

test('migration preserves legacy evaluations and academic data without inventing new answers', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();
    $migration = require database_path('migrations/2026_10_19_000000_add_decision_based_recommendations.php');
    $migration->down();
    DB::table('recommendation_logs')->insert([
        'user_id' => $user->id, 'scholarship_id' => $scholarship->id,
        'score' => 75, 'status' => 'Partially Eligible', 'failed_hard_rules' => '[]',
        'score_breakdown' => '{"academic":40,"field":0,"institution":20,"income":15}',
        'explanation' => '["Previous explanation"]', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $migration->up();
    $log = RecommendationLog::first();
    expect($log->legacy_evaluation['score'])->toBe(75);
    expect($log->legacy_evaluation['status'])->toBe('Partially Eligible');
    expect($log->isCurrentEvaluation())->toBeFalse();
    expect($user->fresh()->academicResult->cgpa)->toBe('3.50');
    expect($user->fresh()->academicResult->funding_study_level)->toBeNull();
    expect($user->fresh()->academicResult->study_status)->toBeNull();
    $log->update(['evaluation_version' => ScholarshipRecommendationService::ALGORITHM_VERSION,
        'status' => 'Eligible', 'explanation' => ['Current explanation']]);
    $migration->down();
    expect(DB::table('recommendation_logs')->first()->status)->toBe('Partially Eligible');
    expect(DB::table('recommendation_logs')->first()->score)->toBe(75);
    $migration->up();
});
