# ScholarMatch — Architecture Document

**Project:** ScholarMatch: A Result-Based and Socioeconomic Scholarship Recommendation Platform for Malaysian Students  
**Version:** 2.0 Short Architecture  
**Stack:** Laravel, PHP, MySQL, Blade, Tailwind CSS or Bootstrap

---

## 1. Architecture Overview

ScholarMatch uses a Laravel MVC monolith with a service layer. Laravel handles routing, authentication, validation, business logic, database access, and Blade page rendering. MySQL stores users, profiles, academic results, scholarships, rules, saved scholarships, and recommendation logs.

```text
Browser → Routes → Controllers → Services → Eloquent Models → MySQL
```

Blade is used for server-rendered pages to keep the MVP simple, fast to build, and easy to demonstrate on localhost.

---

## 2. Architecture Pattern

ScholarMatch follows MVC plus service-layer architecture.

```text
Model      → Database tables and relationships
View       → Blade pages
Controller → Request handling and validation
Service    → Business logic and recommendation engine
Tests      → Automated validation of recommendation logic
```

The recommendation logic must be placed in:

```text
app/Services/ScholarshipRecommendationService.php
```

Controllers should call the service and pass results to Blade views. They should not contain scoring logic.

---

## 3. Core Modules

## 3.1 Authentication Module

Handles student registration, login, logout, admin login, and role-based access.

Roles:

```text
student
admin
```

Student routes are restricted to students. Admin routes are restricted to admins.

---

## 3.2 Student Profile Module

Stores socioeconomic and study information:

```text
nationality
state
household_income
number_of_dependents
income_category
institution_type
field_of_study
```

The system classifies household income into B40, M40, or T20 using the `income_categories` table.

---

## 3.3 Academic Result Module

Stores academic data:

```text
education_level
spm_as
spm_credits
cgpa
result_status
```

SPM students use SPM As and credits. STPM, Foundation, Matriculation, Diploma, and Undergraduate students use CGPA.

Result status:

```text
official
pending
```

Pending results should show preliminary guidance only.

---

## 3.4 Scholarship Module

The `scholarships` table stores metadata only:

```text
name
provider
description
award_type
deadline
application_link
is_active
```

Eligibility criteria must be stored separately in `scholarship_rules`.

---

## 3.5 Scholarship Rule Module

Scholarship rules should support both structured fields and flexible JSON rules.

Core rule fields:

```text
scholarship_id
required_nationality
required_study_level
required_income_category
max_household_income
min_spm_as
min_spm_credits
min_cgpa
required_field_of_study
required_institution_type
rule_payload JSON
```

Rule type fields:

```text
income_rule_type
study_level_rule_type
field_rule_type
institution_rule_type
```

Rule type values:

```text
hard
soft
none
```

The `rule_payload` JSON column stores dynamic future criteria such as birthplace, state-specific rules, special categories, or custom scholarship conditions without requiring a new database migration.

Example:

```json
{
  "birth_state": "Johor",
  "preferred_category": "STEM",
  "requires_leadership": true
}
```

---

## 4. Recommendation Engine

The recommendation engine uses two phases.

### Phase 1: Hard Eligibility Filtering

Hard rules are checked first. If any hard rule fails, the scholarship is classified as:

```text
Not Suitable
```

Hard rules may include nationality, income category, maximum household income, study level, deadline, field of study, institution type, and custom JSON rules.

### Phase 2: Soft Scoring

Soft scoring only runs after hard rules pass.

Suggested weights:

```text
Academic result match: 40
Field of study match: 25
Institution type match: 20
Income priority match: 15
Total: 100
```

Classification:

```text
80–100 = Eligible
50–79  = Partially Eligible
0–49   = Not Suitable
```

Each result should include:

```text
score
status
failed_hard_rules
explanation
score_breakdown
```

Example `score_breakdown` JSON:

```json
{
  "academic": 40,
  "field": 25,
  "institution": 0,
  "income": 15
}
```

---

## 5. Recommendation Execution Strategy

For the MVP, recommendations may be generated synchronously when the student clicks “View Recommendations”. This is acceptable for a small seeded dataset.

For future scalability, recommendation generation should be moved to Laravel Queues using a job such as:

```text
GenerateRecommendationsJob
```

Future flow:

```text
Student requests recommendations → Job dispatched → Queue processes scoring → Results stored → Student views completed matches
```

---

## 6. Database Tables

Main tables:

```text
users
student_profiles
academic_results
income_categories
scholarships
scholarship_rules
saved_scholarships
recommendation_logs
```

Key relationships:

```text
User hasOne StudentProfile
User hasOne AcademicResult
User hasMany SavedScholarship
User hasMany RecommendationLog
Scholarship hasOne ScholarshipRule
Scholarship hasMany RecommendationLog
Scholarship hasMany SavedScholarship
```

The `recommendation_logs` table should include structured JSON columns:

```text
failed_hard_rules JSON
explanation JSON
score_breakdown JSON
```

---

## 7. Recommended Folder Structure

```text
app/
 ├── Http/Controllers/Student/
 ├── Http/Controllers/Admin/
 ├── Http/Middleware/RoleMiddleware.php
 ├── Models/
 ├── Services/ScholarshipRecommendationService.php
 └── Jobs/GenerateRecommendationsJob.php

resources/views/
 ├── public/
 ├── student/
 ├── admin/
 └── layouts/

tests/
 ├── Unit/ScholarshipRecommendationServiceTest.php
 └── Feature/RecommendationFlowTest.php
```

---

## 8. Testing Strategy

ScholarMatch must include automated tests for the recommendation engine.

Required unit tests:

```text
T20 student fails B40 hard rule
Expired scholarship returns Not Suitable
Student with matching CGPA receives academic score
Soft score produces correct Eligible classification
Soft score produces correct Partially Eligible classification
Missing academic result blocks final recommendation
Pending result returns preliminary guidance
score_breakdown totals correctly to final score
```

Use PHPUnit or Pest for testing.

---

## 9. Security Design

The system should use:

```text
Laravel authentication
Password hashing
CSRF protection
Input validation
Role middleware
Escaped Blade output
Authenticated database actions
```

---

## 10. Seed Data and Localhost

Required seeders:

```text
UserSeeder
IncomeCategorySeeder
ScholarshipSeeder
ScholarshipRuleSeeder
```

Test accounts:

```text
Admin: admin@scholarmatch.test / password
Student: student@scholarmatch.test / password
```

Local setup:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
npm run dev
```

Local URL:

```text
http://127.0.0.1:8000
```

---

## 11. Final Architecture Notes

ScholarMatch should prioritize correctness, explainability, and maintainability. The MVP can use synchronous recommendations, but the architecture should be ready for queues, JSON-based dynamic rules, structured score breakdowns, and automated tests.
