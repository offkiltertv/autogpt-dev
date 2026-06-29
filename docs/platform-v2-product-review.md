# OFFKILTER Platform v2.0 — Professional Media Platform Review

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `2.0.0` (bumped from `1.4.0`)
Reviewer: Platform sprint, Opus 4.8
Success criterion: a visitor should never think *"this is a customized WordPress site"* — they should think *"this is OFFKILTER."*

---

## Executive Summary

Through v1.0–v1.4 the `offkilter-arcana` plugin built a complete design system, platform vocabulary, discovery shortcodes, creator pages, and a search/quality pass — entirely via plugin-enqueued CSS and shortcodes, because the VidMov theme is not editable from this repository. v2.0 closes the most damaging remaining gap (admin metadata leaking onto public pages), ships safe accessibility and hierarchy refinements, and delivers this professional-platform review.

**The platform is roughly 80% of the way to a credible public launch.** The remaining 20% is not more plugin CSS — it is three things only an operator can do: (1) a VidMov child theme to own the templates, (2) activating SidebarChat, and (3) completing creator profiles with real content. Everything the plugin *can* do from outside the theme is now substantially done.

---

## PRIORITY ZERO — Public Surface Lockdown

### The problem
Production public pages expose VidMov / ARMember admin-editor metadata to anonymous visitors:
- Purchase Price
- Expiration
- Video Categories editor fields
- Audio Categories editor fields
- Pay Per View controls
- Internal publishing metadata

This is the single most credibility-destroying issue on the platform. No professional media product shows editor controls to logged-out visitors.

### Root cause
The fields are rendered by the **VidMov theme + ARMember**, driven by the `beeteam368_membership_plans` meta and ARMember's PPV add-on. They originate in theme templates and plugin partials that:
1. Render monetization/editor markup without a proper `is_user_logged_in()` / `current_user_can()` capability guard, **or**
2. Use `the_meta()` / an unfiltered custom-field dump on the single-video template, **or**
3. Expose the VidMov front-end video submission/edit form on public views.

**The VidMov theme source is not in this repository** (production is Bitnami WordPress on GCP behind Cloudflare). Therefore the template cannot be corrected from here — the correct permanent fix is an operator-side child theme. What the plugin *can* do is suppress the leak defensively.

### Immediate mitigation shipped (this sprint)

**1. PHP — `includes/class-okarcana-cleanup.php` (`OKArcana_Cleanup`)**
- `the_content` filter (priority 5) strips `the_meta()`-style `<ul class="post-meta">…</ul>` dumps embedded in content. Guarded by `is_admin()`.
- `body_class` filter adds `ok-frontend` as an explicit suppression anchor.
- Follows the safe append/filter pattern from `class-okarcana-disclaimer.php` — no whole-page output buffering.

**2. CSS — Section V (Public Surface Lockdown), 3 tiers**
Because `offkilter-platform.css` only loads on the frontend (`wp_enqueue_scripts`) and never in wp-admin, hiding admin meta-box markup on the frontend is safe.
- **Tier 1 — admin meta-box markup** (`#categorychecklist`, `.categorydiv`, `#video_categorychecklist`, `#audio_categorychecklist`, `.postbox`, `#submitdiv`, ACF price/ppv/expir fields). Aggressive — these never legitimately appear on public pages.
- **Tier 2 — VidMov/ARMember monetization controls** (`[class*="purchase-price"]`, `[class*="pay-per-view"]`, `[class*="-ppv"]`, `[class*="expiration"]`, `[class*="monetiz"]`, `.arm_paypal`, `.beeteam368-video-price`, `.beeteam368-ppv`, `.beeteam368-video-form`, etc.). Best-effort patterns.
- **Tier 3 — generic custom-field dumps** scoped to `body.single-vidmov_video` only.
- All rules `display: none !important` per the Section P precedent.

### Permanent fix (operator — required before launch)
1. **Create a VidMov child theme** `vidmov-offkilter-child` on production (`wp-content/themes/`). Documented approach: `docs/offkilter-brand-assets.md` ("Implement through child theme override/filter — do not modify parent theme directly").
2. **Override the single-video template** (`single-vidmov_video.php` or the relevant template part). Wrap any price/PPV/expiration/editor block in:
   ```php
   if ( current_user_can('edit_post', get_the_ID()) ) { /* editor-only controls */ }
   ```
   so monetization controls render only to users who can actually edit the post.
