# OFFKILTER Platform v1.4 — Productization Sprint Record

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `1.4.0` (bumped from `1.3.0`)
Scope: Productization — hide the implementation, expose the product.

---

## Mission

v1.3 gave OFFKILTER a discovery platform: platform intro, editorial curation, full creator pages. v1.4 is a finishing sprint — no new features for their own sake, just making every public surface feel intentionally product-designed. Visitors should never see WordPress, VidMov, or plugin terminology. The implementation is hidden; the product is what they see.

---

## What Changed in This Sprint

### Track A — PHP Extensions (`class-okarcana-discovery.php`)

#### 4 New Shortcodes

---

##### `[oktv_discover_page]`
Editorial Discover page — an alternative to the generic Trending page. Four editorial sections (Signals, Arcana, Creators, Discussions) composited into a single coherent page layout. Operators create a "Discover" WordPress page, embed this shortcode, rename the nav item.

| Attribute | Default | Notes |
|---|---|---|
| `signals_label` | "Featured Signals" | |
| `signals_ids` | "" | Hand-picked IDs; falls back to category query |
| `signals_limit` | `4` | |
| `arcana_label` | "Featured Arcana" | |
| `arcana_ids` | "" | |
| `arcana_limit` | `4` | |
| `show_creators` | `1` | |
| `creators_label` | "Creators to Watch" | |
| `creator_ids` | "" | Falls back to top creators by post count |
| `creators_limit` | `3` | |
| `show_discussions` | `1` | Renders community CTA |
| `discussions_label` | "In the Community" | |

Calls `render_curated_section()` directly for DRY content rendering. Creators row calls `OKArcana_Signals::render_creator_spotlight_shortcode()` per user ID.

---

##### `[oktv_recommended_next]`
"Watch Next" block for video detail pages. Queries related content by `vidmov_video_category` taxonomy match, excluding the current post. Auto-detects current post ID.

| Attribute | Default |
|---|---|
| `post_id` | `0` (auto-detects) |
| `limit` | `3` |
| `label` | "Watch Next" |
| `pillar` | "" |
| `wrapper_class` | "" |

Falls back to recent posts if no taxonomy match. Returns empty string if no related content found — no visible empty state.

---

##### `[oktv_watch_nav]`
Playlist prev/next navigation. Accepts an explicit ordered list of post IDs, finds the current post's position, renders previous/next links with optional post titles.

| Attribute | Default |
|---|---|
| `playlist_ids` | "" (required) |
| `current_id` | `0` (auto-detects) |
| `prev_label` | "Previous" |
| `next_label` | "Next" |
| `show_title` | `1` |
| `wrapper_class` | "" |

Returns empty if `playlist_ids` is empty, current post not found in list, or only one item in list with no neighbors.

---

##### `[oktv_featured_creator]`
Prominent editorial creator callout — horizontal card with 96px avatar, name, handle, editorial description text, and CTA button. Larger and more narrative than `[oktv_creator_spotlight]`.

| Attribute | Default |
|---|---|
| `user_id` | `0` (required) |
| `headline` | "" (falls back to display_name) |
| `body` | "" (editorial description) |
| `cta_label` | "See their work" |
| `cta_href` | "" (falls back to author archive URL) |
| `pillar` | "" (accent color modifier) |
| `wrapper_class` | "" |

---

#### Extended `[oktv_creator_page]`

4 new attributes:

| New Attribute | Default | Behavior |
|---|---|---|
| `show_bio` | `0` | Renders WP user description field as "About" section |
| `show_videos` | `0` | Shows long-form Watch content (META_CLASS=watch) |
| `videos_limit` | `6` | Max 12 |
| `videos_label` | "Videos" | Section label |

Bio section inserted after creator header, before featured. Videos section inserted after Signals section.

---

#### Microcopy Fixes

| File | Before | After |
|---|---|---|
| `class-okarcana-discovery.php` | "No content found." | "Nothing here yet." |
| `class-okarcana-discovery.php` | "More Creators" | "Discover Creators" |

---

### Track B — CSS Extensions (Sections S, T, U)

**Section S — Discover Page + Card Quality + Featured Creator** (~190 rules)

- `.ok-discover-page` — flex column layout, 2.5rem section gap
- `.ok-discover-creators-row` — wrapping flex row; creator spotlights get card treatment
- `.ok-discuss-placeholder` — community teaser with dashed community-green border
- `.oktv-signals-card` — added `border-color` hover state (card shell now responds to hover)
- `.oktv-signals-card__creator` — promoted from `--ok-text-muted` to `--ok-text`, `font-size: 0.82rem` (WHO is more visible)
- `.ok-featured-creator` — horizontal card with `border-left: 3px solid var(--ok-accent)` (pillar modifier overrides color)
- `.ok-recommended-next` — compact related content row; horizontal scroll on mobile
- `.ok-watch-nav` — prev/next nav bar with direction labels and post titles
- `.ok-creator-page__bio` — bio text treatment for new `show_bio` section

