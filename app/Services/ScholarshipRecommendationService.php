<?php

namespace App\Services;

use App\Models\AcademicResult;
use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScholarshipRecommendationService
{
    public function getRecommendations(User $user): array
    {
        $profile = $user->studentProfile;
        $academicResult = $user->academicResult;

        if (!$profile) {
            return [
                'error' => 'Please complete your student profile first.',
                'recommendations' => [],
            ];
        }

        if (!$academicResult) {
            return [
                'error' => 'Please enter your academic result first.',
                'recommendations' => [],
            ];
        }

        $incomeCategory = IncomeCategory::classifyIncome((float) $profile->household_income);
        $profile->income_category = $incomeCategory;
        $profile->save();

        $scholarships = Scholarship::where('is_active', true)
            ->with('rule')
            ->get();

        $recommendations = [];

        foreach ($scholarships as $scholarship) {
            $rule = $scholarship->rule;

            if (!$rule) {
                continue;
            }

            $hardCheck = $this->checkHardRules($profile, $academicResult, $rule, $scholarship);

            if (!empty($hardCheck['failed'])) {
                $recommendations[] = [
                    'scholarship' => $scholarship,
                    'score' => 0,
                    'status' => 'Not Suitable',
                    'failed_hard_rules' => $hardCheck['failed'],
                    'explanation' => $hardCheck['explanations'],
                    'score_breakdown' => [
                        'academic' => 0,
                        'field' => 0,
                        'institution' => 0,
                        'income' => 0,
                    ],
                    'is_preliminary' => $academicResult->result_status === 'pending',
                ];
                continue;
            }

            $softScore = $this->calculateSoftScore($profile, $academicResult, $rule);
            $totalScore = array_sum($softScore['breakdown']);
            $status = $this->classifyStatus($totalScore);

            $recommendations[] = [
                'scholarship' => $scholarship,
                'score' => $totalScore,
                'status' => $status,
                'failed_hard_rules' => [],
                'explanation' => array_merge($hardCheck['explanations'], $softScore['explanations']),
                'score_breakdown' => $softScore['breakdown'],
                'is_preliminary' => $academicResult->result_status === 'pending',
            ];
        }

        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'error' => null,
            'recommendations' => $recommendations,
        ];
    }

    public function checkHardRules(
        StudentProfile $profile,
        AcademicResult $academicResult,
        ScholarshipRule $rule,
        Scholarship $scholarship
    ): array {
        $failed = [];
        $explanations = [];

        if ($rule->required_nationality && $profile->nationality !== $rule->required_nationality) {
            $failed[] = 'nationality';
            $explanations[] = "This scholarship requires {$rule->required_nationality} nationality.";
        } else {
            $explanations[] = 'You meet the nationality requirement.';
        }

        if ($rule->income_rule_type === 'hard') {
            if ($rule->required_income_category && $profile->income_category !== $rule->required_income_category) {
                $failed[] = 'income_category';
                $explanations[] = "This scholarship is only open to {$rule->required_income_category} students.";
            } else {
                $explanations[] = 'You meet the income category requirement.';
            }

            if ($rule->max_household_income && $profile->household_income > $rule->max_household_income) {
                $failed[] = 'max_household_income';
                $explanations[] = "Your household income exceeds the maximum allowed (RM {$rule->max_household_income}).";
            } else {
                $explanations[] = 'Your household income is within the allowed range.';
            }
        }

        if ($rule->study_level_rule_type === 'hard') {
            if ($rule->required_study_level && $academicResult->education_level !== $rule->required_study_level) {
                $failed[] = 'study_level';
                $explanations[] = "This scholarship is only for {$rule->required_study_level} students.";
            } else {
                $explanations[] = 'Your education level matches the scholarship requirement.';
            }
        }

        if ($scholarship->isExpired()) {
            $failed[] = 'deadline';
            $explanations[] = 'This scholarship has passed its deadline.';
        } else {
            $explanations[] = 'This scholarship is still open for applications.';
        }

        if ($rule->field_rule_type === 'hard') {
            if ($rule->required_field_of_study && $profile->field_of_study !== $rule->required_field_of_study) {
                $failed[] = 'field_of_study';
                $explanations[] = "This scholarship requires {$rule->required_field_of_study} field of study.";
            } else {
                $explanations[] = 'Your field of study matches the scholarship requirement.';
            }
        }

        if ($rule->institution_rule_type === 'hard') {
            if ($rule->required_institution_type && $profile->institution_type !== $rule->required_institution_type) {
                $failed[] = 'institution_type';
                $explanations[] = "This scholarship is only for {$rule->required_institution_type} students.";
            } else {
                $explanations[] = 'Your institution type matches the scholarship requirement.';
            }
        }

        return [
            'failed' => $failed,
            'explanations' => $explanations,
        ];
    }

    public function calculateSoftScore(
        StudentProfile $profile,
        AcademicResult $academicResult,
        ScholarshipRule $rule
    ): array {
        $breakdown = [
            'academic' => 0,
            'field' => 0,
            'institution' => 0,
            'income' => 0,
        ];
        $explanations = [];

        $academicScore = $this->calculateAcademicScore($academicResult, $rule);
        $breakdown['academic'] = $academicScore['score'];
        $explanations[] = $academicScore['explanation'];

        if ($rule->field_rule_type === 'soft') {
            $fieldScore = $this->calculateFieldScore($profile, $rule);
            $breakdown['field'] = $fieldScore['score'];
            $explanations[] = $fieldScore['explanation'];
        } else {
            $explanations[] = 'Field of study matching is not a scoring criterion for this scholarship.';
        }

        if ($rule->institution_rule_type === 'soft') {
            $institutionScore = $this->calculateInstitutionScore($profile, $rule);
            $breakdown['institution'] = $institutionScore['score'];
            $explanations[] = $institutionScore['explanation'];
        } else {
            $explanations[] = 'Institution type matching is not a scoring criterion for this scholarship.';
        }

        if ($rule->income_rule_type === 'soft') {
            $incomeScore = $this->calculateIncomeScore($profile, $rule);
            $breakdown['income'] = $incomeScore['score'];
            $explanations[] = $incomeScore['explanation'];
        } else {
            $explanations[] = 'Income priority matching is not a scoring criterion for this scholarship.';
        }

        return [
            'breakdown' => $breakdown,
            'explanations' => $explanations,
        ];
    }

    private function calculateAcademicScore(AcademicResult $academicResult, ScholarshipRule $rule): array
    {
        $score = 0;
        $explanation = '';

        if ($academicResult->education_level === 'SPM') {
            if ($rule->min_spm_as && $academicResult->spm_as >= $rule->min_spm_as) {
                $score = 40;
                $explanation = "Your SPM results ({$academicResult->spm_as} As) meet the minimum requirement ({$rule->min_spm_as} As).";
            } elseif ($rule->min_spm_credits && $academicResult->spm_credits >= $rule->min_spm_credits) {
                $score = 30;
                $explanation = "Your SPM credits ({$academicResult->spm_credits}) meet the minimum requirement ({$rule->min_spm_credits} credits).";
            } else {
                $explanation = 'Your SPM results do not meet the minimum academic requirement.';
            }
        } else {
            if ($rule->min_cgpa && $academicResult->cgpa >= $rule->min_cgpa) {
                $score = 40;
                $explanation = "Your CGPA ({$academicResult->cgpa}) meets the minimum requirement ({$rule->min_cgpa}).";
            } else {
                $explanation = 'Your CGPA does not meet the minimum academic requirement.';
            }
        }

        if ($academicResult->result_status === 'pending') {
            $explanation .= ' (Preliminary - results pending)';
        }

        return ['score' => $score, 'explanation' => $explanation];
    }

    private function calculateFieldScore(StudentProfile $profile, ScholarshipRule $rule): array
    {
        if (!$rule->required_field_of_study) {
            return ['score' => 25, 'explanation' => 'No specific field of study required.'];
        }

        if ($profile->field_of_study === $rule->required_field_of_study) {
            return ['score' => 25, 'explanation' => "Your field of study ({$profile->field_of_study}) matches the scholarship requirement."];
        }

        return ['score' => 0, 'explanation' => "Your field of study ({$profile->field_of_study}) does not match the required field ({$rule->required_field_of_study})."];
    }

    private function calculateInstitutionScore(StudentProfile $profile, ScholarshipRule $rule): array
    {
        if (!$rule->required_institution_type) {
            return ['score' => 20, 'explanation' => 'No specific institution type required.'];
        }

        if ($profile->institution_type === $rule->required_institution_type) {
            return ['score' => 20, 'explanation' => "Your institution type ({$profile->institution_type}) matches the scholarship requirement."];
        }

        return ['score' => 0, 'explanation' => "Your institution type ({$profile->institution_type}) does not match the required type ({$rule->required_institution_type})."];
    }

    private function calculateIncomeScore(StudentProfile $profile, ScholarshipRule $rule): array
    {
        if (!$rule->required_income_category) {
            return ['score' => 15, 'explanation' => 'No specific income category required.'];
        }

        if ($profile->income_category === $rule->required_income_category) {
            return ['score' => 15, 'explanation' => "Your income category ({$profile->income_category}) matches the scholarship priority."];
        }

        $priorityOrder = ['B40' => 3, 'M40' => 2, 'T20' => 1];
        $studentPriority = $priorityOrder[$profile->income_category] ?? 0;
        $requiredPriority = $priorityOrder[$rule->required_income_category] ?? 0;

        if ($studentPriority > $requiredPriority) {
            return ['score' => 10, 'explanation' => "Your income category ({$profile->income_category}) has higher priority than required ({$rule->required_income_category})."];
        }

        return ['score' => 0, 'explanation' => "Your income category ({$profile->income_category}) does not match the required category ({$rule->required_income_category})."];
    }

    private function classifyStatus(int $score): string
    {
        if ($score >= 80) {
            return 'Eligible';
        }
        if ($score >= 50) {
            return 'Partially Eligible';
        }
        return 'Not Suitable';
    }
}