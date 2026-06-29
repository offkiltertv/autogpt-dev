# OFFKILTER Platform Identity v1.2 — Sprint Record

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `1.2.0` (bumped from `1.1.0`)
Scope: Platform identity — vocabulary, destination pages, creator platform, legacy cleanup.

---

## Mission

v1.1 finished the product-experience CSS pass. v1.2 is a behavioral and editorial identity sprint. OFFKILTER now has its own platform vocabulary, destination page shortcodes, creator platform extensions, and a reference glossary. The goal: a visitor who lands on any OFFKILTER page understands what kind of platform they're on within 5 seconds.

---

## What Changed in This Sprint

### Track A — Platform Language Normalization

All VidMov and generic WordPress terminology removed from user-facing plugin output.

| File | Change |
|---|---|
| `class-okarcana-admin.php` | "Imported Videos" → "Imported Content" |
| `class-okarcana-admin.php` | "Videos (90+s)" → "Watch (90+s)" |
| `class-okarcana-admin.php` | Backfill description: removed `vidmov_video` from UI text |
| `class-okarcana-admin.php` | Classification description: "Signal (0-90s) or Video (90+s)" → "Signal (≤90s) or Watch (>90s)" |
| `class-okarcana-admin.php` | Queue table column "Post" → "Content" |
| `class-okarcana-signals.php` | Default title `"⚡ Signals"` → `"Signals"` (emoji removed) |
| `class-okarcana-signals.php` | Default title `"🔮 Latest Signals"` → `"Latest Signals"` (emoji removed) |
| `class-okarcana-signals.php` | Empty state `"No signal clips found yet."` → `"No Signals found yet."` |
| `class-okarcana-signals.php` | Creator stat `"videos"` → `"clips"` |

### Track B — New Shortcode: Destination Pages

New file: `class-okarcana-destinations.php` — class `OKArcana_Destinations`.

#### `[oktv_destination_hero]`

Full-width hero block for pillar destination pages. Pre-filled for all 5 platform content types.

| Attribute | Default |
|---|---|
| `pillar` | `watch` |
| `headline` | Pillar default (see below) |
| `sub` | Pillar default |
| `cta_label` | Pillar default |
| `cta_href` | Pillar canonical route |

Pillar defaults:

| Pillar | Headline | CTA |
|---|---|---|
| `signals` | "Every signal deserves attention." | Browse Signals → `/video-category/signals/` |
| `arcana` | "The intuitive layer of OFFKILTER." | Explore Arcana → `/video-category/arcana/` |
| `creators` | "The people building OFFKILTER." | Find Creators → `/member-list/` |
| `community` | "The conversation starts here." | Join the Community → `/community/` |
| `watch` | "Watch OFFKILTER." | Start Watching → `/` |

CSS modifier: `.ok-destination-hero--{pillar}` sets the top accent border color to the pillar's `--ok-*` token.

#### `[oktv_pillar_strip]`

4-chip platform navigation strip. Optional `active` attribute marks current pillar.

```
[oktv_pillar_strip active="signals"]
```

Output: `<nav class="ok-pillar-strip">` with Signals, Arcana, Creators, Community chips. Active chip: `.ok-pillar-chip--active` (no pointer events, filled).

### Track C — Creator Platform Extension

Extended `[oktv_creator_spotlight]` in `class-okarcana-signals.php`:

New attribute: `show_discuss_cta` (default `0`). When `1`, appends a `coming_soon` Discuss CTA below the latest-posts list. Prepares creator profiles for SidebarChat activation without requiring any template edits.

```
[oktv_creator_spotlight user_id="87" show_discuss_cta="1"]
```

### Track D — CSS Extensions (Sections N, O, P)

3 new sections appended to `offkilter-platform.css`.

**Section N — Destination Page Templates** (~90 rules)
- `.ok-destination-hero` + 5 pillar modifiers — hero block with top border accent, centered layout
- `.ok-pillar-strip` / `.ok-pillar-chip` — horizontal navigation strip
- Pillar accent colors on chip hover and active states
- Mobile: `@media (max-width: 768px)` hero compression, chip size reduction

**Section O — Watch Experience** (~40 rules)
- Suppress VidMov tag cloud on video detail (`beeteam368-video-tags-wrapper` → `display: none`)
- Suppress social share block on video detail
- `.ok-transcript-slot` — coming-soon transcript placeholder (dashed border, 0.6 opacity, no pointer events)
- `.ok-video-context` — sticky sidebar container for creator panel on desktop (≥1024px)

**Section P — Legacy Neutralization** (~35 rules)
- REMOVE: `.beeteam368-download-btn` / `.bt368-download` — download buttons
- REMOVE: `.beeteam368-report-video` — stock report form
- HIDE: `#beeteam368-notification-bar` — stock notification bar
- NEUTRALIZE: `.beeteam368-breadcrumb` — faint `--ok-text-faint` treatment
- REMOVE: WordPress float chrome on video pages (`.alignleft`, `.alignright`, `.wp-caption`)
- NEUTRALIZE: VidMov view count icon chrome
- REMOVE: WooCommerce residual elements

