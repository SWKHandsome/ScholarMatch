<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Models\StudentProfile;
use App\Support\StudentOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);
        $incomeCategories = IncomeCategory::orderBy('min_income')->get();
        $onboarding = StudentOnboarding::nextRoute($user) !== null;

        return view('student.profile.edit', compact('profile', 'incomeCategories', 'onboarding'));
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
