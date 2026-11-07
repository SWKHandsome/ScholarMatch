<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSnapshot
{
    /** A data-only snapshot; restore MySQL tables into the same migrated schema. */
    public function create(Connection $connection): string
    {
        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory, 0700);
        $path = $directory.'/before-demo-refresh-'.now()->format('Ymd-His').'-'.Str::random(8);
        if ($connection->getDriverName() === 'sqlite' && $connection->getDatabaseName() !== ':memory:') {
            $path .= '.sqlite';
            $connection->statement('VACUUM INTO ?', [$path]);
        } elseif ($connection->getDriverName() === 'mysql') {
            $path .= '.json';
            $snapshot = $connection->transaction(function () use ($connection) {
                $tables = [];
                foreach ($connection->getSchemaBuilder()->getTableListing(schemaQualified: false) as $table) {
                    $tables[$table] = $connection->table($table)->get()->map(fn ($row) => (array) $row)->all();
                }

                return ['format' => 'scholarmatch-data-backup-v1', 'driver' => 'mysql', 'created_at' => now()->toIso8601String(), 'tables' => $tables];
            });
            File::put($path, json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if (json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR) !== $snapshot) {
                throw new RuntimeException('Database backup could not be verified.');
            }
        } else {
            throw new RuntimeException('A file-backed SQLite or MySQL database is required for backup.');
        }

        return $path;
    }
}
