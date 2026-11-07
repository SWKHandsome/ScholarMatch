# Scholarship catalogue

For another device, see [fresh installation and existing-70-demo upgrade commands](ANOTHER_DEVICE_SETUP.md).

The current seed is **20 richer fictional scholarships**, version `scholarmatch-rich-demo-20-v1`, defined in [demo-scholarships.json](../database/data/demo-scholarships.json). See [all scenarios](DEMO_SCHOLARSHIPS.md). Manually entered scholarships coexist with these records and are not overwritten by demo seeding.

Providers, benefits, dates and eligibility thresholds are invented examples for the FYP. Names and descriptions identify fiction; source URLs/check dates are empty. Application URLs use reserved `scholarmatch.example` placeholders. Fixed 2099 dates are synthetic test data. Named subjects, offer letters, portfolios, interviews, renewal and loan/bond terms go in Special Requirements and do not become automatic checks.

For a fresh database, run `php artisan migrate --seed`. To upsert the current demo awards/rules without changing users, run `php artisan db:seed --class=ScholarshipCatalogSeeder`. Demo keys are `demo-rich-01` through `demo-rich-20`. Reseeding preserves scholarship IDs and bookmarks, restores current demo rules and does not reintroduce the retired 70-entry dataset.

To remove the old `demo-01` through `demo-70` catalogue:

```bash
php artisan scholarships:replace-catalog --dry-run
php artisan scholarships:replace-catalog
```

The command validates the new catalogue, creates a backup, deletes only the exact retired demo keys and their associated rules/bookmarks/logs, then seeds the current 20. Other scholarships, their related records and all student accounts/profiles/results remain. Repeating the command preserves manual awards and does not duplicate the current demos. This targeted behaviour supersedes older documentation describing removal of every scholarship.

MySQL and file-backed SQLite backups are supported. See [MySQL setup](MYSQL_SETUP.md) for transfer, backup formats and separate database testing. Historical real-world source material is retained in REAL_SCHOLARSHIP_CATALOG_ARCHIVE.md and is not a statement of current scholarship availability.
