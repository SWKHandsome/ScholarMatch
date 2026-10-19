@php
    $data = $recommendation['scholarship_data'];
    $status = $recommendation['status'];
    $showSaveButton = $showSaveButton ?? true;
    $showApplyButton = $showApplyButton ?? true;
    $showDetailLink = $showDetailLink ?? true;
    $saved = auth()->user()->savedScholarships()->where('scholarship_id', $data['id'])->exists();
@endphp
<article class="card p-5 flex flex-col h-full">
    <div class="mb-3">
        <h3 class="text-lg font-semibold"><a class="hover:text-primary hover:underline" href="{{ route('student.recommendations.show', $data['id']) }}">{{ $data['name'] }}</a></h3>
        <p class="text-sm text-on-surface-variant mt-1">{{ $data['provider_name'] }}</p>
    </div>
    <div class="mb-4">
        @include('components.decision-badge', ['status' => $status, 'requiresVerification' => $recommendation['requires_verification'], 'specialRequirements' => $recommendation['special_requirements']])
    </div>
    <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">{{ $data['description'] }}</p>
    <p class="text-sm mb-1">{{ $data['award_type'] }}</p>
    <p class="text-sm text-on-surface-variant mb-4">Deadline: {{ \Carbon\Carbon::parse($data['deadline'])->format('M d, Y') }}</p>
    <details class="mb-4 border-t border-outline-variant pt-3">
        <summary class="cursor-pointer text-sm font-medium text-primary">View Explanation</summary>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach($recommendation['requirement_results'] as $check)
                @if($check['outcome'] !== 'not_applicable')
                    <li data-explanation-status="{{ $check['outcome'] === 'not_met' ? 'failed' : 'passed' }}" class="{{ $check['outcome'] === 'not_met' ? 'text-error' : 'text-on-surface-variant' }}">
                        <span class="font-semibold">{{ $check['outcome'] === 'not_met' ? 'Not Met:' : 'Met:' }}</span> {{ $check['message'] }}
                    </li>
                @endif
            @endforeach
        </ul>
    </details>
    <div class="mt-auto flex flex-wrap gap-2 pt-4 border-t border-outline-variant">
        @if($showDetailLink)<a class="btn btn-ghost text-sm" href="{{ route('student.recommendations.show', $data['id']) }}">View Details</a>@endif
        @if($showApplyButton && $status === 'Eligible')
            <a class="btn btn-primary text-sm" href="{{ $data['application_link'] }}" target="_blank" rel="noopener noreferrer">Apply Now</a>
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
