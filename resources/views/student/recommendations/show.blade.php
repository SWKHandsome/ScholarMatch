@extends('layouts.student', ['pageTitle' => $recommendation['scholarship']->name])

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header with Score & Status -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-on-surface truncate">{{ $recommendation['scholarship']->name }}</h1>
            <p class="text-on-surface-variant mt-1">{{ $recommendation['scholarship']->provider }}</p>
        </div>
        <div class="flex-shrink-0 flex lg:flex-col lg:items-end gap-3">
            <!-- Circular Progress Score -->
            <div class="progress-circle w-20 h-20">
                <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 80 80">
                    <circle
                        cx="40" cy="40" r="36"
                        class="text-surface-container-highest"
                        fill="none" stroke="currentColor" stroke-width="6"
                    />
                    <circle
                        cx="40" cy="40" r="36"
                        class="{{ $recommendation['status'] === 'Eligible' ? 'text-success' : ($recommendation['status'] === 'Partially Eligible' ? 'text-warning' : 'text-error') }}"
                        fill="none" stroke="currentColor" stroke-width="6"
                        stroke-linecap="round"
                        stroke-dasharray="{{ 2 * pi() * 36 }}"
                        stroke-dashoffset="{{ (1 - $recommendation['score'] / 100) * (2 * pi() * 36) }}"
                        style="transition: stroke-dashoffset 0.8s ease-out;"
                    />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold text-on-surface">{{ $recommendation['score'] }}</span>
                </div>
            </div>
            <!-- Status Badges -->
            <div class="flex flex-col items-end gap-1">
                <span class="badge
                    {{ $recommendation['status'] === 'Eligible' ? 'badge-eligible' : ($recommendation['status'] === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable') }}">
                    {{ $recommendation['status'] }}
                </span>
                @if($recommendation['is_preliminary'])
                    <span class="badge badge-info">Preliminary</span>
                @endif
                @if($recommendation['scholarship']->deadline && \Carbon\Carbon::parse($recommendation['scholarship']->deadline)->isPast())
                    <span class="badge badge-error">Expired</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-on-surface mb-3">Description</h2>
                <p class="text-on-surface-variant whitespace-pre-line">{{ $recommendation['scholarship']->description }}</p>
            </div>

            <!-- Eligibility Explanation -->
            @if(!empty($recommendation['explanation']))
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-on-surface mb-4">Eligibility Explanation</h2>
                <button type="button"
                    class="w-full text-left text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-between mb-2"
                    onclick="toggleExplanation(this)">
                    <span>View All Explanations ({{ count($recommendation['explanation']) }})</span>
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="space-y-2 hidden" style="display: none;">
                    @foreach($recommendation['explanation'] as $explanation)
                        <p class="text-sm text-on-surface-variant flex items-start gap-2 p-2 rounded bg-surface-container-low">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0
                                {{ (str_contains($explanation, 'does not meet') || str_contains($explanation, 'exceeds') || str_contains($explanation, 'not match') || str_contains($explanation, 'passed its deadline')) ? 'text-error' : 'text-success' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ (str_contains($explanation, 'does not meet') || str_contains($explanation, 'exceeds') || str_contains($explanation, 'not match') || str_contains($explanation, 'passed its deadline')) ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"></path>
                            </svg>
                            {{ $explanation }}
                        </p>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Score Breakdown -->
            @if(!empty($recommendation['score_breakdown']) && array_sum($recommendation['score_breakdown']) > 0)
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-on-surface mb-4">Score Breakdown</h2>
                <div class="grid grid-cols-2 gap-4">
                    @foreach(['academic' => 'Academic (40)', 'field' => 'Field of Study (25)', 'institution' => 'Institution (20)', 'income' => 'Income Priority (15)'] as $key => $label)
                        <div class="flex flex-col gap-1 p-3 rounded-lg bg-surface-container-low">
                            <span class="text-xs text-on-surface-variant font-medium">{{ $label }}</span>
                            <span class="text-2xl font-bold {{ ($recommendation['score_breakdown'][$key] ?? 0) > 0 ? 'text-success' : 'text-on-surface-variant' }}">{{ $recommendation['score_breakdown'][$key] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-on-surface-variant mt-3">Total: <span class="font-bold text-on-surface">{{ array_sum($recommendation['score_breakdown']) }}/100</span></p>
            </div>
            @endif

            <!-- Failed Hard Rules -->
            @if(!empty($recommendation['failed_hard_rules']))
            <div class="card p-6 border-l-4 border-error">
                <h2 class="text-lg font-semibold text-error mb-3">Not Eligible - Failed Requirements</h2>
                <ul class="space-y-2">
                    @foreach($recommendation['failed_hard_rules'] as $rule)
                        <li class="text-sm text-on-surface-variant flex items-center gap-2">
                            <svg class="w-4 h-4 text-error flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span class="capitalize">{{ str_replace">{{ str_replace('_', ' ', $rule) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Scholarship Details -->
            <div class="card p-6 space-y-4">
                <h3 class="text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Details</h3>

                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant">Award Type</p>
                        <p class="font-medium text-on-surface">{{ $recommendation['scholarship']->award_type }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant">Deadline</p>
                        <p class="font-medium text-on-surface {{ \Carbon\Carbon::parse($recommendation['scholarship']->deadline)->isPast() ? 'text-error' : '' }}">
                            {{ \Carbon\Carbon::parse($recommendation['scholarship']->deadline)->format('M d, Y') }}
                            @if(\Carbon\Carbon::parse($recommendation['scholarship']->deadline)->isPast())
                                <span class="text-xs text-error ml-1">(Passed)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-on-surface-variant">Application Link</p>
                        <a href="{{ $recommendation['scholarship']->application_link }}" target="_blank" rel="noopener noreferrer"
                            class="text-sm text-primary hover:underline break-all">{{ $recommendation['scholarship']->application_link }}</a>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card p-6 space-y-3">
                <a href="{{ $recommendation['scholarship']->application_link }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-primary w-full text-center">
                    Apply Now
                </a>

                <form action="{{ route('student.saved-scholarships.store') }}" method="POST" class="flex">
                    @csrf
                    <input type="hidden" name="scholarship_id" value="{{ $recommendation['scholarship']->id }}">
                    <button type="submit"
                        class="btn {{ $isSaved ? 'btn-secondary' : 'btn-outline' }} w-full"
                        {{ $isSaved ? 'disabled' : '' }}>
                        {{ $isSaved ? 'Saved' : 'Save Scholarship' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleExplanation(btn) {
    const panel = btn.nextElementSibling;
    const icon = btn.querySelector('svg');
    panel.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endpush