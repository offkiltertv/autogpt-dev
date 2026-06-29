# OFFKILTER Public Surface Audit v1

Date: 2026-06-29
Auditor: Platform v1.4 sprint
Branch: `feature/arcana-plugin`
Scope: All public pages visible to a logged-out visitor.

---

## Audit Criteria

Every public page was evaluated for:
1. **Developer language** — post types, class names, plugin terminology
2. **Admin leakage** — internal IDs, debug output, queue terminology
3. **Plugin language** — "WP", "widget", "taxonomy", "shortcode", "vidmov", "beeteam368"
4. **Duplicated metadata** — view counts, tags, categories shown twice
5. **Confusing labels** — inherited WordPress/VidMov labels with no OFFKILTER meaning
6. **Implementation exposure** — URLs that reveal the underlying tech stack

---

## Canonical "Never Say" List

These words and phrases should never appear on OFFKILTER's public pages.

| Never say | Say instead |
|---|---|
| Video | Signal (≤90s) or Watch (>90s) |
| Post | Content, Signal, Watch, or Arcana reading |
| Author | Creator |
| Tag | (suppress entirely) |
| Category | Pillar (Signals, Arcana, Creators, Community) |
| Archive | Discovery, Browse, or Pillar page |
| Trending | Discover (editorial, not algorithmic) |
| Forum | Community |
| Topic | Discussion |
| Register | Join OFFKILTER |
| User | Creator or Member |
| Dashboard | (suppress entirely — admin term) |
| Widget | (suppress entirely) |
| Plugin | (suppress entirely) |
| Import / Imported | (suppress entirely) |
| Queue | (suppress entirely) |
| vidmov_video | (suppress entirely) |
| beeteam368 | (suppress entirely) |
| bt368 | (suppress entirely) |

---

## Per-Page Inventory

### Homepage (`/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| No "What is OFFKILTER?" orientation block | High | **Open — operator task** | Embed `[oktv_platform_intro]` in Elementor |
| Pillar strip not present | Medium | **Open — operator task** | Embed `[oktv_pillar_strip]` below hero |
| Footer shows `© 2023 OFFKILTER.TV` | Medium | **Open — operator task** | Edit Elementor footer template |
| Social share bar on homepage posts (suppressed) | Low | **Resolved** | CSS Section: `body.home .heateor_sss_*` |

---

### Signals Archive (`/video-category/signals/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Page title says "Videos" or "Signals" with no pillar framing | High | **Open — operator task** | Embed `[oktv_destination_hero pillar="signals"]` + `[oktv_pillar_strip active="signals"]` |
| Purely chronological feed — no editorial curation | Medium | **Open — operator task** | Embed `[oktv_curated_section]` or `[oktv_discover_page]` |
| VidMov pagination shows "Older Posts / Newer Posts" | Low | **Open — CSS/theme** | Requires child theme template to override |
| Archive URL `/video-category/signals/` exposes `video-category` taxonomy slug | Low | **Open — operator task** | Change taxonomy rewrite slug to `category` or `pillar` in WP Settings |

---

### Arcana Archive (`/video-category/arcana/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Same issues as Signals Archive | High | **Open** | Same fix pattern |
| Single Arcana posts use generic WordPress post template | High | **Open** | Requires Elementor single template scoped to `arcana_entry` post type |
| No pillar color treatment on Arcana-specific posts | Medium | **Open** | Elementor template + Section Q CSS already in place for destination hero |

---

### Video Detail (`/video/[slug]/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Tag cloud below player (suppressed) | N/A | **Resolved** | CSS Section O |
| Social share block below player (suppressed) | N/A | **Resolved** | CSS Section O |
| Download button (suppressed) | N/A | **Resolved** | CSS Section P |
| Report video button (suppressed) | N/A | **Resolved** | CSS Section P |
| Related videos section uses VidMov default treatment | Medium | **Open** | `[oktv_recommended_next]` now available (v1.4); embed in Elementor single template |
| Creator byline shows "Published by [name]" (WordPress phrasing) | Low | **Open** | Requires child theme or Elementor dynamic tag override |
| View count shows generic number (no label) | Low | **Open** | CSS treatment in place (Section K); label requires VidMov template override |
| No playlist navigation | Medium | **Open** | `[oktv_watch_nav]` now available (v1.4); embed when playlist content exists |

---

### Creator Channel Pages (`/channel/[id]/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| VidMov channel tabs ("Videos", "About", "Discussion") use WordPress terminology | High | **Open** | CSS overrides for tab labels require child theme template or Elementor |
| Tab label "Videos" should say "Signals" or "Watch" | High | **Open** | VidMov template override |
| Creator page uses compact spotlight, not full layout | Medium | **Open — operator task** | Embed `[oktv_creator_page]` in Elementor channel template |
| Bio section not shown by default | Low | **Open — operator task** | Add `show_bio="1"` to `[oktv_creator_page]` embed |

---

### Creator Directory (`/member-list/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Destination hero absent | Medium | **Open — operator task** | Embed `[oktv_destination_hero pillar="creators"]` |
| Hash-like handles visible for creators without custom handles | Low | **Open — operator task** | Operator needs to set display name / username for each creator |
| VidMov grid uses generic "Member" terminology | Medium | **Open** | Requires VidMov member list template override |
| "Featured Creator" callout not present | Low | **Open — operator task** | Embed `[oktv_featured_creator]` (v1.4) for top creators |

---

### Discover / Trending Page

