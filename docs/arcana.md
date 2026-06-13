# Arcana System Documentation

## Purpose
Arcana is OffKilter.TV's curated knowledge-vault layer for structuring YouTube-sourced content into Arcana entries, applying taxonomy/classification, and enabling optional community discussion for selected items.

## Operating Model
Primary ingestion engine: **WP Automatic (existing production plugin)**

Arcana role: **enhancement layer**
- Arcana CPT/taxonomy model
- Arcana-specific category/tag semantics
- disclaimer enforcement
- optional wpForo discussion linkage
- optional deterministic scheduler governance

## Categories
Arcana uses `arcana_category` taxonomy with initial structure:
- The Arcana (root)
- Poems
- Premonitions
- Outcomes
- Tarot
- Archetypes
- Symbolism
- Dreams
- Synchronicities
- Literature

## Ingestion Workflow (WP Automatic First)
Supported via WP Automatic campaigns:
- YouTube imports
- Feed/RSS imports
- Auto-posting via cron
- Custom post type targeting (`arcana_entry`)

Rules:
- Metadata/embed only
- No video downloads
- No rehosting

Classification defaults:
- Source containing `Prem and Outcome 2026` -> `Premonitions` + `Outcomes`
- Source containing `Poem` -> `Poems`

## Publishing Workflow
Queue/publish states may be tracked as:
- Imported
- Queued
- Scheduled
- Published

Scheduling options:
- WP Automatic interval drip for continuous publishing
- Arcana scheduler for deterministic 90-day cadence control when needed

## Forum Integration
Forum is an optional community layer.

For selected Arcana entries:
- Create wpForo topic titled: `Discuss: {Arcana Entry Title}`
- Store bidirectional references in post meta:
  - `_arcana_wpforo_topic_id`
  - `_arcana_wpforo_topic_url`

Default behavior:
- Arcana content publishes normally without requiring a forum topic for every item.

## Disclaimer System
Every Arcana entry must display the entertainment disclaimer.

Canonical requirement text:

FOR ENTERTAINMENT PURPOSES ONLY

The Arcana is a curated collection of media, symbolism, poetry,
tarot-inspired interpretation, and community discussion.

Content published within The Arcana is intended for entertainment,
creative exploration, storytelling, education, and personal reflection.

No content on OffKilter.TV should be interpreted as financial,
medical, legal, psychological, or professional advice.

Interpretations, predictions, symbolism, and discussions represent
the opinions of individual creators and community members.

Viewers are encouraged to exercise critical thinking and form their
own conclusions.
