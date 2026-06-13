# WP Automatic Live Audit (OffKilter.TV)

Date: 2026-06-13 08:09 UTC
Environment: `oktv-main-deployment-vm` (`offkilter-tv`, `us-west1-a`)
Site: `https://www.offkilter.tv`
Method: read-only WP-CLI + SQL audit on production

## Executive Summary
- WP Automatic is active and importing into VidMov now.
- The required `Poem` and `Prem and Outcome 2026` playlist campaigns do **not** exist.
- Active imports are currently channel-based campaigns (`@DaniElleLuminati`, `@Lipps`, `@LC`).
- Shortest path to Poem videos on OffKilter: add one new WP Automatic YouTube playlist campaign targeting `vidmov_video`.
- Arcana plugin is **not required** for this ingestion workflow.

## A. Existing Campaigns

### Totals
- Total campaigns in `wp_automatic_camps`: **18**
- Active (processing recently + campaign post status `publish`): **3** (`4168`, `4206`, `4708`)
- Paused (campaign post status `draft`): **1** (`6986`)
- Stale/orphan legacy entries (no `wp_automatic` campaign post + no recent processing): **14**

### Campaign Inventory

Notes:
- `Schedule` values below are raw WP Automatic values from campaign payload (`cg_update_every` + `cg_update_unit`).
- `Source URL` is from `camp_yt_user` when available.

| ID | Campaign Name | Type | Source URL | Schedule (every/unit) | Target Post Type | Import Post Status | Imported | Published | Draft | Last Processing | Last Posted | Status |
|---:|---|---|---|---|---|---|---:|---:|---:|---|---|---|
| 3855 | BSpears Music Videos | Youtube | (not set) | 60 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-04 21:58:21 | stale-orphan |
| 3874 | Britney Spears Vevo | Youtube | (not set) | 1 / 60 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-05 19:52:29 | stale-orphan |
| 3878 | Bob Farrell YT | Youtube | https://www.youtube.com/channel/UCO8e00B1t6X6Taaz1B849Zg | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | - | stale-orphan |
| 3879 | @Bob-Farrell | Youtube | https://www.youtube.com/channel/UCO8e00B1t6X6Taaz1B849Zg | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-04 23:33:01 | stale-orphan |
| 3917 | @bob-farrell | Youtube | https://www.youtube.com/channel/UCO8e00B1t6X6Taaz1B849Zg | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-05 01:14:11 | stale-orphan |
| 4049 | @DaniElleLuminati | Youtube | https://www.youtube.com/@DaniElleLuminati/ | 60 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-08-30 13:33:11 | stale-orphan |
| 4127 | @Lipps5 | Youtube | https://www.youtube.com/@DaniElleLuminati/ | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | - | stale-orphan |
| 4128 | @DaniElleLuminati | Youtube | https://www.youtube.com/@DaniElleLuminati/ | 60 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | - | stale-orphan |
| 4139 | @LincolnCaine | Youtube | https://www.youtube.com/@ShaniM4/streams | 60 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-18 17:19:15 | stale-orphan |
| 4143 | @Leafy | Single | (not set) | 60 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-07-19 15:42:41 | stale-orphan |
| 4168 | @DaniElleLuminati | Youtube | https://www.youtube.com/@DaniElleLuminati | 1 / 1 | vidmov_video | publish | 61 | 61 | 0 | 2026-06-13 08:06:29 | 2026-06-13 03:01:54 | active |
| 4185 | @bob-farrell | Youtube | https://www.youtube.com/@bob-farrell | 5 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-09-14 03:31:39 | stale-orphan |
| 4206 | @Lipps | Youtube | https://www.youtube.com/@Lipps5 | 1 / 1 | vidmov_video | publish | 287 | 287 | 0 | 2026-06-13 08:05:25 | 2026-06-13 08:05:26 | active |
| 4708 | @LC | Youtube | https://www.youtube.com/@ShaniM4 | 1 / 1 | vidmov_video | publish | 213 | 212 | 1 | 2026-06-13 08:04:23 | 2026-06-13 04:09:47 | active |
| 4711 | @diffiCULTreSEARCH | Youtube | https://www.youtube.com/@diffiCULTreSEARCH | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2023-09-22 05:20:58 | stale-orphan |
| 5930 | @diffiCULTreSEARCH | Youtube | https://www.youtube.com/@diffiCULTreSEARCH | 1 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2024-04-15 12:32:35 | stale-orphan |
| 6986 | Bob Farrell | Youtube | https://www.youtube.com/@Bob-Farrell | 1000 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2024-05-16 21:30:20 | paused-draft |
| 7101 | @diffiCULTresearch | Youtube | https://www.youtube.com/@diffiCULTresearch | 10 / 1 | vidmov_video | publish | 0 | 0 | 0 | - | 2024-04-15 13:08:21 | stale-orphan |

