# Signals Discovery Sprint - Strategy

Date: 2026-06-14
Scope: Read-only production audit + architecture recommendation (no implementation changes)
Environment: `oktv-main-deployment-vm` / OffKilter production WordPress

## Executive Summary
- Shorts are **not** currently represented as `/shorts/` URLs in imported OKTV posts.
- Short-form content **is** already entering OKTV: `180` imported `vidmov_video` posts are under 90 seconds.
- Current campaigns are configured as generic YouTube imports (`type=any`, `duration=any`) and do not have a dedicated Shorts mode.
- WP Automatic can support an automated Signals pipeline, but the cleanest path is playlist-first ingestion and duration-based classification.

## Phase 1 - Active YouTube Campaign Audit

Active/publish campaign posts in production (`post_type=wp_automatic`):
- `4168` `@DaniElleLuminati`
- `4206` `@Lipps`
- `4708` `@LC`
- `8089` `Arcana Outcome2026`
- `8090` `Arcana Prem`

Draft test campaign (not active):
- `8092` `Arcana Outcome2026 TEST Single Video`

### Campaign settings snapshot

| Campaign ID | Name | Source Type | Source | Frequency | Duration Filter | Type Filter | Definition | Safety |
|---:|---|---|---|---|---|---|---|---|
| 4168 | @DaniElleLuminati | Channel/User | `https://www.youtube.com/@DaniElleLuminati` | every `1` x `1` | `any` | `any` | `any` | `none` |
| 4206 | @Lipps | Channel/User | `https://www.youtube.com/@Lipps5` | every `1` x `1` | `any` | `any` | `any` | `none` |
| 4708 | @LC | Channel/User | `https://www.youtube.com/@ShaniM4` | every `1` x `1` | `any` | `any` | `any` | `moderate` |
| 8089 | Arcana Outcome2026 | Playlist | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | every `1440` x `1` | `any` | `any` | `any` | `none` |
| 8090 | Arcana Prem | Playlist | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | every `15` x `1` | `any` | `any` | `any` | `none` |

Notes:
- Campaigns currently target `vidmov_video` and publish successfully.
- No campaign has a Shorts-only configuration.

## Phase 2 - Imported Content Audit (Short-form / Vertical / Shorts URL)

From imported `vidmov_video` posts linked to WP Automatic campaigns:
- Total imported videos audited: `689`
- YouTube watch URL imports: `689`
- YouTube `/shorts/` URL imports: `0`
- Videos under 90 seconds: `180`
- Runtime missing in post content: `248`
- Stored ratio metadata `16:9`: `689`
- Stored ratio metadata `9:16`: `0`

### Under-90 distribution by campaign

| Campaign ID | Name | Total Imported | Under 90s |
|---:|---|---:|---:|
| 4168 | @DaniElleLuminati | 61 | 13 |
| 4206 | @Lipps | 355 | 167 |
| 4708 | @LC | 212 | 0 |
| 8089 | Arcana Outcome2026 | 2 | 0 |
| 8090 | Arcana Prem | 58 | 0 |
| 8092 | Arcana Outcome2026 TEST Single Video | 1 | 0 |

Interpretation:
- OKTV is already importing short-duration videos.
- They are stored and rendered as normal `vidmov_video` entries with canonical watch links, not as explicit Shorts objects.
- Current VidMov ratio metadata does not preserve a Shorts/vertical distinction.

## Phase 3 - Source Creator Shorts Audit (sample)

Sample creators requested:
- FOOD FOR THOUGHT 313
- Astraea 5D
- POSHRANDY55
- MADAMEBUTTERFLY444

### Estimated Shorts inventory from channel `/shorts` feeds

| Creator | Channel ID | Shorts Found |
|---|---|---:|
| FOOD FOR THOUGHT 313 | `UCp0aH-FNneZ3FruKFHCK8Kg` | 1 |
| Astraea 5D | `UC_jcZkpWMkEeq5s13KnZCYw` | 2 |
| POSHRANDY55 | `UCWGEyzi6F9l5LjcOahE2gmg` | 37 |
| MADAMEBUTTERFLY444 | `UCe5plOBE5ACyODhSWJuUSxg` | 9 |

Finding:
- Shorts exist on source channels even though current Arcana imports show zero `/shorts/` URLs.

## Phase 4 - Feasibility (WP Automatic + Shorts)

### Can WP Automatic import Shorts directly?
- WP Automatic currently imports YouTube into OKTV through:
  - Channel/user source (`OPT_YT_USER`)
  - Playlist source (`OPT_YT_PLAYLIST`)
  - Single video ID (`YT_ID` in test campaign)
- There is no dedicated "Shorts" mode visible in current campaign configuration.
- Operationally, short-form videos can import automatically when they appear in the configured source feed.

### Supported source paths for future Signals ingestion
- Channel feed: feasible, but Shorts inclusion behavior can vary by channel/feed output over time.
- Playlist feed: most deterministic path (editorial control over exactly which Shorts enter pipeline).
- Custom/single URL feed: feasible for targeted/manual imports.

## Phase 5 - Signals Architecture (No Implementation)

### Definition
- `Signal`: `0-90` seconds
- `Video`: `90+` seconds

### Recommended architecture (cleanest path)
1. Keep existing ingestion stack: `YouTube -> WP Automatic -> vidmov_video`.
2. Use curated source playlists for Signals-first ingestion (highest control, lowest noise).
3. Classify imported items into `Signal` vs `Video` by duration.
4. Surface Signals as a first-class discovery layer using native VidMov/Elementor blocks.

### Surface model
- Homepage:
  - Add `⚡ Signals` rail near top discovery rails.
  - Show newest short-form entries with creator avatar + publish time.
- Creator page:
  - Add/enable a Signals-focused listing (latest short-form by creator).
- Archive:
  - Add dedicated Signals archive entry point with filters by Arcana/other silos.
- Mobile:
  - Prioritize Signals rail high on page, low-scroll discovery, compact card density.

### Operating model
- Keep Arcana as high-signal curated source.
- Treat Signals as a format layer across silos (Arcana first, then Commentary/Gaming/etc).
- Use playlist inclusion as editorial approval gate for automatic publishing.

## Direct Answers

1. Are Shorts currently importing?
- **Partially.** Short-form videos under 90 seconds are importing, but explicit `/shorts/` URLs are not present in imported records.

2. Can Shorts be imported automatically?
- **Yes.** Through existing WP Automatic channel/playlist/single-video ingestion paths, with playlist-based sources as the most reliable control mechanism.

3. What is the cleanest implementation path?
- **Use existing WP Automatic + VidMov pipeline, add duration-based classification, and drive Signals from curated playlists.** No new importer required.

4. Should Signals become a first-class OKTV content type?
- **Yes.** As a first-class discovery format/surface. Implement first as a classification layer on `vidmov_video` (lowest risk), then revisit dedicated type only if needed later.

## Source References
- `youtube_inventory.csv` (Prem / Outcome2026 inventory)
- Production campaign audit (`wp_automatic` + `wp_automatic_camps`)
- Imported post metadata audit (`vidmov_video` + `wp_postmeta`)
