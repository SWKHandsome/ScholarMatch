# ScholarMatch — Manual Test Cases

**Project:** FYP ScholarMatch — Scholarship Recommendation Platform for Malaysian Students
**Stack:** Laravel 13 + PHP 8.3 + Pest + Tailwind CSS + Vite + MySQL (dev; SQLite isolated tests)
**Last Updated:** 2026-11-08 (Asia/Singapore)
**Test Suite Status:** Full MySQL suite passed on PHP 8.3 and 8.5: 474 tests / 2,249 assertions per runtime. SQLite on PHP 8.5: 467 passed / 2,223 assertions, with seven MySQL-only cases skipped. All 27 JavaScript tests, production build, Composer validation/audit and syntax parsing passed. Desktop/mobile student/admin browser checks completed. See [the full regression report](docs/FULL_SYSTEM_TEST_REPORT_2026_11_08.md) and IMP-040 in improve.md for coverage and limitations.

---

## Setup

Use a dedicated disposable test database and synthetic accounts for these steps. Do not run migration/seed/reset commands against an existing application database simply to test it. Browser tests should use a separate cookie name as well as a separate database so they cannot reuse real sessions.

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
| P2 | Public navigation after About removal | Inspect Home navigation; visit `/about` | No About link; the retired route returns 404. |
| P3 | Login page | Visit `/login` | Breeze login form |
| P4 | Register page | Visit `/register` | Breeze registration form |
| P5 | Guest → admin redirect | Visit `/admin/dashboard` as guest | Redirect to `/login` |
| P6 | Guest → student redirect | Visit `/student/dashboard` as guest | Redirect to `/login` |

---

## 2. Authentication

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| A1 | Student register | `/register` → fill form → submit | Open Student Profile setup, with Save and Continue and the two-step indicator. |
| A2 | Student login | `/login` with student creds | Open first incomplete Student Profile/Academic Result; both complete goes to Dashboard or the normal intended page. |
| A3 | Admin login | `/login` with admin creds | Redirect to `/admin/dashboard` |
| A4 | Wrong password | `/login` with bad password | Error "These credentials do not match" |
| A5 | Logout | Click "Log Out" dropdown | Redirect to `/`, session destroyed |
| A6 | Optional email-verification routes | Exercise an unverified synthetic user and a valid/invalid signed verification link | Valid signed link verifies the address; invalid hash is rejected. Current User does not enforce mandatory verified-email login; do not expect automatic verification mail or a verified badge as a registration requirement. |
| A7 | Password reset | `/forgot-password` → email → reset link → new password | Login with new password works |
| A8 | Switch between account forms | Open Login, click Register on the left, then Login on the right | Full-width switch appears above each form; current page is blue and marked aria-current=page. Links open the correct form. |
| A9 | Login password visibility | Enter a test password; click the eye twice | Password starts masked, becomes visible and is masked again. Labels/pressed state follow visibility; toggling does not submit or clear the form. |
| A10 | Independent registration controls | Toggle Password and Confirm Password separately; use Space on an eye button | Only the associated field changes; keyboard operation works without registering or submitting. |
| A11 | Return from Forgot Password | Follow Forgot password?; inspect before/after a reset-link request and after a validation error; click Back to Login | Return link stays available with form errors/status messages and opens Login. |
| A12 | Reset Password visibility and return | Open a reset link; inspect default masking; toggle each eye separately; click Back to Login | Both fields have independent controls and retain required/autocomplete behaviour. Return navigation works. |
| A13 | Mobile authentication layout | Inspect Login, Register, Forgot Password and Reset Password at 390px width | Switch stays side by side, fields/buttons fit, eye icons have reserved input space, and page content has no horizontal overflow. |
| A14 | Stable shared card | Switch Login/Register on desktop and at 390px; compare card edges and submit buttons | Card height/top and button baseline stay fixed for unchanged content. Longer validation messages can expand the natural layout without clipping. |
| A15 | Animated switch and history | Switch forms, use Back/Forward, refresh each URL and open a link normally in a new tab | Blue background slides for 250ms, selected form fades in for 150ms, URL/active state follow selection. Direct routes and native link navigation work. Reduced-motion settings disable panel/slider transitions. |
| A16 | Current-state eye icons and focus | Toggle each eye by mouse, then reach it using Tab | Masked = crossed eye; visible = open eye. Mouse clicks show no blue outline; keyboard focus has a visible inset ring. Labels describe the next action. |
| A17 | Inactive form and validation | Inspect keyboard/assistive navigation; submit invalid Login details; switch modes | Only the visible form is interactive/exposed. Login errors appear in Login, unique field IDs label the correct inputs, and switching masks revealed passwords. |
| A18 | Login survives server restart | Sign in on an isolated database-backed session, stop/restart only artisan serve, then reload | A still-valid browser cookie/session remains authenticated. Explicit Logout ends it; restarting the process alone does not log everyone out. Normal lifetime is configured separately, and Remember me may extend authentication. |
| SET-01 | First registration/login | Use a student without personal/academic information | Open Student Profile first, overriding old intended URLs; show two-step progress. |
| SET-02 | Resume setup | Log in with a complete profile but missing/pending/incomplete academics | Open Academic Result directly; retain saved profile information. |
| SET-03 | Save profile then academics | Submit valid personal information, then official academic results | Profile save continues to Academic Result; academic save reaches Dashboard once both are complete. If academics already exist, profile save can finish setup directly. |
| SET-04 | Manual edit and completed login | Use a student with both sections complete | Keep normal intended/default Dashboard navigation and Save Changes; hide setup progress. Admin flow is unchanged. |
| SET-05 | Legacy/partial profile and genuine zero values | Inspect missing/invalid required fields and zero income/dependents | Partial records prompt the first incomplete step; valid zero values are complete. Failed validation retains the current form and entered values. |
| SET-06 | Territory names and old records | Open/save Kuala Lumpur, Labuan and Putrajaya, including old prefixed selections | Dropdown shows only short names; existing Wilayah Persekutuan selections stay selected and change to short storage only on profile save. |

