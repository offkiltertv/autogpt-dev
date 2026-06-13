# Arcana Metadata Activation

Date: 2026-06-13
Scope: Activation readiness assessment only (no production changes applied)
Reference: `docs/arcana-enrichment-roadmap.md`
Campaigns in scope: `8089` (Arcana Outcome2026), `8090` (Arcana Prem), baseline `4206` (Golden)

## Activation Decision
Arcana creator metadata enrichment is **ready for controlled production activation** with staged rollout.

Recommended status:
- `channel_id`: Safe to enable
- `channel_title`: Safe to enable
- `youtube_publish_date`: Safe to enable
- `youtube_like_count`: Test first
- `youtube_tags_raw`: Test first

No field in current scope is a hard `DO NOT ENABLE`, but two require controlled validation before broad rollout.

## Phase 1: Field Activation Review

## Current storage location
Current Arcana imports store baseline data in `wp_postmeta` for `vidmov_video` posts, including:
- `beeteam368_video_url`
- `beeteam368_video_mode`
- `beeteam368_video_formats`
- `beeteam368_video_ratio`
- `beeteam368_views_counter_totals`

The proposed creator fields are **not currently stored** as dedicated post meta keys.

## Target storage location
All proposed fields should be saved to `wp_postmeta` on imported `vidmov_video` posts via campaign custom fields:
- `channel_id = [vid_author]`
- `channel_title = [vid_author_title]`
- `youtube_like_count = [vid_likes]`
- `youtube_publish_date = [vid_time]`
- `youtube_tags_raw = [vid_tags]`

## Compatibility review
| Field | Target Storage | VidMov Compatibility | WP Automatic Compatibility | Notes |
|---|---|---|---|---|
| `channel_id` | `wp_postmeta.channel_id` | Compatible (ignored unless consumed) | Supported (`[vid_author]`) | Stable external identity key |
| `channel_title` | `wp_postmeta.channel_title` | Compatible | Supported (`[vid_author_title]`) | Human display identity |
| `youtube_like_count` | `wp_postmeta.youtube_like_count` | Compatible | Supported (`[vid_likes]`) | May be blank/0 on some videos |
| `youtube_publish_date` | `wp_postmeta.youtube_publish_date` | Compatible | Supported (`[vid_time]`) | Timestamp provenance |
| `youtube_tags_raw` | `wp_postmeta.youtube_tags_raw` | Compatible | Supported (`[vid_tags]`) | Variable length/format |

## Phase 2: Test Impact Analysis (Posts 8095, 8096)

Current baseline for both posts:
- Already includes video URL, thumbnail, view counters, imported comments.
- Does not include dedicated `channel_id`, `channel_title`, `youtube_like_count`, `youtube_publish_date`, `youtube_tags_raw` meta keys.

## Before / after model

### Post 8095 (Arcana Prem)
Before:
- no `channel_id`
- no `channel_title`
- no `youtube_like_count`
- no `youtube_publish_date`
- no `youtube_tags_raw`

After activation (on newly imported/reprocessed posts):
- `channel_id` populated from `[vid_author]`
- `channel_title` populated from `[vid_author_title]`
- `youtube_like_count` populated from `[vid_likes]` (or empty/0)
- `youtube_publish_date` populated from `[vid_time]`
- `youtube_tags_raw` populated from `[vid_tags]`

### Post 8096 (Arcana Outcome2026)
Before:
- no `channel_id`
- no `channel_title`
- no `youtube_like_count`
- no `youtube_publish_date`
- no `youtube_tags_raw`

After activation (on newly imported/reprocessed posts):
- same metadata additions as 8095 using source video payload

Impact summary:
- No frontend regression expected from adding passive meta keys.
- This is additive enrichment; existing VidMov behavior remains unchanged.

## Phase 3: Creator Graph Preparation

`channel_id` + `channel_title` become the creator graph foundation:

1. Creator directories
- Group and de-duplicate imported creators by `channel_id`.
- Use `channel_title` as display label.

2. Creator archives
- Build creator archive lookups by `channel_id` rather than post author alone.

3. Featured Readers
- Use `channel_id` as stable slot key for rotation.
- Use `channel_title` for card presentation.

4. Ownership workflows
- Claim process maps OffKilter account -> `channel_id`.
- `channel_title` supports human review and communication.

## Phase 4: Field-by-Field Readiness Classification

| Field | Classification | Justification |
|---|---|---|
| `channel_id` | SAFE TO ENABLE | Deterministic identity key, low risk, high creator value |
| `channel_title` | SAFE TO ENABLE | Display metadata, non-destructive |
| `youtube_publish_date` | SAFE TO ENABLE | Provenance timestamp, additive only |
| `youtube_like_count` | TEST FIRST | Source variability (missing/zero) requires null handling check |
| `youtube_tags_raw` | TEST FIRST | Tag payload can vary in length/format; validate storage behavior |

`DO NOT ENABLE` (current scope): none.

## Phase 5: Execution Plan

## Exact campaign changes
Apply to `8089` and `8090` first:
1. Campaign -> Custom Fields -> Add:
- `channel_id` => `[vid_author]`
- `channel_title` => `[vid_author_title]`
- `youtube_publish_date` => `[vid_time]`
2. Save campaign.
3. Run one controlled import per campaign.
4. Validate new post meta keys exist.

Then (test-first fields):
5. Add:
- `youtube_like_count` => `[vid_likes]`
- `youtube_tags_raw` => `[vid_tags]`
6. Run one controlled import per campaign.
7. Validate null/empty handling and field size behavior.

After Arcana validation pass:
8. Mirror same mappings to `4206` for template consistency.

## Rollback plan
If any issue appears:
1. Set Arcana campaign post status to `draft` (pause imports).
2. Remove newly added custom field mappings from affected campaign(s).
3. Save campaign.
4. Re-run one controlled import and confirm baseline behavior restored.
5. Keep enrichment disabled until root cause is documented.

No destructive rollback is required because changes are additive metadata only.

## Validation checklist
Per test import (`8089`, `8090`):
- [ ] New `vidmov_video` post created without import failure
- [ ] Existing VidMov playback/thumbnail remains functional
- [ ] `channel_id` exists on new post
- [ ] `channel_title` exists on new post
- [ ] `youtube_publish_date` exists on new post
- [ ] `youtube_like_count` stored correctly (value or expected blank)
- [ ] `youtube_tags_raw` stored without truncation/fatal issues
- [ ] Comments import behavior unchanged
- [ ] Duplicate prevention behavior unchanged

## Go / No-Go
Go condition:
- All checklist items pass on one controlled import per Arcana campaign.

No-Go condition:
- Any import failure, malformed metadata persistence, or VidMov rendering regression.

## Final Readiness Statement
Arcana creator enrichment is ready for production activation **in a staged rollout** beginning with `channel_id`, `channel_title`, and `youtube_publish_date`, followed by `youtube_like_count` and `youtube_tags_raw` after one-cycle validation.
