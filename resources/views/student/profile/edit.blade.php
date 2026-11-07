@extends('layouts.student', ['pageTitle' => $onboarding ? 'Student Profile' : 'Edit Profile'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">{{ $onboarding ? 'Complete Student Profile' : 'Edit Profile' }}</h1>
        <p class="text-on-surface-variant mt-1">Update your personal and socioeconomic information for better scholarship matching.</p>
    </div>

    @if($onboarding)
        @include('components.student-setup', ['currentStep' => 1])
    @endif
    <form method="POST" action="{{ route('student.profile.update') }}" class="card p-6 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Nationality -->
        <div>
            <label for="nationality" class="label">Nationality <span class="text-error">*</span></label>
            <select id="nationality" name="nationality" class="input @error('nationality') input-error @enderror" required>
                <option value="">Select Nationality</option>
                @foreach(config('scholarship.nationalities') as $nationality)
                    <option value="{{ $nationality }}" {{ old('nationality', $profile->nationality) === $nationality ? 'selected' : '' }}>{{ $nationality }}</option>
                @endforeach
            </select>
            @error('nationality')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- State -->
        <div>
            <label for="state" class="label">State <span class="text-error">*</span></label>
            <select id="state" name="state" class="input @error('state') input-error @enderror" required>
                <option value="">Select State</option>
                @foreach(\App\Models\StudentProfile::STATES as $state)
                    <option value="{{ $state }}" {{ \App\Models\StudentProfile::normalizeState(old('state', $profile->state)) === $state ? 'selected' : '' }}>{{ $state }}</option>
                @endforeach
            </select>
            @error('state')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Household Income -->
        <div x-data="incomeCategoryPreview(@js($incomePreview))">
            <label for="household_income" class="label">Household Income (RM) <span class="text-error">*</span></label>
            <input type="number" id="household_income" name="household_income" step="0.01" min="0" max="9999999999.99" class="input @error('household_income') input-error @enderror"
                value="{{ $incomeValue }}" @input="setIncome($event.target.value, $event.target.validity.badInput)"
                aria-describedby="income-category-feedback @error('household_income') household-income-error @enderror" required>
            @error('household_income')
                <p id="household-income-error" class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
            <div id="income-category-feedback" class="mt-2 min-h-[3rem] space-y-1 text-sm leading-5" role="status" aria-live="polite" aria-atomic="true">
                <p class="font-medium text-primary" x-text="heading">{{ $incomeHeading }}</p>
                <p class="text-on-surface-variant" x-text="detail">{{ $incomeDetail }}</p>
            </div>
        </div>

        <!-- Number of Dependents -->
        <div>
            <label for="number_of_dependents" class="label">Number of Dependents <span class="text-error">*</span></label>
            <input type="number" id="number_of_dependents" name="number_of_dependents" min="0" class="input @error('number_of_dependents') input-error @enderror"
                value="{{ old('number_of_dependents', $profile->number_of_dependents) }}" required>
            @error('number_of_dependents')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-sm text-on-surface-variant">Institution type and field of study are in <a class="text-primary underline" href="{{ route('student.academic-result.edit') }}">Academic Result</a>, under Programme Seeking Funding.</p>

        <!-- Income Category Reference -->
        <div class="bg-surface-container-low rounded-lg p-4 border border-outline-variant">
            <h4 class="font-medium text-on-surface mb-3">Income Category Reference</h4>
            <div class="grid sm:grid-cols-3 gap-4 text-sm">
                @forelse($incomeCategories as $incomeCategory)
                    @php
                        $colour = match ($incomeCategory->name) {
                            'B40' => ['bg-success/5 border-success/20', 'text-success'],
                            'M40' => ['bg-warning/5 border-warning/20', 'text-warning'],
                            'T20' => ['bg-error/5 border-error/20', 'text-error'],
                            default => ['bg-primary/5 border-primary/20', 'text-primary'],
                        };
                    @endphp
                    <div class="p-3 rounded-lg border {{ $colour[0] }}">
                        <div class="font-medium mb-1 {{ $colour[1] }}">{{ $incomeCategory->name }}</div>
                        <div class="text-on-surface-variant">{{ $incomeCategory->rangeLabel() }}</div>
                    </div>
                @empty
                    <p class="sm:col-span-3 text-on-surface-variant">No income ranges are configured. Please contact the administrator.</p>
                @endforelse
            </div>
            <p class="mt-3 text-xs text-on-surface-variant">Income ranges configured by the administrator.</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $onboarding ? 'Save and Continue' : 'Save Changes' }}</button>
        </div>
    </form>
</div>
@endsection
