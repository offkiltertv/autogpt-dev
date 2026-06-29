# VidMov Decontamination Inventory

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Sprint: OFFKILTER Identity & Fit-and-Finish v1

## Purpose

A complete audit of every inherited UI element from the VidMov theme ecosystem.
Items are classified as REMOVE, REPLACE, or KEEP with implementation notes.

---

## Classification Key

| Label | Meaning |
|---|---|
| **REMOVE** | Element should be deleted with no replacement. It adds noise, confusion, or legacy signal. |
| **REPLACE** | Element serves a real function but must be replaced with OFFKILTER-native equivalent. |
| **KEEP** | Element is functional, not visually branded to VidMov, or too costly to change for zero gain. |

---

## Section 1 — Icons

| Element | Location | Classification | Notes |
|---|---|---|---|
| `fab fa-canadian-maple-leaf` rail icons | Homepage page `1244`, multiple Elementor widgets | **REMOVED** ✅ | Already replaced in sprint `offkilter-identity-implementation-sprint`. Replaced with `fas fa-gem`, `fas fa-bolt`, `fas fa-user-group`, `fas fa-play-circle`, `fas fa-comments`. |
| Generic sidebar widget icons (`fa-thumbs-up`, `fa-headphones-alt`, `fa-tv`, `fa-star-half-alt`) | `widget_beeteam368_post_extensions` | **REPLACE** | Swap to icon family: Signals→`bolt`, Arcana→`gem`, Watch→`play-circle`, Community→`comments`, Explore→`compass`. Update via WordPress widget admin. |
| VidMov admin menu icon | `wp-admin` sidebar (native `dashicons-art` on Arcana menu) | **KEEP** | Dashboard-only; not public-facing. Current `dashicons-art` is acceptable. |
| wpForo default thread/post icons | Community pages | **REPLACE** | Long-term: custom wpForo theme stylesheet. Near-term: acceptable. |
| Font Awesome version lock | Global theme dependency | **KEEP** | FA 5 is adequate for the icon language chosen. Upgrading to FA 6 gains nothing material. |

---

## Section 2 — Images & Placeholders

| Element | Location | Classification | Notes |
|---|---|---|---|
| VidMov default thumbnail placeholder | `wp-content/themes/vidmov/css/images/placeholder.png` — referenced by `inc/template-tags.php` | **REPLACE** | Override via child theme or `wp_lazy_load_image_placeholder` filter. Target: `creator-placeholder.svg` (exported PNG). Asset exists at `docs/assets/brand-system/dark/creator-placeholder.svg`. |
| Default channel banner (empty) | Creator channel surfaces with no banner set | **REPLACE** | Upload `docs/assets/brand-system/dark/default-channel-banner.svg` as default in VidMov channel settings or apply via `beeteam368_user_cover_photo` default filter. |
| Default avatar fallback | Gravatar/VidMov user avatar chain | **REPLACE** | Set via WordPress `avatar_defaults` filter or ARMember default avatar. Target: `creator-placeholder.svg`. |
| WooCommerce placeholder product image | `wp-content/uploads/woocommerce-placeholder-*.jpg` | **REPLACE** | If Woo is retained: upload branded placeholder via `WooCommerce > Settings > Products`. If Woo is deprecated: safe to leave until removal. |
| ARMember default profile cover | `wp-content/uploads/armember/profile_default_cover.png` | **REPLACE** | Upload `default-channel-banner.svg` (exported PNG, 1500×500) as ARMember default cover. Path: `ARMember > Appearance > Default Cover`. |
| VidMov header preset images | `wp-content/themes/vidmov/inc/theme-options/images/header-*.png`, `archive-*.png` | **REMOVE** | Never displayed publicly; these are admin theme-picker previews shipped with the theme. Safe to ignore unless admin UX is a priority. |
| Legacy 2023 imagery in homepage rails | Various `wp_posts` attachments from 2023 campaigns | **REPLACE** | Replace with current 2026 creator/content assets during creator profile polish. See `art-direction` findings. |
| Social preview / OG image | AIOSEO social meta | **REPLACED** ✅ | Bunny CDN breakage fixed. Now points to canonical `www.offkilter.tv` asset. |

---

## Section 3 — Colors & Gradients

| Element | Location | Classification | Notes |
|---|---|---|---|
| Rainbow header accent strip | VidMov theme default header preset (`header-1.png` rainbow gradient top bar) | **REMOVE** | If active, replace with solid `#E50914` (OFFKILTER red) 3–4px bar or none. Check `Appearance > Customize > Header` settings. |
| Theme-colored content pills (category chips) | VidMov auto-generated category color pills on video cards | **REPLACE** | Standardize to `--ok-pill--signal` (blue) and `--ok-pill--arcana` (purple) classes. Low-priority — requires custom category color assignment per term. |
| Mixed accent colors in sidebar widgets | Legacy widget color overrides in `widget_custom_html` | **REPLACE** | Consolidate to `--ok-accent`, `--ok-signal`, `--ok-arcana`. CSS custom properties shipped via `offkilter-platform.css` now provide the baseline. |
| VidMov default blue link color | Global `a` styles in theme | **KEEP** | VidMov blue links are acceptable as a neutral state. Override only on OFFKILTER-specific content blocks. |
| myCred point type icon background colors | `wp-content/uploads/*/default-point-type*.png` | **REPLACE** | Long-term: create custom `ok-cred-point` icon set matching brand palette. Near-term: acceptable. |