---

## 3. Student Profile Management

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| S1 | View profile edit | Login student → `/student/profile` | Nationality, state, income and dependents; programme fields are linked to Academic Result |
| S2 | Create profile (B40) | Fill: Malaysian, Selangor, 2500 income, 3 dependents → Save | Success toast, income_category = "B40" auto-set; no programme fields required here |
| S3 | Create profile (M40) | Income: 5000 | income_category = "M40" |
| S4 | Create profile (T20) | Income: 8000 | income_category = "T20" |
| S5 | Update profile | Change state, income → Save | Values updated, income_category recalculated |
| S6 | Validation: empty fields | Submit empty form | All required field errors shown |
| S7 | Validation: negative income | Income: -100 | Error "must be at least 0" |
| S8 | Validation: negative dependents | Dependents: -1 | Error "must be at least 0" |
| S9 | Role guard | Login admin → `/student/profile` | 403 Forbidden |
| S10 | Income input layout | Open Profile with income 3000.00 | Amount is fully readable with standard input padding; RM appears in the label, with no overlaid prefix |
| S11 | Required-field markers | Open Profile, Academic Result and admin Add/Edit Scholarship | No separate “* Required field.” note; required labels retain stars and validation still applies |
| S12 | Nationality choices and save | Open Profile, select Non-Malaysian and save | Only Malaysian and Non-Malaysian are selectable categories; the chosen value saves and reloads; individual-country/Other submissions fail validation |
| S13 | Scholarship nationality matching | Compare each student category against Malaysian, Non-Malaysian and unrestricted rules | Exact categories pass, the other category fails, and unrestricted rules accept both; admin forms offer the same two specific choices plus Any nationality |

---

## 4. Student Academic Results

