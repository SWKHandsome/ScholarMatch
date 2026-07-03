@extends('layouts.admin', ['pageTitle' => 'Dashboard'])

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-on-surface">Admin Dashboard</h1>
        <p class="text-on-surface-variant mt-1">Overview of scholarship data and system activity</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-5 border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Total Scholarships</p>
                    <p class="text-3xl font-bold text-on-surface mt-1">{{ $totalScholarships ?? \App\Models\Scholarship::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5 border-l-4 border-success">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Active Scholarships</p>
                    <p class="text-3xl font-bold text-on-surface mt-1">{{ $activeScholarships ?? \App\Models\Scholarship::where('is_active', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5 border-l-4 border-info">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Total Students</p>
                    <p class="text-3xl font-bold text-on-surface mt-1">{{ $totalStudents ?? \App\Models\User::where('role', 'student')->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-info/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5 border-l-4 border-warning">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Total Recommendations</p>
                    <p class="text-3xl font-bold text-on-surface mt-1">{{ $totalLogs ?? \App\Models\RecommendationLog::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-warning/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-on-surface mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('admin.scholarships.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <p class="font-medium text-on-surface">Add Scholarship</p>
                        <p class="text-sm text-on-surface-variant">Create new scholarship with rules</p>
                    </div>
                </a>
                <a href="{{ route('admin.income-categories.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0l1-1m-1 1l-1-1"></path></svg>
                    </div>
                    <div>
                        <p class="font-medium text-on-surface">Manage Income Categories</p>
                        <p class="text-sm text-on-surface-variant">Update B40/M40/T20 thresholds</p>
                    </div>
                </a>
                <a href="{{ route('admin.recommendation-logs.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <p class="font-medium text-on-surface">View Recommendation Logs</p>
                        <p class="text-sm text-on-surface-variant">Monitor student matches and scores</p>
                    </div>
                </a>
                <a href="{{ route('admin.scholarships.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <p class="font-medium text-on-surface">Manage Scholarships</p>
                        <p class="text-sm text-on-surface-variant">Edit, deactivate, or delete scholarships</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Recommendation Logs -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-on-surface">Recent Recommendation Activity</h2>
                <a href="{{ route('admin.recommendation-logs.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            @php
                $recentLogs = \App\Models\RecommendationLog::with(['user', 'scholarship'])
                    ->latest()
                    ->take(5)
                    ->get();
            @endphp
            @if($recentLogs->isNotEmpty())
                <div class="space-y-3">
                    @foreach($recentLogs as $log)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium text-sm">
                                    {{ strtoupper($log->user->name[0]) }}
                                </div>
                                <div>
                                    <p class="font-medium text-on-surface text-sm">{{ $log->user->name }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $log->scholarship->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge
                                    {{ $log->status === 'Eligible' ? 'badge-eligible' : ($log->status === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable') }}">
                                    {{ $log->status }}
                                </span>
                                <span class="text-sm font-medium text-on-surface">{{ $log->score }}</span>
                                <span class="text-xs text-on-surface-variant">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-on-surface-variant py-8">No recommendation logs yet</p>
            @endif
        </div>
    </div>

    <!-- System Info -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-on-surface mb-4">System Information</h2>
        <dl class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-on-surface-variant">Laravel Version</dt>
                <dd class="font-medium text-on-surface">{{ app()->version() }}</dd>
            </div>
            <div>
                <dt class="text-on-surface-variant">PHP Version</dt>
                <dd class="font-medium text-on-surface">{{ PHP_VERSION }}</dd>
            </div>
            <div>
                <dt class="text-on-surface-variant">Database</dt>
                <dd class="font-medium text-on-surface">{{ config('database.default') }} ({{ DB::connection()->getDatabaseName() }})</dd>
            </div>
            <div>
                <dt class="text-on-surface-variant">Environment</dt>
                <dd class="font-medium text-on-surface">{{ config('app.env') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection