@props(['id', 'name', 'autocomplete' => 'current-password'])

<div class="relative mt-1" x-data="{ visible: false }" x-on:auth-panel-changed.window="visible = false">
    <input id="{{ $id }}" name="{{ $name }}" type="password" :type="visible ? 'text' : 'password'"
           autocomplete="{{ $autocomplete }}" {{ $attributes->merge(['class' => 'input pr-12']) }}>
    <button type="button" x-cloak @click="visible = !visible"
            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-md text-on-surface-variant transition-colors hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset"
            aria-controls="{{ $id }}" aria-label="{{ __('Show password') }}" aria-pressed="false"
            :aria-label="visible ? @js(__('Hide password')) : @js(__('Show password'))"
            :aria-pressed="visible">
        <svg x-show="visible" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg x-show="!visible" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 5.1A10 10 0 0 1 12 5c6.25 0 9.75 7 9.75 7a18.2 18.2 0 0 1-3.2 4.1M6.3 6.3A18.6 18.6 0 0 0 2.25 12S5.75 19 12 19a10.8 10.8 0 0 0 5.7-1.7M9.9 9.9a3 3 0 0 0 4.2 4.2" />
        </svg>
    </button>
</div>
