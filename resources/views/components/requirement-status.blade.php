@php
    $statusStyle = match ($outcome) {
        'met' => ['bg-emerald-100 text-emerald-800', '✓', 'Met'],
        'not_met' => ['bg-rose-100 text-rose-800', '✕', 'Not Met'],
        default => ['bg-surface-container text-on-surface-variant', '—', 'Not Applicable'],
    };
@endphp
<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyle[0] }}">
    <span aria-hidden="true">{{ $statusStyle[1] }}</span>{{ $statusStyle[2] }}
</span>
