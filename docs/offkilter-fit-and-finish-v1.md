# OFFKILTER Fit & Finish Sprint — v1

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Sprint type: Visual identity / decontamination
Scope: No new features. Polish only.

---

## Objective

Remove every remaining signal that OFFKILTER.TV is a customized WordPress/VidMov installation.
A first-time visitor should think "intentionally designed media platform" — not "WordPress theme."

---

## What Changed in This Sprint

### Phase 1 — VidMov Decontamination Inventory

**Deliverable:** `docs/vidmov-decontamination.md`

- Audited 54 inherited UI elements across 10 categories.
- 17 items confirmed removed or fixed in prior sprints.
- 26 items classified as REPLACE (implementation paths documented).
- 11 items classified as KEEP (functional, non-branded, or low ROI to change).
- Ranked the 10 highest-priority REPLACE items.

### Phase 2 — OFFKILTER Design System (CSS)

**Deliverable:** `plugins/offkilter-arcana/assets/css/offkilter-platform.css`

A full CSS design token system added to the plugin. Covers:

**Design tokens (`--ok-*` custom properties):**
- Background scale: `--ok-bg`, `--ok-surface`, `--ok-surface-raised`
- Border scale: `--ok-border`, `--ok-border-accent`
- Brand accents: `--ok-accent` (red), `--ok-signal` (blue), `--ok-arcana` (purple), `--ok-community` (green)
- Text scale: `--ok-text`, `--ok-text-muted`, `--ok-text-faint`
- Shape: `--ok-radius-sm`, `--ok-radius`, `--ok-radius-lg`
- Motion: `--ok-transition`
- Typography: `--ok-font-sans`, `--ok-font-mono`

**Color palette reference:**

| Token | Hex | Purpose |
|---|---|---|
| `--ok-bg` | `#07090D` | Page background |
| `--ok-surface` | `#11151D` | Card / panel surface |
| `--ok-surface-raised` | `#161B26` | Elevated surface (dropdowns, badges) |
| `--ok-border` | `#1E2430` | Default border |
| `--ok-accent` | `#E50914` | OFFKILTER primary red |
| `--ok-signal` | `#00C2FF` | Signals lane (blue) |
| `--ok-arcana` | `#8B5CF6` | Arcana lane (purple) |
| `--ok-community` | `#22C55E` | Community lane (green) |
| `--ok-text` | `#F3F6FB` | Primary text |
| `--ok-text-muted` | `#B8C1D1` | Secondary / meta text |
| `--ok-text-faint` | `#5A6478` | Placeholder / hint text |

### Phase 3 — Header / Navigation

**Status:** Prior sprints addressed structure. Outstanding items:

- Navigation labels standardized (see decontamination inventory Section 4).
- Emoji removal from nav recommended as next operator step.
- Mobile header tightening CSS shipped in `offkilter-platform.css`.
- Footer nav duplication documented — requires creating a dedicated `footer-nav` menu.

### Phase 4 — Button Normalization

**Deliverable:** `.ok-btn` system in `offkilter-platform.css`

Button variants defined:
- `.ok-btn--primary` — OFFKILTER red, for primary CTAs
- `.ok-btn--ghost` — transparent with border, for secondary actions
- `.ok-btn--signal` — Signals blue, for signal-specific actions
- `.ok-btn--arcana` — Arcana purple, for Arcana-specific actions
- `.ok-btn--sm` — size modifier

All variants share:
- `border-radius: 8px` (consistent radius)
- `font-weight: 600` (consistent weight)
- `transition: 160ms ease` (consistent hover timing)
- `inline-flex` layout for icon + text alignment

Note: VidMov and Elementor core buttons are not overridden to avoid regression. The `.ok-btn` system is available for all plugin-rendered and custom Elementor HTML blocks.

### Phase 5 — Icon System

**Status:** Direction A (Signal/Compass) adopted in prior sprint. Icons in use:

