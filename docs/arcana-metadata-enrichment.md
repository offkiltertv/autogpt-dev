# Arcana Metadata Enrichment

Date: 2026-06-13
Scope: WP Automatic metadata enrichment only (no plugin development, no production changes applied)
Reference: `docs/wp-automatic-capability-gap.md`
Campaigns:
- `4206` Golden Campaign (`@Lipps`)
- `8089` Arcana Outcome2026
- `8090` Arcana Prem

## Phase 1: Field Implementation Plan

## Current baseline (all 3 campaigns)
Existing mapped custom fields:
- `beeteam368_video_url = [source_link]`
- `beeteam368_video_mode = pro`
- `beeteam368_video_formats = auto`
- `beeteam368_video_formats_preview = auto`
- `beeteam368_video_ratio = 16:9`
- `beeteam368_views_counter_totals = [vid_views]`

Enabled options already present:
- `OPT_YT_COMMENT`
- `OPT_YT_TAG`
- `OPT_YT_ORIGINAL_TIME`
- `OPT_YT_AUTHOR`
- `OPT_THUMB`

## Required new custom field mappings
Add the following in WP Automatic campaign custom fields for each campaign (`4206`, `8089`, `8090`):

| Custom Field Key | WP Automatic Value | Purpose |
|---|---|---|
| `channel_id` | `[vid_author]` | Stable YouTube channel identifier |
| `channel_title` | `[vid_author_title]` | Human-readable channel name |
| `youtube_like_count` | `[vid_likes]` | Video popularity signal |
| `youtube_publish_date` | `[vid_time]` | Source publish timestamp |
| `youtube_tags_raw` | `[vid_tags]` | Source semantic/topic payload |

## Optional but recommended companion field
| Custom Field Key | WP Automatic Value | Purpose |
|---|---|---|
| `channel_url` | `https://www.youtube.com/channel/[vid_author]` | Direct attribution/verification link |

## Rollout method
Apply the same field set to all three campaigns for consistency:
1. Confirm campaign clone parity from Golden campaign baseline.
2. Add fields above to `4206`, `8089`, `8090`.
3. Run one controlled import in draft/test mode first.
4. Validate meta exists on imported post before scaling cadence.

## Phase 2: Creator Data Model

Flow:

Imported Video
-> Channel Metadata
-> Creator Profile
-> Claimable Channel

## Data mapping
| Layer | Inputs from WP Automatic | OffKilter use |
|---|---|---|
| Imported Video | title, description, thumbnail, `beeteam368_video_url`, views, comments | Core VidMov content object |
| Channel Metadata | `channel_id`, `channel_title`, `channel_url`, `youtube_tags_raw` | Canonical creator identity seed |
| Creator Profile | `channel_title` + profile assets + ownership state | Creator archive and directory identity |
| Claimable Channel | `channel_id` + verification workflow | Claim handoff and account linking |

## Minimum identity key strategy
- Primary external identity key: `channel_id`
- Display identity key: `channel_title`
- Attribution proof link: `channel_url`

This avoids relying on mutable video titles for creator mapping.

## Phase 3: Comment Import Validation

Validation sample (Arcana posts):
- Post `8095`: `33` comments
  - first: `2024-01-19 03:45:53`
  - last: `2025-08-20 17:51:38`
- Post `8096`: `49` comments
  - first: `2026-04-21 12:41:55`
  - last: `2026-05-09 13:14:49`

Observed characteristics:
- Comment authors appear as YouTube-style handles (for example `@Cheri1181`, `@Jay-j2l7t`).
- Comment timestamps reflect source-era timing, not just import execution time.
- Comment ingestion is operational and already useful as engagement/social-proof metadata.

## Phase 4: Creator Enrichment Uses

How new fields power creator-centric features:

1. Creator archives
- Group imported videos by `channel_id` (not only by post author).
- Show canonical channel attribution using `channel_title`.

2. Creator directories
- Rank or filter by `channel_title`, video count, and engagement fields.
- De-duplicate creator identities that share similar names by using `channel_id`.

3. Featured Readers
- Use `channel_id` as stable slot identity.
- Use `channel_title` for card display and `channel_url` for attribution verification.

4. Ownership workflows
- Claim workflow binds OffKilter account to `channel_id`.
- `channel_title` supports human review; `channel_url` supports manual validation.

## Phase 5: Implementation Safety Classification

| Proposed field | Classification | Reason |
|---|---|---|
| `channel_id = [vid_author]` | Safe Immediately | Read-only source metadata; no rendering dependency |
| `channel_title = [vid_author_title]` | Safe Immediately | Display-only metadata; low operational risk |
| `youtube_publish_date = [vid_time]` | Safe Immediately | Source timestamp capture; no schema change |
| `youtube_tags_raw = [vid_tags]` | Requires Testing | Payload size/format varies; verify storage and sanitization |
| `youtube_like_count = [vid_likes]` | Requires Testing | Source may return blank/zero; validate numeric handling |
| `channel_url = https://www.youtube.com/channel/[vid_author]` | Requires Review | Best when `channel_id` exists; review fallback behavior for missing IDs |

## Recommended sequence
1. Add `channel_id`, `channel_title`, `youtube_publish_date` first.
2. Validate one import per Arcana campaign (`8089`, `8090`).
3. Add `youtube_like_count` and `youtube_tags_raw` after metadata QA pass.
4. Add `channel_url` once missing-ID fallback rule is finalized.

## Outcome
This enrichment layer converts Arcana imports from video-only records into creator-addressable records using existing WP Automatic capabilities only, and sets clean groundwork for claim workflows without building new import infrastructure.