## B. Missing Campaigns

No campaign matched the following (name/source/config search):
- `Poem`
- `Prem and Outcome 2026`

Result:
- There is currently **no WP Automatic ingestion path** for those playlists.

## C. Broken / Non-Importing Campaigns

### Active and importing
- `4168` `@DaniElleLuminati`
- `4206` `@Lipps`
- `4708` `@LC`

### Paused
- `6986` `Bob Farrell` (`wp_automatic` post status `draft`)

### Stale/orphan
- 14 campaign rows are present but not currently scheduled/processing (no linked `wp_automatic` campaign post and no recent processing timestamps).

### Failures
- No recent WP Automatic log entries with `error` in `action` or `data` were found.

## VidMov Target Verification

## 1) VidMov video post type
- Slug: `vidmov_video`
- WP Automatic active campaigns already target this slug.

## 2) Taxonomy structure for videos
- Primary category taxonomy: `vidmov_video_category`
- Tag taxonomy: `post_tag`

## 3) Existing category structure in use
- Root: `OffKilter.TV Creator - Free To View` (count 560)
- Root: `YouTube Embedded Library` (count 560)
- Child categories currently used by active imports:
  - `@DaniElleLuminati` (count 61)
  - `@lipps` (count 287)
  - `@LC` (count 212)

## D. Recommended Configuration for `Poem` Playlist

Do **not** create yet (planning only). Minimum required config:

1. Campaign type: `Youtube`
2. Source: playlist URL for `Poem`
   - `https://www.youtube.com/playlist?list=<POEM_PLAYLIST_ID>`
3. Target post type: `vidmov_video`
4. Imported post status:
   - `draft` for first validation run
   - then `publish` for live operation
5. Category assignment (`vidmov_video_category`):
   - keep `YouTube Embedded Library`
   - keep `OffKilter.TV Creator - Free To View`
   - add/create one dedicated child (recommended): `Poem`
6. Duplicate protection: keep WP Automatic duplicate handling enabled.
7. Thumbnail/embed handling: keep current default behavior (already proven on active campaigns).

## E. Recommended Drip Schedule

Use one post per run from the playlist campaign.

- 1 video/day:
  - run every 24 hours
  - lowest moderation load, longest content runway
- 3 videos/day:
  - run every 8 hours
  - balanced growth for traffic + engagement
- 5 videos/day:
  - run every 4.8 hours
  - faster indexation, higher moderation/community load

Recommended starting point for `Poem`: **1/day for 7 days**, then increase to **3/day** if quality and moderation remain stable.

## Arcana Requirement (Explicit)

Desired workflow can be achieved with existing stack **without Arcana**:

YouTube Playlist
-> WP Automatic
-> VidMov (`vidmov_video`)

Arcana is optional enhancement only; it is not required to make Poem playlist videos appear on OffKilter.TV.

## Shortest Path to Restore Poem Ingestion

1. Create one new WP Automatic YouTube campaign for `Poem` playlist.
2. Target `vidmov_video`.
3. First run in `draft` mode to confirm import + category assignment.
4. Switch to `publish` and set drip interval (start 1/day).
5. Monitor WP Automatic log for `Processing Campaign:<new_id>` and `Posted:<new_id>` entries.
