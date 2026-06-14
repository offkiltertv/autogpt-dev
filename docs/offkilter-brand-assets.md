# OFFKILTER Brand Assets - Implementation Package

Date: 2026-06-14
Scope: Brand asset conversion and implementation mapping only. No production edits applied.
Reference: `docs/offkilter-brand-system.md`

## Phase 1 - Direction Decision

Primary direction selected: **Direction A - Signal / Compass**.

Decision rationale:
- Best fit for platform pillars (`Watch`, `Explore`, `Signals`) without forcing a purely mystical visual language.
- Most compatible with existing VidMov/Elementor structure and icon fields.
- Easiest migration from stock theme icons while preserving Arcana as a premium sub-identity.

Secondary accent strategy:
- Use Direction C motifs (`crystal/gem`) for Arcana-specific surfaces only.

## Phase 2 - Asset Inventory (Implementation-Ready)

Asset root:
- `docs/assets/brand-system/`

Variant sets:
- `monochrome/`
- `dark/`
- `light/`

Required assets delivered per variant:
1. `favicon.svg`
2. `app-icon.svg`
3. `social-preview.svg`
4. `creator-placeholder.svg`
5. `default-channel-banner.svg`
6. `signals-icon.svg`
7. `arcana-icon.svg`
8. `community-icon.svg`
9. `explore-icon.svg`

Quick reference:
- Dark mode signals icon: `docs/assets/brand-system/dark/signals-icon.svg`
- Dark mode arcana icon: `docs/assets/brand-system/dark/arcana-icon.svg`
- Dark mode social preview: `docs/assets/brand-system/dark/social-preview.svg`

## Phase 3 - SVG Concept Package

All concept SVGs created and stored under:
- `docs/assets/brand-system/README.md`
- `docs/assets/brand-system/monochrome/*`
- `docs/assets/brand-system/dark/*`
- `docs/assets/brand-system/light/*`

Concept intent by asset:
- `favicon`/`app-icon`: OFFKILTER compass mark.
- `signals-icon`: bolt + ring (short-form velocity).
- `arcana-icon`: gem/crystal motif (interpretive lane).
- `community-icon`: dual chat bubbles.
- `explore-icon`: compass rose.
- `creator-placeholder`: branded fallback avatar art.
- `default-channel-banner`: branded creator header fallback.
- `social-preview`: OG-ready composited identity card.

## Phase 4 - Homepage Migration Table

| Current VidMov Asset/Setting | Current Location | Replacement Asset/Setting | Target Location |
|---|---|---|---|
| `fab fa-canadian-maple-leaf` rail icon (Arcana) | Elementor page `1244`, widget `884acac`, `block_title_icons.value` | `fas fa-gem` (Arcana) or `fas fa-compass` (global base) | Same widget setting |
| `fab fa-canadian-maple-leaf` rail icon (Featured Readers) | Elementor page `1244`, widget `d7deecd` | `fas fa-user-group` | Same widget setting |
| `fab fa-canadian-maple-leaf` rail icon (Trending) | Elementor page `1244`, widget `954fa60` | `fas fa-chart-line` or `fas fa-fire` | Same widget setting |
| `fab fa-canadian-maple-leaf` rail icon (Latest Uploads) | Elementor page `1244`, widget `fab6803` | `fas fa-play-circle` | Same widget setting |
| `fab fa-canadian-maple-leaf` rail icon (Recent Discussion) | Elementor page `1244`, widget `fe0ddc4` | `fas fa-comments` | Same widget setting |
| Theme image fallback `placeholder.png` | `wp-content/themes/vidmov/css/images/placeholder.png` (used by `inc/template-tags.php`) | `creator-placeholder.svg` + branded content fallback raster exports | Child-theme override or filtered image path |
| Generic social preview | Existing OG image behavior | `social-preview.svg` (export PNG for OG) | AIOSEO/Site social meta image setting |
| Default channel header look | Creator/channel surfaces with sparse profile data | `default-channel-banner.svg` | Channel/profile default banner assignment |
| Default avatar fallback | Gravatar/default avatar path | `creator-placeholder.svg` | Avatar fallback pipeline (`beeteam368` user avatar fallback chain) |