| ID | Action | Expected result |
| --- | --- | --- |
| AR-01 | Select SPM | Status becomes Completed; current-year and CGPA controls are hidden/disabled. |
| AR-02 | Submit SPM without official-result confirmation | Validation prevents saving. |
| AR-03 | Enter 8 A and 1 C subjects for completed SPM, with Bachelor’s Degree funding | Summary shows 9 subjects, 8 As and 9 credits; save succeeds; stored CGPA/year are empty; funding identifier stays Undergraduate. |
| AR-04 | Enter 2 A+, 1 A, 1 A-, 2 B, 1 C and 1 G | Derived totals are 8 subjects, 4 As and 7 credits; forged aggregate values cannot replace these totals. |
| AR-05 | Select a CGPA-based level and Currently Studying | Current year and official cumulative CGPA are required. |
| AR-06 | Select Completed for a CGPA-based level | Final CGPA is required; previous current year is cleared. |
| AR-07 | Submit pending results or omit CGPA | Validation prevents saving; no preliminary recommendation is generated. |
| AR-08 | Enter CGPA 0.00 or 4.00 | Both boundaries are valid official numeric results. |
| AR-09 | Change from SPM to Diploma and save | Old SPM counts are cleared. |
| AR-10 | Open a legacy academic record missing new fields | Student must confirm status/funding level before recommendations. |
| AR-11 | Enter Programme Seeking Funding | Funding level, institution type and field appear together and are required; supported options save and reload. |
| AR-12 | Change institution or field on Academic Result | Matching uses the updated academic values immediately, including after earlier results were cached. |
| AR-13 | Update Profile with extra institution/field values | Academic choices remain unchanged; Profile does not edit programme data. |
| AR-14 | Upgrade existing profile/academic data | Programme choices are copied; results and existing record counts are preserved. A profile-only student keeps choices as first-form prefill without invented results. |
| AR-15 | View Programme Seeking Funding on desktop and a narrow screen | A continuous divider appears above the heading, with consistent spacing and no line cutting through the legend; helper text and controls wrap within the form |
| AR-16 | Choose each education category | Only its supported qualifications appear; Bachelor’s Degree, Cambridge IGCSE/O-Level and Senior UEC labels retain compatible stored identifiers. |
| AR-17 | Use plus/minus buttons and direct entry | Summary and total update immediately; minus cannot go below zero; at least one subject is required. |
| AR-18 | Submit negative, fractional, boolean, nested, unknown-grade or excessive counts | Server rejects invalid counts even if browser controls are bypassed; total caps are 12 for SPM and 20 for the other supported grade formats. |
| AR-19 | Save and reopen SPM, IGCSE letters, IGCSE 9–1, O-Level, A-Level and UEC | Exact scale/counts persist; status is Completed, CGPA/year are empty and official confirmation is required. |
| AR-20 | Switch IGCSE from 9–1 to letters after entering results | Counts and official confirmation clear; numeric grades do not carry into letters and no conversion occurs. |
| AR-21 | Change category or qualification after entering results | Incompatible result fields and official confirmation clear; Programme Seeking Funding remains separate. |
| AR-22 | Save each STAM overall pangkat | Selected rank persists, count/CGPA fields are empty and official final results are required. |
| AR-23 | Open a migrated aggregate-only SPM record | Preserved As/credits are shown; no distribution is fabricated; recommendations require the actual grade breakdown. Valid existing CGPA records remain usable. |
| AR-24 | Inspect the form at 390px and with keyboard navigation | Pills wrap, counters form a readable grid, buttons/inputs have accessible labels and focus states, and no horizontal page overflow occurs. |
| AR-25 | Attempt forecast/expected results or studying status for an exam-based qualification | Submission fails; there is no forecast/expected or AS-Level input path. |
| AR-26 | Enter STPM grade counts and save/reopen | Completed STPM calculates overall CGPA from PA plus the best three others with confirmation; studying STPM keeps manual official CGPA. Provider best-subject averages remain separate. Counts use the STPM scale and cap of 5. |
| AR-27 | Open a CGPA-only legacy STPM record | CGPA is preserved; a prompt requests actual subject counts before recommendations. Missing counts produce a completion message rather than Not Eligible or !. |
| AR-28 | Use STPM Currently Studying and change status | Current year/latest official results and manual CGPA are supported; changing status clears year, CGPA, counts, PA grade and confirmations in the form. Completed students use final results and calculation. |
| AR-29 | Enter A/A/A/A/C with PA=C | Read-only overall CGPA shows 3.50, total subjects stays 5 and PA is included exactly once. Choosing PA=A instead yields 4.00. |
| AR-30 | Enter 2 A and 2 A-, PA=A; test 4 Fs separately | First CGPA rounds half up to 3.84; four Fs produce valid 0.00. |
| AR-31 | Omit PA, select an uncounted/unsupported PA grade, or enter fewer than 4/more than 5 final subjects | Calculation cannot complete and saving fails; the server validates even when browser checks are bypassed. |
| AR-32 | Confirm calculated CGPA, then edit counts using typing/counters or change PA | Confirmation clears and must be checked again for the current results. |
| AR-33 | Save completed STPM with no match confirmation or a forged posted CGPA | Missing/rejected confirmation fails. A posted CGPA cannot override the server-calculated value or inflate eligibility. |
| AR-34 | Reopen a saved calculated record; then switch to studying or another qualification | PA, calculated CGPA and confirmation restore when unchanged. Other modes use manual CGPA as applicable and clear STPM-only calculation metadata on save. |
| AR-35 | Upgrade an existing usable manual STPM record | Its grades/CGPA and readiness remain intact, PA stays unanswered and confirmation false. Editing completed results requests PA and confirmation; no grade identity is invented. Rollback guards populated metadata. |
| AR-36 | Open the calculator at 390px | PA selection, read-only value and match confirmation wrap within the form without horizontal overflow. |


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
| REC-10 | Leave a criterion explicitly unrestricted | Student details place it in the collapsed View criteria with no restrictions section, with the student's information available. Admin log tables retain Not Applicable. |
| REC-11 | Use a scholarship with no rule record | Excluded; configuration notice appears. |
| REC-12 | Use inactive/expired scholarships | Absent from current recommendations. Saved records remain available. |
| REC-13 | Open a scholarship on its deadline day | It remains open until that day ends in the application timezone. |
| REC-14 | Compare two scholarships in a decision group | Earlier deadline first; ties use name then ID. |
| REC-15 | Switch Eligible / Not Eligible tabs | Correct panel appears; Eligible ! stays in Eligible. |
| REC-16 | Open an incomplete/legacy academic profile | Completion prompt appears; missing data is not classified as failure or !. |
| REC-17 | Put HTML/script text in special requirements | It is shown as escaped text and does not execute. |
| REC-21 | Inspect View Details on dashboard/recommendation cards | Visible outlined button matches Save; Apply Now stays filled; View Details still opens the scholarship. |
| REC-22 | Inspect wide dashboard and narrow recommendation cards | Eligible/Not Eligible badge stays at the top right; long titles wrap without overlap; award type and deadline are highlighted. |
| REC-23 | Expand card explanations and open scholarship/log details | Met checks are green, Not Met red, Not Applicable neutral; required/student values are bold; labels and symbols also explain the outcome. |
| REC-25 | Expand View Explanation on a recommendation card | One readable message per applicable check with bold green Met/red Not Met label; no repeated value rows or individual boxes. Student details use grouped comparisons; admin log tables retain their comparison styling. |
| REC-26 | Open details with several alternative routes, including a passing route | Show the selected route, combine qualification/status and show its relevant comparisons. Other routes are collapsed and are not listed as overall failure reasons. |
| REC-27 | Fail academic/general restrictions | Failed comparisons appear before passing academic/other checks. All configured restrictions remain visible, with required/actual values and red text/status. |
| REC-28 | Open details with uncertain dates or special requirements | Amber Check with the provider section shows date confirmation and escaped special text. Rolling/automatic awards show application timing without an invented deadline failure. |
| REC-29 | Expand alternatives and unrestricted criteria using mouse/keyboard | Both disclosures open, each alternative can be inspected independently, and unrestricted information is retained under No restriction. |
| REC-30 | Inspect Eligible and Not Eligible details on desktop/390px | Values are side by side on desktop and stacked on mobile, text wraps without horizontal overflow, and real zero/income currency values remain readable. |
| REC-26 | Open Recommendations on desktop and mobile | Filters appear beside a single column of full-width cards on desktop and above it on mobile; Dashboard cards are unchanged |
| REC-27 | Apply search, award type, funded level, field and institution filters together | Name/provider search ignores case; all filters combine; unrestricted programme conditions and matching supported-field lists remain included; decisions are unchanged |
| REC-28 | Open a group with more than 10 results and choose page 2 | First page has 10 cards; next page has the remaining cards in deadline order; counts/range/page are accurate; filters and group persist in navigation |
| REC-29 | Change group or filters while on page 2, then Reset | Each action returns to page 1; group links preserve filters; Reset clears filters while keeping the chosen group |
| REC-30 | Apply filters with no matching results or open an out-of-range page | Empty groups keep filters/reset and clear guidance; stale high page values show the last available page |
| REC-24 | Activate the top-right Eligible ! badge with keyboard | Special text opens in a bounded amber panel and remains readable; the badge retains normal keyboard interaction. |


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
| D4 | Recent recommendations | Complete profile/results and evaluate several scholarships | Shows up to five preview entries in the recommendation service's order; these may include Not Eligible entries. |
| D5 | No available recommendations | Dashboard with no active/configured awards to evaluate | Shows No Scholarships Matched. A zero Eligible count with evaluated Not Eligible awards still permits the preview. |
| D6 | Quick action links | Click "View Recommendations" / "Edit Profile" | Navigates correctly |
| D7 | Eligible total differs from preview size | Four Eligible awards plus at least two Not Eligible awards | Recommendations card reads 4 eligible; preview still contains five entries. Eligible ! awards count as Eligible. |
| D8 | Eligible total above five | Seven Eligible awards plus rejected awards | Card reads 7 eligible rather than the five-entry preview count. |
| D9 | No Eligible awards | Six Not Eligible awards | Card reads 0 eligible while the preview can show five rejected awards. |
| D10 | Academic update changes eligibility | Update official CGPA so an additional award passes, then reload Dashboard | Eligible total changes from four to five, even though the preview length stays five. |

