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
    @elseif(empty($recommendations))
        <p class="card p-8 text-center">No open scholarships are available for evaluation yet. Check back as scholarships are added.</p>
    @else
        <div x-data="{ tab: 'Eligible' }">
            <div class="grid sm:grid-cols-2 gap-4 mb-6" role="tablist" aria-label="Eligibility decisions">
                @foreach(['Eligible', 'Not Eligible'] as $decision)
                    <button type="button" role="tab" id="{{ $decision === 'Eligible' ? 'eligible-tab' : 'not-eligible-tab' }}"
                        :aria-selected="tab === '{{ $decision }}'" aria-controls="{{ $decision === 'Eligible' ? 'eligible-panel' : 'not-eligible-panel' }}"
                        @click="tab = '{{ $decision }}'" class="card p-5 text-left"
                        :class="tab === '{{ $decision }}' ? 'ring-2 ring-primary' : ''">
                        <span class="font-semibold">{{ $decision }}</span>
                        <span class="block text-2xl font-bold mt-1">{{ collect($recommendations)->where('status', $decision)->count() }}</span>
                    </button>
                @endforeach
            </div>
            @foreach(['Eligible', 'Not Eligible'] as $decision)
                <section role="tabpanel" id="{{ $decision === 'Eligible' ? 'eligible-panel' : 'not-eligible-panel' }}"
                    aria-labelledby="{{ $decision === 'Eligible' ? 'eligible-tab' : 'not-eligible-tab' }}"
                    x-show="tab === '{{ $decision }}'" @if($decision !== 'Eligible') x-cloak @endif>
                    @php($items = collect($recommendations)->where('status', $decision))
                    @if($items->isEmpty())
                        <p class="card p-8 text-center">No {{ strtolower($decision) }} scholarships.</p>
                    @else
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($items as $rec)
                                @include('components.scholarship-card', ['recommendation' => $rec])
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
    @if(($unconfiguredCount ?? 0) > 0)
        <p class="mt-5 text-sm text-on-surface-variant">Some scholarships are awaiting requirement configuration and cannot be evaluated yet.</p>
    @endif
    <div class="card p-5 mt-8 text-sm space-y-2">
        <p><strong>Eligible:</strong> All applicable automatic requirements are met.</p>
        <p><strong>Eligible !:</strong> Automatic requirements are met; expand ! to read special conditions that need checking with the provider.</p>
        <p><strong>Not Eligible:</strong> At least one applicable requirement is not met. Open the explanation to see why.</p>
        <p class="text-on-surface-variant">Open scholarships appear in each group by nearest deadline. The provider makes the final award decision.</p>
    </div>
</div>
@endsection
