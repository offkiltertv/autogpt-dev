# OFFKILTER Platform v1.1 — Product Experience Sprint Record

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `1.1.0` (bumped from `1.0.0`)
Scope: Product experience — every page answers "What is this? Why stay? What next?"

---

## Mission

v1.0 established the design system and removed VidMov inheritance. v1.1 targets the four lowest-scoring surfaces (auth 4/10, community 5/10, video detail 6/10, search 4/10) and ensures every page gives a visitor a reason to stay and a clear path forward.

---

## What Changed in This Sprint

### CSS Extensions — `offkilter-platform.css` (7 new sections)

| Section | Purpose | Key selectors |
|---|---|---|
| G — ARMember Auth Pages | Replaces ARMember blue default with OFFKILTER design tokens | `.arm-form`, `.arm-input-field`, `.arm-submit-btn`, `.arm-field-title` |
| H — wpForo Community | Dark surfaces, OFFKILTER button language, community-accent typography | `.wpforo-post`, `.wpforo-btn`, `.wpforo-topic`, `.wpforo-board-head` |
| I — SidebarChat CTA Foundation | Reserved Discuss CTA component + coming-soon variant + content explainer + next actions row | `.ok-discuss-cta`, `.ok-discuss-cta--coming-soon`, `.ok-content-explainer`, `.ok-next-actions` |
| J — Navigation Refinements | Active menu item clarity, dropdown dark surface, 44px hamburger tap target | `.current-menu-item > a`, `.sub-menu`, `.beeteam368-menu-toggle` |
| K — Video Detail Page | Fluid title type, creator attribution row, category pills on video, related section border | `body.single-vidmov_video` scoped rules |
| L — Accessibility | `.sr-only`, `.ok-skip-link`, focus-visible ring, `pointer: coarse` touch targets, contrast guidance | `.sr-only`, `.ok-skip-link`, `a:focus-visible`, `*:focus-visible` |
| M — Performance | Deduplication notes, mobile block consolidation guidance | (documentation only) |

### New PHP Class — `class-okarcana-editorial.php`

**`OKArcana_Editorial`** registers three shortcodes and one `body_class` filter.

#### `body_class` filter
Adds `ok-auth-frame` to `body` on page IDs 7695 (register) and 7697 (login). Enables Section G CSS to scope the OFFKILTER wordmark context block above ARMember forms.

#### `[oktv_discuss_cta]`

| Attribute | Values | Default |
|---|---|---|
| `context` | `signals`, `arcana`, `video`, `creator`, `default` | `default` |
| `label` | string | `Discuss` |
| `href` | URL | `/community/` |
| `coming_soon` | `0` / `1` | `0` |

Examples:
```
[oktv_discuss_cta context="arcana" label="Talk Arcana"]
[oktv_discuss_cta coming_soon="1"]
```

Output: `.ok-discuss-cta` — pill-shaped anchor with Font Awesome comments icon. Coming-soon variant renders as `<span>` with `aria-disabled="true"` and no pointer events.

#### `[oktv_content_explainer]`

| Attribute | Values | Default |
|---|---|---|
| `type` | `signals`, `arcana`, `community`, `creators`, `watch` | `watch` |
| `headline` | string override | (type default) |
| `body` | string override | (type default) |
| `wrapper_class` | string | — |

Pre-filled defaults by type:

| Type | Headline | Body |
|---|---|---|
| `signals` | Fast clips. Under 90 seconds. | Short observations from creators across OFFKILTER. |
| `arcana` | Readings. Premonitions. Outcomes. | Curated interpretive content for entertainment. Trust your own intuition. |
| `community` | The conversation lives here. | Join discussions around Signals, Arcana, and creators. |
| `creators` | The people behind OFFKILTER. | Independent creators building something different. |
| `watch` | Watch OFFKILTER. | Arcana readings, short signals, and creator content in one place. |

Output: `.ok-content-explainer` with `role="region"` — icon (Font Awesome), headline, body. Uses `.ok-content-explainer--signals/arcana/community/creators` modifier for accent border.

