# Use the same 20 demo scholarships on another device

The shared dataset is `database/data/demo-scholarships.json`, version `scholarmatch-rich-demo-20-v1`. DatabaseSeeder calls ScholarshipCatalogSeeder, which reads this file for all 20 awards, their rules and 52 qualification routes. There is no separate device-specific copy to maintain. The current seed does not recreate the old 70 awards.

Matching Settings also requires the study_fields migration. Running pending migrations registers the default field choices without resetting existing records. StudyFieldSeeder preserves administrator renames/status, and demo rules follow renamed default fields. Custom field settings are database records and require data transfer or configuration on the other device; Git shares the initial defaults and feature code. See [Matching Settings](MATCHING_SETTINGS.md).

First commit and push the source changes from the original device, including the updated dataset, seeders, commands and new source files. Git does not share the running database, local `.env`, passwords or generated frontend assets. The 3 manually entered scholarships on the original device are not in this fictional seed file; a fresh device receives 20 demos rather than the original device's 23 total records.

## Fresh installation / empty MySQL database

Install/start Laragon MySQL and open Laragon Terminal. Clone/pull the updated repository and enter its project folder. Then run:

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `scholarmatch` and set that device's connection credentials in its `.env`. Only copy `.env.example` and generate APP_KEY for a new installation; preserve the existing `.env`/APP_KEY when updating a device that already has data.

```powershell
php artisan config:clear
php artisan migrate --seed
npm run build
php artisan serve
```

Expected catalogue: **20 demo scholarships, 20 rules and 52 qualification routes**. Full fresh seeding also provides the default income ranges and local demonstration accounts. Students complete Profile and Academic Result before recommendations.

## Existing installation with the old 70 demos

Keep its `.env`, APP_KEY, existing accounts and administrator income settings. Start the configured database, pull the updated code, and run from the project folder:

```powershell
git pull
composer install
npm ci
php artisan config:clear
php artisan migrate
php artisan scholarships:replace-catalog --dry-run
php artisan scholarships:replace-catalog
npm run build
```

The dry run reports the retired demo count. The actual command backs up first, removes only existing `demo-01` through `demo-70` records and their related rules/bookmarks/logs, then seeds `demo-rich-01` through `demo-rich-20`. Manually entered scholarships, their related records, student accounts/profiles/results and income ranges remain. Restart an already-running Laravel server and refresh the browser.

Use the named catalogue command for this upgrade rather than the full DatabaseSeeder, which also seeds demonstration accounts and default income settings. Do not use `migrate:fresh` on an existing database.

If the older device still uses SQLite, its data is not transferred by simply changing `.env` to MySQL. Follow [the MySQL transfer guide](MYSQL_SETUP.md) first. The catalogue replacement command itself supports file-backed SQLite as well as MySQL.

## Repeat seeding / later dataset updates

Once the retired catalogue has been removed, this command upserts the current 20 demos and rules without duplicate scholarship rows or lost bookmarks:

```powershell
php artisan db:seed --class=ScholarshipCatalogSeeder
```

It restores those demo records from the shared file and preserves manual awards. It deliberately does not delete unrelated awards or remove retired keys; use the backed-up replacement command for the one-time old-catalogue cleanup.

See [all 20 scenarios](DEMO_SCHOLARSHIPS.md) and [catalogue behaviour](SCHOLARSHIP_CATALOG.md). Repeated fresh/catalogue seeding is covered by ScholarshipCatalogTest on isolated SQLite and MySQL databases; the original device's actual records are not reseeded during these checks.