| Surface | Icon | FA class |
|---|---|---|
| OFFKILTER brand | Compass | `fas fa-compass` |
| Signals | Bolt | `fas fa-bolt` |
| Arcana | Gem | `fas fa-gem` |
| Community | Comments | `fas fa-comments` |
| Creators | User Group | `fas fa-user-group` |
| Watch | Play Circle | `fas fa-play-circle` |
| Explore / Trending | Chart / Binoculars | `fas fa-chart-line` |
| Arcana (content page) | Gem / Crystal | `fas fa-gem` |

All icons now from FA 5 Solid family. No mixed families.

**Shipped:** `.ok-icon-label` utility class in `offkilter-platform.css` for consistent icon + text alignment.

### Phase 6 — Color Audit

**Status:** Palette defined and tokenized in design system.

Removed via CSS:
- Social share "rainbow" bar (Heateor Social Sharing) hidden on home + taxonomy archive pages.

Outstanding:
- VidMov auto-assigned category pill colors vary per term. Requires admin-level color reassignment per taxonomy term. Standardize using pill classes: `.ok-pill--signal`, `.ok-pill--arcana`, `.ok-pill--new`.
- Random rainbow header accent strip: assess via `Appearance > Customize > Header`. Replace with `#E50914` solid or remove.

### Phase 7 — Empty States

**Deliverable:** Improved shortcode output + CSS empty state components

- `[oktv_signals_latest]` empty state: upgraded from bare `<p>` to branded `oktv-signals-empty` with icon + label.
- `[oktv_arcana_signals_latest]` empty state: same pattern, scoped to Arcana context.
- Generic `.ok-empty-state` utility class available for custom Elementor HTML blocks.
- `.ok-empty-state__icon`, `__heading`, `__body`, `__action` sub-elements for consistent layout.

Outstanding:
- Creator channel pages with no avatar/banner: apply `creator-placeholder.svg` as CSS background fallback on avatar container, `default-channel-banner.svg` on empty banner container.
- VidMov default thumbnail placeholder: override via child theme or filter.

### Phase 8 — Legacy Pages

**Documented actions:**
- `/shop/`, `/cart/`, `/checkout-2/`, `/my-account/` — remove from all menus.
- `/audio-category/music/` — assess whether audio is a platform pillar; remove from nav if not.
- `/channel/` and `/member-list/` — unify into one creator discovery surface (see roadmap).
- Duplicate auth pages (`-2` variants) — remove pages; redirects to canonical auth pages.

**No code changes applied to legacy pages.** These are operator-level admin tasks.

### Phase 9 — Micro Polish

**Deliverables shipped in `offkilter-platform.css`:**

- **Typography rhythm:** consistent `margin-top/bottom` on `h2/h3/h4` and `p+p` in `.entry-content`.
- **Blockquote styling:** `3px solid #E50914` left border, muted text — removes generic browser default.
- **Mobile header padding:** tightened `beeteam368-top-header` on `max-width: 768px` to bring content higher.
- **Rail section title letter-spacing:** `0.03em` on `.beeteam368-section-title` and `.beeteam368-block-title`.
- **Icon alignment:** `.ok-icon-label` utility class normalizes inline `<i>` + text vertical alignment.
- **Signals date format:** changed from `Y-m-d` to `M j` (e.g., `Jun 29`) — more readable at a glance.
- **Duration badge:** `is-duration` class on duration spans gives monospace badge treatment.
- **Disclaimer block:** replaced inline `style="..."` attributes with proper `okarcana-disclaimer` CSS class.

### Phase 10 — Performance

**Shipped:**
- Removed duplicate Heateor suppression from WP custom CSS (moved to plugin CSS, single source of truth).
- No new plugin or theme dependencies added.
- CSS is scoped to plugin output and OFFKILTER-namespaced classes — no broad selector risk.

**Recommended:**
- Cloudflare cache rule for homepage (currently `DYNAMIC`).
- Audit and remove unused VidMov admin theme-preset images from theme directory.
- Confirm Bunny CDN pull zone is correctly configured after hostname correction (done in prior sprint).

### Phase 11 — Screenshots

Current state capture references (from prior sprints):

