# WP Automatic -> Arcana Test Plan

Date: 2026-06-12
Environment target: OffKilter production WordPress (`oktv-main-deployment-vm`)
Mode: Production-safe, no modification of existing live campaigns

## Objective
Validate end-to-end enhancement flow:

WP Automatic campaign
-> creates `arcana_entry`
-> Arcana enrichment (playlist/source metadata + taxonomy + related refs)
-> disclaimer rendering
-> publish transition
-> wpForo topic creation (no duplicates)

## Current campaign observations (read-only audit)
- `wp_automatic_camps` rows: 18
- Dominant campaign type: `Youtube`
- Dominant target CPT today: `vidmov_video`
- Cron hook active: `wp_automatic_hook` every minute

Notable low-risk template candidate:
- Campaign ID `6986` (`Bob Farrell`) has a corresponding `wp_automatic` post in `draft` state.
- This makes it a safer template baseline than editing active publish campaigns.

## Test campaign strategy

### Preferred approach
- Duplicate a draft/non-active WP Automatic campaign into a new campaign.
- Keep existing campaigns untouched.
- Name test campaign: `TEST - Arcana Pipeline`.
- Keep campaign itself in draft until final trigger window.

### Target configuration for test campaign
1. Post type: `arcana_entry`
2. Post status: `draft` for first pass, then `publish` for forum/discussion validation
3. Source: controlled YouTube source with low volume (single playlist or single channel)
4. Category/taxonomy mapping:
   - If source contains `Poem` -> include `Poems`
   - If source contains `Prem and Outcome 2026` -> include `Premonitions`, `Outcomes`
5. Posting frequency:
   - controlled low frequency for test (avoid flood)

## Execution checklist

### A. Pre-check
1. Arcana plugin installed and activated.
2. Arcana Settings configured:
   - `enable_disclaimer = on`
   - `enable_wpforo = on`
   - valid `wpforo_forum_id`
   - playlist mapping lines configured
3. WP Automatic campaign duplicated to test-only campaign.
4. Existing production campaigns unchanged.

### B. Draft validation pass
1. Run test campaign once (or wait for cron).
2. Verify one new post created with `post_type = arcana_entry`.
3. Verify post meta:
   - `_arcana_source_playlist`
   - `_arcana_imported_at`
   - `_arcana_enriched_at`
   - `_arcana_related_post_ids` (may be empty initially)
4. Verify taxonomy assignment on the post:
   - `arcana_category` includes mapped categories
   - `arcana_tag` includes mapped/default tags where configured

### C. Publish validation pass
1. Publish the test Arcana entry.
2. Verify wpForo topic created:
   - `_arcana_wpforo_topic_id`
   - `_arcana_wpforo_topic_url`
   - `_arcana_discussion_topic_id`
   - `_arcana_discussion_url`
3. Re-save/re-publish same post and verify no duplicate topic created.

### D. Frontend validation
1. Open Arcana entry frontend URL.
2. Verify disclaimer block is visible.
3. Verify discussion URL opens expected wpForo topic.

## Success criteria
- Campaign can target `arcana_entry` safely.
- Arcana enrichment runs automatically on WP Automatic-created entries.
- Taxonomy mapping applies correctly.
- Disclaimer renders on frontend.
- wpForo topic is created exactly once per published Arcana entry.

## Failure handling
If any step fails:
1. Pause only the test campaign.
2. Keep existing campaigns unchanged.
3. Collect:
   - WP Automatic logs
   - PHP/NGINX logs
   - problematic post meta snapshot
4. Disable Arcana wpForo toggle if failure is only forum API related, then retest ingestion/enrichment path.

## Post-test cleanup
- Leave test campaign in draft/off state unless approved for production usage.
- Optionally retain one test Arcana entry and topic as known-good reference artifact.
