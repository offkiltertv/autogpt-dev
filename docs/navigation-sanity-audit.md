# Navigation Sanity Audit

Date: 2026-06-13
Scope: Main menu, mobile/side menu, sidebar/footer-linked menus, creator/category destinations

## Audited Surfaces
- Main Menu (`term_id=183`)
- Side/Mobile Menu (`term_id=184`)
- wpForo menu (`term_id=2153`)
- Footer nav widgets (currently wired to `term_id=184` via `widget_nav_menu`)
- Arcana creator links (sample top Arcana creators)
- Arcana category links

## Findings

### Main Menu (`183`)
Healthy links:
- `Home` -> `200`
- `Member List` -> `200`
- `Forum` (`/community/`) -> `200`

Requires review:
- `Video` -> `#` (placeholder parent)
- `Channel` -> `#` (placeholder parent)

Broken/legacy:
- menu items `1692`-`1704` point to `http://34.105.65.179/...`
- these links are non-functional/time out and should be removed/replaced.

### Side/Mobile Menu (`184`)
Current item:
- `Music` -> `/audio-category/music/` (`200`)

No broken destination found in current single-item set.

### Sidebar/Footer-linked menu behavior
- Footer nav widgets (`nav_menu-1`, `nav_menu-2`) both point to menu `184`.
- Result: duplicate menu source reused across sections (not broken, but low-information duplication).

### Creator links (sample)
Sample Arcana creator URLs all healthy (`200`):
- `@food_for_thought_313`
- `@astraea_5d`
- `@allseeingisisoracle`
- `@poshrandy55`
- `@madamebutterfly444`

### Arcana category links
Healthy (`200`):
- `/video-category/arcana/`
- `/video-category/premonitions/`
- `/video-category/outcomes/`

## 34.105.65.179 Reference Status
Confirmed remaining legacy references in active menu:
- Main Menu items `1692` through `1704`
- pattern: channel-tab URLs using `34.105.65.179`

## Classification

### SAFE TO FIX
1. Remove/replace menu items `1692`-`1704` (legacy IP links).
2. Add Arcana menu tree in Main + Mobile menus.
3. Replace duplicated footer menu usage with dedicated info/support menu.
4. Keep `Video`/`Channel` as parent labels only if dropdown children are valid.

### REQUIRES REVIEW
1. Any `#` parent items that currently depend on theme JS behaviors.
2. wpForo menu alias URLs (`/%wpforo-home%/` patterns) before editing forum nav.
3. Historical channel-tab destinations if any are expected to be restored in-theme.

## Summary
Navigation is mostly healthy for core public destinations, but the legacy IP migration links are the highest-priority cleanup item before broad Arcana homepage promotion.
