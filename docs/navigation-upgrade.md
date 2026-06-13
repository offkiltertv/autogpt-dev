# Navigation Upgrade

Date: 2026-06-13

## Objective
Provide one-click Arcana discovery from primary navigation using existing WordPress menus.

## Verified Current Menus
- Main menu: `main-menu`
- Side menu: `side-menu`
- Legacy links present in main menu Channel subtree pointing to `http://34.105.65.179/...`

## Target Menu Structure
Add under main navigation:
- `🔮 Arcana` -> `/video-category/arcana/`
  - `Premonitions` -> `/video-category/premonitions/`
  - `Outcomes` -> `/video-category/outcomes/`
  - `Latest Readings` -> `/video-category/arcana/?orderby=date`
  - `Readers` -> `/member-list/` (or existing creator/member archive URL in use)

If emoji rendering is inconsistent in theme menu font, label as `Arcana` (no emoji).

## Exact Placement Recommendation
Place top-level `Arcana` between:
- `Video`
- `Forum`

Reason:
- keeps Arcana adjacent to core media discovery
- avoids burying Arcana under Channel submenu

## Admin Steps
1. `Appearance` -> `Menus`.
2. Select `Main Menu`.
3. Add custom links/taxonomy links for:
   - Arcana
   - Premonitions
   - Outcomes
4. Create child hierarchy under `Arcana`.
5. Add `Latest Readings` and `Readers` links.
6. Save menu.

## Legacy Link Remediation in Same Pass
For menu items currently pointing to `34.105.65.179`:
- Replace with canonical `https://www.offkilter.tv/...` URLs where valid.
- Remove items that no longer map to active sections.

## Side Menu Recommendation
Add lightweight Arcana quick links in `side-menu`:
- Arcana
- Premonitions
- Outcomes

Keep side menu minimal (max 3 Arcana entries).

## Verification Checklist
- Main menu displays Arcana entry and children.
- Arcana links return HTTP 200.
- No main-menu link contains `34.105.65.179`.
- Mobile menu drawer shows Arcana tree cleanly.
- Existing Video/Forum/Member links remain unchanged.

## Rollback
- Remove Arcana menu items from main and side menus.
- Restore prior menu ordering.
- Save.
