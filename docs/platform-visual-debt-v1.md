# OFFKILTER Platform — Visual Debt Tracker v1

**Date:** 2026-06-29
**Plugin version:** 2.6.0
**Sprint:** v2.6 Premium UX & Cross-Device Polish

Living register of known visual and UX debt. Prioritized by user impact, not severity.
Update statuses as items close. Feed this into v2.7+ sprint planning.

---

## Status Key

| Symbol | Meaning |
|---|---|
| ✅ | Closed in a prior sprint |
| 🔧 | Fixed in v2.6 |
| ⏸ | Deferred — documented exception |
| 📋 | Backlog — not yet scheduled |
| ⚠ | Bug — may cause functional breakage |

**Priority:** P0 = blocking / P1 = high / P2 = medium / P3 = low

---

## Top 50 Visual Debt Items

| # | Area | Description | Priority | Status | Target |
|---|---|---|---|---|---|
| 1 | Empty State | Pulse uses bare `<p>` inside `.ok-empty-state`; Signals uses icon+label `.oktv-signals-empty`. CSS bridge added in v2.6; PHP markup unification pending. | P1 | ⏸ CSS bridge v2.6 | v2.7 PHP |
| 2 | Button System | `.ok-btn--pulse` (teal CTA variant) was missing. | P1 | 🔧 v2.6 Section AC | Done |
| 3 | Footer Nav | Pulse link was absent from footer navigation. | P1 | 🔧 v2.6 PHP | Done |
| 4 | Footer Legal | No Privacy / Terms links. Now conditional on attrs. | P1 | 🔧 v2.6 PHP | Done |
| 5 | Footer Layout | Single-column only; no desktop multi-column grid. | P1 | 🔧 v2.6 Section AC | Done |
| 6 | Footer Copy | Tagline "Discover. Create. Discuss. Return." — generic. Updated to "Where the signal finds you." | P2 | 🔧 v2.6 PHP | Done |
| 7 | Footer Copy | Brand line was hardcoded in PHP (no `line` attr). Now attr-configurable. | P2 | 🔧 v2.6 PHP | Done |
| 8 | Footer Social | No social link stubs. Now conditional on `social_x/youtube/instagram` attrs. | P2 | 🔧 v2.6 PHP | Done |
| 9 | Footer Version | No plugin version display. Now opt-in via `show_version="1"`. | P3 | 🔧 v2.6 PHP | Done |
| 10 | Platform Intro | Default `pillars` attr omitted Pulse and Community. Updated to all 5. | P1 | 🔧 v2.6 PHP | Done |
| 11 | Platform Intro | Headline default was "OFFKILTER is a discovery platform." — generic. | P2 | 🔧 v2.6 PHP | Done |
| 12 | Platform Intro | Sub default was "Three things worth understanding." — inaccurate with 5 pillars. | P2 | 🔧 v2.6 PHP | Done |
| 13 | Platform Story | Lede default was "OFFKILTER is a creator discovery network." — generic. | P2 | 🔧 v2.6 PHP | Done |
| 14 | Watch Hero | Sub copy omitted Pulse. Updated to lead with all four content types. | P2 | 🔧 v2.6 PHP | Done |
| 15 | Pill System | `.ok-pill--pulse` teal variant was missing. | P2 | 🔧 v2.6 Section AC | Done |
| 16 | Icon System | `.ok-platform-intro__card--creators` icon color was implicitly inheriting; no explicit rule. | P3 | 🔧 v2.6 Section AC | Done |
| 17 | Responsive | No tablet breakpoint (769–1023px) for `.oktv-signals-cards` grid. | P1 | 🔧 v2.6 Section AC | Done |
| 18 | Responsive | No tablet breakpoint for `.ok-platform-intro__grid`. | P1 | 🔧 v2.6 Section AC | Done |
| 19 | Responsive | Pillar strip (5 chips) overflows without scroll on narrow screens. Added horizontal scroll at ≤480px. | P1 | 🔧 v2.6 Section AC | Done |
| 20 | Responsive | Pulse destination hero had no tablet layout rule. | P2 | 🔧 v2.6 Section AC | Done |
| 21 | Responsive | No foldable / 320px rules for chips, footer mark, or intro cards. | P2 | 🔧 v2.6 Section AC | Done |
| 22 | Microinteraction | Pillar chip had no hover lift. Added `translateY(-1px)` matching card language. | P2 | 🔧 v2.6 Section AC | Done |
| 23 | Microinteraction | Platform intro cards had generic signal-blue focus ring regardless of pillar. Added per-pillar override. | P2 | 🔧 v2.6 Section AC | Done |
| 24 | ⚠ Bug | `[oktv_creator_page show_videos="1"]` references undefined constant `OKArcana_Signals::CLASS_WATCH` (should be `CLASS_VIDEO`). Fatal in PHP 8.x. Dormant because `show_videos` defaults to `0`. **Do not pass `show_videos="1"` until v2.7.** | P0 | 📋 | v2.7 |
| 25 | VidMov Ownership | Single-video template is VidMov-owned. Render-path lockdown buffer is the documented exception until a child theme lands. | P0 | ⏸ Documented | v2.7+ |
| 26 | Legal | `/privacy/` and `/terms/` pages must be created by the operator for footer links to render. | P1 | 📋 Operator action | — |
| 27 | Signals Empty | `.oktv-signals-empty` uses a ⚡ emoji icon. Should use a Font Awesome icon to match the design system. | P3 | 📋 | v2.7 |
| 28 | Aria | No `aria-live` region on dynamic feed shortcodes. Screen readers don't announce content updates. | P2 | 📋 | v2.7 |
| 29 | Signals Card | Creator name is a `<span>`, not a link to the creator page. | P2 | 📋 | v2.7 |
| 30 | Curated Section | Empty-state copy "Nothing here yet." is not on-brand voice. | P3 | 📋 | v2.7 |
| 31 | CSS Header | File comment says "Version: 1.0" — misleading at v2.6. | P3 | 📋 | v2.7 |
| 32 | Pulse Badge | `.ok-pulse-badge` renders in list layout where it's visually awkward. Should be card-only. | P3 | 📋 | v2.7 |
| 33 | Discuss CTA | `.ok-discuss-cta--coming-soon` treatment is too faint on pulse cards; loses legibility at small sizes. | P3 | 📋 | v2.7 |
| 34 | Accessibility | Footer nav links: focus ring on dark surface needs visual verification against WCAG AA contrast ratios. | P2 | 📋 | v2.7 |
| 35 | Accessibility | No skip-to-content link. Users navigating by keyboard must tab through the entire header on every page. | P3 | 📋 | v2.7 |
| 36 | Accessibility | No print stylesheet. Decorative gradient/animation rules render in print output. | P3 | 📋 | v2.8 |
| 37 | Image Attrs | Cards use `width="320" height="180"` hardcoded. May not match actual served thumbnail sizes. | P2 | 📋 | v2.7 |
| 38 | Creator Page | Bug #24 (`CLASS_WATCH`) hides `show_videos` section for all users until fixed. | P1 | ⚠ See #24 | v2.7 |
| 39 | Discover Page | `[oktv_discover_page]` renders the Featured Pulse section even when 0 `pulse_item` posts are published. Renders empty state in a prominent position. | P2 | 📋 | v2.7 |
| 40 | Related Creators | Creator page uses `.ok-creator-spotlight` (compact) inconsistently alongside `.ok-featured-creator` (larger). Visual hierarchy is ambiguous. | P2 | 📋 | v2.7 |
| 41 | Profile Meter | Progress bar color uses `--ok-pulse` (teal). Semantically `--ok-community` (green) better conveys "completeness / success." | P3 | 📋 | v2.7 |
| 42 | Motion | `.ok-skeleton-card` shimmer (Section AA) has no reduced-motion rule at the AA level; it's only covered in AC-11. Confirm no flash before AC. | P3 | 📋 | v2.7 |
| 43 | Platform Intro | Card grid transition to 1-col on mobile is abrupt; no animation or crossfade. | P3 | 📋 | v2.7 |
| 44 | Creator Page | Profile completeness meter renders even when all content sections (`show_signals`, `show_pulse`, etc.) are disabled. Shows incomplete meter with no actionable items visible. | P3 | 📋 | v2.7 |
| 45 | Pulse | Pulse REST `create → finalize` flow not yet integration-tested with a live Pulse Clipper. Endpoints are live but untested end-to-end. | P1 | 📋 | v2.7 |
| 46 | Community | wpForo CSS (Section H) not validated against wpForo v2.x. Selectors may have changed upstream. | P1 | 📋 | v2.7 |
| 47 | Auth | ARMember CSS scoping (`Section G`) untested after any ARMember major version bump. | P1 | 📋 | v2.7 |
| 48 | Search | Section T search experience not tested on mobile. Search form layout may break at ≤375px. | P2 | 📋 | v2.7 |
| 49 | Signals List | Date column hidden on mobile; creator column also disappears via grid-template change at small sizes — both pieces of context lost. | P2 | 📋 | v2.7 |
| 50 | CSS File | Comment header version string ("Version: 1.0") is out of sync with plugin version (2.6.0). | P3 | 📋 | v2.7 |

