<header class="border-b border-outline-variant/60 bg-white">
    <nav
        x-data="{ open: false }"
        @keydown.escape.window="open = false"
        @click.outside="open = false"
        aria-label="Main navigation"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
    >
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="{{ route('home') }}" aria-label="ScholarMatch home" class="shrink-0 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
                <x-brand-logo />
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif class="text-sm font-medium {{ request()->routeIs('home') ? 'text-primary' : 'text-on-surface-variant hover:text-primary' }}">Home</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-on-surface-variant hover:text-primary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                @endauth
            </div>

            <button
                type="button"
                class="md:hidden p-2 rounded-md text-on-surface hover:bg-surface-container-low focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary"
                aria-label="Toggle navigation"
                aria-controls="public-mobile-navigation"
                aria-expanded="false"
                :aria-expanded="open.toString()"
                @click="open = !open"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path x-show="!open" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-cloak x-show="open" stroke-linecap="round" d="M6 6l12 12M6 18L18 6" />
                </svg>
            </button>
        </div>

        <div id="public-mobile-navigation" x-cloak x-show="open" class="md:hidden border-t border-outline-variant/60 pt-3 pb-5 space-y-1">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Home</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary w-full mt-3">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-on-surface-variant hover:bg-surface-container-low">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary w-full mt-3">Get Started</a>
            @endauth
        </div>
    </nav>
</header>
