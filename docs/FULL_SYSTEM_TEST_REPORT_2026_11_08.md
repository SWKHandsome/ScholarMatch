# ScholarMatch full regression report

**Test period:** 7–8 November 2026, Asia/Singapore  
**Source checkpoint:** main, c511810 (merged PR #2), including Symfony security patches from df4dded  
**Outcome:** No functional failure was reproduced in the completed automated suites and browser scenarios. This is a local regression checkpoint, not a guarantee that every possible defect has been excluded or that Azure is already configured.

## Automated results

| Environment / check | Result |
| --- | --- |
| PHP 8.3.36 + MySQL 8.4.3 | 474 passed; 2,249 assertions |
| PHP 8.5.11 + MySQL 8.4.3 (localhost runtime) | 474 passed; 2,249 assertions |
| PHP 8.5.11 + isolated SQLite | 467 passed; 2,223 assertions; 7 MySQL-only cases skipped |
| JavaScript | 27 passed: 18 academic, 4 rule-preview, 5 income-preview |
| Production Vite build | Passed; CSS app-BINctOmj.css, JavaScript app-D22EgJo-.js |
| Composer validation | Passed |
| Locked-dependency audit, refreshed 8 November | 0 reported advisories; 0 abandoned packages |
| PHP/Blade syntax parsing | 174 files parsed successfully, including 71 Blade views |
| Configured application MySQL connection | Successful |
| Final whitespace check | Passed |

The backend rows are the same test suite exercised across different environments, not three independent sets of unique features. The seven skips are the MySQL-specific transfer/backup tests; these ran and passed in both MySQL environments.

The existing backend suite covers authentication and roles, onboarding, profiles/results and validation, grade scales and academic calculations, alternative qualification routes, scholarship administration, eligibility decisions/explanations, filters and pagination, bookmarks, recommendation logs, income synchronization, shared study fields, migration preservation, catalogue reseeding/replacement and database transfer/backup refusal and rollback paths. No new application code was required to obtain these results.

## Browser scenarios

Browser testing used the in-app browser with production-built assets, a database-backed test session, a separate cookie name and a separate database. All entered accounts, income values, awards and rules were synthetic.

| Area | Verified behaviour |
| --- | --- |
| Public pages | Home, branding and account links render. Mobile navigation expands and exposes Login/Get Started. |
| Guest protection | Opening the student dashboard redirects to Login. |
| Login validation | Incorrect credentials remain unauthenticated and show the expected error. |
| Password visibility | The password input switches from masked to visible without submitting the form. |
| Login/Register switch | The correct panel/URL opens. Card height stayed 502px in the measured empty/default state on desktop and mobile; mobile card width was 342px. |
| Password recovery | Forgot Password has a working return link; a synthetic reset-link request returns the expected status with an in-memory mail transport. Real email delivery was not tested. |
| New student | Registration opens Profile; saving Profile opens Academic Result; saving valid official SPM results reaches Dashboard. |
| Profile choices | Malaysian/Non-Malaysian choices and shortened Kuala Lumpur option are present. |
| Income feedback | Zero classifies normally; RM3169.50 reports the configured category gap; RM5000 shows M40; a valid profile saves. |
| SPM input | Plus/minus controls work. Five A grades plus three C grades give eight subjects, five As and eight credits. Official-result confirmation is required. |
| STPM calculation | Completed STPM with three As and one B, identifying B as Pengajian Am, produces 3.75. |
| Qualification switching | Switching to Senior UEC exposes its nine grade counters and hides study-year input. Unsaved qualification experiments did not replace the saved A-Level record. |
| Complete student login | A student with complete information goes to Dashboard without repeating setup. |
| Role restrictions | Student access to Admin Dashboard and admin access to Student Profile both return 403. |
| Restart persistence | Restarting only the test server preserves the existing valid database-backed login session. |
| Dashboard count | The synthetic ABB student initially has one eligible award; the recommendation list also reports one eligible award. |
| Eligibility explanations | ABB satisfies one A-or-better and three B-or-better. A requirement for two As correctly reports an actual count of one and a shortfall of one. |
| Provider conditions | Eligible ! expands the configured provider-verification text. |
| Bookmarking | A synthetic award can be saved, appears in Saved Scholarships, and can be unsaved so the page returns to its empty state. This does not delete a scholarship. |
| Filtering/pagination | Twenty-one not-eligible results show 10, 10 and 1 cards on pages 1–3. Search narrows results and resets pagination. |
| Mobile student pages | Recommendation filters stack above results at 390px. No document-wide horizontal overflow was detected. Mobile sidebar navigation/logout work. |
| Admin dashboard | Counts and quick actions render; the removed System Information card is absent. |
| Matching Settings layout | Income Categories appears before Fields of Study on the same page. |
| Study-field editing | Add a field, rename Engineering to Engineering Review and deactivate the new field. Linked student/rule labels update. The student form retains Engineering Review and excludes the inactive field from new assignments. |
| Income synchronization | Adjusting only test ranges reclassifies both test profiles to M40; the student helper displays the new range after refresh. |
| Rule editor | A grouped condition with one A and three B-or-better previews the minimum pattern ABB. |
| Hypothetical rule test | ABB passes, BBB fails, and changing the sample clears the previous result. |
| Scholarship administration | Create a scholarship, edit its name/provider conditions, deactivate/reactivate it and save an alternative UEC route with a maximum 11 total points across the best five subjects. |
| Student after admin edits | The created/edited award appears with Eligible !, the renamed field is selected and the changed income category is visible. |
| Recommendation logs | Filters render, the empty historical filter state works, and keyboard activation opens a saved log's full requirement snapshot. Earlier snapshot labels remain historical after settings changes. |
| Mobile admin | Matching Settings fits at 390px; wide income columns scroll inside their table container rather than widening the page. |
| Browser console | No JavaScript errors were captured in the final resumed browser session. |

The bookmark interaction briefly stalled the automation transport; a fresh tab visibly confirmed the bookmark had been removed. Log details were verified through keyboard activation after the automated pointer action did not navigate. These observations do not establish a reproducible application failure; they limit claims about those particular pointer-driven automation steps.

## Data isolation and preservation

Automated MySQL tests used the pre-existing dedicated scholarmatch_test database and a newly created scholarmatch_fullcheck_test85_20261107 database. SQLite tests ran in memory. Browser tests used scholarmatch_fullcheck_20261107 at port 8147, with its own session-cookie name. The browser fixture initially contained 20 fictional catalogue awards plus two synthetic boundary awards; one more synthetic award was created through the admin form.

The application database was never reset, reseeded or used as a test target. No actual scholarship, account or bookmark was deleted by these checks.

The initial 7 November fingerprint checkpoint matched all ten live business tables during that day's isolated testing. On resuming on 8 November, the live rows differed: the user confirmed independently creating/editing local data while testing was in progress. Those changes were retained. A fresh 8 November checkpoint was captured after that confirmation; all ten tables matched its counts and row hashes at the end. No synthetic fullcheck accounts were present in the real database.

Final preserved live counts at the resumed checkpoint:

| Table | Rows |
| --- | ---: |
| users | 7 |
| student_profiles | 4 |
| academic_results | 4 |
| income_categories | 3 |
| scholarships | 24 |
| scholarship_rules | 24 |
| scholarship_qualification_routes | 66 |
| saved_scholarships | 0 |
| recommendation_logs | 46 |
| study_fields | 21 |

After verification, the temporary preview server and both newly created test databases were removed/stopped using explicit identity/count guards. The existing application database and scholarmatch_test were retained. Test-only compiled-view folders were cleaned; logs, receipts and screenshots were retained privately. MySQL was started with its existing Laragon configuration when the resumed session found it stopped; no server settings were changed.

## Evidence

These files are local, Git-ignored artifacts. They contain synthetic browser data or aggregate fingerprints, not a public deployment record:

- storage/logs/full-system-mysql83.log and .xml
- storage/logs/full-system-mysql85.log and .xml
- storage/logs/full-system-sqlite85.log and .xml
- storage/logs/full-system-js.log
- storage/logs/full-system-build.log
- storage/logs/full-system-audit-final.json
- storage/app/private/full-system-check-before-20261107.json
- storage/app/private/full-system-check-before-20261108.json
- storage/app/private/full-system-preservation-receipt-20261108.json
- storage/app/private/full-system-student-desktop.png
- storage/app/private/full-system-student-mobile.png
- storage/app/private/full-system-admin-desktop.png
- storage/app/private/full-system-admin-mobile.png
- storage/app/private/full-system-final-checkpoint.png

## Limits and next step

- This verifies the current local source and tested flows. It is not exhaustive proof that no bugs exist.
- Responsive checks used a desktop browser viewport, not physical Android/iOS devices or every browser engine. No full accessibility audit was performed.
- Password-reset logic is covered by automated tests; local browser requests used a non-delivering mail transport. SMTP delivery needs a real configured mail service and its own test.
- No real provider applications, external award availability or scholarship award decisions were exercised.
- No load/endurance test or penetration test was performed. A clean dependency audit is not a complete security assessment.
- Azure resources, networking/TLS, deployment artifacts, production environment settings, database restoration and the live URL still need configuration and verification.

The next planned work can be Azure preparation, using this report as the local regression baseline. The only repository changes from this full regression are test documentation/handoff updates; no application feature or dependency version changed.
