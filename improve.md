# ScholarMatch Improvement Log

**Updated:** 2026-10-20 (Asia/Singapore)

This file records completed changes, verification, and the project/FYP documentation to revise. Chapter numbers are omitted because the student's report structure has not been supplied.

## Current agreed behaviour

| Situation | Result |
| --- | --- |
| General requirements pass and any one complete qualification route passes (or no routes restrict it); no special text | Eligible |
| Same automatic checks pass; administrator entered special text | Eligible !, inside the Eligible group |
| A general requirement fails, or none of the configured qualification routes passes | Not Eligible |
| Required profile/official academic information is incomplete | Ask the student to complete it before generating recommendations |

The ! warning applies only to administrator-entered special requirements. It is not used for missing student results. The provider makes the final award decision.

Academic information uses one latest study level, Currently Studying/Completed status, current year when studying, and a manually selected Study Level Seeking Funding. SPM requires completed exams and official results. Other levels require a latest official cumulative CGPA, or final CGPA if completed. SPM credits count subjects graded C or above, including A subjects.

Administrator qualification boxes combine with OR; requirements inside each box combine with AND. Each box has its own result/status/year settings. See IMP-005 for the current route editor and migration; earlier entries describe previous stages.

## IMP-001 — Establish this improvement log

**Date:** 2026-10-19
**Type:** Documentation
**Status:** Completed

Added this record so implementation changes can be traced to the corresponding FYP report edits.

## IMP-002 — Review the original recommendation engine

**Date:** 2026-10-19
**Type:** Implementation review
**Status:** Completed; original behaviour superseded by IMP-003

The original engine checked hard requirements and then calculated weighted academic/field/institution/income points, normalized the result, and classified it as Eligible, Partially Eligible or Not Suitable. Academic minimums affected points rather than acting as universal eligibility requirements. The same education-level field was used for academic evidence and funded study level.

The review identified the need to separate qualification and funding level, remove scores and hard/soft categories, and explain individual requirements.

## IMP-003 — Replace scoring with requirement-based eligibility

**Date:** 2026-10-19
**Type:** Recommendation redesign, academic workflow, interface, data migration and documentation
**Implementation status:** Completed and verified

### What changed and why

| Area | Before | After / benefit |
| --- | --- | --- |
| Decisions | Score thresholds determined Eligible, Partially Eligible or Not Suitable | Every applicable automatic condition must pass for Eligible; any failure produces Not Eligible |
| Requirement settings | Hard, soft and none selectors, plus weighted points | Every specified condition is enforced; empty criteria are Not Applicable |
| Academic evidence | One level mixed qualification and funded programme; pending grades could be evaluated | Latest qualification and manually selected funding level are separate |
| Study status/year | Not recorded | Currently Studying or Completed; current year required for studying CGPA-based records |
| Official results | Pending results generated preliminary guidance | Official results are required before recommendations; SPM additionally requires completed exams |
| SPM minimums | As and credits used a points fallback | Both configured minimums must pass; credits must include all A subjects |
| Qualification routes | Threshold applicability could silently skip the intended academic evidence | Admin specifies accepted latest qualifications; threshold types provide a fallback for existing rules |
| Special conditions | No scholarship-level field | Optional Add/Edit Scholarship textbox; otherwise Eligible results display expandable ! and provider-check guidance |
| Explanations | Score circles, point breakdowns and message-wording guesses | Explicit Requirement / Required / Your Information / Result comparisons |
| Ordering | Highest score first | Eligible group (including !) then Not Eligible; nearest deadline, name and ID within each group |
| Availability | Expired active listings were evaluated as unsuitable | Only active, open, configured scholarships enter recommendations; deadline day remains open |
| Income updates | Stored category and cached results could become stale | Reclassify current income and invalidate decisions when admin thresholds change |
| Cache updates | Timestamp-only keys could collide within a second | Input-content hash, catalog version and calendar date determine cache identity |
| Nationality | Student choices and admin country-specific choices differed | Shared options; a broad Non-Malaysian answer requests clarification when a specific nationality is needed |
| Old evaluations | Old score statuses could be mistaken for current results | Preserve a legacy snapshot and label Previous Evaluation until refreshed |

### Files and implementation areas

- Engine: app/Services/ScholarshipRecommendationService.php.
- Models: app/Models/AcademicResult.php, Scholarship.php, ScholarshipRule.php and RecommendationLog.php.
- Student controllers: AcademicResultController.php, RecommendationController.php, DashboardController.php and ProfileController.php.
- Admin controllers: ScholarshipController.php, ScholarshipRuleController.php, IncomeCategoryController.php and RecommendationLogController.php.
- Shared configuration: config/scholarship.php.
- Database upgrade: database/migrations/2026_10_19_000000_add_decision_based_recommendations.php.
- Student screens: academic result, profile, recommendations list/detail, dashboard and saved-scholarship deadline display.
- Admin screens: scholarship Add/Edit, rule Add/Edit/summary, dashboard and recommendation-log list/detail.
- Shared views: components/decision-badge.blade.php, requirement-results.blade.php and scholarship-card.blade.php.
- Public information: resources/views/public/home.blade.php and about.blade.php.
- Supporting files: resources/css/app.css, ScholarshipRuleSeeder.php and the affected unit/feature/migration tests.

### Implementation choices

- At this stage, keep one latest qualification record. Multiple academic-history records and different CGPA thresholds for individual qualification routes were outside scope. IMP-005 subsequently adds different per-route thresholds; multiple student academic-history records remain outside scope.
- Funded level is selected directly. The system does not infer new-entry, existing-award or renewal status solely from that level.
- Matching any supported field/qualification value is sufficient. Independent applicable conditions combine with AND; equality passes at minimums/maximums.
- Within the student's accepted qualification route, all configured academic minimums must pass.
- Missing official results or required onboarding fields block recommendations; they are not classified as Not Eligible and do not produce !.
- Scholarships without a rule record are excluded with a configuration notice. An intentionally saved unrestricted rule can pass its non-applicable criteria.
- Special text is plain text, safely escaped, with no internal administrator approval workflow.
- Existing score/rule-type database columns remain inactive to preserve compatibility and historical data. No active scoring code or score display uses them.

### Documentation updated and FYP report impact

| Project document | Updated sections | FYP report material to revise |
| --- | --- | --- |
| PRD.md | 1, 5, 6.2–6.9, 7–14 and 16–17 | Requirements, user stories, use cases, decision table, validation and scope |
| ARCHITECTURE.md | 3.2–3.5, 4–10 and 11 | Academic/rule data design, ERD/data dictionary, algorithm, execution/cache strategy and limitations |
| DESIGN.md | Scholarship Cards, Badges & Chips, Academic Form and Requirement Explanations | Interface design, warning behaviour, academic form and screenshots |
| README.md | Key features, How recommendations work, upgrade and testing guidance | System overview, installation/upgrade and demonstration instructions |
| MANUAL_TEST_CASES.md | 4, 5, 10, 12–13 and regression/coverage notes | Academic/recommendation/admin test tables and expected results |
| Public Home/About pages | Feature descriptions and methodology | User guide and public-page screenshots |

Replace the report's weighted-score formula, 40/25/20/15 point table, score-threshold classification and hard/soft flowchart with the decision flow below. Update screenshots that show circular match scores, Partially Eligible tabs, pending-result choices or hard/soft selectors.

