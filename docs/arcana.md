# Arcana System Documentation

## Purpose
Arcana is OffKilter.TV's curated knowledge-vault system for ingesting YouTube metadata, publishing structured Arcana entries, and attaching forum discussion as the primary value layer.

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

## Import Workflow
Supported inputs:
- Manual YouTube URL import
- CSV import
- Google Takeout-derived URL import

Rules:
- Metadata/embed only
- No video downloads
- No rehosting

Classification defaults:
- Source containing `Prem and Outcome 2026` -> `Premonitions` + `Outcomes`
- Source containing `Poem` -> `Poems`

## Publishing Workflow
Queue states:
- Imported
- Queued
- Scheduled
- Published

Scheduler behavior:
- Promotes imports into queue
- Distributes publication slots across 90 days
- Targets 3-5 entries/day with randomized publish times
- Uses WP-Cron hook: `okarcana_schedule_queue`

## Forum Integration
On Arcana entry publication:
- Create wpForo topic titled: `Discuss: {Arcana Entry Title}`
- Store bidirectional references in post meta:
  - `_arcana_wpforo_topic_id`
  - `_arcana_wpforo_topic_url`

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
