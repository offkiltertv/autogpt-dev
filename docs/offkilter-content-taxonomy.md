# OffKilter Content Taxonomy

Date: 2026-06-13
Purpose: Reclassify content inventory so Arcana remains high-signal and focused.

## Classification Model

## Tier 1: Arcana Sources (high-signal, interpretation-first)
Use only curated Arcana source playlists:
- `Prem`
- `Outcome2026`
- `Poem` (when present)
- Tarot-related playlists
- Symbolism-related playlists

Rule: Tier 1 is allowlist-only. No automatic spillover from Liked Videos.

## Tier 2: OffKilter General Content (broad audience lanes)
Primary silos:
- Gaming
- Commentary
- Reality TV
- Music
- Investigations

Additional operational silos:
- Technology
- Streaming
- Reservoir (Liked/Watch Later/Favorites holding pool)

## Reservoir Policy (Critical)
`Liked videos` is a master reservoir, not Arcana content.
- Do not bulk-import Liked into Arcana.
- Curate selected videos from reservoir into specific silos.
- Promote to Arcana only when a video passes Arcana relevance criteria.

## Silo Definitions

### Arcana
Symbolism, tarot-style interpretation, premonition/outcome narratives, reflection content.

### Gaming
Game-related clips, streams, hardware mods, console ecosystem topics.

### Commentary
Opinion, current events takes, creator commentary, narrative analysis.

### Reality TV
Personality-driven unscripted content, lifestyle/vanlife/tiny-home/culture narratives.

### Music
Music videos, instrumental/soundtrack sets, music-centered discovery posts.

### Investigations
Research-heavy topics, legal/case analysis, true-crime and documentary-style breakdowns.

## Estimated Content Volume by Silo
Source: current `youtube_inventory.csv` playlist counts (playlist-level estimate, not per-video semantic classification).

- Arcana: **1,400**
- Gaming: **2**
- Commentary: **397**
- Reality TV: **6**
- Music: **5**
- Investigations: **48**
- Technology: **48**
- Streaming: **5**
- Reservoir (Liked + Watch Later + Favorites): **5,166**
- Unclassified: **16**

Important note:
- `Stefon - SNL (chronological)` (16) is external/saved and should remain excluded from Arcana sourcing.

## Arcana Candidate Baseline
Using strict Arcana sources from current inventory:
- `Prem` (1,168)
- `Outcome2026` (179)
- `Poem` (0 currently detected)
- Arcana-adjacent (`Spiritual` + `Prayers`) optional: +53

Operational ranges:
- Strict Arcana queue: **1,347**
- Expanded Arcana-adjacent queue: **1,400**

## Future Ingestion Strategy

## 1) Two-stage intake
1. Ingest into silo queue (never directly into Arcana unless source is Tier 1).
2. Curate/promote into Arcana only after review.

## 2) Arcana promotion criteria
Require at least 2 of 3:
1. Strong symbolic/tarot/premonition framing.
2. High discussion potential (interpretation ambiguity, debate signal).
3. Evergreen or semi-evergreen relevance.

## 3) Cadence by silo
- Arcana: primary publishing lane.
- Investigations/Commentary: supporting lanes to diversify sessions.
- Music/Reality/Gaming: lower-frequency balancing lanes.

## 4) Recommended operating split
Per 10 published items:
- Arcana: 6
- Commentary: 2
- Investigations: 1
- Rotating (Music/Reality/Gaming/Technology/Streaming): 1

## 5) Reservoir curation quota
Weekly reservoir pull:
- Review 50-100 reservoir items.
- Promote 10-20 into silo queues.
- Promote 3-8 into Arcana (if criteria met).

This keeps Arcana focused while using the reservoir for long-term growth.

## Immediate Execution Guidance
1. Lock Arcana WP Automatic campaigns to Tier 1 source playlists only.
2. Keep `Liked videos` in reservoir status.
3. Create separate ingestion campaigns per non-Arcana silo.
4. Review silo mix weekly and rebalance based on forum replies, return sessions, and moderation load.
