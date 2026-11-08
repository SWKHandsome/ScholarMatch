# Academic qualifications and grade-count matching

Implemented 31 October 2026 and extended with explicit requirement types. See [Academic Requirement Types](ACADEMIC_REQUIREMENT_TYPES.md) for the current selector, STPM grade counts, and STPM-average/UEC-total formulas.

## Student selection and results

As of 8 November 2026, Field of Study and Institution Type under Programme Seeking Funding each offer **Haven't decided**. Either or both answers complete those selections; blank answers still require correction. The latest qualification, official results and funded study level remain required. These are intended programme preferences, not facts inferred from the student's latest qualification.

A restricted undecided preference produces **Not checked**, with the scholarship's actual requirement and guidance kept visible. All checked requirements passing still produces **Eligible**; a definite failure elsewhere produces **Not Eligible**. Unrestricted criteria stay **No restriction**, and undecided preferences do not trigger the special-condition !. Later programme choices are compared normally. The stored __undecided__ sentinel uses the existing academic string columns, so no migration or catalogue change is needed.

Choose an education category, then its current/latest qualification. Category is derived from the qualification and is not a separate stored column.

| Category | Stored qualifications | Result format |
| --- | --- | --- |
| Secondary | SPM, IGCSE, O-Level | Official final grade counts |
| Pre-U, Diploma & Pathway | STPM, STAM, A-Level, UEC, Matriculation, Foundation, Diploma | STPM official CGPA plus grade counts; STAM overall pangkat; final grade counts for A-Level/UEC; official CGPA for Matriculation/Foundation/Diploma |
| Undergraduate | Undergraduate | Official cumulative/final CGPA |

The UI displays Undergraduate as Bachelor’s Degree, UEC as Senior UEC and O-Level as Cambridge O-Level. Stored identifiers remain compatible with existing scholarship records. These categories organise navigation; they do not establish academic equivalence between qualifications.

| Grading scale | Grades in descending achievement order |
| --- | --- |
| spm | A+, A, A-, B+, B, C+, C, D, E, G |
| igcse_letters | A*, A, B, C, D, E, F, G, U |
| igcse_9_1 | 9, 8, 7, 6, 5, 4, 3, 2, 1, U |
| o_level / a_level | A*, A, B, C, D, E, U |
| uec | A1, A2, B3, B4, B5, B6, C7, C8, F9 |
| stpm | A, A-, B+, B, B-, C+, C, C-, D+, D, F |

IGCSE students explicitly select letters or 9–1. One record holds one grading scale; the system does not convert between them. A-Level means the full qualification; AS-Level is outside this version. AUSMAT, IB Diploma, CIMP and unspecified qualifications are also outside this version.

Counters support minus/plus buttons and direct whole-number entry. Zero is permitted for an unused grade; at least one subject must be entered. Negative, fractional, boolean, nested, unknown-grade and mixed-scale values are rejected server-side. The application currently supports a maximum total of 12 SPM subjects, 5 STPM subjects and 20 subjects for the other grade-count formats. These are ScholarMatch input limits, not statements of examination-board maximums.

The grade summary and total update immediately. For SPM, As count A+, A and A-, while credits count C and above. For example, 2A+, 1A, 1A-, 2B, 1C and 1G produces 8 subjects, 4 As and 7 credits. The server derives these totals; submitted spm_as/spm_credits values cannot override them.

SPM, IGCSE, O-Level, A-Level, UEC and STAM require Completed status and official final results. STPM requires subject counts and retains studying/completed status and current year when studying. Completed STPM now calculates overall CGPA from four/five final subjects and an identified Pengajian Am grade, with confirmation against the official slip; studying STPM retains manual official CGPA. See STPM_CGPA_CALCULATION.md. STAM uses the printed overall pangkat, ordered Mumtaz, Jayyid Jiddan, Jayyid, Maqbul, Rasib. CGPA-based students retain Currently Studying/Completed, current year when studying, and official cumulative/final CGPA. Zero CGPA remains a valid result. Forecast/expected/pending results cannot generate recommendations.

Changing category, qualification or grading scale clears incompatible results and official-result confirmation in the form. Switching result format clears unused stored fields on save. Programme Seeking Funding retains its separate funded qualification, institution and field.

## Administrator requirements

The shared Add/Edit Scholarship and Rules editor uses Add Another Requirement and a qualification-specific Requirement Type selector, with a live preview and cumulative grade-pattern summary. Grade thresholds share one group and are added with Add Grade Row; each row is still a separate AND condition in the stored flat list. It supports:

