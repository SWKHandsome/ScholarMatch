@props([
    'recommendation',
    'showSaveButton' => true,
    'showDetailLink' => true,
    'showApplyButton' => true,
])

<?php
$status = $recommendation['status'] ?? 'Not Suitable';
$score = $recommendation['score'] ?? 0;
// Support both old (Eloquent model) and new (array data) structures
$scholarshipData = $recommendation['scholarship_data'] ?? null;
$scholarshipModel = $recommendation['scholarship'] ?? null;
$scholarship = $scholarshipData ?: $scholarshipModel;
$isArray = is_array($scholarship);

// Helper to safely get value from array or object
$get = function($key, $default = '') use ($scholarship, $isArray) {
    if ($isArray) {
        return $scholarship[$key] ?? $default;
    }
    return $scholarship->$key ?? $default;
};

$explanations = $recommendation['explanation'] ?? [];
$breakdown = $recommendation['score_breakdown'] ?? [];
$isPreliminary = $recommendation['is_preliminary'] ?? false;
$failedRules = $recommendation['failed_hard_rules'] ?? [];
?>

@if($scholarship)
<div class="card p-5 flex flex-col h-full">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-on-surface truncate">
                <a href="{{ route('student.recommendations.show', $get('id')) }}"
                    class="hover:text-primary hover:underline transition-colors">
                    {{ $get('name') }}
                </a>
            </h3>
            <p class="text-sm text-on-surface-variant mt-1">{{ $get('provider_name') }}</p>
        </div>
        <!-- Score Circle -->
        <div class="flex-shrink-0 progress-circle w-16 h-16">
            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
                <circle
                    cx="32" cy="32" r="28"
                    class="text-surface-container-highest"
                    fill="none" stroke="currentColor" stroke-width="4"
                />
                <circle
                    cx="32" cy="32" r="28"
                    class="{{ $status === 'Eligible' ? 'text-success' : ($status === 'Partially Eligible' ? 'text-warning' : 'text-error') }}"
                    fill="none" stroke="currentColor" stroke-width="4"
                    stroke-linecap="round"
                    stroke-dasharray="{{ 2 * pi() * 28 }}"
                    stroke-dashoffset="{{ (1 - $score / 100) * (2 * pi() * 28) }}"
                    style="transition: stroke-dashoffset 0.8s ease-out;"
                />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-sm font-bold text-on-surface">{{ $score }}</span>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-3 flex items-center gap-2 flex-wrap">
        <span class="badge
            {{ $status === 'Eligible' ? 'badge-eligible' : ($status === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable') }}">
            {{ $status }}
        </span>
        @if($isPreliminary)
            <span class="badge badge-info">Preliminary</span>
        @endif
        @php
            $deadline = $get('deadline');
        @endphp
        @if($deadline && \Carbon\Carbon::parse($deadline)->isPast())
            <span class="badge badge-error">Expired</span>
        @endif
    </div>

    <!-- Description -->
    <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">{{ $get('description') }}</p>

    <!-- Award Type & Deadline -->
    <div class="mb-4 flex flex-wrap items-center gap-4 text-sm text-on-surface-variant">
        <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            {{ $get('award_type') }}
        </span>
        <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Deadline: {{ \Carbon\Carbon::parse($deadline)->format('M d, Y') }}
        </span>
    </div>

    <!-- Explanation Accordion -->
    @if(!empty($explanations))
    <div class="mb-4 border-t border-outline-variant pt-4">
        <button type="button"
            class="w-full text-left text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-between"
            onclick="toggleExplanation(this)">
            <span>View Explanation ({{ count($explanations) }})</span>
            <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="mt-2 space-y-1 hidden">
            @foreach($explanations as $explanation)
                <p class="text-sm text-on-surface-variant flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0
                        {{ str_contains($explanation, 'does not meet') || str_contains($explanation, 'exceeds') || str_contains($explanation, 'not match') ? 'text-error' : 'text-success' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ str_contains($explanation, 'does not meet') || str_contains($explanation, 'exceeds') || str_contains($explanation, 'not match') ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"></path>
                    </svg>
                    {{ $explanation }}
                </p>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Score Breakdown -->
    @if(!empty($breakdown) && array_sum($breakdown) > 0)
    <div class="mb-4 border-t border-outline-variant pt-4">
        <h4 class="text-sm font-medium text-on-surface mb-2">Score Breakdown</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            @foreach(['academic' => 'Academic', 'field' => 'Field', 'institution' => 'Institution', 'income' => 'Income'] as $key => $label)
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">{{ $label }}</span>
                    <span class="font-medium {{ ($breakdown[$key] ?? 0) > 0 ? 'text-success' : 'text-on-surface-variant' }}">{{ $breakdown[$key] ?? 0 }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="mt-auto flex flex-wrap gap-2 pt-4 border-t border-outline-variant">
        <a href="{{ route('student.recommendations.show', $get('id')) }}"
            class="flex-1 btn btn-ghost text-center text-sm">
            View Details
        </a>

        @php
            $applicationLink = $get('application_link');
        @endphp
        @if($showApplyButton && $applicationLink)
            <a href="{{ $applicationLink }}" target="_blank" rel="noopener noreferrer"
                class="flex-1 btn btn-primary text-center text-sm">
                Apply Now
            </a>
        @endif

        @php
            $scholarshipId = $get('id');
        @endphp
        @if($showSaveButton)
            <form action="{{ route('student.saved-scholarships.store') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="scholarship_id" value="{{ $scholarshipId }}">
                <button type="submit"
                    class="w-full btn btn-outline text-center text-sm"
                    {{ auth()->user()->savedScholarships()->where('scholarship_id', $scholarshipId)->exists() ? 'disabled' : '' }}>
                    {{ auth()->user()->savedScholarships()->where('scholarship_id', $scholarshipId)->exists() ? 'Saved' : 'Save' }}
                </button>
            </form>
        @endif
    </div>
</div>
@endif