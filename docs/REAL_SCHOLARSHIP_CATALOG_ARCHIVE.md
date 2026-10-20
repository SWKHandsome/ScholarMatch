# Archived real-world catalogue (not seeded)

This document describes the previous dataset. The default is now the fictional demo catalogue; see DEMO_SCHOLARSHIPS.md.

# Local scholarship and tuition award catalogue

Snapshot: **20 October 2026**. The [checked-in catalogue](../database/data/malaysia-scholarships.json) contains **50 real, sourced scholarships and tuition awards for study in Malaysia**, limited to pre-university, diploma and bachelor’s/honours study. Overseas-only and postgraduate funding are excluded. The list includes academic/talent scholarships, tuition bursaries, progression awards and explicitly named loyalty discounts; a discount is not presented as a competitive scholarship.

## Application windows

All 34 recorded calendar dates are after the snapshot date: **24 published award deadlines, 9 admission-linked dates and 1 calculated relative date**. The other **16** records comprise **1 explicitly rolling scheme, 4 automatic progression/nomination awards and 11 intake-based awards**. These have no single published calendar closing date; their real rules are recorded instead of invented dates. INTI publishes an intake-relative cutoff and accepts 2027 intake enquiries; KLUST’s 2026 scheme explicitly includes November/December intakes. Confirm the exact course/campus window and remaining availability with the provider.

Examples: PMG explicitly states “No deadlines”; Nottingham Dean’s Excellence is awarded at academic progression with no application; Monash’s continuing High Achiever award uses semester nomination. For Sunway ACE, 8 March 2027 is **calculated** from the published rule (two weeks after commencement) and the 22 February 2027 general degree start. It is not a separately announced scholarship closing date. Monash’s 19 February 2027 and Southampton’s 18 October 2027 dates are **admission deadlines** for the stated Malaysian intake, not separate scholarship announcements.

Provider/university pages and PDFs support the programme facts and window rules. Additional official evidence appears below where dates and criteria are on separate pages. Sources were checked on the snapshot date; there is no runtime scraping or automatic annual date rollover. Application availability and provider terms can change. Known dates will eventually expire and recommendations will exclude them; a static seed cannot keep all 50 unexpired forever.

## Eligibility limits

The app checks only supported profile/academic fields. Exact institution/course, prerequisite qualifications, A versus A- or A+ grades, weighted points, percentage marks, UEC/IGCSE/A-level results, age, gender, Bumiputera status, family relationships, sporting records, interviews and employment obligations still require provider verification. These conditions remain in Special Requirements. A blank structured threshold does not mean there is no provider requirement. Percentages and subject grades are never invented as CGPA equivalents. Some award bands depend on the programme or require a maximum result not represented by the current matcher.

“Eligible !” is a preliminary match that requires those manual checks. Not every student qualifies for every award. On this snapshot, all 50 records are active/configured and are included in the unfiltered recommendation evaluation; Eligible plus Not Eligible should total 50. Search, filters and pagination can reduce what is visible on screen.

## Same initial data on every device

Use the same repository revision and JSON file on every device. For a fresh local installation:

```bash
php artisan migrate --seed
```

The existing composer setup script also migrates and seeds. Refreshing a browser does not run database seeders.

To deliberately replace an existing SQLite scholarship database, preserving users, profiles and academic results:

```bash
php artisan migrate
php artisan scholarships:replace-catalog
```

The command validates the replacement before deleting anything, creates a consistent full SQLite backup under storage/app/backups, then replaces all scholarships in one transaction. Related scholarship rules, saved links and recommendation history are removed through foreign keys. It refuses replacement if any recorded date has already been reached; reverify/update the source snapshot first. The --force option skips only the interactive replacement prompt. Other database engines require a separate backup/import workflow.

Ordinary php artisan db:seed --class=ScholarshipCatalogSeeder remains a non-destructive upsert: it restores the checked-in catalogue metadata/rules without duplicates and preserves custom records/bookmarks/history. It is not the replacement command and can therefore leave more than 50 total rows in an existing database. Admin edits do not sync between devices. Only use replacement when deletion of existing scholarship data is intended.

## Source register

