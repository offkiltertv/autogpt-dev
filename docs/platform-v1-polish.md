# OFFKILTER Platform v1.0 Polish — Sprint Record

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `1.0.0` (bumped from `0.2.0`)
Scope: Visual identity, platform consistency, creator experience, discovery quality.

---

## Mission

Every public page on OFFKILTER should feel like one platform.
No page should make a visitor think "this is a WordPress theme."

---

## What Changed in This Sprint

### Plugin Changes

#### New file: `class-okarcana-identity.php`

WordPress image/avatar pipeline hooks that serve OFFKILTER-branded fallbacks without touching VidMov theme files.

| Hook | Effect |
|---|---|
| `avatar_defaults` | Adds `OFFKILTER Creator` as a selectable default avatar in WP Settings > Discussion |
| `get_avatar_url` | Returns `creator-placeholder.svg` when resolved URL points to Gravatar mystery-person |
| `wp_lazy_load_image_placeholder` | Returns branded placeholder for deferred VidMov/placeholder images |

**Activation:** Set default avatar to `OFFKILTER Creator` in WP Settings > Discussion after deploying.

#### New assets: `assets/img/`

| File | Source | Purpose |
|---|---|---|
| `creator-placeholder.svg` | `docs/assets/brand-system/dark/` | Avatar and thumbnail fallback |
| `default-channel-banner.svg` | `docs/assets/brand-system/dark/` | Channel header fallback |

#### Enhanced shortcode: `[oktv_signals_latest]`

New `layout` attribute with two options:

| Value | Output | Use case |
|---|---|---|
| `list` (default) | Row list with title, creator, duration badge, date | Sidebar, compact discovery blocks |
| `cards` | Grid of thumbnail cards with duration overlay | Full-width page sections, Signals page |

Card layout example:
```
[oktv_signals_latest layout="cards" limit="6" show_date="1"]
[oktv_arcana_signals_latest layout="cards" limit="6"]
```

List layout is unchanged (no regression).

#### New shortcode: `[oktv_creator_spotlight]`

Renders a compact creator card for embedding in any page or Elementor HTML block.

```
[oktv_creator_spotlight user_id="87" show_stats="1" show_latest="3"]
```

Output: creator avatar, display name, `@handle`, video count, latest N video titles linked.
CSS: `.ok-creator-spotlight` and sub-elements (see `offkilter-platform.css`).

#### CSS extensions: `offkilter-platform.css` — 6 new sections

| Section | Coverage |
|---|---|
| A — Creator Profile | Banner default, avatar sizing/ring, name/handle hierarchy, stats row, channel tabs, `.ok-creator-spotlight` component |
| B — Content Card | Hover lift, thumbnail background, duration badge, creator name weight, date color, title link color |
| C — Archive / Category | Archive header accent bar, body-class scoped colors for Signals (blue) and Arcana (purple), term description style |
| D — Signals Card Layout | `.oktv-signals-cards` grid, `.oktv-signals-card` with thumb overlay, title clamp, footer row |
| E — Microinteractions | `@keyframes ok-pulse` skeleton, `*:focus-visible` ring, button active state, link transition, `prefers-reduced-motion` guard |
| F — Mobile Precision | 44px tap targets, signals list 2-column collapse, single-column cards on mobile, creator spotlight stack, archive header tightening |

---

### Documentation Changes

| File | Purpose |
|---|---|
| `docs/platform-v1-consistency-audit.md` | Page-by-page 1–10 scoring across 11 public pages |
| `docs/platform-v1-polish.md` | This document — sprint record and operator runbook |

---

## Operator Runbook

Tasks that cannot be automated via the plugin. Execute after deploying plugin v1.0.0.

### Step 1 — Activate OFFKILTER default avatar
**Where:** WP Admin > Settings > Discussion > Default Avatar
**Action:** Select `OFFKILTER Creator`
**Effect:** Every missing avatar → `creator-placeholder.svg` branded fallback

### Step 2 — Upload default channel banner
**Where:** VidMov Theme Settings or ARMember > Appearance > Default Cover Photo
**Asset:** Export `plugins/offkilter-arcana/assets/img/default-channel-banner.svg` to PNG (1500×500)
**Effect:** Every empty channel header → OFFKILTER branded default

### Step 3 — Fix footer copyright year
**Where:** Appearance > Theme Editor > footer.php (or Elementor footer template)
**Action:** Change `© 2023 OFFKILTER.TV` to `© 2026 OFFKILTER.TV` (or dynamic: `© <?php echo date('Y'); ?> OFFKILTER.TV`)
**Impact:** Removes "abandoned site" signal immediately

