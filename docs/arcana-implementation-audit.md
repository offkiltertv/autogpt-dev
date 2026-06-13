# Arcana Implementation Audit

Scope: `plugins/offkilter-arcana/` code-level audit only.
Date: 2026-06-12
Mode: No deployment, no activation, no production changes.

## File Inventory

| FILE | PURPOSE | STATUS | DEPENDENCIES | RISK LEVEL |
|---|---|---|---|---|
| `plugins/offkilter-arcana/offkilter-arcana.php` | Plugin bootstrap, constants, class loading, lifecycle wiring | Fully implemented | WordPress plugin loader, all include files | Low |
| `plugins/offkilter-arcana/includes/class-okarcana-activator.php` | Activation/deactivation hooks, default setup, cron setup | Fully implemented | `OKArcana_Settings`, `OKArcana_Post_Types`, `OKArcana_DB`, `OKArcana_Scheduler` | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-db.php` | Queue table schema and creation | Fully implemented | `$wpdb`, `dbDelta()` | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-settings.php` | Settings defaults/sanitization/mapping parser | Fully implemented | WordPress options API | Low |
| `plugins/offkilter-arcana/includes/class-okarcana-post-types.php` | CPT/taxonomy registration and default terms | Fully implemented | `register_post_type`, `register_taxonomy`, term APIs | Low |
| `plugins/offkilter-arcana/includes/class-okarcana-disclaimer.php` | Disclaimer rendering on Arcana entry pages | Fully implemented | `the_content` filter, disclaimer markdown file fallback | Low |
| `plugins/offkilter-arcana/includes/class-okarcana-enhancer.php` | Post-save enrichment, playlist detection, mapping, related IDs | Partially implemented | `save_post_arcana_entry`, WP Automatic DB/meta conventions | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-importer.php` | Legacy manual/CSV/Takeout import into `arcana_entry` + queue | Fully implemented | WordPress post/meta APIs, oEmbed HTTP, queue DB table | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-scheduler.php` | Queue state transitions and randomized 90-day scheduling | Fully implemented | WP-Cron, queue DB table, post status transitions | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-wpforo.php` | Topic creation on publish + metadata link storage | Partially implemented | wpForo runtime API (`wpforo()->topic`) | Medium |
| `plugins/offkilter-arcana/includes/class-okarcana-admin.php` | Admin menu, settings UI, queue display, legacy action handlers | Partially implemented | WordPress admin actions, settings class, importer/scheduler classes | Low |
| `plugins/offkilter-arcana/arcana_disclaimer.md` | Canonical disclaimer text source | Fully implemented | Loaded by disclaimer class | Low |
| `plugins/offkilter-arcana/README.md` | Plugin usage and feature summary | Partially implemented | Documentation only | Low |
| `plugins/offkilter-arcana/docs/IMPLEMENTATION_NOTES.md` | Short implementation summary | Placeholder | Documentation only | Low |
| `plugins/offkilter-arcana/docs/sample-import.csv` | Example CSV schema | Fully implemented | Documentation/test fixture only | Low |

## Capability Matrix (Code-Verified)

| Capability | Status | Code Evidence |
|---|---|---|
| Register CPT | Yes | `class-okarcana-post-types.php:21-35` |
| Register Taxonomies | Yes | `class-okarcana-post-types.php:37-55` |
| Detect WP Automatic Imports | Yes (limited) | `class-okarcana-enhancer.php:53-67` reads `wp_automatic_camp` and `*_automatic_camps` |
| Map Playlist Names | Yes | `class-okarcana-settings.php:167-185`, `class-okarcana-enhancer.php:69-99`, `class-okarcana-post-types.php:109-124` |
| Inject Disclaimer | Yes | `class-okarcana-disclaimer.php:11-41` |
| Create wpForo Topic | Yes (best effort) | `class-okarcana-wpforo.php:37-60` |
| Store Forum Link | Yes | `class-okarcana-wpforo.php:66-71` |
| Create Admin Settings | Yes | `class-okarcana-admin.php:54-108`, `161-177` |
| Schedule Processing | Yes | `class-okarcana-scheduler.php:53-119`, `153-190` |
| Generate Related Arcana | Partial | `class-okarcana-enhancer.php:101-144` stores related IDs metadata only |
| Render Frontend Widgets | No | No widget/block/shortcode rendering code found |
| Build Arcana Homepage | No | No homepage template/section code found |
| Manage Queue | Yes (engine), Partial (ops UI) | DB + state machine in `db/importer/scheduler`; no full queue operations UI |
| Support Bulk Imports | Partial | CSV and Takeout batch supported (`class-okarcana-importer.php:17-67`), but no WP Automatic-native bulk orchestration inside plugin |

## Smallest Missing Pieces

### 1) Required Before Alpha Test
1. Add minimal observability for wpForo failures in admin queue screen (surface `_arcana_wpforo_error` and topic status).
2. Add explicit test-safe toggle for enrichment hooks (enable/disable per environment) to reduce rollout risk.
3. Add one deterministic validation command or admin action to process one Arcana draft and report outcomes.

### 2) Required Before Beta
1. Implement frontend rendering for related entries from `_arcana_related_post_ids`.
2. Add stronger WP Automatic campaign metadata compatibility handling if campaign table/prefix differs.
3. Expand admin UI for queue controls (requeue, hold, inspect payload, retry wpForo link).

### 3) Required Before Production
1. Add idempotent reconciliation job for missing forum topics on already-published entries.
2. Add structured logging/audit trail (post ID, source playlist, mapping result, topic result).
3. Add automated tests for taxonomy mapping, disclaimer output, and publish transition behavior.

## Summary
Arcana core enhancement behavior is real and executable: CPT/taxonomies/settings, enrichment hooks, disclaimer rendering, queue scheduler, and wpForo publish hook all exist in code. The largest current gaps are operational hardening, frontend presentation, and reliability tooling rather than core ingestion/enrichment primitives.