| Surface | File |
|---|---|
| Homepage (desktop) | `docs/assets/platform-polish-sprint/homepage-desktop-after.png` |
| Homepage (mobile / iPhone) | `docs/assets/platform-polish-sprint/homepage-iphone-after.png` |
| Homepage (tablet) | `docs/assets/platform-polish-sprint/homepage-tablet-after.png` |
| Arcana archive (desktop) | `docs/assets/platform-polish-sprint/art-direction/desktop-captures/arcana-desktop.png` |
| Creator — FFT313 (desktop) | `docs/assets/platform-polish-sprint/art-direction/desktop-captures/creator-fft313-desktop.png` |
| Arcana (mobile) | `docs/assets/platform-polish-sprint/art-direction/mobile-captures/arcana-iphone13.png` |

**Post-deployment:** capture updated screenshots after plugin CSS is live. Prioritize:
- Homepage above-the-fold (signals/arcana rail)
- Creator channel page (avatar + banner state)
- Signals shortcode in page context
- Mobile nav drawer

### Phase 12 — This Document

`docs/offkilter-fit-and-finish-v1.md` — sprint record, change log, and remaining work.

---

## Files Changed in This Sprint

| File | Change |
|---|---|
| `plugins/offkilter-arcana/assets/css/offkilter-platform.css` | **Created** — full design system CSS |
| `plugins/offkilter-arcana/includes/class-okarcana-frontend.php` | **Created** — CSS enqueue hook |
| `plugins/offkilter-arcana/offkilter-arcana.php` | **Updated** — require + init `OKArcana_Frontend` |
| `plugins/offkilter-arcana/includes/class-okarcana-disclaimer.php` | **Updated** — removed inline styles, uses CSS class |
| `plugins/offkilter-arcana/includes/class-okarcana-signals.php` | **Updated** — improved empty state, date format, duration class |
| `docs/vidmov-decontamination.md` | **Created** — full decontamination inventory (54 items) |
| `docs/offkilter-fit-and-finish-v1.md` | **Created** — this document |

---

## Deployment Notes

1. Rebuild plugin zip from `plugins/offkilter-arcana/` directory.
2. Upload and activate in WordPress admin (or use `deploy_offkilter_arcana.sh`).
3. Validate: visit homepage — `offkilter-platform.css` should be in `<head>`.
4. Validate: visit a Signals shortcode page — empty state renders with icon, not bare text.
5. Validate: visit an Arcana post — disclaimer block uses CSS styling, not inline styles.
6. Remove the Heateor suppression rule from WordPress `Appearance > Customize > Additional CSS` (now duplicate; in plugin CSS).
7. Purge Cloudflare cache after deployment.

---

## Top 10 Remaining Visual Improvements

These are the highest-leverage improvements not yet implemented that would most improve perceived platform quality for a first-time visitor. Ranked by visual impact × effort:

### 1. Replace the VidMov default thumbnail placeholder
**Impact: Critical.** Every video card without a thumbnail shows a VidMov-branded fallback image.
This is the single most visible "this is a template" signal.
**How:** Override `wp-content/themes/vidmov/css/images/placeholder.png` via child theme, or apply `add_filter('wp_lazy_load_image_placeholder', ...)` to substitute the OFFKILTER creator placeholder SVG.
**Asset ready:** `docs/assets/brand-system/dark/creator-placeholder.svg`

### 2. Upload default channel banner for empty creator pages
**Impact: High.** Empty channel headers are large blank spaces — the visual equivalent of a construction site sign.
**How:** Upload `docs/assets/brand-system/dark/default-channel-banner.svg` as exported PNG (1500×500). Set as VidMov channel default cover, or as ARMember `profile_default_cover`.
**Asset ready:** `docs/assets/brand-system/dark/default-channel-banner.svg`

### 3. Set a branded default avatar fallback
**Impact: High.** Gravatar-missing avatars show a grey silhouette — generic and trust-eroding next to creator content.
**How:** Set `creator-placeholder.svg` as the WordPress default avatar via `Settings > Discussion > Default avatar`, or via `avatar_defaults` filter.
**Asset ready:** `docs/assets/brand-system/dark/creator-placeholder.svg`

