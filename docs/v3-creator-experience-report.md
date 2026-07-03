# OFFKILTER v3.0 — Creator Experience Sprint Report

**Date:** 2026-07-02
**Plugin:** v3.0.0 deployed and active on production
**Branch:** feature/arcana-plugin
**Backups:** `/opt/bitnami/backups/v3-20260702-121814/` (full DB gz-verified, launch usermeta, settings, homepage Elementor JSON, discover/creators page content, seeded-pulse ID list)

---

## Executive summary

v3.0 shipped end-to-end in one session: every featured creator is now complete (avatar, banner, bio, featured video, featured Pulse, internal verification), **Pulse is alive** (8 curated launch items on a branded destination), the homepage opens with a true hero, video pages carry a full post-watch engagement module, Discover gained Trending Pulse + a Recommended Creator, and — for the first time — the cross-device and performance audits were **actually executed** (35 real device-profile screenshots + 4 Lighthouse runs), not simulated.

**Re-score: 85/100 (from 81). Target 90–92 not reached — the gap is explained and ranked below; the top blockers are operator-level (consent-manager performance, legal pages), not code.**

---

## Phase 1 — Creator trust ✅

Every featured creator (87, 88, 89, 90, 91) now has:

| Item | State |
|---|---|
| Avatar | ✅ (prior sprint; spotlights now use it too) |
| Banner | ✅ platform-branded SVGs (pillar-hue + name; honest OFFKILTER-default art, replaceable) — `okarcana_banner` set; theme's own banner meta intentionally skipped (needs uploads-relative raster derivatives) |
| Biography | ✅ (curation sprint; first-pass copy) |
| Featured video | ✅ `okarcana_featured_video` per creator, renders on creator pages + spotlights |
| Featured Pulse | ✅ `okarcana_featured_pulse` → their seeded pulse |
| Featured playlist | ➖ no playlists exist for these creators (gap, not a defect — noted for content ops) |
| Verification | ✅ **internal** `_ok_verification_status=platform_internal` (counted by the completeness meter; public badge deliberately withheld until accounts are claimed) |

## Phase 2 — Pulse identity ✅

- **Pulse has content**: 8 embed-based launch pulses (2/creator where possible), real thumbnails, `ready` status, `launch` tag. IDs 10751–10758, logged for one-command removal.
- Cards: duration chip, `#tag` chip, linked creator byline; destination hero gained the pulsing-dot lockup; archive/landing/empty states verified.
- Honest caveat: seeds are full-length embeds, not clipper-native shorts — Pulse's "signature" claim completes when the tus upload pipeline ships.

## Phase 3 — Homepage ✅

`[oktv_platform_story hero="1"]` live: **"Off-script. On point."** + one-line promise + Watch/Discover CTAs, pillar rows beneath, values remain in the footer. Verified legible from 373 px (Fold folded) to 1920 px (TV) — 5-second comprehension achieved above the fold on every profile.

## Phase 4 — Watch experience ✅

`[oktv_post_watch]` composite on all public single-video pages: Watch Next + Related Signals (author's signal-class clips) + Related Pulse + Related Creator spotlight + SidebarChat placeholder. Sections self-suppress when empty (verified: FFT313 video shows Related Pulse ×2 + spotlight; non-launch author's page correctly skips pulse).

## Phase 5 — Creator discovery ✅

Discover now: Editor's Picks (was silently disabled — `show_editors` missing; fixed), Featured Arcana, **Trending Pulse** (engagement-ordered), Creators to Watch (curated lineup), **Recommended Creator** (deterministic most-recently-active rotation). Chronological-only presentation eliminated on the discovery surface.

## Phase 6 — Cross-device UX ✅ (real audit)

35 Playwright captures across 7 profiles × 5 pages → `docs/assets/v3-responsive/` + findings in **`docs/v3-responsive-audit.md`**. Layout holds everywhere; the 5 remaining issues (ranked there) are: Termly banner dominance on mobile, zero-state "0%/0 Reactions" noise, share-row injection, TV side-menu default, render-blocking wpForo/fonts CSS.

## Phase 7 — Performance ✅ (audited; safe fixes shipped)

Lighthouse (mobile unless noted):

