@extends('layouts.student', ['pageTitle' => 'Academic Result'])
@section('content')
@php
    $activeStudyFields = \App\Models\StudyField::selectableNames();
    $initialAcademic = [
        'level' => old('education_level', $academicResult->education_level),
        'category' => old('education_category', \App\Support\AcademicQualifications::category(old('education_level', $academicResult->education_level))),
        'studyStatus' => old('study_status', $academicResult->study_status),
        'year' => old('year_of_study', $academicResult->year_of_study),
        'scale' => old('grading_scale', $academicResult->grading_scale),
        'counts' => old('grade_counts', $academicResult->grade_counts),
        'cgpa' => old('cgpa', $academicResult->cgpa),
        'stamRank' => old('stam_rank', $academicResult->stam_rank),
        'paGrade' => old('stpm_pa_grade', $academicResult->stpm_pa_grade),
        'cgpaMatchesOfficial' => old('_token') ? old('stpm_cgpa_confirmed') === '1' : (bool) $academicResult->stpm_cgpa_confirmed,
        'official' => old('_token') ? old('result_status') === 'official' : $academicResult->result_status === 'official',
    ];
@endphp
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold">Academic Qualification and Results</h1>
    <p class="mt-2 mb-6 text-on-surface-variant">Select your current or latest qualification and enter official results.</p>
    @if($onboarding)
        @include('components.student-setup', ['currentStep' => 2])
    @endif
    <form method="POST" action="{{ route('student.academic-result.update') }}" class="card p-6 space-y-6"
        id="academic-form" x-data="academicResultEditor(@js($qualificationConfig), @js($initialAcademic))">
        @csrf
        @method('PATCH')
        @if($errors->any())
            <div role="alert" class="rounded-lg border border-error p-4 text-error"><ul class="list-disc pl-5">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
        @endif
        @if($academicResult->education_level === 'SPM' && $academicResult->grade_counts === null)
            <p class="rounded-lg bg-warning/10 p-4 text-sm">Your previous SPM totals are preserved: {{ $academicResult->spm_as ?? 0 }} As and {{ $academicResult->spm_credits ?? 0 }} credits. Enter your official grade breakdown below to update your results; the old totals cannot identify individual grades.</p>
        @endif
        @if($academicResult->education_level === 'STPM' && $academicResult->grade_counts === null)
            <p class="rounded-lg bg-warning/10 p-4 text-sm">Your official STPM CGPA is preserved. Add your official subject grade counts below so scholarships can check grade and best-subject requirements.</p>
        @endif
        @if($academicResult->education_level === 'STPM' && $academicResult->study_status === 'completed' && $academicResult->stpm_pa_grade === null && $academicResult->cgpa !== null)
            <p class="rounded-lg bg-warning/10 p-4 text-sm">Your saved official CGPA of {{ $academicResult->cgpa }} is preserved. Select the Pengajian Am grade included in your counts, then confirm the calculated CGPA before saving.</p>
        @endif
        <fieldset>
            <legend class="font-semibold mb-3">Current or latest education category <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></legend>
            <div class="flex flex-wrap gap-3">
                @foreach(\App\Support\AcademicQualifications::categories() as $key => $group)
                    <label class="cursor-pointer">
                        <input class="sr-only peer" type="radio" name="education_category" value="{{ $key }}"
                            :checked="category === '{{ $key }}'" @change="selectCategory('{{ $key }}')" required>
                        <span class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2"
                            :class="category === '{{ $key }}' ? 'bg-primary border-primary text-white' : 'bg-surface-container-low border-outline-variant text-on-surface-variant'">{{ $group['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <fieldset class="border-0 p-0">
            <legend class="font-semibold mb-3">Qualification (Latest Study Level) <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></legend>
            <p class="text-sm text-on-surface-variant" x-show="!category">Select an education category first.</p>
            <div class="flex flex-wrap gap-3">
                <template x-for="qualification in (config.categories[category]?.qualifications ?? [])" :key="qualification">
                    <label class="cursor-pointer">
                        <input class="sr-only peer" type="radio" name="education_level" :value="qualification"
                            :checked="level === qualification" @change="selectQualification(qualification)" required>
                        <span class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2"
                            :class="level === qualification ? 'bg-primary border-primary text-white' : 'bg-surface-container-low border-outline-variant text-on-surface-variant'" x-text="config.labels[qualification]"></span>
                    </label>
                </template>
            </div>
        </fieldset>
        <div x-show="level" x-cloak>
            <label class="label" for="study_status">Study Status <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <select class="input" id="study_status" name="study_status" x-model="studyStatus" @change="statusChanged()" :disabled="!level" required>
                <option value="">Select your study status</option>
                <option value="studying" :disabled="!cgpaBased">Currently Studying</option>
                <option value="completed">Completed</option>
            </select>
            <p class="mt-2 text-sm text-on-surface-variant" x-show="!cgpaBased">Official final results are required for this qualification.<span x-show="level === 'A-Level'"> Use full A-Level results; AS-Level is a separate qualification.</span></p>
        </div>
        <div x-show="cgpaBased && studyStatus === 'studying'" x-cloak>
            <label class="label" for="year_of_study">Current Year of Study <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <input class="input" type="number" id="year_of_study" name="year_of_study" min="1" max="10" x-model="year"
                :disabled="!cgpaBased || studyStatus !== 'studying'" :required="cgpaBased && studyStatus === 'studying'">
        </div>
        <div x-show="gradeBased || level === 'STAM'" x-cloak>
            <p class="font-semibold mb-3">My latest <span x-text="config.labels[level]"></span> results</p>
            <span class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white" x-show="level !== 'STPM' || studyStatus === 'completed'">Final Results (Official)</span>
            <span class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white" x-show="level === 'STPM' && studyStatus !== 'completed'">Latest Results (Official)</span>
            <p class="mt-2 text-sm text-on-surface-variant">Forecast and expected results cannot be used for recommendations.</p>
        </div>
        <div x-show="level === 'IGCSE'" x-cloak>
            <label class="label" for="grading_scale">IGCSE Grading Scale <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <select class="input" id="grading_scale" name="grading_scale" x-model="scale" @change="scaleChanged()" :disabled="level !== 'IGCSE'" :required="level === 'IGCSE'">
                <option value="">Select the scale on your official results</option>
                <option value="igcse_letters">Letter grades (A*–G)</option>
                <option value="igcse_9_1">Number grades (9–1)</option>
            </select>
            <p class="mt-1 text-sm text-on-surface-variant">Record one grading scale per result entry. Matching uses that exact scale.</p>
        </div>
        <input type="hidden" name="grading_scale" :value="scale" :disabled="!gradeBased || level === 'IGCSE'">
        <section x-show="gradeBased && scale" x-cloak aria-labelledby="grade-count-heading">
            <h2 class="font-semibold mb-3" id="grade-count-heading">And my results are <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></h2>
            <label class="sr-only" for="grade-summary">Grade summary</label>
            <input id="grade-summary" class="input bg-surface-container-low mb-5" type="text" :value="summary" placeholder="Your grade summary appears here" readonly>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-5 gap-y-4">
                <template x-for="grade in grades" :key="scale + '-' + grade">
                    <div class="flex items-center justify-between gap-2">
                        <label class="font-semibold text-sm" :for="'grade-count-' + grade" x-text="grade"></label>
                        <div class="inline-flex items-center rounded-full border border-primary/15 bg-primary/5 text-primary">
                            <button type="button" class="rounded-full px-2.5 py-2 text-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary disabled:opacity-40"
                                :aria-label="'Decrease subjects graded ' + grade" @click="adjust(grade, -1)" :disabled="count(grade) === 0">−</button>
                            <input class="grade-counter" type="number" inputmode="numeric" min="0" :max="maxSubjects"
                                :id="'grade-count-' + grade" :name="'grade_counts[' + grade + ']'" x-model.number="counts[grade]" @input="stpmInputsChanged()"
                                :aria-label="'Subjects graded ' + grade" :disabled="!gradeBased || !scale" required>
                            <button type="button" class="rounded-full px-2.5 py-2 text-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary disabled:opacity-40"
                                :aria-label="'Increase subjects graded ' + grade" @click="adjust(grade, 1)" :disabled="totalSubjects >= maxSubjects">+</button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="mt-5 rounded-lg bg-surface-container-low p-3 text-sm" role="status" aria-live="polite">
                <p>Total subjects: <strong x-text="totalSubjects"></strong></p>
                <p x-show="level === 'SPM'">Total SPM As: <strong x-text="countAtOrAbove('A-')"></strong> · Total credits: <strong x-text="countAtOrAbove('C')"></strong></p>
            </div>
            <p class="mt-2 text-xs text-on-surface-variant">Leave unused grades at zero. Maximum total: <span x-text="maxSubjects"></span> subjects in this entry.</p>
            <p class="mt-2 text-sm text-error" x-show="!validCounts">Enter whole-number counts totalling at least one subject, within the supported limit.</p>
        </section>
        <section class="space-y-4" x-show="autoStpmCgpa" x-cloak aria-labelledby="stpm-cgpa-heading">
            <h2 class="font-semibold" id="stpm-cgpa-heading">STPM Overall CGPA</h2>
            <div>
                <label class="label" for="stpm_pa_grade">Pengajian Am Grade <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                <select class="input" id="stpm_pa_grade" name="stpm_pa_grade" x-model="paGrade" @change="stpmInputsChanged()" :disabled="!autoStpmCgpa" :required="autoStpmCgpa">
                    <option value="">Select your official Pengajian Am grade</option>
                    @foreach(\App\Support\AcademicQualifications::scales()['stpm']['grades'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-on-surface-variant">Include Pengajian Am once in the grade counts above. This selection identifies that result; it does not add another subject.</p>
            </div>
            <div>
                <label class="label" for="calculated_stpm_cgpa">Calculated Overall CGPA</label>
                <input class="input bg-surface-container-low" id="calculated_stpm_cgpa" type="text" readonly :value="calculatedStpmCgpa" placeholder="Complete your final grades to calculate">
                <p class="mt-1 text-sm text-on-surface-variant">Uses Pengajian Am and the best three other subjects, rounded to two decimal places. Four or five official final subject results are required.</p>
                <p class="mt-1 text-sm text-error" x-show="!calculatedStpmCgpa">Enter four or five final subjects and select a Pengajian Am grade already included in your counts.</p>
            </div>
            <label class="flex items-start gap-3">
                <input class="mt-1" type="checkbox" name="stpm_cgpa_confirmed" value="1" x-model="cgpaMatchesOfficial"
                    :disabled="!autoStpmCgpa || !calculatedStpmCgpa" :required="autoStpmCgpa">
                <span>This calculated CGPA matches my official STPM result slip. <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></span>
            </label>
            <p class="text-sm text-on-surface-variant">If it does not match, check your final subject counts and Pengajian Am grade before continuing.</p>
        </section>
        <div x-show="cgpaBased && !autoStpmCgpa" x-cloak>
            <label class="label" for="cgpa">Official CGPA <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <input class="input" type="number" id="cgpa" name="cgpa" min="0" max="4" step="0.01" x-model="cgpa" :disabled="!cgpaBased || autoStpmCgpa" :required="cgpaBased && !autoStpmCgpa">
            <p class="mt-1 text-sm text-on-surface-variant">Currently studying: latest official cumulative CGPA. Completed: final CGPA.</p>
            <p class="mt-1 text-sm text-on-surface-variant" x-show="level === 'STPM'">Enter the overall CGPA printed on your official results. Keep the grade counts above from the same results. Best-subject scholarship averages are calculated separately.</p>
        </div>
        <div x-show="level === 'STAM'" x-cloak>
            <label class="label" for="stam_rank">Official Overall STAM Pangkat <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <select class="input" id="stam_rank" name="stam_rank" x-model="stamRank" :disabled="level !== 'STAM'" :required="level === 'STAM'">
                <option value="">Select the pangkat on your official results</option>
                @foreach(\App\Support\AcademicQualifications::RANKS as $rank)<option value="{{ $rank }}">{{ $rank }}</option>@endforeach
            </select>
        </div>
        <div class="border-t border-outline-variant pt-5">
            <fieldset class="min-w-0 space-y-5">
                <legend class="font-semibold">Programme Seeking Funding</legend>
                <p class="text-sm text-on-surface-variant">Describe the programme you want the scholarship to support. Its qualification, institution type and field can differ from your latest qualification above.</p>
                <div>
                    <label class="label" for="funding_study_level">Study Level Seeking Funding <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input" id="funding_study_level" name="funding_study_level" required>
                        <option value="">Select the level you want funded</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level }}" @selected(old('funding_study_level', $academicResult->funding_study_level) === $level)>{{ \App\Support\AcademicQualifications::label($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="institution_type">Institution Type <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input" id="institution_type" name="institution_type" required>
                        <option value="">Select Institution Type</option>
                        <option value="{{ \App\Support\ProgrammeChoices::UNDECIDED }}" @selected(old('institution_type', $academicResult->institution_type) === \App\Support\ProgrammeChoices::UNDECIDED)>{{ \App\Support\ProgrammeChoices::UNDECIDED_LABEL }}</option>
                        @foreach(config('scholarship.institution_types') as $type)
                            <option value="{{ $type }}" @selected(old('institution_type', $academicResult->institution_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="field_of_study">Field of Study <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input" id="field_of_study" name="field_of_study" required>
                        <option value="">Select Field of Study</option>
                        <option value="{{ \App\Support\ProgrammeChoices::UNDECIDED }}" @selected(old('field_of_study', $academicResult->field_of_study) === \App\Support\ProgrammeChoices::UNDECIDED)>{{ \App\Support\ProgrammeChoices::UNDECIDED_LABEL }}</option>
                        @foreach(\App\Models\StudyField::selectableNames(array_filter([$academicResult->field_of_study])) as $field)
                            <option value="{{ $field }}" @selected(old('field_of_study', $academicResult->field_of_study) === $field)>{{ $field }}{{ in_array($field, $activeStudyFields, true) ? '' : ' (Inactive)' }}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>
        </div>
        <label class="flex items-start gap-3">
            <input class="mt-1" type="checkbox" name="result_status" value="official" x-model="official" required>
            <span>I have received the official results entered above. <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></span>
        </label>
        <noscript><p class="text-error">Enable JavaScript to choose your qualification and enter grade counts.</p></noscript>
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline">Cancel</a>
            <button class="btn btn-primary" type="submit" :disabled="!level || (gradeBased && !validCounts) || (autoStpmCgpa && (!calculatedStpmCgpa || !cgpaMatchesOfficial))">Save Academic Result</button>
        </div>
    </form>
</div>
@endsection
