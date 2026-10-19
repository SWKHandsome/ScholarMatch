@extends('layouts.admin', ['pageTitle' => 'Scholarship Rules'])
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center gap-4">
        <div><h1 class="text-2xl font-bold">Scholarship Rules</h1><p class="mt-1 text-on-surface-variant">{{ $scholarship->name }}</p></div>
        @if($rule)
            <a class="btn btn-primary" href="{{ route('admin.scholarships.rules.edit', [$scholarship, $rule]) }}">Edit Rules</a>
        @else
            <a class="btn btn-primary" href="{{ route('admin.scholarships.rules.create', $scholarship) }}">Add Rules</a>
        @endif
    </div>
    @if(session('success'))<p class="card p-4 text-success">{{ session('success') }}</p>@endif
    @if($rule)
        <div class="card p-6">
            <p class="mb-5 text-on-surface-variant">Every applicable general requirement must be met, plus any one complete qualification route. No points are assigned.</p>
            <dl class="grid sm:grid-cols-2 gap-5">
                @foreach([
                    'required_nationality' => 'Nationality', 'required_study_level' => 'Funded Study Level',
                    'required_income_category' => 'Income Category',
                    'max_household_income' => 'Maximum Household Income',
                    'required_institution_type' => 'Institution Type',
                ] as $key => $label)
                    <div><dt class="text-sm text-on-surface-variant">{{ $label }}</dt><dd class="font-medium">{{ $rule->$key ?? 'Not specified' }}</dd></div>
                @endforeach
                <div><dt class="text-sm text-on-surface-variant">Supported Fields of Study</dt><dd>{{ implode(', ', $rule->supportedFieldsOfStudy()) ?: 'Any field' }}</dd></div>
            </dl>
            <h2 class="mt-6 mb-3 font-semibold">Accepted Qualification Routes (pass any one)</h2>
            <div class="space-y-3">
                @forelse($rule->academicRoutes() as $route)
                    <div class="rounded-lg border border-outline-variant p-4">
                        <h3 class="font-medium">{{ $route['education_level'] }} — {{ ['studying' => 'Currently Studying', 'completed' => 'Completed', 'any' => 'Either status'][$route['study_status']] }}</h3>
                        <dl class="mt-2 grid sm:grid-cols-2 gap-2 text-sm">
                            @foreach(['min_spm_as' => 'Minimum SPM As', 'min_spm_credits' => 'Minimum SPM Credits', 'min_cgpa' => 'Minimum CGPA', 'min_year_of_study' => 'Minimum Year', 'max_year_of_study' => 'Maximum Year'] as $key => $label)
                                @if(($route[$key] ?? null) !== null)
                                    <div><dt class="text-on-surface-variant">{{ $label }}</dt><dd>{{ $route[$key] }}</dd></div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @empty
                    <p class="text-sm text-on-surface-variant">Any latest qualification; no result, status or year restriction.</p>
                @endforelse
            </div>
        </div>
    @else
        <p class="card p-6">No rules configured. Add and review requirements before this scholarship can be recommended.</p>
    @endif
    <div class="card p-6"><h2 class="font-semibold">Special Requirements</h2><p class="mt-2 whitespace-pre-line">{{ $scholarship->special_requirements ?: 'None specified.' }}</p><p class="mt-3 text-sm text-on-surface-variant">Edit these on the scholarship form. They add ! to otherwise Eligible results.</p></div>
    <a class="btn btn-outline" href="{{ route('admin.scholarships.index') }}">Back to Scholarships</a>
</div>
@endsection
