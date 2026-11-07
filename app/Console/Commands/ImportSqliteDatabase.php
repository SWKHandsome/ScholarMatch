<?php

namespace App\Console\Commands;

use App\Services\SqliteDatabaseTransfer;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportSqliteDatabase extends Command
{
    protected $signature = 'database:import-sqlite {source : Path to a migrated SQLite backup} {--dry-run : Check without copying rows} {--force : Run without a confirmation prompt}';

    protected $description = 'Copy and verify SQLite application records into an empty, migrated MySQL database';

    public function handle(SqliteDatabaseTransfer $transfer): int
    {
        if (! $this->option('dry-run') && ! $this->option('force')
            && ! $this->confirm('Import into the configured empty MySQL database? The source will remain unchanged.')) {
            return self::SUCCESS;
        }
        try {
            $counts = $transfer->transfer($this->argument('source'), DB::connection(), $this->option('dry-run'));
            $this->table(['Table', 'Source rows'], array_map(fn ($table, $count) => [$table, $count], array_keys($counts), $counts));
            $this->info($this->option('dry-run') ? 'Preflight passed. No rows copied.' : 'Import committed after row-count and content verification.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            // Database exceptions can include personal row values; keep those out of console logs.
            $this->error($e instanceof \RuntimeException && ! $e instanceof \PDOException
                && ! $e instanceof QueryException ? $e->getMessage() : 'Database import failed; check schema compatibility. No imported rows were committed.');

            return self::FAILURE;
        }
    }
}
