# First Live Test

Date: 2026-06-13
Goal: Smallest possible live validation of Arcana pipeline with minimal risk.

## Test Scope
Single source, single campaign, single imported entry, draft-first.

## Named Test Assets
1. Safe test playlist: `OFFKILTER ARCANA ALPHA TEST - ONE VIDEO` (new isolated playlist, not `Prem and Outcome 2026`, not `Poem`).
2. Draft-only test campaign: `TEST - Arcana Pipeline` (duplicate of safe template, never edit original).
3. Test Arcana entry: first imported post from the test campaign (exactly one item).

## Preconditions
1. No active production campaign edits in this session.
2. Arcana plugin ZIP available: `offkilter-arcana.zip`.
3. Rollback operator assigned.
4. Existing runbooks available:
- `docs/alpha-deployment-runbook.md`
- `docs/alpha-validation-matrix.md`

## Step-by-Step Execution
1. Log into `https://www.offkilter.tv/wp-admin/`.
2. Upload plugin ZIP via `Plugins -> Add Plugin -> Upload Plugin`.
3. Activate `OffKilter Arcana`.
4. Verify `Arcana` menu appears and settings page loads.
5. Verify `Arcana Entries` CPT and Arcana taxonomies appear.
6. Open WP Automatic campaigns and locate safe template candidate (prefer prior audited draft-safe template).
7. Duplicate template campaign.
8. Rename duplicate to `TEST - Arcana Pipeline`.
9. In duplicate only, set source to playlist `OFFKILTER ARCANA ALPHA TEST - ONE VIDEO`.
10. In duplicate only, set target post type to `arcana_entry`.
11. In duplicate only, set post status to `draft`.
12. Ensure import limit is constrained to one item for first run.
13. Run one campaign cycle.
14. Verify exactly one `arcana_entry` draft was created.
15. Verify post metadata includes:
- `_arcana_source_playlist`
- `_arcana_imported_at`
- `_arcana_enriched_at`
- `_arcana_related_post_ids`
16. Verify Arcana category assignment is present.
17. Open frontend single entry and verify disclaimer injection.
18. Manually publish the test entry.
19. If discussion routing is enabled for this test entry, verify wpForo topic creation and linkage metadata:
- `_arcana_wpforo_topic_id`
- `_arcana_wpforo_topic_url`
- `_arcana_discussion_topic_id`
- `_arcana_discussion_url`
20. Verify no changes occurred to existing VidMov campaigns/posts.

## Pass Criteria
1. One draft `arcana_entry` imported from test playlist.
2. Arcana metadata and taxonomy assignment present.
3. Disclaimer visible on entry page.
4. If discussion routing is enabled, one wpForo topic created on publish with stored bidirectional links.
5. No VidMov regression.

## Immediate Stop / Rollback Conditions
1. Any admin/frontend fatal error after activation.
2. Existing VidMov campaign behavior changes.
3. Import creates non-Arcana posts unexpectedly.
4. wpForo integration causes blocking failures.

## Rollback Steps
1. Disable `TEST - Arcana Pipeline`.
2. Deactivate `OffKilter Arcana` plugin.
3. Confirm homepage and core VidMov flows are normal.
4. Preserve logs and evidence for incident review.
