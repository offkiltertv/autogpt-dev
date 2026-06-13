# Arcana Production Deployment Checklist

Date: 2026-06-12
Branch: `feature/arcana-plugin`
Reference commit: `23999e4`
Artifact: `build/offkilter-arcana.zip`
Artifact SHA256: `bbf9be72942c9616fd444f8a234bec75c9eacc75a54ee8f52915f3f81dd204dc`

## 1) Pre-Deployment Review

### Plugin structure verified
- Activation/deactivation hooks: present in `offkilter-arcana.php`.
- Settings model: `class-okarcana-settings.php`.
- Arcana settings admin page: `class-okarcana-admin.php`.
- WP Automatic enrichment hook layer: `class-okarcana-enhancer.php`.
- wpForo publish integration: `class-okarcana-wpforo.php`.
- CPT/taxonomy registration: `class-okarcana-post-types.php`.

### Required runtime dependencies
- WordPress core (required)
- Arcana plugin files (required)

### Optional integrations (recommended)
- WP Automatic (for ingestion + `wp_automatic_camp` source detection)
- wpForo (for auto topic creation)
- VidMov ecosystem (content presentation)

### Non-blocking integrations
- ARMember, myCred, VidMov: no hard coded dependency in Arcana bootstrap path.

## 2) Package Verification

### ZIP build and integrity
- Build command: `scripts/deploy_offkilter_arcana.sh`
- Zip root folder: `offkilter-arcana/` (valid for WP Admin upload)
- Plugin header file exists: `offkilter-arcana/offkilter-arcana.php`
- Includes referenced by bootstrap: all present
- PHP lint: clean on all plugin PHP files

## 3) Production-Safe Activation Checklist

### Before upload
1. Confirm latest VM snapshot exists.
2. Confirm recent SQL backup exists.
3. Confirm recent `wp-content` backup exists.
4. Confirm healthy free disk space on root filesystem.
5. Confirm site and wp-admin return HTTP 200.

### Install
1. Upload `offkilter-arcana.zip` via `Plugins -> Add New -> Upload Plugin`.
2. Confirm plugin appears in Installed Plugins list.
3. Confirm plugin version is `0.1.0` and header metadata is readable.

### Activate
1. Activate `OffKilter Arcana` during low-traffic window.
2. Immediately verify no fatal errors in:
   - PHP error logs
   - NGINX logs
   - wp-admin UI

### Functional checks
1. Arcana Settings page appears: `wp-admin -> Arcana`.
2. CPT appears: `arcana_entry`.
3. Taxonomies appear: `arcana_category`, `arcana_tag`.
4. Default Arcana terms exist (`The Arcana`, `Poems`, `Premonitions`, `Outcomes`, etc.).
5. Disclaimer toggle works in Arcana Settings.
6. wpForo integration toggle + forum ID setting is present.

### Compatibility checks
1. No wpForo fatal/conflict on publish.
2. No VidMov admin/frontend regressions.
3. No ARMember plan/access regressions.
4. No myCred points/rank regressions.
5. No WP Automatic campaign execution regressions.

## 4) Rollback Conditions

Rollback immediately if any occur:
- Frontend 5xx responses
- wp-admin inaccessible
- repeated PHP fatal errors tied to Arcana plugin
- unexpected post/taxonomy corruption

Rollback steps:
1. Deactivate `OffKilter Arcana`.
2. Clear caches.
3. Recheck site/admin status.
4. Restore previous plugin artifact only if needed.

## 5) Deployment Decision Gate

Go/No-Go criteria:
- Package integrity verified
- Activation checks pass
- One controlled WP Automatic -> Arcana test pass completed
- wpForo topic creation path validated without duplicates