```mermaid
flowchart TD
    A[Student profile and official results] --> B{Information complete?}
    B -- No --> C[Request profile or academic update]
    B -- Yes --> D[Load active open scholarships with rules]
    D --> E[Check every applicable automatic requirement]
    E --> F{Any requirement fails?}
    F -- Yes --> G[Not Eligible with failure explanations]
    F -- No --> H{Admin special requirements present?}
    H -- No --> I[Eligible]
    H -- Yes --> J[Eligible ! with verification text]
```

**Suggested report wording:** “ScholarMatch applies a requirement-based eligibility process. The system compares the student's official academic results and socioeconomic profile with each applicable scholarship condition. A failure produces Not Eligible; satisfying all automatic conditions produces Eligible. Administrator-entered special conditions add an exclamation warning to otherwise Eligible results and identify what the student must verify with the provider. The student's latest academic qualification is recorded separately from the study level seeking funding.”

### Verification and database upgrade

- Baseline before application edits: 133 tests and 444 assertions passed.
- Updated suite: 162 tests and 495 assertions passed after the final code changes.
- Laravel Pint passed; git diff --check passed after documentation whitespace cleanup.
- Frontend production build succeeded with Vite 8.1.0.
- Desktop browser checks used a separate synthetic SQLite database: decision tabs, expandable ! text, failed CGPA explanation, academic form switching, Add Scholarship special textbox, rule editor and administrator logs.
- The actual project-local SQLite database was backed up, then upgraded. The pending existing multi-field migration was also applied.
- Record counts were unchanged: users 5; profiles 2; academic records 2; scholarships 21; rule records 20; saved scholarships 1; recommendation logs 20.
- SQLite integrity_check returned ok.
- Backup retained at storage/app/private/scholarmatch-before-decisions-20261019-021601-27456.sqlite.
- Verification environment: PHP 8.5.11 and SQLite. MySQL integration and the full manual/mobile checklist have not been executed.

### What the student must update for the FYP

1. Replace the recommendation methodology and flowchart with the new decision process.
2. Revise the academic-result and scholarship-rule data dictionaries/ERD with the added fields.
3. Capture current student/admin screens, especially the official-result form, Eligible ! disclosure and requirement explanation table.
4. Replace score-based test cases/results with the requirement-based cases and verified automated results.
5. Describe the latest-qualification scope and provider verification as limitations.

### Upgrade note

Existing students must update their academic form to confirm study status, required year, funding level and official results. These values were not guessed during migration. Existing scholarships and student records were preserved; old evaluations refresh when students open recommendations.

## IMP-004 — Clarify required form inputs

**Date:** 2026-10-19
**Type:** Form usability and guidance
**Status:** Completed

- Added red * markers to required academic inputs and the official-results confirmation, with screen-reader text. Existing conditional visibility and validation remain in place: SPM shows As/credits, other levels show CGPA, and current year is shown for Currently Studying.
- Added a required-field legend to student profile, academic-result and administrator scholarship Add/Edit forms. Existing profile and scholarship markers were retained; scholarship description is explicitly labelled optional.
- Clarified admin rule guidance: configure only provider restrictions; Diploma uses the CGPA route; academic minimums can be empty when unrestricted; year limits require Currently Studying and a maximum year requires a minimum.
- No new mandatory eligibility criteria or scoring were introduced. * means an input must be completed, while every configured scholarship condition still applies equally.

**FYP report updates:** Revise the form/interface design and user-guide explanation of required versus optional inputs. Replace academic-form and scholarship Add/Edit screenshots. Explain conditional SPM/CGPA/year inputs and the distinction between required form entries and optional administrator restrictions. DESIGN.md records the input-marker convention.

**Admin workflow discussion:** A future option is to show only the result inputs relevant to explicitly accepted qualifications, and show year controls only for Currently Studying. This visibility change has not been implemented as part of the marker update.

**Verification:** Existing student academic/profile and admin scholarship/rule feature tests passed: 57 tests, 201 assertions. git diff --check passed. No eligibility algorithm or validation rules changed in this update.

## IMP-005 — Add repeatable qualification boxes and alternative matching

**Date:** 2026-10-19
**Type:** Administrator workflow, database, recommendation logic and explanations
**Status:** Completed; supersedes the shared academic settings in IMP-003 and the proposed workflow in IMP-004

### Behaviour and reason for the change

An Undergraduate award can accept a Completed Diploma applicant with CGPA >= 3.00 OR a Currently Studying Undergraduate applicant with CGPA >= 3.30 in Years 1–3. The previous shared status/year/CGPA fields could not express these independent alternatives.

- Add/Edit Scholarship now includes general eligibility settings and an Add Qualification button. The same editor is used on Add/Edit Rules.
- Each repeatable box stores its own qualification level, study status, result minimums and current-year boundaries. Qualification and status have required * markers. Each box has Remove; removing a middle box keeps the remaining values associated correctly.
- SPM shows optional minimum As and credits and requires Completed. Other qualifications show optional minimum CGPA. Currently Studying reveals optional minimum/maximum years. Either status is available for non-SPM, including unrestricted legacy settings.
- Result minimums and year boundaries are optional independently. Both year bounds, when supplied, are inclusive and ordered. Zero is a real result minimum. Switching qualification/status hides, disables and clears inapplicable fields; the server also clears them.
- All conditions within a box use AND. Complete boxes use OR. Students must pass any one complete box plus every applicable general nationality, funded-level, income, field and institution requirement. Conditions from separate boxes cannot be combined.
- No boxes means no academic qualification/result/status/year restriction. Students still need complete official academic information before recommendations. Removing the last box clears old academic columns so old restrictions cannot return.
- Funded Study Level remains outside the boxes. The student still supplies only one latest qualification; historical qualifications are not inferred.
- Student and administrator-log explanations include the matched route (or closest relevant failing route), plus an expandable comparison of every alternative. Failing an unused alternative does not override a passing route. Eligible ! keeps its existing special-requirements meaning.
- Scholarship metadata, general requirements and route rows save in a transaction. Invalid routes prevent partial updates. An initialization marker prevents submissions without a working JavaScript editor from erasing routes.

### Database and implementation

- Added scholarship_qualification_routes: id, scholarship_rule_id, education_level, study_status, min_spm_as, min_spm_credits, min_cgpa, min_year_of_study, max_year_of_study and timestamps. A scholarship rule has many routes; deleting its scholarship/rule cascades to the route rows.
- Added migration 2026_10_19_010000_create_scholarship_qualification_routes_table.php, which backfills equivalent routes from existing settings and retains legacy columns. Existing imports/seeders have a legacy fallback; new form saves use route rows and clear old academic columns.
- Added ScholarshipQualificationRoute model, ScholarshipRuleEditor service and AcademicRouteEvaluator service; updated scholarship/rule controllers, route relationships and recommendation service.
- Added admin/rules/_qualification-routes.blade.php and qualificationEditor in resources/js/app.js; updated shared rule form, scholarship Add/Edit, rule summary and requirement explanation component.
- Algorithm version is decisions-v2-routes. Earlier cached results cannot be reused; previous logs remain identifiable until recommendations refresh.

### FYP documentation to revise

| Report area | Required change | Updated project reference |
| --- | --- | --- |
| Functional requirements / use cases | Add/remove multiple qualification boxes directly on Add/Edit Scholarship; separate general restrictions from alternatives | PRD.md sections 6.4–6.7 |
| Database / ERD / data dictionary | Add one-to-many scholarship_rules to scholarship_qualification_routes; document per-route nullable result/year fields and cascading relationship | PRD.md section 8; ARCHITECTURE.md sections 3.5 and 6 |
| Recommendation methodology / flowchart | General conditions AND any one complete qualification route; AND within each route; no mixing partial matches | PRD.md section 6.6; ARCHITECTURE.md section 4 |
| Interface / user guide | Screenshot Add/Edit with Diploma Completed and Undergraduate Currently Studying boxes; explain *, optional thresholds, conditional fields and Remove | DESIGN.md Administrator Qualification Boxes; screenshot storage/app/private/qualification-routes-preview.jpg |
| Testing / evaluation | Add alternative-route, same-level alternatives, partial-match rejection, middle-removal, stale-field clearing, transaction and migration cases | MANUAL_TEST_CASES.md section 10; new QualificationRoutesTest and engine tests |
| Scope / limitations | Continue to state that each student has one latest qualification and that provider award decisions/manual verification are external | PRD.md sections 9 and 14 |