| # | Award / official source | Recorded date or rule | Basis |
| --- | --- | --- | --- |
| 1 | [Taylor's University Merit Scholarship](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-01-24 | Published award deadline |
| 2 | [Taylor's University Talent Scholarship](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-01-24 | Published award deadline |
| 3 | [Taylor's University Sports Scholarship](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-06-06 | Published award deadline |
| 4 | [Taylor's University Community Scholarship](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-07-04 | Published award deadline |
| 5 | [Taylor's University PPE Exclusive Scholarship](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-04-11 | Published award deadline |
| 6 | [Taylor's University Excellence Award](https://university.taylors.edu.my/en/study/scholarships-and-financial-aid/undergraduate-scholarships.html) | 2027-02-08 | Published award deadline |
| 7 | [Taylor's College Merit Scholarship](https://college.taylors.edu.my/en/study/scholarships-and-financial-aid/scholarships.html) | 2027-01-24 | Published award deadline |
| 8 | [Taylor's College Talent Scholarship](https://college.taylors.edu.my/en/study/scholarships-and-financial-aid/scholarships.html) | 2027-01-24 | Published award deadline |
| 9 | [Taylor's College Sports Scholarship](https://college.taylors.edu.my/en/study/scholarships-and-financial-aid/scholarships.html) | 2027-06-06 | Published award deadline |
| 10 | [Taylor's College Community Scholarship](https://college.taylors.edu.my/en/study/scholarships-and-financial-aid/scholarships.html) | 2027-07-04 | Published award deadline |
| 11 | [MMU President's Scholarship](https://www.mmu.edu.my/wp-content/uploads/2026/02/Feb-2026-Scholarship_Financial-Aids-Pre-U_-Undergraduate-Malaysian-Students.pdf) | 2026-11-08 | Published award deadline |
| 12 | [MMU Merit Scholarship](https://www.mmu.edu.my/wp-content/uploads/2026/02/Feb-2026-Scholarship_Financial-Aids-Pre-U_-Undergraduate-Malaysian-Students.pdf) | 2026-11-08 | Published award deadline |
| 13 | [MMU Talent Scholarship](https://www.mmu.edu.my/wp-content/uploads/2026/02/Feb-2026-Scholarship_Financial-Aids-Pre-U_-Undergraduate-Malaysian-Students.pdf) | 2026-11-08 | Published award deadline |
| 14 | [Monash Equity Scholarship](https://www.monash.edu.my/student-services/financial-assistance/equity-scholarships) | 2027-01-11 | Published award deadline |
| 15 | [MMU Siblings Discount](https://www.mmu.edu.my/mmu-scholarship/) | 2027-02-09 | Published award deadline |
| 16 | [MMU Alumni Discount](https://www.mmu.edu.my/mmu-scholarship/) | 2027-02-09 | Published award deadline |
| 17 | [Monash Pharmacy Equity Scholarship](https://www.monash.edu.my/student-services/financial-assistance/equity-scholarships) | 2027-01-11 | Published award deadline |
| 18 | [UCSI Principal's Award](https://apps.ucsiuniversity.edu.my/principal-award/) | 2026-11-30 | Published award deadline |
| 19 | [Sunway University CIS-ECA Scholarship 2027](https://scholarship.sunway.edu.my/scholarship/cis-eca) | 2027-02-28 | Published award deadline |
| 20 | [APU UEC Senior 3 Results Scholarship](https://www.apu.edu.my/index.php/uec-scholarships) | 2026-12-10 | Published award deadline |
| 21 | [EcoWorld Scholarship Programme 2026](https://aasd.um.edu.my/financial-aid-for-undergraduate) | 2026-10-31 | Published award deadline |
| 22 | [MMC Group Scholarship Programme 2026](https://aasd.um.edu.my/financial-aid-for-undergraduate) | 2026-10-30 | Published award deadline |
| 23 | [PMG Scholarship](https://pmg2u.com/pmg-scholarship/) | Rolling applications | Rolling applications |
| 24 | [Sunway Special Scholarship 2027 (June IGCSE Route)](https://scholarship.sunway.edu.my/scholarship/sunway-special-scholarship) | 2027-05-29 | Published award deadline |
| 25 | [INTI Merit Scholarship](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 26 | [INTI Sports Excellence Scholarship](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 27 | [INTI Tertiary Scholarship](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 28 | [INTI Chairman Award](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 29 | [INTI Family Loyalty Award](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 30 | [INTI Secondary School Teacher Recognition Award](https://newinti.edu.my/why-inti/student-support-services/scholarships-financial-assistance/) | Intake-based deadline | Intake-based deadline |
| 31 | [Monash High Achiever Award (New Students)](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/merit-scholarships) | 2027-02-19 | Admission deadline |
| 32 | [Monash High Achiever Award (Continuing Students)](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/merit-scholarships) | Automatic award | Automatic award |
| 33 | [Monash Female in Engineering Scholarship](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/merit-scholarships) | 2027-02-19 | Admission deadline |
| 34 | [Monash Graduate Research Pathway Scholarship (Honours Route)](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/merit-scholarships) | 2026-11-27 | Admission deadline |
| 35 | [Monash Bachelor in Actuarial Analytics Bursary](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/bursaries) | 2027-02-19 | Admission deadline |
| 36 | [Monash Bachelor of Biomedical Engineering (Honours) Bursary](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/bursaries) | 2027-02-19 | Admission deadline |
| 37 | [Monash Bachelor of Computer Science in Artificial Intelligence Bursary](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/bursaries) | 2027-02-19 | Admission deadline |
| 38 | [Monash Sports Scholarship](https://www.monash.edu.my/student-services/financial-assistance/undergraduate-scholarships-and-bursaries/sport-scholarship) | 2026-11-30 | Published award deadline |
| 39 | [Southampton Malaysia Top Achiever Scholarship](https://www.southamptonmalaysia.edu.my/scholarships-financial-aid/undergraduate-scholarships) | 2027-10-18 | Admission deadline |
| 40 | [Southampton Malaysia Exceptional Achiever Scholarship](https://www.southamptonmalaysia.edu.my/scholarships-financial-aid/undergraduate-scholarships) | 2027-10-18 | Admission deadline |
| 41 | [Southampton Malaysia High Achiever Scholarship](https://www.southamptonmalaysia.edu.my/scholarships-financial-aid/undergraduate-scholarships) | 2027-10-18 | Admission deadline |
| 42 | [Southampton Malaysia Academic Excellence Progression Scholarship](https://www.southamptonmalaysia.edu.my/scholarships-financial-aid/undergraduate-scholarships) | Automatic award | Automatic award |
| 43 | [Heriot-Watt Malaysia Foundation Excellence Award](https://www.hw.ac.uk/malaysia/study/fees-and-funding/scholarships/heriot-watt-university-scholarships) | Automatic award | Automatic award |
| 44 | [Sunway ACE Scholarship](https://scholarship.sunway.edu.my/scholarship/sunway-ace-scholarship) | 2027-03-08 | Calculated relative date |
| 45 | [Dean's Excellence Scholarship](https://www.nottingham.edu.my/Study/Fees-and-Scholarships/Scholarships/Foundation-undergraduate-scholarships.aspx) | Automatic award | Automatic award |
| 46 | [KLUST Star Teenagers Student Excellence Tuition Waiver / Scholarship](https://klust.edu.my/admissions/scholarships-and-loans/2026-your-future-starts-here-scholarships-promotions/) | Intake-based deadline | Intake-based deadline |
| 47 | [KLUST Star Teenagers Free Foundation Tuition Waiver / Scholarship](https://klust.edu.my/admissions/scholarships-and-loans/2026-your-future-starts-here-scholarships-promotions/) | Intake-based deadline | Intake-based deadline |
| 48 | [KLUST Principal Recommended Star Teenagers Tuition Waiver / Scholarship](https://klust.edu.my/admissions/scholarships-and-loans/2026-your-future-starts-here-scholarships-promotions/) | Intake-based deadline | Intake-based deadline |
| 49 | [KLUST Progressing Student Tuition Waiver (Foundation / Diploma to Degree)](https://klust.edu.my/admissions/scholarships-and-loans/2026-your-future-starts-here-scholarships-promotions/) | Intake-based deadline | Intake-based deadline |
| 50 | [KLUST Progressing Student Discount (Foundation / Diploma to Degree)](https://klust.edu.my/admissions/scholarships-and-loans/2026-your-future-starts-here-scholarships-promotions/) | Intake-based deadline | Intake-based deadline |

## Additional window evidence

- **MMU President's Scholarship**: [Supporting official source 1](https://www.mmu.edu.my/financial-assistance/). The official listing gives 8 November 2026 as closing date.
- **MMU Merit Scholarship**: [Supporting official source 1](https://www.mmu.edu.my/financial-assistance/). The official listing gives 8 November 2026 as closing date.
- **MMU Talent Scholarship**: [Supporting official source 1](https://www.mmu.edu.my/financial-assistance/). The official listing gives 8 November 2026 as closing date.
- **MMU Siblings Discount**: [Supporting official source 1](https://www.mmu.edu.my/financial-assistance/). MMU's official financial assistance register marks this award open with a 9 February 2027 deadline. This is a tuition discount, rather than a competitive merit scholarship; the provider sets the applicable amount.
- **MMU Alumni Discount**: [Supporting official source 1](https://www.mmu.edu.my/financial-assistance/). MMU's official financial assistance register marks this award open with a 9 February 2027 deadline. This is a tuition discount, rather than a competitive merit scholarship; the provider sets the applicable amount.
- **EcoWorld Scholarship Programme 2026**: [Supporting official source 1](https://ecoworldfoundation.com/). Universiti Malaya's official undergraduate funding notice, updated 14 October 2026, lists 31 October 2026 as the deadline.
- **MMC Group Scholarship Programme 2026**: [Supporting official source 1](https://mmcscholarship.ai.studio/). UM's official notice updated 14 October 2026 confirms the 30 October 2026 deadline and links to the application portal.
- **PMG Scholarship**: [Supporting official source 1](https://aasd.um.edu.my/financial-aid-for-undergraduate). Rolling applications: the provider's FAQ explicitly states there are no deadlines.
- **INTI Merit Scholarship**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Two weeks before class commencement. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **INTI Sports Excellence Scholarship**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Two weeks before class commencement. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **INTI Tertiary Scholarship**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Two weeks before class commencement. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **INTI Chairman Award**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Intake date. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **INTI Family Loyalty Award**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Intake date. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **INTI Secondary School Teacher Recognition Award**: [Supporting official source 1](https://newinti.edu.my/request-information/), [Supporting official source 2](https://newinti.edu.my/programme/bachelor-of-technology-honours-data-science/). Intake-based deadline: Intake date. The live INTI enquiry/programme pages offer 2027 intakes; obtain the exact campus and course date before applying. No single calendar deadline is published.
- **Monash High Achiever Award (New Students)**: [Supporting official source 1](https://www.monash.edu.my/study/apply/intake-periods). Automatic scholarship assessment with admission. The scholarship uses the course application deadline; 19 February 2027 is the published Malaysian regular deadline for the first 2027 intake.
- **Monash Female in Engineering Scholarship**: [Supporting official source 1](https://www.monash.edu.my/study/apply/intake-periods). Automatic assessment with course admission; the regular Malaysian first-intake deadline is 19 February 2027. Places are limited by intake.
- **Monash Graduate Research Pathway Scholarship (Honours Route)**: [Supporting official source 1](https://www.monash.edu.my/sass/current/undergraduate/honours-application-information). This record covers the Bachelor of Arts (Honours) first 2027 intake: the School of Arts and Social Sciences publishes 27 November 2026, noon MYT. Other schools have different course deadlines. Postgraduate diploma funding is excluded.
- **Monash Bachelor in Actuarial Analytics Bursary**: [Supporting official source 1](https://www.monash.edu.my/study/apply/intake-periods). Automatic with admission; the provider explicitly includes intakes through Semester 2, 2027. Recorded deadline is the Malaysian regular first-intake course deadline, 19 February 2027.
- **Monash Bachelor of Biomedical Engineering (Honours) Bursary**: [Supporting official source 1](https://www.monash.edu.my/study/apply/intake-periods). Automatic with admission; the provider explicitly includes intakes through Semester 2, 2027. Recorded deadline is the Malaysian regular first-intake course deadline, 19 February 2027.
- **Monash Bachelor of Computer Science in Artificial Intelligence Bursary**: [Supporting official source 1](https://www.monash.edu.my/study/apply/intake-periods). Automatic with admission; the provider explicitly includes intakes through October 2027. Recorded deadline is the Malaysian regular first-intake course deadline, 19 February 2027.
- **Southampton Malaysia Top Achiever Scholarship**: [Supporting official source 1](https://www.southamptonmalaysia.edu.my/apply-now/application). The university confirms 2027 applications are open. This record uses the published Malaysian undergraduate September 2027 admission deadline. There is no separate scholarship closing date on the award page.
- **Southampton Malaysia Exceptional Achiever Scholarship**: [Supporting official source 1](https://www.southamptonmalaysia.edu.my/apply-now/application). The university confirms 2027 applications are open. This record uses the published Malaysian undergraduate September 2027 admission deadline. There is no separate scholarship closing date on the award page.
- **Southampton Malaysia High Achiever Scholarship**: [Supporting official source 1](https://www.southamptonmalaysia.edu.my/apply-now/application). The university confirms 2027 applications are open. This record uses the published Malaysian undergraduate September 2027 admission deadline. There is no separate scholarship closing date on the award page.
- **Southampton Malaysia Academic Excellence Progression Scholarship**: [Supporting official source 1](https://www.southamptonmalaysia.edu.my/apply-now/application). Progression award, assessed on UoSM Foundation results. No separate closing date is published. The university is accepting applications for its 2027 local programmes.
- **Heriot-Watt Malaysia Foundation Excellence Award**: [Supporting official source 1](https://www.hw.ac.uk/malaysia/study/apply). Automatic progression award. The published scheme explicitly covers Foundation cohorts entering January and May 2027; the university accepts admissions all year. No separate scholarship application deadline is published.
- **Sunway ACE Scholarship**: [Supporting official source 1](https://sunwayuniversity.edu.my/campus-life/academic-calendar). Provider rule: within two weeks after programme commencement OR actual results release, as applicable. For the published 22 February 2027 general degree intake, the commencement-based date is 8 March 2027 (calculated, not separately announced). The results-release route and programme-specific dates may differ.
