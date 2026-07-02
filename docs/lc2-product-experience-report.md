# OFFKILTER Launch Candidate 2 — Product Experience Sprint Report

**Date:** 2026-07-02
**Plugin:** v2.9.0 — **deployed and active on production** (first production deployment of the Arcana plugin)
**Branch:** feature/arcana-plugin
**Backups:** `/opt/bitnami/backups/lc2-20260702-112544/` (full DB gz-verified, page-1244 Elementor JSON, menu-183 snapshot, page-8339 content, pre-deploy plugin list)

---

## Executive summary

LC2 shipped in one session: Priority Zero eliminated at the render layer, the plugin deployed to production for the first time, `/discover/` launched and wired into the main nav, the homepage now leads with the platform story, watch pages gained a Watch Next block, and `/creators/` gained the launch lineup. All verified by anonymous curls across desktop/mobile/tablet user agents.

## Priority Zero — root cause and elimination ✅

**Diagnosis.** Every public page carried the theme's front-end **upload/edit modal** and **live-stream ("go live") form** in its HTML — `beeteam368-extensions-pro` hooks `submit_form_html` (`user-submit-post/post-submit.php:14`) and `live_form_html` (`live-streaming/live-streaming.php:14`) to `wp_footer` with **no permission gate**. Those two components are the entire P0 cluster: Purchase Price, Pay Per View, Video Categories, Audio Categories, Featured Image. Both modules also register **anonymous (nopriv) submit AJAX handlers**. Separately, archived creator terms appeared in front-end category dropdowns.

**Fix (template/data layer — no CSS), plugin v2.9.0:**
- `suppress_submit_surface()` — unhooks `submit_form_html`/`live_form_html` (+ submit/live icons) for any user failing `okarcana_can_submit` (default: logged-in + `edit_posts`). Verified: administrators and contributors keep the full upload flow.
- `remove_nopriv_submit()` — removes both anonymous submit AJAX handlers. The viewer-facing live-channel-info nopriv endpoint is deliberately untouched.
- `hide_archived_terms()` — `get_terms` filter drops `archived-*` slugs from all front-end term queries (admin + WP-CLI unaffected).
- All guards individually defeatable via filters (operator escape hatches).

**Before → after (anonymous HTML, string occurrences):**

| String | Home | Video | Channel |
|---|---|---|---|
| Purchase Price | 4 → **0** | 4 → **0** | 4 → **0** |
| Pay Per View | 2 → **0** | 2 → **0** | 2 → **0** |
| Video Categories | 2 → **0** | 2 → **0** | 2 → **0** |
| Audio Categories | 1 → **0** | 1 → **0** | 1 → **0** |
| Featured Image | 1 → **0** | 1 → **0** | 1 → **0** |
| Archived - (terms) | 2 → **0** | 2 → **0** | 2 → **0** |

Final sweep: **0 occurrences of every P0 string on all 7 tested surfaces** (home, /discover/, /creators/, /trending/, video, channel, search) under desktop, iPhone, and iPad user agents.

## Phase 1 + 6 — Discover + editorial collections ✅
- **`/discover/` created (page 10748):** `[oktv_platform_intro]` pillar grid + `[oktv_discover_page]` with Editor's Picks (`8184,8226,8102,8096,8095`), featured Arcana, Signals, and the launch-creator row (87/91/90/89/88). All sections render; curated (hand-picked), not chronological.
- **Main-menu item 8123 "Explore" → "Discover"** now points to `/discover/`. `/trending/` remains reachable by URL; revert is one `wp menu item update`.
- Curation settings live: `featured_creator_ids=87,88,89,90,91`, `deprecated_creator_user_ids=17,48,49,54,15,64,12`, `deprecated_creator_term_ids=2055,2056,2059` — the v2.8 discovery/pulse rails and scheduler hold guard are now active in production.

## Phase 2 — Homepage story ✅
New first section on the Elementor front page (1244): a shortcode widget rendering `[oktv_platform_story]` — lede plus one "why it exists" line per pillar (Pulse/Signals/Arcana/Creators/Community). Applied via a dry-run-first JSON edit (`okstory2` section prepended, 6 → 7 sections), Elementor CSS cache regenerated. All pre-existing blocks verified intact.

## Phase 3 — Creator trust ✅ (partial by design)
- `/creators/` (8339) now ends with a **Launch Lineup** spotlight row (5 creators, stats shown); original content snapshotted.
- Avatars (prior sprint) + bios (curation sprint) render; plugin's identity layer now also upgrades gravatar-mystery fallbacks site-wide.
- **Open:** banner assets still don't exist (operator gap); **verified badges** for the launch five are ready to set but deliberately await your call — they're unclaimed accounts, and a verified mark implies the creator claimed the channel.

## Phase 4 — Watch experience ✅
Public single-video pages now end with **Watch Next** (3 taxonomy-related recommendations) + the reserved discussion slot, appended at the data layer (`the_content`, main query only) — no theme template edits. Toggleable in Arcana settings (`enable_watch_next_append`) and via filter. Verified rendering on a live video page.

## Phase 5 — Returning user 📄 (design only, per brief)
No backend exists for continue-watching/saved/following — **`docs/returning-user-design.md`** specifies data model, endpoints, theme-subscription reuse for Following, sequencing (Saved → Continue Watching → Following → Recent Pulse), and guardrails.

## Phase 7 — Quality review ✅ (with stated limits)
- Anonymous browsing: 7 surfaces × 3 device UAs — all HTTP 200, P0 clean, key blocks render.
- Logged-in: capability matrix verified via `wp eval` (admin/contributor → modal renders; anonymous → suppressed). A real logged-in browser pass and true-device (touch/layout) pass remain **operator follow-ups** — curl UA emulation can't judge visual layout.

## Remaining friction / follow-ups
1. **Real-device + logged-in browse pass** (operator; curl can't see layout).
2. **BunnyCDN dashboard purge** for cached assets (no CLI purge available).
3. **Verified badges** for the launch five — say the word and they're set.
4. **Banner assets** for launch creators.
5. `/trending/` page still exists (intentional); retire or redirect once Discover proves out.
6. Elementor edit was applied programmatically — opening page 1244 in the Elementor editor and re-saving once will normalize its internal cache/state (cosmetic, not urgent).

## Rollback
- Plugin: `wp plugin deactivate offkilter-arcana` restores pre-sprint rendering wholesale.
- Homepage: restore `page1244-elementor-data.json` from the backup dir.
- Menu: item 8123 back to `Explore` / `/trending/`.
- `/creators/`: restore `page8339-content-before.html`; `/discover/`: unpublish page 10748.
- Full DB dump is the backstop.

## Constraints honored
- Every P0 fix is template/data layer — **zero CSS hiding**.
- No new platform features: all surfaces shipped were pre-existing plugin capabilities; Phase 5 stayed a design doc.
- `beeteam368_membership_plans` untouched; viewer-facing nopriv endpoint preserved; all mutations snapshotted; gates honored (L0/L1 clean, L2 dry-run-first).
