---
name: ScholarMatch System
colors:
  surface: '#FFFFFF'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#444653'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#757684'
  outline-variant: '#c4c5d5'
  surface-tint: '#3755c3'
  primary: '#1E40AF'
  on-primary: '#ffffff'
  primary-container: '#DBEAFE'
  on-primary-container: '#a8b8ff'
  inverse-primary: '#b8c4ff'
  secondary: '#4338CA'
  on-secondary: '#ffffff'
  secondary-container: '#6860ef'
  on-secondary-container: '#fffbff'
  tertiary: '#611e00'
  on-tertiary: '#ffffff'
  tertiary-container: '#872d00'
  on-tertiary-container: '#ffa583'
  error: '#E11D48'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dde1ff'
  primary-fixed-dim: '#b8c4ff'
  on-primary-fixed: '#001453'
  on-primary-fixed-variant: '#173bab'
  secondary-fixed: '#e3dfff'
  secondary-fixed-dim: '#c3c0ff'
  on-secondary-fixed: '#100069'
  on-secondary-fixed-variant: '#372abf'
  tertiary-fixed: '#ffdbce'
  tertiary-fixed-dim: '#ffb59a'
  on-tertiary-fixed: '#380d00'
  on-tertiary-fixed-variant: '#802a00'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
  success: '#10B981'
  warning: '#F59E0B'
  info: '#0EA5E9'
  text-primary: '#0F172A'
  text-muted: '#64748B'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 16px
  margin-mobile: 16px
  margin-desktop: 32px
---

## Brand & Style

Matching Settings uses one stacked page: Income Categories, followed by Fields of Study with clear section spacing. Both sections use the existing table/card style and blue primary add buttons. Fields have Active/Inactive text badges and an Edit action. A single labelled modal handles adding, renaming and availability, retaining values and displaying errors after validation failure. Explain inactive-field preservation below the table. Keep income actions and their layout unchanged.

This design system is engineered for a high-trust, academic environment. It facilitates the complex journey of scholarship discovery and application through a **Corporate / Modern** aesthetic that emphasizes clarity, logic, and reliability. 

The visual language is "Information-Forward," prioritizing the scannability of eligibility criteria and match scores. By utilizing a structured 4px grid and a "Soft Depth" philosophy, the UI feels technical and precise without being intimidating. The tone is professional and institutional, designed to instill confidence in students and educators navigating critical financial decisions.

## Colors

