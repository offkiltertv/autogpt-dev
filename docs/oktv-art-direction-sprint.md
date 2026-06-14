# OKTV Art Direction Sprint

Date: June 14, 2026 (America/Los_Angeles)
Branch: `feature/arcana-plugin`
Scope: Visual curation and discovery quality improvements using existing VidMov/Elementor architecture.

## Objectives
- Improve homepage visual hierarchy and perceived curation.
- Increase prominence of active Arcana creators.
- Reduce adjacent rail repetition.
- Validate mobile presentation (homepage, Arcana, creator pages).
- Define native `/arcana/` destination behavior.

## Production Changes Applied

### 1. Homepage Content Deduplication (Query-Level)
Homepage `1244` block tuning applied:
- `884acac` (`The Arcana`): dynamic Arcana categories (`2258,2259,2260`), date-desc.
- `d7deecd` (`Featured Readers`): curated creator-specific IDs.
- `954fa60` (`Trending This Week`): non-Arcana pool (`youtube,oktv-ftv`), comment_count.
- `fab6803` (`Latest Uploads`): non-Arcana pool (`youtube,oktv-ftv`), date-desc.
- `fe0ddc4` (`Recent Discussion`): non-Arcana pool (`youtube,oktv-ftv`), modified-desc.

Evidence:
- `docs/assets/platform-polish-sprint/art-direction/homepage-rail-settings.post-art-direction.tsv`
- `docs/assets/platform-polish-sprint/art-direction/post_1244_elementor.pre-art-direction.json`
- `docs/assets/platform-polish-sprint/art-direction/post_1244_elementor.post-art-direction.json`

### 2. Creator Prominence (Arcana Priority Creators)
Featured Readers now prioritizes:
- FOOD FOR THOUGHT 313
- Astraea 5D
- POSHRANDY55
- AllseeingisisOracle
- MADAMEBUTTERFLY444

Configured IDs:
- `8220,8226,8102,8096,8095`

Dedup impact vs Arcana latest set:
- overlap before: `3`
- overlap after: `2`

Evidence:
- `docs/assets/platform-polish-sprint/art-direction/arcana-featured-overlap-before-after.tsv`
- `docs/assets/platform-polish-sprint/art-direction/creator-arcana-posts.tsv`

### 3. Discovery Route Polish (Arcana Destination Readiness)
Canonical destination aliases already active and validated:
- `/arcana/` -> `/video-category/arcana/`
- `/videos/` -> `/video/`

This removes legacy dead-end behavior while preserving VidMov-native routing.

## Legacy Content Sweep Findings

### Stale/Low-Signal Creator Signals
- Homepage still surfaces some legacy/non-priority creators mixed with Arcana priority creators.
- Member list still includes hash-like imported handles.

Evidence:
- `docs/assets/platform-polish-sprint/art-direction/creator-surface-audit.tsv`

### Outdated Imagery Signals
Homepage image reference mix:
- 2026 assets: dominant
- 2024 assets: present
- 2023 assets: still referenced (`39` occurrences)

Evidence:
- `docs/assets/platform-polish-sprint/art-direction/homepage-image-year-distribution.tsv`

### Recommended Legacy Cleanup (No Theme-Core Changes)
1. Remove low-context hash-like creators from top discovery rails.
2. Keep priority Arcana creators pinned in Featured Readers until creator quality pass completes.
3. Replace high-visibility 2023 legacy imagery used in discovery surfaces with current creator assets.
4. Continue steering top nav `Creators` to `member-list` until `/channel/` parity is achieved.

## Mobile Experience Audit
Captured surfaces:
- homepage
- Arcana destination
- creator page (FOOD FOR THOUGHT 313)

Devices:
- iPhone 13
- Pixel 5
- iPad Pro 11

Evidence:
- `docs/assets/platform-polish-sprint/art-direction/mobile-captures/capture-index.tsv`
- `docs/assets/platform-polish-sprint/art-direction/desktop-captures/capture-index.tsv`

### Top 10 Mobile Improvements
1. Remove or relocate the “Spread the love” share panel from above first content rail.
2. Reduce top chrome height (logo + dual menu rows) to bring first content card higher.
3. Compress filter row controls (`View All`, sort dropdown) for smaller vertical footprint.
4. Increase contrast/weight on rail subtitles for faster scanability.
5. Keep Arcana + Featured Readers fully visible within first 1.5 viewport heights.
6. Reduce repeated metadata lines on cards (runtime/description truncation on mobile).
7. Normalize creator name truncation to avoid uneven line breaks.
8. Tighten card spacing between rails to reduce dead vertical space.
9. Simplify bottom fixed icon prominence to reduce visual noise during content scan.
10. Add clearer breadcrumb/title treatment on Arcana and creator pages for orientation.

## Arcana Landing Page Plan (`/arcana/`)
Use existing VidMov structures only.

### Canonical Model
- Keep `/arcana/` as a vanity alias redirect to `/video-category/arcana/`.
- Preserve canonical children:
- `/video-category/arcana/premonitions/`
- `/video-category/arcana/outcomes/`

