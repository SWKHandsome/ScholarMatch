@extends('layouts.student', ['pageTitle' => 'Recommendations'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-on-surface">Scholarship Recommendations</h1>
        <p class="text-on-surface-variant mt-1">Your personalized matches based on academic and socioeconomic criteria.</p>
    </div>

    @if($error)
        <div class="card p-6 bg-warning/5 border-warning/20 mb-8">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-warning flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <p class="text-warning">{{ $error }}</p>
            </div>
        </div>
    @endif

    @if(!empty($recommendations))
        <!-- Summary Stats -->
        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <div class="card p-4 border-l-4 border-success">
                <p class="text-sm text-on-surface-variant">Eligible</p>
                <p class="text-2xl font-bold text-success mt-1">{{ collect($recommendations)->where('status', 'Eligible')->count() }}</p>
            </div>
            <div class="card p-4 border-l-4 border-warning">
                <p class="text-sm text-on-surface-variant">Partially Eligible</p>
                <p class="text-2xl font-bold text-warning mt-1">{{ collect($recommendations)->where('status', 'Partially Eligible')->count() }}</p>
            </div>
            <div class="card p-4 border-l-4 border-error">
                <p class="text-sm text-on-surface-variant">Not Suitable</p>
                <p class="text-2xl font-bold text-error mt-1">{{ collect($recommendations)->where('status', 'Not Suitable')->count() }}</p>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="card overflow-hidden">
            <div class="border-b border-outline-variant">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Recommendation status tabs">
                    <button class="tab-btn px-4 py-3 text-sm font-medium text-primary border-b-2 border-primary whitespace-nowrap" data-tab="eligible">
                        Eligible <span class="ml-1 px-2 py-0.5 text-xs bg-primary/10 rounded-full">{{ collect($recommendations)->where('status', 'Eligible')->count() }}</span>
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium text-on-surface-variant border-b-2 border-transparent hover:text-on-surface hover:border-outline-variant whitespace-nowrap" data-tab="partial">
                        Partially Eligible <span class="ml-1 px-2 py-0.5 text-xs bg-warning/10 rounded-full">{{ collect($recommendations)->where('status', 'Partially Eligible')->count() }}</span>
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium text-on-surface-variant border-b-2 border-transparent hover:text-on-surface hover:border-outline-variant whitespace-nowrap" data-tab="not-suitable">
                        Not Suitable <span class="ml-1 px-2 py-0.5 text-xs bg-error/10 rounded-full">{{ collect($recommendations)->where('status', 'Not Suitable')->count() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Panels -->
            <div class="p-4">
                <!-- Eligible -->
                <div id="tab-eligible" class="tab-panel">
                    @php $eligible = collect($recommendations)->where('status', 'Eligible')->values()->all() @endphp
                    @if(!empty($eligible))
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($eligible as $rec)
                                @include('components.scholarship-card', ['recommendation' => $rec])
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-on-surface-variant py-8">No eligible scholarships</p>
                    @endif
                </div>

                <!-- Partially Eligible -->
                <div id="tab-partial" class="tab-panel hidden">
                    @php $partial = collect($recommendations)->where('status', 'Partially Eligible')->values()->all() @endphp
                    @if(!empty($partial))
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($partial as $rec)
                                @include('components.scholarship-card', ['recommendation' => $rec])
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-on-surface-variant py-8">No partially eligible scholarships</p>
                    @endif
                </div>

                <!-- Not Suitable -->
                <div id="tab-not-suitable" class="tab-panel hidden">
                    @php $notSuitable = collect($recommendations)->where('status', 'Not Suitable')->values()->all() @endphp
                    @if(!empty($notSuitable))
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($notSuitable as $rec)
                                @include('components.scholarship-card', ['recommendation' => $rec])
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-on-surface-variant py-8">All scholarships are at least partially suitable!</p>
                    @endif
                </div>
            </div>
        </div>

    @else
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Recommendations Yet</h3>
            <p class="text-on-surface-variant mb-6">Complete your profile and academic result to see personalized scholarship matches.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('student.profile.edit') }}" class="btn btn-primary">Complete Profile</a>
                <a href="{{ route('student.academic-result.edit') }}" class="btn btn-outline">Add Academic Result</a>
            </div>
        </div>
    @endif

    <!-- Legend -->
    <div class="mt-8 card p-4">
        <h4 class="font-medium text-on-surface mb-3">Understanding Your Results</h4>
        <div class="grid sm:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center gap-2 p-3 bg-success/5 rounded-lg">
                <span class="badge badge-eligible">Eligible</span>
                <span class="text-on-surface-variant">Passes all hard rules, score 80–100</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-warning/5 rounded-lg">
                <span class="badge badge-partial">Partially Eligible</span>
                <span class="text-on-surface-variant">Passes hard rules, score 50–79</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-error/5 rounded-lg">
                <span class="badge badge-not-suitable">Not Suitable</span>
                <span class="text-on-surface-variant">Fails hard rule or score 0–49</span>
            </div>
        </div>
        <p class="mt-3 text-xs text-on-surface-variant">
            <strong>Hard rules</strict> are strict pass/fail requirements (nationality, income category, study level, deadline).
            <strong>Soft rules</strong> contribute to your score for ranking. Click "View Explanation" on any card for details.
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;

            // Update buttons
            tabBtns.forEach(b => {
                b.classList.remove('text-primary', 'border-primary');
                b.classList.add('text-on-surface-variant', 'border-transparent');
            });
            btn.classList.remove('text-on-surface-variant', 'border-transparent');
            btn.classList.add('text-primary', 'border-primary');

            // Update panels
            tabPanels.forEach(panel => {
                panel.classList.add('hidden');
            });
            document.getElementById('tab-' + tab).classList.remove('hidden');
        });
    });
});
</script>
@endsection