---

## Launch Readiness Scores

Scale: 0–100. **80+ = green. 60–79 = amber. <60 = red.**

Scored after v2.6 is deployed. Self-assessment from render path + code audit — the site is behind Cloudflare and requires operator verification in a real browser.

| Category | Score | Notes |
|---|---|---|
| **Brand Identity** | 82 | Copy updated, footer redesigned, product-led messaging in place. Operator must verify placed shortcodes still pass desired overrides. |
| **Navigation** | 78 | Pillar strip and footer nav include Pulse. WP menu labels are operator-controlled. |
| **Discovery** | 85 | Platform intro shows all 5 pillars. Discover page sections are ordered well. Empty state still bare. |
| **Creator Experience** | 72 | Creator page covers profile, Pulse, Signals, bio. `show_videos` bug dormant; verification flow unbuilt. |
| **Pulse** | 80 | Flagship feel: teal identity, heartbeat badge, New marker, destination landing, REST `create/finalize` live. Media storage (tus) deferred. |
| **Signals** | 80 | Feed, cards, arcana variant solid. Empty state uses emoji icon. Creator link not wired. |
| **Arcana** | 68 | Destination hero + pillar present. Single-video template is VidMov-owned. Arcana-specific single template absent. |
| **Responsiveness** | 82 | Mobile solid. Tablet gaps closed in v2.6. Foldable rules added. Device testing still required. |
| **Accessibility** | 72 | Focus rings solid (pillar-aware in v2.6). 44px touch targets enforced. ARIA live regions, skip-link, print rules absent. |
| **Visual Polish** | 84 | Design language consolidated (AB). Hover/focus/loading/motion unified. Empty state CSS bridge added. |
| **Consistency** | 85 | BEM naming, `--ok-*` tokens, shortcode-atts pattern applied throughout. Two CSS class naming conventions still coexist (`oktv-*` legacy + `ok-*` current). |
| **Legal** | 50 | Footer Privacy/Terms links are wired. `/privacy/` and `/terms/` pages don't exist yet (operator action). |
| **Overall** | **76** | **Amber.** Premium-feeling, functionally complete. Two infra unlocks (child theme + Pulse storage) and editorial staffing are the gap to green. |

