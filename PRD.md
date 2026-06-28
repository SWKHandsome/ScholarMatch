# ScholarMatch — Product Requirements Document (PRD)

**Project Title:** ScholarMatch: A Result-Based and Socioeconomic Scholarship Recommendation Platform for Malaysian Students  
**Project Type:** Final Year Project (FYP) Web Application  
**Target Users:** Malaysian students seeking scholarships  
**Recommended Stack:** Laravel, PHP, MySQL, Blade, Tailwind CSS or Bootstrap  
**Version:** 2.0  

---

## 1. Product Overview

ScholarMatch is a web-based scholarship recommendation platform that helps Malaysian students identify scholarships matching their academic results and socioeconomic background.

The system is not only a scholarship listing website. It is an explainable eligibility-matching platform that compares student profiles with scholarship rules and classifies each scholarship as:

- **Eligible**
- **Partially Eligible**
- **Not Suitable**

Each recommendation includes explanation messages so students can understand why they qualify or do not qualify.

---

## 2. Problem Statement

Malaysian students often struggle to find suitable scholarships because scholarship information is scattered across government portals, university websites, private institutions, foundations, and scholarship listing platforms.

Many scholarships contain multiple eligibility requirements, such as nationality, academic results, household income, income category, study level, field of study, institution type, and application deadline. Students may not know whether they qualify because comparing these requirements manually is time-consuming and confusing.

This issue is especially important for lower-income students, such as B40 students, who may depend on financial support to continue higher education. ScholarMatch aims to reduce this problem by providing a centralized and explainable scholarship matching system.

---

## 3. Product Goals

ScholarMatch aims to:

1. Help Malaysian students find scholarships that match their profile.
2. Recommend scholarships using academic and socioeconomic criteria.
3. Prevent false-positive recommendations by using hard eligibility filters.
4. Rank suitable scholarships using soft scoring.
5. Explain each recommendation result clearly.
6. Allow admins to manage scholarship data, rules, and income thresholds.
7. Provide a functional localhost prototype for FYP demonstration.

---

## 4. Target Users

### 4.1 Student Users

Students searching for scholarships after completing or currently pursuing:

- SPM
- STPM
- Foundation
- Matriculation
- Diploma
- Undergraduate degree

### 4.2 Admin Users

Admins manage scholarship records, eligibility rules, income categories, sample data, and recommendation logs.

---

## 5. User Stories

### Student User Stories

- As a student, I want to register and log in so that I can save my profile.
- As a student, I want to enter my academic results so that the system can check scholarship requirements.
- As a student, I want to enter my household income so that financial-need-based scholarships can be matched.
- As a student, I want to view scholarships classified as Eligible, Partially Eligible, or Not Suitable.
- As a student, I want to see reasons for each result so that I understand my eligibility.
- As a student, I want to save scholarships so that I can review them later.

### Admin User Stories

- As an admin, I want to add, edit, and deactivate scholarships.
- As an admin, I want to manage eligibility rules for each scholarship.
- As an admin, I want to manage B40, M40, and T20 income thresholds.
- As an admin, I want to seed sample scholarships for testing and demonstration.
- As an admin, I want to view recommendation logs for evaluation.

---

## 6. Core Features

## 6.1 Authentication and Role Management

The system shall support:

- Student registration
- Student login
- Admin login
- Logout
- Role-based access control

Roles:

- `student`
- `admin`

Students can access only student pages. Admins can access only admin management pages.

---

## 6.2 Student Profile Management

Students shall be able to create and update their profile.

Profile fields:

- Full name
- Nationality
- State
- Household income
- Number of dependents
- Income category
- Institution type
- Field of study

The system should automatically classify income category based on household income using admin-managed thresholds.

Income categories:

- B40
- M40
- T20

---

## 6.3 Academic Result Management

Students shall be able to enter academic information.

Academic fields:

- Education level
- SPM number of As
- SPM credits
- CGPA
- Result status

Education level options:

- SPM
- STPM
- Foundation
- Matriculation
- Diploma
- Undergraduate

For SPM students, the system uses SPM As and SPM credits.  
For STPM, Foundation, Matriculation, Diploma, and Undergraduate students, the system uses CGPA based on the selected education level.

Result status options:

- Official
- Pending

If the result status is pending, the system may show preliminary guidance only, not final eligibility.

