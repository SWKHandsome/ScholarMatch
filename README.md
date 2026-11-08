# ScholarMatch

**ScholarMatch** is a web-based scholarship recommendation platform for Malaysian students. It matches a student's academic results and socioeconomic profile with scholarship eligibility rules, then provides transparent recommendations with clear explanations.

Built as a Final Year Project (FYP), ScholarMatch focuses on making scholarship discovery more accessible, especially for students who need financial support but find eligibility criteria difficult to compare across multiple sources.

## Key features

- Student registration/login with a Register | Login switch, password visibility controls, recovery return links and role-based access control
- Guided Student Profile → Academic Result setup for new/incomplete students, with saved-state resume and shortened territory names
- Numeric shortfall sentences that refine existing requirement explanations
- Administrator rule-testing preview for unsaved requirements, with synthetic qualification/profile samples and A-Level grade presets
- Student profile management for nationality, state, household income, dependents and income category
- Grouped qualification selection, official grade-count counters for SPM, IGCSE, O-Level, A-Level and Senior UEC, STAM pangkat and existing CGPA-based results
- Programme Seeking Funding section in Academic Result groups funding level, institution type and field of study
- Administrator-configured income classification, live Profile feedback/reference ranges and automatic saved-category synchronization after admin changes
- Scholarship, scholarship-rule, and income-threshold management for administrators
- Matching Settings groups income ranges and editable fields of study on one page, with linked renames and inactive-field preservation
- Repeatable qualification boxes on Add/Edit Scholarship, with separate results, study status and year limits for each accepted route
- Explainable **Eligible / Not Eligible** decisions, with **!** for administrator-entered special requirements
- **Haven't decided** programme field/institution choices: restricted undecided criteria are shown as **Not checked**; Eligible means all checked automatic requirements pass
- Recommendation filters for name/provider, award type, funded level, field and institution, with full-width cards and 10 results per page
- Saved scholarships for students
- Recommendation logs for administrators
- Catalogue of 20 richer fictional scholarships with benefits, alternative qualifications and provider checks, alongside administrator-entered awards

## How recommendations work

The [administrator rule preview](docs/ELIGIBILITY_PLANNING.md) reuses the existing rule engine to test unsaved requirements against a synthetic student without creating records. Student comparisons retain their existing decisions, with small numeric shortfall explanations where useful. Run the preview request regressions with `npm run test:planning` alongside the academic editor tests.

ScholarMatch checks every applicable scholarship requirement against the student's official academic results and profile. There are no points, weights or percentage match scores.

| Display | Meaning |
| --- | --- |
| Eligible | Every applicable automatic requirement is met. |
| Eligible ! | Automatic requirements are met, with special conditions entered by the administrator that must be verified with the provider. Expand ! to read them. |
| Not Eligible | At least one applicable requirement is not met. |

Students select an education category and latest qualification, then enter official grade counts, STAM overall pangkat or CGPA. IGCSE supports letters and 9–1 separately. SPM As/credits are calculated automatically. SPM, IGCSE, O-Level, A-Level, UEC and STAM require completed exams and final results. Completed STPM identifies Pengajian Am within four/five final subject results and automatically calculates overall CGPA, with confirmation against the official slip. Studying STPM keeps manual official CGPA and current year; other CGPA qualifications retain cumulative/final CGPA. The programme seeking funding remains separate. Named-subject scholarship conditions go in Special Requirements; Pengajian Am identification is used only for the completed-STPM overall calculation. See [academic qualifications](docs/ACADEMIC_QUALIFICATIONS.md) for formats, examples and matching rules.

Institution type and field of study are required on Academic Result beside the funding level. They describe the programme seeking funding. Profile contains personal/household information. Existing programme values are transferred during migration; a profile without an academic record prefills those choices when the student first enters official results.

Nationality choices are Malaysian and Non-Malaysian for students and scholarship restrictions. An empty scholarship restriction accepts either category. Country-specific conditions belong in Special Requirements for provider verification.

Requirements include nationality, funded level, accepted latest qualifications, study status/year, income, academics, field and institution. Within the student's qualification route, every configured minimum must pass. Empty criteria are not applicable. Results include each requirement, student value and outcome.

