<footer class="border-t border-outline-variant/60 bg-white">
    <div class="max-w-7xl mx-auto flex flex-col items-center justify-between gap-4 px-4 py-8 sm:flex-row sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="ScholarMatch home" class="rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
            <x-brand-logo compact />
        </a>
        <p class="text-sm text-on-surface-variant">&copy; {{ date('Y') }} ScholarMatch. All rights reserved.</p>
    </div>
</footer>
