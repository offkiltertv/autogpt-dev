# Arcana Homepage V2

Date: 2026-06-13

## Objective
Make Arcana a first-class discovery surface on the homepage using existing Elementor + Beeteam/VidMov widgets only.

## Verified Baseline
- Homepage page ID: `1244`
- Builder: Elementor (`_elementor_edit_mode=builder`)
- Existing homepage rail widget: `beeteam368_block_addon`
- Existing rail widget id: `d826690`
- Existing layout preset: `leilani`

## Above-the-Fold Arcana Section
Clone existing Beeteam block `d826690` and convert it into:
- Title: `🔮 The Arcana`
- Subtitle: `Latest Premonitions & Outcomes`

### Exact Widget Configuration
- Widget type: `beeteam368_block_addon`
- Layout: `leilani`
- Post type filter: `vidmov_video`
- Taxonomy: `vidmov_video_category`
- Include terms: `Arcana` (`2258`)
- Sort: `date DESC`
- Items: `8` desktop (theme handles responsive downscaling)
- Pagination style: match existing homepage rail (`load more` or `infinite`, whichever is currently active)
- Display flags: keep enabled
  - thumbnail
  - title
  - creator/avatar line
  - category chip
  - membership badge

## Secondary Arcana Rail (Optional, same rollout)
Add a second Arcana rail directly below the primary Arcana rail only if homepage height allows:
- Title: `Premonitions & Outcomes`
- Query mode: same post type, split by category tabs/filters
- Include terms: `Premonitions` (`2259`), `Outcomes` (`2260`)
- Items: `6`

If page becomes too dense, skip this section and keep one Arcana rail only.

## Homepage Content Order Recommendation
1. Hero / top masthead (existing)
2. Trending (existing)
3. `🔮 The Arcana` (new)
4. Featured Creators (existing)
5. Latest Videos (existing global rail)
6. Community / forum blocks (existing)

This keeps Arcana above the fold without displacing core VidMov discovery rails.

## Implementation Steps (WP Admin Only)
1. `Pages` -> `Home` (`ID 1244`) -> `Edit with Elementor`.
2. Locate widget `d826690` (`beeteam368_block_addon`).
3. Duplicate widget/section.
4. Move duplicate above current global feed rail.
5. Rename title/subtitle to Arcana labels.
6. Restrict query to `post_type=vidmov_video` + category `Arcana (2258)`.
7. Set items to `8`.
8. Save/Update.
9. Verify on desktop and mobile.

## Verification Checklist
- Homepage shows `🔮 The Arcana` without layout break.
- Cards display thumbnail, title, creator, category, membership indicator.
- New imports in Arcana categories appear in this rail.
- Existing non-Arcana rails remain unchanged.
- Mobile viewport keeps Arcana rail readable and scroll-safe.

## Rollback
- Open homepage in Elementor.
- Delete Arcana cloned block.
- Update page.
- Confirm homepage returns to prior state.
