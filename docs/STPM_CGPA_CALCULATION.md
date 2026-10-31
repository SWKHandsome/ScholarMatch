# Completed STPM overall CGPA calculation

Implemented 31 October 2026. Completed STPM results now calculate overall CGPA from final subject grade counts plus the identified Pengajian Am grade. Currently studying STPM retains manual latest official CGPA entry and current year. Other qualifications keep their existing result formats.

## Student workflow

1. Choose STPM and Completed, then enter four or five final subject results using grade counts. Include Pengajian Am once in those counts.
2. Select the Pengajian Am grade. That grade must already have a count of at least one; selecting it does not add a subject.
3. Review the read-only Calculated Overall CGPA.
4. Confirm that it matches the official STPM result slip, and confirm that the entered results are official before saving.

Editing a count through typing or a counter button, or changing the Pengajian Am grade, clears the calculation confirmation. Changing qualification or study status clears the old calculation inputs/confirmation. The form cannot save an incomplete calculation. If the calculated result differs from the official slip, correct the entered final grades/PA identification before confirming; there is no manual override for completed STPM.

Pengajian Am identification is the only subject-specific input in this version. Other subject names are not collected. Named-subject scholarship conditions remain Special Requirements for provider verification.

## Calculation

Use the existing official STPM subject-point map: A=4.00, A-=3.67, B+=3.33, B=3.00, B-=2.67, C+=2.33, C=2.00, C-=1.67, D+=1.33, D=1.00, F=0.00.

1. Validate whole non-negative counts in the STPM scale and a total of four or five subjects.
2. Validate that the selected Pengajian Am grade appears in the counts.
3. Remove one occurrence of that grade from a working copy of the counts.
4. Select the strongest three remaining subjects by STPM grade order.
5. Add the Pengajian Am points to those three subjects and divide by four.
6. Round half up to two decimal places. PHP and JavaScript both work in integer hundredths to avoid binary floating-point rounding differences.

For example:

| All entered grades | Pengajian Am | Included results | Calculated CGPA |
| --- | --- | --- | --- |
| A, A, A, A, C | C | C plus three As | 3.50 |
| A, A, A, A, C | A | Four As | 4.00 |
| A, A, A-, A- | A | All four subjects | 3.84 |
| F, F, F, F | F | All four subjects | 0.00 |

The same aggregate counts can therefore produce different overall CGPAs depending on the Pengajian Am grade. It cannot simply be the strongest four grades or a mean across all five.

This calculation uses complete final subject grades; it does not infer final grades from semester marks, calculate a CGPA for fewer than four subjects or accept incomplete-result codes such as W/X/Y/Z.

## Server validation and matching

AcademicQualifications::stpmCgpa supplies the PHP calculation; frontend metadata shares its subject-point map with the live preview. AcademicResultController requires stpm_pa_grade and an accepted stpm_cgpa_confirmed for completed STPM. It excludes any submitted cgpa field, recalculates on the server and saves the result in the existing cgpa column. A forged client value cannot change the stored CGPA or matching outcome.

For records using this calculation, AcademicResult readiness requires confirmation and consistency between the stored CGPA, counts and PA grade. Studying STPM continues requiring manually entered official CGPA, grade counts and current year, and clears PA/calculation-confirmation values on save. Switching to another qualification also clears the STPM-only fields.

Scholarship overall-CGPA checks continue reading cgpa. Provider-defined best-N STPM averages remain separate, use their existing settings and are compared without rounding into a pass. The new PA identification does not automatically introduce named-subject inclusions/exclusions into scholarship formulas. The grouped administrator grade editor is unchanged.

## Data and upgrade

Migration 2026_10_31_020000_add_stpm_cgpa_calculation adds:

| academic_results field | Meaning |
| --- | --- |
| stpm_pa_grade | Nullable STPM grade identifying the already-counted Pengajian Am result |
| stpm_cgpa_confirmed | Boolean, default false; student confirmed the calculated value matches the official slip |

Existing grades/CGPAs and record counts are preserved. No PA grade is guessed and no existing CGPA is recalculated during migration. Previously usable official STPM records with valid counts retain their saved manual CGPA until edited; then the completed-result form requires PA identification and confirmation. Older CGPA-only STPM records still require their actual grade counts under the prior completeness policy.

Rollback refuses to discard PA/confirmation data once present. Evaluation version decisions-v8-stpm-cgpa refreshes older caches/logs under the new input workflow. No scholarship catalogue changes are required.

## Verification and FYP report

Automated tests cover four/five subjects, PA inclusion versus excluding a stronger extra subject, zero/maximum and half-up boundaries, absent/malformed/uncounted PA grades, insufficient subjects, missing confirmation, server rejection of forged CGPA, studying/manual behaviour, switching formats, legacy preservation and protected rollback. Browser checks cover calculation/confirmation changes, save/reload, manual studying CGPA and mobile layout.

Update functional requirements, the student academic use case/activity diagram, interface screenshots/user guide, academic_results data dictionary/ERD, input validation and CGPA pseudocode. Explain the bounded Pengajian Am exception to aggregate-only entry and the difference between overall CGPA and a scholarship's best-subject average.

Primary references: [MPM registration guidance on four/five subjects and Pengajian Am](https://daftarstpm.mpm.edu.my/), [MPM STPM 2026 regulations](https://mpmweb.mpm.edu.my/images/dokumen/calon-peperiksaan/stpm/Peraturan_dan_skema/Peraturan_dan_Skema_STPM_2026_KEMASKINI_2052025_1.pdf).