#### `[oktv_next_action]`

| Attribute | Values | Default |
|---|---|---|
| `actions` | comma-separated: `watch`, `discuss`, `arcana`, `signals`, `creators`, `explore` | `watch,discuss` |
| `wrapper_class` | string | — |

Canonical routes:
- `watch` → `/`
- `discuss` → `/community/`
- `arcana` → `/video-category/arcana/`
- `signals` → `/video-category/signals/`
- `creators` → `/member-list/`
- `explore` → `/trending/`

Override URL syntax: `arcana:/custom-path/`

Output: `<nav class="ok-next-actions">` row of `.ok-btn.ok-btn--ghost` links with Font Awesome icons.

Example:
```
[oktv_next_action actions="arcana,signals,discuss"]
```

### Plugin Bootstrap — `offkilter-arcana.php`

- Version: `1.0.0` → `1.1.0`
- Added `require_once` for `class-okarcana-editorial.php`
- Added `OKArcana_Editorial::init()` call in `okarcana_bootstrap()`

---

## Operator Runbook

All prior v1.0 operator tasks (see `docs/platform-v1-polish.md`) remain applicable. New tasks for v1.1:

### Step 1 — Embed content explainers on Signals and Arcana archive pages
**Where:** WP Admin > Pages — or edit Elementor template for category archives
**Action:**
```
[oktv_content_explainer type="signals"]
[oktv_content_explainer type="arcana"]
```
Add these at the top of the respective archive page before the video grid.
**Effect:** Visitors immediately understand what Signals and Arcana are without reading any existing copy.

### Step 2 — Add next actions row to homepage
**Where:** Elementor Homepage (page ID 1244) — add HTML widget above the fold or below hero
**Action:**
```
[oktv_next_action actions="arcana,signals,creators,explore"]
```
**Effect:** Every new visitor has four clear paths from the homepage.

### Step 3 — Add Discuss CTA to Arcana video detail pages
**Where:** VidMov single video template or Elementor single-video Elementor template
**Action (coming-soon placeholder):**
```
[oktv_discuss_cta context="arcana" coming_soon="1"]
```
**Action (live, once community is active):** Remove `coming_soon="1"` attribute.
**Effect:** The platform communicates that discussion is a feature, not an accident. Even in coming-soon state, it frames the community as intentional.

### Step 4 — Add Discuss CTA to wpForo community page
**Where:** `/community/` page content area (before wpForo shortcode)
**Action:**
```
[oktv_content_explainer type="community"]
[oktv_discuss_cta context="default" label="Join the conversation" href="/community/forum/general/"]
```
**Effect:** Community page has a purpose statement before the forum list.

### Step 5 — Add skip link to theme header
**Where:** VidMov theme header template — or Elementor header template if applicable
**Action:** Add as first element inside `<body>`:
```html
<a href="#main" class="ok-skip-link">Skip to main content</a>
```
And add `id="main"` to the main content wrapper.
**Effect:** WCAG keyboard navigation, screen reader compatibility.

### Step 6 — Verify ARMember auth page CSS applies
**Where:** Visit `/register/` (page ID 7695) and `/login-2/` (page ID 7697) while logged out
**Verify:**
- `body` has class `ok-auth-frame`
- Form renders in dark surface with `--ok-border` input borders
- Submit button renders with `--ok-accent` red background
- Focus state on inputs shows `--ok-signal` blue outline (not default browser ring)
**Note:** If page IDs differ on production, update the `$auth_page_ids` array in `OKArcana_Editorial::add_auth_body_class()`.

### Step 7 — Verify wpForo community CSS applies
**Where:** Visit `/community/` and open a forum thread
**Verify:**
- Forum board head renders with `--ok-community` green label text
- Topic rows have dark surface, visible border separator
- wpForo buttons match `.ok-btn--ghost` ghost styling (transparent background, border)
- Reply textarea uses dark `--ok-bg` background with `--ok-border` border

