<?php

namespace App\Console\Commands;

use App\Models\Scholarship;
use App\Services\DatabaseSnapshot;
use Database\Seeders\ScholarshipCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceScholarshipCatalog extends Command
{
    protected $signature = 'scholarships:replace-catalog {--dry-run : Show the targeted counts without writes} {--force : Replace without an interactive prompt}';

    protected $description = 'Back up the database and replace only the retired demo catalogue, preserving manual scholarships';

    public function handle(DatabaseSnapshot $snapshot): int
    {
        ScholarshipCatalog::validateReplacement(ScholarshipCatalog::entries(), today()->toDateString());
        $oldCount = Scholarship::whereIn('catalog_key', ScholarshipCatalog::retiredKeys())->count();
        $this->info("Retired demos to remove: {$oldCount}. New catalogue: ".ScholarshipCatalog::EXPECTED_COUNT.'. Other scholarships are preserved.');
        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }
        if (! $this->option('force') && ! $this->confirm('Back up and remove only retired demos and their saved links/logs, then seed the new catalogue?')) {
            return self::SUCCESS;
        }

        $backup = $snapshot->create(DB::connection());
        $this->info('Database backup: '.$backup);
        ScholarshipCatalog::replace();
        $this->info('Seeded '.ScholarshipCatalog::EXPECTED_COUNT.' richer fictional awards. Manual scholarships and their related records were preserved.');

        return self::SUCCESS;
    }
}
