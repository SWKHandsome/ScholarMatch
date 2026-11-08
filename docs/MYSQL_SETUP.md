# MySQL setup, data transfer and hosting preparation

MySQL is the default application database. Local development uses Laragon; the application remains in `D:\Github\ScholarMatch`. SQLite remains supported for isolated tests and as the preserved source of earlier installations.

## Local development

1. Install Laragon Full and start MySQL. The verified local installation is `D:\laragon`, with MySQL 8.4.3 and PHP 8.3.36.
2. Use Laragon Terminal, or a PHP CLI with `pdo_mysql` enabled. The transfer also requires `pdo_sqlite`.
3. Create a MySQL database named `scholarmatch`, using `utf8mb4` / `utf8mb4_unicode_ci`.
4. Copy `.env.example` for a new installation and configure `DB_CONNECTION=mysql`, host, port, database, username and password. Laragon's local defaults may use root with an empty password; hosting needs the credentials supplied for its own database.
5. New installations: `php artisan migrate --seed`. Existing-data transfers: follow the next section instead of seeding accounts.
6. Build assets with `npm run build`; start the application with `php artisan serve`. The existing project location does not need to move into Laragon's `www` folder.

If the server reports running on port 8000 but the browser times out, check for a previously started PHP server still holding that port. Stop the previous project server with Ctrl+C in its terminal and restart the intended command. Identify the project/process before stopping anything; do not stop MySQL or unrelated PHP applications. On this Windows device, an older Laragon PHP 8.3 ScholarMatch server held port 8000 without answering connections. Stopping its verified parent/child processes restored the original command on 8000; no firewall rule changes were needed.

For temporary diagnosis, the same application can also run on another port:

```powershell
& ".\.codex\toolchain\php\php.exe" artisan serve --host=127.0.0.1 --port=8001
```

Open the address matching the chosen port and keep the server terminal running. Keep MySQL running in Laragon. Align the local .env APP_URL with that address, clearing cached configuration if enabled. The current local URL was restored to http://127.0.0.1:8000 after diagnosis. The toolchain path is local and Git-ignored; other devices may use their own PHP executable. A port change can isolate a connectivity problem; it does not identify the cause by itself.

## Transfer an existing SQLite installation

Pause application writes while taking the final snapshot and switching connections. Preserve the original `.env`/APP_KEY and keep the SQLite file. Use a consistent SQLite `VACUUM INTO` backup rather than copying an actively written file.

Configure the new MySQL destination, clear cached configuration, then run:

```bash
php artisan config:clear
php artisan migrate --force
php artisan database:import-sqlite /absolute/path/to/backup.sqlite --dry-run
php artisan database:import-sqlite /absolute/path/to/backup.sqlite
```

The importer opens SQLite read-only and checks integrity, foreign keys and completed migration names. The MySQL destination must already have the matching schema and empty application tables. Its study_fields table may contain only untouched migration defaults; these unused defaults are replaced in the import transaction. Edited/custom destination fields are refused. It copies study-field settings and application tables in parent-first order, retaining IDs, labels/status, password hashes, profiles, academic results, scholarships, rules, routes, bookmarks, logs, password-reset tokens, sessions and queue data. It verifies counts/content before commit; errors roll back imported rows. `--force` skips the prompt, not the checks. MySQL may advance auto-increment counters after a rolled-back attempt; existing application records are never replaced.

The destination's migration history remains the one created by its own migrations. Recommendation cache entries/locks are rebuilt, not imported. JSON object key order, numeric column trailing zeros and midnight formatting of DATE columns are normalised only for verification. Named fields and string values remain intact. The source file is not changed. Unmigrated sources must be updated/backed up before transfer. For a failed preflight, correct the problem rather than running `migrate:fresh` on an existing application database.

## Refresh the fictional catalogue

```bash
php artisan scholarships:replace-catalog --dry-run
php artisan scholarships:replace-catalog
```

This now removes only the retired `demo-01`–`demo-70` keys, with their associated rules, bookmarks and logs. It seeds `demo-rich-01`–`demo-rich-20`. Other scholarships and their associated records remain. The new seeder does not reintroduce the old 70 demos. Running the new seeder again updates those 20 demo records by key without duplicating them.

The command saves a backup first under `storage/app/private/backups`: SQLite uses a consistent database snapshot; MySQL uses a verified JSON data snapshot of all tables. The JSON file contains data, not CREATE TABLE statements, so it requires the matching migrated schema to restore. For a standalone MySQL SQL backup use `mysqldump --single-transaction --no-tablespaces --set-gtid-purged=OFF --result-file=backup.sql scholarmatch` with the appropriate connection arguments. Keep backups private and outside Git.

## Database-specific tests

Default `php artisan test` uses isolated in-memory SQLite. Run the same suite against a separate MySQL database as well. **Never use the application database for tests**, because RefreshDatabase rebuilds test tables.

Create an empty `scholarmatch_test` database, then in PowerShell:

```powershell
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'mysql'
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_DATABASE = 'scholarmatch_test'
$env:DB_USERNAME = 'root'
$env:DB_PASSWORD = ''
$env:DB_URL = ''
php artisan test
```

The seven transfer/backup cases run only on MySQL. They verify full transfer, dry run, refusal of a populated destination, schema mismatch, transaction rollback, backup content and preservation/refusal cases for field settings. Close this test terminal afterward so its test-database environment overrides cannot affect normal commands.

## Azure preparation

Laragon is a local tool; it is not uploaded as the hosting server. GitHub stores/deploys the project, Azure runs the PHP application, and the configured MySQL service stores its records. Azure resource provisioning is a separate step: confirm the current student subscription/credit and choose the runtime, region and database service before deployment. No Azure resources have been created by this change.

On the host, supply database credentials and APP_KEY through its environment configuration, use the required TLS settings (`MYSQL_ATTR_SSL_CA` when a CA file is required), set production mode with debug disabled, deploy built assets, run pending migrations and cache configuration only after host settings are correct. Keep the existing APP_KEY when moving encrypted application data. Use the host's database backup/restore workflow for subsequent releases rather than reseeding accounts or rerunning the SQLite importer.