### Step 4 — Remove emoji from navigation labels
**Where:** Appearance > Menus > Main Menu
**Action:** Edit labels: `⚡ Signals` → `Signals`, `🔮 Arcana` → `Arcana`
**Why:** Emoji render inconsistently on Windows/older Android; use Font Awesome icon classes instead
**Effect:** Professional, consistent nav across all OS/browsers

### Step 5 — Normalize category pill colors
**Where:** Posts > Categories (or VidMov taxonomy term color settings)
**Action:**
- Arcana → `#8B5CF6`
- Premonitions → `#8B5CF6`
- Outcomes → `#8B5CF6`
- Signals → `#00C2FF`
- YouTube (legacy) → `#5A6478` (muted — de-emphasize)
**Effect:** Consistent pill language across all video cards

### Step 6 — Add Signals archive page description
**Where:** Posts > Categories > Signals > Description field
**Action:** Add 1–2 sentences: `Fast clips, quick observations, and short-form discoveries from creators across OffKilter. Under 90 seconds.`
**Effect:** Archive page header shows editorial framing instead of blank space

### Step 7 — Add Arcana archive page description
**Where:** Posts > Categories > Arcana > Description field
**Action:** Add 1–2 sentences: `Curated tarot readings, premonitions, and outcomes from Arcana creators. For entertainment purposes only.`
**Effect:** Archive header shows Arcana editorial context

### Step 8 — Complete top 5 Arcana creator profiles
**Where:** Users > Edit User (for each creator)
**Priority creators:** FOOD FOR THOUGHT 313 (user `87`), Astraea 5D, POSHRANDY55, AllseeingisisOracle, MADAMEBUTTERFLY444
**For each:**
1. Upload avatar (minimum 400×400, square)
2. Upload channel banner (1500×500)
3. Add 2–4 sentence bio
4. Attach `vidmov_user_profile` to user record
5. Verify creator appears correctly on `/channel/channel-id/@username/`

### Step 9 — Create dedicated footer navigation menu
**Where:** Appearance > Menus
**Action:** Create new menu `Footer Nav` with links: Home, Arcana, Signals, Community, About
**Assign to:** Both footer widget areas (`nav_menu-1`, `nav_menu-2`)
**Effect:** Footer has editorial intent — not just a duplicate of the side menu

### Step 10 — Redirect `/channel/` to `/member-list/`
**Where:** Plugins > Redirection (or Nginx config on server)
**Action:** 301 redirect `/channel/` → `/member-list/`
**Effect:** One canonical creator discovery surface

### Step 11 — Remove WooCommerce from navigation menus
**Where:** Appearance > Menus
**Action:** Remove `/shop/`, `/cart/`, `/checkout-2/`, `/my-account/` from all menus
**Effect:** Platform navigation no longer suggests e-commerce confusion

### Step 12 — Enable Cloudflare homepage cache rule
**Where:** Cloudflare Dashboard > Rules > Cache Rules
**Action:** Create rule: `hostname equals offkilter.tv AND URI path equals /` → Cache Eligible, TTL 600s (10 min)
**Note:** Pair with a Cloudflare plugin or webhook to purge on homepage publish

### Step 13 — Remove duplicate auth route pages
**Where:** Pages (WP Admin)
**Action:** Delete `/login-2/`, `/register-2/`, `/edit_profile-2/`, `/password-2/` pages
**Set up:** 301 redirects from `-2` variants to canonical single pages
**Effect:** No more split login flows

### Step 14 — Deploy and validate
```bash
# From repo root
./scripts/deploy_offkilter_arcana.sh

# Then validate:
curl -I https://www.offkilter.tv/
# Check: offkilter-platform.css in <head>

# Validate avatar fallback:
# Visit creator page with no avatar → should show creator-placeholder.svg

# Validate signals cards:
# Embed [oktv_signals_latest layout="cards"] on a test page → thumbnail grid

# Validate creator spotlight:
# Embed [oktv_creator_spotlight user_id="87"] → FOOD FOR THOUGHT 313 card
```

---

## CSS Section Reference

All CSS is in `plugins/offkilter-arcana/assets/css/offkilter-platform.css`.