**Suggested report wording:** “ScholarMatch represents accepted academic backgrounds as alternative qualification routes. Each route defines a qualification level, study status and optional result or current-year thresholds. A student must satisfy all applicable conditions within at least one route, together with the scholarship's general requirements. This supports scholarships that accept both completed Diploma applicants and current Undergraduate students under different academic conditions.”

### Verification and local database upgrade

- Automated suite passed: 185 tests, 584 assertions. Includes separate qualification thresholds/statuses, same-level alternatives, failure of incomplete routes, SPM As/credits, general-condition priority, transactional saves, server normalization, removal, initialization guard and legacy backfill.
- Laravel Pint passed and the Vite production build succeeded. git diff --check passed.
- Browser checks used a separate synthetic SQLite database: Add/Edit shared controls, SPM-to-Diploma switching, adding/removing a middle box, current-year controls/clearing, saving/reopening both routes, and Eligible ! with a passing Undergraduate route plus expanded alternatives.
- Before applying the route migration, created storage/app/private/scholarmatch-before-qualification-routes-20261019-110935.sqlite.
- Existing local database counts were preserved: users 5; profiles 2; academic records 2; scholarships 21; rules 21; saved scholarships 1; recommendation logs 30. Counts reflect the database at this upgrade and may differ from earlier log entries.
- Backfilled 94 qualification route rows; SQLite integrity_check returned ok. No sample preview records were inserted into the user's database.
- MySQL integration and the full manual/mobile checklist have not been executed.

## IMP-006 — Group programme details inside Academic Result

**Date:** 2026-10-20
**Type:** Student workflow, data ownership and migration
**Status:** Completed

- Moved Institution Type and Field of Study from Profile into Academic Result, grouped with Study Level Seeking Funding under Programme Seeking Funding. Both retain required * markers and describe the programme seeking funding, which may differ from the latest qualification supplying results.
- Profile now edits nationality, state, household income and dependents, and links to Academic Result for programme details. Its save endpoint ignores programme fields and cannot overwrite academic choices.
- Added required academic validation and completeness checks for shared supported institution/field options. Matching and explanations read academic_results; edits change the recommendation input hash immediately. Missing programme choices produce an academic-completion prompt.
- Added migration 2026_10_20_000000_move_programme_details_to_academic_results.php. It copies existing selections to existing academic records, keeps nullable legacy profile columns for recovery, and does not invent qualifications, results or academic records. Profile-only legacy choices prefill the first academic form. Rollback copies updated academic values back to existing profiles.
- Updated models/controllers, shared configuration, student/public views, test fixtures and recommendation checks. Existing qualification-route and special-warning logic is unchanged.

**FYP report updates:** Revise Profile and Academic Result functional requirements/user guides, form screenshots, data dictionaries and ERD field ownership. Institution Type and Field of Study now belong to academic_results; legacy profile columns are inactive compatibility data. Describe Programme Seeking Funding separately from the qualification supplying results. PRD.md sections 6.2–6.3, 8–9 and 11; ARCHITECTURE.md sections 3.2–3.3 and 6; DESIGN.md, README.md and MANUAL_TEST_CASES.md were updated.

**Verification:** 196 tests passed with 622 assertions; Vite production build passed. PHP formatting checked. A full-suite run initially encountered a Windows lock in the shared compiled-view cache; rerunning with a separate test view folder passed. Browser checks with synthetic data confirmed Profile saves without programme fields, the academic section displays all three choices, and changed institution/field selections save and reopen correctly. Screenshot: storage/app/private/programme-details-preview.jpg. MySQL integration and the full mobile checklist were not run.

**Local database:** Backed up to storage/app/private/scholarmatch-before-programme-details-20261020-19aa1d9b.sqlite before migration. All original academic values and record counts were preserved: users 5; profiles 2; academics 2; scholarships 21; rules 21; qualification routes 94; saved scholarships 1; logs 30. Both existing academic records received the matching saved programme choices. SQLite integrity_check returned ok.

## IMP-007 — Give View Details a visible button border

**Date:** 2026-10-20
**Type:** Interface consistency
**Status:** Completed

Changed View Details in the shared scholarship-card component from the borderless ghost style to btn-outline, matching Save. Apply Now remains the filled primary action. The border, padding, corner radius, hover and focus treatment come from the existing shared button styles; destination and behaviour are unchanged. This applies wherever the shared card appears, including the dashboard and recommendations.

**FYP report updates:** Replace scholarship-card/action screenshots in the interface design or user guide. DESIGN.md Scholarship Cards and MANUAL_TEST_CASES.md REC-21 describe the outlined View Details action. No algorithm or database change is associated with this styling update.

**Verification:** Browser preview confirmed a visible 1px border matching Save, and clicking View Details still opens the scholarship. Screenshot: storage/app/private/view-details-button-preview.jpg. The styling reuses existing compiled button CSS; no additional automated test was added for the class-only change.

## IMP-008 — Position card badges and colour requirement explanations

**Date:** 2026-10-20
**Type:** Interface layout, visual emphasis and accessibility
**Status:** Completed

- Moved Eligible / Eligible ! / Not Eligible to the top-right corner of the shared card header. Titles/providers wrap on the left without overlapping the badge, including narrow recommendation-grid cards.
- Preserved the Eligible ! disclosure as a keyboard-accessible, bounded amber panel anchored below the top-right badge. It retains the existing special text and verification meaning.
- Card explanations now use pale green panels for Met and pale red panels for Not Met. Labels, required values and student information receive stronger emphasis. Award type uses a blue-tinted chip and the deadline date is bold.
- Shared requirement tables use green met rows, red unmet rows and neutral Not Applicable rows. Applicable required/student values are bold; student values and result pills use darker outcome colours. Added a shared requirement-status component with check/cross/dash and explicit labels, preserving meaning without relying on colour alone.
- Alternative-route tables and administrator recommendation-log explanations inherit the shared styling. The detail page highlights award/deadline information, failed requirements and amber special conditions.
- Changed presentation only. Academic routes, AND/OR evaluation, thresholds, decisions and database records are unchanged. All displayed values and special text remain escaped.

**FYP report updates:** Replace dashboard/recommendation-card screenshots and scholarship/log detail screenshots. Describe badge placement, required-versus-student-value emphasis and the green/red/neutral outcome convention in interface design and the user guide. DESIGN.md Scholarship Cards, Badges & Chips, and Academic Form and Requirement Explanations were updated; MANUAL_TEST_CASES.md REC-22–REC-24 cover the new presentation.

**Verification:** Existing dashboard, recommendation and administrator-log tests passed: 27 tests, 104 assertions. Vite production build and git diff --check passed. Synthetic browser checks confirmed the top-right badge on wide and 310px cards without title overlap, keyboard activation of the ! disclosure, green expanded card explanations, and red/green/neutral detail rows with bold results. Screenshots: storage/app/private/recommendation-card-colours.jpg and storage/app/private/requirement-result-colours.jpg. No new automated tests were added for the presentation-only changes, and no live student data was modified for previewing.

