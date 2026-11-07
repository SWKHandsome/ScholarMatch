<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Models\StudentProfile;
use App\Support\StudentOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);
        $incomeCategories = IncomeCategory::configuredCategories();
        $onboarding = StudentOnboarding::nextRoute($user) !== null;
        $income = old('household_income', $profile->household_income);
        $incomeValue = is_scalar($income) ? (string) $income : '';
        $validIncome = Validator::make(['income' => $incomeValue], ['income' => ['required', ...IncomeCategory::amountRules()]])->passes();
        $category = $validIncome ? IncomeCategory::matchingCategory((float) $incomeValue, $incomeCategories) : null;
        $incomeHeading = $category ? 'Your income category: '.$category->name : ($incomeValue === '' ? 'Income category' : ($validIncome ? 'Income category unavailable' : 'Check the income amount'));
        $incomeDetail = $category ? 'Current range: '.$category->rangeLabel() : ($incomeValue === ''
            ? 'Enter your monthly household income to see its category.'
            : (! $validIncome ? 'Enter a non-negative amount with up to 2 decimal places.'
                : ($incomeCategories->isEmpty() ? 'No income ranges are configured. Please contact the administrator.' : 'No category matches this amount. Please contact the administrator.')));
        $incomePreview = [
            'value' => $incomeValue,
            'categories' => $incomeCategories->map(fn ($item) => [
                'name' => $item->name, 'min' => $item->min_income, 'max' => $item->max_income, 'range' => $item->rangeLabel(),
            ])->values()->all(),
        ];

        return view('student.profile.edit', compact('profile', 'incomeCategories', 'onboarding', 'incomeValue', 'incomeHeading', 'incomeDetail', 'incomePreview'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->merge(['state' => StudentProfile::normalizeState($request->input('state'))]);
        $validated = $request->validate(StudentProfile::validationRules());

        $incomeCategory = IncomeCategory::classifyIncome((float) $validated['household_income']);

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, ['income_category' => $incomeCategory])
        );

        $user->unsetRelation('studentProfile');

        return redirect()->route(StudentOnboarding::nextRoute($user) ?? 'student.dashboard')
            ->with('success', 'Profile updated successfully.');
    }
}