- Existing SPM minimum As/credits and CGPA/year restrictions.
- Grade-count conditions containing grade and min_subjects. Each means at least that many subjects at the selected grade or better in that qualification’s own ordering.
- Optional exact IGCSE scale. An IGCSE route without a scale or grade minimum accepts either scale; use separate boxes when defining alternative scale-specific thresholds.
- Minimum overall STAM pangkat, accepting that rank or better.
- An explicit UEC average object containing subjects, minimum_grade and max_average, configured only when that formula is stated by the provider.
- STPM best-subject minimum average and UEC best-subject maximum total, as described in ACADEMIC_REQUIREMENT_TYPES.md. Official overall CGPA remains separate from averages of selected subjects.

All configured conditions inside a box use AND; complete boxes use OR. For AAB, configure 2 subjects at A or above AND 3 at B or above. A*AB passes; ABB and AAC fail. Stronger grades count towards all applicable threshold conditions, but those conditions describe the same set of results rather than adding subjects together.

UEC A1-only requirements select threshold A1; A2-or-better includes A1 and A2. The supported UEC average formula selects the best N subjects meeting the configured minimum grade, maps A1=1 through F9=9 and compares their unrounded average against a maximum. A lower average is better. Too few qualifying subjects fails the condition; it is never divided by a smaller number. For A1, A2, B3, B4 and B6, the five-subject average is 3.20. Other conversion formulas belong in Special Requirements until explicitly supported.

Maximum-average thresholds use at most two decimal places, matching the admin input and explanation. Actual averages are not rounded into a pass.

Grade counts cannot identify Mathematics or another named subject. Administrators put subject-specific conditions in Special Requirements; the system never claims to have automatically verified those subjects. Automatic checks passing with special text produces Eligible !; an automatic failure remains Not Eligible. Existing application-date/provider verification behaviour is retained.

## Stored data and migration

AcademicResult adds nullable grading_scale (string), grade_counts (JSON grade-to-count map), and stam_rank (string). The completed-STPM calculator adds nullable stpm_pa_grade and default-false stpm_cgpa_confirmed. SPM aggregate columns remain for compatibility and are derived on new submissions. ScholarshipQualificationRoute adds nullable grading_scale, grade_requirements (JSON condition list), min_stam_rank and uec_average (JSON formula settings). The follow-up migration adds stpm_average and uec_total JSON objects.

Migration 2026_10_31_000000_add_qualification_grade_counts extends the qualification enum and adds these fields. It preserves all existing values and rows. It does not invent a grade distribution from old SPM As/credits. Existing SPM students see their preserved totals and must enter a real breakdown before matching. Valid non-STPM CGPA records continue working; existing CGPA-only STPM records retain their values and need their actual subject counts before matching. Rollback refuses to discard stored new-format results or rules; preserve/export them before rollback.

AcademicQualifications supplies the shared PHP/JavaScript definitions. AcademicResultController validates and normalises submissions; ScholarshipRuleEditor validates/clears route-specific fields; AcademicRouteEvaluator computes the comparisons. Algorithm version decisions-v9-undecided-programme refreshes prior cached evaluations/logs. Legacy CGPA inference remains limited to the original five CGPA qualifications, so it does not accidentally accept new exam-based qualifications.

## Verification and FYP documentation

Update the functional requirements, academic-entry use case/activity diagram, student/admin user guide, form screenshots, data dictionary/ERD and qualification-rule pseudocode. Explain the separation between category, qualification, results and funded programme, plus the limits of grade counts and subject-specific provider checks. Pengajian Am identification for completed STPM is the one supported subject-specific exception; it is used for overall CGPA only.

Automated coverage includes form persistence, derived totals, invalid inputs, both IGCSE scales, A-Level AND/OR cases, UEC threshold direction and averages, STAM ordering, migrations and preservation of existing data. JavaScript tests cover counter limits, clearing incompatible values and stable editor identities. Synthetic browser checks cover save/reload, mobile layout and restoring saved admin grade selections.

Grade/reference sources:

- [Cambridge result formats](https://www.cambridgeinternational.org/exam-administration/results/statements-of-results/).
- [Cambridge A-Level and AS-Level qualifications](https://www.cambridgeinternational.org/programmes-and-qualifications/cambridge-advanced/cambridge-international-as-and-a-levels/qualification/).
- [STAM rank definitions](https://www.spa.gov.my/?catid=14&id=554:kelayakan-stam&view=article).
- [Example of a provider-defined UEC entry average](https://www.monash.edu.my/study/entry-requirements/academic/undergraduate/calculating-entry-scores).
