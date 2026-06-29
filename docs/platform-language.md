# OFFKILTER Platform Language

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Version: `1.2.0`

This document is the canonical reference for all terminology used across OFFKILTER.TV — in UI labels, shortcode defaults, admin settings, navigation, and editorial copy.

---

## Platform Vocabulary

| OFFKILTER term | What it means | Avoid using |
|---|---|---|
| **Signal** | Short-form clip ≤90 seconds. Fast observation, quick take. | "Video", "Short video", "Clip" (generic) |
| **Watch** | Longer-form content >90 seconds. | "Video" (generic), "Post" |
| **Arcana** | Curated tarot readings, premonitions, and outcomes from Arcana creators. | "Post", "Article", "Entry" |
| **Creator** | Content creator who publishes Signals, Watch, or Arcana content. | "Author", "User", "Member" |
| **Community** | The forum and discussion space (powered by wpForo). | "Forum", "Board", "Thread" (in nav) |
| **SidebarChat** | Coming live chat and discussion feature. | "Chat", "Comments", "Live" |
| **Premonitions** | Arcana sub-type: a reading that looks forward. | "Category" |
| **Outcomes** | Arcana sub-type: a reading that reflects back. | "Category" |
| **Signals Rail** | A curated shortcode feed of Signal clips. | "Video list", "Recent posts" |
| **Explore** | Discovery feed — algorithmic trending content. | "Trending" (in editorial copy, OK in URL) |

---

## Pillar Hierarchy

OFFKILTER has four platform pillars. Every navigation, shortcode, and destination page maps to one of these:

| Pillar | Icon | Accent color | Canonical route |
|---|---|---|---|
| **Signals** | `fas fa-bolt` | `--ok-signal` (#00C2FF) | `/video-category/signals/` |
| **Arcana** | `fas fa-gem` | `--ok-arcana` (#8B5CF6) | `/video-category/arcana/` |
| **Creators** | `fas fa-user-group` | `--ok-accent` (#E50914) | `/member-list/` |
| **Community** | `fas fa-comments` | `--ok-community` (#22C55E) | `/community/` |

Additional navigation icons:
- Watch / Play: `fas fa-play-circle`
- Explore: `fas fa-compass`

---

## Canonical Routes

| Destination | URL |
|---|---|
| Homepage | `/` |
| Signals archive | `/video-category/signals/` |
| Arcana archive | `/video-category/arcana/` |
| Premonitions archive | `/video-category/premonitions/` |
| Outcomes archive | `/video-category/outcomes/` |
| Creator directory | `/member-list/` |
| Community | `/community/` |
| Explore / Trending | `/trending/` |
| Login | `/login-2/` (page ID 7697) |
| Register | `/register/` (page ID 7695) |

---

## CSS Design Tokens → Platform Meaning

| Token | Value | Use |
|---|---|---|
| `--ok-accent` | `#E50914` | Primary brand, Creators pillar, error states |
| `--ok-signal` | `#00C2FF` | Signals pillar |
| `--ok-arcana` | `#8B5CF6` | Arcana pillar |
| `--ok-community` | `#22C55E` | Community pillar, success states |
| `--ok-text` | `#F3F6FB` | Body text |
| `--ok-text-muted` | `#B8C1D1` | Secondary / metadata text (WCAG AA: 7.5:1) |
| `--ok-text-faint` | `#5A6478` | Decorative metadata only — NOT for interactive or informational text |
| `--ok-bg` | `#07090D` | Page background |
| `--ok-surface` | `#11151D` | Card/panel backgrounds |
| `--ok-surface-raised` | `#161B26` | Hover surfaces, nested panels |

---

## Shortcode Language Reference

| Shortcode | Default title/label | Notes |
|---|---|---|
| `[oktv_signals_latest]` | "Signals" | Removed emoji (⚡ deprecated) |
| `[oktv_arcana_signals_latest]` | "Latest Signals" | Removed emoji (🔮 deprecated) |
| `[oktv_creator_spotlight]` | "Latest" (section label) | Stat label: "clips" (was "videos") |
| `[oktv_discuss_cta]` | "Discuss" | Coming-soon: "Community opens soon" |
| `[oktv_content_explainer type="signals"]` | "Fast clips. Under 90 seconds." | — |
| `[oktv_content_explainer type="arcana"]` | "Readings. Premonitions. Outcomes." | — |
| `[oktv_content_explainer type="community"]` | "The conversation lives here." | — |
| `[oktv_content_explainer type="creators"]` | "The people behind OFFKILTER." | — |
| `[oktv_next_action actions="signals"]` | "Signals" button | Routes to `/video-category/signals/` |
| `[oktv_destination_hero pillar="signals"]` | "Every signal deserves attention." | Full hero block for Signals page |
| `[oktv_pillar_strip active="signals"]` | — | 4-chip platform navigation strip |

---

## Admin Panel Language

| Old label | New label | File |
|---|---|---|
| "Imported Videos" | "Imported Content" | `class-okarcana-admin.php` |
| "Videos (90+s)" | "Watch (90+s)" | `class-okarcana-admin.php` |
| "Automatically classify imported vidmov_video content as Signal (0-90s) or Video (90+s)" | "Automatically classify imported content as Signal (≤90s) or Watch (>90s)" | `class-okarcana-admin.php` |
| Queue column "Post" | Queue column "Content" | `class-okarcana-admin.php` |
| "Classifies imported vidmov_video posts..." | "Classifies imported content..." | `class-okarcana-admin.php` |

---

## Editorial Voice

**What OFFKILTER says:**
- "Every signal deserves attention."
- "The intuitive layer of OFFKILTER."
- "Independent creators. Real communities."
- "Fast clips, fast observations."
- "Readings, premonitions, and outcomes."
- "The conversation starts here."

**What OFFKILTER avoids:**
- Generic WordPress or VidMov terminology in user-facing copy
- "Watch this video" → "Watch this Signal" or "Watch this reading"
- "Posted by [author]" → "From [Creator name]"
- "0 comments" → use Community framing or omit entirely
- "Filed under" or "Tagged with" → hide or omit

---

## Post Types (internal — not user-facing)

These are WordPress post type slugs. They appear in code and meta keys, not in UI.

| Internal type | User-facing equivalent |
|---|---|
| `vidmov_video` | Signal or Watch (depending on duration) |
| `arcana_entry` | Arcana reading |

---

## Versioning

This document tracks language decisions made in or after v1.2.0. For pre-v1.2 decisions, see `docs/platform-v1_1-product-review.md` and `docs/vidmov-decontamination.md`.
