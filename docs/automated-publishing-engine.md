# Automated Publishing Engine

Date: 2026-06-13
Scope: OffKilter automated publishing pipeline using curated sources.

## Core Rule
Playlist inclusion = editorial approval.

No per-item human approval is required for content originating from:
- curated playlists
- curated source collections
- approved OffKilter content reservoirs

Per-item approval remains required for exception sources (user-submitted, AI-discovered, auto-harvested, third-party feeds).

## System Roles
1. WP Automatic: ingestion engine (source polling, import creation, dedupe at source campaign level).
2. VidMov: primary publishing/presentation layer for imported video content.
3. Arcana layer: enrichment + taxonomy for Arcana-classified content.
4. Publishing Queue: cadence orchestration overlay for silo balancing and long-horizon scheduling policy.
5. Forum layer (optional): discussion topics for selected content only.

## End-to-End Workflow
Source Playlist
-> Classification
-> Content Silo
-> Scheduled Queue
-> Publication
-> Optional Forum Topic

Operational steps:
1. Source playlist ingested by WP Automatic into target post type.
2. Classification assigns content silo from source mapping rules.
3. VidMov handles normal publishing/presentation flow for video content.
4. Queue engine applies cadence policy and slot balancing where needed.
5. Selected posts may receive forum topics based on editorial/community rules.

## Silo Cadence Requirements
Daily target ranges:
- Arcana: 1-3/day
- Commentary: 1-2/day
- Reality TV: 1-2/day
- Gaming: 1/day
- Music: 1/day

Recommended default mix (steady state):
- Arcana: 2/day
- Commentary: 1/day
- Reality TV: 1/day
- Gaming: 1/day
- Music: 1/day

Approximate output: 6/day average, adjustable by moderation and engagement KPIs.

## Queue Assignment Logic

## 1) Source-to-silo mapping
Map by curated source ID/name:
- Arcana playlists -> Arcana
- Commentary playlists -> Commentary
- Reality playlists -> Reality TV
- Gaming playlists -> Gaming
- Music playlists -> Music

## 2) Queue states
Required states:
- Imported
- Queued
- Scheduled
- Published
- Failed (retryable)

## 3) Slot generation
Generate future slots per silo for a 90-day rolling window.
- Arcana: 1-3/day random within allowed windows
- Commentary: 1-2/day random
- Reality TV: 1-2/day random
- Gaming: 1/day random
- Music: 1/day random

## 4) Assignment policy
1. Oldest queued item in silo gets next open slot in that silo.
2. Preserve source diversity:
- avoid same source playlist in consecutive slots when alternatives exist.
3. Preserve category diversity:
- do not stack 3+ near-identical topics in a row.

## 5) Randomization rules
Randomize publish times by silo-specific windows:
- Morning window: 08:00-11:30
- Midday window: 12:00-15:00
- Evening window: 17:00-21:30

Use weighted distribution (default):
- 30% morning
- 25% midday
- 45% evening

Hard constraints:
- minimum 45 minutes between same-silo publishes
- minimum 15 minutes between any two publishes platform-wide

## 6) Continuous 90+ day publication
Maintain rolling backlog coverage:
1. Queue builder runs daily.
2. If scheduled horizon < 90 days for any silo, generate additional slots.
3. If queue depth drops below 14 days in any silo, alert operations.

## Duplicate Prevention

## Import dedupe
1. Primary key: canonical source URL (YouTube URL normalized).
2. Secondary key: source video ID.
3. Tertiary key: source URL hash + title similarity.

## Queue dedupe
1. Do not queue if item is already `Queued`, `Scheduled`, or `Published`.
2. Re-imported existing content updates metadata only; no new publish record.

## Forum dedupe
1. Only selected posts are eligible for topic creation.
2. For selected posts, create topic only if no existing topic ID meta exists.
3. Re-publish/update never creates duplicate discussion topic.

## Failure and Retry Behavior
1. Failed import/classification -> `Failed`, retry max 3 with exponential backoff.
2. Failed schedule assignment -> retain `Queued`, retry on next queue run.
3. Failed forum create for selected posts -> post remains published; enqueue forum-link retry job.

## Operational Controls
1. Pause/resume by silo.
2. Pause/resume by source playlist.
3. Daily max publish cap override for moderation incidents.
4. Blackout windows (optional) for maintenance/outages.

## Metrics and Alerts
Track per silo:
1. Imported/day
2. Scheduled horizon (days)
3. Published/day
4. Publish failures
5. Optional discussion attach rate
6. Forum topic creation success rate (selected posts only)
7. Duplicate prevention hit rate

Alert thresholds:
1. Scheduled horizon < 14 days
2. Forum topic success < 95% over 7 days (selected posts only)
3. Duplicate collision spikes > 5% day-over-day
4. Failed items > 2% of daily imports

## Implementation Profile (Current Stack)
1. WP Automatic campaigns remain source-specific ingestion entry points.
2. Arcana enhancement hooks continue for Arcana CPT.
3. Queue engine orchestrates schedule slots and future post assignment across silos.
4. VidMov remains the default publish/presentation path.
5. Forum automation is optional and applied to selected posts/campaigns only.

## Minimal Rollout Plan
1. Phase 1: Arcana + Commentary queue automation.
2. Phase 2: Add Reality TV + Gaming + Music.
3. Phase 3: Enable full 90-day rolling auto-slot generation and alerting.

Result: curated playlists become a durable automated publishing pipeline in VidMov, with discussion workflows attached selectively where community value is highest.
