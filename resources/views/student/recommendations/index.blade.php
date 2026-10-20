@extends('layouts.student', ['pageTitle' => 'Recommendations'])
@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold">Scholarship Recommendations</h1>
    <p class="mt-2 mb-6 text-on-surface-variant">Eligibility based on your official results and the scholarship's applicable requirements.</p>
    @if(session('warning'))<p class="card p-4 mb-5 text-warning">{{ session('warning') }}</p>@endif
    @if($error)
        <div class="card p-6 mb-6" role="alert">
            <p class="mb-4">{{ $error }}</p>
            <a class="btn btn-outline" href="{{ route('student.profile.edit') }}">Complete Profile</a>
            <a class="btn btn-primary" href="{{ route('student.academic-result.edit') }}">Update Academic Information</a>
        </div>
    @elseif($totalRecommendations === 0)
        <p class="card p-8 text-center">No open scholarships are available for evaluation yet. Check back as scholarships are added.</p>
    @else
        <div class="grid items-start gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            <aside class="card p-5" aria-labelledby="recommendation-filters-heading">
                <h2 class="text-lg font-semibold" id="recommendation-filters-heading">Filter Scholarships</h2>
                <form method="GET" action="{{ route('student.recommendations') }}" class="mt-4 space-y-4">
                    <input type="hidden" name="decision" value="{{ $decision }}">
                    <div>
                        <label class="label" for="search">Search</label>
                        <input class="input" type="search" id="search" name="search" value="{{ $filters['search'] }}" maxlength="255" placeholder="Name or provider">
                    </div>
                    <div>
                        <label class="label" for="award_type">Award Type</label>
                        <select class="input" id="award_type" name="award_type">
                            <option value="">All award types</option>
                            @foreach($awardTypes as $type)
                                <option value="{{ $type }}" @selected($filters['award_type'] === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="study_level">Study Level Funded</label>
                        <select class="input" id="study_level" name="study_level">
                            <option value="">All study levels</option>
                            @foreach($educationLevels as $level)
                                <option value="{{ $level }}" @selected($filters['study_level'] === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="field_of_study">Field of Study</label>
                        <select class="input" id="field_of_study" name="field_of_study">
                            <option value="">All fields</option>
                            @foreach(config('scholarship.fields_of_study') as $field)
                                <option value="{{ $field }}" @selected($filters['field_of_study'] === $field)>{{ $field }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="institution_type">Institution Type</label>
                        <select class="input" id="institution_type" name="institution_type">
                            <option value="">All institution types</option>
                            @foreach(config('scholarship.institution_types') as $type)
                                <option value="{{ $type }}" @selected($filters['institution_type'] === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-on-surface-variant">Includes scholarships open to any level, field or institution. Filters do not change your eligibility.</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="btn btn-primary text-sm" type="submit">Apply Filters</button>
                        <a class="btn btn-outline text-sm" href="{{ route('student.recommendations', ['decision' => $decision]) }}">Reset</a>
                    </div>
                </form>
            </aside>
            <div class="min-w-0 scroll-mt-24" id="recommendation-results">
                <nav class="grid grid-cols-2 gap-3 mb-5" aria-label="Eligibility decisions">
                    @foreach(['Eligible', 'Not Eligible'] as $status)
                        <a href="{{ route('student.recommendations', array_merge(array_filter($filters, fn ($value) => $value !== ''), ['decision' => $status])) }}"
                            @if($decision === $status) aria-current="page" @endif
                            @class(['card p-4', 'ring-2 ring-primary' => $decision === $status])>
                            <span class="font-semibold">{{ $status }}</span>
                            <span class="block text-2xl font-bold mt-1">{{ $decisionCounts->get($status, 0) }}</span>
                        </a>
                    @endforeach
                </nav>
                @if($recommendations->isEmpty())
                    <div class="card p-8 text-center">
                        <p>No {{ strtolower($decision) }} scholarships match these filters.</p>
                        <p class="mt-2 text-sm text-on-surface-variant">Try adjusting the filters or checking the other eligibility group.</p>
                    </div>
                @else
                    <p class="mb-4 text-sm text-on-surface-variant" role="status">Showing {{ $recommendations->firstItem() }}–{{ $recommendations->lastItem() }} of {{ $recommendations->total() }} {{ strtolower($decision) }} scholarships · Page {{ $recommendations->currentPage() }} of {{ $recommendations->lastPage() }}</p>
                    <div class="space-y-5">
                        @foreach($recommendations as $rec)
                            @include('components.scholarship-card', ['recommendation' => $rec])
                        @endforeach
                    </div>
                    @if($recommendations->hasPages())
                        <div class="mt-6">{{ $recommendations->onEachSide(1)->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    @endif
    @if(($unconfiguredCount ?? 0) > 0)
        <p class="mt-5 text-sm text-on-surface-variant">Some scholarships are awaiting requirement configuration and cannot be evaluated yet.</p>
    @endif
    <div class="card p-5 mt-8 text-sm space-y-2">
        <p><strong>Eligible:</strong> All applicable automatic requirements are met.</p>
        <p><strong>Eligible !:</strong> Automatic requirements are met; expand ! to read special conditions that need checking with the provider.</p>
        <p><strong>Not Eligible:</strong> At least one applicable requirement is not met. Open the explanation to see why.</p>
        <p class="text-on-surface-variant">Scholarships appear by recorded deadline, followed by those whose dates need confirmation. Check current availability with the provider, who makes the final award decision.</p>
    </div>
</div>
@endsection
