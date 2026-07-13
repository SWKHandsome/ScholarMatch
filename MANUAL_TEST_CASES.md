# ScholarMatch — Manual Test Cases

**Project:** FYP ScholarMatch — Scholarship Recommendation Platform for Malaysian Students  
**Stack:** Laravel 13 + PHP 8.3 + Pest + Tailwind CSS + Vite + SQLite (dev)  
**Last Updated:** 2026-07-13  
**Test Suite Status:** 117/117 automated tests passing

---

## Setup

```bash
php artisan migrate:fresh --seed
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

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| AR1 | View edit page | `/student/academic-result/edit` | Form with education level dropdown |
| AR2 | Create SPM result | Level: SPM, As: 8, Credits: 5, Status: Official → Save | Saved, CGPA hidden |
| AR3 | Create Undergrad CGPA | Level: Undergraduate, CGPA: 3.75, Status: Official → Save | Saved, SPM fields hidden |
| AR4 | Create pending result | Any level, Status: Pending → Save | Saved, `result_status = 'pending'` |
| AR5 | Update result | Change CGPA 3.75 → 3.90 → Save | Updated |
| AR6 | Validation: missing level | Submit without education level | Error required |
| AR7 | Validation: SPM missing As/credits | SPM level, no As/credits, Official | Error "SPM requires As or Credits" |
| AR8 | Validation: non-SPM missing CGPA (official) | Undergrad, no CGPA, Official | Error "CGPA required for official results" |
| AR9 | Validation: non-SPM allows missing CGPA (pending) | Undergrad, no CGPA, Pending | Saves OK |
| AR10 | Validation: CGPA range | CGPA: 4.5 or -0.5 | Error "between 0 and 4" |
| AR11 | Validation: SPM As range | As: 13 or -1 | Error "between 0 and 12" |
| AR12 | Role guard | Admin → `/student/academic-result/edit` | 403 Forbidden |

---

## 5. Student Recommendations (Core Feature)

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| R1 | No profile | Fresh student → `/student/recommendations` | Error: "Complete your profile first" |
| R2 | Profile only, no academic | Complete profile → `/student/recommendations` | Error: "Add academic results first" |
| R3 | Eligible student (B40, Engineering, 3.5 CGPA) | Complete both → `/student/recommendations` | List of eligible scholarships with scores, status badges |
| R4 | Not suitable student (T20, non-matching) | T20 profile, Arts field → `/student/recommendations` | "Not Suitable" badges, failed hard rules shown |
| R5 | Preliminary badge | Pending academic result → `/student/recommendations` | Yellow "Preliminary" badge on each card |
| R6 | Sort order | Multiple matches | Highest score first |
| R7 | Detail page (eligible) | Click "View Details" on eligible card | `/student/recommendations/{id}` shows: circular progress, score breakdown, explanations, scholarship details, Apply/Save buttons |
| R8 | Detail page (not suitable) | Click on Not Suitable card | Shows failed hard rules in red, soft score breakdown |
| R9 | Detail page (preliminary) | Pending result student | Shows "Preliminary Guidance" notice |
| R10 | Apply button | Click "Apply Now" on detail | Opens scholarship `application_link` in new tab |
| R11 | Role guard | Admin → `/student/recommendations` | 403 Forbidden |

---

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

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| AR1 | Rules index | `/admin/scholarships/1/rules` | Shows existing rule for scholarship |
| AR2 | Create page | Click "Add Rules" | Form with all rule fields + 4 rule type dropdowns |
| AR3 | Create rule | Fill nationality, income B40, study level Undergrad, income_rule_type=hard, study_level_rule_type=hard, others=soft → Save | Success, rule appears |
| AR4 | Edit page | Click "Edit" on rule | Pre-filled form |
| AR5 | Update rule | Change income to M40, income_rule_type=soft → Save | Updated |
| AR6 | Validation: rule types required | Submit with empty dropdowns | Errors on all 4 rule type fields |
| AR7 | Validation: rule types valid | Submit with "invalid" type | Error "must be hard/soft/none" |
| AR8 | One rule per scholarship | Try create second rule for same scholarship | Unique constraint prevents (handled by updateOrCreate) |
| AR9 | Role guard | Student → rules pages | 403 Forbidden |

---

## 11. Admin Income Categories

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| IC1 | Index page | `/admin/income-categories` | Table: B40 (≤3169), M40 (3170-6339), T20 (≥6340) |
| IC2 | Classify method | Click "Classify" or test via seeder | 2500→B40, 5000→M40, 8000→T20 |
| IC3 | Role guard | Student → `/admin/income-categories` | 403 Forbidden |

---

## 12. Admin Recommendation Logs

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| RL1 | Index page | `/admin/recommendation-logs` | Table with student, scholarship, score, status, date |
| RL2 | Detail page | Click "View" on log row | Shows: score breakdown, explanations, failed hard rules (if any) |
| RL3 | Failed hard rules display | View log for Not Suitable student | Red badges with specific failed rules |
| RL4 | Role guard | Student → logs pages | 403 Forbidden |

---

## 13. Edge Cases & Integration

| # | Test Case | Steps | Expected |
|---|-----------|-------|----------|
| E1 | Expired scholarship hidden | Set scholarship deadline to yesterday → student recommendations | Not shown in recommendations |
| E2 | Inactive scholarship hidden | Set `is_active=false` → student recommendations | Not shown |
| E3 | Hard rule filter priority | Student fails nationality hard rule but passes all soft | Status = "Not Suitable", score = 0, failed hard rule shown |
| E4 | Soft scoring only | All rules soft, partial match | Score < 100, status based on threshold (80/50) |
| E5 | Score breakdown totals 100 | Check detail page breakdown | Academic 40 + Field 25 + Institution 20 + Income 15 = 100 |
| E6 | Recommendation logging | Generate recommendation → `/admin/recommendation-logs` | Log entry created with score, status, breakdown, explanations |
| E7 | Mobile responsive | Resize browser < 768px | Cards stack, navigation collapses, tables scroll |
| E8 | Design tokens | Check colors | Primary #00288E, Success #10B981, badges rounded-full |

---

## Quick Regression Checklist (run after any change)

```bash
# 1. Full test suite
php artisan test

# 2. Fresh DB + seed
php artisan migrate:fresh --seed

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

**12 Scholarships seeded** across categories:
- Government: Yayasan Khazanah, MARA, JPA, PTPTN
- Corporate: Petronas, Maybank, CIMB, Khazanah, Sime Darby, YTL, Sunway, Taylor's

**Rules cover:** nationality, income category, study level, field of study, institution type, SPM As/credits, CGPA, max income — with mix of hard/soft/none types.

```bash
# Inspect rules
php artisan db:seed --class=ScholarshipRuleSeeder
```

---

## Automated Test Coverage (117 tests)

| Area | Tests |
|------|-------|
| Auth | 18 |
| Profile (Breeze) | 5 |
| Student Profile | 10 |
| Student Academic Result | 14 |
| Student Dashboard | 7 |
| Student Recommendations | 9 |
| Student Saved Scholarships | 7 |
| Admin Dashboard | 3 |
| Admin Scholarships | 8 |
| Admin Scholarship Rules | 9 |
| Admin Income Categories | 5 |
| Admin Recommendation Logs | 6 |
| Unit: Recommendation Service | 12 |
| **Total** | **117** |

Run: `php artisan test --testdox` for full list.