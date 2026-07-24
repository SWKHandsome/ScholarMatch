@extends('layouts.student', ['pageTitle' => 'Dashboard'])

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-on-surface">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-on-surface-variant mt-1">Here's your scholarship matching overview.</p>
    </div>

    <!-- Profile Completion Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Profile Status -->
        <div class="card p-5 {{ $profileComplete ? 'border-l-4 border-success' : 'border-l-4 border-warning' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Profile</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $profileComplete ? 'Complete' : 'Incomplete' }}</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    {{ $profileComplete ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $profileComplete ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }}"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('student.profile.edit') }}" class="mt-3 inline-block text-sm text-primary hover:underline">
                {{ $profileComplete ? 'Update Profile' : 'Complete Profile' }}
            </a>
        </div>

        <!-- Academic Result Status -->
        <div class="card p-5 {{ $academicComplete ? 'border-l-4 border-success' : 'border-l-4 border-warning' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Academic Result</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $academicComplete ? 'Complete' : 'Incomplete' }}</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    {{ $academicComplete ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $academicComplete ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }}"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('student.academic-result.edit') }}" class="mt-3 inline-block text-sm text-primary hover:underline">
                {{ $academicComplete ? 'Update Result' : 'Add Academic Result' }}
            </a>
        </div>

        <!-- Saved Scholarships -->
        <div class="card p-5 border-l-4 border-info">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Saved Scholarships</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $savedCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-info/10 text-info flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                </div>
            </div>
            <a href="{{ route('student.saved-scholarships.index') }}" class="mt-3 inline-block text-sm text-primary hover:underline">View Saved</a>
        </div>

        <!-- Recommendations Status -->
        <div class="card p-5 border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-on-surface-variant">Recommendations</p>
                    @if($profileComplete && $academicComplete)
                        <p class="text-2xl font-bold text-on-surface mt-1">{{ count($recentRecommendations) }} found</p>
                    @else
                        <p class="text-2xl font-bold text-on-surface mt-1">—</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
            @if($profileComplete && $academicComplete)
                <a href="{{ route('student.recommendations') }}" class="mt-3 inline-block text-sm text-primary hover:underline">View All</a>
            @else
                <p class="mt-3 text-sm text-on-surface-variant">Complete profile & academic result to see matches</p>
            @endif
        </div>
    </div>

    <!-- Recent Recommendations -->
    @if($profileComplete && $academicComplete && !empty($recentRecommendations))
    <div class="card">
        <div class="p-5 border-b border-outline-variant flex items-center justify-between">
            <h2 class="text-lg font-semibold text-on-surface">Top Recommendations</h2>
            <a href="{{ route('student.recommendations') }}" class="text-sm text-primary hover:underline">View All</a>
        </div>
        <div class="divide-y divide-outline-variant/50">
            @foreach($recentRecommendations as $recommendation)
                <div class="p-5">
                    @include('components.scholarship-card', ['recommendation' => $recommendation, 'showSaveButton' => true, 'showDetailLink' => true])
                </div>
            @endforeach
        </div>
    </div>
    @elseif($profileComplete && $academicComplete)
    <div class="card p-10 text-center">
        <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        <h3 class="text-lg font-medium text-on-surface mb-1">No Scholarships Matched</h3>
        <p class="text-on-surface-variant">No active scholarships match your current criteria. Check back later as new scholarships are added.</p>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8 grid sm:grid-cols-2 gap-4">
        <a href="{{ route('student.profile.edit') }}" class="card p-6 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h3 class="font-semibold text-on-surface mb-1">Update Profile</h3>
            <p class="text-sm text-on-surface-variant">Keep your socioeconomic info current for accurate matching</p>
        </a>

        <a href="{{ route('student.academic-result.edit') }}" class="card p-6 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h3 class="font-semibold text-on-surface mb-1">Update Academic Result</h3>
            <p class="text-sm text-on-surface-variant">Refresh your grades to see updated scholarship matches</p>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleExplanation(btn) {
    const panel = btn.nextElementSibling;
    const icon = btn.querySelector('svg');
    panel.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endpush