# Arcana Production Validation Checklist

Date: 2026-06-12
Purpose: safely validate Arcana enhancement layer on production without impacting existing OffKilter content.

## Pre-Validation Safety
1. Confirm current VM snapshot exists.
2. Confirm recent SQL backup exists.
3. Confirm recent `wp-content` backup exists.
4. Confirm root disk free space is healthy.
5. Confirm homepage and `wp-admin` return HTTP 200.
6. Confirm existing WP Automatic campaigns are unchanged before starting.

## Validation Flow
Install Plugin
-> Activate Plugin
-> Verify CPT
-> Verify Taxonomies
-> Verify Settings
-> Create Test Arcana Entry
-> Duplicate Test WP Automatic Campaign
-> Import Test Video
-> Validate enrichment and discussion outputs

## Step-by-Step

### A) Install + Activate
1. Upload `offkilter-arcana.zip` via WordPress Admin.
2. Activate plugin during low-traffic window.
3. Check logs for immediate PHP errors.

### B) Core object checks
1. Verify CPT `arcana_entry` exists.
2. Verify taxonomies exist:
   - `arcana_category`
   - `arcana_tag`
3. Verify default terms exist (`The Arcana`, `Poems`, `Premonitions`, `Outcomes`, etc.).
4. Verify Arcana settings page is present and loads.

### C) Settings checks
1. `enable_disclaimer` is ON.
2. `enable_wpforo` is ON.
3. `wpforo_forum_id` is correct.
4. Playlist mapping lines include:
   - `Poem|Poems|...`
   - `Prem and Outcome 2026|Premonitions,Outcomes|...`

### D) Manual Arcana entry sanity check
1. Create a manual `arcana_entry` draft.
2. Publish it.
3. Verify no fatal errors and wpForo topic creation path works.

### E) WP Automatic controlled test
1. Duplicate a safe campaign template to `TEST - Arcana Pipeline`.
2. Set target post type to `arcana_entry`.
3. Keep test source low volume.
4. Run one campaign cycle.

### F) Output verification
For the test imported Arcana entry, verify:
1. Category assignment is correct.
2. Disclaimer appears on frontend entry page.
3. Related metadata exists (`_arcana_related_post_ids` allowed empty on first sample).
4. Source playlist detection metadata exists (`_arcana_source_playlist`).
5. Import timestamp metadata exists (`_arcana_imported_at`).
6. On publish, wpForo topic metadata exists:
   - `_arcana_wpforo_topic_id`
   - `_arcana_wpforo_topic_url`
   - `_arcana_discussion_topic_id`
   - `_arcana_discussion_url`
7. Duplicate publish attempts do not create duplicate topics.

### G) Compatibility pass
1. Confirm no wpForo admin/frontend regressions.
2. Confirm no VidMov content regressions.
3. Confirm no ARMember access/membership regressions.
4. Confirm no myCred scoring/rank regressions.
5. Confirm existing non-test WP Automatic campaigns continue unaffected.

## Exit Criteria
Validation is complete when:
- all checks above pass,
- no fatal/runtime errors are present,
- and no existing production campaign behavior has been altered.
