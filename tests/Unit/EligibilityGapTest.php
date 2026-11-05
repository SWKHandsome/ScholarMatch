<?php

use App\Support\EligibilityGap;

test('numeric explanation refinements use precise differences without suggesting personal fact changes', function () {
    $check = ['outcome' => 'not_met', 'key' => 'min_cgpa', 'required' => '3.50', 'actual' => '3.20'];
    expect(EligibilityGap::describe($check))->toBe('Your CGPA is 0.30 below this requirement.');
    expect(EligibilityGap::describe(array_merge($check, ['outcome' => 'met'])))->toBeNull();
    expect(EligibilityGap::describe(array_merge($check, ['key' => 'max_household_income', 'required' => 3000, 'actual' => 3500])))
        ->toBe('Your recorded household income is RM 500.00 above this limit.');
});