---

## 6.4 Scholarship Management

Admins shall be able to create, view, update, and deactivate scholarship records.

The `scholarships` table should store scholarship metadata only.

Scholarship metadata fields:

- Scholarship name
- Provider
- Description
- Award type
- Deadline
- Application link
- Active status

Award type examples:

- Full scholarship
- Partial scholarship
- Tuition fee waiver
- Living allowance
- Loan
- Convertible loan

---

## 6.5 Scholarship Rule Management

All matching criteria should be stored in `scholarship_rules`, not duplicated in the `scholarships` table.

Rule fields:

- Scholarship ID
- Required nationality
- Required study level
- Required income category
- Maximum household income
- Minimum SPM As
- Minimum SPM credits
- Minimum CGPA
- Required field of study
- Required institution type
- Income rule type
- Study level rule type
- Field rule type
- Institution rule type

Rule type values:

- `hard`
- `soft`
- `none`

A hard rule acts as a strict pass/fail requirement. A soft rule contributes to ranking. A none rule is ignored.

---

## 6.6 Recommendation Engine

The recommendation engine shall use two phases:

1. **Hard Eligibility Filtering**
2. **Soft Scoring and Ranking**

### Phase 1: Hard Eligibility Filtering

The system first checks strict scholarship requirements. If a student fails any hard rule, the scholarship is classified as **Not Suitable**, regardless of score.

Hard rules may include:

- Nationality
- Required income category
- Maximum household income
- Required study level
- Deadline validity
- Required field of study, if marked hard
- Required institution type, if marked hard

Example:

If a scholarship is strictly for B40 students and the student is categorized as T20, the result must be **Not Suitable**, not Partially Eligible.

### Phase 2: Soft Scoring and Ranking

Soft scoring only runs after the student passes all hard rules.

Suggested score weights:

- Academic result match: 40 points
- Field of study match: 25 points
- Institution type match: 20 points
- Income priority match: 15 points

Total score: 100 points.

Recommendation classification after hard filters are passed:

- **80–100:** Eligible
- **50–79:** Partially Eligible
- **0–49:** Not Suitable

Results should be sorted from highest score to lowest score.

---

## 6.7 Recommendation Explanation

Each recommendation must include explanation messages for both hard-rule checks and soft scoring.

Example explanations:

- You meet the nationality requirement.
- This scholarship is only open to B40 students.
- Your CGPA meets the minimum requirement.
- Your education level matches the scholarship rule.
- Your field of study partially matches this scholarship.
- This scholarship has passed its deadline.

Explanation is a key differentiator of ScholarMatch.

---

## 6.8 Saved Scholarships

Students shall be able to save or remove scholarships from their saved list.

Saved scholarship data:

- User ID
- Scholarship ID
- Saved date

---

## 6.9 Recommendation Logs

The system should store recommendation logs for testing and evaluation.

Log fields:

- User ID
- Scholarship ID
- Score
- Status
- Failed hard rules
- Explanation
- Created date

---

## 7. Main Pages

### Public Pages

- Home page
- About page
- Login page
- Register page

### Student Pages

- Student dashboard
- Edit profile page
- Academic result form
- Recommendation results page
- Scholarship detail page
- Saved scholarships page

### Admin Pages

- Admin dashboard
- Manage scholarships
- Add scholarship
- Edit scholarship
- Manage scholarship rules
- Manage income categories
- Recommendation logs

---

## 8. Database Tables

Main tables:

- `users`
- `student_profiles`
- `academic_results`
- `income_categories`
- `scholarships`
- `scholarship_rules`
- `saved_scholarships`
- `recommendation_logs`

### users

Fields:

- `id`
- `name`
- `email`
- `password`
- `role`
- `created_at`
- `updated_at`

### student_profiles

Fields:

- `id`
- `user_id`
- `nationality`
- `state`
- `household_income`
- `number_of_dependents`
- `income_category`
- `institution_type`
- `field_of_study`
- `created_at`
- `updated_at`

### academic_results

Fields:

- `id`
- `user_id`
- `education_level`
- `spm_as`
- `spm_credits`
- `cgpa`
- `result_status`
- `created_at`
- `updated_at`

### income_categories

Fields:

- `id`
- `name`
- `min_income`
- `max_income`
- `created_at`
- `updated_at`

