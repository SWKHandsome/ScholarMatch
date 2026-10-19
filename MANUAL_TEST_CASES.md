# ScholarMatch — Manual Test Cases

**Project:** FYP ScholarMatch — Scholarship Recommendation Platform for Malaysian Students
**Stack:** Laravel 13 + PHP 8.3 + Pest + Tailwind CSS + Vite + SQLite (dev)
**Last Updated:** 2026-10-19 (Asia/Singapore)
**Test Suite Status:** 185/185 automated tests passed (584 assertions); see improve.md for verification details.

---

## Setup

```bash
php artisan migrate --seed
php artisan serve  # http://127.0.0.1:8000
npm run dev        # in separate terminal
```

**Seed Credentials:**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@scholarmatch.test | password |
| Student | student@scholarmatch.test | password |

---

## 1. Public Pages

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| P1 | Home page loads | Visit `/` | Hero, How It Works, Features, CTA buttons visible |
| P2 | About page | Click "About" or visit `/about` | Static info page loads |
| P3 | Login page | Visit `/login` | Breeze login form |
| P4 | Register page | Visit `/register` | Breeze registration form |
| P5 | Guest → admin redirect | Visit `/admin` as guest | Redirect to `/login` |
| P6 | Guest → student redirect | Visit `/student/dashboard` as guest | Redirect to `/login` |

---

## 2. Authentication

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| A1 | Student register | `/register` → fill form → submit | Redirect to `/student/dashboard`, profile incomplete warning |
| A2 | Student login | `/login` with student creds | Redirect to `/student/dashboard` |
| A3 | Admin login | `/login` with admin creds | Redirect to `/admin/dashboard` |
| A4 | Wrong password | `/login` with bad password | Error "These credentials do not match" |
| A5 | Logout | Click "Log Out" dropdown | Redirect to `/`, session destroyed |
| A6 | Email verification | Register new → check email (log) → click link | Verified badge, no resend prompt |
| A7 | Password reset | `/forgot-password` → email → reset link → new password | Login with new password works |

---

## 3. Student Profile Management

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| S1 | View profile edit | Login student → `/student/profile/edit` | Form with all fields |
| S2 | Create profile (B40) | Fill: Malaysian, Selangor, 2500 income, 3 dependents, Public Uni, Engineering → Save | Success toast, income_category = "B40" auto-set |
| S3 | Create profile (M40) | Income: 5000 | income_category = "M40" |
| S4 | Create profile (T20) | Income: 8000 | income_category = "T20" |
| S5 | Update profile | Change state, income → Save | Values updated, income_category recalculated |
| S6 | Validation: empty fields | Submit empty form | All required field errors shown |
| S7 | Validation: negative income | Income: -100 | Error "must be at least 0" |
| S8 | Validation: negative dependents | Dependents: -1 | Error "must be at least 0" |
| S9 | Role guard | Login admin → `/student/profile/edit` | 403 Forbidden |

---

## 4. Student Academic Results

| ID | Action | Expected result |
| --- | --- | --- |
| AR-01 | Select SPM | Status becomes Completed; current-year and CGPA controls are hidden/disabled. |
| AR-02 | Submit SPM without official-result confirmation | Validation prevents saving. |
| AR-03 | Submit completed SPM with 8 As and 9 credits, funding Undergraduate | Save succeeds; stored CGPA/year are empty; funding level stays Undergraduate. |
| AR-04 | Enter fewer credits than As | Validation explains that As are included in credits. |
| AR-05 | Select a CGPA-based level and Currently Studying | Current year and official cumulative CGPA are required. |
| AR-06 | Select Completed for a CGPA-based level | Final CGPA is required; previous current year is cleared. |
| AR-07 | Submit pending results or omit CGPA | Validation prevents saving; no preliminary recommendation is generated. |
| AR-08 | Enter CGPA 0.00 or 4.00 | Both boundaries are valid official numeric results. |
| AR-09 | Change from SPM to Diploma and save | Old SPM counts are cleared. |
| AR-10 | Open a legacy academic record missing new fields | Student must confirm status/funding level before recommendations. |


## 5. Student Recommendations (Core Feature)

