# Arcana Rollback Plan

Date: 2026-06-12
Scope: rollback procedure for Arcana plugin deployment if production validation fails.

## Rollback Triggers
Rollback immediately if any of the following occur:
1. Frontend returns persistent 5xx after activation.
2. `wp-admin` becomes unstable/inaccessible.
3. Repeated PHP fatals attributable to Arcana classes/hooks.
4. Unexpected wpForo topic duplication or forum write failures with user impact.
5. Interference with existing WP Automatic campaign publishing.

## Immediate Stabilization Procedure
1. Deactivate `OffKilter Arcana` in WP Admin.
2. Confirm site and `wp-admin` health returns to baseline.
3. Clear site caches/CDN caches as needed.
4. Verify existing production WP Automatic campaigns are still operating as before.

## File Removal Procedure
If deactivation is insufficient:
1. Remove plugin directory:
   - `wp-content/plugins/offkilter-arcana/`
2. Re-scan installed plugins page to confirm removal.
3. Re-check logs for residual fatal errors.

Note:
- Prefer deactivation-first rollback before deleting files.

## Database Impact Review
Arcana may create/use:
- custom post type entries: `arcana_entry`
- taxonomies: `arcana_category`, `arcana_tag`
- options row: `okarcana_settings`
- metadata keys such as:
  - `_arcana_source_playlist`
  - `_arcana_imported_at`
  - `_arcana_enriched_at`
  - `_arcana_related_post_ids`
  - `_arcana_wpforo_topic_id`
  - `_arcana_wpforo_topic_url`
  - `_arcana_discussion_topic_id`
  - `_arcana_discussion_url`
  - `_arcana_wpforo_error`
- queue table: `{prefix}okarcana_import_queue`

## Metadata Cleanup Strategy
Use staged cleanup (only if required):

### Stage 1: functional rollback only
- Keep Arcana data intact after plugin deactivation.
- This preserves auditability and reduces accidental data loss.

### Stage 2: controlled data cleanup (optional)
If full removal is approved:
1. Remove test-only Arcana entries created during validation.
2. Remove Arcana-related post meta from test posts.
3. Remove `okarcana_settings` option.
4. Drop `{prefix}okarcana_import_queue` only if no future re-enable is planned.
5. Preserve backup/snapshot before destructive cleanup.

## Recovery Confirmation Checklist
After rollback:
1. OffKilter homepage and `wp-admin` stable.
2. Existing WP Automatic campaigns unaffected.
3. wpForo, VidMov, ARMember, myCred behavior restored to pre-change baseline.
4. Logs show no ongoing Arcana-origin errors.

## Governance
- Do not execute destructive database cleanup without explicit approval.
- Keep rollback actions logged with timestamps and operator identity.
