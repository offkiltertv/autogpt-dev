# Arcana Discovery Optimization

Date: 2026-06-13

## Scope
Audit-only visibility optimization for existing production stack:
- WordPress + Elementor homepage
- VidMov blocks/widgets
- WP Automatic imported `vidmov_video`

No VidMov core modification required.

## 1) Homepage Layout Audit
Homepage: page `1244` (`/`, Elementor builder, template type `wp-page`).

### Active content blocks detected
1. Main video block (`beeteam368_block_addon`)
- Title: `Side Menu`
- Subtitle: `Right - Always Open ^^`
- Layout: `leilani`
- Post types: `vidmov_video`, `vidmov_audio`, `vidmov_playlist`, `vidmov_series`
- Sort: `order_by=date`, `order=DESC`
- Items: `12`
- Filter chips: `tv-shows,romance,entertainment,gaming,movies,sports,music`
- Category constraint: none (global feed)

2. Sidebar widget block
- Title: `Most Liked Videos`
- Post type: `vidmov_video`
- Sort: `order_by=like`
- Items: `3`

3. Top nav utility
- `Trending` link exists (`/trending/`).

### Current Arcana visibility
- Arcana posts appear in category archives and search.
- Arcana posts are not guaranteed to appear in homepage hero/top lists due to global date/trending selection behavior.

## 2) Configurability Assessment
Configurable without code changes:

1. Homepage blocks (Elementor)
- Add/duplicate `beeteam368_block_addon` widgets.
- Configure by post type, category, order, item count, labels, pagination.

2. Navigation menus (WP Menus)
- Main menu and side menu are editable.
- Existing legacy links can be replaced.

3. Sidebar block content
- Widget block can be replaced or complemented with Arcana-targeted block.

## 3) Can We Add A Dedicated Arcana Homepage Section?
Yes.

Fastest implementation path:
- Duplicate existing `beeteam368_block_addon` on homepage.
- Configure:
  - Section title: `🔮 The Arcana`
  - Post type: `vidmov_video`
  - Category: `Arcana` (`vidmov_video_category` term `2258`)
  - Order: `date DESC`
  - Items: `6` (or `8` desktop / `4` mobile equivalent)
  - Optional: disable non-Arcana filter chips

This keeps import workflow unchanged and uses existing theme widgets.

## 4) Navigation Audit
Main menu (`main-menu`) includes:
- `Home`, `Video`, `Channel`, `Forum`, `Member List`
- Legacy channel-tab links pointing to `http://34.105.65.179/...` (stale)

Side menu (`side-menu`) currently includes only:
- `Music`

### Navigation recommendation
- Add top-level `Arcana` -> `/video-category/arcana/`
- Add submenu:
  - `Premonitions` -> `/video-category/premonitions/`
  - `Outcomes` -> `/video-category/outcomes/`
- Remove or replace legacy `34.105.65.179` links.

## 5) Discovery Recommendations

### A. Homepage placement (highest impact)
1. Add `🔮 The Arcana` block above current global feed block.
2. Keep existing global feed intact.
3. Use Arcana-only category query to guarantee surfacing.

### B. Menu placement
1. Add `Arcana` top-level menu item.
2. Add `Premonitions` and `Outcomes` as children.
3. Clean legacy channel-tab links in same pass.

### C. Sidebar placement
1. Add `Latest Arcana` sidebar widget (3 items, date-desc).
2. Keep `Most Liked Videos` beneath it, not replaced.

### D. Search optimization
1. Promote Arcana archive URLs as canonical discovery pages:
- `/video-category/arcana/`
- `/video-category/premonitions/`
- `/video-category/outcomes/`
2. Ensure Arcana terms are visible in breadcrumbs/category links on cards.
3. Keep post status `publish` and preserve unique titles; avoid title duplication in campaign settings.

## 6) Feature/Sticky Strategy For Initial Launch
Recommendation: **Normal by default**, selective manual featuring.

- Default for imported Arcana videos: `Normal`
- Weekly editorial picks: manually feature `1-3` Arcana videos in homepage module (if editorially desired)
- Avoid blanket sticky/featured on all Arcana imports to prevent feed distortion and reduce moderation pressure.

## 7) Fastest Path (No Core Code Changes)
1. In Elementor homepage (`page 1244`), duplicate a Beeteam block and set category to Arcana.
2. Add Arcana menu entries.
3. Add sidebar Arcana widget.
4. Remove/replace legacy `34.105.65.179` links.
5. Validate:
- homepage Arcana section visible
- menu navigation to Arcana archives works
- latest imports (`8095`, `8096`, onward) appear in Arcana section

## 8) Operational Note
This visibility rollout is compatible with current production pipeline:
- WP Automatic campaigns `8089` and `8090`
- VidMov post type `vidmov_video`
- Existing membership and taxonomy behavior

No new importer/plugin required.
