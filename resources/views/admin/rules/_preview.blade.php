@php
    $previewSource = $scholarship ?? null;
    $previewDefaults = [
        'deadline' => $previewSource?->deadline?->toDateString(),
        'deadline_type' => $previewSource?->deadline_type ?? 'unconfirmed',
        'special_requirements' => $previewSource?->special_requirements ?? '',
        'application_notes' => $previewSource?->application_notes ?? '',
    ];
@endphp
<details class="rounded-lg border border-primary/30 bg-primary/5 p-4" x-data="rulePreview(@js(route('admin.rule-preview')), @js($previewDefaults))">
    <summary class="cursor-pointer font-semibold text-primary">Test these rules with a sample student</summary>
    <p class="mt-3 text-sm text-on-surface-variant">Try the requirements currently entered in this form before saving. This sample is hypothetical; it creates no student, scholarship or recommendation log. Required sample fields are checked when you run the test.</p>
    <p class="mt-2 text-sm text-on-surface-variant">Run the test again after editing the rules or sample; the previous result will be cleared.</p>
    <div class="mt-4 space-y-4" x-data="academicResultEditor(@js(\App\Support\AcademicQualifications::frontend()), {level: 'Undergraduate', studyStatus: 'studying', year: 1, cgpa: '3.20'})">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="preview-level">Sample qualification</label>
                <select class="input" id="preview-level" form="rule-preview-sample" name="sample[education_level]" :value="level" @change="selectQualification($event.target.value)">
                    <option value="">Select qualification</option>
                    @foreach(\App\Support\AcademicQualifications::categories() as $category)
                        <optgroup label="{{ $category['label'] }}">@foreach($category['qualifications'] as $value)<option value="{{ $value }}">{{ \App\Support\AcademicQualifications::label($value) }}</option>@endforeach</optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="preview-status">Sample study status</label>
                <select class="input" id="preview-status" form="rule-preview-sample" name="sample[study_status]" x-model="studyStatus" @change="statusChanged()">
                    <option value="">Select status</option><option value="completed">Completed</option><option value="studying" :disabled="!cgpaBased">Currently studying</option>
                </select>
            </div>
            <div x-show="cgpaBased && studyStatus === 'studying'" x-cloak>
                <label class="label" for="preview-year">Sample year of study</label>
                <input class="input" id="preview-year" type="number" form="rule-preview-sample" name="sample[year_of_study]" min="1" max="10" step="1" x-model="year" :disabled="!cgpaBased || studyStatus !== 'studying'">
            </div>
            <div x-show="cgpaBased && !autoStpmCgpa" x-cloak>
                <label class="label" for="preview-cgpa">Sample overall CGPA</label>
                <input class="input" id="preview-cgpa" type="number" form="rule-preview-sample" name="sample[cgpa]" min="0" max="4" step="0.01" x-model="cgpa" :disabled="!cgpaBased || autoStpmCgpa">
            </div>
            <div x-show="level === 'IGCSE'" x-cloak>
                <label class="label" for="preview-scale">Sample IGCSE scale</label>
                <select class="input" id="preview-scale" form="rule-preview-sample" name="sample[grading_scale]" x-model="scale" @change="scaleChanged()" :disabled="level !== 'IGCSE'">
                    <option value="">Select scale</option><option value="igcse_letters">Letters A*–G</option><option value="igcse_9_1">Numbers 9–1</option>
                </select>
            </div>
            <div x-show="level === 'STAM'" x-cloak>
                <label class="label" for="preview-stam">Sample overall STAM pangkat</label>
                <select class="input" id="preview-stam" form="rule-preview-sample" name="sample[stam_rank]" x-model="stamRank" :disabled="level !== 'STAM'">
                    <option value="">Select pangkat</option>@foreach(\App\Support\AcademicQualifications::RANKS as $rank)<option value="{{ $rank }}">{{ $rank }}</option>@endforeach
                </select>
            </div>
        </div>
        <input type="hidden" form="rule-preview-sample" name="sample[grading_scale]" :value="scale" :disabled="!gradeBased || level === 'IGCSE'">
        <fieldset x-show="gradeBased" x-cloak class="space-y-3">
            <legend class="font-medium">Sample subject grade counts</legend>
            <p class="text-sm text-on-surface-variant">Enter the number of subjects at each grade. For completed STPM, include Pengajian Am once.</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <template x-for="grade in grades" :key="scale + grade">
                    <div><label class="label" :for="'preview-grade-' + grade" x-text="grade"></label><input class="input" type="number" min="0" :max="maxSubjects" step="1" form="rule-preview-sample" :id="'preview-grade-' + grade" :name="'sample[grade_counts][' + grade + ']'" x-model="counts[grade]" :disabled="!gradeBased"></div>
                </template>
            </div>
            <p class="text-sm" x-text="summary ? 'Sample results: ' + summary : 'No sample grades entered.'"></p>
            <div class="flex flex-wrap gap-2">
                @foreach(['AAB' => ['A' => 2, 'B' => 1], 'ABB' => ['A' => 1, 'B' => 2], 'BBB' => ['B' => 3]] as $pattern => $counts)
                    <button class="btn btn-outline text-sm" type="button" @click="selectQualification('A-Level'); studyStatus = 'completed'; counts = Object.fromEntries(grades.map(grade => [grade, 0])); Object.assign(counts, @js($counts)); $dispatch('change')">Try A-Level {{ $pattern }}</button>
                @endforeach
            </div>
        </fieldset>
        <div x-show="autoStpmCgpa" x-cloak class="grid gap-4 sm:grid-cols-2">
            <div><label class="label" for="preview-pa">Sample Pengajian Am grade</label><select class="input" id="preview-pa" form="rule-preview-sample" name="sample[stpm_pa_grade]" x-model="paGrade" :disabled="!autoStpmCgpa"><option value="">Select included PA grade</option>@foreach(\App\Support\AcademicQualifications::scales()['stpm']['grades'] as $grade)<option value="{{ $grade }}">{{ $grade }}</option>@endforeach</select></div>
            <div><label class="label" for="preview-calculated">Calculated sample STPM CGPA</label><input class="input" id="preview-calculated" readonly :value="calculatedStpmCgpa" placeholder="Enter 4–5 subjects and PA grade"></div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="label" for="preview-nationality">Sample nationality</label><select class="input" id="preview-nationality" name="sample[nationality]" form="rule-preview-sample">@foreach(config('scholarship.nationalities') as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach</select></div>
            <div><label class="label" for="preview-income">Sample household income (RM/month)</label><input class="input" id="preview-income" type="number" min="0" step="0.01" value="3000" name="sample[household_income]" form="rule-preview-sample"></div>
            <div><label class="label" for="preview-funded">Sample level seeking funding</label><select class="input" id="preview-funded" name="sample[funding_study_level]" form="rule-preview-sample">@foreach(\App\Models\AcademicResult::EDUCATION_LEVELS as $value)<option value="{{ $value }}" @selected($value === 'Undergraduate')>{{ \App\Support\AcademicQualifications::label($value) }}</option>@endforeach</select></div>
            <div><label class="label" for="preview-institution">Sample institution type</label><select class="input" id="preview-institution" name="sample[institution_type]" form="rule-preview-sample">@foreach(config('scholarship.institution_types') as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach<option value="{{ \App\Support\ProgrammeChoices::UNDECIDED }}">{{ \App\Support\ProgrammeChoices::UNDECIDED_LABEL }}</option></select></div>
            <div><label class="label" for="preview-field">Sample field of study</label><select class="input" id="preview-field" name="sample[field_of_study]" form="rule-preview-sample">@foreach(\App\Models\StudyField::names() as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach<option value="{{ \App\Support\ProgrammeChoices::UNDECIDED }}">{{ \App\Support\ProgrammeChoices::UNDECIDED_LABEL }}</option></select></div>
        </div>
    </div>
    <button class="btn btn-primary mt-4" type="button" @click="run()" :disabled="busy" x-text="busy ? 'Testing…' : 'Run rule test'">Run rule test</button>
    <noscript><p class="mt-3 text-sm text-error">Enable JavaScript to use the rule preview.</p></noscript>
    <div class="mt-4" aria-live="polite" :aria-busy="busy">
        <ul x-show="errors.length" x-cloak class="list-disc space-y-1 rounded-md bg-rose-50 p-4 pl-8 text-sm text-rose-800" role="alert"><template x-for="(message, index) in errors" :key="index"><li x-text="message"></li></template></ul>
        <template x-if="result">
            <section class="space-y-3 rounded-lg border border-outline-variant bg-white p-4">
                <h4 class="font-semibold" :class="result.passed ? 'text-emerald-800' : 'text-rose-800'" x-text="result.message"></h4>
                <p class="text-sm" x-text="result.selected_route"></p>
                <p class="text-sm text-on-surface-variant">Hypothetical sample only. Nothing has been saved.</p>
                <p x-show="result.sample_cgpa !== null" class="text-sm" x-text="'Sample overall CGPA: ' + result.sample_cgpa"></p>
                <ul class="divide-y divide-outline-variant text-sm"><template x-for="check in result.checks" :key="check.key"><li class="py-3"><p class="font-medium" :class="check.outcome === 'met' ? 'text-emerald-800' : (check.outcome === 'not_checked' ? 'text-blue-800' : 'text-rose-800')" x-text="(check.outcome === 'met' ? '✓ ' : (check.outcome === 'not_checked' ? '— Not checked · ' : '✕ ')) + check.label"></p><p class="mt-1 break-words" x-text="'Required: ' + check.required + ' · Sample: ' + check.actual"></p><p class="mt-1 text-blue-800" x-show="check.outcome === 'not_checked'" x-text="check.message"></p><p class="mt-1" x-show="check.gap" x-text="check.gap"></p></li></template></ul>
                <div x-show="result.provider_checks" class="rounded-md bg-amber-50 p-3 text-sm text-amber-900"><strong>Provider checks remain</strong><p class="mt-1 whitespace-pre-line" x-text="result.provider_checks"></p></div>
            </section>
        </template>
    </div>
</details>
@push('scripts')
    <form id="rule-preview-sample" hidden aria-hidden="true"></form>
@endpush
