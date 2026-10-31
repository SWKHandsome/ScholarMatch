@props(['initial' => 'login'])

<nav class="auth-switch relative mb-6 grid grid-cols-2 rounded-md border border-outline-variant bg-surface-container-low p-1" aria-label="{{ __('Account access') }}">
    <span class="auth-switch-indicator" aria-hidden="true"></span>
    @foreach(['register' => __('Register'), 'login' => __('Login')] as $destination => $label)
        <a href="{{ route($destination) }}" data-auth-link="{{ $destination }}" @click="switchTo(@js($destination), $event)"
           class="relative z-10 rounded-md px-4 py-2.5 text-center text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
           @if($initial === $destination) aria-current="page" @endif
           :aria-current="panel === @js($destination) ? 'page' : null">{{ $label }}</a>
    @endforeach
</nav>