---

## Explanation refinements and rule preview

| # | Test case | Expected |
| --- | --- | --- |
| GAP-01 | Official 3.20 versus required 3.50 | Details show a 0.30 gap and all other failed conditions. Grade shortfalls are not added together. |
| PRE-01 | Test ABB rule with AAB, ABB and BBB presets | AAB/ABB pass; BBB fails. All unused grades are zero and no student/award/log record is created. |
| PRE-02 | Edit the draft requirement before running | Preview evaluates the unsaved values; the saved scholarship remains unchanged. Edit and Rules forms work despite their ordinary PUT action. |
| PRE-03 | Invalid rule/grade/scale/PA data | Inline JSON errors appear without saving or navigating away. Completed STPM computes PA plus best three and ignores manual CGPA. |
| PRE-04 | Edit rules/sample while a result/request exists | Previous result clears and stale responses are discarded. Run again evaluates the new values. |
| PRE-05 | General restrictions, OR routes and provider conditions | All configured general checks apply, any complete route may pass, and special/date checks remain visible. |
| PRE-06 | Normal scholarship save with empty/invalid sample controls | Sample inputs belong to their own form and do not block or contaminate a normal save. |
| PRE-07 | Guest/wrong-role requests and desktop/390px layout | Admin preview auth/role guards apply; controls/results wrap without horizontal overflow. |

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
| RULE-16 | Set Either status on a CGPA-based qualification | Studying and completed applicants may use this route; year controls are hidden. Exam-based qualifications only permit Completed. |
| RULE-17 | Configure full A-Level AAB using 2 at A or better and 3 at B or better | A*AB passes; ABB and AAC fail. All grade conditions in that box must pass. |
| RULE-18 | Configure separate IGCSE letter and 9–1 boxes | Each compares only its explicit scale; either complete passing box satisfies the academic requirement. An unrestricted IGCSE box can accept either scale. |
| RULE-19 | Save, reopen and resubmit grade-based routes | Saved grade dropdowns restore correctly, including after dynamic options render; adding/removing another condition retains existing selections and minima. |
| RULE-20 | Configure Senior UEC A2-or-better minimum | A1 and A2 both count; lower achievement grades do not. A1-only uses the separate A1 threshold. |
| RULE-21 | Configure UEC best 5, minimum B6, maximum average 3.20 | A1/A2/B3/B4/B6 averages 3.20 and passes. Too few B6-or-better subjects fails. A weaker average fails without rounding into a pass. |
| RULE-22 | Enter an incomplete UEC formula, invalid grade, duplicate threshold or maximum average 3.333 | Validation rejects it; maxima allow up to two decimal places. |
| RULE-23 | Configure minimum STAM Jayyid | Mumtaz/Jayyid Jiddan/Jayyid pass; Maqbul/Rasib fail. |
| RULE-24 | Put Mathematics at least C in Special Requirements | Automatic matching does not claim to verify Mathematics; otherwise Eligible receives ! with the text; automatic failure remains Not Eligible. |
| RULE-25 | Change between grade-based, STAM and CGPA routes | Unused scale/count/rank/average/year values are cleared on save. |
| RULE-26 | Use Add Another Requirement and inspect Requirement Type for each qualification | Only supported types appear. STPM offers overall CGPA, grade counts and minimum best-subject average; UEC also offers maximum average/total. A-Level has no generic CGPA conversion. |
| RULE-27 | Open an existing rule and save without changing conditions | Existing SPM/CGPA/grade/STAM/UEC-average thresholds restore as typed rows with the same values and meaning; empty/zero remain distinct. |
| RULE-28 | Configure UEC maximum total 11 across best 5 | A1/A1/A2/B3/B4 passes at 11, a weaker total fails, and too few qualifying subjects fails. No manual conversion into average 2.20 is needed. |
| RULE-29 | Compare STPM average 3.00 across best 3 with 3 subjects each at B or better | A/B/C/C passes the best-three average (3.00), fails three B-or-better subjects, and has a separate official overall CGPA. Combining with a minimum overall CGPA requires both conditions. |
| RULE-30 | Change a condition type, remove a middle condition and save | Incompatible values clear, other conditions retain their values and stable identities, and removed restrictions disappear. Added conditions must be complete. |
| RULE-31 | Add A-Level thresholds 1 at A and 3 at B | Plain-language descriptions and cumulative minimum pattern show A, B, B; stronger grades count towards lower thresholds. |
| RULE-32 | Configure a best-subject rule requiring named-subject exclusions or an undocumented conversion | Record the unsupported part in Special Requirements; aggregate counts must not be presented as verification of named subjects or an invented formula. |
| RULE-33 | Upgrade route schema with an existing database and attempt rollback with new point rules | Old values/counts remain intact; stpm_average/uec_total are nullable JSON; rollback refuses to discard new rule data. |
| RULE-34 | Open saved A-Level grade minima 1 at A and 3 at B | They appear as two rows inside one grade-count requirement with one selector and one A, B, B preview. Saving preserves the same flat thresholds and eligibility meaning. |
| RULE-35 | Add/remove grade rows, including the first and final row | Other row values stay attached to their stable identities. Removing the final row removes only its group; other CGPA/average requirements remain. The grade type becomes available again. |
| RULE-36 | Use Add Grade Row and Add Another Requirement | The first appends a threshold inside the grade group; the second adds a different supported requirement type. No duplicate grade group is offered. Each row is required when present; up to 20 grade rows are supported. |
| RULE-37 | Create, edit and reopen grouped requirements at desktop and 390px widths | Both rows remain readable, all controls have labels, there is no horizontal page overflow, and native form submission succeeds without irrelevant hidden required inputs. |
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

