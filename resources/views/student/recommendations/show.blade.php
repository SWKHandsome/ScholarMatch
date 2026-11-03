@extends('layouts.student', ['pageTitle' => 'Scholarship Details'])
@section('content')
@php($scholarship = $recommendation['scholarship'])
<div class="max-w-5xl mx-auto space-y-6">
    <a href="{{ route('student.recommendations') }}" class="text-primary hover:underline">Back to Recommendations</a>
    <div class="card p-6">
        <h1 class="text-2xl font-bold mb-1">{{ $scholarship->name }}</h1>
        <p class="text-on-surface-variant mb-4">{{ $scholarship->provider }}</p>
        @include('components.decision-badge', ['status' => $recommendation['status'], 'requiresVerification' => $recommendation['requires_verification'], 'specialRequirements' => $recommendation['special_requirements']])
        <p class="mt-5 whitespace-pre-line">{{ $scholarship->description }}</p>
        <dl class="grid sm:grid-cols-2 gap-4 mt-5">
            <div class="rounded-lg bg-primary/5 p-3"><dt class="text-sm text-on-surface-variant">Award Type</dt><dd class="mt-1 font-semibold text-primary">{{ $scholarship->award_type }}</dd></div>
            <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-sm text-on-surface-variant">Deadline</dt><dd class="mt-1 font-semibold">{{ $scholarship->deadlineLabel() }}</dd></div>
        </dl>
        @include('components.scholarship-source', ['scholarship' => $scholarship])
        <div class="mt-5 flex flex-wrap gap-3">
            @if($recommendation['status'] === 'Eligible')
                <a class="btn btn-primary" href="{{ $scholarship->application_link }}" target="_blank" rel="noopener noreferrer">{{ $scholarship->isDemo() || $scholarship->deadline ? 'Apply Now' : 'Check Provider' }}</a>
            @endif
            <form method="POST" action="{{ route('student.saved-scholarships.store') }}">
                @csrf
                <input type="hidden" name="scholarship_id" value="{{ $scholarship->id }}">
                <button type="submit" class="btn btn-outline" @disabled($isSaved)>{{ $isSaved ? 'Saved' : 'Save Scholarship' }}</button>
            </form>
        </div>
    </div>
    <section class="card p-6">
        <h2 class="text-lg font-semibold mb-4">Requirement Explanation</h2>
        @include('components.student-requirement-explanation')
    </section>
</div>
@endsection
