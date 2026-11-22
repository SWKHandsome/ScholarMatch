@php
    $content = old($field, $value ?? '');
    $content = is_string($content) ? $content : '';
    $editorDocument = \App\Support\ScholarshipText::document($content);
@endphp
<div x-data="scholarshipTextEditor(@js($editorDocument))">
    <label class="label" id="{{ $field }}-label" for="{{ $field }}">{{ $label }}</label>
    <div x-show="ready" x-cloak class="rounded-md border border-outline bg-white focus-within:ring-2 focus-within:ring-primary focus-within:border-primary @error($field) border-error @enderror">
        <div class="flex flex-wrap gap-1 border-b border-outline-variant p-2" role="toolbar" aria-label="{{ $label }} formatting">
            <button type="button" class="btn btn-ghost text-sm px-3 py-1" :class="bold ? 'bg-primary/10 text-primary' : ''"
                :aria-pressed="bold" @mousedown.prevent @click="toggleBold()" aria-label="Bold"><strong>B</strong></button>
            <button type="button" class="btn btn-ghost text-sm px-3 py-1" :class="listStyle === 'bullet' ? 'bg-primary/10 text-primary' : ''"
                :aria-pressed="listStyle === 'bullet'" @mousedown.prevent @click="toggleList('bullet')">• Bullets</button>
            <button type="button" class="btn btn-ghost text-sm px-3 py-1" :class="listStyle === 'dash' ? 'bg-primary/10 text-primary' : ''"
                :aria-pressed="listStyle === 'dash'" @mousedown.prevent @click="toggleList('dash')">– Dashes</button>
        </div>
        <div x-ref="editor"></div>
    </div>
    <textarea id="{{ $field }}" name="{{ $field }}" rows="4" class="input" x-ref="input" x-show="!ready"
        @if(!empty($helpId)) aria-describedby="{{ $helpId }}" @endif>{{ $content }}</textarea>
    @error($field)<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
</div>
