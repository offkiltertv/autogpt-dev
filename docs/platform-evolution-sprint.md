# OFFKILTER Platform Evolution Sprint

Date: 2026-06-14  
Branch: `feature/arcana-plugin`

## Objective
Move OFFKILTER from theme-shaped navigation into a coherent platform centered on:
- ⚡ Signals
- 🔮 Arcana
- 👥 Creators
- 💬 Community

## What Was Implemented

## Phase 1: Platform Audit
- Completed route inventory and friction analysis.
- Output: `docs/platform-audit-v1.md`

## Phase 2: Destination Strategy
- Updated primary navigation to reflect destination-first structure.
- Main menu now prioritizes:
  1. Home
  2. Watch
  3. ⚡ Signals
  4. Arcana
  5. Creators
  6. Community
  7. Explore

## Phase 3: Signals Implementation

### Signals category/archive created
- Taxonomy: `vidmov_video_category`
- New term:
  - `Signals` (`slug: signals`, `term_id: 2429`)
  - Description: `Short-form OFFKILTER videos (0-90 seconds).`
- Public archive:
  - `/video-category/signals/`

### Signals content assignment (initial production pass)
- Method: duration classification from existing YouTube source URLs (`yt-dlp`, no rehosting/importer changes).
- Scope: recent 200 published `vidmov_video` posts.
- Result:
  - 135 URLs resolved with valid durations.
  - 80 videos classified as `<= 90s` and assigned to Signals.
  - Signals term count now `80`.

### Homepage integration
- `⚡ Signals` rail now uses native category query:
  - `filter_items: signals`
  - `category: 2429`
  - `ids: (empty)` for dynamic latest Signals
  - subtitle updated to `Quick hits (0-90 seconds)`

## Phase 4: Creator Experience
- Existing Arcana creator prioritization preserved on homepage.
- Featured creators subtitle updated to: `Active Arcana + Signals creators`.
- Signals archive renders creator avatar/name/channel links natively on each card.

Current caveat:
- Initial Signals set is skewed toward one active source creator (`@lipps`) due current short-form density in recent imports.
- This is a content distribution reality, not a routing failure.

## Phase 5: SidebarChat Integration Readiness
- Existing homepage placeholders retained and aligned:
  - `Discuss in #signals`
  - `Discuss in #arcana`
  - `Discuss in #cases`
- No live stream/chat integration introduced in this sprint.

## Phase 6: Legacy Cleanup
- Reduced stale navigation prominence:
  - Removed side-menu `Music` entry.
  - Added side-menu `Community`.
  - Side menu now focused on Signals/Arcana/Community.

## Phase 7: Mobile Experience
- Mobile/side discovery hierarchy improved:
  1. ⚡ Signals
  2. Arcana
  3. Premonitions
  4. Outcomes
  5. Community

## Validation

### Route checks
All primary discovery routes return `HTTP 200`:
- `/`
- `/video/`
- `/video-category/signals/`
- `/video-category/arcana/`
- `/video-category/arcana/premonitions/`
- `/video-category/arcana/outcomes/`
- `/member-list/`
- `/channel/`
- `/community/`
- `/trending/`

### Homepage source checks
Confirmed in rendered HTML:
- `⚡ Signals` main rail present
- Signals category query active (`category: 2429`)
- Main and side menu links include Signals + Community

## Rollback Notes
- Backups created before platform evolution edits:
  - `/opt/bitnami/backups/offkilter-platform-evolution-20260614-185955/`
  - includes pre-change homepage Elementor data and menu CSV snapshots.
- Rollback path:
  1. Restore menu structures from backup snapshots.
  2. Restore pre-change homepage `_elementor_data`.
  3. Re-save Elementor page `1244` to refresh rendered content cache.

## Outstanding Work (Next Sprint)
- Expand Signals classification beyond recent-200 window to full library in controlled batches.
- Canonicalize creator discovery between `/member-list/` and `/channel/`.
- Consolidate duplicate auth/profile route families (`-2` and non-`-2` variants).
- Reduce visibility of legacy Woo/auth utilities on public discovery surfaces.

## Sprint Outcome
OFFKILTER now presents a clearer platform model:
- Signals is a first-class discovery destination.
- Arcana remains a dedicated pillar.
- Creator and community routes are surfaced intentionally.
- Navigation is materially less theme-generated and more product-directed.
