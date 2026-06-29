# OFFKILTER Platform v1.3 — Discovery Platform Sprint Record

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `1.3.0` (bumped from `1.2.0`)
Scope: Discovery UX — platform intro, editorial curation, full creator pages.

---

## Mission

v1.2 gave OFFKILTER vocabulary and destination heroes. v1.3 makes it a discovery platform: visitors find content through editorial curation (not just chronological feeds), creator pages become rich destinations, and the homepage can explain the platform in under 5 seconds.

---

## What Changed in This Sprint

### New PHP Class: `class-okarcana-discovery.php`

Three new shortcodes. All follow the established static method / `shortcode_atts` / full-escaping pattern.

---

#### `[oktv_platform_intro]`

"What is OFFKILTER?" — a pillar grid for homepage orientation. Covers Phase 2 (homepage storytelling) in a single shortcode.

| Attribute | Default |
|---|---|
| `headline` | "OFFKILTER is a discovery platform." |
| `sub` | "Three things worth understanding." |
| `pillars` | `signals,arcana,creators` |
| `wrapper_class` | — |

Output: Dark surface section with centered headline, sub, and a 3-column grid of pillar cards. Each card has a FontAwesome icon, name, one-line description, and links to the canonical pillar route.

Pillar card modifiers: `.ok-platform-intro__card--{signals|arcana|creators|community|watch}` — sets accent color on icon.

---

#### `[oktv_curated_section]`

Editorial curation wrapper. Replaces purely chronological feeds with hand-picked or category-scoped sections bearing an editorial accent header.

| Attribute | Default |
|---|---|
| `title` | "Worth Watching" |
| `label` | — (no badge if empty) |
| `post_ids` | — (overrides query; rendered in order) |
| `categories` | — |
| `limit` | `6` |
| `layout` | `cards` |
| `pillar` | — (signals/arcana/creators/community — tints accent bar + label) |
| `wrapper_class` | — |

When `post_ids` is set, queries exactly those IDs in order. Otherwise queries by category. Content rendered using `.oktv-signals-cards` (cards layout) or `.oktv-signals-list` (list layout) — reuses all existing card CSS.

---

#### `[oktv_creator_page]`

Full creator page layout. Replaces the compact `[oktv_creator_spotlight]` for dedicated creator/channel pages.

| Attribute | Default |
|---|---|
| `user_id` | `0` (required) |
| `featured_ids` | — (pinned post IDs, horizontal scroll row) |
| `show_signals` | `1` |
| `signals_limit` | `6` |
| `show_playlists` | `0` |
| `playlist_title` | "Playlist" |
| `playlist_ids` | — (ordered list by explicit IDs) |
| `show_related` | `0` |
| `related_ids` | — (user IDs for "More Creators" row) |
| `show_discuss_cta` | `0` |
| `wrapper_class` | — |

Sections rendered only when relevant content/flag present:
1. **Creator header** — avatar (80px), name, handle, clips count
2. **Featured** — horizontal scroll row of pinned posts (when `featured_ids` set)
3. **Latest Signals** — signals card grid (when `show_signals=1` and creator has Signals)
4. **Playlist** — numbered ordered list (when `show_playlists=1` + `playlist_ids` set)
5. **More Creators** — row of compact `.ok-creator-spotlight` cards (when `show_related=1` + `related_ids` set)
6. **Discuss slot** — coming-soon community CTA (when `show_discuss_cta=1`)

Related creators section calls `OKArcana_Signals::render_creator_spotlight_shortcode()` directly — no code duplication.

---

### CSS Extensions (Sections Q, R)

**Section Q — Discovery UX** (~90 rules)

`.ok-platform-intro`:
- 3-column grid on desktop, 1-column on mobile
- Cards link to pillar routes; horizontal layout on mobile (icon left, text right)
- Pillar icon accent colors: signals=blue, arcana=purple, community=green, creators/watch=red

`.ok-curated-section`:
- `::before` accent bar (3px, pillar-colored)
- `.ok-curated-section__label` — small pill badge with pillar-tinted background
- Reuses `.oktv-signals-cards` / `.oktv-signals-list` — no new grid CSS

**Section R — Creator Page** (~100 rules)

