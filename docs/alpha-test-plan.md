# Arcana Alpha Test Plan

Date: 2026-06-13
Branch: `feature/arcana-plugin`
Package: `offkilter-arcana.zip`
Mode: Deployment preparation only (no activation, no production writes in this phase)

## 1) Scope
Validate the smallest safe end-to-end Arcana flow:

Test Playlist
-> WP Automatic (duplicate test campaign only)
-> `arcana_entry`
-> Arcana enhancements
-> Optional wpForo topic link (selected-entry test)

## 2) Preflight (Code-Level Verification)
Verified from plugin source before deployment:

- CPT registration: `arcana_entry` in `class-okarcana-post-types.php`.
- Taxonomy registration: `arcana_category`, `arcana_tag` in `class-okarcana-post-types.php`.
- Settings page: `Arcana` admin menu and settings save handler in `class-okarcana-admin.php`.
- WP Automatic integration point: source detection via `wp_automatic_camp` in `class-okarcana-enhancer.php`.
- wpForo integration point: topic creation on publish transition in `class-okarcana-wpforo.php`.
- Disclaimer injection: `the_content` filter for Arcana single pages in `class-okarcana-disclaimer.php`.

## 3) Existing WP Automatic Campaign Safety Review
Source for current campaign observations: repo audit docs (`docs/wp-automatic-arcana-test-plan.md`, `docs/wp-automatic-arcana-campaign-plan.md`, `docs/existing-import-capabilities.md`).

Observed from prior read-only production audit snapshot:
- Total campaigns: 18
- YouTube campaigns: 17
- Safer duplicate candidate: campaign `camp_id 6986` (`Bob Farrell`) with draft context noted in prior audit.

Safest duplication strategy:
1. Duplicate a draft/non-critical campaign template only.
2. Do not edit any existing active VidMov campaign.
3. Keep duplicate as draft until explicit trigger window.

## 4) Alpha Test Workflow (Production-Safe)
1. Install plugin ZIP in WordPress admin.
2. Activate plugin.
3. Verify `arcana_entry` exists in admin post types.
4. Verify `arcana_category` and `arcana_tag` terms include default Arcana terms.
5. Verify `Arcana` settings page appears and loads without PHP warnings.
6. Duplicate the selected safe WP Automatic campaign into `TEST - Arcana Pipeline`.
7. In duplicate only, set target post type to `arcana_entry`.
8. Keep output status = `draft` for first run.
9. Point duplicate to a controlled test playlist source.
10. Run one cycle manually or wait one cron interval.
11. Verify one new `arcana_entry` draft is created.
12. Verify enrichment metadata:
- `_arcana_source_playlist`
- `_arcana_imported_at`
- `_arcana_enriched_at`
- `_arcana_related_post_ids`
13. Verify taxonomy mapping applied (playlist-driven categories/tags).
14. Verify disclaimer appears on single Arcana entry frontend.
15. Publish test entry manually.
16. If discussion routing is enabled for this test entry, verify wpForo topic created once and link metadata stored:
- `_arcana_wpforo_topic_id`
- `_arcana_wpforo_topic_url`
- `_arcana_discussion_topic_id`
- `_arcana_discussion_url`
17. Confirm no regressions in existing VidMov content flows.

## 5) Non-Interference Validation (VidMov Safety)
Pass criteria for non-impact:
1. Existing `vidmov_video` campaigns remain unchanged.
2. No active campaign settings edited except the duplicate test campaign.
3. No theme/plugin fatal errors during Arcana activation.
4. Existing homepage/video pages continue normal rendering.
5. No unexpected post creation outside `arcana_entry` from test campaign.

## 6) Rollback Trigger for Alpha Test
Immediately stop test and deactivate Arcana if any of the following occur:
1. PHP fatal/warning loop in admin or frontend.
2. Existing WP Automatic campaigns begin misposting or changing post type.
3. wpForo topic creation causes blocking errors.
4. Arcana hooks modify non-Arcana post types.

## 7) Evidence to Capture During Alpha Test
1. Screenshot of Arcana settings page loaded.
2. Screenshot/listing of created `arcana_entry` draft.
3. Post meta export for one test post.
4. wpForo topic URL and topic ID.
5. Before/after check of one existing VidMov campaign and one VidMov post publish result.
