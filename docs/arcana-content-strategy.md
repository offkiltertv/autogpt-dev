# Arcana Content Strategy

Date: 2026-06-13
Mode: Content operations plan (business execution), not software development.

## 1) Reality Check
Arcana can run now using existing stack:
- Ingestion: WP Automatic
- Content object: `arcana_entry`
- Categorization/enrichment: Arcana plugin
- Community: optional wpForo discussion routing for selected entries
- Distribution: Homepage modules + forum activity

## 2) Source Inventory and Production Capacity

### Current source sets
1. `Prem and Outcome 2026`
2. `Poem`
3. `Liked Videos Archive` (curation source; not direct full automation source)

### Count status
Existing repository artifacts do not contain current playlist video counts for `Prem and Outcome 2026` and `Poem`, and no finalized `Liked Videos` export file is committed.

Operationally, define:
- `P = video count in Prem and Outcome 2026`
- `M = video count in Poem`
- `L = curated import-ready count from Liked Videos`

### Arcana entry capacity
- Theoretical maximum entries: `P + M + L`
- Recommended publishable backlog (quality-controlled): `P + M + (0.25 to 0.40) * L`
  - Reason: Liked Videos should be curated, not bulk imported, to avoid low-relevance noise and moderation load.

## 3) Alpha Through Year-One Cadence

## Recommended baseline cadence
- Weekdays: 2 posts/day
- Weekends: 1 post/day
- Weekly total: 12 entries/week

Use this as default until stable moderation and forum throughput are confirmed.

## Scale-up trigger
Move to:
- Weekdays: 3 posts/day
- Weekends: 1-2 posts/day
only when both conditions hold for 2 consecutive weeks:
1. At least 20% of new Arcana entries receive forum replies.
2. Moderation queue remains under 24-hour response time.

## Ideal schedule if Poem + Prem were imported today
1. Do not publish all imported items immediately.
2. Queue all imported entries as draft/future.
3. Release in waves:
- Days 1-14: 2/day (stability + moderation calibration)
- Days 15-45: 3/day weekdays, 1/day weekends (growth phase)
- Days 46-90: 2/day weekdays, 1/day weekends plus 1 weekly “deep-dive recap” thread

This maximizes returning visits and forum momentum while reducing content fatigue.

## 4) Forum Structure (Operations-Oriented)
Use one Arcana category group in wpForo with six boards:
1. Arcana: Premonitions
2. Arcana: Outcomes
3. Arcana: Poems
4. Arcana: Interpretations & Symbolism
5. Arcana: Weekly Recaps
6. Arcana: Meta / Requests

Topic template for entries selected for discussion:
- `Discuss: {Arcana Title}`
- Starter prompts:
  - Core interpretation
  - Symbol/theme breakdown
  - Counter-interpretation

Moderation controls:
- New-user rate limits
- Auto-hold first post per new account
- Weekly moderator sweep for stale/duplicate topics

## 5) Taxonomy Structure (Business Use, Not Just Metadata)

## Primary categories
1. Premonitions
2. Outcomes
3. Poems
4. Tarot
5. Symbolism
6. Archetypes

## Source mappings
- Playlist contains `Prem and Outcome 2026` -> `Premonitions`, `Outcomes`
- Playlist contains `Poem` -> `Poems`
- Liked archive imports -> assign one primary category manually at ingest review

## Working tags (search + recommendation utility)
Use controlled tag pool (20-40 tags max in year one), e.g.:
- Destiny, Choice, Journey, Transformation, Shadow, Loss, Love, Crossroads

Do not allow uncontrolled tag explosion; cap new tags per week.

## 6) Homepage Exposure Strategy

## Goal
Convert one-time video views into repeat OffKilter sessions + forum participation.

## Homepage slots
1. Featured Arcana Entry (1)
2. New Arcana Entries (latest 6)
3. Trending Arcana Discussions (top 5 by replies in last 7 days)
4. Most Discussed This Week (top 5)
5. Editor’s Arcana Picks (manual, 3)

## Rotation policy
- Featured: daily
- New entries: automatic
- Trending/discussed: daily refresh
- Editor picks: weekly refresh

## 7) 30 / 90 / 1-Year Plan

## Next 30 days (stability + proof)
1. Run controlled Arcana publishing at 12 entries/week.
2. Validate that selected Arcana entries can receive working discussion topics.
3. Build moderator routine and response SLA (<24h for flagged content).
4. Establish baseline KPIs:
- return visitor rate
- forum reply rate per entry
- session duration on Arcana pages

## Next 90 days (growth + consistency)
1. Expand to 14-18 entries/week if moderation supports it.
2. Introduce weekly recap posts linking best discussions.
3. Start curated Liked Videos ingestion (small batch weekly).
4. Tune category/tag distribution for search coverage and internal linking.

## Next year (scale + monetization)
1. Maintain a 6-12 month publishing backlog.
2. Segment high-performing Arcana categories for membership perks (ARMember).
3. Build sponsor/ad inventory packages around recurring Arcana traffic blocks.
4. Launch recurring community events tied to top Arcana threads.

## 8) Business KPI Targets

Primary success metrics:
1. Returning visitors from Arcana pages
2. Forum replies per Arcana entry
3. Arcana organic landing pages indexed
4. Ad impressions per Arcana session

Risk controls:
1. Content fatigue: keep cadence adaptive, not maximal.
2. Duplicate content: dedupe by YouTube URL + title checks before publish.
3. Moderation burden: scale cadence only after moderation SLAs are consistently met.

## 9) Recommended Immediate Operating Decision
1. Start with Poem + Prem ingestion queues.
2. Publish at 12 entries/week for first 2 weeks.
3. Review KPI + moderation load.
4. Scale to 16-20 entries/week only if engagement and moderation thresholds are met.

This is the smallest practical path to run Arcana as a content business using current infrastructure.
