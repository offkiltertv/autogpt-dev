# OffKilter Content Operations

Date: 2026-06-13
Scope: Operational framework for repeatable publishing across approved OffKilter silos.

## 1) Content Lifecycle

Discovery
-> Approval Gate
-> Import
-> Classification
-> Scheduling
-> Publication
-> Optional Forum Discussion
-> Monetization

Operational detail:
1. Discovery: source intake from Tier 1, Tier 2, user submissions, and external discovery.
2. Approval Gate: curated OffKilter playlists/collections/source lists are pre-approved by default.
3. Import: ingest approved sources via WP Automatic or controlled manual queue.
4. Classification: assign silo, topic tags, and monetization lane.
5. Scheduling: apply cadence rules by silo and moderation capacity.
6. Publication: publish to appropriate CPT/template with attribution + metadata (VidMov normal flow for video content).
7. Optional Forum Discussion: attach discussion topics only for selected content with high community value.
8. Monetization: ads + memberships + sponsor packaging based on performance.

## 2) Publishing Cadence (Recommended)

Baseline weekly cadence (start):
- Arcana: 12/week (2 weekday, 1 weekend)
- Gaming: 3/week
- Commentary: 4/week
- Reality TV: 2/week
- Music: 2/week
- Investigations: 2/week
- Technology: 2/week
- Streaming: 2/week

Total baseline: 29 posts/week.

Scale rule:
- Increase any silo only after two weeks of:
  - stable moderation SLA (<24h)
  - no publishing regressions
  - forum response rate >= 20% on new posts in that silo

## 3) Source Hierarchy

## Tier 1 Sources
High-priority editorial sources, directly aligned to core brand lanes.
- Arcana allowlist playlists (Prem, Outcome2026, Poem when present, approved tarot/symbolism playlists)

## Tier 2 Sources
Broader audience growth sources aligned to non-Arcana silos.
- Gaming, Commentary, Reality TV, Music, Investigations, Technology, Streaming playlists/channels

## User Submitted Sources
- Submitted URLs/feeds are queued as `Unreviewed`.
- No auto-publish from user submissions.
- Require classification + human approval.

Approval rule by source type:
1. OffKilter curated playlists/collections/source lists: pre-approved at source level.
2. User-submitted content: per-item human approval required.
3. AI-discovered content: per-item human approval required.
4. Automatically harvested content: per-item human approval required.
5. Third-party feed imports: per-item human approval required.

## 4) Content Quality Rules

## Arcana qualifies when
At least 2 of 3 are true:
1. Clear symbolic/tarot/premonition/interpretive framing.
2. Strong discussion signal (multiple plausible interpretations).
3. Evergreen or semi-evergreen relevance.

## Arcana does NOT qualify when
1. Pure news without interpretive layer.
2. Low-context clips with no symbolism/discussion depth.
3. Repetitive duplicates that add no new interpretation value.
4. Content likely to create high moderation load with low insight value.

## 5) Liked Video Reservoir Policy
1. `Liked videos` is a content reservoir, not a publish queue.
2. No automatic Arcana publishing from Liked Videos.
3. Reservoir flow:
- sample
- classify
- approve
- route to silo queue
4. Weekly reservoir processing target:
- review 50-100 items
- approve 10-20 for silo queues
- promote 3-8 into Arcana only if Arcana quality criteria are met

## 6) Future AI Classification Workflow

Liked Video
-> AI Classification
-> Suggested Category
-> Human Approval
-> WP Automatic Campaign

Execution model:
1. AI model proposes silo + confidence + tag candidates.
2. For curated OffKilter playlists, source-level approval already exists; items proceed without per-item approval.
3. For AI-discovered/harvested/third-party/user-submitted items, human approval remains mandatory.
4. Approved items are appended to silo-specific campaign inputs.
5. Rejected items remain in reservoir with reason code for model tuning.

## 7) Monetization Mapping by Silo

## Arcana
- AdSense: high session-depth pages + related content loops
- Memberships: premium interpretations, archive access, member-only recap threads
- Forum engagement: highest priority (discussion-first format)
- Sponsorship potential: niche brands (self-development, books, tools)

## Gaming
- AdSense: high volume, shorter sessions, strong discovery potential
- Memberships: creator picks, member-only game strategy posts
- Forum engagement: matchup/meta discussion threads
- Sponsorship potential: peripherals, indie tools, creator software

## Commentary
- AdSense: consistent RPM via frequent updates
- Memberships: premium deep-dive analysis
- Forum engagement: opinion/debate threads
- Sponsorship potential: newsletters, productivity/media tools

## Reality TV
- AdSense: trend-driven traffic spikes
- Memberships: episode breakdown clubs
- Forum engagement: recap/prediction threads
- Sponsorship potential: lifestyle/media products

## Music
- AdSense: discovery traffic + playlist session loops
- Memberships: curated listening packs
- Forum engagement: interpretation/recommendation threads
- Sponsorship potential: audio gear, music services

## Investigations
- AdSense: long-form high-engagement sessions
- Memberships: dossier-style premium archives
- Forum engagement: evidence-based discussion threads
- Sponsorship potential: research/documentation tools

## Technology
- AdSense: evergreen search capture
- Memberships: implementation notes/toolkits
- Forum engagement: troubleshooting/use-case threads
- Sponsorship potential: SaaS/dev tooling

## Streaming
- AdSense: regular cadence impressions
- Memberships: behind-the-scenes/community access
- Forum engagement: live-thread + post-stream recap
- Sponsorship potential: creator infrastructure services

## 8) Weekly Operating Rhythm
1. Monday: source review + queue build.
2. Tuesday-Thursday: publish core cadence + forum seeding.
3. Friday: analytics review + next-week rebalance.
4. Weekend: lighter publish cadence + moderation cleanup.

Discussion selection rule:
1. Do not require forum topics for every published item.
2. Prioritize forum topics for Arcana flagship entries, high-debate Commentary, and selected Investigations.

## 9) KPI Stack
Primary KPIs:
1. Returning visitors by silo
2. Forum replies per published post
3. Session duration per silo
4. Ad impressions and RPM by silo
5. Membership conversion from silo landing pages

Guardrail KPIs:
1. Moderation SLA
2. Duplicate-content rate
3. Publish failure rate

This framework is designed to keep Arcana high-signal while turning OffKilter into a scalable, repeatable content operation.
