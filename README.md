---
version: alpha
name: adaAcara
description: A bright, editorial landing-page system with bold typography, soft cards, and friendly warm accents.
colors:
  primary: "#0f172a"
  secondary: "#4b5563"
  tertiary: "#b9812f"
  neutral: "#ffffff"
  surface: "#f8fafc"
  on-surface: "#0f172a"
  border: "#e2e8f0"
  muted: "#94a3b8"
  accent-warm: "#fde68a"
  accent-soft: "#fff7ed"
  error: "#dc2626"
typography:
  headline-display:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "66px"
    fontWeight: 950
    lineHeight: 67.473px
    letterSpacing: -2.315px
  headline-lg:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "48px"
    fontWeight: 950
    lineHeight: 58px
    letterSpacing: -1.323px
  headline-md:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "35px"
    fontWeight: 950
    lineHeight: 42px
    letterSpacing: -0.48px
  headline-sm:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "26px"
    fontWeight: 600
    lineHeight: 31px
    letterSpacing: 0px
  body-lg:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "18px"
    fontWeight: 400
    lineHeight: 34px
    letterSpacing: 0px
  body-md:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 28px
    letterSpacing: 0px
  body-sm:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 22px
    letterSpacing: 0px
  label-lg:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "18px"
    fontWeight: 800
    lineHeight: 24px
    letterSpacing: 0px
  label-md:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "14px"
    fontWeight: 800
    lineHeight: 20px
    letterSpacing: 0px
  label-sm:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "12px"
    fontWeight: 700
    lineHeight: 16px
    letterSpacing: 0.02em
  caption-md:
    fontFamily: "Plus Jakarta Sans"
    fontSize: "12px"
    fontWeight: 500
    lineHeight: 18px
    letterSpacing: 0px
rounded:
  none: 0px
  sm: 4px
  md: 8px
  lg: 16px
  xl: 28px
  full: 9999px
spacing:
  xs: 10px
  sm: 18px
  md: 28px
  lg: 54px
  xl: 72px
  gutter: 24px
  section: 80px
components:
  button-primary:
    backgroundColor: "{colors.secondary}"
    textColor: "{colors.neutral}"
    typography: "{typography.label-md}"
    rounded: "{rounded.full}"
    padding: "14px 16px"
    height: "42px"
  button-primary-hover:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.neutral}"
    rounded: "{rounded.full}"
  button-secondary:
    backgroundColor: "{colors.neutral}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-md}"
    rounded: "{rounded.full}"
    padding: "14px 16px"
    height: "42px"
  button-link:
    backgroundColor: "transparent"
    textColor: "{colors.tertiary}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.none}"
    padding: "0px"
  card:
    backgroundColor: "{colors.neutral}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.xl}"
    padding: "24px"
  input:
    backgroundColor: "{colors.neutral}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-md}"
    rounded: "{rounded.full}"
    padding: "14px 16px"
  chip:
    backgroundColor: "{colors.accent-soft}"
    textColor: "{colors.tertiary}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: "10px 14px"
---

# adaAcara

## Overview
adaAcara feels friendly, modern, and highly commercial: a startup landing page for creators who want to sell event invitations and templates without friction. The tone balances playful personality from the mascot illustrations with a serious, conversion-focused layout and bold messaging. Visual density is moderate-to-spacious, with generous breathing room around the hero, cards, and supporting stats so the page feels approachable and premium rather than crowded.

