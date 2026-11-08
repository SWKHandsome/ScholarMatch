@extends('layouts.admin', ['pageTitle' => 'Student Details'])

@section('content')
@php
    $profile = $student->studentProfile;
    $academic = $student->academicResult;
    $profileComplete = $profile?->isComplete() ?? false;
    $academicComplete = $academic?->isReadyForRecommendations() ?? false;
@endphp
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold break-words">{{ $student->name }}</h1>
            <p class="mt-1 text-on-surface-variant break-all">{{ $student->email }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">Edit Account</a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline">Back to Students</a>
        </div>
    </div>
    <section class="card p-6" aria-labelledby="account-heading">
        <h2 id="account-heading" class="text-lg font-semibold mb-4">Account Details</h2>
        <dl class="grid sm:grid-cols-2 gap-4">
            <div><dt class="text-sm text-on-surface-variant">Name</dt><dd class="mt-1 break-words">{{ $student->name }}</dd></div>
            <div><dt class="text-sm text-on-surface-variant">Email</dt><dd class="mt-1 break-all">{{ $student->email }}</dd></div>
            <div><dt class="text-sm text-on-surface-variant">Registered</dt><dd class="mt-1">{{ $student->created_at?->format('d M Y') ?? 'Not available' }}</dd></div>
            <div><dt class="text-sm text-on-surface-variant">Profile Completion</dt><dd class="mt-1"><span class="badge {{ $profileComplete && $academicComplete ? 'badge-eligible' : 'badge-partial' }}">{{ $profileComplete && $academicComplete ? 'Complete' : 'Incomplete' }}</span></dd></div>
        </dl>
    </section>
    <p class="text-sm text-on-surface-variant">The following information is read-only. Students manage their own profile and academic results.</p>
    <section class="card p-6" aria-labelledby="profile-heading">
        <h2 id="profile-heading" class="text-lg font-semibold mb-4">Student Profile <span class="badge {{ $profileComplete ? 'badge-eligible' : 'badge-partial' }}">{{ $profileComplete ? 'Complete' : 'Incomplete' }}</span></h2>
        @if($profile)
            <dl class="grid sm:grid-cols-2 gap-4">
                @foreach([
                    'Nationality' => $profile->nationality,
                    'State' => $profile->state,
                    'Monthly Household Income' => $profile->household_income !== null ? 'RM '.number_format((float) $profile->household_income, 2) : null,
                    'Income Category' => $profile->household_income !== null ? \App\Models\IncomeCategory::classifyIncome((float) $profile->household_income) : null,
                    'Number of Dependents' => $profile->number_of_dependents,
                ] as $label => $value)
                    <div><dt class="text-sm text-on-surface-variant">{{ $label }}</dt><dd class="mt-1 break-words">{{ $value ?? 'Not provided' }}</dd></div>
                @endforeach
            </dl>
        @else
            <p class="text-on-surface-variant">This student has not added a profile yet.</p>
        @endif
    </section>
    <section class="card p-6" aria-labelledby="academic-heading">
        <h2 id="academic-heading" class="text-lg font-semibold mb-4">Academic Information <span class="badge {{ $academicComplete ? 'badge-eligible' : 'badge-partial' }}">{{ $academicComplete ? 'Complete' : 'Incomplete' }}</span></h2>
        @if($academic)
            <dl class="grid sm:grid-cols-2 gap-4">
                @foreach([
                    'Latest Qualification' => \App\Support\AcademicQualifications::label($academic->education_level),
                    'Study Status' => match ($academic->study_status) { 'completed' => 'Completed', 'studying' => 'Currently studying', default => null },
                    'Year of Study' => $academic->study_status === 'studying' ? $academic->year_of_study : 'Not applicable',
                    'Study Level Seeking Funding' => $academic->funding_study_level ? \App\Support\AcademicQualifications::label($academic->funding_study_level) : null,
                    'Institution Type' => $academic->institution_type,
                    'Field of Study' => $academic->field_of_study,
                    'Result Status' => $academic->result_status === 'official' ? 'Official results' : 'Not confirmed',
                    'Grade Summary' => filled($academic->grade_counts) ? \App\Support\AcademicQualifications::summary($academic->grade_counts, $academic->grading_scale) : null,
                    'CGPA' => $academic->cgpa,
                    'SPM As' => $academic->spm_as,
                    'SPM Credits' => $academic->spm_credits,
                    'STAM Overall Grade' => $academic->stam_rank,
                    'Pengajian Am Grade' => $academic->stpm_pa_grade,
                ] as $label => $value)
                    @if($value !== null)
                        <div><dt class="text-sm text-on-surface-variant">{{ $label }}</dt><dd class="mt-1 break-words">{{ $value }}</dd></div>
                    @endif
                @endforeach
            </dl>
        @else
            <p class="text-on-surface-variant">This student has not added academic information yet.</p>
        @endif
    </section>
    <section class="card p-6" aria-labelledby="delete-heading">
        <h2 id="delete-heading" class="text-lg font-semibold text-error mb-2">Delete Student Account</h2>
        <p class="text-sm text-on-surface-variant mb-4">Deleting this account permanently removes its profile, academic results, saved scholarships and recommendation logs.</p>
        @include('admin.students._delete')
    </section>
</div>
@endsection
