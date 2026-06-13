# WP Automatic Campaign Config - Poem Playlist

Date: 2026-06-13
Environment: production `offkilter.tv`
Objective: define Poem campaign values if/when a Poem playlist is confirmed in the authenticated account.

Verification source: authenticated YouTube session (`/feed/playlists`) on 2026-06-13.

## Recommended Campaign Type
- WP Automatic campaign type: `Youtube`
- Source mode: `Playlist`

## Required Inputs (Exact Values)

Current state: `Poem` playlist was not found in `Owned` or `Saved` playlists for the authenticated account (Find-in-page `Poem` returned `0/0`).

Scope note:
- Current primary Arcana ingestion sources are `Prem` and `Outcome2026`.
- Poem remains optional and blocked until a verified playlist URL/ID exists.

| Field (WP Automatic UI) | Value |
|---|---|
| Campaign Name | `Poem Playlist -> VidMov` |
| Campaign Type | `Youtube` |
| Source URL (Playlist) | `N/A - Poem playlist not currently present in account` |
| Playlist URL Format | `https://www.youtube.com/playlist?list=PLAYLIST_ID` |
| Target Post Type | `vidmov_video` |
| Imported Post Status (first validation run) | `draft` |
| Imported Post Status (steady-state) | `publish` |
| Post Author | `1 (user / OKTV)` |
| Category Taxonomy | `vidmov_video_category` |
| Category Assignment | `968 (YouTube Embedded Library)`, `2053 (OffKilter.TV Creator - Free To View)`, plus child `Poem` (create under parent `968` if missing) |
| Update Every | see schedule table below |
| Update Unit | `minutes` |
| Duplicate Prevention | keep duplicate/cache protection enabled (same defaults as active campaigns: `OPT_CACHE`, `OPT_CACHE_CLEAN`) |
| Import Per Run | `1` |
| Campaign Cap (`camp_post_every`) | `2000` (matches active pattern) |

## Playlist Identity (Verified)

| Field | Value |
|---|---|
| Playlist Name | `Poem` |
| Playlist URL | `Not found` |
| Playlist ID | `Not found` |
| Video Count | `Not found` |
| Visibility | `Not found` |

This campaign cannot be created as copy/paste until the `Poem` playlist exists and its URL/ID are confirmed.

## Drip Schedule Values

Use `Update Unit = minutes` and set `Update Every` to:

| Target Cadence | Update Every |
|---|---:|
| 1 video/day | `1440` |
| 3 videos/day | `480` |
| 5 videos/day | `288` |

## Publish Mode Recommendation

Least-risk rollout:
1. First run: `draft` + import limit `1`.
2. Validate one imported `vidmov_video` appears correctly.
3. Switch post status to `publish`.
4. Keep `1/day` for first 7 days, then raise to `3/day` if quality/moderation are stable.

## Verification Checklist
1. Campaign appears in WP Automatic list and can be triggered.
2. New post created with `post_type = vidmov_video`.
3. Categories applied under `vidmov_video_category`.
4. Video appears in VidMov listing after publish mode enabled.
5. No duplicate posts for the same YouTube URL/video ID.
