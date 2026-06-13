# OffKilter Arcana Plugin Installation

## Repository Location
Plugin source is version-controlled at:
- `plugins/offkilter-arcana/`

## Prerequisites
- WordPress instance running on Bitnami stack.
- `wpForo` plugin active (for discussion linking).
- Write access to `wp-content/plugins`.

## Install from this Repo
1. From repository root, package plugin:
   - `cd plugins`
   - `zip -r offkilter-arcana.zip offkilter-arcana`
2. In WordPress Admin:
   - `Plugins -> Add New -> Upload Plugin`
   - Upload `offkilter-arcana.zip`
   - Activate plugin.

## Post-Activation Checks
1. Confirm menu entry exists:
   - `wp-admin -> Arcana`
2. Confirm CPT/taxonomies exist:
   - `arcana_entry`
   - `arcana_category`
   - `arcana_tag`
3. Confirm queue table exists in DB:
   - `{prefix}okarcana_import_queue`
4. Confirm cron hook scheduled:
   - `okarcana_schedule_queue`

## First Import Smoke Test
1. Open `Arcana` admin page.
2. Use Manual URL Import with one YouTube URL.
3. Run `Run Scheduler Now`.
4. Verify entry moves through queue states and gets scheduled.

## Safety Constraints
- Metadata/embed only.
- No video downloads/rehosting.
