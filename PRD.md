# ScholarMatch — Product Requirements Document (PRD)

**Version:** 3.1 — Alternative qualification routes
**Updated:** 2026-10-19 (Asia/Singapore)
**Project:** Final Year Project for Malaysian scholarship discovery
**Stack:** Laravel 13, PHP 8.3+, MySQL/SQLite, Blade, Tailwind CSS, Alpine.js and Vite

## 1. Product Overview

ScholarMatch compares a student's official academic results and socioeconomic profile with administrator-defined scholarship requirements. Results explain each comparison and distinguish academic qualifications from the study level seeking funding.

| Display | Meaning |
| --- | --- |
| Eligible | All applicable automatic requirements are met. |
| Eligible ! | Automatic requirements are met, and the administrator has entered special requirements that the student must verify with the provider. |
| Not Eligible | At least one applicable automatic requirement is not met. |

The exclamation warning is only for special requirements. It is not a separate decision category and does not certify that special conditions have been met.

## 2. Problem Statement

Scholarship requirements are scattered across providers and difficult to compare. Students need a consistent explanation of which requirements they meet, which they fail, and which special conditions require provider verification.

## 3. Product Goals

- Provide transparent requirement checks using official results.
- Support SPM graduates and students at STPM, Foundation, Matriculation, Diploma and Undergraduate levels.
- Separate the qualification providing results from the programme level seeking funding.
- Support current students through study status and year restrictions.
- Keep academic and socioeconomic conditions explicit and editable by administrators.

## 4. Target Users

Students seeking funding and administrators maintaining scholarship data, requirements and income thresholds. Administrators maintain information; they do not approve scholarship applications inside ScholarMatch.

## 5. User Stories

Students can maintain a profile, enter their latest official results, select a funding level, read eligibility explanations and special requirements, and save scholarships. Administrators can manage scholarships, eligibility rules and income categories, and inspect the latest evaluation logs.

## 6. Core Features

### 6.1 Authentication and Role Management

Registration and login use Laravel Breeze. Student and administrator routes are protected by role middleware. Account-profile and password workflows remain available.

### 6.2 Student Profile Management

Collect nationality, state, household income, number of dependents, field of study and institution type. The field and institution should describe the programme for which funding is sought. Income categories are configured in the income_categories table. Recommendation evaluation reclassifies the current household income rather than trusting a stale stored category.

State and number of dependents are recorded but are not automatic eligibility criteria in this version.

### 6.3 Academic Result Management

Store one latest academic record per student:

| Field | Meaning |
| --- | --- |
| education_level | Latest programme currently being studied, or most recently completed level |
| study_status | studying or completed |
| year_of_study | Required for currently studying CGPA-based students; empty when completed |
| funding_study_level | Student's manually selected programme level seeking funding |
| spm_as | Official number of A+, A and A− subjects |
| spm_credits | Official number of subjects graded C or above, including A subjects |
| cgpa | Latest official cumulative CGPA when studying; final CGPA when completed |
| result_status | official for new submissions |

SPM requires completed exams and received official results. SPM does not offer a Currently Studying or pending-results submission. CGPA-based students require at least one official CGPA result. Students without the required official results must wait before receiving recommendations; a real zero result remains a valid numeric value.

SPM credits must be at least the number of As. Changing qualification or completion status clears irrelevant grade/year fields.

### 6.4 Scholarship Management

Administrators manage name, provider, description, award type, deadline, application link, active status, and an optional special_requirements textbox on both Add and Edit Scholarship.

Special requirements are plain text, limited to 5,000 characters. The system displays this text safely rather than interpreting it as automatic eligibility logic.

### 6.5 Scholarship Rule Management

General requirements apply to every qualification route:

- Nationality.
- Funded study level, compared with funding_study_level.
- Required income category and maximum household income.
- Supported fields of study and institution type.