| Item | Severity | Status | Resolution |
|---|---|---|---|
| No editorial Discover page exists | High | **Open — operator task** | Create new WP page "Discover", embed `[oktv_discover_page]` |
| Existing Trending page is generic VidMov output | High | **Open — operator task** | Replace or redirect `/trending/` to the new Discover page |
| Nav item labeled "Trending" reveals algorithmic implementation | Medium | **Open — operator task** | WP Admin > Menus — rename "Trending" → "Discover" |

---

### Search (`/?s=[query]` or `/search/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Search form inherits VidMov/WordPress default styling | High | **Open — partially** | CSS Section T (v1.4) applies dark theme to form + results |
| Page title shows "Search Results for: [query]" in default WP heading | Medium | **Open** | CSS Section T scopes `.page-title` styling; text requires `wp_title` filter or template override |
| Result cards use VidMov default layout | Medium | **Open — partially** | CSS Section T scopes `.beeteam368-video-card` to search context |
| No content-type filtering | High | **Open** | CSS Section T provides filter tab foundations; operator wires up links |
| Empty state is WordPress default | Medium | **Open — partially** | CSS Section T styles `body.search .no-results`; text requires template override |
| Score: 4.5/10 → 6.5/10 after v1.4 CSS | — | **Partial improvement** | Full redesign requires `pre_get_posts` filter + search results template |

---

### Community (`/community/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Forum categories still use WordPress/wpForo default names | Medium | **Open — operator task** | Rename forum categories in wpForo admin to OFFKILTER pillar names |
| "Reply" button is plain wpForo text (no OFFKILTER treatment) | Low | **Resolved** | CSS Section H: `.wpforo-btn` |
| SidebarChat is coming-soon | High | **Design constraint** | CSS stub in place; requires Stream.io or Crisp integration |
| "Forum" label in breadcrumb | Low | **Open** | CSS or wpForo admin rename |

---

### Authentication Pages (`/register/`, `/login-2/`)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Duplicate routes exist (`/login-2/`, `/register-2/`) | High | **Open — operator task** | 301 redirects + delete duplicate pages |
| Login page says "Login" — not branded | Medium | **Open — operator task** | ARMember form settings → rename heading to "Sign In to OFFKILTER" |
| Register page says "Register" — not branded | Medium | **Open — operator task** | ARMember form settings → rename heading to "Join OFFKILTER" |
| ARMember form field labels use generic WordPress defaults | Low | **Resolved** | CSS Section G overrides form field appearance |

---

### Archives (Category, Tag, Date)

| Item | Severity | Status | Resolution |
|---|---|---|---|
| Tag archives are publicly accessible but serve no OFFKILTER purpose | Medium | **Open — operator task** | Noindex via Yoast or redirect `/tag/` to search |
| Date archives accessible (`/2024/01/`) | Low | **Open — operator task** | Noindex or redirect |
| Category archive header says "Category: [name]" | Medium | **Open — partially** | CSS Section C provides `.archive-header` treatment; text requires template override or `archive_title` filter |

---

## Priority Matrix

| Priority | Item | Effort | Impact |
|---|---|---|---|
| P0 | Discover page (rename Trending, embed `[oktv_discover_page]`) | 15 min | +1.5 trending score |
| P0 | Homepage: embed `[oktv_platform_intro]` + `[oktv_pillar_strip]` | 10 min | +0.5 homepage score |
| P0 | Pillar destination heroes on Signals, Arcana, Creators, Community | 20 min | +1.0 per pillar page |
| P1 | Video detail: embed `[oktv_recommended_next]` | 10 min | +0.5 video detail score |
| P1 | Creator channels: embed `[oktv_creator_page]` for top 5 creators | 30 min | +1.5 creator score |
| P1 | Search page: CSS Section T now in place; operator test + verify | 5 min | +2.0 search score |
| P1 | Nav menu: rename Trending → Discover, reorder pillars to top | 5 min | +0.5 all pages |
| P2 | Footer redesign in Elementor | 30 min | +0.5 all pages |
| P2 | Auth page heading rename in ARMember admin | 10 min | +0.5 auth pages |
| P2 | Duplicate auth route cleanup (301 redirects) | 15 min | Clean URLs |
| P3 | wpForo category renaming | 20 min | +0.3 community score |
| P3 | Tag/date archive noindex | 10 min | SEO hygiene |
| P4 | VidMov card template child-theme override | 2–4h | +1.0 card quality everywhere |
| P4 | Arcana single post Elementor template | 2–3h | +1.5 arcana score |

---

## What Plugin CSS Cannot Fix (Requires Operator / Child Theme)

These items are fully known. Plugin-side work is complete. Remaining work is operator tasks or child-theme template overrides that cannot be done via `wp_enqueue_scripts`.

1. **Video detail "Published by [name]"** — Requires `the_author_posts_link` filter or Elementor dynamic tag override
2. **Channel tab labels ("Videos", "About")** — VidMov renders these server-side in a template file
3. **Pagination text ("Older Posts", "Newer Posts")** — VidMov template
4. **Page title text on archive pages** — `archive_title` filter or template override
5. **Category/tag archive breadcrumb text** — VidMov breadcrumb template
6. **SidebarChat** — Requires external chat service activation (Stream.io / Crisp)
7. **ARMember form heading text** — ARMember admin settings
8. **Footer copyright year** — Elementor footer template
9. **wpForo category names** — wpForo admin panel
10. **Search result page title text** — `pre_get_posts` + template partial override
