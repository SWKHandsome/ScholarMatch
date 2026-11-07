<nav class="card mb-6 p-4" aria-label="Student setup steps">
    <p class="mb-3 text-sm text-on-surface-variant">Complete your information to get started. Your saved information is kept when you return.</p>
    <ol class="grid grid-cols-2 gap-3 text-sm">
        @foreach([1 => ['Student Profile', 'student.profile.edit'], 2 => ['Academic Result', 'student.academic-result.edit']] as $number => [$label, $route])
            <li>
                <a href="{{ route($route) }}" class="flex h-full items-center gap-2 rounded-md border p-3 font-semibold {{ $currentStep === $number ? 'border-primary bg-primary/5 text-primary' : 'border-outline-variant text-on-surface-variant' }}" @if($currentStep === $number) aria-current="step" @endif>
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $currentStep === $number ? 'bg-primary text-white' : 'bg-surface-container' }}" aria-hidden="true">{{ $number }}</span>
                    <span>{{ $label }}</span>
                </a>
            </li>
        @endforeach
    </ol>
</nav>
