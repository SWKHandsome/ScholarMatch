@php
    $explanation = \App\Support\StudentRequirementExplanation::prepare($recommendation['requirement_results']);
    $eligible = $recommendation['status'] === 'Eligible';
    $providerText = trim((string) $scholarship->special_requirements);
    $confirmDates = $scholarship->deadline === null && !in_array($scholarship->deadline_type, ['rolling', 'automatic'], true);
@endphp
<div class="space-y-6">
    <div class="border-b border-outline-variant pb-5">
        <p class="text-lg font-semibold {{ $eligible ? 'text-emerald-800' : 'text-rose-800' }}">{{ $eligible ? '✓ Eligible' : '✕ Not Eligible' }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $eligible ? 'You meet all checked automatic requirements.' : 'Review the requirements below that are not met.' }}</p>
        @if($explanation['selected'])
            <p class="mt-3 text-sm"><span class="text-on-surface-variant">{{ $explanation['selected']['passed'] ? 'Your accepted qualification route:' : 'Qualification route to review:' }}</span> <strong>{{ $explanation['selected']['label'] }}</strong></p>
        @endif
    </div>

    @if($explanation['failed'])
        <section class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2">
            <h3 class="pt-2 font-semibold text-rose-800">Not Eligible - Failed Requirements</h3>
            <p class="mt-2 text-sm text-rose-800">Each gap below is a separate condition. Meeting one condition alone does not guarantee eligibility; all conditions in one accepted route and the checked general requirements must pass.</p>
            @if(collect($explanation['failed'])->contains(fn ($check) => str_starts_with($check['key'], 'min_grade_count_')))
                <p class="mt-2 text-sm text-rose-800">Grade-count shortfalls can overlap. A stronger subject can satisfy more than one threshold, so do not add these shortfalls together.</p>
            @endif
            @foreach($explanation['failed'] as $item)
                @include('components.student-requirement-row')
            @endforeach
        </section>
    @endif

    @if($explanation['unchecked'])
        <section class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2">
            <h3 class="pt-2 font-semibold text-blue-800">Programme requirements not checked</h3>
            <p class="mt-2 text-sm text-blue-800">You have not decided these programme choices. These restrictions are not included in your current eligibility decision.</p>
            @foreach($explanation['unchecked'] as $item)
                @include('components.student-requirement-row')
            @endforeach
        </section>
    @endif

    @foreach(['academic' => 'Academic requirements', 'general' => 'Other requirements'] as $group => $heading)
        @if($explanation[$group])
            <section>
                <h3 class="font-semibold">{{ $heading }}</h3>
                @foreach($explanation[$group] as $item)
                    @include('components.student-requirement-row')
                @endforeach
            </section>
        @endif
    @endforeach

    @if($explanation['alternatives'])
        <details class="rounded-lg border border-outline-variant p-4">
            <summary class="cursor-pointer font-medium text-primary">View other accepted qualification routes ({{ count($explanation['alternatives']) }})</summary>
            <p class="mt-3 text-sm text-on-surface-variant">Pass any one complete route. Unmet conditions in an alternative route do not prevent eligibility when another route is satisfied.</p>
            <div class="mt-4 space-y-4">
                @foreach($explanation['alternatives'] as $alternative)
                    <details class="rounded-md border border-outline-variant p-3">
                        <summary class="cursor-pointer font-medium">{{ $alternative['label'] }}</summary>
                        <p class="mt-3 text-sm text-on-surface-variant">{{ $alternative['passed'] ? 'You meet this alternative route too.' : 'You do not meet every condition in this alternative route.' }}</p>
                        @foreach($alternative['checks'] as $alternativeCheck)
                            @if($alternativeCheck['outcome'] !== 'not_applicable')
                                @include('components.student-requirement-row', ['item' => \App\Support\StudentRequirementExplanation::row($alternativeCheck)])
                            @endif
                        @endforeach
                    </details>
                @endforeach
            </div>
        </details>
    @endif

    @if($providerText !== '' || $confirmDates)
        <section class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-900">
            <h3 class="font-semibold">! Check with the provider</h3>
            @if($confirmDates)
                <p class="mt-3 font-semibold">Confirm the application deadline</p>
                <p class="mt-1 text-sm">{{ $scholarship->deadlineLabel() }}. Confirm the current application dates with the scholarship provider.</p>
                @if($scholarship->application_notes)<p class="mt-2 whitespace-pre-line text-sm">{{ $scholarship->application_notes }}</p>@endif
            @endif
            @if($providerText !== '')
                <p class="mt-3 font-semibold">Special conditions</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ $providerText }}</p>
                <p class="mt-2 text-sm">Confirm these conditions with the scholarship provider.</p>
            @endif
        </section>
    @endif
    @if($scholarship->deadline === null && !$confirmDates)
        <p class="text-sm text-on-surface-variant"><strong>Application timing:</strong> {{ $scholarship->deadlineLabel() }}@if($scholarship->application_notes) — {{ $scholarship->application_notes }}@endif</p>
    @endif

    @if($explanation['inactive'])
        <details class="rounded-lg border border-outline-variant p-4">
            <summary class="cursor-pointer font-medium text-on-surface-variant">View criteria with no restrictions ({{ count($explanation['inactive']) }})</summary>
            <p class="mt-3 text-sm text-on-surface-variant">These criteria do not affect your eligibility for this scholarship.</p>
            <dl class="mt-3 divide-y divide-outline-variant text-sm">
                @foreach($explanation['inactive'] as $unrestricted)
                    <div class="py-3"><dt class="font-medium">{{ $unrestricted['label'] }} · No restriction</dt><dd class="mt-1 text-on-surface-variant">Your information: {{ $unrestricted['actual'] }}</dd></div>
                @endforeach
            </dl>
        </details>
    @endif
</div>
