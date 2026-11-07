<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            ScholarshipCatalog::retireUnmodifiedDemoRecords();

            foreach (ScholarshipCatalog::entries() as $entry) {
                Scholarship::updateOrCreate(
                    ['catalog_key' => $entry['catalog_key']],
                    Arr::except($entry, ['rules', 'evidence_urls', 'study_country'])
                );
            }
        });

        ScholarshipRecommendationService::invalidateCatalogCache();
    }
}
