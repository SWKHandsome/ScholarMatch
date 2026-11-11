<div>
    <label for="description" class="label">Description (optional)</label>
    <textarea id="description" name="description" rows="4" class="input @error('description') input-error @enderror"
        x-data="autoGrowingTextarea" @input="resize()"
        placeholder="Describe the benefits, eligibility overview and any application or intake details.">{{ old('description', $scholarship->description ?? '') }}</textarea>
    @error('description')
        <p class="mt-1 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
