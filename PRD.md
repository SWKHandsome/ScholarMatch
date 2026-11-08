# ScholarMatch — Product Requirements Document (PRD)

**Version:** 4.4 — Undecided programme choices and checked eligibility
**Updated:** 2026-11-08 (Asia/Singapore)
**Project:** Final Year Project for Malaysian scholarship discovery
**Stack:** Laravel 13, PHP 8.3+, MySQL (application), SQLite (isolated tests), Blade, Tailwind CSS, Alpine.js and Vite

## 1. Product Overview

ScholarMatch compares a student's official academic results and socioeconomic profile with administrator-defined scholarship requirements. Results explain each comparison and distinguish academic qualifications from the study level seeking funding.

| Display | Meaning |
| --- | --- |
| Eligible | All checked automatic requirements are met. Restricted field/institution conditions are not checked when the student explicitly chooses Haven't decided. |
| Eligible ! | Checked automatic requirements are met, and provider conditions still need verification. |
| Not Eligible | At least one checked automatic requirement is not met. |

The exclamation warning identifies provider checks, including special requirements and unconfirmed application dates. It is not a separate decision category and does not certify that those conditions have been met.

An undecided field/institution does not add an exclamation warning or a third decision label. The detail page explicitly lists the unchecked restrictions and the choices needed to satisfy them. Eligible is a match to the checked information, not confirmation of unchecked programme conditions or a provider's award decision.

## 2. Problem Statement

Scholarship requirements are scattered across providers and difficult to compare. Students need a consistent explanation of which requirements they meet, which they fail, and which special conditions require provider verification.

## 3. Product Goals

- Provide transparent requirement checks using official results.
- Support SPM, Cambridge IGCSE/O-Level, A-Level, Senior UEC, STAM and the existing CGPA-based qualifications.
- Separate the qualification providing results from the programme level seeking funding.
- Support current students through study status and year restrictions.
- Keep academic and socioeconomic conditions explicit and editable by administrators.

## 4. Target Users

Students seeking funding and administrators maintaining scholarship data, requirements and income thresholds. Administrators maintain information; they do not approve scholarship applications inside ScholarMatch.

## 5. User Stories

Students can maintain a profile, enter their latest official results, select a funding level, read eligibility explanations and special requirements, and save scholarships. Administrators can manage scholarships, eligibility rules and income categories, and inspect the latest evaluation logs.

Administrators can also search, create, view, edit and delete student accounts. Only account details are editable by administrators; student profiles and academic records are view-only.

## 6. Core Features

### 6.1 Authentication and Role Management

Registration and login use Laravel Breeze. Student and administrator routes are protected by role middleware. Account-profile and password workflows remain available.

Name is required, trimmed and limited to 100 characters. It must include a letter and allows Unicode letters/combining marks, spaces, apostrophes, hyphens, periods and slash. Email is trimmed, lowercased and checked for valid syntax with a complete domain, such as student@gmail.com or student@college.edu.my. Bare domains such as friend@nihao are rejected. Registration and account editing reject duplicate emails. These are format checks, not identity or mailbox verification; no provider whitelist, DNS lookup or OTP is required. Shared rules apply to registration, account editing and administrator forms; login uses the email format rule. Existing password rules are retained.

New registrations open Student Profile. On login and the generic /dashboard landing route, students open their first incomplete section: Student Profile, then Academic Result, then Dashboard once both are complete. Incomplete students' previous intended URLs are cleared; completed users keep normal intended navigation and administrators retain their existing flow. Profile completeness uses the same required-field rules as saving; a genuine zero income/dependent count is complete. Academic completeness uses the existing official-result readiness check.

Saving either setup form continues to the first remaining incomplete section or Dashboard. During setup a two-step progress indicator and Save and Continue profile action guide the student. Existing saved information is retained; manual editing remains available. State options display Kuala Lumpur, Labuan and Putrajaya. The previous Wilayah Persekutuan-prefixed values preselect their short equivalents and are normalised when that profile is saved, without a bulk data rewrite.

Login and Register cards provide a full-width Register | Login navigation switch, with the current page highlighted in blue. Forgot Password and Reset Password include Back to Login. Login, Register and Reset Password fields start masked and each password/confirmation field has an independent, keyboard-accessible show/hide control. Required inputs, validation errors and reset status messages remain available in the forms.

