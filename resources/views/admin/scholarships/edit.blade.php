@extends('layouts.admin', ['pageTitle' => 'Edit Scholarship'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">Edit Scholarship</h1>
        <p class="text-on-surface-variant mt-1">{{ $scholarship->name }}</p>
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

        @include('admin.scholarships._description')

        <!-- Award Type & Deadline -->
        <div class="grid sm:grid-cols-2 gap-6">
            @include('admin.scholarships._award-type')

            <div>
                <label for="deadline" class="label">Application Deadline (if confirmed)</label>
                <input type="date" id="deadline" name="deadline" class="input @error('deadline') input-error @enderror"
                    value="{{ old('deadline', $scholarship->deadline?->format('Y-m-d')) }}">
                <p class="mt-1 text-sm text-on-surface-variant">Leave blank when there is no confirmed date. Students will see the Deadline Basis.</p>
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
        @include('admin.scholarships._special-requirements')
        @include('admin.scholarships._deadline-basis')
        @include('components.scholarship-source', ['scholarship' => $scholarship])

        <div class="flex items-center gap-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" {{ old('is_active', $scholarship->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="label mb-0 cursor-pointer">Active (visible to students)</label>
        </div>

        <!-- Actions -->
        <section class="border-t border-outline-variant pt-6 space-y-6">
            <h2 class="font-semibold text-xl">Eligibility Requirements</h2>
            @include('admin.rules._form')
        </section>

        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Scholarship</button>
        </div>
    </form>
</div>
@endsection
