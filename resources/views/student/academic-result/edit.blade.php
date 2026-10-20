@extends('layouts.student', ['pageTitle' => 'Academic Result'])
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold">Academic Qualification and Results</h1>
    <p class="mt-2 mb-6 text-on-surface-variant">Use your latest study level and official results. SPM students must have completed the exam and received official results. Other students need at least one official CGPA result.</p>
    <form method="POST" action="{{ route('student.academic-result.update') }}" class="card p-6 space-y-6" id="academic-form">
        @csrf
        @method('PATCH')
        <p class="text-sm text-on-surface-variant">Only the result and year fields that apply to your selections are shown.</p>
        @if($errors->any())
            <div role="alert" class="text-error"><ul class="list-disc pl-5">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
        @endif
        <div>
            <label class="label" for="education_level">Latest Study Level <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <select class="input" id="education_level" name="education_level" required>
                <option value="">Select your latest study level</option>
                @foreach($educationLevels as $level)
                    <option value="{{ $level }}" @selected(old('education_level', $academicResult->education_level) === $level)>{{ $level }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="study_status">Study Status <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <select class="input" id="study_status" name="study_status" required>
                <option value="">Select your study status</option>
                <option value="studying" @selected(old('study_status', $academicResult->study_status) === 'studying')>Currently Studying</option>
                <option value="completed" @selected(old('study_status', $academicResult->study_status) === 'completed')>Completed</option>
            </select>
            <p class="mt-1 text-sm text-on-surface-variant">SPM is available only after completing the exam and receiving official results.</p>
        </div>
        <div id="year-fields">
            <label class="label" for="year_of_study">Current Year of Study <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <input class="input" type="number" id="year_of_study" name="year_of_study" min="1" max="10" value="{{ old('year_of_study', $academicResult->year_of_study) }}">
        </div>
        <fieldset id="spm-fields">
            <legend class="font-semibold mb-3">Official SPM Results</legend>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="spm_as">Number of SPM As <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <input class="input" type="number" id="spm_as" name="spm_as" min="0" max="12" value="{{ old('spm_as', $academicResult->spm_as) }}">
                    <p class="mt-1 text-sm text-on-surface-variant">Count A+, A and A− subjects.</p>
                </div>
                <div>
                    <label class="label" for="spm_credits">Number of SPM Credits <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <input class="input" type="number" id="spm_credits" name="spm_credits" min="0" max="12" value="{{ old('spm_credits', $academicResult->spm_credits) }}">
                    <p class="mt-1 text-sm text-on-surface-variant">Subjects graded C or above, including your As.</p>
                </div>
            </div>
        </fieldset>
        <div id="cgpa-fields">
            <label class="label" for="cgpa">Official CGPA <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <input class="input" type="number" id="cgpa" name="cgpa" min="0" max="4" step="0.01" value="{{ old('cgpa', $academicResult->cgpa) }}">
            <p class="mt-1 text-sm text-on-surface-variant">Currently studying: latest official cumulative CGPA. Completed: final CGPA.</p>
        </div>
        <div class="border-t border-outline-variant pt-5">
            <fieldset class="min-w-0 space-y-5">
                <legend class="font-semibold">Programme Seeking Funding</legend>
                <p class="text-sm text-on-surface-variant">Describe the programme you want the scholarship to support. Its level, institution type and field can differ from your latest qualification above.</p>
                <div>
                    <label class="label" for="funding_study_level">Study Level Seeking Funding <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input" id="funding_study_level" name="funding_study_level" required>
                        <option value="">Select the level you want funded</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level }}" @selected(old('funding_study_level', $academicResult->funding_study_level) === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-on-surface-variant">Select the level of the programme you want the scholarship to support.</p>
                </div>
                <div>
                    <label class="label" for="institution_type">Institution Type <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input @error('institution_type') input-error @enderror" id="institution_type" name="institution_type" required>
                        <option value="">Select Institution Type</option>
                        @foreach(config('scholarship.institution_types') as $type)
                            <option value="{{ $type }}" @selected(old('institution_type', $academicResult->institution_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('institution_type')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="field_of_study">Field of Study <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
                    <select class="input @error('field_of_study') input-error @enderror" id="field_of_study" name="field_of_study" required>
                        <option value="">Select Field of Study</option>
                        @foreach(config('scholarship.fields_of_study') as $field)
                            <option value="{{ $field }}" @selected(old('field_of_study', $academicResult->field_of_study) === $field)>{{ $field }}</option>
                        @endforeach
                    </select>
                    @error('field_of_study')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
                </div>
            </fieldset>
        </div>
        <label class="flex items-start gap-3">
            <input class="mt-1" type="checkbox" name="result_status" value="official" required @checked(old('result_status', $academicResult->result_status) === 'official')>
            <span>I have received the official results entered above. <span class="text-error" aria-hidden="true">*</span><span class="sr-only"> (required)</span></span>
        </label>
        <div class="flex justify-end gap-3">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Academic Result</button>
        </div>
    </form>
</div>
<script>
(() => {
    const level = document.getElementById('education_level');
    const status = document.getElementById('study_status');
    function toggleFields() {
        const spm = level.value === 'SPM';
        status.querySelector('[value="studying"]').disabled = spm;
        if (spm) status.value = 'completed';
        const year = !spm && status.value === 'studying';
        for (const [group, active] of [['spm-fields', spm], ['cgpa-fields', !!level.value && !spm], ['year-fields', year]]) {
            const container = document.getElementById(group);
            container.hidden = !active;
            container.querySelectorAll('input').forEach(input => {
                input.disabled = !active;
                input.required = active;
            });
        }
    }
    level.addEventListener('change', toggleFields);
    status.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>
@endsection
