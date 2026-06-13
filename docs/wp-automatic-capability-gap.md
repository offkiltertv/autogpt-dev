# WP Automatic Capability Gap Audit

Date: 2026-06-13
Environment: OffKilter production (`oktv-main-deployment-vm`) read-only audit
Campaigns audited:
- Golden Campaign: `4206` (`@Lipps`)
- Arcana Prem: `8090`
- Arcana Outcome2026: `8089`

## Sources
- Official WP Automatic documentation (YouTube campaign options and tags):
  - https://s3.amazonaws.com/valvepress/documentation/index.html
- Production plugin source:
  - `/opt/bitnami/wordpress/wp-content/plugins/wp-automatic/p_metabox.php`
  - `/opt/bitnami/wordpress/wp-content/plugins/wp-automatic/core.php`
- Production campaign and post metadata (`wp_automatic_camps`, `wp_posts`, `wp_postmeta`, `wp_comments`, taxonomy tables)

## 1) Capability Status Matrix (Enabled vs Used)

| Capability | Golden 4206 | Arcana 8089 | Arcana 8090 | Actually landing on posts? | Notes |
|---|---|---|---|---|---|
| Post YouTube comments as comments | ON (`OPT_YT_COMMENT`) | ON | ON | Yes | Comments exist on imported posts (`8095`, `8096`) with YouTube-style handles and historic timestamps. |
| Post YouTube tags as tags | ON (`OPT_YT_TAG`) | ON | ON | Partial | Works on some Golden imports (tagged posts found). Arcana sample posts currently have no `post_tag` terms. |
| Original publish date import | ON (`OPT_YT_ORIGINAL_TIME`) | ON | ON | Yes | Post dates reflect source video publish time behavior. |
| Author/channel metadata import | ON (`OPT_YT_AUTHOR`) | ON | ON | Partial | Sets `post_author` only when `vid_author_title` matches an existing WP user display name. |
| Thumbnail import | ON (`OPT_THUMB`) | ON | ON | Yes | `_thumbnail_id` present on imported posts. |
| View count import | Enabled via custom field map `[vid_views]` | Same | Same | Yes | Mapped into `beeteam368_views_counter_totals`. |
| Like count import | No direct map configured | No | No | No | `[vid_likes]` is available but not mapped to any saved meta field. |

## 2) Current Campaign Payload Findings

Common campaign template (all 3 campaigns):
- `camp_post_title`: `[original_title]`
- `camp_post_content`: runtime + description + source link only:
  - `[vid_duration]`
  - `[vid_desc]`
  - `[source_link]`
- Custom fields include:
  - `beeteam368_video_url = [source_link]`
  - `beeteam368_video_mode = pro`
  - `beeteam368_video_formats = auto`
  - `beeteam368_video_formats_preview = auto`
  - `beeteam368_video_ratio = 16:9`
  - `beeteam368_views_counter_totals = [vid_views]`

Gap: creator/channel fields (`[vid_author]`, `[vid_author_title]`), publish date tag (`[vid_date]`), likes (`[vid_likes]`), and raw tag payload (`[vid_tags]`) are not persisted as post meta.

## 3) Available YouTube Metadata Not Currently Used

From WP Automatic YouTube tag set and source code, the following are available but not currently persisted for Arcana posts:
- `[vid_author]` (YouTube channel ID)
- `[vid_author_title]` (channel title)
- `[vid_date]` (publish date string)
- `[vid_likes]` (like count)
- `[vid_tags]` (comma-separated tags when YT-tag option is active)
- `[channel_country]`, `[channel_country_name]` (available in tag list)

Current Arcana posts rely mostly on:
- title
- description
- source URL
- thumbnail
- views count
- imported comments

## 4) Creator Enrichment Feasibility

Question: can creator profiles be enriched using `vid_author`, `vid_author_title`, channel URL, imported comments, and imported tags?

Answer: **Yes, with current WP Automatic only** (no custom importer required), with these caveats:
- `vid_author` and `vid_author_title`: available now and can be saved to custom fields.
- Channel URL: derive from channel ID (`https://www.youtube.com/channel/[vid_author]`) and save as custom field.
- Imported comments: already flowing; useful for social proof and discussion seed.
- Imported tags: option is enabled; quality varies by source video and current tag-limit behavior.

Limitation:
- `OPT_YT_AUTHOR` does not create robust creator ownership by itself; it only assigns WP post author if a matching display name already exists.

## 5) Highest-Value Settings/Changes for Arcana (No New Plugin Work)

## Priority 1 (High value, low risk)
1. Add creator metadata custom fields in campaign mapping:
- `ok_youtube_channel_id = [vid_author]`
- `ok_youtube_channel_title = [vid_author_title]`
- `ok_youtube_channel_url = https://www.youtube.com/channel/[vid_author]`
- `ok_youtube_publish_date = [vid_date]`

2. Add likes and tags raw payload fields:
- `ok_youtube_like_count = [vid_likes]`
- `ok_youtube_tags_raw = [vid_tags]`

3. Add visible attribution block to post content template:
- `Channel: [vid_author_title]`
- link to `https://www.youtube.com/channel/[vid_author]`

## Priority 2 (Medium value, medium risk)
4. Review tag-volume behavior (`cg_tags_limit` currently `1`) and increase carefully for Arcana campaigns to improve search/discovery depth.

5. Keep `OPT_YT_AUTHOR` enabled, but standardize creator-account matching policy (display-name alignment) so assignment is predictable.

## Priority 3 (Optional)
6. Add country metadata (`[channel_country]`, `[channel_country_name]`) if geographic filtering/reporting is needed later.

## 6) Recommended Arcana Enablement Outcome

Fastest path to richer Arcana creator identity without new development:
- Keep current ingestion pipeline unchanged (`YouTube -> WP Automatic -> vidmov_video`).
- Persist channel identity fields (`vid_author`, `vid_author_title`, channel URL) as first-class post meta.
- Persist likes/tags payload for future ranking and creator profile enrichment.
- Maintain existing comment import for social proof and engagement signal.

This gives you richer creator/channel intelligence immediately while staying inside existing WP Automatic + VidMov operations.

## Reference Notes (Official Docs Alignment)
Official documentation confirms these YouTube options/tags exist, including:
- Post YouTube Tags as Tags
- Post YouTube Comments as Comments
- Add posts with original time
- YouTube tag placeholders such as `vid_author`, `vid_author_title`, `vid_likes`, `vid_date`

Source:
- https://s3.amazonaws.com/valvepress/documentation/index.html
