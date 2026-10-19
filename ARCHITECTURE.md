# ScholarMatch — Architecture Document

**Version:** 3.1 — Alternative qualification routes
**Updated:** 2026-10-19 (Asia/Singapore)
**Stack:** Laravel 13, PHP 8.3+, MySQL/SQLite, Blade, Tailwind CSS, Alpine.js, Vite and Pest

## 1. Architecture Overview

ScholarMatch is a Laravel MVC monolith with an application service for eligibility evaluation.

Browser → Routes → Role Middleware → Controllers → ScholarshipRecommendationService → Eloquent → Database

## 2. Architecture Pattern

Controllers validate requests and coordinate views. Models provide relationships and academic-readiness checks. The service evaluates conditions and returns arrays suitable for caching. Shared Blade components render decision badges and requirement explanations.

## 3. Core Modules

### 3.1 Authentication Module

Laravel Breeze handles authentication and account management. RoleMiddleware protects student and administrator routes. Nested rule controllers verify that the supplied rule belongs to the supplied scholarship.

### 3.2 Student Profile Module

Student profiles store nationality, state, household income, dependents, field and institution. Income category is calculated on profile save and recalculated against current ranges during recommendation generation.

State and dependents are currently informational rather than evaluation criteria.

### 3.3 Academic Result Module

AcademicResult stores one latest record per user:

- education_level: current/latest qualification supplying academic evidence.
- study_status: studying/completed.
- year_of_study: current year for studying non-SPM students.
- funding_study_level: manually selected programme level seeking funding.
- spm_as, spm_credits or cgpa: official results.
- result_status: official for new submissions; old pending rows are preserved but incomplete.

AcademicResult::isReadyForRecommendations checks required fields and official results. The academic controller enforces these rules server-side and clears irrelevant values when the qualification or status changes.

SPM submissions require completed exams and received official results. CGPA-based submissions require the latest official cumulative CGPA or final CGPA if completed. Students without these results cannot generate recommendations.

### 3.4 Scholarship Module

Scholarships contain provider metadata, an active flag, a date-only deadline, application link, and optional special_requirements text. Add and Edit forms share the special-requirements field.

Only active, open scholarships enter evaluation. The deadline remains open throughout its application-calendar date. Saved expired scholarships remain visible as expired.

### 3.5 Scholarship Rule Module

A scholarship has at most one rule record. Every specified condition is enforced; empty values are not applicable.

The parent rule stores general nationality, funded level, income, field and institution restrictions. ScholarshipQualificationRoute belongs to ScholarshipRule; a rule has many ordered qualificationRoutes. Each route stores education_level, study_status, optional min_spm_as/min_spm_credits or min_cgpa, and optional min_year_of_study/max_year_of_study.

ScholarshipRuleEditor validates shared Add/Edit Scholarship and Rules forms and replaces routes in a transaction. Qualification and status are required per box. SPM only allows Completed. Either status preserves unrestricted non-SPM status. Inactive academic/year fields are cleared on the server. Each year boundary may be empty; both supplied boundaries must be ordered. A hidden initialization marker prevents an uninitialized JavaScript editor from wiping routes.

required_study_level describes the funded programme and is compared against funding_study_level. Each route compares the student's latest education_level, study_status, official results and current year. AcademicRouteEvaluator combines conditions within each route with AND and complete routes with OR. General restrictions combine with AND. Two boxes may accept the same qualification under different conditions. With no routes, academic restrictions are not applicable.

The migration backfills routes from existing lists and threshold types; legacyAcademicRoutes provides the same fallback for legacy imports/seeders without route rows. Newly edited rules clear old academic columns so deleting the last route cannot resurrect an obsolete minimum. General rule and scholarship changes commit atomically. qualificationEditor in resources/js/app.js manages stable box identities, input names, addition/removal and conditional controls.

The old hard/soft/none columns and rule_payload are inactive compatibility fields. The service does not read them or parse JSON/free text into requirements.

## 4. Recommendation Engine

The service returns error and recommendations. Incomplete student information produces a completion error and an empty result list. Unclassifiable income produces an administrator-action error. Scholarships without rules are skipped and counted in unconfigured_count.

For each configured, open scholarship:

1. Evaluate general conditions and every academic route independently. Require one complete passing route if routes exist.
2. Record every outcome and explanation without stopping at the first failure.
3. Return Not Eligible when a general condition fails or no route passes. Never combine partial matches from different routes.
4. Otherwise return Eligible.
5. Set requires_verification only for an Eligible result with non-whitespace special text.

| Data | Meaning |
| --- | --- |
| status | Eligible or Not Eligible |
| requires_verification | True only for otherwise Eligible results with admin special text |
| special_requirements | Text to verify with the provider |
| requirement_results | key, label, required, actual, outcome, message per condition; academic_route includes nested route comparisons |
| failed_requirements | Keys of automatic conditions that fail |
| explanation | Plain text descriptions of the comparisons |

There is no active score, weighting, normalization or percentage ranking.