`.ok-creator-page`:
- `flex-direction: column; gap: 2rem` — all sections naturally spaced
- Header: 80px avatar, name/handle/stat row, responsive gap reduction on mobile
- `.ok-creator-page__featured` — `overflow-x: auto` horizontal scroll, `flex-shrink: 0` items
- `.ok-creator-page__playlist` — ordered list with right-aligned number + link
- `.ok-creator-page__related` — wrapping flex row; related spotlights get card treatment
- Mobile: related creators stack to full width

---

### Bootstrap (`offkilter-arcana.php`)

- Version: `1.2.0` → `1.3.0`
- Added `require_once` for `class-okarcana-discovery.php`
- Added `OKArcana_Discovery::init()` call

---

## Shortcode Reference (complete — v1.3, 11 total)

| Shortcode | Attributes | Output |
|---|---|---|
| `[oktv_signals_latest]` | `title`, `limit`, `categories`, `show_creator`, `show_duration`, `show_date`, `layout`, `wrapper_class` | Signals feed |
| `[oktv_arcana_signals_latest]` | Same + Arcana category defaults | Arcana signals feed |
| `[oktv_creator_spotlight]` | `user_id`, `show_stats`, `show_latest`, `show_discuss_cta`, `wrapper_class` | Compact creator card |
| `[oktv_discuss_cta]` | `context`, `label`, `href`, `coming_soon` | Discuss CTA pill |
| `[oktv_content_explainer]` | `type`, `headline`, `body`, `wrapper_class` | Editorial context block |
| `[oktv_next_action]` | `actions`, `wrapper_class` | Navigation action row |
| `[oktv_destination_hero]` | `pillar`, `headline`, `sub`, `cta_label`, `cta_href` | Pillar destination hero |
| `[oktv_pillar_strip]` | `active` | Platform navigation strip (4 chips) |
| `[oktv_platform_intro]` | `headline`, `sub`, `pillars`, `wrapper_class` | "What is OFFKILTER?" pillar grid |
| `[oktv_curated_section]` | `title`, `label`, `post_ids`, `categories`, `limit`, `layout`, `pillar`, `wrapper_class` | Editorial curation section |
| `[oktv_creator_page]` | `user_id`, `featured_ids`, `show_signals`, `signals_limit`, `show_playlists`, `playlist_title`, `playlist_ids`, `show_related`, `related_ids`, `show_discuss_cta`, `wrapper_class` | Full creator page layout |

---

## Operator Runbook (v1.3 additions)

All prior v1.0–v1.2 operator tasks remain applicable.

### Step 1 — Homepage orientation block
**Where:** Elementor homepage (page ID 1244) — add HTML widget above content rails, below hero
```
[oktv_platform_intro headline="Three things worth knowing."]
```

### Step 2 — Curated Signals section
**Where:** Replace the standard signals rail on Signals archive or homepage with:
```
[oktv_curated_section title="Signals Worth Your Time" label="Curated" categories="signals" limit="6" pillar="signals" layout="cards"]
```

### Step 3 — Curated Arcana section
**Where:** Arcana archive or homepage:
```
[oktv_curated_section title="This Week in Arcana" label="Editor's Pick" categories="arcana" limit="6" pillar="arcana" layout="cards"]
```

### Step 4 — Hand-picked curated section (editor-driven)
**Where:** Any page where editorial voice is needed:
```
[oktv_curated_section title="Don't Miss" label="Editor's Pick" post_ids="101,234,567" pillar="arcana" layout="cards"]
```
Replace IDs with actual post IDs of curated content.

### Step 5 — Full creator page for FOOD FOR THOUGHT 313
**Where:** Create a new page or use the `/channel/channel-id/@foodforthought313/` Elementor template:
```
[oktv_creator_page user_id="87" show_signals="1" signals_limit="6" show_discuss_cta="1"]
```

With featured content (once 2–3 standout posts identified):
```
[oktv_creator_page user_id="87" featured_ids="101,102" show_signals="1" show_discuss_cta="1"]
```

### Step 6 — Navigation menu cleanup (Phase 1 operator task)
**Where:** WP Admin > Appearance > Menus
- Rename "Trending" → "Explore" (if not already done)
- Move pillar items to top: Signals → Arcana → Creators → Community
- Remove "Categories" / "Archives" links from main nav
- Effect: navigation now leads with platform pillars, not generic WordPress taxonomy browsing

