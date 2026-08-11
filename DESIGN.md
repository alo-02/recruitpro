---
name: Nexus Enterprise Systems
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#45474c'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#75777d'
  outline-variant: '#c5c6cd'
  surface-tint: '#545f73'
  primary: '#091426'
  on-primary: '#ffffff'
  primary-container: '#1e293b'
  on-primary-container: '#8590a6'
  inverse-primary: '#bcc7de'
  secondary: '#0051d5'
  on-secondary: '#ffffff'
  secondary-container: '#316bf3'
  on-secondary-container: '#fefcff'
  tertiary: '#041528'
  on-tertiary: '#ffffff'
  tertiary-container: '#1a2a3e'
  on-tertiary-container: '#8191a9'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d8e3fb'
  primary-fixed-dim: '#bcc7de'
  on-primary-fixed: '#111c2d'
  on-primary-fixed-variant: '#3c475a'
  secondary-fixed: '#dbe1ff'
  secondary-fixed-dim: '#b4c5ff'
  on-secondary-fixed: '#00174b'
  on-secondary-fixed-variant: '#003ea8'
  tertiary-fixed: '#d3e4fe'
  tertiary-fixed-dim: '#b7c8e1'
  on-tertiary-fixed: '#0b1c30'
  on-tertiary-fixed-variant: '#38485d'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-max: 1280px
  gutter: 24px
---

## Brand & Style
The design system is engineered for high-stakes professional environments where clarity, efficiency, and trust are paramount. It targets a dual audience: ambitious candidates seeking career growth and HR professionals managing high-volume recruitment pipelines.

The visual style is **Corporate Modern**, leaning into a structured, systematic aesthetic that prioritizes information density without sacrificing whitespace. It utilizes a modular "tile" philosophy, where every interface element feels like a secure, intentional part of a larger professional ecosystem. The emotional response should be one of "controlled momentum"—the feeling that the platform is powerful enough to handle enterprise-scale data while remaining intuitive for the individual user.

## Colors
The palette is rooted in stability and action. 
- **Deep Recruit Blue (#1E293B)**: Used for high-level navigation, headers, and primary text to establish immediate authority.
- **Action Blue (#2563EB)**: Reserved for primary call-to-actions, progress indicators, and interactive states. It provides the "pulse" of the application.
- **Slate Gray (#64748B)**: Utilized for secondary text, icons, and borders to create a soft hierarchy that doesn't compete with key data.
- **Clean White (#FFFFFF)**: The primary surface color to maintain a "paper-like" professional feel.
- **Background Slate (#F8FAFC)**: A very light neutral used for page backgrounds to allow white cards to "pop" with subtle elevation.

## Typography
This design system utilizes **Inter** for its exceptional legibility and systematic "neutrality" that fits enterprise software. 

The typographic hierarchy is strictly enforced to ensure scannability in data-heavy views. We use tight letter-spacing on larger headings to maintain a modern, "compacted" feel. Labels use uppercase styling with increased tracking to differentiate them from body text in complex forms and metadata rows. Use `body-md` as the default for most UI interactions to maximize information density.

## Layout & Spacing
The system follows a strict **8px grid** (with a 4px "half-step" for tight components like labels and icons). 

- **Desktop (1280px+):** 12-column fluid grid, 24px gutters, 40px side margins.
- **Tablet (768px - 1279px):** 8-column fluid grid, 16px gutters, 24px side margins.
- **Mobile (<768px):** 4-column fluid grid, 16px gutters, 16px side margins.

Content is typically organized within a centered container. Dashboard layouts utilize a "Fixed-Fluid" model: a fixed 280px left navigation rail and a fluid main content area.

## Elevation & Depth
This design system uses **Tonal Layers** combined with **Ambient Shadows** to define hierarchy.

1.  **Level 0 (Base):** Background color (`#F8FAFC`). No shadow.
2.  **Level 1 (Cards/Cards):** White surface. 1px solid border (`#E2E8F0`). Very soft shadow: `0 1px 3px rgba(0,0,0,0.05)`.
3.  **Level 2 (Hover/Dropdowns):** White surface. Medium shadow: `0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)`.
4.  **Level 3 (Modals/Toasts):** White surface. Large shadow: `0 10px 15px -3px rgba(0,0,0,0.1)`.

Depth is primarily communicated through subtle border-bottoms in headers and sidebars rather than aggressive shadows.

## Shapes
We use the **Soft** setting (`0.25rem` or 4px) for most interactive elements. This creates a professional, "tailored" appearance that is more approachable than sharp corners but more serious than highly rounded "bubbly" designs.

- **Buttons & Inputs:** 4px radius.
- **Cards & Modals:** 8px radius (`rounded-lg`).
- **Profile Avatars:** Always circular to contrast with the rigid grid.
- **Kanban Column Headers:** 4px radius at the top only.

## Components

### Buttons & Inputs
Buttons use a 4px radius. The primary action is `Action Blue` with white text. Secondary buttons use a white background with a `Slate Gray` border. Inputs must have a 1px border (`#CBD5E1`) and transition to a 2px `Action Blue` border on focus, with a subtle glow.

### Cards & Kanban
Kanban cards should be minimal, prioritizing the job title and a "Time-since-posted" label. Use vertical "grip" dots on the left of cards to indicate drag-and-drop capability.

### Profile Completeness Gauges
Use a thin, horizontal progress bar (4px height) rather than circular gauges to save vertical space. The bar should transition from `Slate Gray` to `Action Blue` as it fills.

### Navigation (RBAC-aware)
The sidebar should clearly separate "User" actions from "Admin/Recruiter" actions using a thin horizontal divider. Active states are indicated by an `Action Blue` left-accent bar (4px wide) and a light blue tint to the background of the menu item.

### Data Tables
High-density tables use alternating row colors (Zebra striping) with a very faint tint. Headers are `Deep Recruit Blue` text using the `label-md` typographic style.

### Notification Toasts
Toasts appear in the bottom-right. They feature a leading icon (Check for success, Info for updates) and use high-contrast text to ensure they are readable over any background content.