# Arcana Launch Checklist

Date: 2026-06-13
Environment: `https://www.offkilter.tv`
Goal: publish first Arcana videos via existing `WP Automatic -> VidMov` workflow.

## 1) Current WordPress Access Level (Verified)
- Server/operations access: SSH + `sudo` on production VM (`oktv-main-deployment-vm`, zone `us-west1-a`) and functional WP-CLI access.
- WordPress admin role exists (multiple `administrator` users found, including ID `1` and `itsjustrobby`).
- Required minimum for UI execution: WordPress user with `administrator` role.

## 2) Campaign Creation Paths (Verified)

### A. WP Admin UI (Recommended)
- Supported and lowest-risk path.
- Existing WP Automatic campaigns are already managed this way in production.
- Current non-trash campaign count: `4`.

### B. WP-CLI (Partial)
- No dedicated `wp automatic ...` command namespace detected.
- Possible only through generic post/meta commands and/or SQL inserts.
- Not recommended for first live Arcana import because campaign settings are serialized and easy to misconfigure.

### C. Direct Database Configuration (High Risk)
- Technically possible (`wp_automatic_camps` + related records).
- Not recommended for launch due to serialized fields (`camp_general`, `camp_options`) and validation bypass risk.

Decision: use **WP Admin UI only** for launch.

## 3) Preflight Checks (Do Before Creating Campaigns)
1. Login to WordPress Admin as `administrator`.
2. Go to `Plugins` and confirm active:
- `wp-automatic`
- VidMov core/theme extension plugins
- `armember`
3. Confirm VidMov target post type is present: `vidmov_video`.
4. Confirm/create VidMov categories (taxonomy `vidmov_video_category`):
- `Arcana` (parent)
- `Premonitions` (child of Arcana)
- `Outcomes` (child of Arcana)
5. Confirm WP cron is functioning (existing campaigns have recent successful runs).

## 4) Campaign Build Instructions (Exact Click Sequence)

### Campaign 1: Outcome2026
1. WP Admin -> `WP Automatic` -> `All Campaigns`.
2. Click `Add New`.
3. Enter Campaign Name: `Outcome2026 Playlist -> VidMov`.
4. In campaign type/source area, choose `YouTube`.
5. In YouTube source mode, select `Playlist`.
6. Paste Playlist URL:
- `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4`
7. Set target post type to: `vidmov_video`.
8. Set post status for first run to: `Publish`.
9. Set author to: primary admin/editor account (recommended: `user` / ID `1`).
10. In categories/taxonomy assignment, select taxonomy `vidmov_video_category` and assign:
- `Arcana`
- `Outcomes`
11. Keep duplicate prevention/caching options enabled (default WP Automatic duplicate protection).
12. Set import volume per run: `1`.
13. Set schedule interval:
- `1440` minutes for 1/day
- `480` minutes for 3/day
- `288` minutes for 5/day
14. Click `Publish` (campaign status active).
15. Click `Run Now` once for first validation import.

### Campaign 2: Prem
1. WP Admin -> `WP Automatic` -> `All Campaigns`.
2. Click `Add New`.
3. Enter Campaign Name: `Prem Playlist -> VidMov`.
4. Select `YouTube` campaign type.
5. Select source mode `Playlist`.
6. Paste Playlist URL:
- `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE`
7. Set target post type: `vidmov_video`.
8. Set post status: `Publish`.
9. Set author: primary admin/editor account.
10. Assign `vidmov_video_category` terms:
- `Arcana`
- `Premonitions`
11. Keep duplicate prevention enabled.
12. Set import volume per run: `1`.
13. Set schedule interval:
- `1440` minutes for 1/day
- `480` minutes for 3/day
- `288` minutes for 5/day
14. Click `Publish`.
15. Click `Run Now` once for first validation import.

## 5) Visibility & Access Verification (Production-Observed)
- Anonymous HTTP access currently returns `200` for:
- Homepage (`/`)
- VidMov search URL (`/?s=tarot&post_type=vidmov_video`)
- Community page (`/community/`)
- ARMember global site restriction is not enabled (`restrict_site_access` empty).

Conclusion for launch baseline:
- Imported `vidmov_video` posts are expected to be publicly visible unless a specific membership rule is later applied to those categories/posts.

## 6) Required Access Behavior to Validate

### Non-logged-in users must be able to:
- Browse video lists/pages.
- Search for videos.
- Watch video pages.

### Login should be required for:
- Forum posting/reply actions.
- Community/member-only actions.

Commenting note (current live setting):
- WordPress `comment_registration=0` and live video pages render guest comment fields (`name/email/comment`).
- That means login is currently **not** required for commenting.
- If you want login-required comments, enable:
  - `Settings -> Discussion -> Users must be registered and logged in to comment`.

## 7) Go/No-Go Criteria

### Go
- Both campaigns save successfully.
- `Run Now` creates `vidmov_video` posts.
- Imported posts contain correct Arcana category.
- Anonymous visitor can load imported video URL (HTTP `200`).

### No-Go
- Campaign saves but imports fail.
- Imported posts go to wrong post type.
- Imported posts are hidden by unexpected ARMember rule.
- WP Automatic duplicates flood existing content.