| Section | Line range (approx) | Key selectors |
|---|---|---|
| Design Tokens | 1–50 | `:root { --ok-* }` |
| Social Share Suppression | 52–60 | `body.home .heateor_sss_*` |
| Button Normalization | 62–115 | `.ok-btn`, `.ok-btn--*` |
| Pill / Badge | 117–160 | `.ok-pill`, `.ok-pill--*` |
| Signals Rail (list) | 162–260 | `.oktv-signals-*` |
| Disclaimer | 262–285 | `.okarcana-disclaimer` |
| Empty State Utility | 287–320 | `.ok-empty-state` |
| Navigation | 322–345 | `.beeteam368-top-header` (mobile) |
| Typography Rhythm | 347–368 | `.entry-content h2/h3/p/blockquote` |
| Icon Alignment | 370–382 | `.ok-icon-label` |
| **Section A — Creator Profile** | ~385–520 | `.beeteam368-channel-*`, `.ok-creator-spotlight` |
| **Section B — Content Cards** | ~522–590 | `.beeteam368-video-card`, `.bt368-item` |
| **Section C — Archives** | ~592–650 | `body.tax-*`, `.archive-header`, `.beeteam368-section-title::before` |
| **Section D — Signals Cards** | ~652–750 | `.oktv-signals-cards`, `.oktv-signals-card` |
| **Section E — Microinteractions** | ~752–800 | `@keyframes ok-pulse`, `.ok-skeleton`, `*:focus-visible` |
| **Section F — Mobile** | ~802–860 | `@media (max-width: 768px)` |

---

## Shortcode Reference

| Shortcode | Attributes | Output |
|---|---|---|
| `[oktv_signals_latest]` | `title`, `limit`, `categories`, `show_creator`, `show_duration`, `show_date`, `layout` (`list`/`cards`), `wrapper_class` | Signals feed — list or card grid |
| `[oktv_arcana_signals_latest]` | Same as above + defaults to Arcana categories | Arcana-scoped signals feed |
| `[oktv_creator_spotlight]` | `user_id`, `show_stats`, `show_latest`, `wrapper_class` | Compact creator card |

---

## Top 10 Remaining Visual Improvements (v1.1)

1. **wpForo Community visual identity** — Community page is the lowest-scoring surface (5/10). A dedicated wpForo child theme or targeted CSS injection would lift the entire community experience. Scoped as a full sub-sprint.

2. **Video detail page Arcana/Signals pill** — Video pages currently show no content-type badge. Adding a Signals or Arcana pill near the player title would immediately signal context. Requires child theme template or Elementor single template.

3. **Search results page redesign** — Search returns results in a generic VidMov list with no OFFKILTER treatment. A content-type grouped layout (Signals | Videos | Arcana | Creators) would dramatically increase perceived quality.

4. **ARMember auth page branding** — Login, register, and profile pages use ARMember default styling. Custom ARMember CSS to match the OFFKILTER design system would make the logged-in experience feel native.

5. **Arcana post detail page** — The `arcana_entry` custom post type uses the default VidMov/WordPress single template. An Elementor single template for arcana_entry would enable Arcana-specific page design (gem icon header, purple accent, related readings sidebar).

6. **Creator follow / subscribe CTA** — Creator channel pages have a subscribe button but it reads as a generic action. An OFFKILTER-branded "Follow this Creator" CTA with consistent button style would increase conversion.

7. **Trending / Explore editorial curation** — The `/trending/` page is an unedited algorithmic feed. Adding an editorial header via Elementor ("What OFFKILTER is watching this week") and pinning 2–3 curated items above the feed would lift perceived curation quality.

8. **Homepage SidebarChat activation** — The SidebarChat "Coming Soon" placeholder currently takes up real estate. Activating or removing it (replacing with a Community CTA) would improve above-the-fold focus.

9. **Skeleton loading states for Elementor** — The `ok-skeleton` CSS class is now available. Applying it to Elementor placeholder containers (via Custom CSS on widgets) would improve perceived loading speed on slower connections.

10. **Category pill deployment** — The `.ok-pill--signal`, `.ok-pill--arcana`, `.ok-pill--new` classes are defined and ready. Applying them to taxonomy chips on video cards (via VidMov card template override in child theme) would eliminate the random VidMov color-per-term visual noise.

---

## Platform Score Trajectory

| Sprint | Version | Avg Score | Key lift |
|---|---|---|---|
| Initial audit | 0.0 | ~3.5/10 | Baseline |
| Fit & Finish | 0.2.0 | ~5.0/10 | Icon cleanup, design system CSS |
| Platform v1.0 | 1.0.0 | ~6.3/10 | Creator CSS, card hover, archive headers, card layout, identity filters |
| Target v1.1 | — | ~8.0/10 | Community CSS, video detail, search, auth |
| Target v2.0 | — | ~9.0/10 | Full custom templates, creator program, SidebarChat |
