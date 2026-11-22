<div>
    @include('components.scholarship-text-editor', ['field' => 'special_requirements', 'label' => 'Special Requirements (optional)', 'value' => $scholarship->special_requirements ?? '', 'helpId' => 'special-requirements-help'])
    <p id="special-requirements-help" class="mt-1 text-sm text-on-surface-variant">Enter conditions students must verify with the provider, such as an interview or supporting documents. Otherwise Eligible results will show !. Leave empty when there are no special conditions.</p>
</div>
