@extends('layouts.admin', ['pageTitle' => 'Scholarship Rules'])

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Scholarship Rules</h1>
            <p class="text-on-surface-variant mt-1">{{ $scholarship->name }}</p>
        </div>
        <div class="flex gap-2">
            @if($rule)
                <a href="{{ route('admin.scholarships.rules.edit', [$scholarship, $rule]) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Rules
                </a>
            @else
                <a href="{{ route('admin.scholarships.rules.create', $scholarship) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Rules
                </a>
            @endif
            <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline">Back to Scholarships</a>
        </div>
    </div>

    @if(!$rule)
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Rules Configured</h3>
            <p class="text-on-surface-variant mb-6">This scholarship doesn't have eligibility rules yet. Add rules to enable matching.</p>
            <a href="{{ route('admin.scholarships.rules.create', $scholarship) }}" class="btn btn-primary">Add Rules</a>
        </div>
    @else
        <div class="card overflow-hidden">
            <!-- Identity Rules -->
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Identity & Nationality</h2>
            </div>
            <div class="divide-y divide-outline-variant/50">
                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Required Nationality</div>
                    <div class="sm:col-span-2">
                        @if($rule->required_nationality)
                            <span class="badge badge-info">{{ $rule->required_nationality }}</span>
                        @else
                            <span class="text-on-surface-variant">Not specified (any nationality)</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic Rules -->
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Academic Requirements</h2>
            </div>
            <div class="divide-y divide-outline-variant/50">
                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Required Study Level</div>
                    <div class="sm:col-span-2">
                        @if($rule->required_study_level)
                            <span class="badge badge-info">{{ $rule->required_study_level }}</span>
                        @else
                            <span class="text-on-surface-variant">Not specified (any level)</span>
                        @endif
                    </div>
                </div>

                @if($rule->min_spm_as !== null || $rule->min_spm_credits !== null || $rule->min_cgpa !== null)
                    <div class="p-5 grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1 font-medium text-on-surface-variant">Academic Minimums</div>
                        <div class="sm:col-span-2 flex flex-wrap gap-2">
                            @if($rule->min_spm_as !== null)
                                <span class="badge badge-info">SPM As: ≥ {{ $rule->min_spm_as }}</span>
                            @endif
                            @if($rule->min_spm_credits !== null)
                                <span class="badge badge-info">SPM Credits: ≥ {{ $rule->min_spm_credits }}</span>
                            @endif
                            @if($rule->min_cgpa !== null)
                                <span class="badge badge-info">CGPA: ≥ {{ number_format($rule->min_cgpa, 2) }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Financial Rules -->
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Financial Requirements</h2>
            </div>
            <div class="divide-y divide-outline-variant/50">
                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Required Income Category</div>
                    <div class="sm:col-span-2">
                        @if($rule->required_income_category)
                            <span class="badge
                                {{ $rule->required_income_category === 'B40' ? 'badge-eligible' :
                                   ($rule->required_income_category === 'M40' ? 'badge-partial' : 'badge-not-suitable') }}">
                                {{ $rule->required_income_category }}
                            </span>
                        @else
                            <span class="text-on-surface-variant">Not specified (any category)</span>
                        @endif
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Max Household Income</div>
                    <div class="sm:col-span-2">
                        @if($rule->max_household_income !== null)
                            <span class="font-medium text-on-surface">RM {{ number_format($rule->max_household_income, 2) }}/month</span>
                        @else
                            <span class="text-on-surface-variant">No maximum</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Study & Institution Rules -->
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Field & Institution</h2>
            </div>
            <div class="divide-y divide-outline-variant/50">
                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Required Field of Study</div>
                    <div class="sm:col-span-2">
                        @if($rule->required_field_of_study)
                            <span class="badge badge-info">{{ $rule->required_field_of_study }}</span>
                        @else
                            <span class="text-on-surface-variant">Not specified (any field)</span>
                        @endif
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Required Institution Type</div>
                    <div class="sm:col-span-2">
                        @if($rule->required_institution_type)
                            <span class="badge badge-info">{{ $rule->required_institution_type }}</span>
                        @else
                            <span class="text-on-surface-variant">Not specified (any type)</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rule Types (Hard/Soft/None) -->
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Rule Types</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    <strong>Hard:</strong> Strict pass/fail filter | <strong>Soft:</strong> Contributes to score | <strong>None:</strong> Ignored
                </p>
            </div>
            <div class="divide-y divide-outline-variant/50">
                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Income Rule Type</div>
                    <div class="sm:col-span-2">
                        <span class="badge
                            {{ $rule->income_rule_type === 'hard' || $rule->income_rule_type }}">
                            {{ ucfirst($rule->income_rule_type) }}
                        </span>
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Study Level Rule Type</div>
                    <div class="sm:col-span-2">
                        <span class="badge
                            {{ $rule->study_level_rule_type === 'hard' ? 'badge-not-suitable' :
                               ($rule->study_level_rule_type === 'soft' ? 'badge-partial' : 'badge-info') }}">
                            {{ ucfirst($rule->study_level_rule_type) }}
                        </span>
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Field of Study Rule Type</div>
                    <div class="sm:col-span-2">
                        <span class="badge
                            {{ $rule->field_rule_type === 'hard' ? 'badge-not-suitable' :
                               ($rule->field_rule_type === 'soft' ? 'badge-partial' : 'badge-info') }}">
                            {{ ucfirst($rule->field_rule_type) }}
                        </span>
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1 font-medium text-on-surface-variant">Institution Type Rule Type</div>
                    <div class="sm:col-span-2">
                        <span class="badge
                            {{ $rule->institution_rule_type === 'hard' ? 'badge-not-suitable' :
                               ($rule->institution_rule_type === 'soft' ? 'badge-partial' : 'badge-info') }}">
                            {{ ucfirst($rule->institution_rule_type) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Custom JSON Rules -->
            @if($rule->rule_payload && count($rule->rule_payload) > 0)
            <div class="p-5 border-b border-outline-variant bg-surface-container-low">
                <h2 class="text-lg font-semibold text-on-surface">Custom Rules (JSON)</h2>
            </div>
            <div class="p-5 bg-surface-container-lowest">
                <pre class="text-sm text-on-surface-variant overflow-x-auto">{{ json_encode($rule->rule_payload, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    @endif
</div>
@endsection