@props(['initial' => 'login'])

<div class="auth-card" data-panel="{{ $initial }}" :data-panel="panel"
     x-data="authCard(@js($initial), @js(route('login')), @js(route('register')))"
     @popstate.window="syncLocation()" @pageshow.window="syncLocation()">
    <x-auth-switch :initial="$initial" />
    <div class="grid">
        @foreach(['login', 'register'] as $mode)
            <section class="auth-panel" data-auth-panel="{{ $mode }}" aria-labelledby="{{ $mode }}-heading"
                     :inert="panel !== @js($mode)" @if($initial !== $mode) inert @endif>
                <h1 id="{{ $mode }}-heading" class="mb-6 text-xl font-semibold">{{ $mode === 'login' ? __('Login') : __('Create an account') }}</h1>
                @if($initial === $mode)
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                @endif
                <form class="flex flex-1 flex-col" method="POST" action="{{ route($mode) }}">
                    @csrf
                    <div class="space-y-4">
                        @foreach($mode === 'login' ? ['email' => __('Email'), 'password' => __('Password')] : ['name' => __('Name'), 'email' => __('Email'), 'password' => __('Password'), 'password_confirmation' => __('Confirm Password')] as $field => $label)
                            @php($fieldId = $mode.'-'.$field)
                            <div>
                                <x-input-label :for="$fieldId" :value="$label" />
                                @if(str_starts_with($field, 'password'))
                                    <x-password-input :id="$fieldId" :name="$field" required :autocomplete="$mode === 'login' ? 'current-password' : 'new-password'" />
                                @else
                                    <input id="{{ $fieldId }}" class="input mt-1" type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}"
                                           value="{{ $initial === $mode ? old($field) : '' }}" required
                                           autocomplete="{{ $field === 'email' ? 'username' : 'name' }}"
                                           @if($initial === $mode && $loop->first) autofocus @endif>
                                @endif
                                @if($initial === $mode) <x-input-error :messages="$errors->get($field)" class="mt-2" /> @endif
                            </div>
                        @endforeach
                        @if($mode === 'login')
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <label for="remember_me" class="inline-flex items-center">
                                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-primary shadow-sm focus:ring-primary" name="remember">
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                </label>
                                @if(Route::has('password.request'))
                                    <a class="rounded text-sm font-medium text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="mt-auto pt-6"><button class="btn btn-primary w-full" type="submit">{{ $mode === 'login' ? __('Login') : __('Register') }}</button></div>
                </form>
            </section>
        @endforeach
    </div>
</div>
