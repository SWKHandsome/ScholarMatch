<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicResultController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $academicResult = $user->academicResult ?? new AcademicResult(['user_id' => $user->id]);

        $educationLevels = [
            'SPM',
            'STPM',
            'Foundation',
            'Matriculation',
            'Diploma',
            'Undergraduate',
        ];

        $resultStatuses = ['official', 'pending'];

        return view('student.academic-result.edit', compact('academicResult', 'educationLevels', 'resultStatuses'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'education_level' => ['required', 'string', 'in:SPM,STPM,Foundation,Matriculation,Diploma,Undergraduate'],
            'spm_as' => ['nullable', 'integer', 'min:0', 'max:12'],
            'spm_credits' => ['nullable', 'integer', 'min:0', 'max:12'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'result_status' => ['required', 'string', 'in:official,pending'],
        ]);

        if ($validated['education_level'] === 'SPM') {
            $request->validate([
                'spm_as' => ['required', 'integer', 'min:0', 'max:12'],
                'spm_credits' => ['required', 'integer', 'min:0', 'max:12'],
            ]);
        } else {
            if ($validated['result_status'] === 'official') {
                $request->validate([
                    'cgpa' => ['required', 'numeric', 'min:0', 'max:4'],
                ]);
            }
        }

        AcademicResult::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->route('student.dashboard')
            ->with('success', 'Academic result updated successfully.');
    }
}