Login/Register share a stable-height card that reserves space for both forms. Switching fades in the selected form and slides the blue indicator over 250ms without a full reload; the URL and browser history follow the selected mode. Direct URLs and normal link navigation remain available. Hidden fields are inert. Password icons show current visibility (crossed eye when masked, open eye when visible), with an eye-button focus indicator for keyboard use only.

### 6.1.1 Administrator Student Management

Manage Students lists student accounts only, with search by name/email, profile and academic completion indicators, and 10 accounts per page. It is accessible from desktop/mobile admin navigation and the dashboard student-count card.

Administrators create an account using name, email, password and confirmation. The new user is always a student; the administrator stays logged in. The student completes their own profile and academic information after login. Editing permits name, email and an optional confirmed password; blank password fields preserve the existing password. Email/password changes invalidate the target student's database sessions, remembered login and reset tokens. Administrator accounts cannot be managed through these routes.

Deletion requires confirmation and permanently removes the student account and its profile, academic results, saved scholarships and recommendation logs. Scholarship records and other accounts remain. No new database tables are introduced. See MANUAL_TEST_CASES.md for acceptance scenarios.

### 6.2 Student Profile Management

Collect nationality, state, household income and number of dependents. Income categories are configured in the income_categories table. Recommendation evaluation reclassifies the current household income rather than trusting a stale stored category. Programme details are entered on Academic Result; Profile links to that page.

The Profile income helper and reference cards display administrator-configured names/ranges from the database, including exact cents and open upper limits. The helper updates while typing and renders an initial server-side result without JavaScript. Zero income is valid. Blank, invalid, unconfigured and unmatched income have distinct messages. Loaded pages receive administrator changes on refresh; no cross-tab push is implied. Admin category creation, updates and deletion transactionally reclassify saved profiles and invalidate recommendation caches. Income amounts allow at most two decimal places, within DECIMAL(12,2); a configured maximum cannot be below its minimum. Existing gaps are not silently filled. Overlapping ranges use ascending minimum, then ID, consistently across matching and display.

Nationality has exactly two student choices: Malaysian and Non-Malaysian. Scholarship nationality restrictions use the same two choices; leaving the restriction empty means Any nationality. Matching compares the selected categories exactly. Country-specific conditions can be recorded as Special Requirements for provider verification.

State and number of dependents are recorded but are not automatic eligibility criteria in this version.

### 6.3 Academic Result Management

Store one latest academic record per student:

| Field | Meaning |
| --- | --- |
| education_level | Latest programme currently being studied, or most recently completed level |
| study_status | studying or completed |
| year_of_study | Required for currently studying CGPA-based students; empty when completed |
| funding_study_level | Student's manually selected programme level seeking funding |
| institution_type | Intended institution type, or an explicit Haven't decided answer |
| field_of_study | Intended field, or an explicit Haven't decided answer |
| spm_as | Official number of A+, A and A− subjects |
| spm_credits | Official number of subjects graded C or above, including A subjects |
| grading_scale | Qualification-specific scale; IGCSE explicitly selects letters or 9–1 |
| grade_counts | Official number of subjects at each grade; completed STPM separately identifies the included Pengajian Am grade |
| stam_rank | Official overall STAM pangkat |
| cgpa | Official cumulative CGPA when studying; final CGPA when completed; completed STPM is calculated on save from final counts and PA |
| stpm_pa_grade | Pengajian Am grade already included in completed STPM subject counts |
| stpm_cgpa_confirmed | Student confirmed that calculated completed-STPM CGPA matches the official slip |
| result_status | official for new submissions |

The form first selects Secondary, Pre-U/Diploma/Pathway or Undergraduate, then a qualification within that category. SPM, Cambridge IGCSE/O-Level, A-Level and Senior UEC use minus/plus grade counters and direct numeric entry, with a live summary. STAM selects the official overall pangkat. SPM, IGCSE, O-Level, A-Level, UEC and STAM require Completed and official final results. STPM requires official subject grade counts. Completed students identify the included Pengajian Am grade; the system calculates its points plus the best three other subjects divided by four and rounded half up to two decimals, then requires confirmation against the official slip. Four or five final subjects are required. Currently studying STPM retains manual official overall CGPA and current year. Foundation, Matriculation, Diploma and Bachelor’s Degree retain official CGPA and current year when studying. Bachelor’s Degree remains stored as Undergraduate for compatibility. Students without required results must wait before receiving recommendations; a real zero CGPA is valid.