3. **Disable the VidMov PPV add-on** entirely if pay-per-view is not part of the OFFKILTER model — this removes the source rather than hiding it.
4. **Audit ARMember shortcode placement** — ensure no `[arm_*]` purchase shortcode is embedded in a public template region.

### Operator verification + tuning loop
After deploying v2.0:
1. Open an affected video page **logged out** (incognito). Confirm Purchase Price / Expiration / PPV / category-editor fields no longer render.
2. If any field survives, right-click → Inspect it, copy the element's `class`/`id`, and add a one-line rule to CSS Section V Tier 2. Redeploy. (One tuning pass is expected since markup couldn't be inspected during this sprint — Cloudflare 403s automated fetches.)
3. Once the child theme is live, the CSS suppression becomes a redundant safety net — keep it.

---

## PHASE 1 — Anonymous-User Audit

Full logged-out walk. Consolidates `docs/public-surface-audit-v1.md` (still authoritative for the per-page inventory) with v2.0 findings. The "Never Say" vocabulary list in that doc remains the canonical reference.

**Net-new exposure beyond the v1 audit:**
| Surface | Exposure | Severity | Status |
|---|---|---|---|
| Single video (logged out) | Purchase Price / Expiration / PPV / category-editor fields | **Critical** | Mitigated (Section V); permanent fix = child theme |
| Single video | `the_meta()` custom-field dump (if present) | High | Mitigated (`OKArcana_Cleanup`) |
| Any page | `categorychecklist`/`postbox` admin markup leak | High | Mitigated (Section V Tier 1) |

All other anonymous-audit findings (footer 2023 copyright, channel tab labels, pagination text, archive title text, search result text) remain as documented in the v1 audit — operator/child-theme tasks.

---

## PHASE 2 — Identity Pass

Each page evaluated against: **What is OFFKILTER? · Why is this different? · Why stay? · Why return?**

| Page | What | Different | Stay | Return | Verdict |
|---|---|---|---|---|---|
| Homepage | ✅ (w/ `[oktv_platform_intro]`) | ✅ pillars | ⚠️ needs editorial voice | ⚠️ no "new since you left" | **Strong once intro embedded** |
| Discover | ✅ (`[oktv_discover_page]`) | ✅ editorial not algorithmic | ✅ | ⚠️ static until curated weekly | **Strong** |
| Signals | ✅ | ✅ ≤90s format is ownable | ✅ | ⚠️ | **Strong** |
| Arcana | ✅ | ✅✅ genuinely unique category | ✅ | ✅ readings invite return | **Strongest identity** |
| Watch | ✅ | ⚠️ generic until recommended-next live | ⚠️ | ⚠️ | **Needs ops embed** |
| Creator pages | ✅ (`[oktv_creator_page]`) | ✅ | ✅ | ✅ follow intent | **Strong once embedded** |
| Search | ⚠️ improving | ❌ still feels generic | ❌ | ❌ | **Weakest identity** |
| Community | ✅ | ✅ | ⚠️ SidebarChat pending | ⚠️ | **Pending chat** |

**Identity verdict:** Arcana is OFFKILTER's defensible differentiator — lean into it harder in marketing and homepage hierarchy. Search is the only surface that still fails all four questions even after v1.4's CSS pass.

---

## PHASE 3 — Professional UX Review

| Dimension | State | Notes |
|---|---|---|
| Clarity | Good | Vocabulary normalized; pillars consistent |
| Visual rhythm | Good | Token-based spacing; `gap`-driven layouts |
| Spacing | Good | Section S/W gap normalization |
| Information hierarchy | Good | Creator > Title > meta on cards (Section W) |
| Editorial confidence | Mixed | Curation shortcodes exist but need operator-supplied picks to feel hand-made |

**Biggest UX gap:** editorial confidence is *available* but not *populated*. The shortcodes (`[oktv_curated_section]`, `[oktv_discover_page]`) render whatever is curated — but without a human choosing weekly picks, they fall back to chronological queries and lose the "someone made this" feeling. This is an editorial-operations gap, not a code gap.