Add/Edit Scholarship and Add/Edit Rules share a repeatable qualification-box editor. Each box requires a supported latest qualification and an explicit study status (Completed, Currently Studying, or Either status for non-SPM). SPM fixes Completed and shows optional minimum As/credits; other levels show optional minimum CGPA. Currently Studying reveals optional minimum/maximum years. Removing or changing a box clears irrelevant inputs both in the browser and on the server.

All applicable conditions inside one box combine with AND. Boxes combine with OR: passing any complete box satisfies the academic route requirement. General nationality/funding/income/field/institution conditions still combine with AND. Multiple boxes may use the same qualification with different statuses, results or year ranges.

Example: an Undergraduate scholarship accepts Completed Diploma with CGPA >= 3.00 OR Currently Studying Undergraduate with CGPA >= 3.30 in Years 1–3. Failing one alternative does not reject a student who passes another. Conditions from separate boxes cannot be combined to invent a passing route.

Empty general criteria and result/year boundaries are unrestricted; numeric zero is a real minimum. Both specified SPM minimums must pass. Each route has its own CGPA minimum. No boxes means no qualification, result, status or year restriction. Study status concerns the latest academic programme; the funded level remains outside the boxes.

Existing rules are converted into equivalent route rows during migration, retaining unrestricted study status as Either status. Legacy imports can still derive routes from old threshold/list columns; newly edited rules use only route rows and clear the old academic columns. Scholarship information and its rules save together in a transaction.

### 6.6 Recommendation Engine

1. Require a student profile and a complete latest academic record with official results, study status, required year and selected funding level.
2. Reclassify income using the administrator's current ranges. Unclassifiable income shows an action message rather than an invented decision.
3. Load active scholarships whose deadline has not passed. Skip scholarships without a configured rule record.
4. Evaluate general requirements and every complete academic route. Retain each route's comparison results.
5. Return Not Eligible if a general requirement fails or no configured route passes. Otherwise return Eligible. Failure explanations use the closest relevant route; other alternatives are available in an expandable comparison.
6. Attach requires_verification only when the result is Eligible and trimmed special_requirements is non-empty.
7. Group by decision and order by earliest deadline, then name and ID for deterministic ties.

There are no points, weights, percentage match scores or hard/soft rule settings in the active evaluation.

### 6.7 Recommendation Explanation

Each requirement records its key, label, required value, student value, outcome (met, not_met or not_applicable), and explanation. Scholarship cards and detail pages show these results. Eligible ! includes an expandable, keyboard-accessible explanation of special conditions.

### 6.8 Saved Scholarships

Students can save and remove scholarships. Saving does not constitute an application or an eligibility decision. Saved closed scholarships remain visible as expired.

### 6.9 Recommendation Logs

Opening the recommendation list stores the latest decision and explanation per student/scholarship pair, including the algorithm version, failed requirements, verification flag and a snapshot of special text. Repeated evaluation updates the same record rather than appending a complete event history.

Existing score-based evaluations retain a legacy snapshot and appear as Previous Evaluation until refreshed. They are never directly relabeled as new decisions. Their old score columns remain for compatibility and preservation, and are not used in the active algorithm or displayed as current results.

## 7. Main Pages

Public: Home, About, Login and Register.
Student: Dashboard, Profile, Academic Result, Recommendations, Scholarship Details and Saved Scholarships.
Administrator: Dashboard, Scholarships, Scholarship Rules, Income Categories and Recommendation Logs.

Recommendations have Eligible and Not Eligible groups. Eligible ! is included in Eligible. Incomplete onboarding shows a completion message instead of a scholarship decision.

## 8. Database Tables

| Table | Relevant fields / relationships |
| --- | --- |
| users | Authentication and student/admin role |
| student_profiles | user_id, nationality, state, household_income, number_of_dependents, income_category, institution_type, field_of_study |
| academic_results | Unique user_id; education_level, study_status, year_of_study, funding_study_level, spm_as, spm_credits, cgpa, result_status |
| income_categories | name, min_income, max_income |
| scholarships | name, provider, description, award_type, deadline, application_link, is_active, special_requirements |
| scholarship_rules | Unique scholarship_id; general nationality/funding/income/field/institution conditions; legacy academic columns retained |
| scholarship_qualification_routes | scholarship_rule_id; education_level, study_status, min_spm_as, min_spm_credits, min_cgpa, min_year_of_study, max_year_of_study |
| saved_scholarships | user_id and scholarship_id |
| recommendation_logs | user_id, scholarship_id, status, evaluation_version, requirement_results, failed_requirements, explanation, requires_verification, special_requirements, legacy_evaluation |

