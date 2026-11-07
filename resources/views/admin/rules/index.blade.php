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
                        <h3 class="font-medium">{{ \App\Support\AcademicQualifications::label($route['education_level']) }} — {{ ['studying' => 'Currently Studying', 'completed' => 'Completed', 'any' => 'Either status'][$route['study_status']] }}</h3>
                        <dl class="mt-2 grid sm:grid-cols-2 gap-2 text-sm">
                            @foreach(['min_spm_as' => 'Minimum SPM As', 'min_spm_credits' => 'Minimum SPM Credits', 'min_cgpa' => 'Minimum CGPA', 'min_year_of_study' => 'Minimum Year', 'max_year_of_study' => 'Maximum Year'] as $key => $label)
                                @if(($route[$key] ?? null) !== null)
                                    <div><dt class="text-on-surface-variant">{{ $label }}</dt><dd>{{ $route[$key] }}</dd></div>
                                @endif
                            @endforeach
                            @if(!empty($route['grading_scale']))
                                <div><dt class="text-on-surface-variant">Grading Scale</dt><dd>{{ \App\Support\AcademicQualifications::scales()[$route['grading_scale']]['label'] }}</dd></div>
                            @endif
                            @foreach($route['grade_requirements'] ?? [] as $condition)
                                <div><dt class="text-on-surface-variant">Subjects at {{ $condition['grade'] }} or above</dt><dd>At least {{ $condition['min_subjects'] }}</dd></div>
                            @endforeach
                            @if(!empty($route['min_stam_rank']))
                                <div><dt class="text-on-surface-variant">Minimum STAM Pangkat</dt><dd>{{ $route['min_stam_rank'] }}</dd></div>
                            @endif
                            @if(!empty($route['uec_average']))
                                <div><dt class="text-on-surface-variant">UEC Best-subject Average</dt><dd>Best {{ $route['uec_average']['subjects'] }} subjects at {{ $route['uec_average']['minimum_grade'] }} or above; average ≤ {{ $route['uec_average']['max_average'] }}</dd></div>
                            @endif
                            @if(!empty($route['stpm_average']))
                                <div><dt class="text-on-surface-variant">STPM Best-subject Average</dt><dd>Best {{ $route['stpm_average']['subjects'] }} subjects at {{ $route['stpm_average']['minimum_grade'] }} or above; average ≥ {{ $route['stpm_average']['min_average'] }} (separate from overall CGPA)</dd></div>
                            @endif
                            @if(!empty($route['uec_total']))
                                <div><dt class="text-on-surface-variant">UEC Best-subject Total</dt><dd>Best {{ $route['uec_total']['subjects'] }} subjects at {{ $route['uec_total']['minimum_grade'] }} or above; total ≤ {{ $route['uec_total']['max_total'] }} points</dd></div>
                            @endif
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
