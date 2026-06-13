# FOOD FOR THOUGHT 313 Readiness

Date: 2026-06-13
Environment: Production read-only audit

## Objective
Assess channel readiness for Wave 1 creator outreach pilot.

## 1) Channel Readiness Snapshot

## Identity and mapping
- OffKilter user account exists:
  - `ID`: 87
  - `user_login`: `FOOD_FOR_THOUGHT_313`
  - `display_name`: `FOOD FOR THOUGHT 313`
- Channel URL responds:
  - `https://www.offkilter.tv/channel/channel-id/@FOOD_FOR_THOUGHT_313/` -> `HTTP 200`
- Creator term mapping:
  - No dedicated creator term found for `FOOD FOR THOUGHT 313` in `vidmov_video_category`.
  - Current Arcana post is tagged under `@lipps` (term `2055`), not a FOOD FOR THOUGHT creator term.

## Imported video count
- Source inventory (`data/arcana-master.csv`): `93` videos
  - `Prem`: `75`
  - `Outcome2026`: `18`
- Current published Arcana posts authored by this account: `1`
  - Post `8093`
  - Campaign: `8092` (`Arcana Outcome2026 TEST Single Video`)

## Category assignments (live post)
Post `8093` categories:
- `Arcana`
- `Outcomes`
- `YouTube Embedded Library`
- `@lipps` (creator term)

## Profile completeness (user 87)
- `beeteam368_user_profile_id`: empty
- `beeteam368_user_avatar`: empty
- `beeteam368_user_channel_banner`: empty
- `description`: empty
- `beeteam368_subscribe_count`: `0`

## Readiness summary
- Infrastructure readiness: **Pass**
- Channel URL presence: **Pass**
- Content provenance (source inventory): **Strong**
- Creator identity mapping correctness: **Gap**
- Profile quality: **Gap**

## 2) Profile Polish Priorities (Highest Impact / Lowest Effort)

### Priority 1 (must-have before outreach)
1. Set creator avatar.
2. Set channel banner.
3. Add 2-4 sentence creator bio.
4. Create/attach `vidmov_user_profile` to user 87.

### Priority 2 (strongly recommended)
1. Normalize creator identity mapping:
   - stop assigning new FOOD FOR THOUGHT imports to `@lipps`.
   - assign to canonical FOOD FOR THOUGHT creator identity.
2. Pin or feature one high-quality pilot video in Arcana discovery surfaces.

### Priority 3 (nice-to-have)
1. Add social/profile outbound links.
2. Add “Claimed / Verified Creator” display state once claimed.

## 3) Readiness Score
Scoring model (100):
- Account exists (20)
- Channel reachable (15)
- Identity mapping correctness (20)
- Profile completeness (25)
- Live Arcana content presence (20)

Current score:
- Account exists: 20/20
- Channel reachable: 15/15
- Identity mapping: 5/20
- Profile completeness: 2/25
- Live Arcana content: 10/20

**Total: 52/100 (Medium readiness)**

## Decision
Proceed with outreach only after Priority 1 profile polish and identity mapping correction are complete.
