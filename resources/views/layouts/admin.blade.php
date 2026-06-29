<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ScholarMatch') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:flex-shrink-0 bg-white border-r border-outline-variant">
            <div class="flex h-16 items-center justify-center border-b border-outline-variant">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-primary">
                    ScholarMatch Admin
                </a>
            </div>
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                        {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.scholarships.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                        {{ request()->routeIs('admin.scholarships.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Scholarships
                </a>
                <a href="{{ route('admin.income-categories.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                        {{ request()->routeIs('admin.income-categories.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0l1-1m-1 1l-1-1"></path></svg>
                    Income Categories
                </a>
                <a href="{{ route('admin.recommendation-logs.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                        {{ request()->routeIs('admin.recommendation-logs.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Recommendation Logs
                </a>
            </nav>
            <div class="p-4 border-t border-outline-variant">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile sidebar backdrop -->
        <div class="lg:hidden fixed inset-0 z-40 bg-black/50" id="sidebar-backdrop" style="display: none;"></div>

        <!-- Mobile sidebar -->
        <aside class="lg:hidden fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-outline-variant transform -translate-x-full transition-transform duration-300" id="mobile-sidebar">
            <div class="flex h-16 items-center justify-between border-b border-outline-variant px-4">
                <span class="text-xl font-bold text-primary">ScholarMatch Admin</span>
                <button onclick="closeSidebar()" class="p-2 rounded-md hover:bg-surface-container-low" aria-label="Close sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface">Dashboard</a>
                <a href="{{ route('admin.scholarships.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface">Scholarships</a>
                <a href="{{ route('admin.income-categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface">Income Categories</a>
                <a href="{{ route('admin.recommendation-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface">Recommendation Logs</a>
                <hr class="my-2 border-outline-variant">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface">Logout</button>
                </form>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-outline-variant bg-white/80 backdrop-blur-sm px-4 lg:px-6">
                <div class="flex items-center gap-4">
                    <button onclick="openSidebar()" class="lg:hidden p-2 rounded-md hover:bg-surface-container-low" aria-label="Open sidebar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-on-surface">{{ $pageTitle ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:block text-sm text-on-surface-variant">{{ auth()->user()->name }}</span>
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium text-sm">
                        {{ strtoupper(auth()->user()->name[0]) }}
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <div class="p-4 lg:p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-lg bg-success/10 text-success border border-success/20 flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="p-1 hover:bg-success/20 rounded">×</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 rounded-lg bg-error/10 text-error border border-error/20 flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="p-1 hover:bg-error/20 rounded">×</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function openSidebar() {
            document.getElementById('mobile-sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebar-backdrop').style.display = 'block';
        }
        function closeSidebar() {
            document.getElementById('mobile-sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-backdrop').style.display = 'none';
        }
        document.getElementById('sidebar-backdrop')?.addEventListener('click', closeSidebar);
    </script>
</body>
</html>