**Section T — Search Experience** (~80 rules)

All rules scoped to `body.search` or `.ok-search-*` classes.

- Dark-themed search form: `--ok-surface-raised` background, `--ok-signal` focus ring
- Search submit button: `--ok-accent` red, consistent with platform button treatment
- `.ok-search-filters` — filter tab foundation (pill buttons, active state, CSS-only)
- `body.search .beeteam368-video-card` — platform card treatment on search result cards
- `body.search article h2.entry-title a` — `--ok-signal` color on hover (platform-standard link treatment)
- `body.search .no-results` — centered empty state
- `.ok-search-empty` — reusable empty state component for operator embeds

**Section U — Quality Pass** (~40 rules)

- `*:focus-visible` — standardized `2px solid var(--ok-signal)` focus ring
- `img[loading="lazy"]` — `min-height: 1px` prevents layout shift
- `.ok-pill` — normalized padding on base class
- `.ok-curated-section__header::before` — height aligned to section title line-height
- `[aria-disabled="true"].ok-discuss-cta` — `opacity: 0.65` on coming-soon wrapper
- `.ok-btn--sm` — small button variant for featured creator CTA
- Hover transition additions on `.ok-watch-nav__title`, `.ok-recommended-next__title`, `.ok-creator-page__featured-title`

---

### Track C — Bootstrap

- Version: `1.3.0` → `1.4.0` (plugin header + `OKARCANA_VERSION` constant)
- No new `require_once` or `::init()` calls — all new shortcodes added to existing `OKArcana_Discovery::init()`

---

## Shortcode Reference (complete — v1.4, 15 total)

| Shortcode | Class | Summary |
|---|---|---|
| `[oktv_signals_latest]` | OKArcana_Signals | Signals feed (cards or list) |
| `[oktv_arcana_signals_latest]` | OKArcana_Signals | Arcana signals feed |
| `[oktv_creator_spotlight]` | OKArcana_Signals | Compact creator card |
| `[oktv_discuss_cta]` | OKArcana_Editorial | Community CTA pill |
| `[oktv_content_explainer]` | OKArcana_Editorial | Editorial context block |
| `[oktv_next_action]` | OKArcana_Editorial | Navigation action row |
| `[oktv_destination_hero]` | OKArcana_Destinations | Pillar destination hero |
| `[oktv_pillar_strip]` | OKArcana_Destinations | Platform navigation strip |
| `[oktv_platform_intro]` | OKArcana_Discovery | "What is OFFKILTER?" grid |
| `[oktv_curated_section]` | OKArcana_Discovery | Editorial curation section |
| `[oktv_creator_page]` | OKArcana_Discovery | Full creator page layout |
| `[oktv_discover_page]` | OKArcana_Discovery | Editorial Discover page |
| `[oktv_recommended_next]` | OKArcana_Discovery | Watch Next related content |
| `[oktv_watch_nav]` | OKArcana_Discovery | Playlist prev/next nav |
| `[oktv_featured_creator]` | OKArcana_Discovery | Editorial creator callout |

---

## Operator Runbook (v1.4 additions)

All prior v1.0–v1.3 tasks remain applicable.

### Step 1 — Create the Discover page
**Where:** WP Admin > Pages > Add New
**Title:** Discover
**Content:**
```
[oktv_discover_page]
```
Set permalink to `/discover/` (or redirect existing `/trending/` to this page).

### Step 2 — Rename Trending in navigation
**Where:** WP Admin > Appearance > Menus
**Change:** Rename "Trending" → "Discover", update URL to `/discover/`
**Goal:** No visitor sees the word "Trending" — that is an internal algorithmic concept.

### Step 3 — Video detail: embed recommended next
**Where:** Elementor single video template (or custom page template for `single-vidmov_video.php`)
**Embed below player:**
```
[oktv_recommended_next]
```
No attributes needed — auto-detects current post.

### Step 4 — Creator channel pages: add bio + videos sections
**Where:** Elementor channel template or individual creator pages
```
[oktv_creator_page user_id="87" show_bio="1" show_signals="1" show_videos="1" show_discuss_cta="1"]
```

### Step 5 — Featured creator callout on Signals or Arcana archive
**Where:** Elementor sidebar or above-fold section on pillar archive pages:
```
[oktv_featured_creator user_id="87" body="Detroit's most trusted Arcana creator." cta_label="Watch their readings" pillar="arcana"]
```

### Step 6 — Watch nav for playlist content
**Where:** Elementor single video template, when embedding a playlist series.
**Identify the playlist post IDs** (order matters), then embed:
```
[oktv_watch_nav playlist_ids="101,102,103,104,105"]
```
Current video is auto-detected. Renders Previous/Next with post titles.

### Step 7 — Search page verify
**Where:** Visit `/?s=test` as logged-out user
**Check:**
- Search field has dark background (`--ok-surface-raised`)
- Focus ring is signal-blue
- Submit button is red
- Result cards have platform card treatment
- Empty state ("nothing found") is not plain WordPress default

