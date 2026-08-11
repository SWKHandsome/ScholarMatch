# ScholarMatch

**ScholarMatch** is a web-based scholarship recommendation platform for Malaysian students. It matches a student's academic results and socioeconomic profile with scholarship eligibility rules, then provides transparent recommendations with clear explanations.

Built as a Final Year Project (FYP), ScholarMatch focuses on making scholarship discovery more accessible, especially for students who need financial support but find eligibility criteria difficult to compare across multiple sources.

## Key features

- Student registration, login, and role-based access control
- Student profile management for nationality, state, household income, income category, field of study, and institution type
- Academic-result management for SPM, STPM, Foundation, Matriculation, Diploma, and Undergraduate students
- Automatic B40, M40, and T20 income-category classification
- Scholarship, scholarship-rule, and income-threshold management for administrators
- Explainable recommendations classified as **Eligible**, **Partially Eligible**, or **Not Suitable**
- Saved scholarships for students
- Recommendation logs for administrators
- Seeded sample data and test accounts for local demonstrations

## How recommendations work

ScholarMatch uses a two-phase recommendation engine:

1. **Hard eligibility filtering:** Strict requirements are checked first, such as nationality, income category, maximum household income, study level, application deadline, field of study, and institution type. Failing any applicable hard rule results in **Not Suitable**.

2. **Soft scoring and ranking:** Scholarships that pass hard rules are scored out of 100 and ordered from highest to lowest match.

| Criterion | Maximum score |
| --- | ---: |
| Academic-result match | 40 |
| Field-of-study match | 25 |
| Institution-type match | 20 |
| Income-priority match | 15 |

| Score | Recommendation status |
| --- | --- |
| 80–100 | Eligible |
| 50–79 | Partially Eligible |
| 0–49 | Not Suitable |

Each recommendation includes its score breakdown, failed hard rules (if any), and human-readable explanations. Results with pending academic outcomes are marked as preliminary guidance.

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
php artisan migrate:fresh --seed
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

The test suite covers the recommendation engine and main student/admin workflows, including hard-rule failures, score calculation, recommendation classification, missing profile data, pending results, and access control.

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

## License

This project was developed for academic purposes as a Final Year Project.
