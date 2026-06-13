# OffKilter Arcana Plugin

Plugin slug: `offkilter-arcana`
Version: `0.1.0`

## Implemented Subsystems
- CPT: `arcana_entry`
- Taxonomies: `arcana_category`, `arcana_tag`
- Admin menu page: `Arcana`
- Import queue table: `wp_okarcana_import_queue`
- Queue states: `imported`, `queued`, `scheduled`, `published`
- Scheduling engine: 3-5 posts/day distributed over 90 days with randomized times
- wp-cron integration: `okarcana_schedule_queue` every 15 minutes
- Import sources:
  - Manual YouTube URL input
  - CSV upload
  - Google Takeout file upload (URL extraction)
- Disclaimer injection on all `arcana_entry` pages
- wpForo topic creation attempt on publish with bidirectional link metadata

## Install
1. Copy `plugins/offkilter-arcana` into your WordPress `wp-content/plugins` directory.
2. Activate `OffKilter Arcana` in WordPress admin.
3. Open `Arcana` in wp-admin and run imports.

## Import Notes
- Metadata-only ingestion.
- No video file downloads/rehosting.
- YouTube embeds are used for playback.

### CSV Recommended Fields
- `youtube_url` (required)
- `title` (optional)
- `description` (optional)
- `channel_name` (optional)
- `channel_url` (optional)
- `publish_date` (optional)
- `thumbnail_url` (optional)
- `source_playlist` (optional)
- `source_playlist_id` (optional)

## Classification Rules
- Source name contains `Prem and Outcome 2026` -> default categories `Premonitions` + `Outcomes` under `The Arcana`
- Source name contains `Poem` -> default category `Poems` under `The Arcana`

## Disclaimer
The plugin auto-appends Arcana disclaimer text to Arcana entries.

Primary source file:
- `arcana_disclaimer.md`

## wpForo Link Meta
On successful topic creation:
- `_arcana_wpforo_topic_id`
- `_arcana_wpforo_topic_url`

## Known Constraints
- wpForo API signatures vary by version; plugin includes best-effort integration with failure metadata fallback (`_arcana_wpforo_error`).
- oEmbed-based metadata does not always include publish date/duration/description.
