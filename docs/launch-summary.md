# Launch Summary

Date: 2026-06-13
Scope: first Arcana imports using existing `WP Automatic -> VidMov` workflow.

## Required Settings
- Plugin active: `wp-automatic`.
- Target post type: `vidmov_video`.
- Target taxonomy: `vidmov_video_category`.
- Required categories:
  - `Arcana > Outcomes`
  - `Arcana > Premonitions`
- Post status: `Publish` (for launch run).
- Duplicate prevention: enabled (WP Automatic defaults).
- Import limit per run: `1`.

## Required Campaign Fields

### Outcome2026 Campaign
- Campaign Name: `Outcome2026 Playlist -> VidMov`
- Campaign Type: `YouTube`
- Source Mode: `Playlist`
- Playlist URL: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`
- Post Type: `vidmov_video`
- Post Status: `Publish`
- Categories: `Arcana`, `Outcomes`
- Schedule (`minutes`):
  - `1440` (1/day)
  - `480` (3/day)
  - `288` (5/day)

### Prem Campaign
- Campaign Name: `Prem Playlist -> VidMov`
- Campaign Type: `YouTube`
- Source Mode: `Playlist`
- Playlist URL: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
- Post Type: `vidmov_video`
- Post Status: `Publish`
- Categories: `Arcana`, `Premonitions`
- Schedule (`minutes`):
  - `1440` (1/day)
  - `480` (3/day)
  - `288` (5/day)

## Minimum Actions Robby Must Perform in WP Admin

### A) Create Prem Campaign
1. `WP Admin -> WP Automatic -> All Campaigns -> Add New`
2. Set fields above for Prem.
3. Save/Publish campaign.
4. Click `Run Now` once.

### B) Create Outcome2026 Campaign
1. `WP Admin -> WP Automatic -> All Campaigns -> Add New`
2. Set fields above for Outcome2026.
3. Save/Publish campaign.
4. Click `Run Now` once.

## Required Playlist URLs
- Prem: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
- Outcome2026: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`

## Go / No-Go

### Go
- Campaign saves successfully.
- Manual `Run Now` creates one `vidmov_video`.
- Imported post has intended Arcana category.
- Imported post URL loads publicly (HTTP `200`).

### No-Go
- Campaign errors on run.
- Wrong post type/taxonomy.
- Imported post not publicly visible.
- Duplicate flood starts after first run.
