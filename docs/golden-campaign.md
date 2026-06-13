# Golden Campaign Forensics

Date: 2026-06-13

## Selected Golden Campaign
- Campaign: `@Lipps`
- Campaign ID: `4206`
- Why selected: highest total imported views among active campaigns (`266,074`) with highest volume (`355` imported videos)

## Golden Campaign Core Settings
- Source type: `Youtube`
- Target post type: `vidmov_video`
- Imported post status: `publish`
- Campaign post status (active): `wp_posts.post_status=publish`
- Author: `17`
- Category assignment: `2053,968,2055`
- Order: `date`
- Frequency (campaign general): `cg_update_every=1`, `cg_update_unit=1` (1 minute)
- YouTube mode: channel/user (`OPT_YT_USER`)
- URL source: `https://www.youtube.com/@Lipps5`

## Golden Custom Field Mapping (Critical)
- `beeteam368_video_url` = `[source_link]`
- `beeteam368_video_mode` = `pro`
- `beeteam368_video_formats` = `auto`
- `beeteam368_video_formats_preview` = `auto`
- `beeteam368_video_ratio` = `16:9`
- `beeteam368_views_counter_totals` = `[vid_views]`

## Cloneability
Yes. Campaign can be cloned safely by copying campaign row + keeping identical custom field mapping and options, changing only:
- campaign name
- source mode/source value
- category assignment

## Arcana Clone Campaigns Created
- `8089` Arcana Outcome2026 (playlist mode)
- `8090` Arcana Prem (playlist mode)
- `8092` Arcana Outcome2026 TEST Single Video (YT_ID mode for first live recovery test)

## Current Safety State
- `8089`, `8090`, `8092` campaign posts are set to `draft` after live validation to prevent unattended imports.