Migration 2026_10_19_000000_add_decision_based_recommendations adds the new fields while preserving existing records. Legacy rule-type and score columns remain inactive for compatibility. Existing students must confirm their study status and funding level rather than having those answers invented during migration.

Migration 2026_10_19_010000_create_scholarship_qualification_routes_table adds the one-to-many route table and converts existing qualification/status/year/result settings without changing student or scholarship records. Route rows are deleted automatically when their parent rule/scholarship is deleted. The route evaluator uses algorithm version decisions-v2-routes, so earlier cached decisions/logs refresh rather than being treated as current route evaluations.

## 9. Missing Data Handling

Missing profile, academic record or required official academic information blocks recommendation generation and identifies the required update. Pending legacy records cannot generate decisions. An absent scholarship rule record is excluded with an administrator-configuration notice. Missing values are never converted to zero.

This version collects one latest qualification. Earlier qualifications are not inferred. Administrators must define accepted latest-qualification routes consistently; historical SPM or subject-level evidence that cannot be represented should be explained as a special requirement when appropriate.

## 10. Data Seeding Strategy

Seeders provide local demonstration users, income ranges, scholarships and requirements. Sample provider criteria are demonstration data and must be checked against providers before real use. Students complete the new academic fields before recommendations. Automated tests use an isolated in-memory SQLite database.

## 11. Validation Rules

- Latest level and funding level must be supported education levels.
- Study status is studying/completed; SPM requires completed.
- Current year is 1–10 for studying CGPA-based records.
- SPM counts are integers from 0–12; credits must include As.
- CGPA is numeric from 0.00–4.00 and always required for non-SPM records.
- New academic submissions require result_status=official.
- Monetary limits are nonnegative; zero is a real limit.
- Qualification boxes require a supported level and study status. SPM requires Completed. Year boundaries are optional individually, within 1–10, and ordered when both are specified; non-studying routes clear them.
- Inapplicable SPM/CGPA fields are cleared server-side. Route forms require a JavaScript-initialization marker to prevent an uninitialized editor from accidentally removing existing routes.
- A scholarship deadline is a date and its application link is a URL.

## 12. Non-Functional Requirements

Usability: plain explanations and accessible special-condition disclosure.
Security: validation, escaped text, authentication, role checks and nested-resource ownership checks.
Maintainability: one recommendation service and shared Blade components.
Performance: cache evaluations for up to 30 minutes using current input data, catalog version and calendar date.

## 13. MVP Scope

Decision-based eligibility, latest official results, manual funding-level selection, configurable conditions, special-condition warnings, saved scholarships and latest-result logs.

## 14. Out of Scope for MVP

Provider award decisions, internal manual approval workflows, application submission/tracking, renewal management, document authenticity verification, predicted results, multiple academic-history records, and free-text rule parsing.

## 15. Future Business Potential

Possible future work includes provider integrations, multiple student academic-history records and application support. These are not implemented functionality.

## 16. Success Criteria

- A definite applicable failure always produces Not Eligible.
- All applicable automatic checks passing produces Eligible.
- Only admin-entered special requirements cause the ! warning.
- Official results are required before recommendation generation.
- SPM source qualification and target funding level are checked separately.
- A complete alternative route can pass independently; partial conditions from different boxes cannot be combined.
- Explanations and logs reflect current inputs and conditions.
- Existing data survives migration and old decisions remain identifiable.

## 17. Final Notes

The provider makes the final scholarship award decision. ScholarMatch reports matches to configured automatic requirements and clearly identifies special conditions still requiring verification.