## IMP-009 — Simplify the card explanation list

**Date:** 2026-10-20
**Type:** Readability refinement
**Status:** Completed; replaces the expanded card panels from IMP-008

Restored View Explanation to one plain message per applicable check, with a bold green Met or red Not Met label. Removed the individual boxes, separate status pills, repeated requirement headings and duplicated required/student value rows. Longer messages wrap naturally. The top-right eligibility badge, highlighted award/deadline and colour-coded detail/log tables remain in place; recommendation logic is unchanged.

**FYP report updates:** Replace any screenshots of the expanded card-panel design with the simplified explanation list. Update the card interaction/user-guide description: View Explanation is a quick summary, and View Details provides the full comparison table. DESIGN.md Scholarship Cards and MANUAL_TEST_CASES.md REC-25 reflect the final layout.

**Verification:** Existing dashboard and recommendation tests passed: 21 tests, 76 assertions. Vite production build and git diff --check passed. A synthetic browser preview confirmed the plain, wrapping green explanation lines, bold outcome labels and retained top-right badge. Screenshot: storage/app/private/simple-explanation-preview.jpg. No recommendation rules or live student records were changed.

## IMP-010 — Simplify required-field notes and fix income input overlap

**Date:** 2026-10-20
**Type:** Form readability and layout correction
**Status:** Completed

- Removed the separate “* Required field.” note from Profile, Academic Result and administrator Add/Edit Scholarship forms. Required label stars, accessibility text and validation remain in place. The academic form retains its useful explanation about conditional result/year fields.
- Removed the overlaid RM prefix and its special padding from Household Income. The currency remains clear in the Household Income (RM) label, while the input shows only the numeric amount without overlap.

**FYP report updates:** Refresh Profile and affected form screenshots in the interface design/user guide. Describe required fields using their label stars and household income as a numeric amount with the currency in its label. DESIGN.md Input Fields reflects this update. No database, income classification or recommendation logic changed.

**Verification:** Existing Profile, Academic Result and administrator Scholarship tests passed: 51 tests, 177 assertions. Browser preview with synthetic income 3000.00 confirmed a readable numeric amount and the removed top note. Screenshot: storage/app/private/profile-income-layout-preview.jpg. MANUAL_TEST_CASES.md S10–S11 cover the layout and required-label behaviour. No new automated tests were added for this presentation-only change; git diff --check passed.

## IMP-011 — Align the Academic Result programme section divider

**Date:** 2026-10-20
**Type:** Form layout correction
**Status:** Completed

Moved the Programme Seeking Funding divider from the fieldset onto an outer wrapper. The line now spans the content width above the heading instead of being interrupted by the legend. Removed the legend's extra top padding while preserving the accessible fieldset grouping, section spacing, inputs and validation.

**FYP report updates:** Refresh the Academic Result screenshot in interface design/user-guide sections to show the full-width divider above Programme Seeking Funding. DESIGN.md Academic Form and Requirement Explanations describes the corrected layout. No data or eligibility logic changed.

**Verification:** Existing Academic Result tests passed: 26 tests, 88 assertions. Synthetic browser previews confirmed the continuous divider and readable section spacing at desktop and 390px widths. Screenshots: storage/app/private/academic-divider-preview.jpg and storage/app/private/academic-divider-mobile-preview.jpg. MANUAL_TEST_CASES.md AR-15 covers the visual check. Existing CSS utilities were reused; git diff --check passed.

## IMP-012 — Add recommendation filters, full-width cards and 10-item pages

**Date:** 2026-10-20
**Type:** Scholarship browsing functionality and responsive layout
**Status:** Completed

- Added a desktop filter sidebar for scholarship/provider search, award type, funded study level, supported field and institution type. Search ignores case; filters combine with AND. Unrestricted programme criteria also match a selected programme choice, and field filtering supports multiple accepted fields.
- Replaced the small multi-column card grid with one full-width card per row, reusing the Dashboard card component. Filters stack above the list on narrow screens.
- Eligible (including !) and Not Eligible use GET navigation with filtered totals. Each selected group displays at most 10 cards, with result ranges, current page, numbered links and previous/next controls. Filters/group persist across pages; applying/resetting filters or changing group starts at page 1. Stale high page values resolve to the last available page.
- Empty groups retain the filter controls, Reset and helpful guidance. Query values are validated and displayed text remains escaped. The results anchor respects the sticky header.
- Filtering runs after evaluation/cache retrieval and preserves existing ordering. It does not alter student data, scholarship rules or eligibility outcomes. Evaluation logs still cover all evaluated scholarships, including those hidden by filters or pagination; Dashboard behaviour is unchanged.

**FYP report updates:** Add recommendation filtering/pagination to functional requirements and browsing use cases. Refresh recommendation-page screenshots to show the sidebar, single-column list and page controls. Explain unrestricted filter matching, filtered group totals and preserved selections in the interface design/user guide. PRD.md 6.7, ARCHITECTURE.md 5, DESIGN.md and MANUAL_TEST_CASES.md REC-26–REC-30 describe the implementation. No database migration is required.

**Verification:** Recommendation filter, existing recommendation and Dashboard tests passed: 36 tests, 160 assertions. PHP formatting, Vite production build and git diff --check passed. Browser checks using a separate synthetic database confirmed combined filters, 10-card pages, remaining results on page 2, preserved query parameters, group/page resets, empty results and Reset. A 390px preview confirmed filters stack above readable cards. Screenshots: storage/app/private/recommendation-filters-preview.jpg, recommendation-page-two-preview.jpg and recommendation-filters-mobile-preview.jpg. Live student/scholarship data was not used for the preview.

## IMP-013 — Limit nationality to Malaysian or Non-Malaysian

**Date:** 2026-10-20
**Type:** Shared form choices, validation and eligibility matching
**Status:** Completed

- Reduced the shared nationality choices to Malaysian and Non-Malaysian for Profile and all scholarship/rule forms. The student placeholder remains unanswered; administrators can still use Any nationality for an unrestricted requirement.
- Profile and rule validation reject individual-country and Other values. Matching compares the two supported categories exactly, while an unrestricted scholarship accepts either. Removed the request for a specific country, since that is no longer an available input.
- Unsupported legacy student nationality values now request a valid Profile selection before evaluation. Unsupported country-specific legacy scholarship rules remain intact and awaiting configuration rather than being silently broadened or blocking other awards. Country-specific conditions can be entered as Special Requirements for provider verification.
- Advanced the evaluation version to decisions-v3-nationality so old cached results and logs refresh under the current rules. Academic-route evaluation, special-warning meaning and list filters/pagination remain in place.
- Read-only audit of the existing local SQLite database found 2 student profiles and 21 scholarship rules, all using Malaysian. No data conversion, schema migration or live-record modification was required.

**FYP report updates:** Update the Nationality input definition and screenshots to show Malaysian/Non-Malaysian only. Explain exact-category matching and the optional unrestricted scholarship setting; place country-specific conditions under special verification. Update the current algorithm version in implementation/cache/log descriptions. PRD.md 6.2, ARCHITECTURE.md, README.md, DESIGN.md and MANUAL_TEST_CASES.md S12–S13 reflect the changes.

**Verification:** Full suite passed: 226 tests, 756 assertions, including exact-category/unrestricted matching, unsupported legacy values, profile/rule validation, and the new filters/pagination. PHP formatting and git diff --check passed; the frontend production build from IMP-012 remains current. A synthetic browser preview confirmed the Profile dropdown contains only its placeholder, Malaysian and Non-Malaysian, and that Non-Malaysian can be selected. Screenshot: storage/app/private/nationality-options-preview.jpg.

