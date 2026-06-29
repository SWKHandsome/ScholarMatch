@extends('layouts.student', ['pageTitle' => 'Edit Profile'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">Edit Profile</h1>
        <p class="text-on-surface-variant mt-1">Update your personal and socioeconomic information for better scholarship matching.</p>
    </div>

    <form method="POST" action="{{ route('student.profile.update') }}" class="card p-6 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Nationality -->
        <div>
            <label for="nationality" class="label">Nationality <span class="text-error">*</span></label>
            <input type="text" id="nationality" name="nationality" class="input @error('nationality') input-error @enderror"
                value="{{ old('nationality', $profile->nationality) }}" required autocomplete="off">
            @error('nationality')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- State -->
        <div>
            <label for="state" class="label">State <span class="text-error">*</span></label>
            <select id="state" name="state" class="input @error('state') input-error @enderror" required>
                <option value="">Select State</option>
                @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Wilayah Persekutuan Kuala Lumpur', 'Wilayah Persekutuan Labuan', 'Wilayah Persekutuan Putrajaya'] as $state)
                    <option value="{{ $state }}" {{ old('state', $profile->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                @endforeach
            </select>
            @error('state')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Household Income -->
        <div>
            <label for="household_income" class="label">Household Income (RM) <span class="text-error">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">RM</span>
                <input type="number" id="household_income" name="household_income" step="0.01" min="0" class="input pl-8 @error('household_income') input-error @enderror"
                    value="{{ old('household_income', $profile->household_income) }}" required>
            </div>
            @error('household_income')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-on-surface-variant">
                Income category (auto-classified):
                @php
                    $income = old('household_income', $profile->household_income);
                    $category = $income ? \App\Models\IncomeCategory::classifyIncome((float)$income) : ($profile->income_category ?? null);
                @endphp
                @if($category)
                    <span class="font-medium text-primary">{{ strtoupper($category) }}</span>
                    @if($category === 'B40')
                        (≤ RM 3,169)
                    @elseif($category === 'M40')
                        (RM 3,170 – 6,339)
                    @elseif($category === 'T20')
                        (≥ RM 6,340)
                    @endif
                @else
                    <span class="text-on-surface-variant">Enter income to see category</span>
                @endif
            </p>
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

        <!-- Institution Type -->
        <div>
            <label for="institution_type" class="label">Institution Type <span class="text-error">*</span></label>
            <select id="institution_type" name="institution_type" class="input @error('institution_type') input-error @enderror" required>
                <option value="">Select Institution Type</option>
                @foreach(['Public University', 'Private University', 'Polytechnic', 'Community College', 'Foundation College', 'Other'] as $type)
                    <option value="{{ $type }}" {{ old('institution_type', $profile->institution_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            @error('institution_type')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Field of Study -->
        <div>
            <label for="field_of_study" class="label">Field of Study <span class="text-error">*</span></label>
            <input type="text" id="field_of_study" name="field_of_study" class="input @error('field_of_study') input-error @enderror"
                value="{{ old('field_of_study', $profile->field_of_study) }}" required placeholder="e.g., Engineering, Medicine, Business, Computer Science">
            @error('field_of_study')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Income Category Reference -->
        <div class="bg-surface-container-low rounded-lg p-4 border border-outline-variant">
            <h4 class="font-medium text-on-surface mb-3">Income Category Reference (Malaysia)</h4>
            <div class="grid sm:grid-cols-3 gap-4 text-sm">
                <div class="p-3 bg-success/5 rounded-lg border border-success/20">
                    <div class="font-medium text-success mb-1">B40 (Bottom 40%)</div>
                    <div class="text-on-surface-variant">≤ RM 3,169/month</div>
                </div>
                <div class="p-3 bg-warning/5 rounded-lg border border-warning/20">
                    <div class="font-medium text-warning mb-1">M40 (Middle 40%)</div>
                    <div class="text-on-surface-variant">RM 3,170 – 6,339/month</div>
                </div>
                <div class="p-3 bg-error/5 rounded-lg border border-error/20">
                    <div class="font-medium text-error mb-1">T20 (Top 20%)</div>
                    <div class="text-on-surface-variant">≥ RM 6,340/month</div>
                </div>
            </div>
            <p class="mt-3 text-xs text-on-surface-variant">Based on DOSM 2022 household income classifications. Thresholds are admin-configurable.</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection