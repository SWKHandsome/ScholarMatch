@php
    $awardTypes = \App\Models\Scholarship::AWARD_TYPES;
    $submittedAwardType = old('award_type', $scholarship->award_type ?? '');
    $selectedAwardType = is_string($submittedAwardType) ? \App\Models\Scholarship::normalizeAwardType($submittedAwardType) : '';
    if ($selectedAwardType !== '' && ! in_array($selectedAwardType, $awardTypes, true)) {
        $awardTypes[] = $selectedAwardType;
    }
@endphp
<div>
    <label for="award_type" class="label">Award Type <span class="text-error">*</span></label>
    <select id="award_type" name="award_type" class="input @error('award_type') input-error @enderror" required>
        <option value="">Select Award Type</option>
        @foreach($awardTypes as $type)
            <option value="{{ $type }}" @selected($selectedAwardType === $type)>{{ $type }}</option>
        @endforeach
    </select>
    @error('award_type')
        <p class="mt-1 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