## IMP-014 — Agree on grade-count entry and manual subject-specific conditions

**Date:** 2026-10-31
**Type:** Academic-input design decision
**Status:** Implemented in IMP-015 below; this entry retains the original design decision.

- For the proposed grade-based qualification forms, students enter the number of subjects at each grade using minus/plus counters or direct numeric entry. The summary and relevant totals are calculated from those counts. Individual subject names and grades are outside this design.
- Administrators record named-subject conditions, such as Mathematics at least C or English at least B, in the scholarship's existing Special Requirements textbox. Grade counts cannot establish which grade belongs to a particular subject, so these conditions must not be displayed as automatically met.
- Existing decision meanings remain: passing automatic checks with non-empty Special Requirements produces Eligible ! for provider verification; an automatic failure produces Not Eligible. The ! does not confirm that the subject-specific condition has been satisfied.
- The earlier official-results-only policy remains the proposed default. This decision concerns grade-based results; existing CGPA inputs and the proposed STAM pangkat input retain their own result formats. The complete new qualification list and its automatic threshold rules still need to be finalised before implementation.

**FYP report updates:** Record the input-design decision and scope in the proposed interface/system design: aggregate grade counts support general academic thresholds, while named-subject restrictions are explained as provider-verification conditions. Update implementation descriptions, form screenshots, data dictionaries and tests when this design is implemented; no new result-entry behaviour is claimed by this documentation-only entry.

## IMP-015 — Implement grouped qualifications, official grade counts and matching rules

**Date:** 2026-10-31
**Type:** Student academic entry, administrator rules, matching, migration and documentation
**Status:** Implemented and verified locally

**Student changes**

- Replaced the single qualification dropdown with category buttons followed by qualification buttons: Secondary (SPM, Cambridge IGCSE, Cambridge O-Level), Pre-U, Diploma & Pathway (STPM, STAM, A-Level, Senior UEC, Matriculation, Foundation, Diploma), and Undergraduate (Bachelor’s Degree). Categories organise the interface; they do not imply qualification equivalence.
- Added minus/number/plus grade counters, direct whole-number entry, a live grade summary and total subjects for SPM, IGCSE, O-Level, A-Level and Senior UEC. SPM As count A+/A/A- and credits count C or above automatically. Students no longer enter As/credits separately. No subject names or individual subject results are collected.
- Supported IGCSE letter grades and 9–1 as separate, explicitly selected scales, with no conversion. Full A-Level uses A*–U; Senior UEC uses A1–F9. STAM uses the official overall pangkat from Mumtaz through Rasib. Existing STPM/Foundation/Matriculation/Diploma/Bachelor’s Degree results remain CGPA-based.
- All exam-based qualifications require Completed and official final results. Currently studying CGPA students provide current year and official cumulative CGPA; completed students provide final CGPA. Forecast/expected/pending results and AS-Level are not accepted. Changing category, qualification, scale or status clears incompatible form values and official confirmation.
- Kept Programme Seeking Funding separate, with funding level, institution type and field. Friendly labels preserve existing stored identifiers: Bachelor’s Degree = Undergraduate, Senior UEC = UEC, Cambridge O-Level = O-Level.
- Added server validation for whole non-negative counts, recognised grades, matching scales, at least one subject and supported totals. Entry caps are 12 SPM subjects and 20 for the other supported grade-count formats; these are application limits, not examination-board maxima.

**Administrator and recommendation changes**

- Extended shared Add/Edit Scholarship and Rules qualification boxes with conditional grade requirements, exact IGCSE scale, minimum STAM overall rank and optional UEC best-subject average settings. Existing SPM As/credits, CGPA and current-year limits remain available where applicable. Incompatible values are cleared by both the editor and server.
- Each grade condition means a minimum number of subjects at a grade or better. AAB is represented by 2 subjects at A or better AND 3 at B or better. Every condition inside a box uses AND; complete alternative boxes use OR. General nationality, funding, income, field and institution conditions still apply.
- UEC grade ordering treats A1 as strongest. An explicitly configured average selects the best N subjects meeting the minimum grade, maps A1=1 through F9=9 and requires an average no higher than the maximum. Too few qualifying subjects fails; lower is better. Maximum thresholds allow two decimal places; actual averages are compared without rounding into a pass. Other provider conversion formulas remain special conditions.
- Named-subject conditions such as Mathematics at least C belong in Special Requirements. Passing automatic checks with special text gives Eligible ! for provider verification; the system does not claim that the named subject was verified. An automatic failure remains Not Eligible. Existing application-date/provider warning and Apply/Check Provider behaviour is retained.
- Fixed a browser-discovered edit issue: saved grade dropdowns now restore their selected values after dynamic options render. Adding/removing conditions retains the other boxes’ values and stable identities.

**Data and implementation**

- Added the shared AcademicQualifications catalogue, the academic-qualifications.js student/admin editors and migration 2026_10_31_000000_add_qualification_grade_counts. Student fields: grading_scale, grade_counts JSON and stam_rank. Route fields: grading_scale, grade_requirements JSON, min_stam_rank and uec_average JSON. The academic qualification enum now accepts eleven qualifications.
- Preserved existing SPM aggregate columns for reference/compatibility; new submissions derive them from real counts. Old aggregates cannot reveal a student's grade distribution, so migration invents no breakdown. Legacy SPM students see their saved totals and must enter actual counts before recommendations; valid existing CGPA records continue working.
- Migration preserves existing records and values; rollback refuses to discard new-format result/rule data. Advanced the matcher version to decisions-v6-grade-counts to refresh prior caches/logs. Legacy CGPA inference remains limited to the original five CGPA qualifications.
- The stock 70-entry fictional demo catalogue was not replaced or expanded; it still covers the original six qualifications. New qualification cases have dedicated tests, and administrators can add their rules through the editor.

**Where to update the FYP report**

| Report area | Required revision | Project reference |
| --- | --- | --- |
| Functional requirements and scope | Eleven supported qualifications, grouped selection, official-result gate, aggregate counts only, subject-specific provider checks | PRD.md sections 6.3 and 6.5; docs/ACADEMIC_QUALIFICATIONS.md |
| Use cases and activity diagrams | Category → qualification → applicable result input → official confirmation → save; admin adds alternative qualification boxes | DESIGN.md Academic Form and Administrator Qualification Boxes |
| Interface design and user guide | Replace student/admin screenshots; explain counters, IGCSE scale selection, STAM rank, CGPA/year and separate funded programme | Screenshots listed below; DESIGN.md |
| Data dictionary and ERD | Add the student and route fields above, extend education_level values, describe category as derived and aggregates as calculated | PRD.md section 8; ARCHITECTURE.md sections 3.3, 3.5 and 6 |
| Algorithm/pseudocode | Grade ordering/count-at-or-above, AAB example, exact IGCSE scale, STAM direction, best-N UEC average, AND within/OR between routes | docs/ACADEMIC_QUALIFICATIONS.md; AcademicRouteEvaluator |
| Testing and evaluation | Add valid/invalid counts, persistence, threshold boundaries, scale changes, admin edit restoration, migration preservation and mobile checks | MANUAL_TEST_CASES.md AR-16–AR-25 and RULE-17–RULE-25; new PHP/Node tests |

Updated README.md, PRD.md, ARCHITECTURE.md, DESIGN.md, MANUAL_TEST_CASES.md and .ai-handoff.md. Added [the academic format and matching reference](docs/ACADEMIC_QUALIFICATIONS.md). This log is the change summary for the FYP report and GitHub description.