**Rules cover:** nationality, income category, funded level, accepted latest qualification, field, institution, SPM As/credits, CGPA and maximum income. Every configured criterion is enforced; routes also support study status/current year, qualification-specific grade counts/scales, STAM ranks and explicit UEC average formulas. The stock demo catalogue covers the original six qualification formats; additional qualification cases use dedicated test fixtures.

```bash
# Inspect rules
php artisan db:seed --class=ScholarshipRuleSeeder
```

---

## Automated Test Coverage

Run `php artisan test` and `npm run test:academic`. Tests cover the decision engine, official-result form validation, all supported grade formats, latest qualification versus funding level, special warnings, exact/near-miss boundaries, caches, migration preservation/protected rollback, and existing authentication/admin/student workflows. Automated results and synthetic browser checks are recorded in improve.md. Manual cases above describe expected behaviour; they are not a claim that every manual case has been executed. MySQL integration is now included; see the MySQL migration verification entry in improve.md.

## MySQL transfer and richer catalogue

| ID | Scenario | Expected result |
|---|---|---|
| DB-01 | Start Laragon MySQL, run app with MySQL `.env` | App uses the intended MySQL database; existing accounts can sign in with their unchanged passwords. |
| DB-02 | Run all migrations on an empty separate MySQL database | Tables and cascading relationships are created successfully, including saved scholarships. |
| DB-03 | Import a migrated SQLite snapshot into empty MySQL | Matching history/integrity checks pass; IDs, records, hashes and relationships are verified before commit; SQLite stays unchanged. |
| DB-04 | Import dry run, populated destination or mismatched schema | Dry run writes nothing; populated/mismatched targets are refused. |
| DB-05 | A late source value violates MySQL schema | Entire data import rolls back; previously imported parent rows are absent. |
| CAT-01 | Refresh the retired 70 demos | Backup is created first; only the 70 exact retired keys and associated records are removed; 20 richer demos are seeded. Manual awards and student records stay intact. |
| CAT-02 | Seed/refresh again | No duplicate current demo awards; manual scholarships/bookmarks/logs remain; old 70 do not return. |
| CAT-03 | Open Meridian with A-Level ABB, AAB, BBB | ABB/AAB meet academic conditions; BBB fails the A threshold; provider checks remain visible. |
| CAT-04 | UEC best-five total 11 versus 12 | Bayu/Kencana total 11 passes; 12 fails. A best-five average is a separate Mutiara condition. |
| CAT-05 | Test all 20 example applicants and change funded level | Each documented applicant meets its award; incorrect funded level fails even when academic results pass. |
| CAT-06 | Open fictional scholarship information | Fictional providers/benefits and reserved presentation links appear; no official-source verification is claimed. |

