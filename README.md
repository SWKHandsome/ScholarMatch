# ScholarMatch

**ScholarMatch** is a web-based scholarship recommendation platform for Malaysian students. It matches a student's academic results and socioeconomic profile with scholarship eligibility rules, then provides transparent recommendations with clear explanations.

Built as a Final Year Project (FYP), ScholarMatch focuses on making scholarship discovery more accessible, especially for students who need financial support but find eligibility criteria difficult to compare across multiple sources.

## Key features

- Student registration, login, and role-based access control
- Student profile management for nationality, state, household income, income category, field of study, and institution type
- Latest official academic results, study status/year and separately selected funding level for SPM, STPM, Foundation, Matriculation, Diploma and Undergraduate students
- Automatic B40, M40, and T20 income-category classification
- Scholarship, scholarship-rule, and income-threshold management for administrators
- Repeatable qualification boxes on Add/Edit Scholarship, with separate results, study status and year limits for each accepted route
- Explainable **Eligible / Not Eligible** decisions, with **!** for administrator-entered special requirements
- Saved scholarships for students
- Recommendation logs for administrators
- Seeded sample data and test accounts for local demonstrations

## How recommendations work

ScholarMatch checks every applicable scholarship requirement against the student's official academic results and profile. There are no points, weights or percentage match scores.

| Display | Meaning |
| --- | --- |
| Eligible | Every applicable automatic requirement is met. |
| Eligible ! | Automatic requirements are met, with special conditions entered by the administrator that must be verified with the provider. Expand ! to read them. |
| Not Eligible | At least one applicable requirement is not met. |

Students record one latest study level, Currently Studying/Completed status, current year when studying, and a separately selected study level seeking funding. SPM requires completed exams and official As/credits. CGPA-based levels require the latest official cumulative CGPA, or final CGPA when completed. Results that are not yet available cannot be used to generate recommendations.

Requirements include nationality, funded level, accepted latest qualifications, study status/year, income, academics, field and institution. Within the student's qualification route, every configured minimum must pass. Empty criteria are not applicable. Results include each requirement, student value and outcome.

Administrators add alternative qualification boxes directly on Add/Edit Scholarship or the Rules page. Each box has its own level, status, result minimums and optional current-year boundaries. Students must pass any one complete box plus the general requirements. For example, Completed Diploma with CGPA >= 3.00 OR Currently Studying Undergraduate with CGPA >= 3.30 in Years 1–3. Empty minimums/boundaries are unrestricted; no boxes means no academic restriction. The student still needs official results. Qualification level/status have required * markers; irrelevant result/year controls are hidden, disabled and cleared.

Only active, open scholarships with configured rules are evaluated. Eligible (including !) and Not Eligible results are grouped separately and ordered by nearest deadline. Special requirements do not override a failed automatic condition. Providers make the final award decisions.

## Upgrading an existing installation

Apply the additive migration and rebuild assets:

```bash
php artisan migrate
npm run build
```

Existing profiles and scholarships are preserved. Existing students must confirm their study status and selected funding level, and supply official results, before generating new decisions. Old evaluation logs remain identified as Previous Evaluation until refreshed; their historical score data is preserved but not treated as a current decision.

The qualification-route migration converts existing rules into equivalent child rows and retains the original columns. Rebuild assets so the repeatable editor is available. Keep a database backup before upgrades. Earlier evaluations refresh under decisions-v2-routes when the student opens recommendations.

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
```

The test suite covers requirement decisions, official-result validation, qualification/funding separation, warning priority, thresholds, deadlines, caches, legacy migration, and student/admin access control.

## Project structure

```text
app/
├── Http/Controllers/       # Student, admin, authentication, and profile controllers
├── Models/                 # Eloquent models and relationships
└── Services/
    └── ScholarshipRecommendationService.php

database/
├── migrations/             # Database schema
└── seeders/                # Demo accounts, categories, scholarships, and rules

resources/views/            # Blade pages, layouts, and reusable components
tests/                      # Pest unit and feature tests
```

## Documentation

- [Product Requirements Document](PRD.md)
- [Architecture Document](ARCHITECTURE.md)
- [Design System](DESIGN.md)
- [Manual Test Cases](MANUAL_TEST_CASES.md)
- [Improvement Log and FYP Documentation Changes](improve.md)

## License

This project was developed for academic purposes as a Final Year Project.
