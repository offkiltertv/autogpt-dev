# Arcana Channel Audit

Date: 2026-06-13
Environment: Production (`oktv-main-deployment-vm`)
Method: Read-only WP-CLI + public URL validation

## Objective
Assess creator/channel readiness for Arcana blending into existing creator ecosystem.

## 1) Arcana Content and Creator Snapshot
Arcana taxonomy counts:
- `Arcana` (`slug: arcana`): `3`
- `Premonitions` (`slug: premonitions`): `1`
- `Outcomes` (`slug: outcomes`): `2`

Current Arcana posts (published):
- `8093`
- `8095`
- `8096`

Observed post author accounts for Arcana imports:
- `FOOD_FOR_THOUGHT_313` (ID `87`) -> `1` published video
- `AllseeingisisOracle` (ID `88`) -> `1` published video
- `MADAMEBUTTERFLY444` (ID `89`) -> `1` published video

## 2) Existing Creator Ecosystem Overlap
Existing large creator terms in `vidmov_video_category`:
- `@lipps` (`slug: lipps`) -> `358`
- `@LC` (`slug: lc`) -> `212`
- `@DaniElleLuminati` (`slug: danielle`) -> `61`

Arcana overlap:
- `@lipps` currently has Arcana-tagged posts and large non-Arcana footprint.

Interpretation:
- Arcana already touches mainstream creator taxonomy (`@lipps`), which supports unified creator experience.

## 3) Channel URL Reachability
Verified HTTP 200:
- `/channel/channel-id/@lipps/`
- `/channel/channel-id/@LC/`
- `/channel/channel-id/@DaniElleLuminati/`
- `/channel/channel-id/@FOOD_FOR_THOUGHT_313/`
- `/channel/channel-id/@AllseeingisisOracle/`
- `/channel/channel-id/@MADAMEBUTTERFLY444/`

## 4) Creator Profile Completeness
For Arcana import authors (IDs `87, 88, 89`):
- `description` user meta exists but is empty.
- `user_email` empty.
- `user_url` empty.
- No populated avatar/banner/profile social metadata found in filtered user meta keys.

For major creator terms (`@lipps`, `@LC`, `@DaniElleLuminati`):
- term meta currently only shows `beeteam368_membership_plans`.
- no explicit image/banner/description term meta captured in this audit.

## 5) Category Presentation Readiness
Arcana category term metadata:
- Arcana/Premonitions/Outcomes currently carry membership plans (`Free Membership`) only.
- Descriptions are empty.

## 6) Blending Recommendations (No Code)
1. Keep Arcana creators in existing creator rails; do not create separate Arcana creator type.
2. Populate creator profile basics for Arcana import authors:
   - display avatar
   - short bio/description
   - optional outbound profile URL
3. Keep `Featured Creators` mixed and adjacent to Arcana rail.
4. Normalize Arcana creator taxonomy strategy:
   - either consistent author-based channel identity
   - or consistent `@creator` term assignment
   - avoid mixed identity labels for same creator over time.

## Verdict
- Creator channels are reachable and can be blended now.
- Main gap is profile completeness, not infrastructure.
- Arcana creator blending is feasible immediately with admin/profile curation.
