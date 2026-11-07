<?php

use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Support\Facades\DB;

test('programme migration preserves values records and eligibility without creating academic answers', function () {
    seedIncomeCategories();
    $user = makeStudent();
    $profileOnly = User::factory()->create(['role' => 'student']);
    $profileOnly->studentProfile()->create([
        'nationality' => 'Malaysian', 'state' => 'Johor', 'household_income' => 2500,
        'number_of_dependents' => 1, 'income_category' => 'B40',
    ]);
    makeScholarship();
    $before = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'][0]['status'];
    $migration = require database_path('migrations/2026_10_20_000000_move_programme_details_to_academic_results.php');
    $migration->down();
    DB::table('student_profiles')->where('user_id', $profileOnly->id)->update([
        'institution_type' => 'Private University', 'field_of_study' => 'Business',
    ]);
    $counts = [DB::table('student_profiles')->count(), DB::table('academic_results')->count()];
    $migration->up();
    expect([DB::table('student_profiles')->count(), DB::table('academic_results')->count()])->toBe($counts);
    $academic = $user->fresh()->academicResult;
    expect($academic->institution_type)->toBe('Public University')->and($academic->field_of_study)->toBe('Engineering');
    expect($academic->cgpa)->toBe('3.50')->and($academic->education_level)->toBe('Undergraduate');
    expect($profileOnly->fresh()->academicResult)->toBeNull();
    expect($profileOnly->fresh()->studentProfile->field_of_study)->toBe('Business');
    expect(app(ScholarshipRecommendationService::class)->getRecommendations($user->fresh())['recommendations'][0]['status'])->toBe($before);
});

test('programme migration rollback restores updated academic selections to the legacy profile', function () {
    $user = makeStudent([], ['institution_type' => 'Private University', 'field_of_study' => 'Business']);
    $migration = require database_path('migrations/2026_10_20_000000_move_programme_details_to_academic_results.php');
    $migration->down();
    $profile = DB::table('student_profiles')->where('user_id', $user->id)->first();
    expect($profile->institution_type)->toBe('Private University')->and($profile->field_of_study)->toBe('Business');
    $migration->up();
    expect($user->fresh()->academicResult->field_of_study)->toBe('Business');
});
