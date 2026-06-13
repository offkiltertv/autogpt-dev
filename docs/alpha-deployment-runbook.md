# Arcana Alpha Deployment Runbook

Date: 2026-06-13
Mode: Procedure-only. Do not execute until explicit go-live approval.

## 1) Plugin Upload Path
WordPress Admin path:
1. `https://www.offkilter.tv/wp-admin/`
2. `Plugins` -> `Add Plugin` -> `Upload Plugin`
3. Upload file: `offkilter-arcana.zip`

Expected package root:
- `offkilter-arcana/offkilter-arcana.php`

## 2) Activation Procedure
1. In `Plugins -> Installed Plugins`, locate `OffKilter Arcana`.
2. Click `Activate`.
3. Immediately verify no admin fatal warnings/notices.
4. Go to `Arcana` menu in left nav.
5. Confirm settings page renders and can save defaults.

Activation verification checklist:
1. `Arcana` menu visible.
2. `Arcana Entries` post type visible.
3. `Arcana Categories` and `Arcana Tags` taxonomies visible.
4. No disruption to existing VidMov pages/posts.

## 3) Verification Procedure (Single-Test Validation)
Use only duplicate campaign and test source.

1. Duplicate safe WP Automatic campaign (candidate from prior audit: `camp_id 6986`, if still draft/non-critical).
2. Rename duplicate: `TEST - Arcana Pipeline`.
3. In duplicate only:
- target post type: `arcana_entry`
- source: dedicated test playlist
- output status: `draft`
4. Run one campaign cycle.
5. Verify one new `arcana_entry` draft exists.
6. Verify post metadata includes:
- `_arcana_source_playlist`
- `_arcana_imported_at`
- `_arcana_enriched_at`
- `_arcana_related_post_ids`
7. Verify taxonomy assignment exists (`arcana_category`, optional `arcana_tag` based on mapping).
8. Open single Arcana entry and verify disclaimer block appears.
9. Manually publish test entry.
10. Verify wpForo linkage metadata:
- `_arcana_wpforo_topic_id`
- `_arcana_wpforo_topic_url`
- `_arcana_discussion_topic_id`
- `_arcana_discussion_url`
11. Confirm no existing VidMov campaign changed and no VidMov posting regression.

## 4) Rollback Procedure
Trigger rollback immediately if fatal errors, campaign bleed, or non-Arcana mutation occurs.

Rollback steps:
1. Disable test campaign (`TEST - Arcana Pipeline`) first.
2. Deactivate plugin: `Plugins -> Installed Plugins -> OffKilter Arcana -> Deactivate`.
3. Re-check key user paths:
- homepage
- core VidMov video page
- wp-admin plugin list
4. Confirm existing WP Automatic production campaigns remain unchanged.

Optional cleanup after stabilization:
1. Delete test `arcana_entry` created by test campaign.
2. Keep plugin files installed but deactivated for forensic review.
3. Preserve logs/screenshots for incident review.

## 5) Operator Controls
1. One operator executes changes, one operator observes/checks outcomes.
2. Freeze any unrelated plugin/theme updates during test window.
3. Complete the full verification checklist before enabling any recurring Arcana campaign.