---

## Operator Action Required (cannot be done from the repo)

These must be completed in WordPress admin to realize the v2.6 improvements in production:

1. **Create `/privacy/` and `/terms/` pages** — then update the footer shortcode embed in the Elementor footer template to pass the attrs:
   ```
   [oktv_footer_brand privacy_href="/privacy/" terms_href="/terms/"]
   ```

2. **Pass social URLs** when accounts are live:
   ```
   [oktv_footer_brand social_x="https://x.com/offkiltertv" social_youtube="https://youtube.com/@offkiltertv"]
   ```

3. **Enable version display** (optional):
   ```
   [oktv_footer_brand show_version="1"]
   ```

4. **Audit existing shortcode embeds** — any Elementor page that passes `headline="..."`, `pillars="..."`, or `lede="..."` explicitly to `[oktv_platform_intro]` or `[oktv_platform_story]` will not see the new v2.6 defaults. Update those overrides if they conflict.

5. **Flush permalinks** — WP Admin → Settings → Permalinks → Save (after deploying, to pick up the 2.6.0 version bump).

6. **Verify on real devices** — test the pillar strip horizontal scroll on an actual narrow Android (≤360px), and the 3-column footer on a real tablet. Emulated DevTools resize is a good proxy but not authoritative.

7. **Do NOT pass `show_videos="1"` to `[oktv_creator_page]`** until v2.7 ships the `CLASS_WATCH` fix (debt item #24). It will fatal-error in PHP 8.x.

---

## v2.7 Recommendations (from this audit)

Highest-impact items not yet scheduled, ranked:

1. Fix `CLASS_WATCH` bug in `class-okarcana-discovery.php` (P0 — unblock `show_videos`)
2. Unify empty state markup in PHP (`pulse_card()` → icon + label structure)
3. Ship VidMov child theme — single-video template + retires lockdown buffer
4. Fix Signals card creator name → link to creator page
5. Add `aria-live` regions to feed shortcodes
6. Validate Section H (wpForo) and Section G (ARMember) against current plugin versions
7. Arcana single-video template (post type template via child theme)
8. Test search (Section T) on mobile
9. Fix `.oktv-signals-empty` emoji icon → Font Awesome `fas fa-bolt`
10. Update CSS file header comment version string
