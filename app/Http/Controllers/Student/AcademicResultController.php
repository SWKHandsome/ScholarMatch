<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use App\Support\AcademicQualifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

        $qualificationConfig = AcademicQualifications::frontend();

        return view('student.academic-result.edit', compact('academicResult', 'educationLevels', 'qualificationConfig'));
    }

    public function update(Request $request)
    {
        $level = is_string($request->input('education_level')) ? $request->input('education_level') : '';
        $cgpaBased = AcademicQualifications::isCgpa($level);
        $gradeBased = AcademicQualifications::isGradeBased($level);
        $automaticStpm = $level === 'STPM' && $request->input('study_status') === 'completed';
        if ($gradeBased && ! $request->filled('grading_scale') && AcademicQualifications::defaultScale($level)) {
            $request->merge(['grading_scale' => AcademicQualifications::defaultScale($level)]);
        }
        $validator = Validator::make($request->all(), [
            'education_category' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(AcademicQualifications::categories()))],
            'education_level' => ['required', 'string', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'study_status' => ['required', 'string', Rule::in($cgpaBased ? ['studying', 'completed'] : ['completed'])],
            'year_of_study' => [Rule::excludeIf(! $cgpaBased || $request->input('study_status') !== 'studying'), 'required', 'integer', 'between:1,10'],
            'funding_study_level' => ['required', Rule::in(AcademicResult::EDUCATION_LEVELS)],
            'institution_type' => ['required', Rule::in(config('scholarship.institution_types'))],
            'field_of_study' => ['required', Rule::in(config('scholarship.fields_of_study'))],
            'grading_scale' => [Rule::excludeIf(! $gradeBased), 'required', 'string', Rule::in(AcademicQualifications::allowedScales($level))],
            'grade_counts' => [Rule::excludeIf(! $gradeBased), 'required', 'array'],
            'grade_counts.*' => [Rule::excludeIf(! $gradeBased), 'required', 'integer', 'between:0,20'],
            'stam_rank' => [Rule::excludeIf($level !== 'STAM'), 'required', 'string', Rule::in(AcademicQualifications::RANKS)],
            'cgpa' => [Rule::excludeIf(! $cgpaBased || $automaticStpm), 'required', 'numeric', 'between:0,4'],
            'stpm_pa_grade' => [Rule::excludeIf(! $automaticStpm), 'required', 'string', Rule::in(AcademicQualifications::scales()['stpm']['grades'])],
            'stpm_cgpa_confirmed' => [Rule::excludeIf(! $automaticStpm), 'required', 'accepted'],
            'result_status' => ['required', Rule::in(['official'])],
        ], [
            'study_status.in' => $cgpaBased ? 'Choose Currently Studying or Completed.' : 'This qualification requires completed exams and official final results. AS-Level, forecast and expected results are not accepted as full A-Level results.',
            'result_status.in' => 'Official results are required before recommendations can be generated.',
            'stpm_cgpa_confirmed.required' => 'Confirm that the calculated CGPA matches your official STPM result slip.',
            'stpm_cgpa_confirmed.accepted' => 'Confirm that the calculated CGPA matches your official STPM result slip.',
        ]);
        $validator->after(function ($validator) use ($request, $level, $gradeBased, $automaticStpm) {
            if ($request->filled('education_category') && $request->input('education_category') !== AcademicQualifications::category($level)) {
                $validator->errors()->add('education_category', 'Choose a qualification within the selected education category.');
            }
            $scale = $request->input('grading_scale');
            if ($gradeBased && is_string($scale) && ! AcademicQualifications::validCounts($level, $scale, $request->input('grade_counts'))) {
                $max = AcademicQualifications::scales()[$scale]['max_subjects'] ?? 20;
                $validator->errors()->add('grade_counts', "Enter counts for the selected grading scale, with between 1 and $max subjects in total.");
            }
            if ($automaticStpm && AcademicQualifications::validCounts('STPM', 'stpm', $request->input('grade_counts'))) {
                $counts = $request->input('grade_counts');
                if (array_sum($counts) < 4) {
                    $validator->errors()->add('grade_counts', 'Completed STPM calculation requires four or five final subject results, including Pengajian Am.');
                }
                $paGrade = $request->input('stpm_pa_grade');
                if (is_string($paGrade) && in_array($paGrade, AcademicQualifications::scales()['stpm']['grades'], true)
                    && ($counts[$paGrade] ?? 0) < 1) {
                    $validator->errors()->add('stpm_pa_grade', 'The Pengajian Am grade must already be included in your subject grade counts.');
                }
            }
        });
        $validated = $validator->validate();
        unset($validated['education_category']);
        $validated['year_of_study'] = $cgpaBased && $validated['study_status'] === 'studying' ? $validated['year_of_study'] : null;
        $validated['grade_counts'] = $gradeBased ? array_map('intval', $validated['grade_counts']) : null;
        $validated['grading_scale'] = $gradeBased ? $validated['grading_scale'] : null;
        $validated['stam_rank'] = $level === 'STAM' ? $validated['stam_rank'] : null;
        $validated['spm_as'] = $level === 'SPM' ? AcademicQualifications::countAtOrAbove($validated['grade_counts'], 'spm', 'A-') : null;
        $validated['spm_credits'] = $level === 'SPM' ? AcademicQualifications::countAtOrAbove($validated['grade_counts'], 'spm', 'C') : null;
        $validated['stpm_pa_grade'] = $automaticStpm ? $validated['stpm_pa_grade'] : null;
        $validated['stpm_cgpa_confirmed'] = $automaticStpm;
        $validated['cgpa'] = $automaticStpm
            ? AcademicQualifications::stpmCgpa($validated['grade_counts'], $validated['stpm_pa_grade'])
            : ($cgpaBased ? $validated['cgpa'] : null);
        AcademicResult::updateOrCreate(['user_id' => $request->user()->id], $validated);

        return redirect()->route('student.dashboard')->with('success', 'Academic result updated successfully.');
    }
}