---

## Section 4 — Typography

| Element | Location | Classification | Notes |
|---|---|---|---|
| VidMov default heading font | Theme `style.css` — likely Roboto or system sans | **KEEP** | The font itself is acceptable. Spacing and rhythm matter more. `offkilter-platform.css` now addresses vertical rhythm. |
| Inconsistent capitalization in nav labels | `Watch`, `signals`, `ARCANA` mix | **REPLACE** | Standardize to title case: `Home`, `Watch`, `Signals`, `Arcana`, `Creators`, `Community`, `Explore`. Update via `Appearance > Menus`. |
| Emoji in nav labels | `⚡ Signals`, `🔮 Arcana` | **REPLACE** | Remove emoji from navigation labels. They render inconsistently across OS/browser fonts and reduce professionalism. Use icon classes instead (e.g. `fas fa-bolt` via a CSS :before or Elementor icon field). |
| Footer copyright year | `Copyright © 2023 OFFKILTER.TV` | **REPLACE** | Update to `© 2026 OFFKILTER.TV` or use `<?php echo date('Y'); ?>` in footer template. |

---

## Section 5 — Navigation Chrome

| Element | Location | Classification | Notes |
|---|---|---|---|
| Duplicate menu item pairs (`Channel` and `Creators` overlap) | Main Menu | **REMOVED** ✅ | `Creators` now points to `/member-list/` and duplicate `Creator Directory` child removed. |
| Legacy `34.105.65.179` menu items (`1692`–`1704`) | Main Menu | **REMOVED** ✅ | Legacy IP links removed in prior remediation sprint. |
| Placeholder `#` parents (`Video`, `Channel`) | Main Menu | **REPLACE** | Assign real destinations. `Video` → `/video/`, `Channel` → `/member-list/` (or remove if redundant). |
| Footer nav duplication | Footer widgets `nav_menu-1` and `nav_menu-2` both point to `side-menu` | **REPLACE** | Create a dedicated `footer-nav` menu with 4–5 essential links (`Home`, `Arcana`, `Signals`, `Community`, `About`). |
| "Spread the love" social share bar above content rails | Homepage + archive templates (Heateor Social Sharing) | **REMOVED** ✅ | Hidden via CSS in `offkilter-platform.css`. Remains in DOM — consider plugin deactivation on non-post pages for cleaner HTML. |
| Duplicate auth routes (`/login/` and `/login-2/`, etc.) | Public URL space | **REMOVE** | Delete `-2` duplicate pages. User trust is reduced when multiple login/register URLs exist. |

---

## Section 6 — Creator / Member Surfaces

| Element | Location | Classification | Notes |
|---|---|---|---|
| Empty creator channel pages (no avatar, no banner, no bio) | `/channel/channel-id/@.../` for most imported creators | **REPLACE** | Apply `default-channel-banner.svg` and `creator-placeholder.svg` as fallbacks. Prioritize top Arcana creators for manual profile completion. See `creator-experience-review.md`. |
| Hash-like creator handles (`@cea018...`) | Member list, channel URLs | **REPLACE** | These are import artifacts. Manually clean or script-rename to creator's real channel name. Low-priority until Wave 2 creator outreach. |
| ARMember default social badge set | Profile pages | **REPLACE** | Long-term: design OFFKILTER rank/badge language (Signal Watcher, Arcana Reader, etc.). Near-term: remove/hide generic badge row if empty. |
| `vidmov_user_profile` linkage missing | Most creator accounts | **REPLACE** | Required for full VidMov creator page rendering. Complete for priority Arcana creators first. See `creator-experience-review.md`. |
| Subscribe count `0` visible | Creator channel pages | **KEEP** | The count itself is data. The presentation (showing `0` prominently) can be softened via CSS — but data accuracy matters more. |

---

## Section 7 — Homepage Rails

| Element | Location | Classification | Notes |
|---|---|---|---|
| `fab fa-canadian-maple-leaf` on all rails | Elementor page `1244` | **REMOVED** ✅ | Replaced with OFFKILTER icon set. |
| Duplicate content rails | Removed redundant `Latest on OKTV` duplicate | **REMOVED** ✅ | Confirmed unique purpose per rail. |
| Generic "Trending" language | `Trending This Week` rail | **KEEP** | Functional label. Replace if OFFKILTER-specific terminology is adopted (e.g., `Rising` or `Signal Surge`). |
| SidebarChat placeholder blocks | Elementor page `1244` | **KEEP** | Intentional coming-soon state. Remove when SidebarChat ships. |

