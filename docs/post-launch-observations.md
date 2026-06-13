# Post-Launch Observations

Date: 2026-06-13
Scope: monitor first live Arcana imports.

## Current Comment Policy (Verified)
- Current state: `comment_registration=0` (guest comments allowed).
- To enforce login-required comments:
1. `WP Admin -> Settings -> Discussion`
2. Enable: `Users must be registered and logged in to comment`
3. Click `Save Changes`

## What to Monitor After First Import
- WP Automatic campaign log shows successful run.
- Exactly one new post is created per manual test run.
- Post type is `vidmov_video`.
- Category assignment is correct:
  - `Arcana > Outcomes` for Outcome2026
  - `Arcana > Premonitions` for Prem
- Frontend video page loads and player renders.
- Existing VidMov content remains unaffected.

## Where Imported Videos Should Appear
- WP Admin: `Videos` list (`vidmov_video`).
- Frontend video URL path: `/video/{slug}/`.
- Category archives (VidMov):
  - `/video-category/...` (including Arcana branches once created/assigned).
- Homepage/video modules if theme query includes newest/published `vidmov_video`.

## How to Detect Failed Imports
- WP Automatic campaign status/log shows error or no new import.
- No new item appears in `WP Admin -> Videos` after `Run Now`.
- Imported item created as wrong post type.
- Imported item missing taxonomy assignment.
- Imported URL returns non-200 or fails to render embedded video.
- Repeated imports of the same video indicate duplicate-prevention failure.

## Operator Quick Checks (5 Minutes)
1. Run campaign once via `Run Now`.
2. Confirm one new `vidmov_video` in admin list.
3. Open post, verify Arcana category.
4. Open public URL in incognito, verify playback.
5. Review WP Automatic log for warnings/errors.