SPM As/credits are derived from grade counts, so students no longer enter those aggregates separately. Counts must be whole non-negative numbers, have at least one subject and use the selected scale. IGCSE scales are not converted. Changing category, qualification, scale or status clears incompatible inputs. Forecast/expected results, AS-Level and unspecified qualifications are outside this version. Existing aggregate-only SPM records retain their totals but require an actual breakdown before recommendations. See docs/ACADEMIC_QUALIFICATIONS.md for scales, limits and examples, docs/ACADEMIC_REQUIREMENT_TYPES.md for the typed editor and docs/STPM_CGPA_CALCULATION.md for the completed-result workflow. Existing CGPA-only STPM records retain their CGPA but need actual subject counts before recommendations.

Programme Seeking Funding groups funding_study_level, institution_type and field_of_study. These describe the target programme and may differ from the latest qualification supplying results. Both institution and field require a selection, but each independently accepts Haven't decided. Explicit undecided answers complete setup; empty/invalid answers do not. Official results and a supported funding study level remain required. Values are validated server-side and read from academic_results.

When a scholarship restricts a programme choice that is undecided, record not_checked, preserve its required values and explain the condition in blue on cards/details, logs and the synthetic admin preview. For example: Not checked — choose Engineering to meet this condition. An unrestricted criterion remains not_applicable. Decided choices are compared normally; unrelated academic, nationality, income and deadline failures still return Not Eligible. Saving later choices reevaluates matching. Haven't decided is not available as an administrator scholarship restriction or a field to manage in Matching Settings.

### 6.4 Scholarship Management

Administrators manage name, provider, description, award type, confirmed deadline (optional), application/intake notes, application link, active status, and an optional special_requirements textbox on both Add and Edit Scholarship. Seeded records display their official source and check date.

Fresh installations receive 20 richer fictional Malaysian-study awards, version scholarmatch-rich-demo-20-v1. Each has realistic benefit text and supported academic/programme conditions. Providers, funding and application destinations are invented; source-verification fields remain empty. Manual awards coexist with the demo catalogue. Ordinary reseeding upserts current demo keys. The explicit replacement command backs up the database and removes only retired demo-01 through demo-70 and their related records, preserving manual awards and student data. See docs/SCHOLARSHIP_CATALOG.md.

Special requirements are plain text, limited to 5,000 characters. The system displays this text safely rather than interpreting it as automatic eligibility logic.

### 6.5 Scholarship Rule Management

General requirements apply to every qualification route:

- Nationality.
- Funded study level, compared with funding_study_level.
- Required income category and maximum household income.
- Supported fields of study and institution type.

Add/Edit Scholarship and Add/Edit Rules share a repeatable qualification-box editor. Each box requires a qualification and study status. Exam-based qualifications require Completed; CGPA qualifications additionally allow Currently Studying and Either status, with optional year limits for studying. Configure SPM As/credits, grade-count thresholds, CGPA or minimum STAM pangkat as applicable. IGCSE thresholds use an explicit scale; alternatives use separate boxes. Optional UEC average settings specify the best subject count, included grade threshold and maximum average using A1=1 through F9=9. Insufficient qualifying subjects fails; lower averages are better. Removing/changing a box clears irrelevant fields in the browser and server.

Each box uses Add Another Requirement and a Requirement Type selector. Types are qualification-specific: overall CGPA, grade counts, SPM As/credits, STAM rank, STPM minimum best-subject average, and UEC maximum average/total. Live previews explain each condition; cumulative grade conditions also show their minimum grade pattern. One grade-count requirement contains all grade thresholds as compact rows, with Add Grade Row and individual removal. Removing its final row removes only that grade group. CGPA and point requirements stay in separate groups. An added condition must be complete, and changing type clears its incompatible inputs. Grade conditions count subjects at a chosen grade or better. For AAB, require 2 at A or above AND 3 at B or above. Grade counts do not identify individual subjects: named-subject conditions belong in Special Requirements and require provider verification.

All applicable conditions inside one box combine with AND. Boxes combine with OR: passing any complete box satisfies the academic route requirement. Checked general nationality/funding/income/field/institution conditions combine with AND; a restricted undecided field/institution is recorded as not_checked. Multiple boxes may use the same qualification with different statuses, results or year ranges.

