@php
    $supportedFields = $item['supported_fields'] ?? [];
    $expandFields = count($supportedFields) > 1;
@endphp
<div class="border-b border-outline-variant py-4 last:border-0" data-explanation-status="{{ match ($item['outcome']) { 'not_met' => 'failed', 'not_checked' => 'not-checked', default => 'passed' } }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-semibold {{ $item['outcome'] === 'not_met' ? 'text-rose-800' : 'text-on-surface' }}">{{ $item['label'] }}</p>
        @include('components.requirement-status', ['outcome' => $item['outcome']])
    </div>
    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
        <div><dt class="text-on-surface-variant">Required</dt><dd class="mt-1 break-words font-semibold">{{ $expandFields ? 'One of '.count($supportedFields).' supported fields' : $item['required'] }}</dd></div>
        <div><dt class="text-on-surface-variant">{{ $item['key'] === 'deadline' ? 'Today' : 'Your information' }}</dt><dd class="mt-1 break-words font-semibold {{ match ($item['outcome']) { 'not_met' => 'text-rose-800', 'not_checked' => 'text-blue-800', default => 'text-emerald-800' } }}">{{ $item['actual'] }}</dd></div>
    </dl>
    @if($expandFields)
        <details class="mt-3 text-sm">
            <summary class="cursor-pointer font-medium text-primary">View supported fields ({{ count($supportedFields) }})</summary>
            <ul class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                @foreach($supportedFields as $field)
                    @php($matches = $item['outcome'] === 'met' && $field === $item['actual'])
                    <li class="flex items-start gap-2 break-words {{ $matches ? 'font-semibold text-emerald-800' : 'text-on-surface-variant' }}">
                        <span aria-hidden="true">•</span>
                        <span class="min-w-0">{{ $field }}@if($matches) <span aria-label="Your matching field">✓</span>@endif</span>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</div>