**Verification:** Full PHP suite passed: **385 tests, 1,793 assertions**. All **6 JavaScript tests** passed via npm run test:academic. Vite production build, targeted Pint formatting checks and git diff whitespace checks passed. Synthetic browser checks confirmed saved Bachelor’s Degree values, SPM counter totals/save/reload, UEC save/reload, IGCSE scale switching/reset, STAM save/reload, responsive 390px layout, and administrator saved grade options/add/remove/full-form submission. No synthetic records were inserted into the real local database.

Screenshots retained for the report: storage/app/private/uec-grade-counts-preview.jpg (current student result interaction), storage/app/private/uec-grade-counts-full-preview.jpg (full form), storage/app/private/admin-grade-requirements-preview.jpg (AAB/UEC rules) and storage/app/private/grade-counts-mobile-preview.jpg (390px layout).

**Local database:** Backed up before migrating to storage/app/private/scholarmatch-before-grade-counts-20261031-b7e505dc.sqlite. Original values and record counts were compared and preserved: users 5; profiles 2; academic results 2; scholarships 71; rules 71; qualification routes 114; saved scholarships 0; recommendation logs 71. SQLite integrity_check returned ok. One existing SPM record needs its actual grade breakdown; no grades or student results were fabricated. No catalogue reseed/replacement was performed.

**Current limits:** One latest academic record and one IGCSE scale per record; no subject-level automatic checks, cross-qualification equivalence or automatic grade-scale conversion. AUSMAT, IB Diploma, CIMP, AS-Level and unspecified qualifications are outside this implementation. SQLite migration/testing passed; MySQL integration was not run. Changes remain in the working tree; no commit or push was made.

## IMP-016 — Add explicit requirement types and STPM grade/subject-point checks

**Date:** 2026-10-31
**Type:** Academic data, administrator input, eligibility calculations and user guidance
**Status:** Implemented and verified locally

**What changed**

- Replaced separate result panels in the shared Add/Edit Scholarship and Rules editor with **Add Condition → Requirement Type**. Offer supported choices only: minimum overall CGPA, minimum grade count, SPM As/credits, STAM pangkat, STPM minimum best-subject average, and UEC maximum best-subject average or total. Each added condition must be complete. Non-grade types have one setting per box; grade thresholds can repeat at different grades.
- Added a live plain-language explanation for each condition and a cumulative minimum grade-pattern preview. A-Level 1 subject at A or better AND 3 at B or better displays A, B, B, because the A already counts towards the three. UEC 2 As including A1/A2 uses 2 subjects at A2 or better. STPM 1A uses A, or A- only when the provider explicitly accepts it. The interface includes qualification-specific examples.
- STPM students now enter both official overall CGPA and grade counts using A, A-, B+, B, B-, C+, C, C-, D+, D and F. The application cap is five subjects. Official CGPA is entered from the result record; it is never reconstructed from unnamed counts. Currently studying students retain status/year and official latest results; completed students use final results. Changing status clears the form's old STPM grades/CGPA/confirmation.
- Added a minimum average across the best N eligible STPM subjects using official subject points (A=4.00 through F=0.00). This is separate from overall CGPA and from requiring N subjects each at a grade threshold. Example: A/B/C/C has a best-three average of 3.00, but only two B-or-better subjects. Too few qualifying subjects fails; compare actual averages without rounding into a pass. Average thresholds support two decimal places and a real zero minimum.
- Added a direct UEC maximum-total option. A confirmed best-five total of 11 or below can be entered as N=5 and maximum=11, without the previous average conversion. A1/A1/A2/B3/B4 totals 11. Lower UEC points are better. Administrators specify a grade cutoff, or the lowest grade when no extra cutoff is stated. Integer totals must lie between N and 9N; too few qualifying subjects fails.
- Kept AND within a qualification box and OR between complete boxes. General requirements still apply. Existing saved thresholds reopen as typed rows without changing their meaning. Adding/removing conditions retains other rows' stable values; changing type or qualification clears incompatible values and disables irrelevant controls.
- Retained provider verification for unclear wording, named-subject calculations and unsupported conversions. “3.0 in 3 subjects” must be clarified as an average versus a minimum in each subject. Southern UC's A-Level CGPA 3.20 wording provides no conversion formula, so it stays in Special Requirements. Generic A-Level CGPA conversion is not offered. UEC “minimum 11 points” also needs its comparison direction confirmed before using an automatic maximum.

**Data and compatibility**

- Reused grading_scale/grade_counts for STPM and kept existing min_cgpa, SPM minima, grade_requirements, min_stam_rank and uec_average storage. Requirement Type is editor state that generates names for these validated fields, not a free-text rule parser or a new independent score.
- Added nullable route JSON fields stpm_average={subjects, minimum_grade, min_average} and uec_total={subjects, minimum_grade, max_total} through migration 2026_10_31_010000_add_subject_point_requirements. Existing rows/values are preserved; rollback refuses to discard new point rules. Advanced the matcher/cache/log version to decisions-v7-academic-requirement-types.
- Legacy STPM CGPA is preserved, but actual subject counts are now needed before recommendations. Missing counts prompt profile completion rather than Not Eligible or Eligible !. No real grades are invented. Other qualifications and the existing 70-entry fictional catalogue retain their records and rules.

**FYP report updates**

| Report area | What to change |
| --- | --- |
| Requirements/scope | STPM now has CGPA plus grade counts; admins select supported condition types and can express UEC totals directly. Explain unsupported formulas and named-subject verification. |
| UI design/user guide | Replace Add/Edit Scholarship, Rules and STPM screenshots. Describe Add Condition, conditional inputs, live explanations and the cumulative ABB pattern. |
| Use cases/activity diagrams | Add condition → choose type → enter applicable values → review preview → validate/save; student supplies official STPM CGPA and counts. |
| Data dictionary/ERD | Add stpm_average and uec_total JSON to qualification routes; describe STPM use of existing count/scale fields and keep overall CGPA separate. |
| Algorithm/pseudocode | Add exact best-N selection, STPM grade-point averaging versus per-subject minima, UEC maximum totals, insufficient-subject failure and unrounded comparisons; retain AND/OR structure. |
| Evaluation/testing | Add STPM 1A/A- boundaries, average-versus-overall-CGPA examples, UEC total 11 boundaries, validation, saved-type restoration, migration preservation, Add/Edit submission and mobile cases. |

Current references: [requirement-type guide](docs/ACADEMIC_REQUIREMENT_TYPES.md), docs/ACADEMIC_QUALIFICATIONS.md, PRD.md and ARCHITECTURE.md version 3.8, DESIGN.md and MANUAL_TEST_CASES.md (AR-26–AR-28, RULE-26–RULE-33). README.md and .ai-handoff.md were updated.

**Verification:** Full PHP suite passed **416 tests / 1,906 assertions**; all **10 JavaScript tests** passed. Production Vite build, targeted Pint and git diff whitespace checks passed. Synthetic browser checks confirmed: STPM grade counts plus official CGPA save/reload; creation and full edit submission of typed rules; restored old UEC average and new maximum-total values; condition removal/type switching; and student/admin layouts at 390px without horizontal page overflow. A prior HTML-label assertion was updated to the new Requirement Type/Add Condition controls. Tests and previews use separate databases and compiled-view folders; no synthetic records were added to the user's database.

Report screenshots: storage/app/private/admin-stpm-requirement-types-preview.png; storage/app/private/admin-uec-total-points-preview.png; storage/app/private/student-stpm-cgpa-grades-preview.png; storage/app/private/student-stpm-mobile-preview.png; storage/app/private/admin-requirement-types-mobile-preview.png.

