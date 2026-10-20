<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicResultController extends Controller
{
    public function edit(Request $request)
    {
        $academicResult = $request->user()->academicResult;
        if (! $academicResult) {
            $profile = $request->user()->studentProfile;
            $academicResult = new AcademicResult([
                'institution_type' => $profile?->institution_type, 'field_of_study' => $profile?->field_of_study,
            ]);
        }
        $educationLevels = AcademicResult::EDUCATION_LEVELS;

        return view('student.academic-result.edit', compact('academicResult', 'educationLevels'));
    }

    public function update(Request $request)
    {
        $isSpm = $request->input('education_level') === 'SPM';
        $validated = $request->validate([
            'education_level' => ['required', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'study_status' => ['required', Rule::in($isSpm ? ['completed'] : ['studying', 'completed'])],
            'year_of_study' => ['exclude_unless:study_status,studying', 'required', 'integer', 'between:1,10'],
            'funding_study_level' => ['required', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'institution_type' => ['required', Rule::in(config('scholarship.institution_types'))],
            'field_of_study' => ['required', Rule::in(config('scholarship.fields_of_study'))],
            'spm_as' => ['exclude_unless:education_level,SPM', 'required', 'integer', 'between:0,12'],
            'spm_credits' => [
                'exclude_unless:education_level,SPM', 'required', 'integer', 'between:0,12', 'gte:spm_as',
            ],
            'cgpa' => ['exclude_if:education_level,SPM', 'required', 'numeric', 'between:0,4'],
            'result_status' => ['required', Rule::in(['official'])],
        ], [
            'study_status.in' => $isSpm ? 'SPM students must have completed their exam and received official results.' : 'Choose Currently Studying or Completed.',
            'result_status.in' => 'Official results are required before recommendations can be generated.',
            'spm_credits.gte' => 'The credit total must include all of your A subjects.',
        ]);
        $validated['year_of_study'] = $validated['study_status'] === 'studying'
            ? $validated['year_of_study'] : null;
        $validated['spm_as'] = $isSpm ? $validated['spm_as'] : null;
        $validated['spm_credits'] = $isSpm ? $validated['spm_credits'] : null;
        $validated['cgpa'] = $isSpm ? null : $validated['cgpa'];
        AcademicResult::updateOrCreate(['user_id' => $request->user()->id], $validated);

        return redirect()->route('student.dashboard')->with('success', 'Academic result updated successfully.');
    }
}
