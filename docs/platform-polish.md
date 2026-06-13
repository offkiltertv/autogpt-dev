# Platform Polish

Date: 2026-06-13

## Objective
Complete high-impact polish tasks for Arcana discovery launch without theme/plugin code changes.

## 1) Legacy Host Cleanup (`34.105.65.179`)

### Current reference footprint
- `wp_options`: 9
- `wp_posts.post_content`: 66
- `wp_posts.guid`: 153
- `wp_postmeta`: 30

### Safe To Replace
- Menu URL meta (`_menu_item_url`) rows tied to legacy menu entries.
- Published page hard links in `post_content` that directly reference the old host.
- Explicit URL options (example: CDN/base URL options) after UI-level verification.

### Manual Review Required
- `wp_posts.guid` (historical attachment identifiers).
- Serialized option blobs (`aioseo_*`, `beeteam368_*`, `fs_accounts`, `widget_custom_html`, etc.).
- Complex postmeta keys (`beeteam368_ads_mixed`, `_elementor_page_settings`, `_downloadable_files`, `rank_math_og_content_image`).

### Remediation Order
1. Main menu legacy links first (user-visible).
2. Published page content hard links.
3. Option-level URL corrections.
4. Serialized/manual review queue.

## 2) Homepage + Navigation Polish
- Add `🔮 The Arcana` above global feed rail.
- Add Arcana menu tree in main navigation.
- Add 3-link Arcana quick access in side menu.
- Keep Arcana cards as normal items (no global sticky strategy).

## 3) Search & Archive Discovery Polish
- Confirm Arcana, Premonitions, Outcomes archive pages remain indexable and linked from menu/homepage.
- Ensure imported Arcana posts retain category chips and creator line.
- Keep canonical browsing routes:
  - `/video-category/arcana/`
  - `/video-category/premonitions/`
  - `/video-category/outcomes/`

## 4) Operator QA Pass (Post-Change)
Run immediately after menu/homepage edits:
1. Homepage loads with Arcana rail visible.
2. Arcana rail cards open playable video pages.
3. Creator avatar/name links work.
4. Membership badge/rendering matches existing VidMov cards.
5. Search for a known Arcana title returns result.
6. Menu has zero links to `34.105.65.179`.
7. Mobile homepage/menu rendering passes.

## 5) Rollback Safety
If any visual/navigation issue occurs:
- Revert homepage section via Elementor revisions.
- Remove Arcana menu additions.
- Restore prior menu items.
- Re-run QA pass to confirm baseline restored.

## 6) Launch Recommendation
Proceed with admin-only rollout in this order:
1. Main menu cleanup + Arcana menu entries.
2. Homepage Arcana rail insertion.
3. Side menu quick links.
4. Final QA pass.

This sequence gives the biggest discovery lift with lowest operational risk.
