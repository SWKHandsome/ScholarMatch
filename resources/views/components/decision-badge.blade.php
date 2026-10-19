@if($status === 'Eligible' && ($requiresVerification ?? false))
    <details class="inline-block">
        <summary class="badge badge-eligible cursor-pointer list-none">
            Eligible <span class="ml-2 rounded-full bg-warning/20 px-1.5 text-amber-800" aria-hidden="true">!</span>
            <span class="sr-only">Special requirements need verification. Expand to read.</span>
        </summary>
        <div class="mt-2 rounded-lg border border-warning/30 bg-warning/5 p-3 text-sm text-on-surface">
            <p class="font-semibold mb-1">Special requirements need verification</p>
            <p class="whitespace-pre-line">{{ $specialRequirements ?? '' }}</p>
            <p class="mt-2 text-on-surface-variant">You meet the automatic checks. Confirm these special conditions with the scholarship provider.</p>
        </div>
    </details>
@else
    <span class="badge {{ $status === 'Eligible' ? 'badge-eligible' : 'badge-not-suitable' }}">{{ $status }}</span>
@endif
