# Academic requirement types

Implemented 31 October 2026. This extends the qualification and grade-count design in ACADEMIC_QUALIFICATIONS.md.

## Administrator workflow

On Add/Edit Scholarship or Rules, add a qualification box and choose its qualification/status. Click **Add Another Requirement**, then choose **Requirement Type**. The editor shows only supported choices and fields; every added condition is required to be complete. Empty boxes still restrict qualification/status but have no result minimum.

| Type | Qualifications | Settings |
| --- | --- | --- |
| Minimum overall CGPA | STPM, Foundation, Matriculation, Diploma, Bachelor’s Degree | Minimum official CGPA |
| Number of subjects at a grade or better | SPM, IGCSE, O-Level, A-Level, UEC, STPM | Minimum count and grade threshold |
| Minimum SPM As / credits | SPM | Minimum count; existing A+/A/A- and C-or-better definitions |
| Minimum overall pangkat | STAM | Minimum rank |
| Minimum best-subject average | STPM | Number of best subjects, minimum included grade, minimum average |
| Maximum best-subject average | UEC | Number of best subjects, minimum included grade, maximum average |
| Maximum best-subject total | UEC | Number of best subjects, minimum included grade, maximum total |

Each requirement has a live preview. Grade-count conditions share one Requirement Type selector and a compact list of Minimum Subjects / At Grade or Better rows. Add Grade Row appends a threshold inside this group; Add Another Requirement adds a separate type such as CGPA or an average. Each row has Remove; removing the last row removes only the grade group. Remove this requirement removes the whole selected group. Grade groups have one combined cumulative minimum pattern: A-Level 1 subject at A or better AND 3 at B or better previews A, B, B. The A already counts towards the three. AAB and AAA also pass; AB and AAC do not satisfy that pattern. UEC 2 As including A1/A2 is 2 subjects at A2 or better. STPM 1A uses A, or A- only when explicitly accepted by the provider.

All conditions within a box use AND. Complete boxes use OR. Overall CGPA and point conditions can coexist and must both pass. Removing a condition clears its restriction on save. Changing qualification clears previous result conditions; changing IGCSE scale clears grade thresholds. Other conditions retain their stable values when a row is removed. Saved old SPM/CGPA/grade/STAM/UEC-average rules reopen in the new editor without changing their meaning.

## Student STPM data

STPM collects subject grade counts. Completed students now also identify the Pengajian Am grade already included in four/five final results; overall CGPA is calculated and confirmed against the official slip. Studying students retain manual official cumulative CGPA and current year. No other subject names are collected. See [Completed STPM CGPA Calculation](STPM_CGPA_CALCULATION.md).

The scale is A, A-, B+, B, B-, C+, C, C-, D+, D, F. The application allows at most five STPM subjects. Existing CGPA values remain untouched; legacy STPM students without subject counts must still enter their actual counts before recommendations. Previously usable completed manual records remain valid until edited, when PA identification and calculation confirmation are required. Missing counts cause a completion prompt, not Not Eligible or a special-condition warning. No production distributions are inferred.

## STPM averages and ambiguity

Subject points are A=4.00, A-=3.67, B+=3.33, B=3.00, B-=2.67, C+=2.33, C=2.00, C-=1.67, D+=1.33, D=1.00 and F=0.00. Select exactly the best N subjects meeting the configured grade cutoff, sum their points and divide by N. Too few qualifying subjects fails. Compare the unrounded result to the minimum; a zero minimum is valid. Thresholds allow at most two decimal places.

For A, B, C, C, the best-three average is (4+3+2)/3 = 3.00. Three subjects each at 3.00 or better would require three B-or-better grades and fails this example. Official overall CGPA is another value and is checked separately.

The phrase “3.0 in 3 subjects” does not alone establish best-three averaging versus a minimum in each subject. Use automatic rules only after confirming the provider's intended formula. The overall MPM calculation includes Pengajian Am, so a provider's best-three calculation must not be substituted for official CGPA. If a formula includes/excludes named subjects, aggregate counts cannot verify it: use Special Requirements. A-Level converted CGPA also stays in Special Requirements unless a documented conversion is supported.

## UEC total points

For a confirmed requirement of at most 11 points across the best five subjects, choose **Maximum total points**, enter 11 and 5, and choose the provider's included-grade cutoff. Use F9 when no additional cutoff is stated. A1=1 through F9=9; lower is better. A1, A1, A2, B3, B4 totals 11 and passes. Five A1s total 5 and pass. Too few qualifying subjects fails. Totals are whole numbers between N and 9N; average thresholds remain separate and allow up to two decimal places.

“Minimum 11 points” is ambiguous on this lower-is-better scale. Confirm the comparison direction before using the automatic maximum-total condition. Unsupported formulas remain provider-verification text.

## Storage, migration and evaluation

Existing min_cgpa, min_spm_as/min_spm_credits, grade_requirements, min_stam_rank and uec_average storage remains active. The selector itself is UI state; it renders names for the corresponding validated fields. Each qualification has at most one grade-count group, with up to 20 grade rows. Existing flat grade_requirements are loaded into this group and submitted back as the same flat list; grouping is UI state only. Other types have one setting per box.

Migration 2026_10_31_010000_add_subject_point_requirements adds nullable JSON fields stpm_average and uec_total to scholarship_qualification_routes. Their shapes are {subjects, minimum_grade, min_average} and {subjects, minimum_grade, max_total}. It changes no existing rows and refuses rollback when those fields contain data. Student STPM counts reuse grading_scale/grade_counts from the earlier additive migration. The follow-up completed-STPM calculation adds stpm_pa_grade/stpm_cgpa_confirmed to academic_results and advances the cache/log version to decisions-v8-stpm-cgpa.

## Verification and report updates

Update student/admin screenshots, functional requirements, activity diagrams, data dictionary/ERD route fields, and matching pseudocode. Explain why overall CGPA, a count of grades, an average and a total are different. Tests cover persistence, restored legacy conditions, A versus A-, exact/near-miss averages, grade cutoffs, insufficient subjects, AND/OR, UEC total direction, unsupported formulas, validation and protected rollback. JavaScript tests cover type switching, preserved values, generated field names, grouped legacy thresholds, first/final-row removal, row limits and the ABB preview.

Sources: [MPM STPM regulations](https://www.mpm.edu.my/images/dokumen/calon-peperiksaan/stpm/Peraturan_dan_skema/Peraturan_dan_Skema_STPM_2025__KEMASKINI472024.pdf), [APU's published wording](https://new.apu.edu.my/apu-malaysian-scholarships-merit-awards), and [Cambridge on GPA calculations](https://www.cambridgeinternational.org/exam-administration/results/). These references describe formats/limitations; they do not imply every listed scholarship is currently open.