### Step 8 — Review video detail page title size on mobile
**Where:** Visit any video page at `/video/[slug]/` on a 375px viewport
**Verify:** Title renders between 1.15rem and 1.6rem (CSS clamp). At 375px width, `4vw = 15px` — should render close to 1.15rem minimum.

### Step 9 — Confirm skip link behavior
**Where:** Any page — press Tab on keyboard immediately after page load
**Verify:** Skip link appears at top-left corner with red background, navigates focus to `#main` on Enter.

### Step 10 — Deploy and validate
```bash
# From repo root
./scripts/deploy_offkilter_arcana.sh

# Then validate version bump:
curl -s https://www.offkilter.tv/ | grep 'offkilter-platform'
# Should show ?ver=1.1.0 in the stylesheet URL

# Validate CSS loaded:
curl -s https://www.offkilter.tv/ | grep 'ok-skip-link'
# (offkilter-platform.css should be linked; checking for the class is not possible via curl alone)
```

---

## CSS Section Reference (complete — v1.1)

All CSS is in `plugins/offkilter-arcana/assets/css/offkilter-platform.css`.

| Section | Key selectors |
|---|---|
| Design Tokens | `:root { --ok-* }` |
| Social Share Suppression | `body.home .heateor_sss_*` |
| Button Normalization | `.ok-btn`, `.ok-btn--*` |
| Pill / Badge | `.ok-pill`, `.ok-pill--*` |
| Signals Rail (list) | `.oktv-signals-*` |
| Disclaimer | `.okarcana-disclaimer` |
| Empty State Utility | `.ok-empty-state` |
| Navigation | `.beeteam368-top-header` (mobile) |
| Typography Rhythm | `.entry-content h2/h3/p/blockquote` |
| Icon Alignment | `.ok-icon-label` |
| A — Creator Profile | `.beeteam368-channel-*`, `.ok-creator-spotlight` |
| B — Content Cards | `.beeteam368-video-card`, `.bt368-item` |
| C — Archives | `body.tax-*`, `.archive-header` |
| D — Signals Cards | `.oktv-signals-cards`, `.oktv-signals-card` |
| E — Microinteractions | `@keyframes ok-pulse`, `.ok-skeleton`, `*:focus-visible` |
| F — Mobile Precision | `@media (max-width: 768px)` |
| **G — ARMember Auth** | `.arm-form`, `.arm-input-field`, `.arm-submit-btn` |
| **H — wpForo Community** | `.wpforo-post`, `.wpforo-btn`, `.wpforo-topic` |
| **I — SidebarChat CTA Foundation** | `.ok-discuss-cta`, `.ok-content-explainer`, `.ok-next-actions` |
| **J — Navigation Refinements** | `.current-menu-item > a`, `.sub-menu` |
| **K — Video Detail** | `body.single-vidmov_video` scoped rules |
| **L — Accessibility** | `.sr-only`, `.ok-skip-link`, `a:focus-visible` |
| **M — Performance** | Notes only — see inline comments |

---

## Shortcode Reference (complete — v1.1)

| Shortcode | Attributes | Output |
|---|---|---|
| `[oktv_signals_latest]` | `title`, `limit`, `categories`, `show_creator`, `show_duration`, `show_date`, `layout` (`list`/`cards`), `wrapper_class` | Signals feed |
| `[oktv_arcana_signals_latest]` | Same + Arcana category defaults | Arcana feed |
| `[oktv_creator_spotlight]` | `user_id`, `show_stats`, `show_latest`, `wrapper_class` | Creator card |
| `[oktv_discuss_cta]` | `context`, `label`, `href`, `coming_soon` | Discuss CTA pill |
| `[oktv_content_explainer]` | `type`, `headline`, `body`, `wrapper_class` | "What is this?" block |
| `[oktv_next_action]` | `actions`, `wrapper_class` | Navigation action row |

---

## Page Score Deltas (v1.1 estimated)

