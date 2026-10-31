<?php

use App\Models\AcademicResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('grade migration preserves existing academic values and routes without inventing grade counts', function () {
    $migration = require database_path('migrations/2026_10_31_000000_add_qualification_grade_counts.php');
    $migration->down();
    $cgpaStudent = makeStudent();
    $spmStudent = User::factory()->create(['role' => 'student']);
    DB::table('academic_results')->insert([
        'user_id' => $spmStudent->id, 'education_level' => 'SPM', 'study_status' => 'completed',
        'spm_as' => 8, 'spm_credits' => 9, 'result_status' => 'official', 'funding_study_level' => 'Foundation',
        'institution_type' => 'Public University', 'field_of_study' => 'Engineering', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $award = makeScholarship();
    $award->rule->qualificationRoutes()->create(['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3.25]);
    $before = DB::table('academic_results')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $migration->up();
    foreach ($before as $row) {
        expect((array) DB::table('academic_results')->find($row['id']))->toMatchArray($row);
    }
    expect(DB::table('academic_results')->count())->toBe(2);
    $legacy = $spmStudent->fresh()->academicResult;
    expect($legacy->grade_counts)->toBeNull()->and($legacy->spm_as)->toBe(8)->and($legacy->spm_credits)->toBe(9)
        ->and($legacy->isReadyForRecommendations())->toBeFalse();
    expect($cgpaStudent->fresh()->academicResult->isReadyForRecommendations())->toBeTrue();
    expect($award->fresh()->rule->qualificationRoutes->first()->min_cgpa)->toBe('3.25');
    $new = User::factory()->create(['role' => 'student']);
    AcademicResult::create(['user_id' => $new->id, 'education_level' => 'UEC', 'grading_scale' => 'uec', 'grade_counts' => ['A1' => 5]]);
    expect($new->fresh()->academicResult->grade_counts)->toBe(['A1' => 5]);
});

test('migration rollback refuses to discard new result data', function () {
    $user = makeStudent([], ['education_level' => 'A-Level', 'grading_scale' => 'a_level', 'grade_counts' => ['A' => 3]]);
    $migration = require database_path('migrations/2026_10_31_000000_add_qualification_grade_counts.php');
    expect(fn () => $migration->down())->toThrow(RuntimeException::class, 'preserve new qualification results');
    expect($user->fresh()->academicResult->grade_counts)->toBe(['A' => 3]);
});