The palette is anchored by "Scholar Blue" (#1E40AF), a deep, authoritative primary color that signifies stability. This is complemented by "Indigo" (#4338CA) for interactive elements and accents. 

Functional colors (Success, Warning, Error, Info) play a critical role in the "Logic Mapping" of the platform—they are not merely decorative but serve as immediate visual indicators of a user's eligibility status. The background is kept exceptionally clean using a cool neutral (#F8FAFC) to ensure that the color-coded status badges and progress indicators remain the focal points of the interface.

## Typography

This design system uses **Inter** exclusively to maintain a systematic and utilitarian feel. The hierarchy follows a Minor Third scale (1.200) to create clear distinctions between different levels of information. 

Headings use `text-primary` for high contrast and impact, while auxiliary information such as timestamps, helper text, and secondary labels utilize `text-muted`. For data-heavy views like scholarship comparison tables, use `label-md` and `label-sm` to maintain high information density without sacrificing legibility.

## Layout & Spacing

The layout is based on a strict 4px grid system. On desktop, Recommendations uses a 15rem filter sidebar beside a flexible results column. Cards span the results column one per row, using the same shared component as Dashboard.

**Breakpoints:**
- **Mobile (< 768px):** Single column, with filters above the recommendation list. Horizontal scroll is enabled for comparison tables.
- **Tablet (768px - 1024px):** Filters stack above the same single-column cards.
- **Desktop (> 1024px):** Standard two-column results view with a maximum container width of 1280px.

Spacing increments follow a linear scale (4, 8, 12, 16, 24, 32, 48, 64) to ensure mathematical harmony across all components.

## Elevation & Depth

Visual hierarchy is achieved through a combination of **Tonal Layers** and **Ambient Shadows**. 

The base application uses the neutral background (#F8FAFC), while primary content containers and cards use a pure white surface (#FFFFFF). To create a soft, sophisticated separation, a single, highly diffused shadow is used for all floating containers: `0 4px 6px -1px rgba(0, 0, 0, 0.1)`. 

Interactive elements like buttons do not use shadows by default, instead relying on color shifts (Primary to Secondary) to indicate state. This keeps the "elevation" reserved for structural elements, maintaining a clean and flat overall profile.

## Shapes

The shape language distinguishes between **actions** and **containers**:

- **Standard Elements (Buttons, Inputs):** Use a `8px` (rounded-md) radius for a soft but professional feel.
- **Content Containers (Cards):** Use a `12px` (rounded-lg) radius to provide a more approachable, modern framing for scholarship details.
- **Status/Meta Elements (Badges, Tags):** Use a `9999px` (full-pill) radius. This distinct shape allows badges to stand out clearly against the more geometric cards and buttons.

## Components

### Buttons
Primary buttons use the Primary Blue (#1E40AF) with white text. Hover states transition to Secondary Indigo (#4338CA). Focus states must include a 2px offset ring in the secondary color.

### Authentication Cards

Incomplete student accounts enter guided setup after registration/login. Student Profile and Academic Result show a two-step navigation indicator with the current step highlighted. First-time profile entry uses Complete Student Profile and Save and Continue; completed accounts retain the normal Edit Profile/Save Changes presentation. Save proceeds to the remaining required section, then Dashboard. The State dropdown uses Kuala Lumpur, Labuan and Putrajaya, recognising old prefixed selections without making students reselect them.

Login/Register now share one grid container: both panels contribute to its natural height, while the inactive panel is invisible and inert. This reserves the taller form's space and aligns both submit buttons at the bottom without a hard-coded height or clipped errors. Switch the visible panel in place, fade it in over 150ms and slide one blue background between the links over 250ms. Keep URL/history and Back/Forward in sync; ordinary and modified links retain navigation fallback. Disable panel/slider transitions for reduced-motion preferences.

Eye icons describe current state: crossed eye for a masked field, open eye for visible text. Action labels remain Show password/Hide password. Use focus-visible with an inset ring on the eye control, so mouse clicks add no blue outline while keyboard focus stays visible. Switching panels masks all password fields again.

Login and Register show a full-width two-part navigation switch at the top of the card: Register on the left, Login on the right. Use Primary Blue with white text for the current page and a neutral background for the other link. These are ordinary page links with aria-current="page" on the active destination. Include a clear page heading and a full-width blue submit button; the switch replaces the old Already registered? link.

Forgot Password and Reset Password place a Back to Login link above the heading. It stays available alongside validation errors and reset-link confirmation. Login groups Remember me and Forgot password? in a wrapping row for narrow screens.

The shared password input starts masked, reserves right padding for an eye button and toggles only its own field. Register and Reset Password therefore have independent Password/Confirm Password controls. Give the button type="button", Show password/Hide password labels, aria-controls, aria-pressed and visible keyboard focus. Hide the eye button until Alpine initialises; the masked input remains usable without JavaScript. Keep existing autocomplete and required attributes. The switch itself uses normal navigation and works without JavaScript.

### Scholarship Cards
Cards use the standard rounded corners and soft shadow. Show the scholarship name, provider, eligibility badge, description, award type, deadline and actions. Requirement explanations expand through native details/summary controls. Cards do not contain score rings, points or percentage matches.

Place the eligibility badge at the top right of a flexible card header, with the title/provider on the left. Long titles wrap without overlapping the badge. Award type uses a blue-tinted chip, and the deadline date is bold inside a neutral chip. Expandable card explanations use a simple list: one message per applicable check, with a bold Met/Not Met label and green/red text. Avoid individual panels, extra outcome pills or duplicated required/student values here; detailed comparisons remain on View Details.

View Details and Save use outlined buttons with a visible border and the same padding/radius. Apply Now remains the filled primary action. The shared card applies this consistently to dashboard and recommendation lists.

Recommendations shows 10 cards per page in the chosen eligibility group, with the current range/page above and numbered previous/next navigation below. The filter sidebar contains name/provider search, award type, funded study level, field and institution selectors, plus Apply Filters and Reset. Preserve selections across page/group links and reset the page when filters or group change. Keep the filters available when no results match; show a clear empty message and guidance to adjust filters or check the other group. Anchor page navigation below the sticky header.

### Input Fields
Standard text inputs use an 8px radius with a 1px border (#E2E8F0). On focus, the border shifts to Primary Blue with a subtle blue outer glow. Error states use the "Error Rose" (#E11D48) for both the border and the helper text below.

Student profile, academic-result and scholarship Add/Edit forms mark required labels with a red *, without a separate required-field note at the top. Academic labels include the marker and screen-reader text; conditional result/year fields and their markers appear together only when applicable. Description and special requirements are explicitly optional. General admin restrictions and result/year minimums are optional to configure. Each added qualification box requires level and status. A * describes form completion, not a requirement weight or priority.

Household Income shows its RM currency unit in the label. The numeric input contains only the amount, avoiding an overlapping currency overlay and preserving standard input padding.

Nationality selectors offer Malaysian and Non-Malaysian. Profile retains an unanswered placeholder; scholarship rules additionally allow Any nationality to represent an unrestricted condition. Do not offer individual countries or Other as selectable nationality values.

### Administrator Qualification Boxes

Add/Edit Scholarship and Rules include a collapsed Test these rules with a sample student section after the general restrictions. Sample academic controls follow qualification-specific formats, with full sample profile/programme inputs and A-Level AAB/ABB/BBB presets. Run rule test is a non-submit button; results use green/red text and required/sample comparisons, with amber provider notes. Errors render inline; edits clear stale results. Keep sample controls associated with their separate form so ordinary scholarship saves remain independent.

Place the visible qualification number and red Remove action in the same header row inside the box, with Remove aligned right. Keep a screen-reader legend for the fieldset and use normal spacing below the header before the level/status dropdowns. Avoid a separate removal row above the controls.

Add/Edit Scholarship and Rules use the same editor. Add Qualification creates a numbered fieldset with grouped qualification options, level/status * controls and a labelled Remove button. Stable identities keep values attached to their own box and grade condition when another item is removed. Exam-based qualifications force Completed; CGPA levels permit Currently Studying/Completed/Either, with year limits only for Currently Studying. Changing level/status clears inapplicable values, and hidden controls are disabled. Result minimums and each year boundary are explicitly optional. No boxes displays an unrestricted-qualification explanation.

Show SPM As/credits plus optional grade conditions; IGCSE/O-Level/A-Level/Senior UEC use grade-threshold/count conditions, STAM uses minimum overall pangkat, and CGPA levels keep CGPA. STPM supports both CGPA and grade-count conditions. IGCSE explicitly selects a scale when setting grade thresholds; do not display a conversion between letters and 9–1. UEC may enable a best-N average with its minimum qualifying grade and maximum average. Explain that lower averages are stronger. Restore saved dynamic grade selections on edit. Named-subject conditions belong in Special Requirements; the completed-STPM Pengajian Am input is used only to calculate overall CGPA.

Add Another Requirement creates a group with a required Requirement Type dropdown and only the applicable inputs. Offer overall CGPA, grade counts, SPM As/credits, STAM rank, STPM minimum best-subject average, and UEC maximum average/total where supported. Each grade-count group has one shared selector, compact Minimum Subjects / At Grade or Better rows, individual Remove actions and an Add Grade Row button. Show column labels on the first row and retain accessible labels on later rows. Use one combined cumulative preview such as A, B, B. Removing the last row removes its group; Remove this requirement removes the whole group. Other types remain separate with their own previews. Each added row must be complete. Type changes clear incompatible values, and requirement types cannot be duplicated in a box; grade groups contain their repeated rows. Preserve the exact prior thresholds when opening existing rules.

Funded study level and general conditions remain outside the boxes. Explain AND within a box and OR between boxes. Student/log explanation tables show the matched route (or closest failing route) and expand to all alternative comparisons. An unrelated failing alternative never overrides a passing route.

### Badges & Chips
Use green text and a pale green pill for Eligible, and rose text with a pale rose pill for Not Eligible. Eligible results with administrator-entered special requirements add an amber ! inside an expandable badge. Its accessible label explains that special requirements need verification; expanding it reveals the full plain text and a provider-verification action. Missing results never cause this warning. Not Eligible remains the decision when an automatic check fails. Previous evaluation logs use an informational badge until refreshed.

On cards, special-condition text opens in a bounded amber panel anchored below the top-right badge. Keep it inside the viewport and preserve keyboard activation. Met/Not Met/Not Applicable requirement pills pair colour with explicit text and a check/cross/dash, so colour is not the only signal.

### Academic Form and Requirement Explanations

Existing failed comparisons include small numeric shortfall explanations, such as CGPA 0.30 below the requirement. Retain all other blockers and explain that overlapping grade shortfalls cannot be added. This refines the existing Required/Your information comparison rather than adding a separate student feature.

Use two accessible radio groups styled as blue selected pills: education category, then current/latest qualification. Secondary contains SPM, Cambridge IGCSE and Cambridge O-Level; Pre-U, Diploma & Pathway contains STPM, STAM, A-Level, Senior UEC, Matriculation, Foundation and Diploma; Undergraduate contains Bachelor’s Degree. Grouping organises choices without asserting equivalence. Preserve stored qualification identifiers while showing friendly labels.

For grade-based qualifications, show an official Final Results chip, a read-only live summary and a responsive grid of grade counters. Each counter has labelled minus/plus buttons and a directly editable whole-number input, keyboard focus styling and a minimum of zero. At least one subject is required. Show total subjects, plus derived SPM As and credits; As include A+, A and A-, and credits include C and above. The application caps entries at 12 SPM subjects, 5 STPM subjects and 20 for the other supported scales; these are application limits, not exam-board maxima. Only completed STPM identifies the already-counted Pengajian Am grade; other subject names are not collected.

IGCSE explicitly chooses letters or 9–1, and switching scales clears counts. Changing category or qualification clears incompatible values and official confirmation. STAM displays an overall pangkat selector. SPM, IGCSE, O-Level, A-Level, UEC and STAM force Completed and official final results; no forecast/expected/AS-Level option is provided. STPM has subject grade counters with studying/completed status and conditional year. Completed students select the already-counted Pengajian Am grade, view a read-only calculated overall CGPA and confirm it matches the official slip. Require four/five final subjects and clear this confirmation on count or PA edits. Currently studying students retain the manual official CGPA field. The PA dropdown, calculation and confirmation are hidden/disabled in other modes. Explain that best-subject scholarship averages do not replace the overall calculation that includes PA. Show old saved CGPA without inventing a PA grade; completed edits require the new calculation/confirmation. Existing CGPA-only STPM values remain visible with a prompt to add actual counts. The original CGPA levels retain study status, conditional current year and official cumulative/final CGPA. Hide and disable irrelevant controls and enforce the same rules on the server. Legacy SPM totals remain visible with a prompt to enter the real breakdown; never invent a distribution.

Group Study Level Seeking Funding, Institution Type and Field of Study in a Programme Seeking Funding fieldset on Academic Result. All three have required * markers. Explain that this programme may differ from the latest qualification supplying results. Profile collects only personal/household details and links to this section. Preserve existing selections through migration and retain submitted values when validation fails.

Place the programme section divider on an outer wrapper, with the fieldset legend below it. The divider spans the full content width without intersecting the heading; retain consistent spacing between the heading, helper text and fields.

Use a responsive requirement table with Requirement, Required, Your Information and Result columns. Render Met, Not Met and Not Applicable explicitly; do not infer meaning from message wording or colour alone. Special text must remain escaped and keyboard-accessible. Eligibility navigation groups Eligible (including !) and Not Eligible using GET links, filtered counts and aria-current on the selected group. Render only the selected group's current page so filtering and pagination work without JavaScript.

Student scholarship details use a short eligibility summary followed by the evaluator-selected route and readable comparison rows. Combine qualification/status, place failed checks first in a pale-red section, then show passing Academic requirements and Other requirements. Each row has a plain label, outcome pill and bold Required/Your information values, side by side on desktop and stacked on mobile. Green/red values and text labels reinforce the result. Keep other accepted routes inside a collapsed disclosure with a reminder that passing any one is sufficient; each alternative has its own expandable conditions. Collapse unrestricted criteria into a separate neutral disclosure. Do not treat failures from another route as overall rejection reasons. Use an amber provider section for special conditions and uncertain application dates; rolling/automatic timing is informational. Preserve zero values and show RM on income comparisons. Administrator logs retain their detailed coloured table. These changes present existing outcomes without changing eligibility logic.

### Skeleton Screens
During data fetching or match engine processing, use shimmering gray blocks that mirror the card layout to maintain user perceived performance.

### Navigation
Clean, white top-bar with `text-primary` links. Use a bottom-border of 2px in Primary Blue for the active state to reinforce the brand's academic identity.

### Profile income feedback

Place a two-line income helper immediately below Household Income, with the category as the stronger first line and its current range on the second line. Both align with the input/label; reserve a small minimum height without clipping longer messages. Use an accessible polite status region and connect it through aria-describedby. Keep initial server-rendered text so a valid result remains available without JavaScript; Alpine updates text while typing.

Income reference cards list actual administrator-configured names and ranges, including cents and open upper limits. Retain familiar B40/M40/T20 accents and use the primary accent for other names. Stack cards on small screens. The caption is Income ranges configured by the administrator; avoid a fixed statistical-source claim for editable thresholds. Empty, invalid, unmatched and unconfigured income states have distinct messages. A page refresh loads later admin changes.
