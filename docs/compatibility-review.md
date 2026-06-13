# Arcana Compatibility Review

Date: 2026-06-12
Environment basis: OffKilter production plugin stack audit (read-only)

## Reviewed Systems
- VidMov (BeeTeam368 ecosystem)
- WP Automatic
- wpForo
- ARMember
- myCred

## Compatibility Matrix

### VidMov
Status: Compatible (low collision risk)
- Arcana creates separate CPT `arcana_entry` and taxonomies `arcana_category`, `arcana_tag`.
- Existing WP Automatic campaigns currently target `vidmov_video`; Arcana does not override that CPT.
- No template override or direct VidMov function replacement in Arcana plugin.

Risk level: Low

### WP Automatic
Status: Compatible and intentionally integrated
- Arcana enhancement hook runs on `save_post_arcana_entry`.
- Source playlist detection reads `wp_automatic_camp` metadata and `automatic_camps` table (read-only behavior).
- No WP Automatic file changes required.

Potential collision areas:
- If WP Automatic campaign is misconfigured to a different post type, Arcana hooks will not run.
- If campaign metadata is missing, source playlist mapping may be incomplete.

Risk level: Medium-Low (configuration dependent)

### wpForo
Status: Compatible with guarded integration
- Arcana only attempts topic creation when enabled in Arcana settings.
- Duplicate prevention exists (`_arcana_wpforo_topic_id` guard + publish transition checks).
- Calls are wrapped in try/catch and error meta fallback.

Potential collision areas:
- wpForo API signature differences across versions may prevent topic creation.
- Incorrect forum ID in settings will route topic creation to wrong forum or fail.

Risk level: Medium

### ARMember
Status: Compatible (no direct runtime coupling)
- No direct ARMember class/method calls in Arcana bootstrap path.
- ARMember cron/events run independently of Arcana.

Potential collision areas:
- Access-control configuration at site level could hide Arcana content unintentionally.

Risk level: Low

### myCred
Status: Compatible (no direct runtime coupling)
- No direct myCred class/method calls in Arcana bootstrap path.
- Arcana post lifecycle does not alter myCred points by default.

Potential collision areas:
- Future point hooks could trigger if global site rules auto-award on post publish.

Risk level: Low

## Conflict Type Review

### Hook collisions
- No direct hook name collisions identified.
- Arcana uses namespaced class handlers and plugin-specific option key.

### CPT conflicts
- Arcana CPT is `arcana_entry`, unique from current `vidmov_video` campaigns.
- No duplicate CPT registration observed.

### Taxonomy conflicts
- Arcana taxonomies are uniquely namespaced (`arcana_category`, `arcana_tag`).
- No naming collision with standard `category`/`post_tag`.

### Scheduler conflicts
- Arcana schedules `okarcana_schedule_queue`.
- WP Automatic uses `wp_automatic_hook` every minute.
- Coexistence expected; both rely on WP-Cron.

Operational caution:
- High-frequency WP Automatic plus Arcana queue jobs can increase cron activity. Monitor execution time and logs during first activation window.

## Conclusion
No blocking compatibility conflicts were found for VidMov, WP Automatic, wpForo, ARMember, or myCred. Main production risk is configuration quality (campaign target post type, taxonomy mapping, wpForo forum ID), not structural plugin conflict.
