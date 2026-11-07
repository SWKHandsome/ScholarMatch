@if($scholarship->isDemo())
    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-semibold">Fictional demo scholarship</p>
        <p class="mt-1">This award is for demonstrating ScholarMatch. No real funding or applications are available.</p>
    </div>
@elseif($scholarship->application_notes || $scholarship->source_url)
    <div class="mt-4 space-y-2 rounded-lg border border-outline-variant p-4 text-sm text-on-surface-variant">
        @if($scholarship->deadline_type && $scholarship->deadline_type !== 'unconfirmed')
            <p class="font-medium">{{ \App\Models\Scholarship::DEADLINE_TYPES[$scholarship->deadline_type] ?? 'Check provider' }}</p>
        @endif
        @if($scholarship->application_notes)
            <p>{{ $scholarship->application_notes }}</p>
        @endif
        @if($scholarship->source_url)
            <p>
                <a class="font-medium text-primary hover:underline" href="{{ $scholarship->source_url }}" target="_blank" rel="noopener noreferrer">Official scholarship source</a>
                @if($scholarship->source_checked_at)
                    <span> · Source checked {{ $scholarship->source_checked_at->format('d M Y') }}</span>
                @endif
            </p>
            <p>Programme information was checked against the provider's published material. Confirm current availability and all conditions with the provider.</p>
        @endif
    </div>
@endif
