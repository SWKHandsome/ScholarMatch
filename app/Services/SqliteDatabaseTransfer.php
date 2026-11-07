<?php

namespace App\Services;

use App\Models\StudyField;
use Illuminate\Database\Connection;
use PDO;
use Pdo\Sqlite;
use RuntimeException;

class SqliteDatabaseTransfer
{
    /** Parent tables precede children. Cache and migration history are rebuilt locally. */
    public const TABLES = [
        'study_fields', 'users', 'income_categories', 'scholarships', 'student_profiles', 'academic_results',
        'scholarship_rules', 'scholarship_qualification_routes', 'saved_scholarships',
        'recommendation_logs', 'password_reset_tokens', 'sessions', 'jobs', 'job_batches', 'failed_jobs',
    ];

    public function transfer(string $path, Connection $target, bool $dryRun = false): array
    {
        if ($target->getDriverName() !== 'mysql') {
            throw new RuntimeException('The destination must be MySQL. The SQLite source is never modified.');
        }
        if (! is_file($path)) {
            throw new RuntimeException('The SQLite source file does not exist.');
        }
        $source = new PDO('sqlite:'.$path, null, null, [
            PHP_VERSION_ID >= 80500 ? Sqlite::ATTR_OPEN_FLAGS : PDO::SQLITE_ATTR_OPEN_FLAGS => PHP_VERSION_ID >= 80500 ? Sqlite::OPEN_READONLY : PDO::SQLITE_OPEN_READONLY,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $source->beginTransaction();
        try {
            if ($source->query('PRAGMA integrity_check')->fetchColumn() !== 'ok'
                || $source->query('PRAGMA foreign_key_check')->fetchAll() !== []) {
                throw new RuntimeException('The SQLite source failed its integrity/foreign-key checks.');
            }
            $history = $source->query('SELECT migration FROM migrations ORDER BY migration')->fetchAll(PDO::FETCH_COLUMN);
            if ($history !== $target->table('migrations')->orderBy('migration')->pluck('migration')->all()) {
                throw new RuntimeException('Source and destination must have the same completed migrations.');
            }
            $counts = [];
            foreach (self::TABLES as $table) {
                if ($target->table($table)->exists() && ($table !== 'study_fields' || ! $this->hasOnlyInitialFields($target))) {
                    throw new RuntimeException("Destination table {$table} is not empty; import refused.");
                }
                $counts[$table] = (int) $source->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
            }
            if (! $dryRun) {
                $target->transaction(function () use ($source, $target, $counts) {
                    foreach (self::TABLES as $table) {
                        $rows = $source->query('SELECT * FROM "'.$table.'"')->fetchAll();
                        if ($table === 'study_fields') {
                            // Only the unused migration defaults passed preflight above.
                            $target->table($table)->delete();
                        }
                        foreach (array_chunk($rows, 100) as $chunk) {
                            $target->table($table)->insert($chunk);
                        }
                        if ($target->table($table)->count() !== $counts[$table]) {
                            throw new RuntimeException("Row count verification failed for {$table}.");
                        }
                        // Compare every column, including password hashes, IDs and JSON payloads.
                        $columns = $target->getSchemaBuilder()->getColumns($table);
                        $json = array_column(array_filter($columns, fn ($c) => $c['type_name'] === 'json'), 'name');
                        $dates = array_column(array_filter($columns, fn ($c) => $c['type_name'] === 'date'), 'name');
                        $numeric = array_column(array_filter($columns, fn ($c) => preg_match('/int|decimal|numeric|float|double/', $c['type_name'])), 'name');
                        $actual = $target->table($table)->get()->map(fn ($row) => (array) $row)->all();
                        if ($this->fingerprints($rows, $json, $dates, $numeric) !== $this->fingerprints($actual, $json, $dates, $numeric)) {
                            throw new RuntimeException("Row content verification failed for {$table}; import rolled back.");
                        }
                    }
                });
            }

            return $counts;
        } finally {
            $source->rollBack();
        }
    }

    private function fingerprints(array $rows, array $json, array $dates, array $numeric): array
    {
        $hashes = [];
        foreach ($rows as $row) {
            ksort($row);
            foreach ($row as $key => &$value) {
                if ($value !== null && in_array($key, $json, true)) {
                    $value = $this->canonicalJson(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
                } elseif ($value !== null && in_array($key, $dates, true)) {
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}(?: 00:00:00(?:\.0+)?)?$/D', $value)) {
                        throw new RuntimeException('A date column contains a non-date value; import refused.');
                    }
                    $value = substr($value, 0, 10);
                } elseif ($value !== null && in_array($key, $numeric, true) && is_numeric($value)) {
                    // SQLite numeric affinity and MySQL DECIMAL differ in trailing zero formatting.
                    $value = (string) $value;
                    if (str_contains($value, '.')) {
                        $value = rtrim(rtrim($value, '0'), '.');
                    }
                }
            }
            unset($value);
            $hashes[] = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
        }
        sort($hashes);

        return $hashes;
    }

    private function hasOnlyInitialFields(Connection $target): bool
    {
        $actual = $target->table('study_fields')->get(['name', 'seed_name', 'is_active'])->map(fn ($row) => [
            'name' => $row->name, 'seed_name' => $row->seed_name, 'is_active' => (bool) $row->is_active,
        ])->all();
        $expected = StudyField::defaultRows();
        usort($actual, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($expected, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $actual === $expected;
    }

    private function canonicalJson(mixed $value): mixed
    {
        if (is_array($value)) {
            if (! array_is_list($value)) {
                ksort($value);
            }
            $value = array_map($this->canonicalJson(...), $value);
        }

        return $value;
    }
}