Administrators add alternative qualification boxes directly on Add/Edit Scholarship or the Rules page. Each box has its own level, status and optional current-year boundaries. Add Another Requirement opens a Requirement Type selector for supported CGPA, grade-count, rank, average or total-point rules, with a live explanation. Grade thresholds share one selector and multiple rows added with Add Grade Row, with a combined minimum-pattern preview. STPM overall CGPA stays separate from its best-subject average; UEC can compare a best-five total directly against 11. See [requirement types](docs/ACADEMIC_REQUIREMENT_TYPES.md) for examples. Students must pass any one complete box plus the general requirements. For example, Completed Diploma with CGPA >= 3.00 OR Currently Studying Undergraduate with CGPA >= 3.30 in Years 1–3. Empty minimums/boundaries are unrestricted; no boxes means no academic restriction. The student still needs official results. Qualification level/status have required * markers; irrelevant result/year controls are hidden, disabled and cleared.

Only active scholarships with configured rules and an unexpired or unknown deadline are evaluated. Eligible (including !) and Not Eligible results are grouped separately and ordered by nearest recorded deadline, with unknown dates last. Unknown dates require provider verification. Special requirements do not override a failed automatic condition. Providers make the final award decisions.

## Updating an existing installation or device

To get the same 20 fictional scholarships on another device, follow [the fresh/existing device setup guide](docs/ANOTHER_DEVICE_SETUP.md). The checked-in JSON is shared through Git; actual database rows and local `.env` settings are not.

MySQL is the default development and deployment database. Use Laragon locally, start MySQL, create a database named `scholarmatch`, and configure `.env` using `.env.example`. Pulling newer project files does not update an existing database automatically. Run pending migrations and rebuild assets:

```bash
php artisan migrate
npm run build
```

`php artisan migrate` updates the database schema; it does not populate seed data. New databases should be initialized with `php artisan migrate --seed` as shown under Local installation. If an existing device database is missing the default B40/M40/T20 rows, restore only those rows with:

```bash
php artisan db:seed --class=IncomeCategorySeeder
```

Existing profiles and scholarships are preserved. Existing students must confirm their study status and selected funding level, and supply official results, before generating new decisions. Old evaluation logs remain identified as Previous Evaluation until refreshed; their historical score data is preserved but not treated as a current decision.

For existing SQLite installations, follow [MySQL setup and transfer](docs/MYSQL_SETUP.md). The verified importer preserves IDs, password hashes and relationships, refuses a populated destination, and leaves SQLite unchanged. SQLite remains available for fast isolated tests.

The qualification-route migration converts existing rules into equivalent child rows and retains the original columns. The grade-count migration adds result breakdowns and qualification-specific route settings while preserving records; the follow-up subject-point migration adds nullable STPM-average and UEC-total settings. Legacy SPM students must enter their grade breakdown; old As/credits are retained for reference. Legacy STPM students also need actual subject counts alongside their preserved official CGPA. Other valid CGPA records remain usable. Rebuild assets for the new editors. The STPM calculator migration adds a nullable PA grade and confirmation flag, preserving existing CGPA values and grades. Existing usable manual records stay valid until edited; completed STPM saves then use the new calculation. Earlier evaluations refresh under decisions-v9-undecided-programme. Legacy unsupported nationalities still need correction.

## Technology stack

- PHP 8.3+
- Laravel 13
- MySQL
- Blade templates
- Tailwind CSS
- Vite
- Pest PHP for automated tests

## User roles

### Student

- Maintain a personal and academic profile
- View explainable scholarship recommendations
- Review scholarship details
- Save and remove scholarships

### Administrator

- Manage scholarship listings and active status
- Create and update eligibility rules
- Manage income categories and thresholds
- Review generated recommendation logs

## Local installation

### Prerequisites

- PHP 8.3 or later
- Composer
- Node.js and npm
- MySQL

### Setup

```bash
git clone <your-repository-url>
cd ScholarMatch

composer install
npm install

copy .env.example .env
php artisan key:generate
```

Start MySQL in Laragon and create the empty `scholarmatch` database. Configure database credentials in `.env`, then migrate and seed a **new installation**:

```bash
php artisan migrate --seed
```

