# OFFKILTER Platform v2.7 — Platform Confidence Sprint Review

**Date:** 2026-06-29
**Plugin version:** 2.7.0 (was 2.6.0)
**Branch:** feature/arcana-plugin

---

## What Shipped

### Track A — CLASS_WATCH P0 Bug Fix
- `class-okarcana-discovery.php` line 564: `OKArcana_Signals::CLASS_WATCH` (undefined) → `OKArcana_Signals::CLASS_VIDEO`
- `[oktv_creator_page show_videos="1"]` no longer fatals in PHP 8.x
- No test harness in repo — v2.8 action: scaffold `composer.json` + WP-PHPUnit for constant and shortcode regression coverage

### Track B — Child Theme Scaffold + Buffer Auto-Retire
- `themes/vidmov-offkilter-child/style.css` — required WP child theme header (`Template: vidmov`)
- `themes/vidmov-offkilter-child/functions.php` — enqueues parent stylesheet; applies `okarcana_guard_buffer_enabled=false` explicitly
- `class-okarcana-cleanup.php` — `maybe_start_lockdown_buffer()` auto-retires when any child theme is active (`get_stylesheet() !== get_template()`)
- **Operator action required:** WP Admin → Appearance → Themes → activate "VidMov OFFKILTER Child"
- VidMov template overrides (`single-vidmov_video.php`) deferred to v2.8 — VidMov theme source not in repo
- Data-layer guard (`okarcana_guard_monetization_meta`) and content filters remain active at all times

### Track C — Unified Empty States
All four empty-state locations now emit `.ok-empty-state` with `__icon` + `__label` children:

| Location | Icon | Copy |
|---|---|---|
| Pulse feed (`class-okarcana-pulse.php`) | `fa-wave-square` | "No Pulse yet." |
| Curated section (`class-okarcana-discovery.php`) | `fa-layer-group` | "Nothing here yet." |
| Signals list (`class-okarcana-signals.php`) | `fa-bolt` | "No Signals found yet." |
| Signals cards (`class-okarcana-signals.php`) | `fa-bolt` | "No Signals found yet." |

`oktv-signals-empty` and its CSS are now dead code (not deleted — safe to leave or remove in v2.8 cleanup pass).

### Track D — Homepage Story
- `pillar_config()` Signals desc: "Fast clips. Under 90 seconds." → "The standout clip worth your attention. Under 90 seconds."
- `pillar_config()` Community desc: "Join the conversation." → "Where the conversation lives."
- `render_discover_page()` Featured Pulse section guarded by `wp_count_posts('pulse_item') > 0` — no longer shows an empty state in the editorial lead position when no Pulse posts exist

### Track E — Creator Trust
- Creator page avatar now prefers `okarcana_avatar` user meta; falls back to `get_avatar()` (Gravatar)
- Creator page banner added via `okarcana_banner` user meta; falls back to `assets/img/default-channel-banner.svg`; opt-out via `show_banner="0"`
- `show_bio` shortcode attr default changed from `0` → `1` (bios display when set)
- `show_banner` attr added (default `1`)
- `@handle` display was already implemented at line 398 — no change needed
- Signals card/list creator name now links to `get_author_posts_url()` author archive

### Track F — CSS Section AD
94 lines appended (4,462 → 4,556 total):
- `AD-1`: `.ok-empty-state` flex column; `__icon` + `__label` children
- `AD-2`: `.ok-creator-page__banner` — 4:1 desktop / 3:1 mobile aspect ratio
- `AD-3`: `.ok-creator-page__handle` — @username typography
- `AD-4`: `.ok-creator-page__avatar-img` — 80px circle, `object-fit: cover`
- `AD-5`: `.oktv-signals-card__creator a` — link hover (color lift + underline); reduced-motion guard

---

## Launch Readiness Delta

**v2.6 → v2.7** (self-assessed from code audit; operator must verify on production)

