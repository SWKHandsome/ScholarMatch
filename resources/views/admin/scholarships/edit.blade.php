@extends('layouts.admin', ['pageTitle' => 'Edit Scholarship'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Edit Scholarship</h1>
            <p class="text-on-surface-variant mt-1">{{ $scholarship->name }}</p>
        </div>
        @if($scholarship->rule)
            <a href="{{ route('admin.scholarships.rules.index', $scholarship) }}"
                class="btn btn-outline text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Manage Rules
            </a>
        @else
            <a href="{{ route('admin.scholarships.rules.create', $scholarship) }}"
                class="btn btn-primary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Rules
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.scholarships.update', $scholarship) }}" class="card p-6 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div>
            <label for="name" class="label">Scholarship Name <span class="text-error">*</span></label>
            <input type="text" id="name" name="name" class="input @error('name') input-error @enderror"
                value="{{ old('name', $scholarship->name) }}" required maxlength="255">
            @error('name')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Provider -->
        <div>
            <label for="provider" class="label">Provider <span class="text-error">*</span></label>
            <input type="text" id="provider" name="provider" class="input @error('provider') input-error @enderror"
                value="{{ old('provider', $scholarship->provider) }}" required maxlength="255">
            @error('provider')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="4" class="input @error('description') input-error @enderror"
                placeholder="Describe the scholarship, eligibility overview, benefits, etc.">{{ old('description', $scholarship->description) }}</textarea>
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
                        <option value="{{ $type }}" {{ old('award_type', $scholarship->award_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('award_type')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deadline" class="label">Application Deadline <span class="text-error">*</span></label>
                <input type="date" id="deadline" name="deadline" class="input @error('deadline') input-error @enderror"
                    value="{{ old('deadline', $scholarship->deadline->format('Y-m-d')) }}" required min="{{ now()->format('Y-m-d') }}">
                @error('deadline')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Application Link -->
        <div>
            <label for="application_link" class="label">Application Link <span class="text-error">*</span></label>
            <input type="url" id="application_link" name="application_link" class="input @error('application_link') input-error @enderror"
                value="{{ old('application_link', $scholarship->application_link) }}" required placeholder="https://example.com/apply">
            @error('application_link')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" {{ old('is_active', $scholarship->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="label mb-0 cursor-pointer">Active (visible to students)</label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Scholarship</button>
        </div>
    </form>
</div>
@endsection