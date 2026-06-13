# Arcana Content Inventory

Date: 2026-06-13
Collection mode: YouTube authenticated session, read-only.

## Source Inventory Results

## Specifically requested playlists
- `Poem`: **not found as an exact playlist name** in current Playlists view.
- `Prem and Outcome 2026`: appears split as two playlists:
  - `Prem` (`1,168` videos)
  - `Outcome2026` (`179` videos)
- `Liked videos`: found (`5,000` videos).

## Total Arcana candidate videos

### Core Arcana set (without Liked archive)
- `Prem` + `Outcome2026` + `Poem`
- `1,168 + 179 + 0 = 1,347`

### Extended Arcana set (including full Liked archive)
- `Prem` + `Outcome2026` + `Poem` + `Liked videos`
- `1,168 + 179 + 0 + 5,000 = 6,347`

Note: Liked Videos should be treated as an archive pool, not a direct full publish queue.

## Publication Projection (Estimated Days of Content)

### Core set: 1,347 entries
- `1 post/day`: `1,347` days
- `3 posts/day`: `449` days
- `5 posts/day`: `270` days

### Extended set: 6,347 entries
- `1 post/day`: `6,347` days
- `3 posts/day`: `2,116` days
- `5 posts/day`: `1,270` days

## Practical cadence recommendation
To maximize SEO/forum/returning visitors while controlling moderation load:
1. Start at `2 posts/day weekdays`, `1 post/day weekends` (~12/week).
2. Keep `Prem` and `Outcome2026` as primary feed.
3. Pull from `Liked videos` only via curation batches (10-25 items/week) to avoid low-signal imports.
4. Scale toward `3/day weekdays` only after:
- forum reply rate >= 20% of new Arcana posts, and
- moderation SLA stays <24h for two consecutive weeks.

## Forum growth alignment
Use one dedicated Arcana forum lane with recurring topic structure:
- `Discuss: {Arcana Title}`
- Required starter prompts:
  - interpretation
  - evidence/symbolism
  - alternate reading

This keeps each publish event tied to engagement, not just content volume.

## Inventory file
Full playlist inventory is in:
- `/Users/mbp-apple-m1/autogpt-dev/youtube_inventory.csv`

Important: the Playlists view includes owned and saved playlists; external saved playlists (for example, SNL) should be excluded from Arcana source campaigns.
