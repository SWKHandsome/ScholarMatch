<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use App\Services\AcademicResultInput;
use App\Support\AcademicQualifications;
use App\Support\StudentOnboarding;
use Illuminate\Http\Request;

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

        $qualificationConfig = AcademicQualifications::frontend();
        $onboarding = StudentOnboarding::nextRoute($request->user()) !== null;

        return view('student.academic-result.edit', compact('academicResult', 'educationLevels', 'qualificationConfig', 'onboarding'));
    }

    public function update(Request $request)
    {
        $validated = app(AcademicResultInput::class)->validate($request, $request->user()->academicResult?->field_of_study);
        AcademicResult::updateOrCreate(['user_id' => $request->user()->id], $validated);
        $request->user()->unsetRelation('academicResult');

        return redirect()->route(StudentOnboarding::nextRoute($request->user()) ?? 'student.dashboard')->with('success', 'Academic result updated successfully.');
    }
}
