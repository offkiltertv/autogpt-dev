# Arcana Operator Checklist

Date: 2026-06-13

Purpose: Execute Arcana discovery rollout through WordPress Admin only.

## Pre-Flight
- Confirm Arcana campaigns are active and healthy (`8089`, `8090`).
- Confirm current Arcana imports render in archive:
  - `8095`
  - `8096`
- Take screenshots of current homepage and main menu for rollback.

## A) Homepage Arcana Block Creation

1. Go to `Pages` -> `Home` (`ID 1244`) -> `Edit with Elementor`.
2. Locate existing Beeteam block widget `d826690` (current “Side Menu / Right - Always Open ^^”).
3. Duplicate the section containing this widget.
4. Move duplicated section above the current global feed section.
5. Configure duplicate block:
- Title: `🔮 The Arcana`
- Subtitle: `Latest Premonitions & Outcomes`
- Post type: `vidmov_video`
- Category filter (`vidmov_video_category`): `Arcana`
- Sort: `Date DESC`
- Items: `8`
- Keep card fields enabled: thumbnail, title, creator, category, membership badge
6. Click `Update`.
7. Verify on homepage:
- Arcana block visible
- At least one Arcana post card renders
- Links open to valid video pages

## B) Menu Updates

1. Go to `Appearance` -> `Menus` -> `Main Menu`.
2. Add top-level menu item:
- `Arcana` -> `https://www.offkilter.tv/video-category/arcana/`
3. Add submenu under Arcana:
- `Premonitions` -> `https://www.offkilter.tv/video-category/premonitions/`
- `Outcomes` -> `https://www.offkilter.tv/video-category/outcomes/`
4. Save menu.
5. Verify frontend navigation:
- Arcana link visible
- Child links visible
- All links return HTTP 200 and load correct archives

## C) Category Visibility Verification

Verify all surfaces resolve and show content:
1. Arcana archive: `/video-category/arcana/`
- Expect `8095` and `8096` visible.
2. Premonitions archive: `/video-category/premonitions/`
- Expect `8095` visible.
3. Outcomes archive: `/video-category/outcomes/`
- Expect `8096` visible.
4. Search checks:
- `/?s=unalive+you&post_type=vidmov_video` -> includes `8095`
- `/?s=severe+consequences&post_type=vidmov_video` -> includes `8096`

## D) Rollback Steps

If anything regresses:

Homepage rollback
1. Elementor -> Home page revisions.
2. Restore revision immediately before Arcana section addition.
3. Update page.

Menu rollback
1. Appearance -> Menus -> Main Menu.
2. Remove Arcana, Premonitions, Outcomes entries.
3. Save menu.

Post-rollback verification
1. Homepage returns HTTP 200.
2. Main menu renders correctly.
3. Arcana imports continue unaffected (campaigns still ingesting).

## Go/No-Go Gate

Go if all are true:
- Homepage Arcana section renders correctly.
- Arcana/Premonitions/Outcomes menu links work.
- `8095` and `8096` visible in expected archives/search.

No-Go if any are true:
- Homepage section breaks layout.
- Menu link mismatch/404.
- Arcana cards missing metadata (title/thumb/creator/badge).
