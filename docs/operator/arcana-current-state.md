# Arcana Current State (Operator)

Date: 2026-06-13

## What Is Automated
- Playlist ingestion via WP Automatic campaigns:
  - `8089` Arcana Outcome2026
  - `8090` Arcana Prem
- Import target: `vidmov_video`
- Category assignment: Arcana branches (`arcana`, `premonitions`, `outcomes`)
- Imported comments posting (YouTube comments to WP comments)
- Metadata phase 1 on Arcana campaigns:
  - `channel_id`
  - `channel_title`
  - `youtube_publish_date`

## What Is Importing
- Arcana playlists through campaigns `8089` and `8090`
- Latest observed Arcana imports:
  - `8102` (campaign `8089`)
  - `8104` (campaign `8090`)

## What Requires Manual Intervention
- Homepage Arcana rail placement (Elementor)
- Featured Readers placement and rotation
- Main/mobile menu Arcana tree updates
- Legacy menu link cleanup (`34.105.65.179`)
- Creator claim verification/approval workflow
- Metadata phase 2 activation (`youtube_like_count`, `youtube_tags_raw`) after controlled tests

## What Should Not Be Modified (Without Change Window)
- Core VidMov theme/plugin code
- WP Automatic core plugin code
- Active production campaign logic outside scoped fields
- Cloudflare/Bunny settings without rollback plan

## Recovery Procedures

### Pause Arcana imports safely
1. Set campaign post status to `draft` for `8089`/`8090`.
2. Confirm no new `wp_automatic_camp` posts are created.

### Roll back metadata phase 1 fields
1. Remove custom field mappings from campaign config:
- `channel_id`
- `channel_title`
- `youtube_publish_date`
2. Save campaign.
3. Run one controlled import and validate baseline behavior.

### Validate platform after recovery action
- Arcana video page returns `200`
- Arcana category archive returns `200`
- Imported video renders with working thumbnail/player
- Campaign logs show normal processing

## Operator Notes
- Production is currently stable.
- Keep discovery surfaces public.
- Reserve ownership and monetization controls for authenticated workflows.
