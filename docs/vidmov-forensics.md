# VidMov Production Forensics

Date: 2026-06-13
Environment: `oktv-main-deployment-vm` (`offkilter-tv` project)

## What VidMov Uses To Treat A Post As Video
- `post_type`: `vidmov_video`
- Required/standard meta keys seen on successful imports:
  - `beeteam368_video_url`
  - `beeteam368_video_mode` (`pro`)
  - `beeteam368_video_formats` (`auto`)
  - `beeteam368_video_formats_preview` (`auto`)
  - `beeteam368_video_ratio` (`16:9`)
  - `_thumbnail_id`
  - `wp_automatic_camp`
  - `original_link`
  - `canonical_url`
  - `beeteam368_views_counter_totals`
  - reaction counters: `beeteam368_reactions_*`

## How Player/Thumbnail/Creator Are Determined
- Player source URL: `beeteam368_video_url` (YouTube URL)
- Player mode/network handling: `beeteam368_video_mode=pro` (+ VidMov extension logic)
- Thumbnail: WordPress featured image via `_thumbnail_id` attachment
- Creator linkage: post author (`post_author`) and VidMov creator category terms (`vidmov_video_category`)
- Campaign provenance: `wp_automatic_camp` maps video -> WP Automatic campaign ID

## Top 10 Successful Imported Videos (Current)
By `beeteam368_views_counter_totals`:
1. `8093` (Arcana test) - 12,691 views - camp `8092`
2. `6802` - 12,266 views - camp `4206`
3. `7931` - 7,730 views - camp `4206`
4. `6744` - 4,977 views - camp `4168`
5. `6846` - 4,693 views - camp `4206`
6. `7149` - 4,437 views - camp `4206`
7. `6853` - 4,294 views - camp `4708`
8. `6783` - 3,987 views - camp `4206`
9. `6841` - 3,806 views - camp `4206`
10. `7611` - 3,662 views - camp `4168`

## Category Pattern On Working Imports
Common category set:
- Creator category (`@lipps`, `@LC`, etc.)
- `YouTube Embedded Library` (`term_id=968`)
- Free-view creator membership category (`OffKilter.TV Creator - Free To View`)

Arcana test post (`8093`) category set:
- `Arcana` (`2258`)
- `Outcomes` (`2260`)
- `YouTube Embedded Library` (`968`)
- `@lipps` (`2055`)

## Key Conclusion
The existing WP Automatic -> VidMov pipeline is the authoritative workflow. Arcana content can use the same path if campaign field mappings and taxonomy targets are compatible.