### Step 7 — Deploy and validate
```bash
./scripts/deploy_offkilter_arcana.sh

# Validate version:
curl -s https://www.offkilter.tv/ | grep 'ver=1.3'

# Test on a staging page:
# Embed [oktv_platform_intro] — check 3-column grid on desktop, stacks on mobile
# Embed [oktv_curated_section title="Test" categories="signals" pillar="signals"]
# Embed [oktv_creator_page user_id="87" show_signals="1"]
```

---

## VidMov Remaining Artifact Inventory (Phase 5 Audit)

Artifacts still present that require operator or child-theme action. CSS suppression is in place where possible (Sections P and previous).

| Artifact | Location | Status | Resolution |
|---|---|---|---|
| Default `"© 2023 OFFKILTER.TV"` footer | Footer template | **Open** | Operator: edit Elementor footer template |
| Category pill colors (auto-assigned per term) | Video cards | **Open** | Requires VidMov card template override in child theme |
| Generic "more like this" related videos section | `body.single-vidmov_video` | **Open** | Requires VidMov single template override |
| Tag cloud below video player | Suppressed via CSS Section O (`display: none`) | **Resolved** | — |
| Download button | Suppressed via CSS Section P | **Resolved** | — |
| Report video button | Suppressed via CSS Section P | **Resolved** | — |
| Notification bar | Suppressed via CSS Section P | **Resolved** | — |
| WooCommerce residuals | Suppressed via CSS Section P | **Resolved** | — |
| Social share block on video detail | Suppressed via CSS Section O | **Resolved** | — |
| Emoji in shortcode default titles (⚡🔮) | Removed in v1.2 | **Resolved** | — |
| `vidmov_video` in admin UI labels | Removed in v1.2 | **Resolved** | — |
| ARMember default form styling | Overridden via CSS Section G | **Resolved** | — |
| wpForo default theme | Overridden via CSS Section H | **Resolved** | — |

---

## Platform Confidence Review (Phase 6 — "Investor / Creator Pitch" Standard)

### Homepage
**Score: 7.5/10 → 8/10 after ops embed platform_intro**
Would ship. The above-the-fold Elementor layout already has content hierarchy. Platform intro shortcode now available — adds the "why should I care" layer.
**Remaining gap:** Footer still shows 2023 copyright. No editorial "from the team" voice above content rails.

### Signals Archive
**Score: 7/10 → 8.5/10 after ops embed destination_hero + curated_section**
With destination hero + curated section shortcodes embedded: would confidently present as a purpose-built short-form content surface.
**Remaining gap:** Archive page template itself is still VidMov default — no custom grid density control.

### Arcana Archive
**Score: 7.5/10 → 8.5/10 after ops embed**
Same pattern as Signals. Purple accent treatment exists. Disclaimer on arcana_entry posts is the right call.
**Remaining gap:** No Arcana-specific single post template — readings look like generic WordPress posts.

### Creator Directory (`/member-list/`)
**Score: 6/10 → 7/10 after ops embeds**
Creator page shortcode now available for featured creator embeds. Directory grid itself is still VidMov default styling.
**Remaining gap:** Hash-like handles visible in grid for creators without custom handles. Profile completions.

### Creator Channel Pages
**Score: 6–7.5/10 → 7.5–8.5/10 with creator_page shortcode**
`[oktv_creator_page]` transforms a creator page from "compact card" to "real destination." Featured content, signals rail, playlist, discuss slot all available.
**Remaining gap:** Needs operator embed per creator. VidMov channel tabs (Videos, About, Discussion) still use default styles.

### Video Detail
**Score: 8/10**
Section K, O have cleaned this up well. Creator attribution row, category pills, clutter suppression all in place.
**Remaining gap:** Related videos section is still VidMov default. No transcript.

### Community
**Score: 7/10**
wpForo Section H treatment is solid. Editorial explainer shortcode available to embed above forum list.
**Remaining gap:** SidebarChat is still coming-soon only. Forum category names could benefit from OFFKILTER terminology.

