# Default scholarship catalogue

The current default is **70 fictional demo scholarships**, defined in [demo-scholarships.json](../database/data/demo-scholarships.json). Version: scholarmatch-demo-70-v2.

See [the 70-award scenario guide](DEMO_SCHOLARSHIPS.md) for every rule, useful demo profiles, exact boundary examples and instructions for sharing the same initial data across devices. All academic conditions use fields supported by the existing matcher; the cancelled academic-audit changes are not required.

All 70 records are active and open. Sixty fixed synthetic dates use 2099; nine awards are rolling and one automatic. Complete profiles receive 70 unfiltered evaluations. The sample names, providers and dates are fictional. No official verification or real application links are presented.

Demo 51–60 add ten complex Diploma-funded cases; Demo 61–70 add ten Degree-funded cases (the app calls Degree **Undergraduate**). They combine programme funding with entry/continuing qualification routes, grades, current year, income, nationality, fields and institutions. On a device already using the original 50 demos, run `php artisan db:seed --class=ScholarshipCatalogSeeder` once to add the new awards without deleting bookmarks/history.

Fresh device:

```bash
php artisan migrate --seed
npm run build
```

Existing SQLite installation changing to this catalogue:

```bash
php artisan migrate
php artisan scholarships:replace-catalog
npm run build
```

Replacement first creates a full SQLite backup, then removes old scholarships and their related rules, saved links and recommendation logs. Student accounts, profiles and academic results are preserved. This is the path to exactly 70 demo records on an existing device.

Ordinary `php artisan db:seed --class=ScholarshipCatalogSeeder` upserts the same demo keys without duplicates and preserves custom records/history. It does not delete older real-world rows, so use replacement once when switching an existing database. Refreshing the website alone does not seed a different device.

The earlier real-world JSON remains at [malaysia-scholarships.json](../database/data/malaysia-scholarships.json), with its [archived source register](REAL_SCHOLARSHIP_CATALOG_ARCHIVE.md). It is retained for reference and is not used by the default seeders.
