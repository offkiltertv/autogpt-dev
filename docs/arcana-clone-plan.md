# Arcana Clone Plan (Using Existing Workflow)

Date: 2026-06-13

## Objective
Use existing Golden Campaign structure to ingest Arcana sources into `vidmov_video` with no new importer/plugin workflow.

## Clones (Created)
- `8089` Arcana Outcome2026
  - Source playlist: `PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`
  - Categories: `2258,2260,968,2055`
- `8090` Arcana Prem
  - Source playlist: `PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
  - Categories: `2258,2259,968,2055`

## Root Cause Found
Both source playlists are private. YouTube Data API key mode cannot fetch private playlists, causing:
- `Youtube Error: The playlist identified with the request's playlistId parameter cannot be found.`

## Recovery Path
1. Keep Arcana campaigns cloned (done).
2. Change source playlists to `Unlisted` or `Public` in YouTube.
3. Publish campaigns (`wp_automatic` posts) and run one campaign manually:
   - `https://www.offkilter.tv/?wp_automatic=cron&id=8089`
4. Confirm one new `vidmov_video` with:
   - thumbnail
   - `beeteam368_video_url`
   - Arcana taxonomy assignment
5. Set cadence.

## Temporary Validation Path (Executed)
- Created `8092` in `YT_ID` mode with one public video ID for a no-risk single-item live validation.
