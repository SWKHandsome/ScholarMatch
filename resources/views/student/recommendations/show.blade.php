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
    @if($recommendation['failed_requirements'])
        <div class="card p-5 border-l-4 border-error bg-rose-50">
            <h2 class="font-semibold text-error">Not Eligible - Failed Requirements</h2>
            <ul class="list-disc pl-5 mt-2">
                @foreach($recommendation['requirement_results'] as $check)
                    @if($check['outcome'] === 'not_met')<li class="font-medium text-rose-800">{{ $check['message'] }}</li>@endif
                @endforeach
            </ul>
        </div>
    @endif
    <section class="card p-6">
        <h2 class="text-lg font-semibold mb-4">Requirement Explanation</h2>
        @include('components.requirement-results', ['checks' => $recommendation['requirement_results']])
    </section>
    @if($recommendation['special_requirements'] !== '')
        <section class="card p-6 border-l-4 border-amber-400 bg-amber-50">
            <h2 class="font-semibold text-amber-900">Special Requirements</h2>
            <p class="mt-2 whitespace-pre-line">{{ $recommendation['special_requirements'] }}</p>
            <p class="mt-3 text-sm text-on-surface-variant">Confirm these conditions with the scholarship provider.</p>
        </section>
    @endif
</div>
@endsection
