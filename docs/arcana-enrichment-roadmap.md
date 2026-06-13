# Arcana Enrichment Roadmap

Date: 2026-06-13
Scope: Production-safe rollout planning only (no production edits applied)
Reference: `docs/arcana-metadata-enrichment.md`
Campaigns in scope: `4206`, `8089`, `8090`

## Phase 1: Existing Field Inventory

## Current custom field mappings (all 3 campaigns)
- `beeteam368_video_url = [source_link]`
- `beeteam368_video_mode = pro`
- `beeteam368_video_formats = auto`
- `beeteam368_video_formats_preview = auto`
- `beeteam368_video_ratio = 16:9`
- `beeteam368_views_counter_totals = [vid_views]`

## Current enabled YouTube options (all 3 campaigns)
- `OPT_YT_COMMENT` (comments import)
- `OPT_YT_TAG` (YouTube tags as WP tags)
- `OPT_YT_ORIGINAL_TIME` (original publish time)
- `OPT_YT_AUTHOR` (set post author if display-name match exists)
- `OPT_THUMB` (thumbnail import)

## Unused available YouTube fields
Available in WP Automatic but not currently persisted as dedicated custom fields:
- `[vid_author]`
- `[vid_author_title]`
- `[vid_likes]`
- `[vid_tags]` (raw payload)
- `[vid_time]` / `[vid_date]`

## Phase 2: Creator Metadata Design

## Planned metadata keys
- `channel_id = [vid_author]`
- `channel_title = [vid_author_title]`
- `youtube_like_count = [vid_likes]`
- `youtube_publish_date = [vid_time]`
- `youtube_tags_raw = [vid_tags]`
- Recommended companion: `channel_url = https://www.youtube.com/channel/[vid_author]`

## Field-to-feature mapping
| Field | Creator Archives | Creator Directory | Featured Readers | Ownership Workflow |
|---|---|---|---|---|
| `channel_id` | Canonical grouping key | De-dup key | Stable slot identity | Primary claim key |
| `channel_title` | Display attribution | Card/display name | Reader card label | Human verification aid |
| `youtube_like_count` | Engagement signal | Ranking input | Sort/weight input | Evidence only (not identity) |
| `youtube_tags_raw` | Topic context | Interest clustering | Theme-based curation | Context for reviewer |
| `youtube_publish_date` | Timeline ordering | Freshness signal | Recency emphasis | Provenance timestamp |
| `channel_url` | Attribution link | External profile link | Trust signal | Manual proof/validation link |

## Phase 3: Historical Backfill Analysis

## Can existing Arcana videos be enriched without re-importing?
Short answer: **Not fully**.

Reason:
- Historical Arcana posts do not currently store `channel_id`, `channel_title`, `youtube_like_count`, or `youtube_tags_raw`.
- Those values are available during WP Automatic ingestion, but are not retroactively stored unless a post is reprocessed through campaign logic.

## What can be backfilled without re-import?
- `youtube_publish_date`: partially inferable from current `post_date` because original-time import is already enabled.
- `channel_url`: only if `channel_id` exists (currently missing on historical Arcana posts).

## Safest backfill strategy
1. Enable new metadata fields on `8089` and `8090` first.
2. Validate on newly imported posts only (draft-first validation run).
3. Build forward from that point as source of truth.
4. Defer historical backfill to a separate controlled operation after outreach wave 1 readiness.

Recommended historical policy now:
- Do not mass re-import old playlist items.
- Do not disable duplicate protection in production campaigns.

## Phase 4: Comment Ecosystem Analysis

Current Arcana validation sample:
- Post `8095`: 33 imported comments
- Post `8096`: 49 imported comments
- Authors appear as YouTube-style handles and preserve historical timestamps.

## How comments strengthen creator identity
- Credibility: visible third-party audience interaction on imported videos.
- Social proof: comment volume and persistence show source-channel engagement.
- Channel richness: creator pages can show discussion depth beyond view counts.

Operational use:
- Use comment count as a soft quality signal in Featured Readers selection.
- Keep comments import enabled on Arcana campaigns.

## Phase 5: Implementation Roadmap

## A) Safe Immediately
- `channel_id = [vid_author]`
- `channel_title = [vid_author_title]`
- `youtube_publish_date = [vid_time]`

Why: deterministic source metadata, low UI risk, high identity value.

## B) Requires Testing
- `youtube_like_count = [vid_likes]`
- `youtube_tags_raw = [vid_tags]`

Why: value presence/format may vary by video; needs field-length and null behavior validation.

## C) Requires Review
- `channel_url = https://www.youtube.com/channel/[vid_author]`

Why: needs fallback rule when `channel_id` is missing/blank.

## Rollout sequence (production-safe)
1. Configure fields in Arcana campaigns (`8089`, `8090`) only.
2. Run one controlled import per campaign with verification checklist:
- post created
- new meta fields present
- no VidMov rendering regression
- no duplicate behavior change
3. If pass, mirror same field set to Golden baseline campaign (`4206`) for consistency.
4. Monitor 7 days before any historical-backfill initiative.

## Success criteria
- New Arcana imports carry creator-centric metadata (`channel_id`, `channel_title`).
- Creator directory and Featured Readers can key off stable channel identity.
- No changes to existing ingestion architecture.
- Metadata readiness achieved before homepage rollout and creator outreach expansion.
