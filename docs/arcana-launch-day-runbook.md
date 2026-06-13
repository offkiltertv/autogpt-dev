# Arcana Launch Day Runbook

Date: 2026-06-13
Status: Pre-production execution plan (no production changes executed)

## Objective
Launch Arcana discovery surfaces using existing WordPress admin tools only:
- Elementor
- Beeteam368/VidMov widgets
- WordPress Menus
- Existing creator/member/subscription surfaces

No plugin development. No theme file edits. No VidMov core changes.

## Scope
This runbook implements:
1. Homepage Arcana rail
2. Creator blending in existing ecosystem
3. Navigation upgrade
4. Discovery optimization priorities

## Pre-Flight Checklist
1. Confirm admin access to:
   - `Pages -> Home (ID 1244)`
   - `Appearance -> Menus`
   - `Appearance -> Widgets` (if used)
2. Confirm Arcana categories exist:
   - `Arcana` (term ID `2258`)
   - `Premonitions` (term ID `2259`)
   - `Outcomes` (term ID `2260`)
3. Confirm rollback capability:
   - Elementor revision history available on Home page
   - Current menu structure captured (screenshot/export)
4. Confirm known working widget baseline:
   - Widget type: `beeteam368_block_addon`
   - Widget ID: `d826690`
   - Layout preset: `leilani`

## Phase 1 - Homepage Execution (Arcana Rail)

### Source Widget To Clone
- Widget type: `beeteam368_block_addon`
- Widget ID: `d826690`
- Elementor location: Home page (`ID 1244`), main global feed rail section

### Arcana Rail Configuration
Set cloned widget to:
- Block title: `🔮 The Arcana`
- Block subtitle: `Latest Premonitions & Outcomes`
- Post type: `vidmov_video`
- Taxonomy: `vidmov_video_category`
- Included terms: `Arcana` (`2258`)
- Sort: `date DESC`
- Items: `8`
- Pagination: match existing home rail mode (`load more` or `infinite`)
- Display: keep thumbnail, title, creator/avatar, category chip, membership badge enabled

### Execution Steps (WP Admin)
1. `Pages -> Home (ID 1244) -> Edit with Elementor`.
2. Locate block widget `d826690`.
3. Duplicate widget/section.
4. Move duplicate above current global feed rail.
5. Apply Arcana rail configuration above.
6. Click `Update`.

### Validation
1. Homepage shows `🔮 The Arcana` above the main global feed.
2. Arcana cards render thumbnail/title/creator/category/membership badge.
3. Clicking Arcana card opens playable VidMov video page.
4. Mobile homepage rendering remains stable.

## Phase 2 - Creator Blending (One Ecosystem)

## Target Behavior
Arcana creators are not isolated; they appear in the same creator ecosystem as Gaming, Commentary, Music, and existing creators.

### Creator Sources
- Creator card source: existing VidMov creator/author display on `vidmov_video` cards
- Channel source: existing channel/profile archives (`channel-id/@handle` pattern)
- Subscription behavior: existing subscription system unchanged
- Featured creators source: existing `Featured Creators` / subscription-ranked creator surfaces

### Execution Steps (WP Admin)
1. In homepage Elementor, ensure Arcana rail card settings keep creator avatar/name visible.
2. Keep `Featured Creators` rail directly below Arcana rail (do not split Arcana-only creators).
3. Verify `Arcana -> Readers` menu target points to existing member/creator directory.

### Validation
1. Arcana card creator click opens standard creator/channel page.
2. Subscribe/follow behavior on Arcana creator pages matches non-Arcana creators.
3. Featured creator section remains mixed (Arcana + non-Arcana).

## Phase 3 - Navigation Implementation

### Target Menu Tree
- `Arcana` -> `/video-category/arcana/`
  - `Premonitions` -> `/video-category/premonitions/`
  - `Outcomes` -> `/video-category/outcomes/`
  - `Latest Readings` -> `/video-category/arcana/?orderby=date`
  - `Readers` -> `/member-list/` (or current creator directory URL)

### Menu Locations
- Primary: `main-menu`
- Secondary quick links: `side-menu`

### Existing Main Menu Ordering Baseline
Current top-level baseline observed: `Home`, `Video`, `Channel`, `Forum`, `Member List`.

### Recommended New Placement
Insert `Arcana` between `Video` and `Forum`.

### Execution Steps (WP Admin)
1. `Appearance -> Menus -> Main Menu`.
2. Add `Arcana` top-level item and child items listed above.
3. Reorder so `Arcana` sits between `Video` and `Forum`.
4. Replace/remove legacy `34.105.65.179` menu links.
5. Save menu.
6. `Appearance -> Menus -> Side Menu`: add `Arcana`, `Premonitions`, `Outcomes`.
7. Save side menu.

### Mobile Impact Checks
1. Open mobile menu drawer.
2. Confirm Arcana tree expands/collapses cleanly.
3. Confirm child links are tappable and not clipped.
4. Confirm no duplicated/broken legacy menu items remain.

## Phase 4 - Discovery Optimization Priorities

Ranked by fastest visibility gain and lowest implementation risk:

1. Arcana Homepage Rail
- Expected impact: Very High discovery lift immediately on first visit.

2. Navigation Changes (Main + Side)
- Expected impact: Very High routing/discoverability across all pages.

3. Creator Directory Integration (`Readers` link + mixed creator rail)
- Expected impact: High cross-silo creator discovery and retention.

4. Featured Arcana Readers (within existing featured creators behavior)
- Expected impact: Medium-High creator trust and familiarity.

5. Hero Feature Rotation (editorial/manual, optional)
- Expected impact: Medium; useful after baseline discovery surfaces are live.

## Launch Sequence (Operator Order)
1. Update Main Menu Arcana tree + remove legacy IP links.
2. Add Arcana homepage rail (clone `d826690` + Arcana query).
3. Confirm featured creator rail placement under Arcana rail.
4. Add side-menu Arcana quick links.
5. Run desktop/mobile QA.

## Go/No-Go Gate
Go when all are true:
1. `🔮 The Arcana` is visible above fold on homepage.
2. Arcana reachable in one click from main menu.
3. Arcana creators link to standard creator/channel profiles.
4. Subscription behavior unchanged across Arcana and non-Arcana creators.
5. No primary/side menu links point to `34.105.65.179`.

## Rollback
1. Homepage: remove Arcana cloned widget in Elementor or revert Home page revision.
2. Menus: remove Arcana entries and restore previous order.
3. Side menu: remove Arcana quick links.
4. Re-check homepage/menu and confirm pre-launch baseline restored.

## Estimated Effort and Risk
- Total execution time: 2-4 hours including QA
- Risk profile: Low (configuration-only changes)
- Highest risk item: menu/link mistakes (mitigated with pre/post validation)
