# Legacy IP Cleanup Plan (34.105.65.179)

Date: 2026-06-13

Scope: Remaining references to `34.105.65.179` in production content/config.

## Current Reference Counts
- `wp_options`: 9
- `wp_posts.post_content`: 66
- `wp_posts.guid`: 153
- `wp_postmeta`: 30

## Classification

### Safe to Replace Automatically
These are high-confidence replacements with low break risk.

1. Menu URLs (`_menu_item_url`) in `wp_postmeta` (13 rows)
- IDs `1692-1704` (Channel submenu links)
- Action: replace `http://34.105.65.179/...` with valid `https://www.offkilter.tv/...` targets or remove if obsolete.

2. Published page content references where URL appears in old-host hard links
- Action: targeted search/replace to `https://www.offkilter.tv` after dry-run validation.

3. Explicit single-value options that are host URLs
- Example: `bunnycdn_cdn_url` if still old host.
- Action: update via plugin/settings screen directly.

### Requires Manual Review
These can be historical or serialized and should not be mass-edited blindly.

1. `wp_posts.guid` (153 rows)
- Mostly legacy attachment GUIDs.
- Manual policy: leave unchanged unless a concrete rendering/canonical issue is proven.

2. Serialized/complex options
- Examples:
  - `aioseo_options`
  - `aioseo_options_internal`
  - `fs_accounts`
  - `monsterinsights_notifications`
  - `beeteam368_theme_options-transients`
  - `beeteam368_video_settings`
  - `bsr_data`
  - `widget_custom_html`
- Action: inspect in plugin/theme UI first, then replace only verified URL fields.

3. `wp_posts.post_content` in draft/revision rows
- Many references are in drafts/revisions/demo pages.
- Action: prioritize published pages first; defer drafts/revisions unless reused.

4. `wp_postmeta` non-menu keys (examples)
- `rank_math_og_content_image`
- `beeteam368_ads_mixed`
- `_elementor_page_settings`
- `_downloadable_files`
- Action: review key-by-key before replacement.

## Execution Workflow

1. Snapshot/backup first (required).
2. Activate `Better Search Replace` (installed, inactive).
3. Run dry-run search:
- Search: `34.105.65.179`
- Replace: `www.offkilter.tv`
- Start with targeted tables only (`wp_postmeta` menu keys, `wp_posts` published pages).
4. Apply safe replacements.
5. Manual-review queue for serialized/guid data.
6. Re-run counts and diff.

## Verification Checklist
- Main menu has no `34.105.65.179` links.
- Homepage and key pages load with HTTP 200.
- Arcana archives/search remain unchanged and functional.
- No broken media URLs on sampled video pages.

## Priority Order
1. Menu URL cleanup (`1692-1704`)
2. Published page hard-link cleanup
3. Option-level host URL cleanup (`bunnycdn`/theme URLs)
4. Manual review queue (guid/serialized)