MySQL tests use only `scholarmatch_test`, never the application database. See docs/MYSQL_SETUP.md for commands and docs/DEMO_SCHOLARSHIPS.md for all 20 examples. Azure provisioning remains a later task.

## Income-category synchronization and live feedback

| ID | Scenario | Expected result |
|---|---|---|
| IC-SYNC-01 | Change a category range in Admin, then reload Student Profile | Helper and reference cards use the saved database range; affected saved student categories are reclassified without student resubmission. |
| IC-SYNC-02 | Add, rename or delete a category | Current names/ranges appear after refresh; stored student categories are recomputed, including null when no range matches. |
| IC-SYNC-03 | Enter zero, a boundary, a different category or clear the input | Zero is classified normally; inclusive boundaries match server evaluation; helper changes while typing and clearing removes the previous category. |
| IC-SYNC-04 | Enter RM3169.50 with the unchanged seeded ranges | Explicit no-match message; the system does not invent a category or fill the configured gap. |
| IC-SYNC-05 | Submit income with three decimal places, or an admin maximum below its minimum | Validation rejects the value/settings; no silent rounding or partial range/profile writes. |
| IC-SYNC-06 | Reload after an unrelated profile validation error | Entered income is retained and its matching helper is shown; stored income/category remains unchanged until a successful save. |
| IC-SYNC-07 | Use custom names or no configured categories | Custom names render as escaped text with current ranges; missing configuration gives an explicit message. |
| IC-SYNC-08 | Temporarily overlapping configured ranges | Matching, saved classification and helper consistently use lowest minimum then ID; do not silently alter administrator ranges. |