## Colors
- **Primary (#0f172a):** A deep ink used for the strongest headlines, navigation text, and high-contrast UI elements. It gives the brand a credible, professional backbone.
- **Secondary (#4b5563):** A muted slate used for primary button fills and secondary emphasis. It softens the page compared with pure black while still feeling authoritative.
- **Tertiary (#b9812f):** A warm amber-gold accent used for highlights, links, badges, and key word emphasis. It adds optimism and a creator-friendly energy.
- **Neutral (#ffffff):** The main canvas for cards, buttons, and the majority of surfaces. White keeps the interface light and easy to scan.
- **Surface (#f8fafc):** A faint cool off-white for page background support and subtle separation from pure white elements.
- **Border (#e2e8f0):** A pale blue-gray line color used for cards, pills, and input boundaries. It preserves softness instead of hard contrast.
- **Muted (#94a3b8):** A restrained slate for body-copy secondary text, metadata, and supportive labels.
- **Accent Warm (#fde68a):** A sunlit yellow used in illustration backdrops and decorative highlights. It reinforces the cheerful, festive identity.
- **Accent Soft (#fff7ed):** A creamy warm wash that can sit behind chips, icons, or callouts without overpowering content.
- **Error (#dc2626):** Reserved for validation and destructive states; it should remain sparing in this otherwise upbeat system.

## Typography
The system is built on **Plus Jakarta Sans**, which gives the interface a crisp, contemporary, and slightly rounded editorial feel. Headings are extremely heavy, using 950 weight to create strong hierarchy and immediate attention; this is especially visible in the large hero statement. Body text stays light and readable with generous line heights, while labels and buttons rely on bold weights for quick scanning and conversion-focused clarity.

`headline-display`, `headline-lg`, and `headline-md` are the dominant display styles and should be used for hero copy, section introductions, and large marketing claims. `headline-sm` works for card titles and smaller feature headings where the same family is needed but without overwhelming the layout. `body-lg`, `body-md`, and `body-sm` cover explanatory copy, supporting paragraphs, and compact descriptions; keep their line heights open and avoid tight tracking. `label-lg`, `label-md`, `label-sm`, and `caption-md` are intended for buttons, badges, navigation, and metadata. Short uppercase treatment is not a core pattern here; emphasis comes more from weight and color than from all-caps or wide letter spacing.

## Layout
The layout follows a wide, centered marketing page with a strong hero split between copy on the left and a product illustration panel on the right. Content is arranged in clean vertical stacks with large spacing jumps between major sections, using the `section` rhythm for broad separation and `xs` to `md` for tighter internal groupings. Cards and chips are padded generously, while the overall page keeps ample horizontal breathing room so it feels open and high-end.

The system prefers soft container blocks over rigid grids: large feature cards, stat cards, and pill-like callouts sit in a loose but balanced grid. Use `gutter` for horizontal item spacing and `md` to `lg` for card-to-card separation. Sections should feel spacious, with substantial top and bottom padding, rather than dense dashboard-like compaction.

## Elevation & Depth
Depth is subtle and mostly created through soft shadows, rounded cards, and tonal contrast rather than dramatic elevation. Surfaces are typically white with a pale border and a light shadow, which keeps them floating gently above the background. The design avoids heavy layering or glossy effects; instead, hierarchy comes from size, color contrast, and the strategic use of warm accent shapes behind content.

Cards should use the lighter shadow language and remain visually clean, with borders doing much of the separation work. Buttons may use a slightly stronger shadow to feel clickable, but the overall system should stay restrained and airy.

## Shapes
The shape language is friendly and highly rounded. Pill buttons, chips, and small callouts use `full` radius, while content cards use a large `xl` radius to keep the page soft and approachable. There are almost no sharp corners in the observed UI, which makes the brand feel welcoming and consumer-friendly rather than technical.

Keep interactive elements rounded enough to read as tactile. Large hero containers should remain gently curved, and decorative illustration panels can be even softer to support the playful mascot-led identity.

## Components
**Buttons:** Primary buttons use `button-primary` with a dark slate fill, white text, bold label treatment, `full` rounding, and compact but confident padding. They should feel like the main conversion action and can include a stronger hover state via `button-primary-hover`, which deepens the fill. Secondary buttons use `button-secondary` with a white background, border, and dark text for lower-emphasis actions. Link-style actions use `button-link` and should remain minimal, warm-colored, and underlined when needed. Button sizing should stay consistent at about 42px high for the core CTA pattern.

**Cards:** Use `card` for feature blocks, stats, and content containers. Cards should be white, lightly bordered, and softly shadowed with `xl` radius. Internal padding should stay comfortable so content never feels compressed. Card headers can use `headline-sm` or `label-lg`, while supporting text should use `body-sm` or `caption-md`.

**Inputs:** Inputs should follow the same soft, pill-shaped language as buttons, using `input` with white fill, light border, and `full` rounding. Focus states should be clear but not harsh; avoid aggressive outlines or heavy shadows. Labels and helper text should stay understated and readable.

**Chips and badges:** Use `chip` for small status labels, feature tags, and context pills. These should be warm, lightly tinted, and text-forward, often functioning as compact signals rather than clickable controls.

**Navigation:** Top navigation is minimal, text-led, and low-friction. Use strong but not oversized labels, and reserve the darkest styling for the active or emphasized CTA on the right.

**Illustration containers:** Any mascot or promotional graphic should live inside a soft, bordered, lightly shadowed panel with warm decorative background shapes. Treat these as brand moments, not rigid content modules.

## Do's and Don'ts
- Do keep headlines extremely bold and compact; that contrast is central to the brand voice.
- Do use warm amber accents sparingly to highlight key phrases, badges, and supportive cues.
- Do preserve the airy spacing around the hero and feature blocks so the page feels premium and easy to scan.
- Do rely on soft borders and subtle shadows for separation instead of heavy depth.
- Don't introduce harsh black-heavy themes or dense, dashboard-style layouts.
- Don't use sharp corners or square buttons for primary interactions.
- Don't overuse decorative colors; the interface should stay mostly white with ink, slate, and controlled gold accents.
- Don't make body text too small or too tight; readability and calm pacing are important here.