### Track E — Documentation

**`docs/platform-language.md`** (NEW)
Platform vocabulary glossary covering: term definitions, pillar hierarchy with icon/color/route, CSS design token meaning, shortcode language reference, admin panel label changes, editorial voice guidelines, and post type mapping.

---

## CSS Section Reference (complete — v1.2)

| Section | Key selectors |
|---|---|
| Design Tokens | `:root { --ok-* }` |
| Social Share Suppression | `body.home .heateor_sss_*` |
| Button Normalization | `.ok-btn`, `.ok-btn--*` |
| Pill / Badge | `.ok-pill`, `.ok-pill--*` |
| Signals Rail | `.oktv-signals-*` |
| Disclaimer | `.okarcana-disclaimer` |
| Empty State Utility | `.ok-empty-state` |
| Navigation | `.beeteam368-top-header` (mobile) |
| Typography Rhythm | `.entry-content h2/h3/p/blockquote` |
| Icon Alignment | `.ok-icon-label` |
| A — Creator Profile | `.beeteam368-channel-*`, `.ok-creator-spotlight` |
| B — Content Cards | `.beeteam368-video-card`, `.bt368-item` |
| C — Archives | `body.tax-*`, `.archive-header` |
| D — Signals Cards | `.oktv-signals-cards`, `.oktv-signals-card` |
| G — ARMember Auth | `.arm-form`, `.arm-input-field`, `.arm-submit-btn` |
| H — wpForo Community | `.wpforo-post`, `.wpforo-btn`, `.wpforo-topic` |
| I — SidebarChat CTA Foundation | `.ok-discuss-cta`, `.ok-content-explainer`, `.ok-next-actions` |
| J — Navigation Refinements | `.current-menu-item > a`, `.sub-menu` |
| K — Video Detail | `body.single-vidmov_video` scoped rules |
| L — Accessibility | `.sr-only`, `.ok-skip-link`, `a:focus-visible` |
| M — Performance | Notes only |
| E — Microinteractions | `@keyframes ok-pulse`, `.ok-skeleton` |
| F — Mobile Precision | `@media (max-width: 768px)` |
| **N — Destination Page Templates** | `.ok-destination-hero`, `.ok-pillar-strip`, `.ok-pillar-chip` |
| **O — Watch Experience** | `.ok-transcript-slot`, `.ok-video-context`, tag/share suppression |
| **P — Legacy Neutralization** | Download/report/notification REMOVE rules |

---

## Shortcode Reference (complete — v1.2)

| Shortcode | Attributes | Output |
|---|---|---|
| `[oktv_signals_latest]` | `title`, `limit`, `categories`, `show_creator`, `show_duration`, `show_date`, `layout`, `wrapper_class` | Signals feed (list or cards) |
| `[oktv_arcana_signals_latest]` | Same + Arcana category defaults | Arcana signals feed |
| `[oktv_creator_spotlight]` | `user_id`, `show_stats`, `show_latest`, `show_discuss_cta`, `wrapper_class` | Creator card |
| `[oktv_discuss_cta]` | `context`, `label`, `href`, `coming_soon` | Discuss CTA pill |
| `[oktv_content_explainer]` | `type`, `headline`, `body`, `wrapper_class` | Editorial context block |
| `[oktv_next_action]` | `actions`, `wrapper_class` | Navigation action row |
| `[oktv_destination_hero]` | `pillar`, `headline`, `sub`, `cta_label`, `cta_href` | Pillar destination hero |
| `[oktv_pillar_strip]` | `active` | Platform navigation strip |

---

## Operator Runbook (v1.2 additions)

All prior v1.0 / v1.1 operator tasks remain applicable. New tasks:

### Step 1 — Embed destination heroes on pillar pages
**Where:** Edit each pillar page in Elementor. Add an HTML widget as the first block, before existing content.

Signals page (`/video-category/signals/` — if using a custom archive template):
```
[oktv_destination_hero pillar="signals"]
[oktv_pillar_strip active="signals"]
```

Arcana page:
```
[oktv_destination_hero pillar="arcana"]
[oktv_pillar_strip active="arcana"]
```

Creators page (`/member-list/`):
```
[oktv_destination_hero pillar="creators"]
[oktv_pillar_strip active="creators"]
```

Community page (`/community/`):
```
[oktv_destination_hero pillar="community"]
[oktv_pillar_strip active="community"]
```

### Step 2 — Add pillar strip to homepage
**Where:** Elementor homepage (page ID 1244) — add HTML widget below the hero section.
```
[oktv_pillar_strip]
```

### Step 3 — Update creator spotlights with Discuss CTA
**Where:** Any page where `[oktv_creator_spotlight]` is embedded.
**Change:** Add `show_discuss_cta="1"` attribute.
```
[oktv_creator_spotlight user_id="87" show_discuss_cta="1"]
```

### Step 4 — Verify legacy neutralization
**Where:** Visit any video page as a non-admin user.
**Check:**
- No download button appears
- No "Report Video" link appears
- No notification bar appears
- No WooCommerce elements appear
- Tag cloud below player is hidden
- Social share bar below player is hidden