Example: an Undergraduate scholarship accepts Completed Diploma with CGPA >= 3.00 OR Currently Studying Undergraduate with CGPA >= 3.30 in Years 1–3. Failing one alternative does not reject a student who passes another. Conditions from separate boxes cannot be combined to invent a passing route.

Empty general criteria and result/year boundaries are unrestricted; numeric zero is a real minimum. Both specified SPM minimums must pass. Each route has its own CGPA minimum. No boxes means no qualification, result, status or year restriction. Study status concerns the latest academic programme; the funded level remains outside the boxes.

Existing rules are converted into equivalent route rows during migration, retaining unrestricted study status as Either status. Legacy imports can still derive routes from old threshold/list columns; newly edited rules use only route rows and clear the old academic columns. Scholarship information and its rules save together in a transaction.

### 6.6 Recommendation Engine

1. Require a student profile and a complete latest academic record with official results, study status, required year and selected funding level.
2. Reclassify income using the administrator's current ranges. Unclassifiable income shows an action message rather than an invented decision.
3. Load active scholarships whose deadline has not passed or whose date needs confirmation. Unknown dates require provider verification and are never shown as a passed deadline check. Skip scholarships without a configured rule record.
4. Evaluate general requirements and every complete academic route. Retain each route's comparison results.
5. Return Not Eligible if a general requirement fails or no configured route passes. Otherwise return Eligible. Failure explanations use the closest relevant route; other alternatives are available in an expandable comparison.
6. Attach requires_verification only when the result is Eligible and trimmed special_requirements is non-empty.
7. Group by decision and order by earliest confirmed deadline (unknown dates last), then name and ID for deterministic ties.

There are no points, weights, percentage match scores or hard/soft rule settings in the active evaluation.

### 6.7 Recommendation Explanation

Each requirement records its key, label, required value, student value, outcome (met, not_met, not_checked or not_applicable), and explanation. Scholarship cards and detail pages show these results. not_checked is a comparison outcome, not another overall decision. Detail pages group unchecked programme conditions separately from met, failed and unrestricted criteria. Eligible ! includes an expandable, keyboard-accessible explanation of special conditions.

Student scholarship details present a short decision summary, the evaluator-selected qualification route, failed comparisons first, and grouped academic/other applicable checks. Qualification and study status share one comparison. Alternative routes and unrestricted criteria are collapsed by default. An amber provider section presents special conditions and uncertain dates; rolling/automatic application timing remains informational. Administrator logs retain their full comparison table. This is presentation only and does not change eligibility decisions or stored results.

The recommendation page provides a filter sidebar beside a single column of full-width cards. Students can search scholarship/provider names and filter by award type, funded study level, supported field and institution type. All active filters combine; unrestricted programme criteria also match the selected programme choice. Filters narrow existing evaluations without changing the student's information or eligibility decision.

Eligible and Not Eligible links display filtered group totals. Each group shows at most 10 scholarships per page, ordered by the existing deadline/name/ID rules. Numbered and previous/next links preserve filters and the chosen group. Applying filters, resetting or switching groups starts at page 1; out-of-range page numbers resolve to the last available page. On narrow screens, filters stack above the list. Empty filtered groups retain filters and offer a reset action.

The student Dashboard Recommendations statistic shows the total Eligible count from the complete current evaluation, including Eligible results with provider-verification warnings. It is independent of the five-entry mixed-status preview and of filters on the recommendation list. The card reads N eligible; incomplete academic/profile inputs retain the existing unavailable display.

### 6.7.1 Explanation refinements and administrator rule testing

Existing required/student comparisons and Met/Not Met outcomes remain. A small explanation refinement adds numeric shortfall sentences beside failed checks, such as CGPA 0.30 below the requirement. It does not introduce a new eligibility feature or matching algorithm. Grade shortfalls can overlap and are not summed.

Administrators can test unsaved requirements in Add/Edit Scholarship and Rules using synthetic academic/profile inputs. The preview supports all existing qualification formats and validates the draft and sample without saving either. It shows automatic check results and unresolved provider conditions; editing inputs invalidates the last result. The administrator preview uses the same evaluator as normal matching. See docs/ELIGIBILITY_PLANNING.md for scope and examples.

### 6.8 Saved Scholarships

Students can save and remove scholarships. Saving does not constitute an application or an eligibility decision. Saved closed scholarships remain visible as expired.

