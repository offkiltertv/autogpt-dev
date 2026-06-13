# Arcana Production Operations

Date: 2026-06-13

## 1. Production Pipeline
- Ingestion engine: WP Automatic
- Content post type: `vidmov_video`
- Presentation layer: VidMov
- Arcana routing: `vidmov_video_category` terms

Workflow:
- Playlist -> Campaign (`8089`/`8090`) -> `vidmov_video` -> Arcana categories -> frontend archives/search

## 2. Source & Campaign Registry
- Campaign `8089`: Arcana Outcome2026
  - Playlist URL: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`
  - Playlist ID: `PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`
  - Categories: `2258,2260,968,2055` (Arcana, Outcomes, YouTube Embedded Library, @lipps)
- Campaign `8090`: Arcana Prem
  - Playlist URL: `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
  - Playlist ID: `PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
  - Categories: `2258,2259,968,2055` (Arcana, Premonitions, YouTube Embedded Library, @lipps)

Campaign mode:
- `OPT_YT_PLAYLIST=yes`
- `OPT_YT_USER=no`
- Duplicate controls enabled: `OPT_CACHE`, `OPT_CACHE_CLEAN`
- Schedule baseline currently configured to 1440x1 (daily)

## 3. Post Verification (8095, 8096)
Compared against Golden Campaign imports (`4206`) and published `vidmov_video` baseline.

### 3.1 Match Status
- Thumbnail: PASS (`_thumbnail_id` assigned)
- Creator profile: PASS (author user auto-created, channel links rendered)
- Membership badge: PASS (`Free Membership`, plan id `1`)
- Related videos/suggestions: PASS (suggestion items rendered)
- Categories/taxonomy: PASS (Arcana + child category + shared library/creator terms)
- Search visibility: PASS (both posts appear in video search)
- Category archive visibility: PASS (`/video-category/arcana/`, `/premonitions/`, `/outcomes/`)
- Mobile rendering baseline: PASS (same VidMov template, viewport meta present, responsive srcset)

### 3.2 Differences Observed
- Homepage visibility: NOT IMMEDIATE
  - `8095`/`8096` are not currently in homepage featured blocks.
  - Cause: homepage modules are curated/query-driven; imported posts retain original YouTube publish date.
- Menu exposure: NO dedicated Arcana nav item currently.
- Legacy nav debt found:
  - main menu includes channel-tab links to `http://34.105.65.179/...` (should be remediated separately).

## 4. Campaign Health Audit (8089, 8090)
- Campaign status: `publish`
- Campaign post type target: `vidmov_video`
- Last run marker (`last_update`): present and updating
- Playlist tracking token meta: present (`wp_automatic_yt_nt_3389dae361af79b04c9c8e7057f60cc6`)
- Import count so far:
  - `8089`: 1 post (`8096`)
  - `8090`: 1 post (`8095`)
- Duplicate prevention behavior:
  - duplicate URLs detected and skipped in run output (e.g., existing `8093`)
- Error handling:
  - no active error state in recent log slice for Arcana campaign runs
  - successful `User >> Processing Campaign` and `Posted:<camp_id>` events observed

## 5. Visibility Surfaces
- Homepage: Arcana posts not guaranteed in top modules.
- Trending page (`/trending/`): depends on trending counters; not immediate for new imports.
- Search: confirmed visible by targeted searches.
- Category archives: confirmed visible and reliable for Arcana discovery.

## 6. Cadence Analysis (Inventory: 1327)
Assuming 2 Arcana sources (`Prem`, `Outcome2026`).

- 1/day/source => 2/day total
  - lifespan: `1327 / 2 = 663.5` days (~21.8 months)
- 2/day/source => 4/day total
  - lifespan: `1327 / 4 = 331.8` days (~10.9 months)
- 4/day/source => 8/day total
  - lifespan: `1327 / 8 = 165.9` days (~5.5 months)
- 1/hour/source => 48/day total
  - lifespan: `1327 / 48 = 27.6` days

## 7. Recommended Operating Cadence
Primary recommendation: **1/day/source** for first 30 days.

Why:
- Best inventory longevity
- Better moderation load control
- Stable SEO index growth without content shock
- Enough daily freshness (2 Arcana videos/day)

Scale plan:
1. Days 1-30: 1/day/source
2. Days 31-90: increase to 2/day/source only if:
   - import success rate > 98%
   - no duplicate spike
   - forum/comment moderation stable
3. Avoid 4/day/source or hourly mode unless running short campaign windows

## 8. Homepage / Navigation Recommendation
Without redesigning systems:
- Add a dedicated menu item to `/video-category/arcana/` for discoverability.
- Keep Arcana category archive as canonical index until homepage modules are intentionally adjusted.

## 9. Monitoring Checklist (Daily)
1. Campaign status remains `publish` (`8089`, `8090`).
2. `last_update` advances.
3. New post count increments by expected cadence.
4. New posts have:
   - `_thumbnail_id`
   - `beeteam368_video_url`
   - `wp_automatic_camp`
   - expected Arcana categories
5. Search and category archive visibility still present.
6. `wp_automatic_log` has no repeated Arcana errors.

## 10. Recovery Workflow
If Arcana import fails:
1. Check playlist accessibility (unlisted/public reachable).
2. Manually run one campaign:
   - `https://www.offkilter.tv/?wp_automatic=cron&id=8089`
   - `https://www.offkilter.tv/?wp_automatic=cron&id=8090`
3. Inspect `wp_automatic_log` for latest campaign events.
4. Confirm post creation and metadata.
5. If needed, set campaign post to `draft` to halt imports while triaging.

## 11. Scaling Workflow
When ready to scale:
1. Increase `cg_update_every` carefully (campaign-level schedule), one campaign at a time.
2. Validate for 48 hours before applying same cadence to second campaign.
3. Track inventory burn-down weekly against target horizon (90+ days).