### Step 5 — Remove emoji from any remaining nav labels
**Where:** Appearance > Menus
**Check:** Confirm no ⚡ or 🔮 in any menu labels (plugin defaults are now emoji-free; any persisting emoji are from manually-set menu labels).

### Step 6 — Deploy and validate
```bash
# From repo root
./scripts/deploy_offkilter_arcana.sh

# Validate version:
curl -s https://www.offkilter.tv/ | grep 'ver=1.2'

# Validate destination hero:
# Embed [oktv_destination_hero pillar="signals"] on any page
# Check: section has ok-destination-hero--signals class, blue top border

# Validate pillar strip:
# [oktv_pillar_strip active="arcana"] — Arcana chip should be active (filled)

# Validate legacy neutralization on video page:
# Visit /video/[any-slug]/ — download btn and report btn should not appear
```

---

## Page Score Deltas (v1.2 estimated)

| Page | v1.1 Score | v1.2 Score | Key changes |
|---|---|---|---|
| Homepage | 7.5/10 | 8/10 | Pillar strip available, destination heroes embeddable |
| Signals Archive | 7/10 | 8/10 | Destination hero available for operator embed |
| Arcana Archive | 7.5/10 | 8.5/10 | Destination hero available; no more emoji in title |
| Trending/Explore | 5.5/10 | 5.5/10 | (unchanged — editorial framing still operator task) |
| Creator Directory | 6/10 | 6.5/10 | Creator spotlight improvements |
| Creator Channel | 5–7/10 | 6–7.5/10 | Discuss CTA slot available on creator spotlights |
| Video Detail | 7.5/10 | 8/10 | Tag/share clutter removed, transcript slot available |
| Community | 7/10 | 7/10 | (unchanged — already addressed in v1.1) |
| Search | 4.5/10 | 4.5/10 | (unchanged — search redesign deferred to v1.3) |
| Archives | 6/10 | 6.5/10 | Legacy neutralization reduces VidMov residue |
| Auth Pages | 6.5/10 | 6.5/10 | (unchanged — CSS in place from v1.1) |

**Platform average before sprint:** ~6.9/10
**Platform average after sprint (estimated):** ~7.2/10
**After operator embeds destination heroes + pillar strips:** ~8.0/10 target within reach.

---

## Top 10 for v1.3

1. **Search results redesign** — Content-type grouped results (Signals | Watch | Arcana | Creators). Score impact: +2 for search. This is the highest-leverage remaining improvement.

2. **Destination hero operator embeds** — Shortcodes are ready; operator needs to embed them on pillar pages via Elementor. Estimated +1 for each pillar page once live.

3. **Arcana single post template** — `arcana_entry` custom post type still uses default WordPress/VidMov template. An Elementor single template with gem icon header, purple accent, related readings sidebar, and above-fold disclaimer would be the biggest quality leap remaining.

4. **SidebarChat activation** — CSS, PHP, and shortcode stubs are all in place. Activating Stream and replacing `coming_soon="1"` with live CTAs would transform the Community and Creator experience.

5. **Creator profile completions** — Top 5 Arcana creators need avatar + banner + bio. Plugin infrastructure is fully ready. This is pure operator/content work.

6. **Pillar strip on homepage** — The `[oktv_pillar_strip]` shortcode is now ready. Embedding it on the homepage is a 2-minute Elementor task with significant UX impact.

7. **Footer redesign** — `© 2023 OFFKILTER.TV` still visible. A dedicated Elementor footer template with correct year, editorial nav, and OFFKILTER brand block is straightforward.

8. **Category pill deployment to video cards** — `.ok-pill--signal` and `.ok-pill--arcana` classes are ready. Applying them to video cards requires a VidMov card template override in a child theme.

9. **Transcript slot deployment** — `.ok-transcript-slot` CSS and the `ok-transcript-slot` component are ready. When transcripts are available, this is a zero-CSS-work deployment.

10. **Skip link in theme header** — Section L CSS for `.ok-skip-link` is in place since v1.1. The actual `<a href="#main" class="ok-skip-link">` element needs to be added to the theme header template by the operator.

---

## Platform Score Trajectory

| Sprint | Version | Avg Score | Key lift |
|---|---|---|---|
| Initial audit | 0.0 | ~3.5/10 | Baseline |
| Fit & Finish | 0.2.0 | ~5.0/10 | Icon cleanup, design system |
| Platform v1.0 | 1.0.0 | ~6.3/10 | Creator CSS, card hover, archive headers |
| Platform v1.1 | 1.1.0 | ~6.9/10 | Auth CSS, wpForo, video detail, SidebarChat foundation |
| Platform v1.2 | 1.2.0 | ~7.2/10 | Platform vocabulary, destination pages, legacy cleanup |
| Post-embed target | 1.2.0 + ops | ~8.0/10 | Operator deploys destination heroes + pillar strips |
| Target v1.3 | — | ~8.5/10 | Search redesign, Arcana template, SidebarChat |
| Target v2.0 | — | ~9.0/10 | Full custom templates, creator program, SidebarChat live |