| Page | v1.0 Score | v1.1 Score | Key changes |
|---|---|---|---|
| Homepage | 7/10 | 7.5/10 | Navigation active state, next-action shortcode available |
| Signals Archive | 6/10 | 7/10 | Content explainer embeddable, signals CTA |
| Arcana Archive | 7/10 | 7.5/10 | Content explainer, discuss CTA |
| Trending/Explore | 5/10 | 5.5/10 | Nav active state clarity |
| Creator Directory | 6/10 | 6/10 | (unchanged — profile content still needed) |
| Creator Channel | 5–7/10 | 5–7/10 | (unchanged — operator profile work still needed) |
| Video Detail | 6/10 | 7.5/10 | Title hierarchy, creator row, discuss CTA, category pills |
| Community | 5/10 | 7/10 | wpForo dark surfaces + community accent typography |
| Search | 4/10 | 4.5/10 | Partial card improvements carry through |
| Archives | 6/10 | 6/10 | (unchanged — term descriptions still operator task) |
| Auth Pages | 4/10 | 6.5/10 | ARMember CSS, auth body class, ok-auth-frame wordmark slot |

**Platform average before sprint:** ~6.3/10
**Platform average after sprint (estimated):** ~6.9/10
**Operator tasks remaining before target ~8.0/10:** Content explainers embedded on key pages; skip link in template; creator profiles completed; term descriptions added.

---

## Top 10 for v1.2

1. **Search results page redesign** — Results grouped by content type (Signals | Videos | Arcana | Creators) with OFFKILTER section headers. Score impact: +2 for search page alone.

2. **Arcana post detail custom template** — The `arcana_entry` custom post type needs an Elementor single template (gem icon header, purple accent, related readings sidebar, disclaimer above fold). Currently uses generic VidMov/WordPress single template.

3. **SidebarChat activation** — Replace all `coming_soon="1"` CTAs with live discussion integration. Foundation CSS and shortcode stubs are now in place.

4. **Creator profile completions (operator)** — Top 5 Arcana creators need avatar + banner + bio. The plugin infrastructure is fully in place. Scores 5 → 7+ per completed profile.

5. **Category pill deployment** — `.ok-pill--signal`, `.ok-pill--arcana` classes are ready. Deploying them to video cards requires a VidMov card template override in a child theme.

6. **Footer redesign** — Footer year (© 2023), lack of editorial navigation, and no OFFKILTER brand block are still trust-eroding. A dedicated footer Elementor template would fix all three.

7. **Trending/Explore editorial framing** — Add Elementor block above the algorithmic feed: "What OFFKILTER is watching this week" header + 2–3 pinned/curated items. Lifts Trending from generic sorted list to curated editorial surface.

8. **Creator follow CTA normalization** — ARMember's "Subscribe" / "Follow" button on creator channels should match `.ok-btn--primary` styling. CSS hook: `.arm-subscribe-btn`, `.arm-member-action`.

9. **Skip link and ARIA deployment** — Section L CSS is now in place; the skip link `<a>` element still needs to be added to the theme header template by the operator.

10. **Skeleton loading states activation** — `.ok-skeleton` and `@keyframes ok-pulse` are defined. Apply via Elementor Custom CSS on loading containers for perceived performance improvement on slower connections.

---

## Platform Score Trajectory

| Sprint | Version | Avg Score | Key lift |
|---|---|---|---|
| Initial audit | 0.0 | ~3.5/10 | Baseline |
| Fit & Finish | 0.2.0 | ~5.0/10 | Icon cleanup, design system CSS |
| Platform v1.0 | 1.0.0 | ~6.3/10 | Creator CSS, card hover, archive headers, identity filters |
| Platform v1.1 | 1.1.0 | ~6.9/10 | Auth CSS, wpForo CSS, video detail, SidebarChat foundation, a11y |
| Target v1.2 | — | ~8.0/10 | Search redesign, Arcana template, creator profiles, skip link deployed |
| Target v2.0 | — | ~9.0/10 | Full custom templates, creator program, SidebarChat live |
