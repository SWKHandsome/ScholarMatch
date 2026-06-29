@extends('layouts.student', ['pageTitle' => 'Academic Result'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">Academic Result</h1>
        <p class="text-on-surface-variant mt-1">Enter your academic qualifications for scholarship matching.</p>
    </div>

    <form method="POST" action="{{ route('student.academic-result.update') }}" class="card p-6 space-y-6" id="academic-form">
        @csrf
        @method('PATCH')

        <!-- Education Level -->
        <div>
            <label for="education_level" class="label">Education Level <span class="text-error">*</span></label>
            <select id="education_level" name="education_level" class="input @error('education_level') input-error @enderror" required
                onchange="toggleFields()">
                <option value="">Select Education Level</option>
                @foreach($educationLevels as $level)
                    <option value="{{ $level }}" {{ old('education_level', $academicResult->education_level) === $level ? 'selected' : '' }}>{{ $level }}</option>
                @endforeach
            </select>
            @error('education_level')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- SPM Fields -->
        <div id="spm-fields" class="space-y-4 {{ (old('education_level', $academicResult->education_level) ?? '') === 'SPM' ? '' : 'hidden' }}">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-3">SPM Results</h4>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="spm_as" class="label">Number of As <span class="text-error">*</span></label>
                        <input type="number" id="spm_as" name="spm_as" min="0" max="12" class="input @error('spm_as') input-error @enderror"
                            value="{{ old('spm_as', $academicResult->spm_as) }}">
                        @error('spm_as')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="spm_credits" class="label">Number of Credits <span class="text-error">*</span></label>
                        <input type="number" id="spm_credits" name="spm_credits" min="0" max="12" class="input @error('spm_credits') input-error @enderror"
                            value="{{ old('spm_credits', $academicResult->spm_credits) }}">
                        @error('spm_credits')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Non-SPM Fields (CGPA) -->
        <div id="cgpa-fields" class="space-y-4 {{ (old('education_level', $academicResult->education_level) ?? '') !== 'SPM' && (old('education_level', $academicResult->education_level) ?? '') !== '' ? '' : 'hidden' }}">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-3">CGPA</h4>
                <div>
                    <label for="cgpa" class="label">CGPA <span class="text-error">*</span></label>
                    <input type="number" id="cgpa" name="cgpa" step="0.01" min="0" max="4" class="input @error('cgpa') input-error @enderror"
                        value="{{ old('cgpa', $academicResult->cgpa) }}">
                    @error('cgpa')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-on-surface-variant">Enter your CGPA (0.00 - 4.00)</p>
                </div>
            </div>
        </div>

        <!-- Result Status -->
        <div>
            <label class="label">Result Status <span class="text-error">*</span></label>
            <div class="flex flex-wrap gap-4">
                @foreach($resultStatuses as $status)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="result_status" value="{{ $status }}" class="w-4 h-4 text-primary border-outline-variant focus:ring-primary"
                            {{ old('result_status', $academicResult->result_status) === $status ? 'checked' : '' }} required>
                        <span class="text-on-surface capitalize">{{ $status }}</span>
                    </label>
                @endforeach
            </div>
            @error('result_status')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-on-surface-variant">
                <span class="font-medium">Official:</span> Final verified results used for full eligibility.<br>
                <span class="font-medium">Pending:</span> Preliminary results — shows guidance only, not final eligibility.
            </p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('student.dashboard') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Academic Result</button>
        </div>
    </form>
</div>

<script>
function toggleFields() {
    const level = document.getElementById('education_level').value;
    const spmFields = document.getElementById('spm-fields');
    const cgpaFields = document.getElementById('cgpa-fields');
    const spmAs = document.getElementById('spm_as');
    const spmCredits = document.getElementById('spm_credits');
    const cgpa = document.getElementById('cgpa');

    if (level === 'SPM') {
        spmFields.classList.remove('hidden');
        cgpaFields.classList.add('hidden');
        spmAs.required = true;
        spmCredits.required = true;
        cgpa.required = false;
    } else if (level) {
        spmFields.classList.add('hidden');
        cgpaFields.classList.remove('hidden');
        spmAs.required = false;
        spmCredits.required = false;
        cgpa.required = document.querySelector('input[name="result_status"]:checked')?.value === 'official';
    } else {
        spmFields.classList.add('hidden');
        cgpaFields.classList.add('hidden');
        spmAs.required = false;
        spmCredits.required = false;
        cgpa.required = false;
    }
}

// Update CGPA required on result status change
document.querySelectorAll('input[name="result_status"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const level = document.getElementById('education_level').value;
        const cgpa = document.getElementById('cgpa');
        if (level !== 'SPM' && level !== '') {
            cgpa.required = radio.value === 'official';
        }
    });
});

// Initialize on load
document.addEventListener('DOMContentLoaded', toggleFields);
</script>
@endsection