### scholarships

Fields:

- `id`
- `name`
- `provider`
- `description`
- `award_type`
- `deadline`
- `application_link`
- `is_active`
- `created_at`
- `updated_at`

### scholarship_rules

Fields:

- `id`
- `scholarship_id`
- `required_nationality`
- `required_study_level`
- `required_income_category`
- `max_household_income`
- `min_spm_as`
- `min_spm_credits`
- `min_cgpa`
- `required_field_of_study`
- `required_institution_type`
- `income_rule_type`
- `study_level_rule_type`
- `field_rule_type`
- `institution_rule_type`
- `created_at`
- `updated_at`

### saved_scholarships

Fields:

- `id`
- `user_id`
- `scholarship_id`
- `created_at`
- `updated_at`

### recommendation_logs

Fields:

- `id`
- `user_id`
- `scholarship_id`
- `score`
- `status`
- `failed_hard_rules`
- `explanation`
- `created_at`
- `updated_at`

---

## 9. Missing Data Handling

If required student data is missing, the system should not generate final recommendations.

Examples:

- If profile is incomplete, show: “Please complete your student profile first.”
- If academic result is missing, show: “Please enter your academic result first.”
- If CGPA is pending, show results as preliminary guidance only.
- If a scholarship rule is incomplete, nullable fields should be treated as not required.

---

## 10. Data Seeding Strategy

To avoid an empty MVP, the system shall include sample seed data.

Seeders should include:

- Admin user
- Student test user
- Income categories
- 10 to 20 sample scholarships
- Scholarship rules for each sample scholarship

Sample scholarship categories:

- B40 scholarship
- Merit scholarship
- Foundation scholarship
- Diploma scholarship
- Undergraduate scholarship
- STEM scholarship
- Public university scholarship
- Private university scholarship
- State-based scholarship
- General financial aid

The project should support this command:

```bash
php artisan migrate:fresh --seed
```

---

## 11. Validation Rules

### Student Profile

- Household income is required and numeric.
- Nationality is required.
- State is required.
- Field of study is required.
- Institution type is required.

### Academic Result

- Education level is required.
- For SPM students, SPM As or SPM credits should be required.
- For non-SPM students, CGPA should be required unless result status is pending.
- CGPA must be between 0.00 and 4.00.

### Scholarship

- Scholarship name is required.
- Provider is required.
- Deadline is required.
- Application link should be a valid URL.

---

## 12. Non-Functional Requirements

### Usability

The interface should be simple, clean, and student-friendly.

### Maintainability

Scholarship rules and income thresholds should be editable by admin without source code changes.

### Security

The system should use authentication, validation, password hashing, and role-based access control.

### Explainability

Every recommendation should show clear reasons.

### Performance

Recommendations should load quickly for a small to medium dataset.

---

## 13. MVP Scope

The first version should include:

- Student login and registration
- Admin login
- Student profile form
- Academic result form
- Scholarship CRUD
- Scholarship rule CRUD
- Income category CRUD
- Hard-filter and soft-scoring recommendation engine
- Recommendation explanation
- Saved scholarships
- Recommendation logs
- Seed sample data
- Basic dashboards

---

## 14. Out of Scope for MVP

Not required in the first version:

- Real-time scraping
- Payment integration
- Mobile app
- Complex machine learning model
- Automatic application submission
- Document verification
- Email notification
- PDF report generation

---

## 15. Future Business Potential

ScholarMatch can remain free for students while exploring future sustainability through:

- Premium scholarship listings for providers
- Lead generation for private universities
- Sponsored scholarship campaigns
- Analytics dashboards for institutions
- Application preparation services

These are future possibilities and are not part of the MVP.

---

## 16. Success Criteria

The prototype is successful if:

1. Students can create profiles and enter academic results.
2. Admins can manage scholarships and rules.
3. The system rejects scholarships when hard rules fail.
4. The system ranks suitable scholarships using scores.
5. Each result includes explanation messages.
6. Sample seed data allows immediate testing.
7. The system can be demonstrated on localhost.

---

## 17. Final Notes

ScholarMatch should prioritize correctness, explainability, and usability. The core value is not complex AI, but a reliable eligibility engine that combines Malaysian student academic profiles with socioeconomic scholarship criteria.
