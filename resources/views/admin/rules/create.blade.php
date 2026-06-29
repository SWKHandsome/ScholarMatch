@extends('layouts.admin', ['pageTitle' => 'Add Scholarship Rules'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Add Scholarship Rules</h1>
            <p class="text-on-surface-variant mt-1">{{ $scholarship->name }}</p>
        </div>
        <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline">Back to Scholarships</a>
    </div>

    <form method="POST" action="{{ route('admin.scholarships.rules.store', $scholarship) }}" class="card p-6 space-y-6">
        @csrf

        <!-- Rule Type Legend -->
        <div class="bg-surface-container-low rounded-lg p-4 border border-outline-variant">
            <h3 class="font-medium text-on-surface mb-3">Rule Type Guide</h3>
            <div class="grid sm:grid-cols-3 gap-4 text-sm">
                <div class="p-3 bg-error/5 rounded-lg border border-error/20">
                    <div class="font-medium text-error mb-1 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-error"></span> Hard
                    </div>
                    <div class="text-on-surface-variant">Strict pass/fail filter. Student fails → Not Suitable regardless of score.</div>
                </div>
                <div class="p-3 bg-warning/5 rounded-lg border border-warning/20">
                    <div class="font-medium text-warning mb-1 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-warning"></span> Soft
                    </div>
                    <div class="text-on-surface-variant">Contributes to ranking score (0-100). Only checked after all hard rules pass.</div>
                </div>
                <div class="p-3 bg-info/5 rounded-lg border border-info/20">
                    <div class="font-medium text-info mb-1 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-info"></span> None
                    </div>
                    <div class="text-on-surface-variant">Not used for this scholarship. Field is ignored in matching.</div>
                </div>
            </div>
        </div>

        <!-- Identity Section -->
        <div class="border-t border-outline-variant pt-6">
            <h3 class="text-lg font-semibold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Identity & Nationality
            </h3>

            <div>
                <label for="required_nationality" class="label">Required Nationality</label>
                <select id="required_nationality" name="required_nationality" class="input @error('required_nationality') input-error @enderror">
                    <option value="">Any nationality</option>
                    <option value="Malaysian" {{ old('required_nationality') === 'Malaysian' ? 'selected' : '' }}>Malaysian</option>
                    <option value="Singaporean" {{ old('required_nationality') === 'Singaporean' ? 'selected' : '' }}>Singaporean</option>
                    <option value="Indonesian" {{ old('required_nationality') === 'Indonesian' ? 'selected' : '' }}>Indonesian</option>
                    <option value="Bruneian" {{ old('required_nationality') === 'Bruneian' ? 'selected' : '' }}>Bruneian</option>
                    <option value="Other" {{ old('required_nationality') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('required_nationality')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Leave empty to accept all nationalities.</p>
            </div>
        </div>

        <!-- Academic Section -->
        <div class="border-t border-outline-variant pt-6">
            <h3 class="text-lg font-semibold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Academic Requirements
            </h3>

            <div>
                <label for="required_study_level" class="label">Required Study Level</label>
                <select id="required_study_level" name="required_study_level" class="input @error('required_study_level') input-error @enderror">
                    <option value="">Any study level</option>
                    @foreach(['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'] as $level)
                        <option value="{{ $level }}" {{ old('required_study_level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                    @endforeach
                </select>
                @error('required_study_level')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Leave empty to accept all education levels.</p>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label for="min_spm_as" class="label">Min SPM As</label>
                    <input type="number" id="min_spm_as" name="min_spm_as" min="0" max="12" class="input @error('min_spm_as') input-error @enderror"
                        value="{{ old('min_spm_as') }}" placeholder="0-12">
                    @error('min_spm_as')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="min_spm_credits" class="label">Min SPM Credits</label>
                    <input type="number" id="min_spm_credits" name="min_spm_credits" min="0" max="12" class="input @error('min_spm_credits') input-error @enderror"
                        value="{{ old('min_spm_credits') }}" placeholder="0-12">
                    @error('min_spm_credits')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="min_cgpa" class="label">Min CGPA</label>
                    <input type="number" id="min_cgpa" name="min_cgpa" step="0.01" min="0" max="4" class="input @error('min_cgpa') input-error @enderror"
                        value="{{ old('min_cgpa') }}" placeholder="0.00-4.00">
                    @error('min_cgpa')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-sm text-on-surface-variant">SPM fields apply to SPM students. CGPA applies to STPM, Foundation, Matriculation, Diploma, Undergraduate students.</p>
        </div>

        <!-- Financial Section -->
        <div class="border-t border-outline-variant pt-6">
            <h3 class="text-lg font-semibold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0l1-1m-1 1l-1-1"></path></svg>
                Financial Requirements
            </h3>

            <div>
                <label for="required_income_category" class="label">Required Income Category</label>
                <select id="required_income_category" name="required_income_category" class="input @error('required_income_category') input-error @enderror">
                    <option value="">Any category</option>
                    <option value="B40" {{ old('required_income_category') === 'B40' ? 'selected' : '' }}>B40 (Bottom 40%)</option>
                    <option value="M40" {{ old('required_income_category') === 'M40' ? 'selected' : '' }}>M40 (Middle 40%)</option>
                    <option value="T20" {{ old('required_income_category') === 'T20' ? 'selected' : '' }}>T20 (Top 20%)</option>
                </select>
                @error('required_income_category')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="max_household_income" class="label">Maximum Household Income (RM/month)</label>
                <input type="number" id="max_household_income" name="max_household_income" step="0.01" min="0" class="input @error('max_household_income') input-error @enderror"
                    value="{{ old('max_household_income') }}" placeholder="e.g., 3169.00">
                @error('max_household_income')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Student's household income must be ≤ this amount. Leave empty for no limit.</p>
            </div>
        </div>

        <!-- Field & Institution Section -->
        <div class="border-t border-outline-variant pt-6">
            <h3 class="text-lg font-semibold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Field of Study & Institution
            </h3>

            <div>
                <label for="required_field_of_study" class="label">Required Field of Study</label>
                <input type="text" id="required_field_of_study" name="required_field_of_study" class="input @error('required_field_of_study') input-error @enderror"
                    value="{{ old('required_field_of_study') }}" placeholder="e.g., Engineering, Medicine, Business, Computer Science">
                @error('required_field_of_study')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Leave empty to accept all fields of study.</p>
            </div>

            <div>
                <label for="required_institution_type" class="label">Required Institution Type</label>
                <select id="required_institution_type" name="required_institution_type" class="input @error('required_institution_type') input-error @enderror">
                    <option value="">Any institution type</option>
                    @foreach(['Public University', 'Private University', 'Polytechnic', 'Community College', 'Foundation College', 'Other'] as $type)
                        <option value="{{ $type }}" {{ old('required_institution_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('required_institution_type')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Rule Types Section -->
        <div class="border-t border-outline-variant pt-6">
            <h3 class="text-lg font-semibold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Rule Types
            </h3>
            <p class="text-sm text-on-surface-variant mb-4">Set how each rule category is evaluated.</p>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="income_rule_type" class="label">Income Rule Type <span class="text-error">*</span></label>
                    <select id="income_rule_type" name="income_rule_type" class="input @error('income_rule_type') input-error @enderror" required>
                        <option value="none" {{ old('income_rule_type') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="soft" {{ old('income_rule_type') === 'soft' ? 'selected' : '' }}>Soft (scoring)</option>
                        <option value="hard" {{ old('income_rule_type') === 'hard' ? 'selected' : '' }}>Hard (filter)</option>
                    </select>
                    @error('income_rule_type')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="study_level_rule_type" class="label">Study Level Rule Type <span class="text-error">*</span></label>
                    <select id="study_level_rule_type" name="study_level_rule_type" class="input @error('study_level_rule_type') input-error @enderror" required>
                        <option value="none" {{ old('study_level_rule_type') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="soft" {{ old('study_level_rule_type') === 'soft' ? 'selected' : '' }}>Soft (scoring)</option>
                        <option value="hard" {{ old('study_level_rule_type') === 'hard' ? 'selected' : '' }}>Hard (filter)</option>
                    </select>
                    @error('study_level_rule_type')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="field_rule_type" class="label">Field of Study Rule Type <span class="text-error">*</span></label>
                    <select id="field_rule_type" name="field_rule_type" class="input @error('field_rule_type') input-error @enderror" required>
                        <option value="none" {{ old('field_rule_type') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="soft" {{ old('field_rule_type') === 'soft' ? 'selected' : '' }}>Soft (scoring)</option>
                        <option value="hard" {{ old('field_rule_type') === 'hard' ? 'selected' : '' }}>Hard (filter)</option>
                    </select>
                    @error('field_rule_type')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="institution_rule_type" class="label">Institution Type Rule Type <span class="text-error">*</span></label>
                    <select id="institution_rule_type" name="institution_rule_type" class="input @error('institution_rule_type') input-error @enderror" required>
                        <option value="none" {{ old('institution_rule_type') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="soft" {{ old('institution_rule_type') === 'soft' ? 'selected' : '' }}>Soft (scoring)</option>
                        <option value="hard" {{ old('institution_rule_type') === 'hard' ? 'selected' : '' }}>Hard (filter)</option>
                    </select>
                    @error('institution_rule_type')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('admin.scholarships.rules.index', $scholarship) }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Rules</button>
        </div>
    </form>
</div>
@endsection