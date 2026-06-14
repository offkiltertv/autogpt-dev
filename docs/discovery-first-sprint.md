# Discovery First Sprint

Date: June 13, 2026 (America/Los_Angeles)
Environment: Production (`https://www.offkilter.tv`)

## Sprint Goal
Reduce first-visit friction and improve content discovery using existing VidMov/Beeteam architecture.

## Scope Completed
1. Public video playback access remediation.
2. Homepage discovery density expansion using native `beeteam368_block_addon` rails.
3. Anonymous desktop/mobile validation.
4. Preview and asset-path audit.

## Changes Made

### 1) Public Access
- Disabled global login-to-watch gate.
- Removed term-level membership plan metadata that was forcing restrictions on video category paths.
- Full details: `docs/public-video-access.md`

### 2) Homepage Expansion (Page ID 1244)
A controlled, reversible Elementor data update was applied with full backup.

Backup location (origin VM):
- `/opt/bitnami/backups/discovery-first-sprint-20260614-013203/`

Before/after rail inventory:
- `docs/assets/discovery-first-sprint/homepage-rails-before-after.tsv`

Net effect:
- Rails increased from `3` to `6`.
- Existing Arcana and Featured Readers remained in place.
- Added discovery rails:
  - `Latest Uploads`
  - `Trending This Week`
  - `Recent Discussion`

Additional cleanup:
- Normalized malformed rail title/subtitle text to stable ASCII labels.
- Flushed Elementor CSS cache and WP cache after update.

### 3) Screenshots (After)
- Desktop homepage: `docs/assets/discovery-first-sprint/homepage-desktop-after.png`
- Mobile homepage: `docs/assets/discovery-first-sprint/homepage-mobile-after.png`
- Anonymous video page: `docs/assets/discovery-first-sprint/video-anon-after.png`

## Before/After Comparison

### Before
- Homepage had 3 rails only.
- Arcana and Featured Readers labels contained malformed encoded text.
- Anonymous playback encountered gating conditions from VidMov + term metadata.

### After
- Homepage has 6 rails, improving browse depth without custom code.
- Arcana remains directly visible high on the homepage flow.
- Anonymous users can browse and watch videos immediately.
- Comments/community actions continue to require login.

## Preview Audit Findings

### Working
- Thumbnails render on homepage cards (desktop/mobile).
- Card metadata and creator attribution render consistently.
- Anonymous video pages load with player area and visible related content.

### Inconsistent / Broken
1. Bunny CDN logo asset URLs return `403`:
- `https://oktv.b-cdn.net/wp-content/uploads/2023/03/logo-4-e1679697855158.png`
- `https://oktv.b-cdn.net/wp-content/uploads/2023/03/logo-4-e1679949133472.png`

2. Homepage metadata still references mixed logo hosts:
- OGP uses Bunny URL.
- Twitter image includes `http://offkilter.tv/...` (non-HTTPS canonical mismatch).

3. Cookie banner occupies meaningful viewport area on mobile and can suppress immediate scroll-depth visibility.

## Discovery Validation
Anonymous checks passed for:
- Homepage load
- Arcana archive access
- Search access
- Creator page access
- Video playback page access without login/paywall markers

Validation artifacts:
- `docs/assets/discovery-first-sprint/public-video-access-validation.tsv`

## Recommendations (Next)
1. Replace Bunny logo references in VidMov/global settings with canonical `https://www.offkilter.tv/wp-content/...` assets to remove `403` risk.
2. Normalize social metadata image URLs to HTTPS canonical domain only.
3. Keep current rail count during initial week; tune per-rail item counts after engagement data.
4. If bounce remains high on mobile, minimize above-the-fold clutter before adding new rails.

## Rollback
Homepage rollback path:
- Restore `_elementor_data` from:
  - `/opt/bitnami/backups/discovery-first-sprint-20260614-013203/elementor_1244_before.json`
- Flush Elementor CSS + WP cache.

Public-access rollback path:
- See `docs/public-video-access.md`.
