@props(['compact' => false])

<span {{ $attributes->class(['inline-flex items-center gap-2 whitespace-nowrap text-primary']) }}>
    <img
        src="{{ asset('images/scholarmatch-mark.svg') }}"
        alt=""
        aria-hidden="true"
        width="160"
        height="104"
        class="{{ $compact ? 'h-8' : 'h-10' }} w-auto shrink-0"
    >
    <span class="{{ $compact ? 'text-lg' : 'text-xl' }} font-bold tracking-tight">ScholarMatch</span>
</span>
