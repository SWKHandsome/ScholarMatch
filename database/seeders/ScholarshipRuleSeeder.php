<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use App\Services\ScholarshipRuleEditor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScholarshipRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (ScholarshipCatalog::entries() as $entry) {
                $scholarship = Scholarship::where('catalog_key', $entry['catalog_key'])->firstOrFail();

                // Reset absent fields so obsolete thresholds cannot survive a catalogue update.
                app(ScholarshipRuleEditor::class)->save($scholarship, array_merge([
                    'required_nationality' => null,
                    'required_study_level' => null,
                    'required_income_category' => null,
                    'max_household_income' => null,
                    'required_fields_of_study' => [],
                    'required_institution_type' => null,
                    'qualification_routes' => [],
                ], $entry['rules']));
            }
        });

        ScholarshipRecommendationService::invalidateCatalogCache();
    }
}
