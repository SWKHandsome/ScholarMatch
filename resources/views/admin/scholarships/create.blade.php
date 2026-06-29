@extends('layouts.admin', ['pageTitle' => 'Add Scholarship'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">Add Scholarship</h1>
        <p class="text-on-surface-variant mt-1">Create a new scholarship listing</p>
    </div>

    <form method="POST" action="{{ route('admin.scholarships.store') }}" class="card p-6 space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="label">Scholarship Name <span class="text-error">*</span></label>
            <input type="text" id="name" name="name" class="input @error('name') input-error @enderror"
                value="{{ old('name') }}" required maxlength="255" placeholder="e.g., JPA B40 Undergraduate Scholarship">
            @error('name')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Provider -->
        <div>
            <label for="provider" class="label">Provider <span class="text-error">*</span></label>
            <input type="text" id="provider" name="provider" class="input @error('provider') input-error @enderror"
                value="{{ old('provider') }}" required maxlength="255" placeholder="e.g., Jabatan Perkhidmatan Awam (JPA)">
            @error('provider')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="4" class="input @error('description') input-error @enderror"
                placeholder="Describe the scholarship, eligibility overview, benefits, etc.">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Award Type & Deadline -->
        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <label for="award_type" class="label">Award Type <span class="text-error">*</span></label>
                <select id="award_type" name="award_type" class="input @error('award_type') input-error @enderror" required>
                    <option value="">Select Award Type</option>
                    @foreach(['Full Scholarship', 'Partial Scholarship', 'Tuition Fee Waiver', 'Living Allowance', 'Loan', 'Convertible Loan'] as $type)
                        <option value="{{ $type }}" {{ old('award_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('award_type')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deadline" class="label">Application Deadline <span class="text-error">*</span></label>
                <input type="date" id="deadline" name="deadline" class="input @error('deadline') input-error @enderror"
                    value="{{ old('deadline') }}" required min="{{ now()->format('Y-m-d') }}">
                @error('deadline')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Application Link -->
        <div>
            <label for="application_link" class="label">Application Link <span class="text-error">*</span></label>
            <input type="url" id="application_link" name="application_link" class="input @error('application_link') input-error @enderror"
                value="{{ old('application_link') }}" required placeholder="https://example.com/apply">
            @error('application_link')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active" class="label mb-0 cursor-pointer">Active (visible to students)</label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Scholarship</button>
        </div>
    </form>
</div>
@endsection