| ID | Action | Expected result |
| --- | --- | --- |
| REC-01 | Meet every specified automatic requirement | Eligible; no percentage, points or score ring. |
| REC-02 | Fail only the minimum CGPA | Not Eligible; the CGPA comparison is marked Not Met. |
| REC-03 | Fail income, field and CGPA together | All failures remain visible. |
| REC-04 | Meet checks for a scholarship with special text | Eligible group; ! expands through click, tap or keyboard to show the text. |
| REC-05 | Fail a requirement on a scholarship with special text | Not Eligible; no Eligible ! badge. Special text remains visible in the detail page. |
| REC-06 | Use whitespace-only special text | No ! warning. |
| REC-07 | Use official SPM results for Undergraduate funding | Compare the funding requirement against Undergraduate and academic route against SPM. |
| REC-08 | Configure minimum As and minimum credits | Both must pass, including equality. |
| REC-09 | Match one of multiple supported fields | Field check passes. |
| REC-10 | Leave a criterion explicitly unrestricted | Detail table says Not Applicable. |
| REC-11 | Use a scholarship with no rule record | Excluded; configuration notice appears. |
| REC-12 | Use inactive/expired scholarships | Absent from current recommendations. Saved records remain available. |
| REC-13 | Open a scholarship on its deadline day | It remains open until that day ends in the application timezone. |
| REC-14 | Compare two scholarships in a decision group | Earlier deadline first; ties use name then ID. |
| REC-15 | Switch Eligible / Not Eligible tabs | Correct panel appears; Eligible ! stays in Eligible. |
| REC-16 | Open an incomplete/legacy academic profile | Completion prompt appears; missing data is not classified as failure or !. |
| REC-17 | Put HTML/script text in special requirements | It is shown as escaped text and does not execute. |


## 6. Student Saved Scholarships

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| SS1 | Save scholarship | On recommendation detail → Click "Save" | Toast "Saved", button changes to "Saved" / "Remove" |
| SS2 | View saved list | `/student/saved-scholarships` | List of saved scholarships with Remove button |
| SS3 | Remove saved | Click "Remove" on saved list | Toast "Removed", item disappears |
| SS4 | Duplicate save prevention | Save same scholarship twice | Second click does nothing / shows "Already saved" |
| SS5 | Empty state | No saved scholarships | "No saved scholarships yet" message |
| SS6 | Role guard | Admin → `/student/saved-scholarships` | 403 Forbidden |

---

## 7. Student Dashboard

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| D1 | Incomplete profile/academic | Fresh student → `/student/dashboard` | Warning cards: "Complete Profile", "Add Academic Results" |
| D2 | Complete both | After S2+AR2 → dashboard | Green checkmarks, quick action links |
| D3 | Saved count | After SS1 → dashboard | "Saved Scholarships: 1" |
| D4 | Recent recommendations | After R3 → dashboard | Shows top 3 recent recommendations |
| D5 | No matches message | T20 student → dashboard | "No matching scholarships found" |
| D6 | Quick action links | Click "View Recommendations" / "Edit Profile" | Navigates correctly |

---

## 8. Admin Dashboard

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| AD1 | Stats display | Login admin → `/admin/dashboard` | Cards: Total Scholarships, Active, Students, Recommendations |
| AD2 | Role guard | Student → `/admin/dashboard` | 403 Forbidden |
| AD3 | Guest redirect | Logout → `/admin/dashboard` | Redirect to `/login` |

---

## 9. Admin Scholarship Management

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| AS1 | Index page | `/admin/scholarships` | Table with 12 seeded scholarships, pagination |
| AS2 | Create page | Click "Add Scholarship" | Form with all fields |
| AS3 | Create scholarship | Fill all fields (name, provider, desc, award, deadline, link, active) → Save | Success toast, appears in list |
| AS4 | Validation: required | Submit empty create form | All required field errors |
| AS5 | Validation: URL | Invalid application_link | Error "must be a valid URL" |
| AS6 | Edit page | Click "Edit" on row | Pre-filled form |
| AS7 | Update scholarship | Change name, deadline → Save | Updated in list |
| AS8 | Delete scholarship | Click "Delete" → Confirm | Removed from list |
| AS9 | Role guard | Student → `/admin/scholarships` | 403 Forbidden |

---

## 10. Admin Scholarship Rules (Nested Resource)

| ID | Action | Expected result |
| --- | --- | --- |
| RULE-01 | Open Add/Edit Rules | No hard/soft/scoring selectors; all specified criteria are requirements. |
| RULE-02 | Add Diploma/Completed/CGPA 3.00 and Undergraduate/Currently Studying/CGPA 3.30/Years 1–3 boxes; funded level Undergraduate | Each box saves independently; funded level remains outside the boxes. |
| RULE-03 | Add current-study status and year range | Inclusive ordered bounds save; invalid ranges fail. Either boundary can be empty. |
| RULE-04 | Change Currently Studying to Completed | Year controls hide, disable and clear; the server also removes stale year values. |
| RULE-05 | Switch SPM to Diploma or back | SPM shows As/credits and forces Completed; Diploma shows CGPA; irrelevant values clear. |
| RULE-06 | Clear supported-field selections and save | Previous field restriction is removed. |
| RULE-07 | Save a rule after a student has viewed recommendations | New evaluation reflects the edit immediately through catalog invalidation. |
| RULE-08 | Substitute another scholarship's rule ID in Edit/Update URL | 404; the unrelated rule is unchanged. |
| RULE-09 | Add/Edit Scholarship special textbox | Text persists and appears through ! only on otherwise Eligible results. |
| RULE-10 | Remove special text and save | The Eligible warning disappears after re-evaluation. |
| RULE-11 | Add three boxes and remove the middle box | Remaining values stay with their boxes; submitted indices are valid; removed routes disappear after saving. |
| RULE-12 | Save directly from Add/Edit Scholarship | Scholarship metadata, general rules and route rows save together; any invalid route prevents partial updates. |
| RULE-13 | Leave result minimums empty; use minimum 0.00 separately | Blank is unrestricted; zero is stored as an actual minimum. |
| RULE-14 | Remove all boxes and save | Academic restrictions disappear; obsolete legacy academic columns do not reappear. General requirements remain. |
| RULE-15 | Open the editor with JavaScript disabled and submit | Server rejects the missing editor-initialization marker; saved routes are preserved. |
| RULE-16 | Set non-SPM Either status | Studying and completed applicants may use this route; year controls are hidden. |
| REC-18 | A student passes one complete box and fails another | Eligible if general conditions pass; expanded explanations show each alternative. |
| REC-19 | Student passes CGPA in one box and year in another, but no complete box | Not Eligible; conditions are not mixed across boxes. |
| REC-20 | Student passes a route but fails nationality/income/field | Not Eligible; special text does not override the failure. |


