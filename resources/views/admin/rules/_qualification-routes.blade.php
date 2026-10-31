@php
    $initialRoutes = old('rules_submitted') ? array_values((array) old('qualification_routes', [])) : $rule->academicRoutes();
@endphp
<section class="space-y-4" x-data="qualificationEditor(@js($initialRoutes), @js(\App\Support\AcademicQualifications::frontend()))" aria-labelledby="qualification-routes-heading">
    <input type="hidden" name="qualification_routes_submitted" value="1" disabled :disabled="false">
    <div>
        <h3 id="qualification-routes-heading" class="font-semibold text-lg">Accepted Qualification Routes</h3>
        <p class="mt-1 text-sm text-on-surface-variant">A student must meet all conditions in any one box, along with the scholarship's other requirements. Each box uses the student's latest qualification.</p>
        <p class="mt-1 text-sm text-on-surface-variant">Result minimums are optional; blank means no minimum. Put named-subject conditions, such as Mathematics at least C, in the scholarship's Special Requirements for provider verification.</p>
    </div>
    <p x-show="routes.length === 0" class="rounded-lg bg-surface-container-low p-4 text-sm text-on-surface-variant">No qualification restriction. Add a qualification to restrict accepted qualifications, results or study status.</p>
    <template x-for="(route, index) in routes" :key="route._key">
        <fieldset class="rounded-lg border border-outline-variant p-4 space-y-4" data-qualification-route>
            <legend class="px-2 font-semibold" x-text="'Qualification ' + (index + 1)"></legend>
            <div class="flex justify-end">
                <button type="button" class="text-sm text-error underline" @click="routes.splice(index, 1)" :aria-label="'Remove qualification ' + (index + 1)">Remove</button>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" :for="fieldId(route, 'level')">Qualification Level <span class="text-error">*</span></label>
                    <select class="input" :id="fieldId(route, 'level')" :name="fieldName(index, 'education_level')" x-model="route.education_level" @change="levelChanged(route)" required>
                        <option value="">Select qualification</option>
                        @foreach(\App\Support\AcademicQualifications::categories() as $category)
                            <optgroup label="{{ $category['label'] }}">
                                @foreach($category['qualifications'] as $value)
                                    <option value="{{ $value }}">{{ \App\Support\AcademicQualifications::label($value) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" :for="fieldId(route, 'status')">Study Status <span class="text-error">*</span></label>
                    <select class="input" :id="fieldId(route, 'status')" :name="fieldName(index, 'study_status')" x-model="route.study_status" @change="statusChanged(route)" required>
                        <option value="">Select study status</option>
                        <option value="completed">Completed</option>
                        <option value="studying" :disabled="!cgpaBased(route)">Currently Studying</option>
                        <option value="any" :disabled="!cgpaBased(route)">Either status</option>
                    </select>
                    <p class="mt-1 text-sm text-on-surface-variant" x-show="route.education_level && !cgpaBased(route)" x-cloak>Exam-based applicants need completed exams and official final results.</p>
                </div>
            </div>
            <div x-show="route.education_level === 'IGCSE'" x-cloak>
                <label class="label" :for="fieldId(route, 'grading_scale')">IGCSE Grading Scale</label>
                <select class="input" :id="fieldId(route, 'grading_scale')" :name="fieldName(index, 'grading_scale')" x-model="route.grading_scale"
                    @change="scaleChanged(route)" :disabled="route.education_level !== 'IGCSE'" :required="route.requirements.some(condition => condition.type === 'grade')">
                    <option value="">Any grading scale (no grade conditions)</option>
                    <option value="igcse_letters">Letter grades (A*–G)</option>
                    <option value="igcse_9_1">Number grades (9–1)</option>
                </select>
                <p class="mt-1 text-sm text-on-surface-variant">Choose a scale to add grade minimums. Use separate boxes to accept alternative scales; no automatic conversion is applied.</p>
            </div>
            <input type="hidden" :name="fieldName(index, 'grading_scale')" :value="route.grading_scale" :disabled="!gradeBased(route) || route.education_level === 'IGCSE'">
            <div class="space-y-3">
                <h4 class="font-semibold">Academic Requirements</h4>
                <p class="text-sm text-on-surface-variant">Add only the conditions stated by the provider. Every condition in this box must pass. Different qualification boxes are alternatives.</p>
                <p class="text-sm text-on-surface-variant" x-show="!route.requirements.length">No academic result minimum is configured. The qualification and study status still apply.</p>
                <template x-for="(condition, conditionIndex) in route.requirements" :key="condition._key">
                    <fieldset class="rounded-lg border border-outline-variant bg-surface-container-low p-4 space-y-3" data-academic-condition>
                        <legend class="px-2 text-sm font-semibold" x-text="'Requirement ' + (conditionIndex + 1)"></legend>
                        <div>
                            <label class="label" :for="'condition-' + condition._key + '-type'">Requirement Type <span class="text-error">*</span></label>
                            <select class="input" :id="'condition-' + condition._key + '-type'" x-model="condition.type" @change="conditionChanged(route, condition)" required>
                                <template x-for="type in availableTypes(route, condition)" :key="type.value">
                                    <option :value="type.value" :selected="condition.type === type.value" x-text="type.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-3" x-show="condition.type === 'grade'">
                            <p class="text-sm text-on-surface-variant">Every grade row must pass. Counts include subjects at the selected grade and all stronger grades.</p>
                            <template x-for="(gradeRow, rowIndex) in condition.rows" :key="gradeRow._key">
                                <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] gap-3 items-end" data-grade-row>
                                    <div>
                                        <label :class="rowIndex === 0 ? 'label' : 'sr-only'" :for="'grade-row-' + gradeRow._key + '-count'">Minimum Subjects <span class="text-error">*</span></label>
                                        <input class="input" type="number" min="0" :max="maxSubjects(route)" step="1"
                                            :id="'grade-row-' + gradeRow._key + '-count'" :name="gradeRowName(index, rowIndex, 'value')" x-model="gradeRow.value"
                                            :disabled="condition.type !== 'grade'" :required="condition.type === 'grade'">
                                    </div>
                                    <div>
                                        <label :class="rowIndex === 0 ? 'label' : 'sr-only'" :for="'grade-row-' + gradeRow._key + '-grade'">At Grade or Better <span class="text-error">*</span></label>
                                        <select class="input" :id="'grade-row-' + gradeRow._key + '-grade'" :name="gradeRowName(index, rowIndex, 'grade')" x-model="gradeRow.grade"
                                            :disabled="condition.type !== 'grade'" :required="condition.type === 'grade'">
                                            <option value="">Select grade</option>
                                            <template x-for="grade in grades(route)" :key="grade"><option :value="grade" :selected="gradeRow.grade === grade" x-text="grade"></option></template>
                                        </select>
                                    </div>
                                    <button type="button" class="pb-3 text-sm text-error underline"
                                        @click="removeGradeRow(route, condition, rowIndex)"
                                        :aria-label="'Remove grade row ' + (rowIndex + 1) + ' from requirement ' + (conditionIndex + 1) + ' in qualification ' + (index + 1)">Remove</button>
                                </div>
                            </template>
                            <button type="button" class="btn btn-outline text-sm" @click="addGradeRow(condition)" :disabled="!route.grading_scale || condition.rows.length >= 20">+ Add Grade Row</button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3" x-show="condition.type !== 'grade'">
                            <div x-show="condition.type !== 'min_stam_rank' && condition.type !== 'grade'">
                                <label class="label" :for="'condition-' + condition._key + '-value'"><span x-text="valueLabel(condition)"></span> <span class="text-error">*</span></label>
                                <input class="input" type="number" :id="'condition-' + condition._key + '-value'"
                                    :name="conditionName(index, conditionIndex, condition)" x-model="condition.value"
                                    :min="valueMin(condition)" :max="valueMax(route, condition)" :step="valueStep(condition)"
                                    :disabled="condition.type === 'min_stam_rank' || condition.type === 'grade'" :required="condition.type !== 'min_stam_rank' && condition.type !== 'grade'">
                            </div>
                            <div x-show="condition.type === 'min_stam_rank'">
                                <label class="label" :for="'condition-' + condition._key + '-rank'">Minimum Overall STAM Pangkat <span class="text-error">*</span></label>
                                <select class="input" :id="'condition-' + condition._key + '-rank'"
                                    :name="conditionName(index, conditionIndex, condition)" x-model="condition.value"
                                    :disabled="condition.type !== 'min_stam_rank'" :required="condition.type === 'min_stam_rank'">
                                    <option value="">Select minimum pangkat</option>
                                    @foreach(\App\Support\AcademicQualifications::RANKS as $rank)<option value="{{ $rank }}">{{ $rank }}</option>@endforeach
                                </select>
                            </div>
                            <div x-show="isPoints(condition)">
                                <label class="label" :for="'condition-' + condition._key + '-subjects'">Best Number of Subjects <span class="text-error">*</span></label>
                                <input class="input" type="number" min="1" :max="maxSubjects(route)" step="1"
                                    :id="'condition-' + condition._key + '-subjects'" :name="conditionName(index, conditionIndex, condition, 'subjects')"
                                    x-model="condition.subjects" :disabled="!isPoints(condition)" :required="isPoints(condition)">
                            </div>
                            <div x-show="isPoints(condition)">
                                <label class="label" :for="'condition-' + condition._key + '-grade'">Include Grades at or Above <span class="text-error">*</span></label>
                                <select class="input" :id="'condition-' + condition._key + '-grade'"
                                    :name="conditionName(index, conditionIndex, condition, 'grade')" x-model="condition.grade"
                                    :disabled="!isPoints(condition)" :required="isPoints(condition)">
                                    <option value="">Select grade</option>
                                    <template x-for="grade in grades(route)" :key="grade"><option :value="grade" :selected="condition.grade === grade" x-text="grade"></option></template>
                                </select>
                            </div>
                        </div>
                        <p class="rounded-lg bg-white p-3 text-sm text-on-surface" x-text="describe(route, condition)" aria-live="polite"></p>
                        <p class="text-xs text-on-surface-variant" x-show="isPoints(condition)">Use a best-subject calculation only when the provider confirms its formula. If no separate grade cutoff is stated, use the lowest grade. Named-subject inclusions or exclusions belong in Special Requirements.</p>
                        <p class="text-xs text-on-surface-variant" x-show="condition.type === 'stpm_average'">STPM points: A = 4.00, A- = 3.67, B+ = 3.33, B = 3.00, B- = 2.67, C+ = 2.33, C = 2.00, C- = 1.67, D+ = 1.33, D = 1.00, F = 0.00. Higher is better. This does not recalculate official overall CGPA.</p>
                        <p class="text-xs text-on-surface-variant" x-show="condition.type === 'uec_average' || condition.type === 'uec_total'">UEC points: A1 = 1, A2 = 2, B3 = 3, B4 = 4, B5 = 5, B6 = 6, C7 = 7, C8 = 8, F9 = 9. Lower is better.</p>
                        <button type="button" class="text-sm text-error underline" @click="route.requirements.splice(conditionIndex, 1)" :aria-label="'Remove requirement ' + (conditionIndex + 1) + ' from qualification ' + (index + 1)">Remove this requirement</button>
                    </fieldset>
                </template>
                <p class="text-sm text-on-surface-variant" x-show="route.education_level === 'A-Level'">For ABB, add 1 subject at A or better AND 3 subjects at B or better; the A counts towards the three. This represents one A and two additional B-or-better subjects. Provider-specific CGPA conversions belong in Special Requirements unless a formula is supported.</p>
                <p class="text-sm text-on-surface-variant" x-show="route.education_level === 'UEC'">For 2 As including A1/A2, require 2 subjects at A2 or better. For 11 total points or below across the best 5 subjects, choose Maximum total points, enter 11 and 5, and use the provider's grade cutoff.</p>
                <p class="text-sm text-on-surface-variant" x-show="route.education_level === 'STPM'">For 1 A, select A (or A- only if the provider includes it). An average of 3.00 across three subjects differs from requiring three subjects each at B / 3.00 or better. Confirm the provider's meaning before choosing a type.</p>
                <button type="button" class="btn btn-outline text-sm" @click="addRequirement(route)" :disabled="!route.education_level || !availableTypes(route).length || route.requirements.length >= 20">+ Add Another Requirement</button>
            </div>
            <div class="grid sm:grid-cols-2 gap-4" x-show="cgpaBased(route) && route.study_status === 'studying'" x-cloak>
                @foreach(['min_year_of_study' => 'Minimum Year of Study', 'max_year_of_study' => 'Maximum Year of Study'] as $field => $label)
                    <div>
                        <label class="label" :for="fieldId(route, '{{ $field }}')">{{ $label }} (optional)</label>
                        <input class="input" type="number" min="1" max="10" step="1" :id="fieldId(route, '{{ $field }}')" :name="fieldName(index, '{{ $field }}')" x-model="route.{{ $field }}" :disabled="!cgpaBased(route) || route.study_status !== 'studying'" placeholder="No limit">
                    </div>
                @endforeach
                <p class="text-sm text-on-surface-variant sm:col-span-2">Limits include the entered year. Leave either boundary empty when it is unrestricted.</p>
            </div>
        </fieldset>
    </template>
    <button type="button" class="btn btn-outline" @click="addRoute()" :disabled="routes.length >= 50">+ Add Qualification</button>
    <noscript><p class="text-error">Enable JavaScript to edit qualification routes.</p></noscript>
</section>