The browser demonstration uses synthetic accounts and illustrative ranges in an isolated MySQL database. It does not change the application's actual thresholds. Use npm run test:income for the five live-preview tests.

## Matching Settings — Fields of Study

Use an isolated database for rename/deactivation checks.

| ID | Action | Expected result |
| --- | --- | --- |
| MF-01 | Open Matching Settings from the admin navigation, then the old income-category URL | Income Categories appears above Fields of Study; existing income controls still work. |
| MF-02 | Add a new field, then open student Academic Result and admin scholarship/rule editors | The same new active field is available across forms. |
| MF-03 | Rename a field used in an academic record and single/multiple-field scholarship restrictions | Linked current records update together; matching still uses the renamed label. Historical log snapshots stay unchanged. |
| MF-04 | Deactivate a field, then use a new student/scholarship form | The field is unavailable for new assignments; it remains inspectable in filters/sample preview. |
| MF-05 | Edit a student or scholarship already using an inactive field | Existing selection remains visible and can be retained; the student's academic readiness is preserved. |
| MF-06 | Submit duplicate/case-only duplicate or blank field names | Validation errors appear in the reopened editor with entered values retained; no partial writes. |
| MF-07 | Rerun the study-field seeder after rename/deactivation in a test database | Renames/status survive; bundled demo field restrictions resolve stable original aliases. |
| MF-08 | Import an updated SQLite snapshot into migrated empty MySQL, then try a target with custom field settings | Untouched destination defaults may be replaced transactionally; edited/custom target settings are refused. IDs, labels and active status are preserved. |

Browser verification covered MF-01–MF-06 using synthetic users on desktop, including a renamed existing student field and an inactive field hidden from new selections. Automated tests cover seed/import behaviour and permissions. Screenshot: storage/app/private/matching-settings-preview.png. No mobile viewport check was performed for this change.