| Category | v2.6 | v2.7 | Δ | Notes |
|---|---|---|---|---|
| Brand Identity | 82 | 84 | +2 | Pillar copy sharpened |
| Navigation | 78 | 78 | 0 | WP menu labels are operator-controlled |
| Discovery | 85 | 88 | +3 | Pulse guard + pillar copy + empty state unified |
| Creator Experience | 72 | 80 | +8 | Avatar, banner, bio default on, creator links |
| Pulse | 80 | 82 | +2 | Empty state unified |
| Signals | 80 | 85 | +5 | Creator name linked, FA icon empty state |
| Arcana | 68 | 72 | +4 | CLASS_WATCH fixed, child theme scaffold landed |
| Responsiveness | 82 | 82 | 0 | Tablet rules from v2.6 still the baseline |
| Accessibility | 72 | 73 | +1 | Creator links add keyboard navigability |
| Visual Polish | 84 | 87 | +3 | Unified empty state, banner, creator link hover |
| Consistency | 85 | 88 | +3 | Empty state class unified across all surfaces |
| Legal | 50 | 50 | 0 | Operator must create /privacy/ and /terms/ |
| **Overall** | **76** | **81** | **+5** | **Amber — approaching green** |

The 85 target was not reached in v2.7. The remaining 4 points require:
1. Legal pages created by operator (+5–8 points to Legal category alone)
2. ARIA live regions + skip-to-content link (+2–3 to Accessibility)
3. VidMov child theme template overrides (+3–4 to Arcana, +2 to Consistency)

With operator completing the legal pages and v2.8 shipping ARIA + child theme templates, 85–88 is achievable.

---

## Screenshots

*Add before/after screenshots here after deploying to production in a private browser window. Cloudflare + auth gating make pre-deploy screenshots unavailable from the repo.*

Key surfaces to capture:
1. `/discover/` — Featured Pulse section absent when no pulse_item posts exist
2. Creator page — banner (SVG fallback or custom), avatar, bio visible by default
3. Signals section on creator page — creator name as link
4. Empty Signals feed — `fa-bolt` icon centered above label
5. Platform intro pillar cards — updated Signals and Community copy

---

## Remaining Debt (carry-forward from platform-visual-debt-v1.md)

Top items not addressed in v2.7, by impact:

| # | Item | Priority | Notes |
|---|---|---|---|
| 26 | Legal: `/privacy/` and `/terms/` pages | P1 | Operator action — footer links render when pages exist |
| 28 | ARIA live regions on feed shortcodes | P2 | v2.8 — add `aria-live="polite"` to feed wrappers |
| 35 | Skip-to-content link | P3 | v2.8 — single line in theme header |
| 46 | wpForo CSS (Section H) not validated vs. v2.x | P1 | v2.8 — check selectors against current plugin |
| 47 | ARMember CSS (Section G) not validated | P1 | v2.8 — same |
| 45 | Pulse REST `create/finalize` untested end-to-end | P1 | Requires live Pulse Clipper integration test |
| 40 | Creator page: spotlight vs. featured hierarchy ambiguous | P2 | v2.8 — consolidate to one card size |
| 48 | Search (Section T) not tested on mobile | P2 | v2.8 |
| 31 | CSS file header comment version string | P3 | v2.8 — update "Version: 1.0" to "2.7.0" |
| 27 | `oktv-signals-empty` dead code | P3 | v2.8 — remove in cleanup pass |

---

## Recommended v2.8 Roadmap

1. **VidMov child theme template overrides** — `single-vidmov_video.php` (requires VidMov source access). Until then, the buffer auto-retires when the child theme is active, with the data-layer guard as primary protection.
2. **PHPUnit test scaffold** — `composer.json` + `phpunit-wp`. At minimum: constants exist, shortcode attrs produce expected markup, `maybe_start_lockdown_buffer` returns early when child theme active.
3. **ARIA live regions** — add `aria-live="polite"` wrapper to Pulse, Signals, and Curated feed shortcodes.
4. **Skip-to-content link** — one `<a href="#main-content">` in the header template.
5. **wpForo + ARMember CSS validation** — verify Section H and Section G selectors against current plugin versions installed on production.
6. **Arcana single-video template** — via child theme, replace VidMov default with branded Arcana layout.
7. **Pulse tus media storage** — unblocks Pulse Clipper M1 end-to-end flow.
8. **Search redesign** — group results by Pulse / Signals / Arcana / Creators (Section T).
9. **`oktv-signals-empty` cleanup** — remove dead CSS from Sections D and existing, update CSS file header version string.
10. **Creator page hierarchy** — consolidate `ok-creator-spotlight` and `ok-featured-creator` into a single card component.