---

## Section 8 — Legacy / Inherited Pages

| Page | Route | Classification | Notes |
|---|---|---|---|
| `/audio-category/music/` archive | Side menu | **REPLACE** | Assess whether audio/music is still a product pillar. If not, remove from navigation. |
| `/shop/`, `/cart/`, `/checkout-2/` | Public URL space | **REMOVE** (from nav) | Remove from menus. Keep pages accessible for existing integrations but stop surfacing in discovery. |
| `/my-account/` | Public | **REPLACE** | Redirect to ARMember profile or remove; WooCommerce account UX conflicts with ARMember UX. |
| Trending page (`/trending/`) | Main nav as `Explore` | **KEEP** | Renamed in nav from `Trending` to `Explore`. Page itself is VidMov-native and functional. |
| Member list (`/member-list/`) | Main nav as `Creators` | **KEEP** | Populated, functional, correctly surfaced in nav. |
| Channel directory (`/channel/`) | Not currently surfaced in primary nav | **REPLACE** | Sparse behavior. Assess vs. `/member-list/`. Unify into one creator discovery surface. |

---

## Section 9 — Plugin-Rendered UI

| Element | File | Classification | Notes |
|---|---|---|---|
| Disclaimer inline styles | `class-okarcana-disclaimer.php` | **REPLACED** ✅ | Removed `style="..."` attributes; now uses `okarcana-disclaimer` CSS class from `offkilter-platform.css`. |
| Signals shortcode bare `<p>` empty state | `class-okarcana-signals.php` | **REPLACED** ✅ | Now renders `oktv-signals-empty` branded empty state with icon. |
| Signals duration format (`Y-m-d`) | `class-okarcana-signals.php` | **REPLACED** ✅ | Changed to `M j` format (e.g., `Jun 29`) for readability. |
| Signals duration span (no class) | `class-okarcana-signals.php` | **REPLACED** ✅ | Duration span now carries `is-duration` class for monospace badge styling. |
| No frontend CSS from plugin | `offkilter-arcana.php` | **REPLACED** ✅ | `OKArcana_Frontend` class now enqueues `offkilter-platform.css` on all frontend pages. |

---

## Section 10 — Performance / Asset Debt

| Element | Location | Classification | Notes |
|---|---|---|---|
| Bunny CDN logo 403 (`oktv.b-cdn.net`) | Homepage logo references | **FIXED** ✅ | `bunnycdn_cdn_hostname` set to `www.offkilter.tv`. OG/Twitter images return 200. |
| Duplicate CSS from custom WP CSS and plugin CSS | `wp_options.custom_css` vs plugin-enqueued CSS | **REPLACE** | Move the Heateor suppression rule from custom WP CSS into `offkilter-platform.css` (done). Remove from WP custom CSS after deployment validation. |
| Unused VidMov theme preset images | `wp-content/themes/vidmov/inc/theme-options/images/` | **REMOVE** | These are never loaded in production but add to theme footprint. Safe to delete from the theme directory if a child-theme workflow is adopted. |
| Homepage not edge-cached | `cf-cache-status: DYNAMIC` | **REPLACE** | Add Cloudflare page rule or cache rule to cache the homepage. Low-risk given Elementor content. |

---

## Summary Scorecard

| Category | Total Items | REMOVED/FIXED | REPLACE Pending | KEEP |
|---|---|---|---|---|
| Icons | 5 | 1 | 2 | 2 |
| Images & Placeholders | 9 | 2 | 6 | 1 |
| Colors & Gradients | 5 | 0 | 3 | 2 |
| Typography | 4 | 0 | 4 | 0 |
| Navigation | 6 | 4 | 2 | 0 |
| Creator Surfaces | 5 | 0 | 4 | 1 |
| Homepage Rails | 4 | 3 | 0 | 1 |
| Legacy Pages | 7 | 0 | 3 | 4 |
| Plugin-Rendered UI | 5 | 5 | 0 | 0 |
| Performance / Assets | 4 | 2 | 2 | 0 |
| **Total** | **54** | **17** | **26** | **11** |

---

## Highest-Priority REPLACE Items (ranked)

1. **Theme placeholder image** — every video card without a thumbnail shows a VidMov-branded fallback.
2. **Default avatar fallback** — creator pages without avatars feel unfinished.
3. **Default channel banner** — empty channel headers are the largest visual trust gap.
4. **Footer copyright year** — `© 2023` reads as abandoned.
5. **Nav emoji removal** — `⚡` and `🔮` render inconsistently; replace with CSS icon classes.
6. **ARMember profile cover default** — generic cover on all profiles signals a template, not a platform.
7. **Footer nav menu** — duplicate `side-menu` usage in footer lacks editorial intent.
8. **Category pill color normalization** — random VidMov-assigned colors create visual noise.
9. **`/channel/` and `/member-list/` unification** — creator discovery split across two surfaces.
10. **Hash-like creator handles** — erodes trust when visible in member grid or channel URLs.
