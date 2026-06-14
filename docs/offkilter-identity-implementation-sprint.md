# OFFKILTER Identity Implementation Sprint

Date: 2026-06-14  
Branch: `feature/arcana-plugin`

## Objective
Implement OFFKILTER identity updates using existing VidMov + Elementor architecture, with no new plugin/theme-core development.

## Changes Implemented

### 1. Identity Pass on Homepage (Page ID `1244`)
- Updated Arcana rail branding:
  - Title: `🔮 The Arcana`
  - Icon: `fas fa-gem`
  - Subtitle: `Curated readings from active Arcana creators`
- Added `⚡ Signals` discovery rail:
  - Icon: `fas fa-bolt`
  - Subtitle: `Fast-moving clips across OffKilter`
  - Curated source IDs configured for launch set
- Added `👥 Featured Creators` rail:
  - Icon: `fas fa-user-group`
  - Featured set:
    - FOOD FOR THOUGHT 313
    - Astraea 5D
    - POSHRANDY55
    - AllseeingisisOracle
    - MADAMEBUTTERFLY444
- Added SidebarChat placeholders block:
  - `Discuss in #signals`
  - `Discuss in #arcana`
  - `Discuss in #cases`
- Renamed/rethemed mixed-content rail:
  - `Latest on OKTV` -> `🎬 Watch Now`
  - Icon: `fas fa-play-circle`
  - Subtitle: `Fresh videos across OffKilter`

### 2. Remaining Inherited Icon Cleanup
- Replaced remaining homepage/Elementor maple icon usage (`fab fa-canadian-maple-leaf`) with OFFKILTER icon set.
- Verification: no `fab fa-canadian-maple-leaf` references remain in current homepage render output.

### 3. Render Consistency Fix
- Root cause found during rollout:
  - `_elementor_data` was updated, but frontend was still serving stale `post_content` render.
- Fix applied:
  - Re-saved Elementor document for page `1244` using Elementor document API (`Document::save`) as admin context.
  - Result: live frontend now reflects latest Arcana/Signals/Featured Creators/SidebarChat configuration.

## Validation

Public checks passed:
- `https://www.offkilter.tv/` returns `HTTP/2 200`
- `https://offkilter.tv/` returns canonical redirect to `https://www.offkilter.tv/`
- Homepage source now contains:
  - `🔮 The Arcana`
  - `⚡ Signals`
  - `👥 Featured Creators`
  - `💬 SidebarChat (Coming Soon)`
  - `🎬 Watch Now`

Platform health checks during rollout:
- Origin VM reachable
- NGINX/PHP-FPM/MariaDB healthy

## Backups/Safety
- Pre-sync homepage snapshot captured on origin:
  - `/opt/bitnami/backups/offkilter-identity-sync-20260614-171513/post1244-pre-sync.json`

## Rollback
If rollback is required:
1. Restore `post_content` + Elementor data from pre-sync snapshot/backups.
2. Re-save page `1244` in Elementor to regenerate frontend content.
3. Purge runtime caches if stale output persists.

## Notes
- Changes were implemented with existing Elementor + VidMov capabilities only.
- No new plugin development, no theme-core edits, no importer changes.
