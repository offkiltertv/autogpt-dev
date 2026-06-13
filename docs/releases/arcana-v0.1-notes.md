# Arcana v0.1 Release Notes

Tag: `arcana-v0.1`
Date: 2026-06-13

## Highlights
- Established production Arcana ingestion pipeline via existing stack:
  - YouTube -> WP Automatic -> VidMov
- Activated creator metadata foundation on Arcana campaigns:
  - `channel_id`
  - `channel_title`
  - `youtube_publish_date`
- Validated public discovery surfaces for Arcana videos, archives, and creators.
- Validated imported comment flow on Arcana content.
- Completed operational and ownership strategy documentation.

## Included Operational IDs
- Arcana campaigns: `8089`, `8090`
- Golden baseline campaign: `4206`

## Known Gaps (Planned)
- Metadata phase 2 fields (`youtube_like_count`, `youtube_tags_raw`) pending test-first rollout.
- Homepage Arcana rail + Featured Readers not yet executed in production.
- Legacy menu links to `34.105.65.179` pending cleanup.

## Stability
- Production remained stable during this sprint.
- No new plugin architecture introduced.
- Existing ingestion path preserved.
