<div>
    <label class="label" for="special_requirements">Special Requirements (optional)</label>
    <textarea class="input @error('special_requirements') input-error @enderror" id="special_requirements" name="special_requirements" rows="4" maxlength="5000" aria-describedby="special-requirements-help">{{ old('special_requirements', $scholarship->special_requirements ?? '') }}</textarea>
    <p id="special-requirements-help" class="mt-1 text-sm text-on-surface-variant">Enter conditions students must verify with the provider, such as an interview or supporting documents. Otherwise Eligible results will show !. Leave empty when there are no special conditions.</p>
    @error('special_requirements')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
</div>