For the main explanation, the evaluator selects a passing route, or the closest relevant failing route (qualification, then status, then failed-condition count, with stable order). Nested alternatives remain available without contributing unrelated failures to the overall decision. This selection explains the decision; it does not rank scholarships or award points.

Example: SPM qualification with 8 As and 9 credits, funding level Undergraduate, compared with a scholarship funding Undergraduate and accepting SPM with minimum 8 As and 9 credits. Both academic minimums and the funding-level check pass. Other applicable requirements must also pass.

An interview note adds ! to an Eligible result. It cannot override a failed CGPA, income, nationality or other automatic condition.

## 5. Recommendation Execution Strategy

Evaluation is synchronous when the dashboard, list or detail page requests recommendations. The list also upserts the latest evaluation log per user/scholarship. Detail pages evaluate current data rather than trusting an old log.

Results are cached for 30 minutes. Cache identity includes algorithm version, user ID, a hash of actual profile/academic attributes, catalog version and today's date. This avoids same-second update collisions and prevents yesterday's open deadline from persisting after midnight.

Admin scholarship, rule and income-category mutations invalidate the catalog version. The service reclassifies income, avoiding stale stored categories after threshold changes.

Within each decision group, order by deadline, name and ID. Eligible results with special warnings remain in the Eligible group.

## 6. Database Tables

Core tables: users, student_profiles, academic_results, income_categories, scholarships, scholarship_rules, scholarship_qualification_routes, saved_scholarships and recommendation_logs.

```mermaid
erDiagram
    scholarships ||--o| scholarship_rules : has
    scholarship_rules ||--o{ scholarship_qualification_routes : accepts
    users ||--o| academic_results : provides_latest
    users ||--o| student_profiles : describes
    users ||--o{ recommendation_logs : receives
    scholarships ||--o{ recommendation_logs : evaluated_for
```

The route table has a cascading scholarship_rule_id foreign key, education_level and study_status strings, nullable integer SPM/year minima/maxima, nullable decimal(3,2) CGPA, and timestamps. Migration 2026_10_19_010000 backfills equivalent alternatives while retaining old columns. New saves use the child rows. Algorithm version decisions-v2-routes invalidates cached decisions and identifies earlier logs as previous evaluations.

New academic fields are nullable at schema level so migration can preserve legacy records without inventing study status or funding intent. New submissions require them through application validation.

New log fields: evaluation_version, requirement_results, failed_requirements, requires_verification, special_requirements and legacy_evaluation. The status column supports the new decision labels.

The migration snapshots existing score-based log data into legacy_evaluation and marks evaluation_version as legacy-score-v4. Existing score columns remain intact for preservation; active models/views do not use them as current scoring. Existing legacy rule-type columns also remain stored but inactive. Refreshing a log updates its current decision while retaining its legacy snapshot.

No live student records or scholarships are deleted or recreated by the migration.

## 7. Recommended Folder Structure

- app/Services/ScholarshipRecommendationService.php: evaluation, cache and latest log storage.
- app/Services/AcademicRouteEvaluator.php: AND within routes, OR between routes and nested explanations.
- app/Services/ScholarshipRuleEditor.php: shared validation, normalization and transactional route replacement.
- app/Models/AcademicResult.php: official-result readiness.
- app/Models/ScholarshipRule.php: qualification routes and supported fields.
- app/Models/ScholarshipQualificationRoute.php: individual alternative requirements.
- app/Http/Controllers/Student/: academic, profile, recommendation and dashboard workflows.
- app/Http/Controllers/Admin/: scholarship, rule, income and log workflows.
- resources/views/components/: decision-badge, requirement-results and scholarship-card.
- database/migrations/: additive upgrade and legacy-log preservation.
- tests/: unit, feature and migration checks.

## 8. Testing Strategy

Tests cover individual requirement failures, AND/OR combinations, qualification/funding separation, zero and boundary values, official-result validation, special-warning priority, expired/unconfigured scholarships, stable ordering, cache invalidation, log snapshots and role/nested-resource protection.

Pest uses an isolated in-memory SQLite database. Migration tests exercise old log preservation and academic-data preservation. Browser checks additionally verify form switching, tab interaction and expandable special text.

## 9. Security Design

Use server-side validation and role middleware, escaped Blade output, CSRF forms, user-scoped saved scholarships and nested-rule ownership checks. Special text remains plain text and cannot execute scripts. External application links use rel=noopener noreferrer.

## 10. Seed Data and Localhost

Seeders provide demonstration users, income ranges and sample scholarships/rules. Criteria are demonstration data, not verified live provider requirements.

For an existing database, apply php artisan migrate; do not use migrate:fresh. Existing students update their academic status and funding level before generating new decisions. Rebuild frontend assets after changing templates.

Date-only deadlines close after the configured application's deadline day. Existing timestamp/timezone conventions are preserved.

## 11. Final Architecture Notes

This version evaluates one latest qualification. It does not infer earlier qualifications, GPA equivalence, award renewal eligibility, or entry/enrolment context solely from the selected funding level. Conditions requiring evidence beyond the supported fields belong in the special-requirements description or a future data-model extension.
