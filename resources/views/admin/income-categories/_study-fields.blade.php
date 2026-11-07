@php
    $fieldErrors = $errors->getBag('studyField');
    $failedFieldId = $fieldErrors->any() ? filter_var(old('study_field_id'), FILTER_VALIDATE_INT) : null;
    $initialEditor = [
        'id' => $failedFieldId ?: '',
        'name' => $fieldErrors->any() && is_string(old('name')) ? old('name') : '',
        'is_active' => $fieldErrors->any() ? (bool) old('is_active', false) : true,
        'method' => $failedFieldId ? 'PATCH' : 'POST',
        'action' => $failedFieldId ? route('admin.study-fields.update', $failedFieldId) : route('admin.study-fields.store'),
    ];
@endphp
<section id="fields-of-study" class="mt-10 scroll-mt-24"
    x-data="{ open: @js($fieldErrors->any()), editor: @js($initialEditor) }"
    x-init="if (open) $nextTick(() => $refs.fieldName.focus())" @keydown.escape.window="open = false">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-on-surface">Fields of Study</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Manage the shared choices used in student academic information and scholarship requirements.</p>
        </div>
        <button type="button" class="btn btn-primary shrink-0" x-ref="addField"
            @click="editor = @js(['id' => '', 'name' => '', 'is_active' => true, 'method' => 'POST', 'action' => route('admin.study-fields.store')]); open = true; $nextTick(() => $refs.fieldName.focus())">+ Add Field</button>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-on-surface-variant">Field Name</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-on-surface-variant">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-on-surface-variant">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse($studyFields as $studyField)
                        <tr>
                            <td class="px-4 py-3 font-medium text-on-surface break-words">{{ $studyField->name }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $studyField->is_active ? 'bg-success/10 text-success' : 'bg-surface-container text-on-surface-variant' }}">{{ $studyField->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="btn btn-outline px-3 py-1.5" aria-label="Edit {{ $studyField->name }}"
                                    @click="editor = @js(['id' => $studyField->id, 'name' => $studyField->name, 'is_active' => $studyField->is_active, 'method' => 'PATCH', 'action' => route('admin.study-fields.update', $studyField)]); open = true; $nextTick(() => $refs.fieldName.focus())">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-on-surface-variant">No fields of study configured. Add a field to make it available in forms.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="border-t border-outline-variant px-4 py-3 text-xs text-on-surface-variant">Inactive fields are hidden from new selections. Existing records keep their field; renaming updates linked student and scholarship information.</p>
    </div>

    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="open = false">
        <div role="dialog" aria-modal="true" aria-labelledby="study-field-title" class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-outline-variant p-5">
                <h3 id="study-field-title" class="text-lg font-semibold" x-text="editor.id ? 'Edit Field of Study' : 'Add Field of Study'">{{ $failedFieldId ? 'Edit Field of Study' : 'Add Field of Study' }}</h3>
                <button type="button" class="btn btn-ghost p-2" aria-label="Close field editor" @click="open = false; $refs.addField.focus()">×</button>
            </div>
            <form method="POST" :action="editor.action" class="space-y-4 p-5">
                @csrf
                <input type="hidden" name="_method" :value="editor.method">
                <input type="hidden" name="study_field_id" :value="editor.id">
                <div>
                    <label for="study-field-name" class="label">Field Name <span class="text-error">*</span></label>
                    <input id="study-field-name" name="name" type="text" class="input" x-ref="fieldName" x-model="editor.name" maxlength="255" required>
                    @error('name', 'studyField')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
                </div>
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" x-model="editor.is_active"> Available for new selections</label>
                @error('is_active', 'studyField')<p class="text-sm text-error">{{ $message }}</p>@enderror
                <div class="flex justify-end gap-3 border-t border-outline-variant pt-4">
                    <button type="button" class="btn btn-outline" @click="open = false; $refs.addField.focus()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Field</button>
                </div>
            </form>
        </div>
    </div>
</section>
