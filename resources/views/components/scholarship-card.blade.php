@php
    $data = $recommendation['scholarship_data'];
    $status = $recommendation['status'];
    $showSaveButton = $showSaveButton ?? true;
    $showApplyButton = $showApplyButton ?? true;
    $showDetailLink = $showDetailLink ?? true;
    $saved = auth()->user()->savedScholarships()->where('scholarship_id', $data['id'])->exists();
@endphp
<article class="card p-5 flex flex-col h-full">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-semibold break-words"><a class="hover:text-primary hover:underline" href="{{ route('student.recommendations.show', $data['id']) }}">{{ $data['name'] }}</a></h3>
            <p class="text-sm text-on-surface-variant mt-1 break-words">{{ $data['provider_name'] }}</p>
        </div>
        <div class="shrink-0">
            @include('components.decision-badge', ['status' => $status, 'requiresVerification' => $recommendation['requires_verification'], 'specialRequirements' => $recommendation['special_requirements'], 'alignRight' => true])
        </div>
    </div>
    <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">{{ $data['description'] }}</p>
    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <span class="rounded-md bg-primary/5 px-2.5 py-1 font-semibold text-primary">{{ $data['award_type'] }}</span>
        <span class="rounded-md bg-surface-container-low px-2.5 py-1 text-on-surface-variant">Deadline: <strong class="font-semibold text-on-surface">{{ $data['deadline_label'] ?? ($data['deadline'] ? \Carbon\Carbon::parse($data['deadline'])->format('M d, Y') : 'Check provider') }}</strong></span>
    </div>
    @if(!empty($data['application_notes']))
        <p class="mb-4 text-sm text-on-surface-variant">{{ $data['application_notes'] }}</p>
    @endif
    <details class="mb-4 border-t border-outline-variant pt-3">
        <summary class="cursor-pointer text-sm font-medium text-primary">View Explanation</summary>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach($recommendation['requirement_results'] as $check)
                @if($check['outcome'] !== 'not_applicable')
                    <li data-explanation-status="{{ $check['outcome'] === 'not_met' ? 'failed' : 'passed' }}" class="leading-relaxed {{ $check['outcome'] === 'not_met' ? 'text-rose-800' : 'text-emerald-800' }}">
                        <span class="font-semibold">{{ $check['outcome'] === 'not_met' ? 'Not Met:' : 'Met:' }}</span> {{ $check['message'] }}
                    </li>
                @endif
            @endforeach
        </ul>
    </details>
    <div class="mt-auto flex flex-wrap gap-2 pt-4 border-t border-outline-variant">
        @if($showDetailLink)<a class="btn btn-outline text-sm" href="{{ route('student.recommendations.show', $data['id']) }}">View Details</a>@endif
        @if($showApplyButton && $status === 'Eligible')
            <a class="btn btn-primary text-sm" href="{{ $data['application_link'] }}" target="_blank" rel="noopener noreferrer">{{ !empty($data['is_demo']) || $data['deadline'] ? 'Apply Now' : 'Check Provider' }}</a>
        @endif
        @if($showSaveButton)
            <form action="{{ route('student.saved-scholarships.store') }}" method="POST">
                @csrf
                <input type="hidden" name="scholarship_id" value="{{ $data['id'] }}">
                <button type="submit" class="btn btn-outline text-sm" @disabled($saved)>{{ $saved ? 'Saved' : 'Save' }}</button>
            </form>
        @endif
    </div>
</article>
