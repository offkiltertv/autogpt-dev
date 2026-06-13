# Arcana Plugin Status

Date: 2026-06-12
Plugin path: `plugins/offkilter-arcana/`

## Summary
The plugin contains a mix of:
- production-ready enhancement features
- legacy importer/scheduler scaffolding from the earlier architecture
- best-effort integrations that need runtime validation on production data

## Implemented (Production-Ready Core)
- `arcana_entry` CPT registration
- `arcana_category` and `arcana_tag` taxonomy registration
- default Arcana taxonomy terms seeding
- disclaimer rendering on Arcana single entries
- wpForo publish hook with duplicate prevention (`_arcana_wpforo_topic_id` guard)
- enhancement-layer settings model (`okarcana_settings`)
- WP Automatic enrichment hook on `save_post_arcana_entry`:
  - source playlist detection from `wp_automatic_camp`
  - import timestamp metadata
  - playlist -> taxonomy mapping
  - related Arcana reference metadata generation
- Arcana admin settings UI for:
  - playlist mapping
  - taxonomy mapping
  - disclaimer control
  - wpForo integration control

## Scaffolding / Legacy Components
- Manual URL importer (`OKArcana_Importer`)
- CSV importer (`OKArcana_Importer`)
- Google Takeout importer (`OKArcana_Importer`)
- queue table model (`okarcana_import_queue`)
- scheduler-driven queue promotion (`OKArcana_Scheduler`)

These remain in code for compatibility but are no longer the primary ingestion path.

## Placeholder / Best-Effort Areas
- wpForo API compatibility is best-effort across wpForo versions.
- Bidirectional linking is currently implemented via:
  - Arcana post meta topic refs
  - forum starter post containing Arcana URL
  but not deep wpForo internal metadata writes.
- Related Arcana logic currently uses taxonomy overlap + recency metadata references and does not yet include weighted relevance scoring.

## Decision Alignment
Current architecture now aligns to:
- **WP Automatic = ingestion engine**
- **Arcana plugin = enrichment, mapping, discussion, and governance layer**
