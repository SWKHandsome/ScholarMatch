<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** A deterministic fictional catalogue, shared by every demo installation. */
class ScholarshipCatalog
{
    public const EXPECTED_COUNT = 70;

    public static function entries(): array
    {
        return json_decode(file_get_contents(database_path('data/demo-scholarships.json')), true, 512, JSON_THROW_ON_ERROR)['scholarships'];
    }

    /** Validate before a destructive replacement, including dates on the day it runs. */
    public static function validateReplacement(array $entries, string $asOf): void
    {
        if (count($entries) !== self::EXPECTED_COUNT || count(array_unique(array_column($entries, 'catalog_key'))) !== self::EXPECTED_COUNT
            || count(array_unique(array_column($entries, 'name'))) !== self::EXPECTED_COUNT) {
            throw new InvalidArgumentException('The replacement must contain exactly '.self::EXPECTED_COUNT.' distinct awards.');
        }

        foreach ($entries as $entry) {
            $type = $entry['deadline_type'] ?? 'unconfirmed';
            $demo = str_starts_with($entry['catalog_key'], 'demo-');
            if ($demo && (! str_starts_with($entry['name'], 'Demo ')
                || ! str_starts_with($entry['description'], 'Fictional demonstration award')
                || ! empty($entry['source_url']) || ! empty($entry['source_checked_at'])
                || parse_url($entry['application_link'], PHP_URL_HOST) !== 'scholarmatch.example')) {
                throw new InvalidArgumentException('Demo awards must be clearly fictional and use a reserved application placeholder.');
            }
            if (($entry['study_country'] ?? null) !== 'Malaysia' || ! ($entry['is_active'] ?? false)
                || ! isset(Scholarship::DEADLINE_TYPES[$type]) || $type === 'unconfirmed'
                || (! $demo && empty($entry['source_url'])) || empty($entry['application_notes'])
                || ($entry['deadline'] !== null && $entry['deadline'] <= $asOf)
                || ($entry['deadline'] === null && ! in_array($type, ['rolling', 'intake', 'automatic'], true))) {
                throw new InvalidArgumentException('Review the application window before replacement: '.$entry['name']);
            }
        }
    }

    public static function replace(): void
    {
        self::validateReplacement(self::entries(), today()->toDateString());

        DB::transaction(function () {
            // Foreign keys remove only the deleted scholarships' rules, saved links and logs.
            Scholarship::query()->delete();
            Artisan::call('db:seed', ['--class' => ScholarshipCatalogSeeder::class, '--force' => true]);
            if (Scholarship::count() !== self::EXPECTED_COUNT) {
                throw new InvalidArgumentException('Catalogue replacement did not produce exactly '.self::EXPECTED_COUNT.' records.');
            }
        });
    }

    public static function retireUnmodifiedDemoRecords(): void
    {
        $fingerprints = json_decode(file_get_contents(database_path('data/legacy-demo-fingerprints.json')), true, 512, JSON_THROW_ON_ERROR);

        // Keep IDs, bookmarks and history; retire only exact matches of known demo metadata.
        Scholarship::whereNull('catalog_key')->where('is_active', true)
            ->whereIn('name', array_keys($fingerprints))->get()->each(function (Scholarship $scholarship) use ($fingerprints) {
                $fingerprint = hash('sha256', json_encode([
                    $scholarship->provider, $scholarship->description, $scholarship->award_type,
                    $scholarship->deadline?->toDateString(), $scholarship->application_link,
                ], JSON_THROW_ON_ERROR));

                if (hash_equals($fingerprints[$scholarship->name], $fingerprint) && ! $scholarship->special_requirements) {
                    $scholarship->update(['is_active' => false]);
                }
            });
    }
}
