# Arcana Launch Readiness

Date: 2026-06-13 (updated after live playlist activation)

## Decision
Status: **Production Active**

## Production State
- Arcana campaigns are live and importing through existing stack:
  - YouTube Playlist -> WP Automatic -> `vidmov_video` -> VidMov frontend
- Active Arcana campaigns:
  - `8089` Arcana Outcome2026
  - `8090` Arcana Prem
- First playlist-sourced production posts:
  - `8095` (Premonitions)
  - `8096` (Outcomes)

## What Is Proven In Production
- VidMov-compatible post creation (`post_type=vidmov_video`) works.
- Core video meta is populated (`beeteam368_video_url`, `beeteam368_video_mode=pro`, thumbnails).
- Category routing works:
  - `8095`: Arcana + Premonitions + YouTube Embedded Library
  - `8096`: Arcana + Outcomes + YouTube Embedded Library
- Campaign provenance works (`wp_automatic_camp=8090/8089`).
- Search/category visibility works.
- Single video pages return `HTTP 200`.

## Remaining Operational Constraints
- Homepage modules are not guaranteed to feature newly imported Arcana posts immediately, because imports keep original YouTube publish dates.
- Current top navigation has no Arcana menu entry.
- Main menu still includes legacy channel-tab URLs pointing to `34.105.65.179` (technical debt; unrelated to import pipeline but should be cleaned).

## Current Risk Summary
- Import path risk: Low.
- Cadence misconfiguration risk (inventory burn too fast): Medium.
- Discovery/placement risk without dedicated Arcana entry points: Medium.

## Immediate Operating Recommendation
- Keep campaign cadence conservative initially (1/day/source).
- Monitor import logs, duplicate cache behavior, and category archive growth daily.
- Use Arcana category archives as canonical landing surfaces until/if homepage/menu exposure is expanded.
