# Arcana Plugin Deployment and Validation Guide

## Package Build Output
- Artifact: `build/offkilter-arcana.zip`
- Size: ~16 KB
- SHA256: `0f1a99ad4e7ff330a9d91a463e4f9e4c22336c0cad69c7d92d43f39ce973ca15`

## Plugin Integrity Review

### Header and Bootstrap
Main file: `plugins/offkilter-arcana/offkilter-arcana.php`
- Valid plugin header fields present (`Plugin Name`, `Version`, `Description`, etc.).
- `ABSPATH` guard present.
- Activation hook registered: `OKArcana_Activator::activate`.
- Deactivation hook registered: `OKArcana_Activator::deactivate`.
- Core classes are required before hook execution.

### Activation Effects
On activation:
- Registers Arcana CPT/taxonomies.
- Creates queue table: `{wp_prefix}okarcana_import_queue`.
- Seeds baseline taxonomy terms.
- Registers/schedules cron events.
- Flushes rewrite rules.

## Compatibility Assessment (Static)

### WordPress
Status: Compatible (no activation yet).
- Uses standard APIs: post types, taxonomies, admin menu, WP-Cron, metadata, dbDelta.
- PHP lint pass completed on all plugin PHP files.

### VidMov
Status: No direct conflicts detected.
- Plugin adds independent CPT/taxonomy and admin pages.
- No theme file overrides or template rewrites included.

### wpForo
Status: Soft integration implemented.
- Uses `function_exists('wpforo')` and method checks before topic creation.
- If unavailable/incompatible, plugin fails gracefully and stores `_arcana_wpforo_error`.

### ARMember
Status: No hard dependency.
- No direct ARMember APIs/hooks invoked.
- Coexistence expected; access control can be layered later via existing membership rules.

### myCred
Status: No hard dependency.
- No direct myCred APIs/hooks invoked.
- Coexistence expected; point/rank automation can be added in a follow-up.

## Staging Environment Determination
- No dedicated staging environment is currently documented as existing.
- Current docs only state staging is recommended before production.

## Production-Safe Validation Checklist (No Staging Available)

### Pre-Install Safety
1. Confirm latest VM snapshot exists and is timestamped.
2. Confirm recent SQL backup exists and is restorable.
3. Confirm recent `wp-content` backup exists.
4. Confirm root disk free space is healthy before plugin upload.
5. Confirm WordPress admin and DB health are normal.

### Install (Do Not Activate Yet)
1. Upload `offkilter-arcana.zip` via WordPress plugin uploader.
2. Verify plugin appears in installed plugins list.
3. Verify checksum against local artifact if transferred manually.

### Controlled Activation Window
1. Activate during low-traffic window.
2. Immediately verify:
   - Site homepage HTTP 200.
   - `wp-admin` HTTP 200.
   - No fatal errors in PHP/NGINX logs.
3. Open `wp-admin -> Arcana` and verify page renders.

### Post-Activation Functional Smoke Test
1. Confirm CPT `arcana_entry` exists.
2. Confirm taxonomies `arcana_category` and `arcana_tag` exist.
3. Confirm queue table `{prefix}okarcana_import_queue` exists.
4. Confirm cron hook `okarcana_schedule_queue` is scheduled.
5. Import one manual YouTube URL (single test record).
6. Run scheduler manually from Arcana admin page.
7. Verify record transitions queue state and post scheduling behavior.
8. Publish one controlled test Arcana entry and verify wpForo topic link metadata.
9. Verify disclaimer renders on Arcana single entry.

### Rollback Trigger Conditions
Rollback immediately if any of the following occurs:
- Frontend/admin fatal errors.
- Recurring DB errors tied to plugin table operations.
- Unexpected publish behavior affecting non-Arcana posts.

Rollback steps:
1. Deactivate `OffKilter Arcana` plugin.
2. Clear relevant caches.
3. Re-check site/admin health.
4. Restore previous plugin state if needed.

## Recommended Next Step Before Activation
- Create minimal staging clone of production WordPress to execute this checklist end-to-end prior to live activation.