### 6.9 Recommendation Logs

Opening the recommendation list stores the latest decision and explanation per student/scholarship pair, including the algorithm version, failed requirements, verification flag and a snapshot of special text. Repeated evaluation updates the same record rather than appending a complete event history.

Existing score-based evaluations retain a legacy snapshot and appear as Previous Evaluation until refreshed. They are never directly relabeled as new decisions. Their old score columns remain for compatibility and preservation, and are not used in the active algorithm or displayed as current results.

## 7. Main Pages

Public: Home, About, Login and Register.
Student: Dashboard, Profile, Academic Result, Recommendations, Scholarship Details and Saved Scholarships.
Administrator: Dashboard, Scholarships, Scholarship Rules, Matching Settings and Recommendation Logs. Matching Settings displays Income Categories followed by Fields of Study on a single page.

Recommendations have Eligible and Not Eligible groups. Eligible ! is included in Eligible. Incomplete onboarding shows a completion message instead of a scholarship decision.

## 8. Database Tables

| Table | Relevant fields / relationships |
| --- | --- |
| users | Authentication and student/admin role |
| student_profiles | user_id, nationality, state, household_income, number_of_dependents, income_category; inactive legacy institution/field values retained |
| academic_results | Unique user_id; education_level, study_status, year_of_study, funding_study_level, institution_type, field_of_study, spm_as, spm_credits, cgpa, result_status, grading_scale, grade_counts JSON, stam_rank, stpm_pa_grade, stpm_cgpa_confirmed |
| income_categories | name, min_income, max_income |
| study_fields | id, unique current name, nullable original seed_name, is_active, timestamps; existing student/rule references remain string labels |
| scholarships | name, provider, description, award_type, nullable deadline, deadline_type, application_link, is_active, special_requirements, catalog_key, source_url, source_checked_at, application_notes |
| scholarship_rules | Unique scholarship_id; general nationality/funding/income/field/institution conditions; legacy academic columns retained |
| scholarship_qualification_routes | scholarship_rule_id; education_level, study_status, min_spm_as, min_spm_credits, min_cgpa, min_year_of_study, max_year_of_study, grading_scale, grade_requirements JSON, min_stam_rank, uec_average JSON, stpm_average JSON, uec_total JSON |
| saved_scholarships | user_id and scholarship_id |
| recommendation_logs | user_id, scholarship_id, status, evaluation_version, requirement_results, failed_requirements, explanation, requires_verification, special_requirements, legacy_evaluation |

Migration 2026_10_19_000000_add_decision_based_recommendations adds the new fields while preserving existing records. Legacy rule-type and score columns remain inactive for compatibility. Existing students must confirm their study status and funding level rather than having those answers invented during migration.

Migration 2026_10_19_010000_create_scholarship_qualification_routes_table adds the one-to-many route table and converts existing qualification/status/year/result settings without changing student or scholarship records. Route rows are deleted automatically when their parent rule/scholarship is deleted. Migration 2026_10_31_000000_add_qualification_grade_counts adds the nullable scale/count/rank and route-threshold fields, extends the academic qualification enum and preserves existing data. Old SPM aggregates require a real breakdown before matching; rollback guards against discarding new-format data. The current evaluator uses algorithm version decisions-v9-undecided-programme, so earlier cached decisions/logs refresh rather than being treated as current evaluations. Migration 2026_10_31_010000_add_subject_point_requirements adds nullable stpm_average and uec_total JSON fields without modifying existing rows, and guards against a lossy rollback. Migration 2026_10_31_020000_add_stpm_cgpa_calculation adds nullable stpm_pa_grade and default-false stpm_cgpa_confirmed while preserving prior CGPA and grades; no PA identity is invented. Existing usable manual records remain valid until edited; new completed STPM saves require the calculated/confirmed flow. Format definitions and examples are in docs/ACADEMIC_QUALIFICATIONS.md, docs/ACADEMIC_REQUIREMENT_TYPES.md and docs/STPM_CGPA_CALCULATION.md.

## 9. Missing Data Handling

Migration 2026_10_20_000000_move_programme_details_to_academic_results copies programme choices from profiles to existing academic records without creating qualifications/results. Legacy profile columns are nullable and no longer edited or used for matching. If a student has only a legacy profile, its programme choices prefill the first academic form; no academic record is created until valid official results are submitted. Rollback copies current programme choices back to existing profiles.

