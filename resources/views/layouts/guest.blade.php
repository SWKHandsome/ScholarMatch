<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <x-site-icon />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($showHomeReturn = request()->routeIs('login', 'register'))
    <body @class(['font-sans text-gray-900 antialiased', 'min-h-screen flex flex-col bg-gray-100' => $showHomeReturn])>
        @if($showHomeReturn)
            <nav class="w-full px-4 pt-4 sm:px-6 sm:pt-6" aria-label="Return navigation">
                <a href="{{ route('home') }}" class="btn btn-outline gap-2 bg-white text-sm">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M5 12h14" />
                    </svg>
                    {{ __('Back to Home') }}
                </a>
            </nav>
        @endif
        <div @class([
            'flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100',
            'flex-1 pb-6' => $showHomeReturn,
            'min-h-screen' => !$showHomeReturn,
        ])>
            <div class="shrink-0">
                <a href="{{ route('home') }}" class="inline-block rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4">
                    <img
                        src="{{ asset('images/scholarmatch-logo-blue.png') }}"
                        alt="ScholarMatch"
                        width="1536"
                        height="1024"
                        class="block h-auto w-48 sm:w-56"
                        decoding="async"
                    >
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