**Local database:** Created a full backup at storage/app/private/scholarmatch-before-requirement-types-20261031-289d47ff.sqlite, applied the additive migration, and compared all original values/counts. Preserved users 5; profiles 2; academic records 2; scholarships 71; rules 71; qualification routes 114; saved items 0; recommendation logs 71. SQLite integrity_check returned ok. There are no existing STPM records needing a breakdown locally. No reseed or catalogue replacement was performed.

**Limits:** One latest academic record, aggregate grades only, no named-subject automatic checks or undocumented conversions. Best-N formulas apply only where the provider confirms that any qualifying subjects may be used; specific subject inclusions/exclusions require verification. MySQL integration was not run. No commit or push was made.

## IMP-017 — Group grade thresholds under one requirement-type selector

**Date:** 2026-10-31
**Type:** Administrator form layout and interaction
**Status:** Implemented and verified locally

- Combined all “Number of subjects at a grade or better” thresholds in each qualification box into one requirement group. Its Requirement Type dropdown appears once, with compact Minimum Subjects / At Grade or Better rows below. For ABB, the rows show 1 at A and 3 at B.
- Added **Add Grade Row** inside the group and renamed the outer button to **Add Another Requirement** for CGPA, rank, average or total-point types. Only one grade-count group is offered per qualification; up to 20 grade rows fit inside it. Other requirement types remain separate.
- Each row has a Remove action. Removing the first/middle row preserves the other values and stable identities. Removing the final row removes only the grade group; CGPA/average settings stay intact. Remove this requirement deletes the entire chosen group. Changing the group to another type clears its old grade rows.
- Replaced repeated per-grade explanations with one combined minimum-pattern preview. An incomplete row prompts completion; numeric zero minima are preserved. Every row still uses AND, and stronger grades still count towards lower thresholds. Separate qualification boxes remain OR alternatives.
- Existing flat grade_requirements lists automatically reopen as grouped rows and are submitted using the same validated flat field names. No schema migration, matcher/version change, real database modification or catalogue reseed was needed.

**FYP report updates:** Refresh Add/Edit Scholarship and Rules screenshots and the admin user guide. Explain the distinction between adding a grade row and adding another requirement type, the per-row/group removal actions, the single combined preview and responsive layout. Update the requirement-entry activity diagram to include repeated rows inside one grade group. The data dictionary, ERD and eligibility algorithm retain their existing structure and semantics. Current references: DESIGN.md Administrator Qualification Boxes; PRD.md section 6.5; ARCHITECTURE.md section 3.5; docs/ACADEMIC_REQUIREMENT_TYPES.md; MANUAL_TEST_CASES.md RULE-34–RULE-37. README.md, the academic-format guide and .ai-handoff.md were updated.

**Verification:** Full PHP suite passed **416 tests / 1,908 assertions**; all **14 JavaScript tests** passed. Production Vite build, affected PHP formatting and git diff whitespace checks passed. Synthetic browser checks confirmed old ABB thresholds load as one group, first-row removal preserves the other value, final-row removal preserves separate CGPA/average requirements, a new scholarship can be created with two grouped rows, and full Edit Scholarship submission saves/reopens the same minima. At 390px, the two columns and removal controls remained usable without horizontal page overflow. Preview accounts/data were isolated from the real database, and temporary preview files were removed.

**Screenshots:** storage/app/private/admin-grouped-grade-rows-preview.png and storage/app/private/admin-grouped-grade-rows-mobile-preview.png. Previous IMP-016 screenshots describe the earlier ungrouped layout; use these new screenshots for the grade-count section. No commit or push was made.

## IMP-018 — Calculate completed STPM overall CGPA using Pengajian Am

**Date:** 2026-10-31
**Type:** Student result entry, validated calculation and data preservation
**Status:** Implemented and verified locally

**Changes**

- Completed STPM students enter four/five official final subject results using the existing grade counters, then select the **Pengajian Am Grade** already included in those counts. This is the explicitly approved exception to aggregate-only entry; other subject names are not collected and subject-specific scholarship conditions remain Special Requirements.
- Added a read-only **Calculated Overall CGPA** using Pengajian Am plus the strongest three other subjects, divided by four. PA is removed once from a working copy before choosing the other subjects, so it is never counted twice or dropped for a stronger extra subject. Example: four As and PA=C gives (2+4+4+4)/4 = 3.50. With the same counts and PA=A, the strongest four included grades give 4.00.
- PHP and JavaScript share the STPM point map. They calculate in integer hundredths and round half up to two decimals consistently (e.g. 2A/2A- gives 3.84). A real calculated 0.00 remains valid.
- Added a required confirmation that the calculated value matches the official STPM slip. Count edits through typing/counters or changing PA clear the confirmation. Missing/uncounted/unsupported PA grades, incomplete result codes, insufficient/excessive final subjects, unconfirmed values and pending results cannot save. The controller excludes a submitted completed-STPM cgpa value and recalculates on the server, preventing a forged client value from inflating eligibility.
- Currently studying STPM retains manual official cumulative CGPA, official subject counts and current year. Switching status/qualification clears incompatible PA/calculation-confirmation data. The overall calculation remains separate from provider-defined best-N scholarship averages; the grouped administrator grade editor and AND/OR logic are unchanged.

**Data and upgrade**

- Added migration 2026_10_31_020000_add_stpm_cgpa_calculation with academic_results.stpm_pa_grade (nullable string) and stpm_cgpa_confirmed (boolean, default false). Existing cgpa, counts and other values are preserved. No PA identity or grade is guessed, and migration does not recalculate prior records. Rollback refuses to discard stored PA/confirmation data.
- Existing usable official manual STPM records with valid counts remain usable until edited, when the completed-result form requires PA and calculation confirmation. Older STPM records missing counts still follow the earlier completion prompt. New calculation records require confirmation and consistency of stored counts/PA/CGPA before recommendations.
- Advanced the evaluation version to decisions-v8-stpm-cgpa so caches/logs refresh under the new input workflow. No scholarship catalogue reseed/replacement was performed.

**Where to update the FYP report**

| Area | Required update |
| --- | --- |
| Functional requirements/scope | Completed STPM calculates overall CGPA; studying STPM stays manual. Explain the single PA identification exception and official-slip confirmation. |
| Use case/activity diagram | Branch on studying/completed; completed: final counts → PA selection → calculation → match confirmation → server validation/save. |
| UI design and user guide | Replace completed-STPM screenshots with the PA dropdown, read-only CGPA and confirmation; explain that PA is already included in the counters. |
| Data dictionary/ERD | Add the two academic_results fields; describe cgpa as derived for new completed STPM submissions and manual in the supported other cases. |
| Algorithm/pseudocode | Validate 4–5 final subjects, reserve PA once, select best three other grades, use published points, divide by four and round half up. Separate this from scholarship-specific averages. |
| Testing/evaluation | Include weak-PA inclusion, 4/5 subjects, zero and rounding boundaries, invalid data, confirmation reset, forged CGPA, manual studying, legacy preservation and rollback protection. |

Added [STPM_CGPA_CALCULATION.md](docs/STPM_CGPA_CALCULATION.md); updated PRD.md/ARCHITECTURE.md to version 3.9, DESIGN.md, README.md, the qualification/requirement-type guides, MANUAL_TEST_CASES.md AR-29–AR-36, public About text and .ai-handoff.md.

