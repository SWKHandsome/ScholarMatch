@extends('layouts.admin', ['pageTitle' => 'Recommendation Log Details'])
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Recommendation Log Details</h1>
    <a class="text-primary hover:underline" href="{{ route('admin.recommendation-logs.index') }}">Back to Logs</a>
    <div class="card p-6">
        <h2 class="text-lg font-semibold">{{ $recommendationLog->scholarship->name }}</h2>
        <p class="mt-2">{{ $recommendationLog->user->name }} — {{ $recommendationLog->user->email }}</p>
        <p class="text-sm text-on-surface-variant mt-1">Last evaluated {{ $recommendationLog->updated_at->format('M d, Y H:i') }}</p>
        <div class="mt-4">
            @if($recommendationLog->isCurrentEvaluation())
                @include('components.decision-badge', ['status' => $recommendationLog->status, 'requiresVerification' => $recommendationLog->requires_verification, 'specialRequirements' => $recommendationLog->special_requirements])
            @else
                <p class="text-on-surface-variant">Previous Evaluation — re-evaluation required. The student must open recommendations to generate a current decision and complete academic information first if prompted.</p>
            @endif
        </div>
    </div>
    @if($recommendationLog->isCurrentEvaluation())
        <section class="card p-6">
            <h2 class="text-lg font-semibold mb-4">Requirement Explanation</h2>
            @include('components.requirement-results', ['checks' => $recommendationLog->requirement_results ?? []])
        </section>
        @if($recommendationLog->failed_requirements)
            <section class="card p-6"><h2 class="font-semibold text-error">Failed Requirements</h2><p class="mt-2">{{ collect($recommendationLog->requirement_results)->where('outcome', 'not_met')->pluck('label')->join(', ') }}</p></section>
        @endif
        @if($recommendationLog->special_requirements)
            <section class="card p-6"><h2 class="font-semibold">Special Requirements</h2><p class="mt-2 whitespace-pre-line">{{ $recommendationLog->special_requirements }}</p></section>
        @endif
    @endif
    @if($recommendationLog->explanation)
        <section class="card p-6">
            <h2 class="font-semibold mb-3">{{ $recommendationLog->isCurrentEvaluation() ? 'Explanation' : 'Previous Explanation' }}</h2>
            <ul class="list-disc pl-5 space-y-2">@foreach($recommendationLog->explanation as $message)<li>{{ $message }}</li>@endforeach</ul>
        </section>
    @endif
</div>
@endsection
