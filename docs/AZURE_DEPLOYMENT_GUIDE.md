# ScholarMatch Azure deployment guide

Updated 8 November 2026. Azure resources and the MySQL data copy were created through the user's portal/terminal session. GitHub Actions run42447117581 successfully built/deployed commit75fd36a using OIDC. Live HTTPS GET checks now pass for the homepage, login, registration and /up. Authenticated student/admin functions, restart-readiness settings and actual mail delivery still require verification; public page responses alone are not full acceptance testing.

## 1. Check the existing student subscription

1. Sign in to [Azure Portal](https://portal.azure.com/) with the account that activated Azure for Students.
2. Search for Subscriptions, open Azure for Students and check its status.
3. Open [Azure Sponsorships balance](https://www.microsoftazuresponsorships.com/balance) with the same account. Record remaining credit and the displayed expiry/renewal information.
4. Establish how long the FYP site needs to stay online. Compare the app plan, database compute/storage/backups and any networking charges with that period before creating resources.

Activation at school entry does not establish the present balance. Microsoft describes a one-year USD100 student credit, renewable while eligible. [Student offer](https://learn.microsoft.com/en-us/azure/education-hub/about-azure-for-students), [checking remaining credit](https://learn.microsoft.com/en-us/azure/cost-management-billing/manage/azurestudents-subscription-disabled).

Share only subscription status, remaining credit and expiry for guidance; keep credentials, keys and database exports private.

Current planning checkpoint (7 November 2026): the original Education Hub screenshot showed USD100 available and expiry on 2 December 2026. The user subsequently reports successful renewal until December 2027. The exact new expiry day and post-renewal balance have not been inspected; do not assume credit was added to the old balance. The stated initial hosting need was through 2 December 2026. Resource availability and total cost remain unverified; renewal extends eligibility time, not a guarantee of free continuous app/database operation.

### If Sponsorships says there is no active sponsorship

An active subscription alone does not verify its student credit. Confirm the portal and Sponsorships site use the same subscription-owner account. If that account has both personal and work/school sign-in choices, verify which identity owns the subscription rather than assuming academic verification and subscription ownership are the same login.

In Azure Portal, search Education and open the Education Hub Overview. Microsoft documents this as another place to inspect student subscription usage and the renewal countdown. Check the subscription's actual offer/plan as well as its display name. Record what is shown; do not infer a USD100 balance from Active status or from zero current cost. [Education Hub usage guide](https://learn.microsoft.com/en-us/azure/education-hub/navigate-costs).

If no student benefit appears with the correct account/subscription, use Azure Help + support for account/billing investigation before selecting credit-funded resources. The sponsorship message alone does not establish whether the cause is account selection, the offer or benefit provisioning.

## 2. Select resources using the portal's actual availability and estimates

| Resource | ScholarMatch purpose / proposed choice |
| --- | --- |
| Resource group | One group, for example rg-scholarmatch-fyp, to organize this deployment. |
| App Service + Linux plan | Runs Laravel. The project requires PHP 8.3 or compatible newer PHP; begin with the tested PHP 8.3 runtime if available. |
| MySQL Flexible Server | Stores the existing application records. Prefer MySQL 8.4 to match local development when available. |
| GitHub repository / Actions | Stores and builds source, then deploys to App Service. |

Microsoft's PHP/MySQL tutorial uses a Basic Linux plan and offers a private database connection. Treat its tier as a starting point to price, not a promise of free hosting. Use a common supported region for app/database; Southeast Asia is a candidate only if the subscription offers the required resources there. Review database size/tier and all resources produced by a creation wizard. ScholarMatch already uses database-backed sessions/cache; Redis is not required for this initial deployment. [PHP/MySQL tutorial](https://learn.microsoft.com/en-us/azure/app-service/tutorial-php-mysql-app), [MySQL engine support](https://learn.microsoft.com/en-us/azure/mysql/flexible-server/overview).

### Completed resource checkpoint — 8 November 2026

- Southeast Asia was denied by the subscription's region policy. The allowed-location parameter listed Central India, Japan West, Korea Central, East Asia and Malaysia West.
- Created rg-scholarmatch-fyp; Linux/PHP 8.3 Web App scholarmatch-fyp-swk and Basic B1 plan asp-scholarmatch-fyp in Malaysia West. Portal app-plan estimate: USD12.41/month.
- Created MySQL Flexible Server scholarmatch-fyp-swk-server in Malaysia West, MySQL 8.4, Burstable B1ms (1 vCore, 2 GiB), 20 GiB storage, 360 pre-provisioned IOPS, 7-day local backups, storage autogrow on, HA/geo-redundancy off. Portal base estimate: USD18.82/month before eligible free allowances and additional usage. The green free-allowance labels are not independent verification of the remaining benefit.
- Public database connectivity uses one client-IP rule plus 31 individually allowed Web App outbound IPv4 addresses. The user waited for the asynchronous firewall update to succeed. Access from all Azure services and unrestricted internet ranges remain off; TLS remains enforced. Review the allowlist after app-plan/address changes. Remove the temporary laptop rule when imports/administration no longer need it.
- Created database scholarmatch, utf8mb4/utf8mb4_unicode_ci. A complete local MySQL dump was restored after a VERIFY_IDENTITY connection confirmed an empty destination and an encrypted TLS session.
- User-supplied Azure query results matched current local counts: 18 tables; academics4, income3, migrations21, recommendation logs46, saved scholarships0, qualification routes66, rules24, scholarships24, profiles4, study fields21, users7. This verifies table/selected row counts, not a byte-for-byte comparison of every value. The private backup and CA files remain under storage/app/private and are excluded from the release.
- User saved the 19 initial App Service setting names. Secret values were kept hidden. Their actual runtime values still need live validation.

## 3. Prepare the actual repository before enabling deployment

The following files now implement the local deployment preparation:

| File | Behaviour |
| --- | --- |
| .github/workflows/azure-deploy.yml | Push/PR to main and manual runs install lockfiles, run SQLite backend/JS tests, build Vite, reinstall production-only Composer dependencies and upload the release ZIP. Deployment stays skipped until AZURE_DEPLOY_ENABLED=true; then only successful main builds can deploy with OIDC. |
| deploy/azure/package.php | Creates a ZIP from allowed application directories, vendor and built assets. Refuses development dependencies, missing assets and output overwrites. Excludes .env files, local DB files/dumps, Git/toolchains, all existing storage contents, public/hot, public/storage and bootstrap caches. Adds empty runtime directories instead. |
| deploy/azure/nginx.conf | Uses /home/site/wwwroot/public, Laravel front-controller routing, dotfile denial and PHP execution limited to index.php. |
| deploy/azure/startup.sh | Creates runtime directories, caches the Azure configuration/routes/views, validates Nginx and reloads it. No key generation, database reset, restoration, seeding or automatic migration occurs on restart. |
| config/trustedproxy.php and bootstrap/app.php | TRUSTED_PROXIES is blank/untrusted by default; Azure can opt in with *. Accepts forwarded client IP, scheme and port; deliberately ignores forwarded host and prefix. |

The workflow uses PHP 8.3, Node24 and Linux runners. npm ci --ignore-scripts and npm run build produce assets; production Composer installation uses --no-dev and the existing lockfile. No live database credentials or APP_KEY are supplied to GitHub builds. Do not use composer setup for production: it generates a key and runs seeded migrations.

Review and push the intended source changes yourself. The first push can complete the build while the deploy job is skipped. The assistant does not commit or push automatically.

Sources: [Azure Nginx/startup configuration](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php), [GitHub/OIDC deployment](https://learn.microsoft.com/en-us/azure/app-service/deploy-github-actions), [Laravel trusted proxies](https://laravel.com/docs/13.x/requests#configuring-trusted-proxies).

## 4. Configure production settings and transfer data

Supply private values through App Service environment settings rather than uploading the local .env:

| Setting | Production value |
| --- | --- |
| APP_NAME | ScholarMatch |
| APP_ENV / APP_DEBUG | production / false |
| APP_URL | The exact HTTPS URL displayed by App Service. |
| APP_KEY | Preserve the current application key for the existing-data move; never print it in chat or rotate it on deployment. |
| DB_CONNECTION / DB_HOST / DB_PORT | mysql / actual Azure server hostname / 3306 |
| DB_DATABASE / DB_USERNAME / DB_PASSWORD | The destination database and its private credentials. |
| MYSQL_ATTR_SSL_CA | A valid trusted CA file path for the selected Azure MySQL TLS connection; verify it exists in the deployed runtime. |
| SESSION_DRIVER / CACHE_STORE | database / database; their tables must exist after restoration/migrations. |
| SESSION_SECURE_COOKIE | true, after HTTPS/proxy handling is correct. |
| TRUSTED_PROXIES | * on this Azure App Service managed ingress only; leave blank on direct localhost. |
| SCM_DO_BUILD_DURING_DEPLOYMENT | false; the workflow already builds vendor and public/build. |
| Mail settings | A working mail service if password-reset delivery is required. The current local log mailer does not deliver emails. |

Plan database access before restoration: a private server requires a restore client with access to its network. The laptop or a standard GitHub runner does not automatically have that access. Keep TLS enabled.

Back up the current local MySQL database during a write pause. Restore its complete schema/data/migration history into an empty dedicated Azure database, verify the content, then run only pending migrations with php artisan migrate --force. This preserves the manual scholarships, custom fields, income settings, student accounts and relationships. Do not seed or run migrate:fresh for this move. The earlier SQLite importer is not the tool for this MySQL-to-MySQL transfer. Capture fresh source counts at transfer time rather than assuming historical totals.

After the environment and data are correct, clear stale development configuration, build production caches and start/restart the app. Future releases deploy code and pending migrations without restoring/reseeding the database. Define worker/scheduler hosting only if the deployed app actually needs background jobs.

### Finish the portal settings

1. Add TRUSTED_PROXIES=* and SCM_DO_BUILD_DURING_DEPLOYMENT=false to the Web App's existing App settings, Apply and wait for success. Keep APP_DEBUG=false, the existing APP_KEY, HTTPS APP_URL and SESSION_SECURE_COOKIE=true.
2. Use MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt; this is the platform CA bundle, not the laptop's Windows certificate path. Validate the connection in the actual runtime.
3. Keep HTTPS Only enabled. Use normal extracted ZIP deployment; do not enable WEBSITE_RUN_FROM_PACKAGE because Laravel needs writable storage/bootstrap cache.
4. The workflow supplies startup command sh /home/site/wwwroot/deploy/azure/startup.sh. If setting it manually, use Web App → Configuration → General settings / Stack settings (portal layouts vary). The script becomes available after uploading the release.
5. Prefer a dedicated application MySQL user scoped to scholarmatch.* before opening the site to users. The current imported server administrator was used during setup. Supply application credentials only to App Service, never to the workflow or chat.
6. Add WEBSITE_WARMUP_PATH=/up and WEBSITE_WARMUP_STATUSES=200, Apply and let the restart finish. These settings are recommended following the first live restart diagnosis; their application/behaviour has not yet been verified. Azure's default warm-up endpoint can mark a container ready on a404before Laravel's Nginx reload completes. Requiring200from/up waits for Laravel routing/PHP to respond. This health route proves application boot/routing, not a complete authenticated database workflow. [Warm-up setting reference](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings#app-environment).

### Configure GitHub login without a generated duplicate workflow

The repository already has its own workflow. Use the manual OIDC path below; do not additionally enable Deployment Center's generated workflow, which would create another commit and competing deployment. The user subsequently completed the identity, role, federation and GitHub settings, and run42447117581 successfully authenticated and deployed.

1. In Azure Portal, create a user-assigned Managed Identity in the same resource group/allowed region, for example id-scholarmatch-github.
2. On this Web App → Access control (IAM), assign Website Contributor to that managed identity, scoped to this Web App only.
3. On the identity → Federated credentials → Add credential, choose GitHub Actions deploying Azure resources. Enter the exact GitHub organization/owner and repository (SWKHandsome / ScholarMatch if unchanged), entity Branch, branch main. Audience is api://AzureADTokenExchange. This trusts the subject repo:SWKHandsome/ScholarMatch:ref:refs/heads/main; change owner/repo if your actual repository differs. Do not choose a GitHub environment subject for this workflow, which uses branch federation.

   Portal clarification verified 8 November 2026: the newer GitHub scenario displays required Organization ID and Repository ID inputs. GitHub's public API returned owner206743059 and repository1282586991 for SWKHandsome/ScholarMatch. Its OIDC customization endpoint returned use_default=true and use_immutable_subject=false, so the current subject is name-based. Editing that subject cleared the numeric inputs, and switching the existing credential's editor to Other issuer produced a scenario-format warning/disabled Update. The user instead saved the GitHub scenario with empty ID inputs; reopening confirmed issuer https://token.actions.githubusercontent.com, subject repo:SWKHandsome/ScholarMatch:ref:refs/heads/main, audience api://AzureADTokenExchange and name github-main. Those persisted fields subsequently worked in the actual OIDC deployment. Do not repeatedly refill the ID fields or change the scenario of an already-correct saved credential. For a fresh manual credential, Other issuer with Explicit subject identifier is documented; the API stores issuer/audiences/subject rather than separate owner/repository IDs. If opting into immutable subjects later, first create a matching credential for repo:SWKHandsome@206743059/ScholarMatch@1282586991:ref:refs/heads/main, then change GitHub's OIDC setting and verify login. Newer/renamed/transferred repositories can use the immutable format automatically; check actual configuration. Sources: [Manual Other issuer scenario](https://learn.microsoft.com/en-us/azure/active-directory/workload-identities/workload-identity-federation-create-trust-user-assigned-managed-identity#other), [GitHub subject formats](https://docs.github.com/en/actions/reference/security/oidc#immutable-subject-claims), [Credential API fields](https://learn.microsoft.com/en-us/azure/templates/microsoft.managedidentity/userassignedidentities/federatedidentitycredentials).
4. In GitHub → repository Settings → Secrets and variables → Actions → Secrets, add AZURE_CLIENT_ID (the managed identity's client ID), AZURE_TENANT_ID (directory/tenant ID) and AZURE_SUBSCRIPTION_ID. These are identity identifiers, not a publish profile or database password. Never create a client secret for this OIDC path.

   If Microsoft Entra ID Overview returns insufficient privileges, obtain tenantId from the managed identity Overview → JSON View → properties.tenantId instead. The user encountered this directory-access restriction; it did not prevent successful OIDC deployment. Use properties.clientId for AZURE_CLIENT_ID, not principalId. [Managed identity response fields](https://learn.microsoft.com/en-us/rest/api/managedidentity/user-assigned-identities/get?view=rest-managedidentity-2024-11-30).
5. Keep the repository variable AZURE_DEPLOY_ENABLED absent/false until the source build, App Service environment and identity/role/federation are ready.
6. Then add the Actions repository variable AZURE_DEPLOY_ENABLED with value true. Run Build and deploy ScholarMatch manually from main (changing a variable alone does not start a run). Subsequent main pushes deploy automatically. Pull requests build only and have no Azure token permission.
7. Inspect both build/deploy jobs. The deployment action sets the startup command and uploads only the prepared ZIP. Basic/publish-profile authentication stays disabled. If login fails, check branch subject, tenant, client ID and role scope; keep database credentials private.
8. In App Service SSH, use php artisan migrate:status, then php artisan migrate --force only for genuinely pending migrations. This patch adds no schema changes. Do not restore again or run seeds/migrate:fresh; the 21-row migration history is already imported.

Optional local packaging check (from an isolated production dependency installation, not the developer's dev vendor): php deploy/azure/package.php --output=/new/path/scholarmatch.zip. The PHP CLI needs its ZIP extension. Deployment failure should be diagnosed in GitHub Actions plus App Service startup/application logs; build success alone does not prove a live Azure release.

## Post-deployment route errors

For each update, confirm the newest main commit has both successful build and deploy jobs, then allow App Service startup to finish and check the live application. Ctrl+F5 refreshes browser resources; it does not rebuild Laravel's route cache or restart PHP.

Observed on 8 November 2026 after ab0af7c: the updated admin dashboard referenced admin.students.index while a serving process could not resolve that route. Both errors in the supplied log preceded the new PHP runtime's readiness at 11:50:57 UTC. The route exists in the committed source and is included in the ZIP. A temporary mismatch between updated views and an old route collection/process is the supported explanation; the exact server cache was not inspected. The user clicked Web App Overview → Restart and confirmed recovery. Subsequent read-only /up and /login checks returned 200.

If this specific error remains after deployment/startup finishes, restart the Web App once, wait for readiness and retry. If it persists or repeats, use App Service SSH to run `cd /home/site/wwwroot` and `php artisan route:list --name=admin.students`, and inspect fresh application logs and the deployed commit before making further changes. A missing route in that output warrants checking the deployed routes/web.php and cache; do not diagnose it as a database failure or reimport data. Laravel documents rebuilding route caches when routes change. [Laravel routing cache](https://laravel.com/framework/docs/13.x/routing#route-caching).

The later log shows Azure probing /up during startup; its configured accepted-status values were not independently verified. The earlier warm-up guidance remains relevant but does not prove every authenticated route uses the correct release.

## 5. Verify and collect FYP evidence

Check /up, HTTPS pages/assets, login/logout, onboarding, existing academic/profile records, recommendation decisions and counts, saved scholarships, admin rules/Matching Settings and password-reset email. Compare transferred rows/settings with the backup, inspect application/deployment logs and retest after an App Service restart. Confirm the site works while Laragon is stopped.

Keep the final resource choices, region, costs, deployed URL, workflow outcome, data-transfer receipt and screenshots for the report. Mark this as a proposed deployment until those checks pass. Keep the local backup for recovery; code redeployment alone does not roll back database changes.
