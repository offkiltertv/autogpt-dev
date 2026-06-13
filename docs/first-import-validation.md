# First Import Validation (Live)

Date: 2026-06-13

## Live Test Campaign
- Campaign ID: `8092`
- Name: `Arcana Outcome2026 TEST Single Video`
- Mode: YouTube ID (`YT_ID`)
- Test video: `boVS-RdIL_k`

## Execution
Triggered:
- `https://www.offkilter.tv/?wp_automatic=cron&id=8092`

Result:
- New post created: `8093`
- URL: `https://www.offkilter.tv/video/%f0%9f%98%aeyou-wont-believe-what-they-finally-admitted-tarot-reading/`
- HTTP: `200`

## Validation Matrix
- `post_type=vidmov_video`: PASS
- `post_status=publish`: PASS
- featured image created (`_thumbnail_id=8094`): PASS
- player source meta (`beeteam368_video_url=https://www.youtube.com/watch?v=boVS-RdIL_k`): PASS
- player mode (`beeteam368_video_mode=pro`): PASS
- campaign linkage (`wp_automatic_camp=8092`): PASS
- creator profile linkage (author `FOOD_FOR_THOUGHT_313`): PASS
- category assignment (`Arcana`, `Outcomes`, `YouTube Embedded Library`): PASS
- comments imported from YouTube into `wp_comments`: PASS
- Arcana archive visibility (`/video-category/arcana/`): PASS
- search visibility (`?s=finally+admitted&post_type=vidmov_video`): PASS

## Note On Homepage Visibility
This import preserved YouTube publish date (`2026-04-20`), so homepage surfacing depends on theme module ordering by date/trending/featured, not guaranteed immediate top placement.

## Post-Test Safety Actions
Set campaign posts to `draft`:
- `8089`, `8090`, `8092`
