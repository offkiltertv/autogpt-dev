# Arcana Enhancement Layer Plan (WP Automatic First)

Date: 2026-06-12
Decision: Arcana should act as an enhancement layer over existing production `wp-automatic` ingestion.

## 1) Can WP Automatic publish directly into `arcana_entry`?
Yes.

Evidence:
- WP Automatic campaign editor includes `camp_post_type` selection from available post types.
- Source code applies selected post type when creating posts (`camp_post_type`).

Implication:
- Once `arcana_entry` exists (Arcana plugin activated), WP Automatic campaigns can publish directly to that CPT.

## 2) Can imported posts be automatically assigned `arcana_category` and `arcana_tag`?
Yes.

Evidence:
- Campaign category picker is taxonomy-aware and supports hierarchical taxonomies for selected post types.
- Core processing applies taxonomy terms via `wp_set_post_terms(...)`.
- Tagging supports configured tags and custom taxonomy assignment (`taxonomy_*` mapping and tag controls).

Practical mapping methods:
- `arcana_category`: select Arcana terms in campaign category/taxonomy settings.
- `arcana_tag`: use campaign tag settings (`cg_post_tags`) and/or custom taxonomy mapping (`taxonomy_arcana_tag`).

## 3) Campaign mapping design

### Campaign A: `Prem and Outcome 2026`
- Source: YouTube playlist/channel feed in WP Automatic.
- Target post type: `arcana_entry`.
- Categories to assign:
  - `The Arcana` (root)
  - `Premonitions`
  - `Outcomes`
- Suggested baseline tags:
  - `Destiny`, `Choice`, `Crossroads`, `Transformation`

### Campaign B: `Poem`
- Source: YouTube playlist/channel feed in WP Automatic.
- Target post type: `arcana_entry`.
- Categories to assign:
  - `The Arcana` (root)
  - `Poems`
- Suggested baseline tags:
  - `Literature`, `Journey`, `Love`, `Loss`

## 4) Can frequency be configured to 3-5 posts/day over 90 days?
Yes, with caveats.

What WP Automatic natively provides:
- Recurring frequency (`cg_update_every` + minutes/hours/days).
- Posting windows (custom start/end time).
- Global cron execution (`wp_automatic_hook` active every minute on production).
- Total campaign post cap (`camp_post_every`).

Configuration guidance:
- 3/day target: every 480 minutes.
- 4/day target: every 360 minutes.
- 5/day target: every 288 minutes.
- 90-day target volume: 270-450 posts total.

Important caveat:
- WP Automatic gives interval-based drip, not strict deterministic queue distribution with random 3-5/day balancing.
- If strict day-by-day distribution is required, keep Arcana scheduler as the control layer after ingest.

## 5) Implementation flow

```text
WP Automatic Campaigns
  -> post_type = arcana_entry
  -> assign arcana_category + arcana_tag
  -> auto-create Arcana entries

Arcana Enhancement Layer
  -> disclaimer injection
  -> publication-state metadata / optional scheduler governance
  -> optional wpForo topic creation on publish (selected entries/campaigns)
  -> related Arcana surfacing by shared categories/tags
```

## Recommended operating mode

### Mode B (recommended): Existing plugin + Arcana enhancement
1. Use WP Automatic for ingestion (YouTube/feed automation).
2. Publish into `arcana_entry` directly.
3. Let Arcana handle post-publish enhancements:
   - disclaimer
   - optional wpForo link/topic creation for selected entries
   - related content logic
4. Use Arcana scheduler only if deterministic 90-day cadence is required.

### Mode A (minimal): WP Automatic only
- Fastest to start publishing.
- Accepts less precision in day-level schedule balancing and Arcana-specific enrichment.

## Liked Videos constraint
- Private `Liked Videos` is not a dependable direct import source.
- Recommended input path: export to playlist/CSV, then ingest via WP Automatic campaign or controlled manual import.
