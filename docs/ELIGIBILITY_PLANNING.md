# Administrator rule testing and explanation refinements

Current scope, 3 November 2026: the administrator can test draft rules against a synthetic student. Existing student comparison explanations receive only short numeric refinements. The future-CGPA simulator was removed under IMP-026.

## Small refinement to existing student explanations

Scholarship Details already displayed the required value, student value and Met/Not Met result. The only addition is a calculated shortfall sentence beside a failed comparison. A CGPA of 3.20 against 3.50 shows a 0.30 gap. Grade-count conditions show how many additional qualifying subjects the individual condition needs. Income comparisons describe the recorded amount above a limit; the system does not recommend changing nationality, income or other personal facts.

Gaps belong to the evaluator-selected qualification route and applicable general checks. They are not a claim about the globally smallest change across all alternative routes. Grade thresholds overlap: the same stronger subject may satisfy several thresholds, so subject shortfalls must not be added together. Passing a single condition does not resolve remaining failures or provider checks.

## Administrator draft rule preview

On Add/Edit Scholarship or Add/Edit Rules, expand **Test these rules with a sample student**. It evaluates the requirements currently entered in the form, including unsaved changes.

- Choose the sample qualification/status and its applicable fields. CGPA modes collect sample overall CGPA and current year when studying; grade modes collect counts; IGCSE selects its scale; STAM uses pangkat.
- Completed STPM identifies the already-counted PA grade and computes PA plus the best three other subjects using the same calculation as normal result entry. A submitted manual CGPA cannot override it.
- Enter the sample nationality, income, funding level, institution and field. Current income-category configuration and all general restrictions apply.
- A-Level AAB/ABB/BBB presets fill all unused grades with zero. Against ABB configured as 1 subject at A or better AND 3 at B or better, AAB/ABB pass and BBB fails.
- Run the test. The result shows passing/failing comparisons, the selected route and provider conditions. Invalid requirements/results appear as inline validation errors.
- Editing the rules or sample clears the last result. A response arriving after an edit is discarded; run again for the changed input.

The sample controls belong to a separate form so they do not block or contaminate normal scholarship saves. The preview button uses the existing form's CSRF token, removes its PUT method override and submits to the admin-only POST endpoint. The endpoint returns JSON errors without changing existing HTML error behaviour elsewhere. It validates the draft with ScholarshipRuleEditor and the sample with AcademicResultInput, constructs unsaved models and never calls their save methods.

## Shared implementation

| Component | Responsibility |
| --- | --- |
| ScholarshipEligibilityEvaluator | Existing complete single-award comparisons; reused by official recommendations and admin preview |
| ScholarshipRecommendationService | Official readiness, current catalogue selection, caching and ordering; logs remain an explicit separate operation |
| AcademicResultInput | Existing qualification/result validation and normalisation, shared by official saves and synthetic preview data |
| EligibilityGap | Display-only differences and explanations from failed checks |
| RulePreviewController | Admin-only draft/sample validation and in-memory evaluation |
| rule-preview.js | CSRF-protected request, separate sample data, inline errors and stale-response protection |

No migration or eligibility algorithm-version change is needed: rule semantics and official result formats remain unchanged. The preview route never calls storeRecommendationLogs. Official completed-STPM slip confirmation remains required; synthetic preview validation's internal format flags do not create an official result record.

## Verification and FYP evaluation

Automated checks cover shortfall formatting, retained official comparisons, removal of simulator routes/links, administrator role protection, ABB cumulative logic, OR routes, invalid scales/counts, PA calculation and preview no-persistence. JavaScript tests cover method-spoof removal, last-moment input rendering, stale responses and validation errors. Browser tests use isolated synthetic data and actual authenticated student/admin pages.

For the FYP, update functional requirements/use cases, interface screenshots, component/sequence diagrams, implementation and the testing chapter. The ERD is unchanged. The new feature is administrator draft-rule testing. Numeric student shortfall text is a minor improvement to existing explanations and should not be claimed as a separate matching engine or counterfactual feature. Automated functional tests do not substitute for an independently labelled accuracy dataset or a real student usability study; those remain evaluation work to conduct and report honestly.