### Step 8 — Deploy and validate
```bash
./scripts/deploy_offkilter_arcana.sh

# Validate version:
curl -s https://www.offkilter.tv/ | grep 'ver=1.4'

# Spot checks:
# - [oktv_discover_page] on /discover/
# - [oktv_recommended_next] on any video detail page
# - [oktv_watch_nav playlist_ids="..."] with current video ID in list
# - [oktv_featured_creator user_id="87" pillar="arcana"] — purple left border
# - [oktv_creator_page user_id="87" show_bio="1"] — bio section appears
# - Search: /?s=arcana — dark form, platform cards
```

---

## SidebarChat Integration Recommendations

All CSS foundations are in place since v1.1. Activating SidebarChat requires:

1. **Choose a provider**: Stream.io (most robust, has WP SDK) or Crisp (simpler, embeds via JS snippet). Neither requires plugin modifications — both inject via Elementor HTML widget or `wp_footer` hook.

2. **Update `[oktv_discuss_cta]` shortcodes**: Change `coming_soon="1"` to `coming_soon="0"` with the actual chat href. Example: `href="/community/#sidebarchat"`.

3. **CSS already ready**: `.ok-discuss-slot`, `.ok-discuss-cta`, `.ok-discuss-cta--coming-soon` are all in Section I. Remove `coming_soon` treatment by updating shortcode attributes — no CSS changes needed.

4. **Creator pages**: All `[oktv_creator_page]` embeds with `show_discuss_cta="1"` will automatically activate once the CTA href is live.

5. **Discover page**: The discussions section in `[oktv_discover_page]` already renders a live CTA (not coming-soon) — it points to `/community/`. Update `discussions_label` and the CTA target once Stream/Crisp is configured.

---

## Page Score Deltas (v1.4)

| Page | v1.3 Score | v1.4 Score | Key lift |
|---|---|---|---|
| Homepage | 8/10 | 8/10 | (CSS quality pass) |
| Signals Archive | 8.5/10 | 8.5/10 | (no changes) |
| Arcana Archive | 8.5/10 | 8.5/10 | (no changes) |
| Discover/Trending | 6/10 | 8/10 | `[oktv_discover_page]` (post-ops) |
| Creator Directory | 6.5/10 | 7/10 | `[oktv_featured_creator]` available |
| Creator Channel | 7.5–8.5/10 | 8–9/10 | `show_bio`, `show_videos`, Watch Nav |
| Video Detail | 8/10 | 8.5/10 | `[oktv_recommended_next]` + `[oktv_watch_nav]` |
| Community | 7/10 | 7/10 | (no changes) |
| Search | 4.5/10 | 6.5/10 | CSS Section T — dark form, card treatment |
| Archives | 6.5/10 | 6.5/10 | (no changes) |
| Auth Pages | 6.5/10 | 6.5/10 | (no changes) |

**Platform average (post-v1.4 ops):** ~7.8/10 → ~8.2/10

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
| Platform v1.4 | 1.4.0 | ~7.8/10 | Productization: discover, recommended next, search CSS |
| Post-embed target | 1.4.0 + ops | ~8.2/10 | Operator deploys discover page, video nav, search verify |
| Target v1.5 | — | ~8.8/10 | Arcana template, VidMov card override, SidebarChat |
| Target v2.0 | — | ~9.0/10 | Full custom templates, creator program, live chat |

---

## Top 10 for v1.5

1. **Arcana single post template** — `arcana_entry` needs Elementor single with gem icon header, purple accent, above-fold disclaimer, related readings. Biggest remaining quality gap.

2. **Operator: deploy Discover page** — `[oktv_discover_page]` shortcode ready, rename Trending in nav. Est. 20 minutes, +2.0 for that surface.

3. **Operator: deploy `[oktv_recommended_next]` on video detail** — Adds "Watch Next" to every video. 10 minutes in Elementor single template.

4. **VidMov card template child-theme override** — Apply OFFKILTER pill treatments (`ok-pill--signal`, `ok-pill--arcana`) to video cards everywhere. Requires one child-theme template file.

5. **SidebarChat activation** — All PHP, CSS, and CTA stubs ready. Provider selection + JS embed is the remaining work.

6. **Search results template** — CSS Section T improved search appearance. A full `search.php` override in child theme would allow content-type grouping and "nothing here yet" branded empty state.

7. **`[oktv_featured_creator]` operator deploy** — Ready since v1.4. Needs operator to identify top 3–5 creators and embed on Arcana/Signals destination pages.

8. **ARMember auth heading rename** — "Register" → "Join OFFKILTER", "Login" → "Sign In". ARMember admin panel task.

9. **Duplicate auth route cleanup** — `/login-2/`, `/register-2/` 301 redirects. Pure operator task.

10. **Footer redesign** — 2023 copyright, no editorial nav. Elementor footer template. 30 minutes.
