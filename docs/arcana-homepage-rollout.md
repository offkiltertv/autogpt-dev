# Arcana Homepage Rollout

Date: 2026-06-13

## Goal
Make Arcana content discoverable from homepage and navigation using existing Elementor + VidMov tooling only.

No VidMov core edits. No theme code edits.

## Environment Findings

### Homepage structure
- Homepage is WordPress page `1244` (`/home`) rendered at site root.
- Built with Elementor (`_elementor_edit_mode=builder`).
- Current homepage content uses a single Beeteam widget instance:
  - Widget: `beeteam368_block_addon`
  - Elementor widget id: `d826690`
  - Layout: `leilani`
  - Current label: `Side Menu` / `Right - Always Open ^^`
  - Query: broad feed (`vidmov_video`, `vidmov_audio`, `vidmov_playlist`, `vidmov_series`), date-desc

### Menu structure
- Active menus:
  - `main-menu` (18 items)
  - `side-menu` (1 item)
- Main menu has legacy links to `http://34.105.65.179/...` under `Channel` submenu (IDs `1692-1704`).

### Arcana taxonomy available
- `Arcana` (`2258`)
- `Premonitions` (`2259`)
- `Outcomes` (`2260`)

## 1) Homepage Changes (Admin Only)

### Best block to clone
Clone existing homepage Beeteam block `d826690`.

Why:
- Already production-proven styling/layout.
- Already outputs required card features without extra code:
  - Thumbnail
  - Title
  - Creator
  - Category labels
  - Membership badge

### New section configuration
Add above current global section:
- Section title: `🔮 The Arcana`
- Subtitle: `Latest Premonitions & Outcomes`
- Widget type: `beeteam368_block_addon`
- Layout: `leilani`
- Post type: `vidmov_video`
- Category filter: `Arcana` (`vidmov_video_category`)
- Sort: `date DESC`
- Items per page: `8` (recommended initial desktop footprint)
- Pagination: `infinite-scroll` or `loadmore-btn` (match existing UX)

Recommended keep:
- Display author = yes
- Display categories = yes
- Display comments/views/reactions = yes
- Duration/tag labels = yes

### WordPress Admin path
1. `Pages` -> `Home` (`ID 1244`) -> `Edit with Elementor`
2. Duplicate existing block widget (`d826690`) section.
3. Move duplicate above current section.
4. Rename block title/subtitle to Arcana labels.
5. Restrict query to `vidmov_video` + category `Arcana`.
6. Update page.

## 2) Navigation Changes (Admin Only)

### Add menu entries
Main menu additions:
- `Arcana` -> `https://www.offkilter.tv/video-category/arcana/`
- Child `Premonitions` -> `https://www.offkilter.tv/video-category/premonitions/`
- Child `Outcomes` -> `https://www.offkilter.tv/video-category/outcomes/`

### WordPress Admin path
- `Appearance` -> `Menus` -> `Main Menu`
- Add custom links or taxonomy links for Arcana terms.
- Save Menu.

## 3) Sidebar Changes (Admin Only)

### Recommendation
Add a `Latest Arcana` sidebar module above/before `Most Liked Videos`.

Two admin-safe options:
1. Add Beeteam/Video block widget in sidebar widget area filtered to Arcana.
2. If sidebar widget type is limited, add a simple custom HTML/title link panel:
   - Arcana archive
   - Premonitions archive
   - Outcomes archive

### WordPress Admin path
- `Appearance` -> `Widgets` (or `Customize` -> `Widgets` depending theme setup)

## 4) Search Optimization

No code required:
- Ensure Arcana section links to archive pages.
- Keep Arcana terms attached to all Arcana imports (already operational).
- Use archive URLs in internal links:
  - `/video-category/arcana/`
  - `/video-category/premonitions/`
  - `/video-category/outcomes/`

## 5) Featured / Sticky Policy

`vidmov_video` does not support native WordPress sticky posts.

Recommendation for initial launch:
- Default imported Arcana videos: **Normal**
- Discovery priority: achieved via dedicated Arcana homepage section (not sticky flags)
- Optional editorial feature: manually pin 1-3 picks using a separate curated block/query if needed

## 6) 34.105.65.179 Remediation Plan

### Remaining references (database counts)
- `wp_options`: 9
- `wp_posts.post_content`: 66
- `wp_posts.guid`: 153 (primarily legacy attachments)
- `wp_postmeta`: 30

### High-priority fixes
1. Main menu legacy links (immediate UX issue)
- Replace items `1692-1704` with `https://www.offkilter.tv/...` equivalents or remove if deprecated.

2. Theme option references
- Update options containing old host (example: `beeteam368_theme_options-transients`, `bunnycdn_cdn_url` if still set old host).

3. Published page content references
- Review and replace old-host URLs in published pages with current domain/CDN links.

### Low-priority / caution
- `wp_posts.guid` references on old attachments are historical identifiers; do not bulk rewrite blindly unless there is a concrete rendering issue.

### Admin-only remediation path
- Plugin available: `better-search-replace` (installed, currently inactive).
- Safe process:
  1. Activate `Better Search Replace`.
  2. Run dry-run search `34.105.65.179` -> `www.offkilter.tv` on selected tables.
  3. Apply only to targeted tables/columns after reviewing dry-run counts.
  4. Deactivate plugin if not needed ongoing.

## 7) Rollback Plan

If homepage/nav rollout causes issues:

1. Homepage rollback
- In Elementor, revert page `1244` to previous revision.
- Or remove Arcana cloned block and re-save.

2. Menu rollback
- Restore previous menu state by removing Arcana entries.
- Re-add prior items from menu revision/screenshot backup.

3. Sidebar rollback
- Remove `Latest Arcana` widget block.

4. Verification after rollback
- Homepage loads (`HTTP 200`)
- Main menu renders correctly
- Arcana imports remain unaffected (campaigns `8089`/`8090` continue)

## 8) Can this be done in WP Admin only?

Yes.

All proposed changes are executable via:
- Elementor page editor
- Appearance -> Menus
- Appearance -> Widgets
- Plugin UI (Better Search Replace) for URL cleanup

No theme PHP edits required.
No VidMov core/plugin code edits required.

