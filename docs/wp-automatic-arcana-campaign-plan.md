# WP Automatic Arcana Campaign Plan

Date: 2026-06-12
Mode: Production-safe planning only (no campaign edits executed)

## Production Campaign Snapshot (Read-only)

### Counts
- Total WP Automatic campaigns: 18
- YouTube campaigns: 17
- Single campaigns: 1

### Current target post type distribution
- `vidmov_video`: 18

### Scheduling profile (from campaign settings snapshot)
- Campaign update cadence is configured via per-campaign `cg_update_every` + `cg_update_unit`.
- Most current campaigns use minute-level update units.
- Existing post cap (`camp_post_every`) varies from `2000` to `331616`.

## Safety Constraints
- Do not modify existing production campaigns.
- Create a duplicate/draft test campaign only.
- Keep test source narrow (single low-volume playlist/channel) for first validation.

## Recommended Test Path

### Candidate template
Use a non-disruptive campaign template in draft/non-critical state, then duplicate:
- Candidate observed: `camp_id 6986` (`Bob Farrell`) with corresponding `wp_automatic` post in draft context.

### Test campaign creation
1. Duplicate template campaign.
2. Rename to: `TEST - Arcana Pipeline`.
3. Keep campaign post status as draft until ready to run.

### Required test campaign settings
1. Target post type: `arcana_entry`
2. Post status for pass 1: `draft`
3. Controlled source: one known-safe YouTube source
4. Frequency: low (avoid burst imports)
5. Category/Tag strategy:
   - for `Poem` source, map to `Poems`
   - for `Prem and Outcome 2026` source, map to `Premonitions` and `Outcomes`

### Pass 1 validation (draft creation)
- Run one cycle and verify creation of a new `arcana_entry`.
- Confirm Arcana enhancement metadata is populated:
  - `_arcana_source_playlist`
  - `_arcana_imported_at`
  - `_arcana_enriched_at`
  - `_arcana_related_post_ids` (may be empty with low sample size)
- Confirm taxonomy assignment is correct.

### Pass 2 validation (publish transition)
- Publish one test Arcana entry.
- Validate wpForo topic creation and link metadata:
  - `_arcana_wpforo_topic_id`
  - `_arcana_wpforo_topic_url`
  - `_arcana_discussion_topic_id`
  - `_arcana_discussion_url`
- Re-publish/update and verify no duplicate topic created.

## Promotion Criteria
Promote campaign strategy beyond test only if:
1. One full end-to-end test passes.
2. No regressions in existing VidMov campaign output.
3. No PHP fatal or recurring warnings from Arcana/wpForo/WP Automatic paths.

## Deferred (after first successful test)
- Add 90-day cadence governance (if needed) via Arcana scheduler policy.
- Scale campaign volume gradually.
- Add additional Arcana playlist mappings in Arcana settings.
