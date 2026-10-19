@php
    $selectedFields = old('field_selection_submitted') ? (array) old('required_fields_of_study', []) : $rule->supportedFieldsOfStudy();
@endphp
<input type="hidden" name="rules_submitted" value="1">
<div class="rounded-lg bg-primary/5 p-4 text-sm text-on-surface-variant">
    <p>Configure only the restrictions stated by the scholarship provider. Leave a criterion empty when it does not apply; every specified requirement must be met.</p>
    <p class="mt-2">Qualification boxes are alternatives: a student needs to pass any one complete box. Nationality, funded level, income, field and institution requirements apply to all boxes.</p>
</div>
@if($errors->any())
    <div class="rounded-lg border border-error p-4 text-error" role="alert">
        <ul class="list-disc pl-5">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif
<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="label" for="required_nationality">Required Nationality</label>
        <select class="input" name="required_nationality" id="required_nationality">
            <option value="">Any nationality</option>
            @foreach(config('scholarship.nationalities') as $value)
                <option value="{{ $value }}" @selected(old('required_nationality', $rule->required_nationality) === $value)>{{ $value }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="required_study_level">Funded Study Level</label>
        <select class="input" name="required_study_level" id="required_study_level">
            <option value="">Any funding level</option>
            @foreach(\App\Models\AcademicResult::EDUCATION_LEVELS as $value)
                <option value="{{ $value }}" @selected(old('required_study_level', $rule->required_study_level) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-sm text-on-surface-variant">Compared with the student's selected level seeking funding.</p>
    </div>
</div>
@include('admin.rules._qualification-routes')
<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="label" for="required_income_category">Required Income Category</label>
        <select class="input" id="required_income_category" name="required_income_category">
            <option value="">Any category</option>
            @foreach(['B40', 'M40', 'T20'] as $value)
                <option value="{{ $value }}" @selected(old('required_income_category', $rule->required_income_category) === $value)>{{ $value }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="max_household_income">Max Household Income (RM/month)</label>
        <input class="input" type="number" id="max_household_income" name="max_household_income" min="0" step="0.01" value="{{ old('max_household_income', $rule->max_household_income) }}">
    </div>
</div>
<fieldset>
    <legend class="label">Supported Fields of Study</legend>
    <input type="hidden" name="field_selection_submitted" value="1">
    <div class="grid sm:grid-cols-2 gap-2 mt-2">
        @foreach(config('scholarship.fields_of_study') as $value)
            <label class="flex gap-2 items-center">
                <input type="checkbox" name="required_fields_of_study[]" value="{{ $value }}"
                    @checked(in_array($value, $selectedFields, true))>
                {{ $value }}
            </label>
        @endforeach
    </div>
    <p class="mt-2 text-sm text-on-surface-variant">No selection means any field is accepted.</p>
</fieldset>
<div>
    <label class="label" for="required_institution_type">Required Institution Type</label>
    <select class="input" id="required_institution_type" name="required_institution_type">
        <option value="">Any institution type</option>
        @foreach(['Public University', 'Private University', 'Polytechnic', 'Community College', 'Foundation College', 'Other'] as $value)
            <option value="{{ $value }}" @selected(old('required_institution_type', $rule->required_institution_type) === $value)>{{ $value }}</option>
        @endforeach
    </select>
</div>
