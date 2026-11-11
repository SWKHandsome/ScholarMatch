<div>
    <label class="label" for="deadline_type">Deadline Basis</label>
    <select class="input" id="deadline_type" name="deadline_type">
        @foreach(\App\Models\Scholarship::DEADLINE_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('deadline_type', $scholarship->deadline_type ?? 'unconfirmed') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('deadline_type')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
</div>
