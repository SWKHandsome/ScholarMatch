@extends('layouts.student', ['pageTitle' => 'Saved Scholarships'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-on-surface">Saved Scholarships</h1>
            <p class="text-on-surface-variant mt-1">Scholarships you've bookmarked for later review.</p>
        </div>
        <a href="{{ route('student.recommendations') }}" class="btn btn-primary w-full sm:w-auto sm:shrink-0">Find More Scholarships</a>
    </div>

    @if($savedScholarships->isEmpty())
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Saved Scholarships</h3>
            <p class="text-on-surface-variant mb-6">Start exploring recommendations and save scholarships you're interested in.</p>
            <a href="{{ route('student.recommendations') }}" class="btn btn-primary">View Recommendations</a>
        </div>
    @else
        <div class="space-y-5">
            @foreach($savedScholarships as $saved)
                @php
                    $scholarship = $saved->scholarship;
                    $recommendation = $recommendations->get($scholarship->id);
                    $unavailableMessage = $scholarship->isExpired()
                        ? 'This scholarship has expired. Check with the provider for a new application round.'
                        : (! $scholarship->is_active
                            ? 'This scholarship is no longer active.'
                            : ($evaluationError ?: 'Eligibility is unavailable while this scholarship’s requirements are being configured.'));
                @endphp
                @include('components.scholarship-card', [
                    'recommendation' => $recommendation,
                    'scholarship' => $scholarship,
                    'showSaveButton' => false,
                    'showRemoveButton' => true,
                    'unavailableMessage' => $unavailableMessage,
                ])
            @endforeach
        </div>
        @if($savedScholarships->hasPages())
            <div class="mt-5">{{ $savedScholarships->onEachSide(1)->links() }}</div>
        @endif
    @endif
</div>
@endsection
