<?php

namespace Database\Seeders;

use App\Services\ScholarshipRecommendationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScholarshipCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([ScholarshipSeeder::class, ScholarshipRuleSeeder::class]);
        });

        ScholarshipRecommendationService::invalidateCatalogCache();
    }
}