### Native Block Plan
1. Hero strip: Arcana title + short descriptor.
2. Primary rail: newest Arcana entries.
3. Secondary rail: Featured Readers (creator-priority curated set).
4. Tertiary rail: Outcomes/Premonitions split blocks.
5. Sidebar: Arcana Highlights and latest comments/discussion signal.

### Success Criteria
- New visitor reaches Arcana destination in 1 click from main nav.
- Arcana creator identity is visible before first scroll break.
- Arcana rails are distinct from global Trending/Latest rails.

## Validation
- `https://offkilter.tv` -> `200`
- `https://offkilter.tv/wp-admin` -> login redirect, `200`
- `https://www.offkilter.tv/arcana/` -> canonical Arcana archive, `200`
- `https://www.offkilter.tv/video-category/arcana/` -> `200`

## Rollback
- Elementor pre-change snapshot:
- `docs/assets/platform-polish-sprint/art-direction/post_1244_elementor.pre-art-direction.json`
- Restore by writing snapshot back to post meta `_elementor_data` for page `1244`.

## Summary
This sprint improved curation and creator prominence without introducing new features or custom architecture. Arcana now reads as a stronger destination and creator focus is materially clearer, with remaining gains concentrated in mobile density and legacy creator surfacing cleanup.

## Screenshot Review: Top 10 Visual Improvements (Ranked)
Ranking basis: perceived quality impact across homepage hierarchy, creator prominence, Arcana presentation, mobile first impression, and duplicate-content reduction.

1. Remove above-the-fold "Spread the love" share strip from homepage and Arcana archive templates.
2. Replace generic/incorrect rail iconography with semantically correct rail icons.
3. Tighten first-scroll rail density so hero + Arcana + Featured Readers are visible faster.
4. Reduce adjacent rail repetition by shrinking non-primary rail card counts.
5. Clarify rail subtitles so each rail's purpose is obvious in under 2 seconds.
6. Keep Arcana rail as the primary discovery rail directly under the hero.
7. Keep Featured Readers focused on active Arcana creators only.
8. Normalize metadata emphasis by prioritizing title/creator/discussion cues over noisy secondary text.
9. Improve mobile discovery by reducing non-essential header/utility visual dominance.
10. Continue replacing stale 2023 imagery in high-visibility discovery surfaces.

## High-Confidence Improvements Implemented (v2)
Date: June 14, 2026 (America/Los_Angeles)
Scope: configuration-only changes; no theme-core/plugin-code edits.

### A. Homepage Rail Tuning (Elementor page `1244`)
Applied:
- `884acac` (`The Arcana`)
  - icon: `fas fa-eye`
  - subtitle: `Latest Readings - Premonitions - Outcomes`
  - card count: `6 -> 4`
- `d7deecd` (`Featured Readers`)
  - icon: `fas fa-users`
  - subtitle: `Top Arcana creators`
  - curated IDs preserved: `8220,8226,8102,8096,8095`
- `954fa60` (`Trending This Week`)
  - icon: `fas fa-fire`
  - subtitle: `Most discussed this week`
- `fab6803` (`Latest Uploads`)
  - icon: `fas fa-clock`
  - subtitle: `Newest uploads across OffKilter`
  - card count: `8 -> 6`
- `fe0ddc4` (`Recent Discussion`)
  - icon: `fas fa-comments`
  - card count: `6 -> 4`

Evidence:
- `docs/assets/platform-polish-sprint/art-direction-v2/post_1244_elementor.post-art-direction-v2.json`
- `docs/assets/platform-polish-sprint/art-direction-v2/homepage-rail-settings.post-art-direction-v2.tsv`

### B. Share Strip Suppression on Discovery Archives
Applied custom CSS marker:
- `Art Direction Sprint (2026-06-14)`

Rule added:
- Hide `heateor_sss_sharing_title` and `heateor_sss_sharing_container` on:
  - `body.home`
  - `body.tax-vidmov_video_category`

Evidence:
- `docs/assets/platform-polish-sprint/art-direction-v2/custom-css-current.css`

### C. Validation
- Endpoint status:
  - `docs/assets/platform-polish-sprint/art-direction-v2/endpoint-validation.tsv`
  - `docs/assets/platform-polish-sprint/art-direction-v2/endpoint-validation-follow-redirects.tsv`
- Current homepage duplication snapshot:
  - `docs/assets/platform-polish-sprint/art-direction-v2/homepage-duplication-snapshot.tsv`

### D. Rollback (v2)
Backups on origin:
- `/opt/bitnami/backups/art-direction-v2-20260614-075604/elementor_1244_before.json`
- `/opt/bitnami/backups/art-direction-v2-20260614-080227/custom_css_before.css`

Rollback actions:
1. Restore Elementor meta for post `1244` from backup JSON.
2. Restore custom CSS from `custom_css_before.css`.
3. Flush WordPress cache.
