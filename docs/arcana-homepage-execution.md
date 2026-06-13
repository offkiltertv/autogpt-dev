# Arcana Homepage Execution

Date: 2026-06-13
Scope: Production implementation readiness only (no production edits applied)
Target Homepage: `post_id=1244` (`OFFKILTER.TV | Kick Ass, and Over The Top!`)

## Phase 1: Homepage Audit (Live)

## Elementor homepage structure
Homepage `1244` currently contains one primary Beeteam block widget:

- Section ID: `acb9ab4`
- Column ID: `f2e03d7`
- Widget ID: `d826690`
- Widget type: `beeteam368_block_addon`

Current `d826690` settings:
- `block_layout`: `leilani`
- `post_type`: `vidmov_video, vidmov_audio, vidmov_playlist, vidmov_series`
- `items_per_page`: `12`
- `post_count`: `-1`
- `pagination`: `infinite-scroll`
- `block_title`: `Side Menu`
- `block_sub_title`: `Right - Always Open ^^`
- `filter_items`: `tv-shows,romance,entertainment,gaming,movies,sports,music`

## Existing side rails (widget inventory)
Main sidebar (`main-sidebar`) currently includes:
- `beeteam368_channel_extensions-1` (creator rail)
- `beeteam368_post_extensions-1` (`Most Liked Videos`)
- `beeteam368_post_extensions-2` (`New Playlists`)
- `beeteam368_post_extensions-3` (`News TV-Shows`)

Sidemenu/mobile sidebar (`sidemenu-sidebar`) includes:
- `beeteam368_channel_extensions-2`

Channel extension configs in use:
- Widget instance `1`: `Highest Reaction Score`
- Widget instance `2`: `Most Subscriptions`

## Arcana categories (live)
Taxonomy: `vidmov_video_category`
- Arcana: term `2258`, slug `arcana`
- Premonitions: term `2259`, slug `premonitions`
- Outcomes: term `2260`, slug `outcomes`

## Menus (live)
- Main Menu term ID: `183` (`beeteam368-MainMenu`)
- Side Menu term ID: `184` (`beeteam368-SideMenu`) (mobile/sidemenu surface)

Main menu currently contains legacy `34.105.65.179` channel-tab links (IDs `1692`-`1704`) that should be replaced/removed in same execution window.

## Phase 2: Arcana Feature Rail (Above Fold)

## Recommended implementation approach
Duplicate widget `d826690` and place duplicate immediately below hero/top section, above the current mixed global feed.

New block spec:
- Widget type: `beeteam368_block_addon`
- Source: cloned from `d826690`
- Block title: `🔮 THE ARCANA`
- Block subtitle: `Premonitions • Outcomes • Latest Readings`
- Layout: `leilani` (retain native VidMov card presentation)
- Post type: `vidmov_video` only
- Category filter: `arcana` (term `2258`)
- Secondary filter chips: `premonitions,outcomes`
- Items per page: `6` (desktop)
- Pagination: `infinite-scroll` or `loadmore-btn` (match existing UX)
- Order: date desc (latest-first)

Expected card-level rendering (theme-native):
- video thumbnail
- creator avatar/name
- reaction/comment/view counters
- category labels
- membership badge (if already rendered in active card template)

Visibility goal:
- Arcana block visible within first viewport scroll, within ~5 seconds discovery.

## Phase 3: Featured Readers

## Target creators for Wave 1
Seed list:
- FOOD FOR THOUGHT 313
- Astraea 5D
- POSHRANDY55
- Other highest-scoring Arcana creators from existing priority docs

## Implementation path using existing widgets
Use `beeteam368_channel_extensions` rail and place it directly under `🔮 THE ARCANA` block (or right sidebar aligned to Arcana block).

Recommended configuration:
- Title: `🔮 Featured Readers`
- Order mode: `most_subscriptions` (or `highest_reaction_score` fallback)
- Items per page: `6`
- Post count: `12`

Important limitation:
- Current channel extension widget does not explicitly expose "latest upload" in card settings.
- Workaround: place a small Arcana latest-videos rail adjacent/below readers rail to convey recency.

## Phase 4: Navigation Plan

Add Arcana tree to both menu surfaces:

Main Menu (`term_id=183`):
- Arcana -> `/video-category/arcana/`
  - Premonitions -> `/video-category/premonitions/`
  - Outcomes -> `/video-category/outcomes/`
  - Featured Readers -> reader directory/creator rail landing URL
  - Latest Readings -> Arcana latest archive URL

Mobile/Side Menu (`term_id=184`):
- Mirror Arcana tree above.

Also in same pass:
- remove/replace legacy IP-based channel tab links (`1692`-`1704`).

## Phase 5: Homepage Mixing Logic

Do not isolate Arcana into a separate zone only.
Use this blended order:
1. Hero / existing top module
2. `🔮 THE ARCANA` (new rail)
3. Mixed creators rail (`Featured Readers` + existing creator rail)
4. Existing global mixed video feed (gaming/music/commentary/etc)
5. Sidebar modules (Most Liked, New Playlists, etc)

Outcome:
- Arcana is immediately visible.
- Arcana creators appear in same ecosystem as Gaming/Music/Commentary creators.

## Phase 6: Execution Steps (No Code)

1. Open Elementor on page `1244`.
2. Clone widget `d826690`.
3. Reconfigure clone to Arcana spec above.
4. Add/position `🔮 Featured Readers` channel widget.
5. Update Main Menu (`183`) with Arcana tree.
6. Update Side Menu (`184`) with Arcana tree.
7. Remove/replace menu items `1692`-`1704` (legacy IP links).
8. Validate desktop and mobile discovery paths.

## Validation Checklist
- [ ] Arcana rail visible above fold after hero
- [ ] Arcana cards show creator + engagement stats
- [ ] Premonitions and Outcomes accessible within 1 click from Arcana menu
- [ ] Featured Readers visible on homepage
- [ ] Main + mobile menus both include Arcana tree
- [ ] No menu links use `34.105.65.179`
- [ ] Search returns Arcana videos
- [ ] Homepage still shows non-Arcana mixed content

## Rollback Procedure
If layout or discovery regresses:
1. Remove/disable newly added Arcana widget clone.
2. Revert menu edits (restore pre-change menu snapshot/export).
3. Restore prior homepage widget order.
4. Re-test homepage, archive, and search.

No plugin changes, no theme-core changes required for this rollout.
