<?php

namespace App\Support;

class EligibilityGap
{
    public static function describe(array $check): ?string
    {
        if ($check['outcome'] !== 'not_met') {
            return null;
        }
        $required = $check['required'];
        $actual = $check['actual'];
        if (is_numeric($required) && is_numeric($actual)) {
            if ($check['key'] === 'min_cgpa') {
                $difference = (int) round((float) $required * 100) - (int) round((float) $actual * 100);

                return 'Your CGPA is '.number_format($difference / 100, 2).' below this requirement.';
            }
            if (str_starts_with($check['key'], 'min_grade_count_') || in_array($check['key'], ['min_spm_as', 'min_spm_credits'], true)) {
                $difference = (int) $required - (int) $actual;

                return 'This condition needs '.$difference.' more qualifying '.($difference === 1 ? 'subject' : 'subjects').'. Stronger grades already count towards this threshold.';
            }
            if ($check['key'] === 'max_household_income') {
                return 'Your recorded household income is RM '.number_format((float) $actual - (float) $required, 2).' above this limit.';
            }
        }

        return $check['message'] ?? 'This requirement is not satisfied by the information provided.';
    }
}
