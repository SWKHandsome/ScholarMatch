<?php

namespace App\Console\Commands;

use Database\Seeders\ScholarshipCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReplaceScholarshipCatalog extends Command
{
    protected $signature = 'scholarships:replace-catalog {--force : Replace without an interactive prompt}';

    protected $description = 'Back up SQLite and replace all scholarships with the bundled fictional demo awards';

    public function handle(): int
    {
        ScholarshipCatalog::validateReplacement(ScholarshipCatalog::entries(), today()->toDateString());
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() === ':memory:') {
            $this->error('This replacement command requires a file-backed SQLite database for its backup.');

            return self::FAILURE;
        }
        if (! $this->option('force') && ! $this->confirm('Replace all scholarships and their saved links/recommendation history? Student accounts and profiles will be kept.')) {
            return self::SUCCESS;
        }

        File::ensureDirectoryExists(storage_path('app/backups'));
        $backup = storage_path('app/backups/before-local-catalog-'.now()->format('Ymd-His').'-'.Str::random(6).'.sqlite');
        $connection->statement('VACUUM INTO ?', [$backup]);
        $this->info('Database backup: '.$backup);
        ScholarshipCatalog::replace();
        $this->info('Replaced the catalogue: exactly '.ScholarshipCatalog::EXPECTED_COUNT.' fictional demo scholarships and awards.');

        return self::SUCCESS;
    }
}