## Phase 5 - Signals Visual Identity

Signals identity spec:
- Label: `⚡ Signals`
- Primary icon: `signals-icon.svg` (bolt + compass ring)
- Accent color: `--oktv-signals: #00C2FF`
- Classification tie-in: short-form (`0-90s`) discovery layer

Usage guidance:

### Homepage
- Surface as a dedicated rail directly after Arcana.
- Rail icon: `fas fa-bolt` (or custom icon asset if icon injection path is available).
- Keep 8-10 cards to reduce duplicate adjacency.

### Navigation
- Add under `Explore` as a first-class destination (`Explore -> Signals`).
- Optional top-level nav exposure once click-through validates demand.

### Creator Pages
- Add `Latest Signals` block per creator where data exists.
- Use same Signals accent and icon for consistency.
- Keep `Videos` + `Signals` visually distinct via icon/chip treatment, not separate architecture.

## Phase 6 - Implementation Readiness (Exact Touchpoints)

No changes applied yet. The following are the exact touchpoints for implementation sprint.

### A) WordPress Admin (no code deployment path)
1. **Elementor page 1244**
- Update `block_title_icons.value` for homepage rails:
  - `884acac`, `d7deecd`, `954fa60`, `fab6803`, `fe0ddc4`
- Update rail titles/subtitles to match pillar semantics.

2. **Widgets**
- `widget_beeteam368_post_extensions`:
  - `fontawesome_icon` fields for key sidebar rails.
- `widget_beeteam368_channel_extensions`:
  - `fontawesome_icon` fields for creator-focused widgets.
- `widget_nav_menu` icon fields where applicable.

3. **Theme options (`beeteam368_theme_options`)**
- Logo consistency (main/mobile/side).
- Footer copyright year update.
- Color token mapping (where supported by theme controls).

4. **SEO/social settings**
- Set global social preview image from branded export of `social-preview.svg`.

### B) Theme/File-system (controlled release)
1. **Fallback image pipeline**
- Existing fallback reference:
  - `wp-content/themes/vidmov/inc/template-tags.php`
  - `wp-content/themes/vidmov/css/images/placeholder.png`
- Implement through child theme override/filter (do not modify parent theme directly).

2. **Branded static assets placement (proposed)**
- `wp-content/uploads/oktv-brand/` (deployment target)
  - favicon/app icon exports
  - social preview PNG export
  - creator placeholder export
  - default channel banner export

3. **ARMember visual defaults**
- Review `wp-content/uploads/armember/profile_default_cover.png`
- Replace with branded channel/banner default where approved.

### C) CSS/Style token layer
- Apply via Customizer Additional CSS or child theme stylesheet:
  - `--oktv-brand`
  - `--oktv-signals`
  - `--oktv-arcana`
  - `--oktv-community`
  - `--oktv-creators`

### D) QA checkpoints
- Desktop: homepage rails, creator cards, channel pages.
- Mobile: rail icon legibility + hierarchy.
- Social: OG preview validation.
- Fallback states: missing thumbnail/avatar/banner behavior.

## Phase 7 - GitHub Publication

Files added in this sprint:
- `docs/offkilter-brand-assets.md`
- `docs/assets/brand-system/README.md`
- `docs/assets/brand-system/monochrome/*.svg`
- `docs/assets/brand-system/dark/*.svg`
- `docs/assets/brand-system/light/*.svg`

## Launch-Ready Outcome

Brand assets are now implementation-ready.

Once SSH stability is confirmed, rollout can begin immediately with:
1. icon swaps in Elementor/widgets,
2. fallback asset mapping,
3. color token pass,
4. social preview update,
5. QA + rollback validation.
