<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);
        $incomeCategories = IncomeCategory::orderBy('min_income')->get();

        return view('student.profile.edit', compact('profile', 'incomeCategories'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nationality' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'household_income' => ['required', 'numeric', 'min:0'],
            'number_of_dependents' => ['required', 'integer', 'min:0'],
            'institution_type' => ['required', 'string', 'max:255'],
            'field_of_study' => ['required', 'string', 'max:255'],
        ]);

        $incomeCategory = IncomeCategory::classifyIncome((float) $validated['household_income']);

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, ['income_category' => $incomeCategory])
        );

        return redirect()->route('student.dashboard')
            ->with('success', 'Profile updated successfully.');
    }
}