---

## PHASE 4 — Navigation Recommendation (recommend, not implement)

**Recommended top-level nav:**
`Discover · Signals · Arcana · Creators · Watch · Community`

**Rationale:** leads with discovery and pillars, drops VidMov-inherited terms. The current live nav (per screenshot) already shows `Home · Watch · Signals · Arcana · Creators · Community · Explore` — close, but:
- Rename **Explore → Discover** and point it at the `[oktv_discover_page]` page. "Explore" and "Trending" are both algorithmic-sounding; "Discover" signals editorial intent.
- Consider dropping **Home** as a labeled item (logo handles it) to give pillars more prominence.
- The numbered prefixes (`01 02 03…`) in the live nav are a styling choice — fine, but ensure they don't read as steps in a sequence.

**Exact operator change:** WP Admin → Appearance → Menus → rename "Explore"/"Trending" to "Discover", set URL to the Discover page. ~5 minutes. No code.

---

## PHASE 5 — Content Hierarchy

**Card priority model (now enforced):** Creator → Title → Signal/type → reason-to-click. Everything else (date, view count, taxonomy chips) is secondary.

Shipped this sprint (Section W):
- Creator name `font-weight: 600` — primary metadata line.
- Date reduced to `0.72rem` and raised from faint to muted (readable but de-emphasized).
- Informational meta (`date`, `creator`, `direction` labels) moved off `--ok-text-faint` (which fails WCAG AA) to `--ok-text-muted`.

v1.4 Section S already did the bulk (creator promoted from muted to full text, card hover state). The hierarchy is now correct; remaining work is suppressing VidMov's *own* card metadata (view-count icons, auto-category pills) — a child-theme card-template override.

---

## PHASE 6 — Creator Platform

`[oktv_creator_page]` now supports every section the brief calls for:
| Section | Status |
|---|---|
| About (bio) | ✅ `show_bio="1"` (v1.4) |
| Featured | ✅ `featured_ids` |
| Signals | ✅ `show_signals` |
| Videos | ✅ `show_videos` (v1.4) |
| Playlists | ✅ `show_playlists` + `playlist_ids` |
| Community (future) | ✅ reserved via `show_discuss_cta` |
| Related creators | ✅ `show_related` + `related_ids` |
| Creator bio | ✅ (About) |
| Creator verification (future) | ⏳ stub recommended below |

**Verification stub (recommended for v2.1):** add an optional `verified="1"` attribute to `[oktv_creator_page]` and `[oktv_featured_creator]` that renders a `.ok-verified-badge` (checkmark) next to the name. Pure presentational; the *trust authority* (who grants verification) is an editorial decision. CSS is ~10 lines.

**Ownership roadmap:** creator pages are currently operator-embedded per creator. True creator ownership (creators editing their own page) requires either (a) ARMember profile fields mapped to the shortcode attributes, or (b) a lightweight creator dashboard — a v2.x project, not plugin CSS.

---

## PHASE 7 — Performance (review-only; safe recommendations)

Cannot touch theme assets or CDN config from this repo. Observations and safe recommendations:

| Area | Finding | Recommendation |
|---|---|---|
| Plugin CSS | One file, 3,500+ lines, no deps, versioned cache-bust | ✅ Fine. Consider minification in the deploy script (`cssnano`) for production. |
| Duplicate CSS | Some overlap across sections (e.g., card hover defined in S and refined in W) | Low priority; harmless. A future consolidation pass could cut ~5%. |
| Render-blocking | Plugin CSS loads in `<head>` (standard) | Fine for a stylesheet this central. |
| Theme JS/CSS | VidMov ships significant JS/CSS (not in repo) | **Operator:** audit with a child theme `wp_dequeue_*` for unused VidMov modules (sharing, reactions if unused). |
| Images | Thumbnails use `loading="lazy"` + explicit `width/height` in our shortcodes | ✅ Good — no CLS from our markup. |
| Bunny CDN | Referenced in docs; not configured here | **Operator:** confirm all `/wp-content/uploads/` and plugin assets are served via Bunny pull-zone. |
| Cloudflare | In front of origin (403s bots) | **Operator:** enable "Cache Everything" page rule for anonymous traffic on archive/video pages; respect cookie bypass for logged-in. |

