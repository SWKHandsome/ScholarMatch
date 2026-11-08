@props(['field', 'id', 'value' => '', 'label' => null, 'showErrors' => true])
@php
    $nameField = $field === 'name';
    $hint = $nameField ? \App\Support\AccountValidation::NAME_HINT : \App\Support\AccountValidation::EMAIL_HINT;
    $pattern = $nameField ? \App\Support\AccountValidation::NAME_PATTERN : \App\Support\AccountValidation::EMAIL_PATTERN;
    $fieldErrors = $showErrors ? $errors->get($field) : [];
@endphp
<div data-account-group>
    <label for="{{ $id }}" class="block font-medium text-sm text-gray-700">
        {{ $label ?? ($nameField ? 'Name' : 'Email') }} <span class="text-error" aria-hidden="true">*</span>
    </label>
    <input id="{{ $id }}" name="{{ $field }}" type="{{ $nameField ? 'text' : 'email' }}"
           value="{{ $value }}" required maxlength="{{ $nameField ? 100 : 255 }}"
           pattern="{{ $pattern }}" data-account-field
           data-format-message="{{ $hint }}" autocomplete="{{ $nameField ? 'name' : 'username' }}"
           aria-describedby="{{ $id }}-feedback {{ $id }}-server"
           aria-invalid="{{ $fieldErrors ? 'true' : 'false' }}"
           {{ $attributes->class(['input mt-1', 'border-error' => (bool) $fieldErrors]) }}>
    <p id="{{ $id }}-feedback" data-account-feedback class="mt-1 text-sm text-error" role="alert" hidden></p>
    <div id="{{ $id }}-server" data-account-server-error>
        <x-input-error :messages="$fieldErrors" class="mt-2" />
    </div>
</div>
