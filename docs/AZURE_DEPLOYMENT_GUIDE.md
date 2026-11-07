# ScholarMatch Azure deployment guide

Prepared 7 November 2026. This is a guided deployment plan, not evidence that ScholarMatch is hosted. Subscription details, region availability and prices must be confirmed in the user's Azure account.

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

Next portal step: App Services → Create → Web App. On Basics, select the student subscription, a new rg-scholarmatch-fyp resource group, a unique app name, Code publishing, PHP 8.3 if available, Linux and an available nearby region. Inspect Basic B1 pricing as the initial app-plan candidate. Review the selected region/tier and displayed price before final resource creation; database/network pricing is additional. These are proposed form values, not already created resources.

## 3. Prepare the actual repository before enabling deployment

These items remain deployment work; they have not been implemented by this guide:

- Review and push the intended source changes. Exclude .env, SQL backups, private storage exports and local toolchains from deployment artifacts.
- Configure a Linux build using the lockfiles: production Composer dependencies and npm ci followed by npm run build. Ship vendor and public/build with the application. Do not use composer setup in production: it generates a key and runs seeded migrations.
- Configure persistent Nginx startup behaviour to serve /home/site/wwwroot/public with Laravel front-controller routing. The repository root must not become the public document root. [PHP configuration](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php).
- Configure trusted proxy/HTTPS handling for the actual Azure ingress, then check generated links, redirects and secure session cookies.
- Connect GitHub deployment to the selected app, preferably with OpenID Connect. Review the generated workflow: enabling Deployment Center can write a workflow to GitHub, and the generated PHP build still needs ScholarMatch's frontend build. [GitHub Actions deployment](https://learn.microsoft.com/en-us/azure/app-service/deploy-github-actions).

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
| Mail settings | A working mail service if password-reset delivery is required. The current local log mailer does not deliver emails. |

Plan database access before restoration: a private server requires a restore client with access to its network. The laptop or a standard GitHub runner does not automatically have that access. Keep TLS enabled.

Back up the current local MySQL database during a write pause. Restore its complete schema/data/migration history into an empty dedicated Azure database, verify the content, then run only pending migrations with php artisan migrate --force. This preserves the manual scholarships, custom fields, income settings, student accounts and relationships. Do not seed or run migrate:fresh for this move. The earlier SQLite importer is not the tool for this MySQL-to-MySQL transfer. Capture fresh source counts at transfer time rather than assuming historical totals.

After the environment and data are correct, clear stale development configuration, build production caches and start/restart the app. Future releases deploy code and pending migrations without restoring/reseeding the database. Define worker/scheduler hosting only if the deployed app actually needs background jobs.

## 5. Verify and collect FYP evidence

Check /up, HTTPS pages/assets, login/logout, onboarding, existing academic/profile records, recommendation decisions and counts, saved scholarships, admin rules/Matching Settings and password-reset email. Compare transferred rows/settings with the backup, inspect application/deployment logs and retest after an App Service restart. Confirm the site works while Laragon is stopped.

Keep the final resource choices, region, costs, deployed URL, workflow outcome, data-transfer receipt and screenshots for the report. Mark this as a proposed deployment until those checks pass. Keep the local backup for recovery; code redeployment alone does not roll back database changes.
