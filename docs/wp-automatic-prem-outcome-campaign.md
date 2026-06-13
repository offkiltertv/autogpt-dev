# WP Automatic Campaign Config - Prem and Outcome 2026

Date: 2026-06-13
Environment: production `offkilter.tv`
Objective: import Prem/Outcome playlist content directly into VidMov (`vidmov_video`) with minimal risk.
Verification source: authenticated YouTube session (`/feed/playlists`) on 2026-06-13.

## Arcana Scope (Confirmed)
- Arcana content silo includes tarot, premonition, outcome/prediction, symbolism, and synchronicity content.
- `Prem` and `Outcome2026` are **Approved Arcana Sources**.
- Playlist inclusion is the editorial approval mechanism.
- No per-item additional approval required.
- No forum topic requirement for ingestion/publishing.

## Reality From Current Inventory
Current source inventory is split into two playlists (not one combined playlist):
- `Prem`: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` (Private, 1,168 videos)
- `Outcome2026`: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` (Private, 179 videos)

Recommended implementation: create two campaigns (one per playlist) for clean control and predictable pacing.

## Recommended Campaign Type
- WP Automatic campaign type: `Youtube`
- Source mode: `Playlist`

## Campaign A - Prem

| Field (WP Automatic UI) | Value |
|---|---|
| Campaign Name | `Prem Playlist -> VidMov` |
| Campaign Type | `Youtube` |
| Source URL (Playlist) | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` |
| Target Post Type | `vidmov_video` |
| Imported Post Status (first validation run) | `draft` |
| Imported Post Status (steady-state) | `publish` |
| Post Author | `1 (user / OKTV)` |
| Category Taxonomy | `vidmov_video_category` |
| Category Assignment | `968 (YouTube Embedded Library)`, `2053 (OffKilter.TV Creator - Free To View)`, plus Arcana category path in `vidmov_video_category` (recommended: `Arcana > Premonitions`) |
| Duplicate Prevention | keep duplicate/cache protection enabled (`OPT_CACHE`, `OPT_CACHE_CLEAN`) |
| Import Per Run | `1` |
| Campaign Cap (`camp_post_every`) | `2000` |

## Campaign B - Outcome2026

| Field (WP Automatic UI) | Value |
|---|---|
| Campaign Name | `Outcome2026 Playlist -> VidMov` |
| Campaign Type | `Youtube` |
| Source URL (Playlist) | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` |
| Target Post Type | `vidmov_video` |
| Imported Post Status (first validation run) | `draft` |
| Imported Post Status (steady-state) | `publish` |
| Post Author | `1 (user / OKTV)` |
| Category Taxonomy | `vidmov_video_category` |
| Category Assignment | `968 (YouTube Embedded Library)`, `2053 (OffKilter.TV Creator - Free To View)`, plus Arcana category path in `vidmov_video_category` (recommended: `Arcana > Outcomes`) |
| Duplicate Prevention | keep duplicate/cache protection enabled (`OPT_CACHE`, `OPT_CACHE_CLEAN`) |
| Import Per Run | `1` |
| Campaign Cap (`camp_post_every`) | `2000` |

## Drip Schedule Values

Use `Update Unit = minutes` and set `Update Every` to:

| Target Cadence | Update Every |
|---|---:|
| 1 video/day | `1440` |
| 3 videos/day | `480` |
| 5 videos/day | `288` |

Note:
- Values above are per campaign.
- If both campaigns run at the same cadence, total Arcana output doubles (for example, both at `1/day` = `2 Arcana videos/day` combined).

## Publish Mode Recommendation

Least-risk rollout:
1. Start both campaigns as `draft` with `1` item per run.
2. Validate one Prem and one Outcome import.
3. Switch both campaigns to `publish`.
4. Start at `1/day` each, then increase based on moderation and homepage quality.

## Minimal Arcana Architecture (No New Plugin Requirement)

Prem Playlist
-> WP Automatic
-> `vidmov_video`
-> Arcana category

Outcome2026 Playlist
-> WP Automatic
-> `vidmov_video`
-> Arcana category

## If You Intend a Single Combined "Prem and Outcome 2026" Campaign
Use the same field set above, but source URL is currently unavailable because an exact combined playlist was not found in the authenticated account.

No Arcana plugin involvement is required for this ingestion path.

## Playlist Identity (Verified)

| Playlist Name | Playlist URL | Playlist ID | Video Count | Visibility |
|---|---|---|---:|---|
| Prem | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | `PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | 1,168 | Private |
| Outcome2026 | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | `PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | 179 | Private |

Combined-name check:
- `Prem and Outcome 2026` exact playlist name: not found in authenticated account.
