<div class="border-b border-outline-variant py-4 last:border-0" data-explanation-status="{{ match ($item['outcome']) { 'not_met' => 'failed', 'not_checked' => 'not-checked', default => 'passed' } }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-semibold {{ $item['outcome'] === 'not_met' ? 'text-rose-800' : 'text-on-surface' }}">{{ $item['label'] }}</p>
        @include('components.requirement-status', ['outcome' => $item['outcome']])
    </div>
    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
        <div><dt class="text-on-surface-variant">Required</dt><dd class="mt-1 break-words font-semibold">{{ $item['required'] }}</dd></div>
        <div><dt class="text-on-surface-variant">{{ $item['key'] === 'deadline' ? 'Today' : 'Your information' }}</dt><dd class="mt-1 break-words font-semibold {{ match ($item['outcome']) { 'not_met' => 'text-rose-800', 'not_checked' => 'text-blue-800', default => 'text-emerald-800' } }}">{{ $item['actual'] }}</dd></div>
    </dl>
    @if($item['outcome'] === 'not_checked')
        <p class="mt-3 text-sm text-blue-800">{{ $item['message'] }}</p>
    @endif
    @if(!empty($item['gap']))
        <p class="mt-3 text-sm font-medium text-rose-800">{{ $item['gap'] }}</p>
    @endif
</div>