**Verification:** Full PHP suite passed **441 tests / 2,087 assertions**; all **18 JavaScript tests** passed. Production Vite build, changed-PHP Pint checks and git diff whitespace checks passed. Synthetic browser checks confirmed PA=C produces 3.50 for four As/one C, the value is read-only, count edits clear confirmation, insufficient results and an uncounted PA grade leave no calculable value, confirmed results save/reload with PA and confirmation restored, and currently studying STPM still saves/reloads manual 3.20. Both desktop and 390px layouts were checked without horizontal page overflow. Preview data used a separate SQLite database and compiled-view folder.

**Local database:** Created a full backup at storage/app/private/scholarmatch-before-stpm-cgpa-20261031-f329f032.sqlite before the additive migration. All original column values and counts were compared and preserved: users 5; profiles 2; academic results 2; scholarships 73; rules 73; qualification routes 126; saved items 0; logs 73. These are the database totals at this upgrade. SQLite integrity_check returned ok. The existing STPM record was preserved with no invented PA grade or recalculated CGPA. No catalogue replacement/reseed occurred.

**Screenshots:** storage/app/private/student-stpm-auto-cgpa-preview.png and storage/app/private/student-stpm-auto-cgpa-mobile-preview.png.

**Limits:** This is for complete final STPM subject grades, with four/five subjects including PA; it does not infer final results from semester marks or generate PNGK for fewer than four subjects/incomplete codes. The student must check the calculation against the official slip. Other subject-specific scholarship checks remain manual. SQLite tests/migration passed; MySQL integration was not run. No commit or push was made.

## IMP-019 — Improve login, registration and password recovery navigation

**Date:** 2026-10-31
**Type:** Authentication interface and usability
**Status:** Implemented and verified locally

**Changes**

- Added a full-width **Register | Login** switch at the top of both account forms. Register is on the left, Login on the right, and the current destination is highlighted in Primary Blue with aria-current="page". The links navigate to the existing Breeze routes. Removed the redundant Already registered? link.
- Added page headings, consistent input styling and full-width blue submit buttons. Login places Remember me and Forgot password? in a row that wraps on narrow screens.
- Added **Back to Login** above Forgot Password and Reset Password. On Forgot Password the link stays visible before/after a reset request and alongside validation/status messages. Shortened the explanatory text and labelled the action Send Reset Link.
- Added an eye button to Login Password, Register Password/Confirm Password and Reset Password Password/Confirm Password. Fields start hidden and toggle independently. Buttons have type="button", keyboard focus, Show password/Hide password labels, aria-controls and aria-pressed. Space activates a focused control without submitting the form. Input padding reserves room for the icon.
- Navigation uses normal links. Eye controls use the existing Alpine runtime and are hidden until it initialises; masked password inputs remain usable without JavaScript. Existing field names, required/autocomplete attributes, CSRF inputs, validation errors and reset tokens are preserved.

**Implementation files**

- resources/views/auth/login.blade.php, register.blade.php, forgot-password.blade.php and reset-password.blade.php.
- New shared components: resources/views/components/auth-switch.blade.php, password-input.blade.php and back-to-login.blade.php.
- No controller, authentication policy, password hashing, database schema or recommendation logic changes were needed.

**Where to update the FYP report**

| Area | Required update |
| --- | --- |
| Functional requirements | Users can switch Login/Register, return to Login during recovery and reveal/hide each password field. |
| Interface design and screenshots | Replace the Login, Register, Forgot Password and Reset Password screens; explain the blue active switch and eye controls. |
| User guide/use cases | Describe switching account forms, toggling password visibility and returning from password recovery. |
| Testing/usability evaluation | Add switch navigation, independent/default-hidden controls, keyboard operation, recovery return before/after status messages and mobile layout cases. |
| Data dictionary/ERD | No change for this interface update. |

Updated PRD.md section 6.1, DESIGN.md Authentication Cards, README.md, MANUAL_TEST_CASES.md A8–A13 and .ai-handoff.md.

**Verification:** All **18 existing authentication tests passed (38 assertions)** after the final template adjustment. Production Vite build passed. Browser checks confirmed the left/right switch and active states, Login show/hide, independent Register and Reset Password controls, Space-key activation, return links, and the Forgot Password success message with the return link still available. Desktop and 390px layouts were checked; inspected mobile pages had no horizontal overflow, and no browser warning/error logs were reported. The full 441-test suite result belongs to IMP-018; this interface update used the focused authentication suite.

**Preview and screenshots:** Browser verification used an isolated SQLite database, a synthetic student and an array mail transport, so the reset request sent no external email. Existing application records and credentials were not changed. Screenshots are storage/app/private/login-auth-preview.png, register-auth-mobile-preview.png and forgot-password-auth-mobile-preview.png. No commit or push was made.

## IMP-020 — Stabilise and animate account switching; clarify password icons

**Date:** 2026-10-31
**Status:** Implemented and verified locally

- Login and Register now render the shared auth-card component. Both form panels occupy the same grid cell and contribute to its natural height. The inactive panel is invisible/inert, reserving the taller form's space and preventing card/logo movement. Submit buttons share the same bottom alignment. The layout can grow for longer content and validation messages.
- The blue selection background slides between Register and Login over 250ms, and the selected form fades in over 150ms. Reduced-motion preferences disable these transitions. Switching is handled inside the same card; normal form POSTs still use the existing authentication endpoints.
- Added resources/js/auth-card.js and registered its Alpine factory in app.js. Switching updates the URL/history; Back/Forward and direct refresh select the appropriate form. Links retain normal navigation without JavaScript and for modified clicks. Each form has unique input/label IDs, and errors/old input belong to the server-selected mode. Inactive fields cannot receive focus.
- Password icons now describe the current state: crossed eye when hidden, open eye when visible. Accessible labels continue describing the action. Mouse clicks add no blue outline; keyboard focus retains a subtle inset ring. Form switches mask any revealed password fields again. The shared control also applies these icon/focus changes to Reset Password.

**Implementation:** resources/views/components/auth-card.blade.php, auth-switch.blade.php and password-input.blade.php; auth/login.blade.php and auth/register.blade.php; resources/js/auth-card.js and app.js; resources/css/app.css. No database schema, authentication policy or recommendation changes.

**FYP documentation updates**

| Section | Update |
| --- | --- |
| Interface design/screenshots | Show the equal-height card, aligned actions, sliding highlight, current-state eye icons and keyboard-only eye focus. |
| Functional requirements/user guide | Explain switching forms in place, URL/history navigation, password visibility and automatic masking on mode changes. |
| Implementation | Describe the shared Blade component, overlapping grid panels, inert hidden form, Alpine state/history and reduced-motion CSS. |
| Testing/usability | Compare card geometry across modes on desktop/mobile; check icons, mouse/keyboard focus, Back/refresh, inactive fields and validation errors. |
| ERD/data dictionary | No change. |

Updated PRD.md, DESIGN.md, MANUAL_TEST_CASES.md A14–A17 and .ai-handoff.md. This entry supersedes IMP-019's page-navigation/eye-icon presentation.

**Verification:** All 18 existing authentication tests passed (38 assertions). Production Vite build and whitespace checks passed. Browser measurements showed both modes at height 534px/top 145px on the desktop viewport, and height 534px/top 128px at 390px width, with no mobile horizontal overflow. Verified active/inert panel state, slider endpoints and 250ms styling, Back navigation, direct Register refresh, current-state icons, mouse focus without shadow, visible keyboard inset ring, masking after switches and invalid-login error display in the correct panel. Preview used a separate empty SQLite database and synthetic invalid credentials; no existing application records were changed.

**Screenshots:** storage/app/private/auth-stable-register-preview.png, auth-stable-login-preview.png and auth-stable-mobile-preview.png. No commit or push was made.
