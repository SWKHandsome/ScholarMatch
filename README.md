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
- Automatic B40, M40, and T20 income-category classification
- Scholarship, scholarship-rule, and income-threshold management for administrators
- Repeatable qualification boxes on Add/Edit Scholarship, with separate results, study status and year limits for each accepted route
- Explainable **Eligible / Not Eligible** decisions, with **!** for administrator-entered special requirements
- Recommendation filters for name/provider, award type, funded level, field and institution, with full-width cards and 10 results per page
- Saved scholarships for students
- Recommendation logs for administrators
- Fixed catalogue of 50 clearly fictional demo scholarships covering supported matching scenarios, plus local test accounts

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

The SQLite database file is local to each device and is excluded from Git. Pulling or copying newer project files does not update that device's database schema. Whenever you bring newer code onto a device, run its pending migrations from the project folder before using the app, then rebuild the frontend assets:

```bash
php artisan migrate
npm run build
```

`php artisan migrate` updates the database schema; it does not populate seed data. New databases should be initialized with `php artisan migrate --seed` as shown under Local installation. If an existing device database is missing the default B40/M40/T20 rows, restore only those rows with:

```bash
php artisan db:seed --class=IncomeCategorySeeder
```

Existing profiles and scholarships are preserved. Existing students must confirm their study status and selected funding level, and supply official results, before generating new decisions. Old evaluation logs remain identified as Previous Evaluation until refreshed; their historical score data is preserved but not treated as a current decision.

The qualification-route migration converts existing rules into equivalent child rows and retains the original columns. The grade-count migration adds result breakdowns and qualification-specific route settings while preserving records; the follow-up subject-point migration adds nullable STPM-average and UEC-total settings. Legacy SPM students must enter their grade breakdown; old As/credits are retained for reference. Legacy STPM students also need actual subject counts alongside their preserved official CGPA. Other valid CGPA records remain usable. Rebuild assets for the new editors. The STPM calculator migration adds a nullable PA grade and confirmation flag, preserving existing CGPA values and grades. Existing usable manual records stay valid until edited; completed STPM saves then use the new calculation. Earlier evaluations refresh under decisions-v8-stpm-cgpa. Legacy unsupported nationalities still need correction.

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

Configure the database credentials in `.env`, then migrate and seed the application:

```bash
php artisan migrate --seed
```

This installs **70 fictional demo scholarships** from `database/data/demo-scholarships.json`. Devices using this repository revision receive the same initial records and rules. `composer setup` also includes seeding. The earlier real-world catalogue is archived and is not seeded.

For an existing database, update only the scholarship catalogue without running the account seeders:

```bash
php artisan migrate
php artisan db:seed --class=ScholarshipCatalogSeeder
```

Re-seeding restores the checked-in demo catalogue and updates its rules without duplicates. Other custom scholarships, bookmarks and history remain; unchanged legacy demo metadata is deactivated. Local edits to these 70 catalogue records are overwritten when re-seeded. See the [demo catalogue guide](docs/SCHOLARSHIP_CATALOG.md). To switch an existing real-world database to exactly 70 demos, run `php artisan scholarships:replace-catalog` after migrating. It removes old scholarships, saved links and recommendation history after creating a full SQLite backup; users, profiles and academic results remain.

All 70 demos are active and open: 60 use synthetic fixed dates in 2099, nine are rolling and one is automatic. The fictional dates are fixed across devices. Demo entries have no official-source claims. Apply buttons are available for presentations and use reserved placeholder URLs without real application submission. Mock interview/document conditions demonstrate Eligible !; ordinary academic thresholds are checked automatically.

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
- [Design System](DESIGN.md)
- [Manual Test Cases](MANUAL_TEST_CASES.md)
- [Academic Qualifications, Grade Formats and Matching Examples](docs/ACADEMIC_QUALIFICATIONS.md)
- [Academic Requirement Types and STPM/UEC Calculations](docs/ACADEMIC_REQUIREMENT_TYPES.md)
- [Completed STPM CGPA Calculation](docs/STPM_CGPA_CALCULATION.md)
- [Improvement Log and FYP Documentation Changes](improve.md)

## License

This project was developed for academic purposes as a Final Year Project.

## Fictional demonstration dataset

The default initial catalogue now contains exactly 70 fictional awards. Academic cutoffs are stored in qualification rules, with varied nationality, income, funded level, status/year, field and institution restrictions. Most have no special conditions so plain Eligible appears; selected mock interviews/documents demonstrate Eligible !. All records are open, so unfiltered Eligible plus Not Eligible totals 70.

Demo 51–60 add ten complex Diploma-funded scenarios and Demo 61–70 add ten Degree-funded scenarios (displayed as Bachelor’s Degree, stored as Undergraduate). They demonstrate progression from completed qualifications, support for current students in specific years, alternative entry routes and combined academic/household/programme restrictions. The original 50 awards are unchanged.

See [the scenario guide](docs/DEMO_SCHOLARSHIPS.md). New devices use `php artisan migrate --seed`. On an existing SQLite installation, `php artisan scholarships:replace-catalog` backs up the database and replaces the old scholarships with these 70 demos while preserving student accounts/profiles/results. Normal seeding upserts without duplication but retains unrelated old rows. Build assets with `npm run build`. Demo application buttons use placeholder links and source-verification claims are omitted.
