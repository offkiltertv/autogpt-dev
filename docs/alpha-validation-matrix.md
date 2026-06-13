# Arcana Alpha Validation Matrix

Date: 2026-06-13
Scenario: Single-entry draft-first test only.

## Test Scenario Definition
1. Duplicate one safe WP Automatic campaign (do not edit original).
2. Use dedicated test playlist only.
3. Target post type: `arcana_entry`.
4. Output status: `draft`.
5. Import exactly one entry.
6. Publish that one entry manually; run wpForo verification only if discussion routing is enabled for the test.

## Validation Flow Matrix

| Step | System Transition | Expected Outcome | Evidence | Pass/Fail |
|---|---|---|---|---|
| 1 | WP Automatic -> `arcana_entry` | One new draft Arcana entry created | Post list screenshot + post ID | TBD |
| 2 | Arcana enrichment on save | `_arcana_source_playlist`, `_arcana_imported_at`, `_arcana_enriched_at` present | Post meta dump | TBD |
| 3 | Category assignment | Arcana categories assigned based on mapping/default | Term panel screenshot | TBD |
| 4 | Disclaimer injection | Disclaimer block visible on Arcana single page | Frontend screenshot | TBD |
| 5 | Draft -> Publish transition | Entry publishes cleanly; if discussion routing enabled, exactly one wpForo topic created | Topic URL + topic ID (if enabled) | TBD |
| 6 | Bidirectional link storage | If discussion routing enabled, Arcana post stores wpForo IDs/URLs | Post meta dump (if enabled) | TBD |
| 7 | Non-interference check | Existing VidMov campaigns/content unchanged | Before/after campaign snapshot | TBD |

## Data Checks
Required metadata keys on test post:
- `_arcana_source_playlist`
- `_arcana_imported_at`
- `_arcana_enriched_at`
- `_arcana_related_post_ids`
- `_arcana_wpforo_topic_id` (after publish if discussion routing enabled)
- `_arcana_wpforo_topic_url` (after publish if discussion routing enabled)
- `_arcana_discussion_topic_id` (after publish if discussion routing enabled)
- `_arcana_discussion_url` (after publish if discussion routing enabled)

## Failure Conditions
Any of the following is immediate failure:
1. Import creates non-Arcana post type unexpectedly.
2. Existing VidMov campaigns modified.
3. No draft created after test run.
4. Arcana entry publish causes blocking error.
5. Multiple duplicate wpForo topics for one entry.

## Exit Criteria
Alpha single-test validation is complete when:
1. All matrix rows pass.
2. One-entry test is fully documented.
3. No impact found on existing VidMov production flows.