This installs **20 richer fictional demo scholarships** from `database/data/demo-scholarships.json`. Devices using this revision receive the same initial records and rules. `composer setup` also includes seeding and is intended for new local installations. The earlier catalogues are historical and are not seeded.

For an existing database, update only the scholarship catalogue without running the account seeders:

```bash
php artisan migrate
php artisan db:seed --class=ScholarshipCatalogSeeder
```

Re-seeding restores the 20 checked-in demos and their rules without duplicating scholarships. Manual scholarships remain. To remove the retired `demo-01` through `demo-70` catalogue, first run `php artisan scholarships:replace-catalog --dry-run`, then run it without `--dry-run`. The command backs up MySQL or file-backed SQLite, deletes only those retired demo keys and their related rules/bookmarks/logs, and seeds the new catalogue. Manual awards and their related records remain. See the [catalogue guide](docs/SCHOLARSHIP_CATALOG.md).

All 20 demos are active, with rolling/intake/automatic timing or clearly synthetic future dates in 2099. They cover all supported qualifications and six award types. Demo entries have no official-source claims. Presentation Apply links use reserved placeholder URLs. Interviews, named subjects and other provider checks demonstrate Eligible !; configured academic thresholds are checked automatically.

Build the frontend assets and start the application:

```bash
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

For frontend development, run the Vite development server in another terminal:

```bash
npm run dev
```

## Seeded accounts

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@scholarmatch.test` | `password` |
| Student | `student@scholarmatch.test` | `password` |

These accounts are intended for local development and demonstration only. Change credentials before any non-local deployment.

## Testing

Run the automated test suite with:

```bash
php artisan test
npm run test:academic
```

The PHP suite covers requirement decisions, official-result validation, qualification/funding separation, warning priority, thresholds, deadlines, caches, legacy migration, and student/admin access control. The Node suite covers grade counters, format changes and administrator editor state without adding a test dependency.

## Project structure

```text
app/
├── Http/Controllers/       # Student, admin, authentication, and profile controllers
├── Models/                 # Eloquent models and relationships
└── Services/
    └── ScholarshipRecommendationService.php

database/
├── migrations/             # Database schema
├── data/                   # Fixed 70-award fictional demo catalogue
└── seeders/                # Demo accounts, categories, and catalogue import

resources/views/            # Blade pages, layouts, and reusable components
tests/                      # Pest unit and feature tests
```

## Documentation

- [Product Requirements Document](PRD.md)
- [Architecture Document](ARCHITECTURE.md)
- [MySQL / Laragon Setup and Data Transfer](docs/MYSQL_SETUP.md)
- [Azure Hosting Concept and Infographic](docs/AZURE_HOSTING_CONCEPT.md)
- [Azure Deployment Guide](docs/AZURE_DEPLOYMENT_GUIDE.md)
- [Matching Settings and Fields of Study](docs/MATCHING_SETTINGS.md)
- [Design System](DESIGN.md)
- [Manual Test Cases](MANUAL_TEST_CASES.md)
- [Full System Regression Report — 8 November 2026](docs/FULL_SYSTEM_TEST_REPORT_2026_11_08.md)
- [Academic Qualifications, Grade Formats and Matching Examples](docs/ACADEMIC_QUALIFICATIONS.md)
- [Academic Requirement Types and STPM/UEC Calculations](docs/ACADEMIC_REQUIREMENT_TYPES.md)
- [Completed STPM CGPA Calculation](docs/STPM_CGPA_CALCULATION.md)
- [Improvement Log and FYP Documentation Changes](improve.md)

## License

This project was developed for academic purposes as a Final Year Project.

## Fictional demonstration dataset

The initial catalogue contains 20 richer fictional awards with benefit descriptions, qualification alternatives and combined academic/household/programme restrictions. All 11 supported qualifications and six award types are represented. Eligible ! identifies provider conditions; named-subject requirements remain manual checks.

See [the scenario guide](docs/DEMO_SCHOLARSHIPS.md). New installations use `php artisan migrate --seed`. Existing installations use the backed-up targeted replacement command to remove only the old 70 demo keys and add the new 20, preserving manual scholarships and student records. Apply links are presentation placeholders.
