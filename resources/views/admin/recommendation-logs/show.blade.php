@extends('layouts.admin', ['pageTitle' => 'Recommendation Log Details'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Recommendation Log Details</h1>
            <p class="text-on-surface-variant mt-1">View detailed recommendation analysis</p>
        </div>
        <a href="{{ route('admin.recommendation-logs.index') }}" class="btn btn-outline">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m7 7H3"></path></svg>
            Back to Logs
        </a>
    </div>

    <div class="card p-6">
        <div class="grid sm:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-on-surface mb-3">Student Information</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm text-on-surface-variant">Name</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-on-surface-variant">Email</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->user->email }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-on-surface mb-3">Scholarship Information</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm text-on-surface-variant">Name</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->scholarship->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-on-surface-variant">Provider</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->scholarship->provider }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-on-surface-variant">Award Type</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->scholarship->award_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-on-surface-variant">Deadline</dt>
                        <dd class="font-medium text-on-surface">{{ $recommendationLog->scholarship->deadline?->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-on-surface-variant">Application Link</dt>
                        <dd><a href="{{ $recommendationLog->scholarship->application_link }}" target="_blank" class="text-primary hover:underline">View</a></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mb-6 p-4 rounded-lg bg-surface-container-low">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full border-4 border-primary flex items-center justify-center">
                        <span class="text-2xl font-bold text-primary">{{ $recommendationLog->score }}</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Match Score</p>
                        <p class="text-3xl font-bold text-on-surface">{{ $recommendationLog->score }} / 100</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge
                        {{ $recommendationLog->status === 'Eligible' ? 'badge-eligible' : ($recommendationLog->status === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable') }}">
                        {{ $recommendationLog->status }}
                    </span>
                    <span class="text-sm text-on-surface-variant">Logged {{ $recommendationLog->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        @if(!empty($recommendationLog->failed_hard_rules))
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-on-surface mb-3">Failed Hard Rules</h3>
            <div class="card p-4 border-l-4 border-error">
                <ul class="space-y-2">
                    @foreach($recommendationLog->failed_hard_rules as $rule)
                        <li class="flex items-center gap-2 text-error">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 10-2 0v6a1 1 0 102 0V5z" clip-rule="evenodd"></path></svg>
                            <span class="font-medium text-on-surface">{{ $rule }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(!empty($recommendationLog->explanation))
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-on-surface mb-3">Explanation</h3>
            <div class="card p-4">
                <ul class="space-y-2">
                    @foreach($recommendationLog->explanation as $message)
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <span class="text-on-surface">{{ $message }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(!empty($recommendationLog->score_breakdown))
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-on-surface mb-3">Score Breakdown</h3>
            <div class="card p-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach(['academic' => 'Academic', 'field' => 'Field of Study', 'institution' => 'Institution Type', 'income' => 'Income Priority'] as $key => $label)
                        <div>
                            <dt class="text-sm text-on-surface-variant">{{ $label }}</dt>
                            <dd class="text-2xl font-bold text-on-surface">{{ $recommendationLog->score_breakdown[$key] ?? 0 }}</dd>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-outline-variant flex items-center justify-between">
                    <span class="text-sm text-on-surface-variant">Total</span>
                    <span class="text-2xl font-bold text-on-surface">{{ array_sum($recommendationLog->score_breakdown) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection