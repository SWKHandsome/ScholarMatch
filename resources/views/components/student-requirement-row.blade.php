<div class="border-b border-outline-variant py-4 last:border-0" data-explanation-status="{{ $item['outcome'] === 'not_met' ? 'failed' : 'passed' }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-semibold {{ $item['outcome'] === 'not_met' ? 'text-rose-800' : 'text-on-surface' }}">{{ $item['label'] }}</p>
        @include('components.requirement-status', ['outcome' => $item['outcome']])
    </div>
    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
        <div><dt class="text-on-surface-variant">Required</dt><dd class="mt-1 break-words font-semibold">{{ $item['required'] }}</dd></div>
        <div><dt class="text-on-surface-variant">{{ $item['key'] === 'deadline' ? 'Today' : 'Your information' }}</dt><dd class="mt-1 break-words font-semibold {{ $item['outcome'] === 'not_met' ? 'text-rose-800' : 'text-emerald-800' }}">{{ $item['actual'] }}</dd></div>
    </dl>
</div>