| Page | Perf | LCP | CLS | TBT |
|---|---|---|---|---|
| Home | 49 | 14.4 s | **0** | 550 ms |
| Home (desktop) | 72 | 2.5 s | 0.196 | 0 ms |
| Discover | 56 | 14.2 s | 0.001 | 350 ms |
| Video | 26 | 15.2 s | 0.265 | 730 ms |

**Root causes (documented, mostly not plugin-owned):**
- **The mobile LCP element is the Termly cookie banner**, and `app.termly.io/resource-blocker` is the single largest render-blocker (2.3–2.5 s). Operator: switch Termly off auto-block / async embed, compact banner.
- Render-blocking Google Fonts CSS (~0.9 s) and wpForo `colors.css` on non-forum pages; ~350–374 KiB unused JS (theme/plugins); ~950 ms TTFB; non-WebP thumbnails.
- Video-page CLS (0.265) comes from the theme's live-comments module; desktop-home CLS (0.196) from the main-content/sidebar container — both theme-level.

**Safe optimizations shipped (plugin scope):** preconnect hints to `fonts.googleapis.com` / `fonts.gstatic.com` / `i.ytimg.com` / `www.youtube.com` (verified rendering); dead `.oktv-signals-empty` CSS removed; all plugin-rendered images carry `loading="lazy"` + dimensions; plugin components contribute ~0 CLS. Everything else is documented for theme/operator action — per the "only safe optimizations" rule.

## Phase 8 — Launch readiness re-score

| Category | v2.7 | v3.0 | Evidence |
|---|---|---|---|
| Brand Identity | 84 | 90 | Hero, Pulse lockup, banner system |
| Navigation | 78 | 88 | Discover in main nav; archived terms gone |
| Discovery | 88 | 92 | Curated Discover live (editors/trending/recommended) |
| Creator Experience | 80 | 90 | Complete profiles + featured content + meter |
| Pulse | 82 | 86 | Destination + seeds + identity; native pipeline pending |
| Signals | 85 | 86 | Classification live in prod |
| Arcana | 72 | 85 | P0 root-caused; attribution corrected (curation sprint) |
| Responsiveness | 82 | 88 | 35-shot verified matrix; issues minor + documented |
| Accessibility | 73 | 76 | Reduced-motion + ARIA labels on new components; skip-link/live-regions still missing |
| Visual Polish | 87 | 90 | Chips, hero, unified empty states |
| Consistency | 88 | 91 | P0 = 0 everywhere; one component language |
| Legal | 50 | 55 | Consent manager present; /privacy/ + /terms/ pages still missing |
| **Overall** | **81** | **85** | **Amber-green; below the 90–92 target** |

### Why below target, and the ranked blockers (by user impact)

1. **Mobile performance (Perf 26–56, LCP ~14 s)** — dominated by the Termly resource-blocker + render-blocking theme assets. This is the single biggest real-user harm (bounce before first paint). *Operator: Termly config; theme: font/JS diet.* Worth ~3–4 overall points.
2. **Legal pages missing** (`/privacy/`, `/terms/`) — trust + compliance; dead ends from the cookie banner's own policy links. *Operator: create pages; footer links land.* Worth ~3 points.
3. **Accessibility basics** — skip-to-content link, `aria-live` on feeds, heading order on theme templates. Worth ~1–2 points.
4. **Pulse native pipeline** — seeded embeds prove the identity; the signature-content claim needs clipper-native uploads (tus storage).
5. **Zero-state trust noise** — "0%" / "0 Reactions" overlays undermine the completed creator work; theme-settings fix, 30 minutes.
6. **Cookie-banner UX on small screens** — same Termly config change as #1.

With #1 + #2 done (both operator-side, no code), 90+ is realistic at the next re-score.

## Rollback

Plugin: reinstall the v2.9 zip (or deactivate). Pulses: delete IDs in `seeded-pulse-ids.txt`. User meta: restore from `launch-usermeta-before.tsv`. Homepage/discover/creators content: snapshots in the backup dir. Full DB dump is the backstop.

## Constraints honored

- No unrelated features (Phase 5 of LC2's returning-user design remains a doc; nothing new invented here beyond the brief).
- Data/template layer only; no CSS hiding; `beeteam368_membership_plans` untouched.
- All live mutations gated, dry-run-first, snapshotted; banners are honest platform-default art; verification stays internal until creators claim their accounts.