## 11. Admin Income Categories

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| IC1 | Index page | `/admin/income-categories` | Table: B40 (≤3169), M40 (3170-6339), T20 (≥6340) |
| IC2 | Classify method | Click "Classify" or test via seeder | 2500→B40, 5000→M40, 8000→T20 |
| IC3 | Role guard | Student → `/admin/income-categories` | 403 Forbidden |

---

## 12. Admin Recommendation Logs

| ID | Action | Expected result |
| --- | --- | --- |
| LOG-01 | Student opens recommendations twice | Latest user/scholarship result is updated rather than duplicated. |
| LOG-02 | Open current log detail | Decision, special text snapshot, failed requirements and structured comparisons appear. |
| LOG-03 | Filter Eligible ! | Only current Eligible evaluations with requires_verification are returned. |
| LOG-04 | Search student email or scholarship name | Matching logs appear; filters and pagination remain functional. |
| LOG-05 | Open a pre-upgrade evaluation | Previous Evaluation / re-evaluation required appears; old scores are not shown as current decisions. |
| LOG-06 | Refresh an old evaluation after completing academic fields | New decision is stored while the old snapshot is retained. |
| LOG-07 | Filter by evaluation date | Uses the latest evaluation timestamp. |


## 13. Edge Cases & Integration

| ID | Action | Expected result |
| --- | --- | --- |
| EDGE-01 | Edit official CGPA twice within one second | The second evaluation uses the current value. |
| EDGE-02 | Change administrator income ranges after cached evaluation | Category is recalculated and cached decisions are invalidated. |
| EDGE-03 | Use a household income not covered by any configured range | An administrator-action message appears; no eligibility is invented. |
| EDGE-04 | Cross midnight while a result is still cached | Yesterday's scholarship deadline no longer appears as open. |
| EDGE-05 | Compare actual and minimum values of zero | Zero is evaluated as a real value, not an unspecified criterion. |
| EDGE-06 | Apply upgrade to a copy of an existing database | Student/scholarship data survives, legacy logs are snapshotted, new intent/status fields are not invented. |
| EDGE-07 | Review mobile and keyboard interaction | Forms, decision tabs, requirement tables and ! disclosures remain usable. |


## Quick Regression Checklist (run after any change)

```bash
# 1. Full test suite
php artisan test

# 2. Upgrade schema (preserves existing records)
php artisan migrate

# 3. Key feature tests
php artisan test --filter="Student"
php artisan test --filter="Admin"
php artisan test --filter="RecommendationServiceTest"

# 4. Manual smoke test
# - Student: register → profile → academic → recommendations → save → dashboard
# - Admin: login → scholarships CRUD → rules CRUD → income categories → logs
```

---

## Test Data Reference (Seeders)

**21 Scholarships seeded** across categories:
- Government: Yayasan Khazanah, MARA, JPA, PTPTN
- Corporate: Petronas, Maybank, CIMB, Khazanah, Sime Darby, YTL, Sunway, Taylor's

**Rules cover:** nationality, income category, funded level, accepted latest qualification, field, institution, SPM As/credits, CGPA and maximum income. Every configured criterion is enforced; new rules can also restrict study status and current year.

```bash
# Inspect rules
php artisan db:seed --class=ScholarshipRuleSeeder
```

---

## Automated Test Coverage

Run `php artisan test`. Tests cover the decision engine, official-result form validation, latest qualification versus funding level, special warnings, boundary cases, caches, log migration, and existing authentication/admin/student workflows. Automated results and browser checks are recorded in improve.md. Manual cases above describe expected behaviour; they are not a claim that every manual case has been executed.