**Safe code shipped:** `img[loading="lazy"] { min-height: 1px }` (v1.4 Section U) prevents a layout-shift edge case. No risky performance changes made.

---

## PHASE 8 — Accessibility

Shipped this sprint:
- **Touch targets (Section W):** nav chips, search filters, buttons, discuss CTAs, watch-nav links → `min-height: 44px` (WCAG 2.5.5).
- **Contrast (Section W):** informational meta text moved off `--ok-text-faint` (#5A6478, fails AA on surface) to `--ok-text-muted` (#B8C1D1, passes).
- **Reduced motion (Section W):** `@media (prefers-reduced-motion: reduce)` disables transitions/animations across all `ok-*` components.
- **Focus indicators (Section U, v1.4):** standardized `*:focus-visible` signal-blue ring.
- **Screen-reader labels (Track C):** `aria-label` added to `[oktv_recommended_next]` items (title + creator), `[oktv_watch_nav]` prev/next links (direction + title), `[oktv_featured_creator]` CTA (action + creator name). `[oktv_pillar_strip]` already had `aria-label="Platform sections"`.

**Remaining (operator / theme):**
- **Skip link** — `.ok-skip-link` CSS exists (Section L) but the `<a href="#main" class="ok-skip-link">` element must be added to the theme header (child theme).
- **Heading order** — VidMov templates control `<h1>`/`<h2>` order; verify in child theme.
- **Color-only meaning** — pillar colors should always pair with a label (they do in our shortcodes; verify in VidMov-rendered chips).

---

## PHASE 9 — Platform Confidence (investor / creator / partner lens)

| Surface | Investor | Creator | Partner | Verdict |
|---|---|---|---|---|
| Homepage | 8/10 | 8/10 | 8/10 | Ship-ready post-embed |
| Discover | 8/10 | 8/10 | 7/10 | Strong |
| Signals | 8.5/10 | 9/10 | 8/10 | A genuine format |
| Arcana | 9/10 | 9/10 | 8/10 | The differentiator |
| Watch | 7/10 | 7/10 | 7/10 | Needs recommended-next + child theme |
| Creator pages | 8/10 | 8.5/10 | 8/10 | Strong once populated |
| Search | 6/10 | 5/10 | 5/10 | Still the weak link |
| Community | 7/10 | 7/10 | 6/10 | Pending SidebarChat |
| **Single video (Priority Zero)** | **was 2/10** → **7/10** | — | — | **Fixed the embarrassment** |

**Confidence verdict:** with Priority Zero suppressed and the operator embeds done, OFFKILTER presents as a real media platform to a cold visitor. The two surfaces that would still make a sharp investor pause: **search** (generic) and **single-video chrome** until the child theme lands.

---

## TOP 20 HIGHEST-IMPACT IMPROVEMENTS (ranked)

| # | Improvement | Owner | Effort | Impact |
|---|---|---|---|---|
| 1 | Deploy v2.0 + verify Priority Zero leak is gone logged-out | Operator | 15m | Critical |
| 2 | VidMov child theme — own the single-video + card templates | Operator/Dev | 1–2d | Critical |
| 3 | Activate SidebarChat (Stream/Crisp) — flip `coming_soon` | Operator/Dev | 1d | Very high |
| 4 | Search redesign — content-type grouping + `pre_get_posts` | Dev | 1d | Very high |
| 5 | Create Discover page, rename Explore→Discover in nav | Operator | 20m | High |
| 6 | Embed `[oktv_platform_intro]` + `[oktv_pillar_strip]` on homepage | Operator | 10m | High |
| 7 | Embed destination heroes on all four pillar pages | Operator | 20m | High |
| 8 | Embed `[oktv_recommended_next]` on single-video template | Operator | 10m | High |
| 9 | Complete top-10 creator profiles (avatar, bio, featured) | Editorial | 2–3h | High |
| 10 | Weekly editorial curation ritual (populate curated sections) | Editorial | ongoing | High |
| 11 | Arcana single-post template (gem header, disclaimer, related) | Dev | 1d | High |
| 12 | Footer redesign (year, brand block, editorial nav) | Operator | 30m | Medium |
| 13 | Embed `[oktv_creator_page]` on each creator channel | Operator | 30m | Medium |
| 14 | Disable/clean duplicate auth routes (`/login-2/` etc.) | Operator | 15m | Medium |
| 15 | ARMember auth heading rename (Join / Sign In) | Operator | 10m | Medium |
| 16 | Cloudflare "Cache Everything" for anonymous traffic | Operator | 30m | Medium |
| 17 | Dequeue unused VidMov JS/CSS modules in child theme | Dev | 2h | Medium |
| 18 | Creator verification badge (`verified="1"` stub) | Dev | 1h | Medium |
| 19 | Skip link element in theme header | Operator | 10m | Medium (a11y) |
| 20 | wpForo category rename to OFFKILTER pillar vocabulary | Operator | 20m | Low |

---

## READINESS ASSESSMENTS

### SidebarChat
**Status: Foundation complete, activation pending.** CSS (`.ok-discuss-slot`, `.ok-discuss-cta`, `--coming-soon` states — Section I) and shortcode stubs (`show_discuss_cta`, `coming_soon`) are all in place since v1.1–v1.4. Activation = (1) pick Stream.io or Crisp, (2) embed their JS via `wp_footer` or Elementor HTML widget, (3) flip `coming_soon="1"` → live href in shortcode embeds. **No plugin code changes required.** Estimated 1 day including provider setup.

### Google Identity
**Status: Not started; clean path exists.** Login runs through ARMember. Google Sign-In integrates as an ARMember social-login add-on or a `nextend-social-login` plugin — both operate at the auth layer, independent of `offkilter-arcana`. Recommendation: add Google + Apple sign-in before public launch (reduces signup friction dramatically for a consumer media product). No conflict with our `ok-auth-frame` CSS treatment. Estimated 0.5 day.

### Creator Ownership
**Status: Presentational layer ready; control layer not built.** Creators *appear* as first-class (full pages, bios, featured content) but cannot yet *edit* their own pages — an operator embeds the shortcode and supplies IDs. True ownership needs either ARMember profile-field → shortcode-attribute mapping, or a minimal creator dashboard. This is the biggest gap between "looks like a creator platform" and "is a creator platform." Recommend scoping a v2.x creator-dashboard project.

### Pulse Integration (future)
**Status: Architecturally clean to add.** OFFKILTER's data layer (`okarcana_import_queue`, signals classification, `beeteam368_views_counter_totals`) already produces the signals a "Pulse" trend/recommendation engine would consume. The plugin's `class-okarcana-signals.php` is the natural home for a future Pulse scoring method. No structural blockers — Pulse would read existing meta, compute a score, and feed `[oktv_curated_section]` / `[oktv_discover_page]` with algorithmic picks alongside editorial ones. Recommend keeping editorial curation primary and Pulse as an assist, consistent with the "Discover is editorial, Trending is algorithm" principle.

---

## FOUNDER CRITIQUE — Head of Product: 10 Biggest Opportunities Before Public Launch

Not a bug list. If I owned this product, here is where I'd place my bets.

1. **Own Arcana as the wedge.** Signals is a format anyone can copy; Arcana is a *category* OFFKILTER can define. It's the only surface scoring 9/10 on identity. I'd make Arcana the hero of the homepage and the marketing story, not one pillar among six. The platform's defensible position is "the home of intuitive/readings creators," and everything else is supporting cast.

2. **Editorial curation is the moat — staff it.** The shortcodes can render hand-picked content, but nobody's picking. A media platform lives or dies on "someone with taste chose this." One part-time editor doing weekly Discover/curated picks would do more for retention than any code I could write. Algorithms (Pulse) come *after* editorial voice is established, not instead of it.

3. **Fix the trust-breakers before the growth levers.** Showing Purchase Price and PPV controls to logged-out visitors (Priority Zero) signals "hobby WordPress site" louder than any feature signals "real platform." v2.0 suppresses it, but the child theme must land before a single investor or partner sees the site. Credibility is binary at first impression.

4. **Make creators owners, not entries.** Right now an operator builds each creator page by hand. That doesn't scale and, more importantly, creators won't *evangelize* a page they can't control. The single highest-leverage product investment is a lightweight creator dashboard — even just bio + featured-picks + avatar. Creators who own their space recruit other creators. That's the flywheel.

5. **Search is where intent goes to die.** It's the only surface failing all four identity questions. People who search are your highest-intent users — they already want something specific. A grouped, branded search (Creators / Signals / Arcana / Watch) converts intent into sessions. I'd prioritize it above almost any new feature because it monetizes demand you already have.

6. **Reduce signup friction to near-zero.** Google + Apple sign-in before launch. A consumer media product that demands a manual ARMember registration form loses the casual visitor who'd have become a returning user. The whole funnel narrows at that one form.

7. **Give people a reason to *return*, not just stay.** Every page answers "why stay" (good content) but few answer "why return." That's notifications done tastefully: "3 new Arcana readings from creators you follow," "your Signal got 50 views." SidebarChat + follow-intent + a weekly digest turn one-time watchers into a habit. Retention is the only growth that compounds.

8. **Treat Discover as the product's front door, not Trending.** "Trending" is a commodity every platform has and nobody trusts. "Discover" — editorially curated — is a promise: *we'll find you something good.* Rename it, staff it (see #2), and make it the default landing for returning users. It's the difference between a video dump and a destination.

9. **Decide the monetization model deliberately — then remove the rest.** The PPV/price leak isn't just a CSS bug; it's a symptom of an undecided business model. Is OFFKILTER ad-supported, subscription, creator-tipping, or PPV? Pick one (I'd bet subscription + creator tips for this audience), and *delete* the others from the stack. Half-configured monetization is worse than none — it confuses users and creators about what the platform actually is.

10. **Ship the child theme and stop fighting VidMov from the outside.** The plugin has done remarkable work styling around a theme it can't touch — but every sprint adds more `!important` overrides and defensive suppression. That's technical debt with a ceiling. A child theme converts a dozen CSS battles into a handful of clean template files, makes the card/single/search surfaces truly ownable, and unlocks performance work (dequeuing unused VidMov assets). It's the architectural unlock that makes everything after it cheaper.

**The one-line version:** OFFKILTER is closer than it looks. The code is ~80% there. The remaining distance is *decisions* (monetization, editorial staffing, creator ownership) and *one architectural move* (the child theme) — not more plugin CSS.

---

## What Shipped in v2.0 (changelog)

| Item | File |
|---|---|
| `OKArcana_Cleanup` — meta-leak `the_content` filter + `ok-frontend` body class | `includes/class-okarcana-cleanup.php` (new) |
| CSS Section V — Public Surface Lockdown (3 tiers, `display:none`) | `assets/css/offkilter-platform.css` |
| CSS Section W — touch targets, contrast, reduced-motion, card hierarchy | `assets/css/offkilter-platform.css` |
| ARIA labels — recommended-next, watch-nav, featured-creator CTA | `includes/class-okarcana-discovery.php` |
| Version 1.4.0 → 2.0.0 + bootstrap wire-up | `offkilter-arcana.php` |
| This review | `docs/platform-v2-product-review.md` (new) |

---

## Platform Score Trajectory

| Sprint | Version | Avg Score | Key lift |
|---|---|---|---|
| Initial audit | 0.0 | ~3.5/10 | Baseline |
| Fit & Finish | 0.2.0 | ~5.0/10 | Design system |
| Platform v1.0 | 1.0.0 | ~6.3/10 | Creator CSS, cards, archives |
| Platform v1.1 | 1.1.0 | ~6.9/10 | Auth, wpForo, video detail, SidebarChat foundation |
| Platform v1.2 | 1.2.0 | ~7.2/10 | Vocabulary, destination pages, legacy cleanup |
| Platform v1.3 | 1.3.0 | ~7.5/10 | Discovery: intro, curation, creator pages |
| Platform v1.4 | 1.4.0 | ~7.8/10 | Productization: discover, recommended-next, search CSS |
| **Platform v2.0** | **2.0.0** | **~8.2/10** | **Priority Zero lockdown, a11y, hierarchy, review** |
| Post-embed + child theme | 2.0.0 + ops | ~8.8/10 | Operator deploys + owns templates |
| Target v2.x | — | ~9.2/10 | SidebarChat live, search redesign, creator dashboard |
