@php
    $initialRoutes = old('rules_submitted') ? array_values((array) old('qualification_routes', [])) : $rule->academicRoutes();
@endphp
<section class="space-y-4" x-data="qualificationEditor(@js($initialRoutes))" aria-labelledby="qualification-routes-heading">
    <input type="hidden" name="qualification_routes_submitted" value="1" disabled :disabled="false">
    <div>
        <h3 id="qualification-routes-heading" class="font-semibold text-lg">Accepted Qualification Routes</h3>
        <p class="mt-1 text-sm text-on-surface-variant">A student must meet all conditions in any one box, along with the scholarship's other requirements. Each box uses the student's latest qualification.</p>
        <p class="mt-1 text-sm text-on-surface-variant"><span class="text-error">*</span> Required within each box. Result minimums are optional; blank means no minimum.</p>
    </div>
    <p x-show="routes.length === 0" class="rounded-lg bg-surface-container-low p-4 text-sm text-on-surface-variant">No qualification restriction. Add a qualification to restrict accepted qualifications, results or study status.</p>
    <template x-for="(route, index) in routes" :key="route._key">
        <fieldset class="rounded-lg border border-outline-variant p-4 space-y-4" data-qualification-route>
            <legend class="px-2 font-semibold" x-text="`Qualification ${index + 1}`"></legend>
            <div class="flex justify-end">
                <button type="button" class="text-sm text-error underline" @click="routes.splice(index, 1)" :aria-label="`Remove qualification ${index + 1}`">Remove</button>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" :for="fieldId(route, 'level')">Qualification Level <span class="text-error">*</span></label>
                    <select class="input" :id="fieldId(route, 'level')" :name="fieldName(index, 'education_level')" x-model="route.education_level" @change="levelChanged(route)" required>
                        <option value="">Select qualification</option>
                        @foreach(\App\Models\AcademicResult::EDUCATION_LEVELS as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" :for="fieldId(route, 'status')">Study Status <span class="text-error">*</span></label>
                    <select class="input" :id="fieldId(route, 'status')" :name="fieldName(index, 'study_status')" x-model="route.study_status" @change="statusChanged(route)" required>
                        <option value="">Select study status</option>
                        <option value="completed">Completed</option>
                        <option value="studying" :disabled="route.education_level === 'SPM'">Currently Studying</option>
                        <option value="any" :disabled="route.education_level === 'SPM'">Either status</option>
                    </select>
                    <p class="mt-1 text-sm text-on-surface-variant" x-show="route.education_level === 'SPM'" x-cloak>SPM applicants must have completed the exam and received official results.</p>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4" x-show="route.education_level === 'SPM'" x-cloak>
                @foreach(['min_spm_as' => 'Minimum SPM As', 'min_spm_credits' => 'Minimum SPM Credits'] as $field => $label)
                    <div>
                        <label class="label" :for="fieldId(route, '{{ $field }}')">{{ $label }} (optional)</label>
                        <input class="input" type="number" min="0" max="12" step="1" :id="fieldId(route, '{{ $field }}')" :name="fieldName(index, '{{ $field }}')" x-model="route.{{ $field }}" :disabled="route.education_level !== 'SPM'" placeholder="No minimum">
                    </div>
                @endforeach
                <p class="text-sm text-on-surface-variant sm:col-span-2">If both are set, both must pass. Credits include subjects counted as As.</p>
            </div>
            <div x-show="route.education_level !== '' && route.education_level !== 'SPM'" x-cloak>
                <label class="label" :for="fieldId(route, 'min_cgpa')">Minimum CGPA (optional)</label>
                <input class="input" type="number" min="0" max="4" step="0.01" :id="fieldId(route, 'min_cgpa')" :name="fieldName(index, 'min_cgpa')" x-model="route.min_cgpa" :disabled="!route.education_level || route.education_level === 'SPM'" placeholder="No minimum">
                <p class="mt-1 text-sm text-on-surface-variant">Applies only to this qualification. Current students use their latest official cumulative CGPA; completed students use their final CGPA.</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4" x-show="route.education_level !== 'SPM' && route.study_status === 'studying'" x-cloak>
                @foreach(['min_year_of_study' => 'Minimum Year of Study', 'max_year_of_study' => 'Maximum Year of Study'] as $field => $label)
                    <div>
                        <label class="label" :for="fieldId(route, '{{ $field }}')">{{ $label }} (optional)</label>
                        <input class="input" type="number" min="1" max="10" step="1" :id="fieldId(route, '{{ $field }}')" :name="fieldName(index, '{{ $field }}')" x-model="route.{{ $field }}" :disabled="route.education_level === 'SPM' || route.study_status !== 'studying'" placeholder="No limit">
                    </div>
                @endforeach
                <p class="text-sm text-on-surface-variant sm:col-span-2">Limits include the entered year. Leave either boundary empty when it is unrestricted.</p>
            </div>
        </fieldset>
    </template>
    <button type="button" class="btn btn-outline" @click="addRoute()" :disabled="routes.length >= 50">+ Add Qualification</button>
    <noscript><p class="text-error">Enable JavaScript to edit qualification routes.</p></noscript>
</section>
