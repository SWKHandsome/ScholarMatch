<?php

use App\Models\AcademicResult;
use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;

function makeAdmin(): User
{
    return User::factory()->create(['role' => User::ROLE_ADMIN]);
}

function makeStudent(array $profileAttributes = [], array $academicAttributes = []): User
{
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    StudentProfile::create(array_merge([
        'user_id' => $user->id,
        'nationality' => 'Malaysian',
        'state' => 'Selangor',
        'household_income' => 2500,
        'number_of_dependents' => 3,
        'income_category' => 'B40',
        'institution_type' => 'Public University',
        'field_of_study' => 'Engineering',
    ], $profileAttributes));

    AcademicResult::create(array_merge([
        'user_id' => $user->id,
        'education_level' => 'Undergraduate',
        'spm_as' => null,
        'spm_credits' => null,
        'cgpa' => 3.50,
        'result_status' => 'official',
    ], $academicAttributes));

    return $user->fresh(['studentProfile', 'academicResult']);
}

function makeScholarship(array $attributes = [], array $ruleAttributes = []): Scholarship
{
    $scholarship = Scholarship::create(array_merge([
        'name' => 'Test Scholarship',
        'provider' => 'Test Provider',
        'description' => 'Test description',
        'award_type' => 'Full Scholarship',
        'deadline' => now()->addMonth()->toDateString(),
        'application_link' => 'https://example.com',
        'is_active' => true,
    ], $attributes));

    ScholarshipRule::create(array_merge([
        'scholarship_id' => $scholarship->id,
        'required_nationality' => 'Malaysian',
        'required_study_level' => 'Undergraduate',
        'required_income_category' => 'B40',
        'max_household_income' => 3169.00,
        'min_spm_as' => null,
        'min_spm_credits' => null,
        'min_cgpa' => 3.00,
        'required_field_of_study' => 'Engineering',
        'required_institution_type' => 'Public University',
        'income_rule_type' => 'soft',
        'study_level_rule_type' => 'hard',
        'field_rule_type' => 'soft',
        'institution_rule_type' => 'soft',
        'rule_payload' => null,
    ], $ruleAttributes));

    return $scholarship->fresh(['rule']);
}

function seedIncomeCategories(): void
{
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => \Database\Seeders\IncomeCategorySeeder::class,
    ]);
}