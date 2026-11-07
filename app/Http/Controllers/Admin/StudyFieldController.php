<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicResult;
use App\Models\ScholarshipRule;
use App\Models\StudentProfile;
use App\Models\StudyField;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudyFieldController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        StudyField::create($data);
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect(route('admin.matching-settings').'#fields-of-study')->with('success', 'Field of study added successfully.');
    }

    public function update(Request $request, StudyField $studyField)
    {
        $data = $this->validated($request, $studyField);
        DB::transaction(function () use ($studyField, $data) {
            $oldName = $studyField->name;
            $studyField->update($data);
            if ($oldName !== $data['name']) {
                AcademicResult::where('field_of_study', $oldName)->update(['field_of_study' => $data['name']]);
                StudentProfile::where('field_of_study', $oldName)->update(['field_of_study' => $data['name']]);
                ScholarshipRule::where('required_field_of_study', $oldName)->update(['required_field_of_study' => $data['name']]);
                ScholarshipRule::whereNotNull('required_fields_of_study')->chunkById(200, function ($rules) use ($oldName, $data) {
                    foreach ($rules as $rule) {
                        $fields = is_array($rule->required_fields_of_study) ? $rule->required_fields_of_study : [];
                        if (in_array($oldName, $fields, true)) {
                            $rule->update(['required_fields_of_study' => array_map(fn ($field) => $field === $oldName ? $data['name'] : $field, $fields)]);
                        }
                    }
                });
            }
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return redirect(route('admin.matching-settings').'#fields-of-study')->with('success', 'Field of study updated successfully.');
    }

    private function validated(Request $request, ?StudyField $field = null): array
    {
        return $request->validateWithBag('studyField', [
            'name' => ['bail', 'required', 'string', 'max:255', Rule::unique('study_fields', 'name')->ignore($field?->id), function ($attribute, $value, $fail) use ($field) {
                if (! is_string($value)) {
                    return;
                }
                if (StudyField::whereKeyNot($field?->id ?? 0)->get()->contains(fn ($item) => mb_strtolower($item->name) === mb_strtolower($value))) {
                    $fail('This field of study already exists.');
                }
            }],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
