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
  primary: '#00288e'
  on-primary: '#ffffff'
  primary-container: '#1e40af'
  on-primary-container: '#a8b8ff'
  inverse-primary: '#b8c4ff'
  secondary: '#4e45d5'
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

This design system is engineered for a high-trust, academic environment. It facilitates the complex journey of scholarship discovery and application through a **Corporate / Modern** aesthetic that emphasizes clarity, logic, and reliability. 

The visual language is "Information-Forward," prioritizing the scannability of eligibility criteria and match scores. By utilizing a structured 4px grid and a "Soft Depth" philosophy, the UI feels technical and precise without being intimidating. The tone is professional and institutional, designed to instill confidence in students and educators navigating critical financial decisions.

## Colors

The palette is anchored by "Scholar Blue" (#1E40AF), a deep, authoritative primary color that signifies stability. This is complemented by "Indigo" (#4338CA) for interactive elements and accents. 

Functional colors (Success, Warning, Error, Info) play a critical role in the "Logic Mapping" of the platform—they are not merely decorative but serve as immediate visual indicators of a user's eligibility status. The background is kept exceptionally clean using a cool neutral (#F8FAFC) to ensure that the color-coded status badges and progress indicators remain the focal points of the interface.

## Typography

This design system uses **Inter** exclusively to maintain a systematic and utilitarian feel. The hierarchy follows a Minor Third scale (1.200) to create clear distinctions between different levels of information. 

Headings use `text-primary` for high contrast and impact, while auxiliary information such as timestamps, helper text, and secondary labels utilize `text-muted`. For data-heavy views like scholarship comparison tables, use `label-md` and `label-sm` to maintain high information density without sacrificing legibility.

## Layout & Spacing

The layout is based on a strict 4px grid system. For desktop, a two-column fluid grid is employed for results pages: a 3-column width sidebar for filters and a 9-column width area for scholarship cards. 

**Breakpoints:**
- **Mobile (< 768px):** Single column. Left-hand filters transition to a bottom-drawer mechanism. Horizontal scroll is enabled for comparison tables.
- **Tablet (768px - 1024px):** Condensed two-column layout or stacked cards depending on content density.
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

### Scholarship Cards
The centerpiece of the UI. Cards must feature a 12px border radius and the standard soft shadow. The "Match Score" is positioned on the left as a circular progress indicator (animated on load). The top-right corner is reserved for status ribbons/badges.

### Input Fields
Standard text inputs use an 8px radius with a 1px border (#E2E8F0). On focus, the border shifts to Primary Blue with a subtle blue outer glow. Error states use the "Error Rose" (#E11D48) for both the border and the helper text below.

### Badges & Chips
Pill-shaped containers. For eligibility status, use high-contrast combinations (e.g., Emerald background with white text for "Eligible"). For general metadata tags, use a light-gray tint with `text-muted` content.

### Skeleton Screens
During data fetching or match engine processing, use shimmering gray blocks that mirror the card layout to maintain user perceived performance.

### Navigation
Clean, white top-bar with `text-primary` links. Use a bottom-border of 2px in Primary Blue for the active state to reinforce the brand's academic identity.