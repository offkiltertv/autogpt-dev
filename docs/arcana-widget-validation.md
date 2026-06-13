# Arcana Widget Validation

Date: 2026-06-13
Environment: Production (`oktv-main-deployment-vm`)
Method: Read-only WP-CLI + Elementor meta inspection

## Scope
Validate homepage block assumptions in `docs/arcana-launch-day-runbook.md` before any edits.

## Validation Results

### Homepage object
- Page ID `1244` exists and is `publish`.
- Title: `OFFKILTER.TV | Kick Ass, and Over The Top!`
- Elementor mode: `builder`
- Template type: `wp-page`

### Beeteam block presence
- `beeteam368_block_addon` count on homepage: `1`
- Target widget ID `d826690`: **present**
- Elementor tree path: `root[0].elements[0].elements[0]`

### Current widget settings (live)
- `widgetType`: `beeteam368_block_addon`
- `block_title`: `Side Menu`
- `block_sub_title`: `Right - Always Open ^^`
- `block_layout`: `leilani`
- `items_per_page`: `12`
- `post_type`: `vidmov_video, vidmov_audio, vidmov_playlist, vidmov_series`
- `filter_items`: `tv-shows,romance,entertainment,gaming,movies,sports,music`
- No Arcana-specific category filter configured yet.

## Cloning Safety Assessment
- Cloning `d826690` is low risk.
- Existing homepage has only one Beeteam rail widget in this branch; duplication adds a new section without mutating existing query logic when the original is left unchanged.
- Risk comes from editing the original widget instead of the duplicate.

## Operator Guardrails
1. Duplicate first, then rename duplicate to `🔮 The Arcana`.
2. Confirm original widget retains ID `d826690` and original title before save.
3. Only apply Arcana query filters to duplicated widget.
4. Save and validate mobile + desktop.

## Verdict
- Widget baseline assumption is valid.
- Runbook widget target is correct.
- Homepage Arcana rail can be implemented by cloning `d826690` safely.