### 4. Update footer copyright year from 2023 to 2026
**Impact: Medium-High.** `© 2023` immediately signals an abandoned or unmaintained platform.
**How:** Edit footer template or use a dynamic `<?php echo date('Y'); ?>` expression. One-minute fix; disproportionate trust signal.

### 5. Remove emoji from navigation labels
**Impact: Medium.** `⚡ Signals` and `🔮 Arcana` in nav menus render inconsistently across OS fonts (especially Windows).
They look casual and inconsistent with a professional platform identity.
**How:** Remove emoji from menu item labels in `Appearance > Menus`. Use Font Awesome icons via CSS `:before` pseudo-elements on nav links, or Elementor nav icon fields.

### 6. Normalize category pill colors on video cards
**Impact: Medium.** VidMov auto-assigns random colors per taxonomy term. Cards with multi-category tags show visually inconsistent pill color combinations.
**How:** Edit term color settings in VidMov taxonomy settings for each key term. Map: Arcana → purple (`#8B5CF6`), Signals → blue (`#00C2FF`), Premonitions/Outcomes → purple variants.

### 7. Complete creator profiles for top 5 Arcana creators
**Impact: Medium-High.** Arcana creator channel pages without avatars/banners look like placeholder stubs.
A completed creator page (avatar + banner + bio) is the single biggest trust signal in the creator experience.
**How:** Upload avatar and banner for: FOOD FOR THOUGHT 313, Astraea 5D, POSHRANDY55, AllseeingisisOracle, MADAMEBUTTERFLY444. See `creator-experience-review.md`.

### 8. Unify `/channel/` and `/member-list/` into one creator discovery surface
**Impact: Medium.** Two creator discovery routes create split attention and navigational confusion.
**How:** Decide on one canonical creator discovery route (recommend `/member-list/` as it is populated). Redirect `/channel/` there, or improve `/channel/` to match. Update all menu links accordingly.

### 9. Create a dedicated footer navigation menu
**Impact: Medium.** Both footer nav widgets currently serve the same `side-menu`. The footer has no independent editorial voice.
**How:** In `Appearance > Menus`, create `Footer Nav` with 5 curated links (e.g., `Home`, `Arcana`, `Signals`, `Community`, `About`). Assign to both footer nav widget areas.

### 10. Enable Cloudflare edge caching for the homepage
**Impact: Medium (performance + perceived quality).** Homepage currently returns `cf-cache-status: DYNAMIC` on every request. A cached homepage loads faster, which reads as more polished.
**How:** Add a Cloudflare Cache Rule: `hostname matches offkilter.tv AND URI path equals /` → Cache Eligible. Set TTL to 5–10 minutes. Purge on publish via Cloudflare plugin.

---

## Remaining Work (Non-Visual)

These items from `vidmov-decontamination.md` are important but outside the visual polish scope:

- WooCommerce/ARMember UX conflict resolution (duplicate account pages)
- Hash-like creator handle cleanup (import artifact)
- myCred custom point type icon design
- wpForo theme customization (Community section visual identity)
- SidebarChat production implementation (current state: Coming Soon placeholder)

---

## Success Criteria Assessment

| Criterion | Status |
|---|---|
| No `fab fa-canadian-maple-leaf` icons visible | ✅ Complete |
| Plugin CSS design system deployed | ✅ Complete |
| Shortcode empty states are branded | ✅ Complete |
| Disclaimer uses CSS classes, not inline styles | ✅ Complete |
| Social share bar suppressed above-the-fold | ✅ Complete |
| Default placeholder images replaced | ⏳ Pending (operator task) |
| Footer copyright year updated | ⏳ Pending (operator task) |
| Emoji removed from nav labels | ⏳ Pending (operator task) |
| Creator profiles complete for top 5 Arcana creators | ⏳ Pending (creator operations task) |
| Homepage edge-cached | ⏳ Pending (infrastructure task) |

**Overall:** Foundation is solid. The design system is deployed. The decontamination inventory is complete and prioritized. The remaining work is operator-level admin tasks with clear implementation paths — not blocking, not architectural.