Missing profile, academic record or required official academic information blocks recommendation generation and identifies the required update. Pending legacy records cannot generate decisions. An absent scholarship rule record is excluded with an administrator-configuration notice. Missing values are never converted to zero.

This version collects one latest qualification. Earlier qualifications are not inferred. Administrators must define accepted latest-qualification routes consistently; historical SPM or subject-level evidence that cannot be represented should be explained as a special requirement when appropriate.

## 10. Data Seeding Strategy

Seeders provide local demonstration users, configurable income ranges and 20 richer fictional scholarships. Academic rules cover all 11 supported qualifications, alternative routes, grade patterns, UEC calculations, CGPA/status/year and provider checks. MySQL is the application database; isolated SQLite and separate MySQL tests verify behaviour. See docs/MYSQL_SETUP.md for safe transfer and hosting preparation.

## 11. Validation Rules

- Latest level and funding level must be supported education levels.
- Institution type and field each require a supported choice or the explicit Haven't decided answer; blank values remain incomplete.
- Study status is studying/completed; SPM, IGCSE, O-Level, A-Level, UEC and STAM require completed. STPM retains either student status and requires both official CGPA and subject counts.
- Current year is 1–10 for studying CGPA-based records.
- Grade counts are whole non-negative numbers in the chosen scale, with at least one subject; supported totals are at most 12 for SPM, 5 for STPM and 20 for the other grade-count formats. SPM As/credits are derived.
- Manually entered CGPA is numeric from 0.00–4.00 for studying STPM and Foundation/Matriculation/Diploma/Undergraduate. Completed STPM excludes any submitted cgpa value and derives it server-side from four/five final subjects and the included PA grade; accepted confirmation is required. PA/confirmation fields clear for other statuses/qualifications.
- STAM requires a recognised overall pangkat. IGCSE requires an explicit scale and does not convert between scales.
- New academic submissions require result_status=official.
- Monetary limits are nonnegative; zero is a real limit.
- Qualification boxes require a supported level and study status. Exam-based qualifications require Completed. Year boundaries are optional individually, within 1–10, and ordered when both are specified; routes other than studying CGPA clear them.
- Grade conditions require a recognised scale/grade and whole-number minimum; duplicate grade thresholds in one box are rejected. Optional UEC formulas require all settings and a maximum average from 1–9 with up to two decimal places, or an integer maximum total between N and 9N. STPM averages use 1–5 subjects and a minimum from 0–4 with up to two decimal places. All formulas require enough qualifying subjects and compare unrounded actual values.
- Inapplicable result, scale, rank, average and year fields are cleared server-side. Route forms require a JavaScript-initialization marker to prevent an uninitialized editor from accidentally removing existing routes.
- A confirmed scholarship deadline is a date; an unconfirmed date stays empty. Its application link is a URL.

## 12. Non-Functional Requirements

Usability: plain explanations and accessible special-condition disclosure.
Security: validation, escaped text, authentication, role checks and nested-resource ownership checks.
Maintainability: one recommendation service and shared Blade components.
Performance: cache evaluations for up to 30 minutes using current input data, catalog version and calendar date.

## 13. MVP Scope

Decision-based eligibility, latest official results, manual funding-level selection, configurable conditions, special-condition warnings, saved scholarships and latest-result logs.

## 14. Out of Scope for MVP

Subject-specific calculations from unnamed grade counts, undocumented provider grade conversions, provider award decisions, internal manual approval workflows, application submission/tracking, renewal management, document authenticity verification, predicted results, multiple academic-history records, and free-text rule parsing.

## 15. Future Business Potential

Possible future work includes provider integrations, multiple student academic-history records and application support. These are not implemented functionality.

## 16. Success Criteria

- A definite applicable failure always produces Not Eligible.
- All checked automatic requirements passing produces Eligible, with undecided programme restrictions explicitly labelled Not checked.
- Only admin-entered special requirements cause the ! warning.
- Official results are required before recommendation generation.
- SPM source qualification and target funding level are checked separately.
- A complete alternative route can pass independently; partial conditions from different boxes cannot be combined.
- Explanations and logs reflect current inputs and conditions.
- Existing data survives migration and old decisions remain identifiable.

## 17. Final Notes

The provider makes the final scholarship award decision. ScholarMatch reports matches to configured automatic requirements and clearly identifies special conditions still requiring verification.