### Authentication
**Score: 6.5/10**
ARMember CSS in place. Auth body class applied.
**Remaining gap:** Duplicate auth routes still need operator cleanup.

### Search
**Score: 4.5/10 — NOT investor-ready**
No OFFKILTER treatment on search results. Generic VidMov list. Content-type grouping absent.
**This is the single highest-priority remaining surface for v1.4.**

---

## Page Score Deltas (v1.3)

| Page | v1.2 Score | v1.3 Score | Key lift |
|---|---|---|---|
| Homepage | 7.5/10 | 8/10 | `[oktv_platform_intro]` available |
| Signals Archive | 7/10 | 8.5/10 | Destination hero + curated section (post-ops) |
| Arcana Archive | 7.5/10 | 8.5/10 | Destination hero + curated section (post-ops) |
| Trending/Explore | 5.5/10 | 6/10 | Curated section embeddable on this page |
| Creator Directory | 6/10 | 6.5/10 | Creator page shortcode available |
| Creator Channel | 6–7.5/10 | 7.5–8.5/10 | `[oktv_creator_page]` full layout |
| Video Detail | 8/10 | 8/10 | (no changes) |
| Community | 7/10 | 7/10 | (no changes) |
| Search | 4.5/10 | 4.5/10 | (deferred to v1.4) |
| Archives | 6.5/10 | 6.5/10 | (no changes) |
| Auth Pages | 6.5/10 | 6.5/10 | (no changes) |

**Platform average before sprint:** ~7.2/10
**Platform average after sprint + operator embeds:** ~7.8/10
**Search redesign (v1.4) is the biggest remaining lift.**

---

## Top 10 for v1.4

1. **Search results redesign** — Content-type grouped results (Signals | Watch | Arcana | Creators). Score impact: +3 for search page. Highest single-page impact remaining.

2. **Operator: embed platform_intro on homepage** — One Elementor HTML widget. Estimated impact: +0.5 homepage score immediately.

3. **Operator: embed destination heroes on pillar pages** — Signals, Arcana, Creators, Community. Each is a 2-minute Elementor edit.

4. **Arcana single post template** — `arcana_entry` needs Elementor single template with gem header, purple accent, above-fold disclaimer, related readings. The highest-quality remaining template work.

5. **SidebarChat activation** — All foundation CSS and shortcode stubs in place. Activating Stream and replacing `coming_soon="1"` would transform Community and Creator pages.

6. **Creator profile completions** — Top 5 Arcana creators need avatar + banner + bio. Plugin infrastructure fully ready.

7. **Footer redesign** — 2023 copyright, no editorial nav, no brand block. All fixable via Elementor footer template. ~30 minutes of operator work.

8. **Category pill deployment to video cards** — `.ok-pill--signal` and `.ok-pill--arcana` ready. Requires VidMov card template override in child theme.

9. **`[oktv_creator_page]` operator embed for featured creators** — The shortcode is ready; someone needs to embed it on creator pages and provide `featured_ids` + `playlist_ids`.

10. **Duplicate auth route cleanup** — `/login-2/`, `/register-2/`, `/edit_profile-2/`, `/password-2/` still exist. 301 redirects + page deletion is pure operator work.

---

## Platform Score Trajectory

| Sprint | Version | Avg Score | Key lift |
|---|---|---|---|
| Initial audit | 0.0 | ~3.5/10 | Baseline |
| Fit & Finish | 0.2.0 | ~5.0/10 | Design system |
| Platform v1.0 | 1.0.0 | ~6.3/10 | Creator CSS, cards, archives |
| Platform v1.1 | 1.1.0 | ~6.9/10 | Auth, wpForo, video detail, SidebarChat |
| Platform v1.2 | 1.2.0 | ~7.2/10 | Vocabulary, destination pages, legacy cleanup |
| Platform v1.3 | 1.3.0 | ~7.5/10 | Discovery: platform intro, curation, creator pages |
| Post-embed target | 1.3.0 + ops | ~7.8/10 | Operator deploys shortcodes to key pages |
| Target v1.4 | — | ~8.5/10 | Search redesign, Arcana template, SidebarChat |
| Target v2.0 | — | ~9.0/10 | Full custom templates, creator program, live chat |
