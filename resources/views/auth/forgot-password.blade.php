<x-guest-layout>
    <x-back-to-login />

    <h1 class="mb-2 text-xl font-semibold">{{ __('Forgot Password') }}</h1>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter your email address and we will send you a link to reset your password.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <input id="email" class="input mt-1" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button class="btn btn-primary mt-6 w-full" type="submit">{{ __('Send Reset Link') }}</button>
    </form>
</x-guest-layout>
