<div>
    <label class="label" for="deadline_type">Deadline Basis</label>
    <select class="input mb-3" id="deadline_type" name="deadline_type">
        @foreach(\App\Models\Scholarship::DEADLINE_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('deadline_type', $scholarship->deadline_type ?? 'unconfirmed') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('deadline_type')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
    <label class="label" for="application_notes">Application / Intake Notes (optional)</label>
    <textarea class="input @error('application_notes') input-error @enderror" id="application_notes" name="application_notes" rows="3" maxlength="2000">{{ old('application_notes', $scholarship->application_notes ?? '') }}</textarea>
    <p class="mt-1 text-sm text-on-surface-variant">Record the applicable intake, relative deadline or closed application round.</p>
    @error('application_notes')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
</div>
