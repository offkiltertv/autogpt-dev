# Public Video Access Audit and Remediation

Date: June 13, 2026 (America/Los_Angeles)
Environment: Production (`https://www.offkilter.tv`)

## Objective
Allow anonymous visitors to browse and watch videos without login while keeping community actions authenticated.

## Root Cause
Two controls were gating playback:

1. Global VidMov setting was enabled:
- Option: `beeteam368_video_settings.beeteam368_video_login_to_watch`
- Before: `on`
- After: `off`

2. Term-level membership gating was present on key video categories via:
- Term meta key: `beeteam368_membership_plans`
- Affected terms captured in:
  - `docs/assets/discovery-first-sprint/public-video-access-term-membership-before.tsv`

## Changes Applied
1. Updated global login gate to off.
2. Removed `beeteam368_membership_plans` term meta from video taxonomy terms that were forcing plan checks.
3. Flushed WordPress/object cache.

Evidence snapshots:
- Pre-change video settings: `docs/assets/discovery-first-sprint/beeteam_video_settings-before.json`
- Current validation: `docs/assets/discovery-first-sprint/public-video-access-validation.tsv`

## Validation
Validated anonymous access on representative video URLs:
- HTTP: `200`
- `Requires Login` marker: not present
- `login_to_watch` marker: not present
- Membership/paywall marker: not present
- YouTube source reference: present

Validated public discovery endpoints:
- Homepage: `https://www.offkilter.tv/` -> `200`
- Arcana archive: `https://www.offkilter.tv/video-category/arcana/` -> `200`
- Search: `https://www.offkilter.tv/?s=tarot` -> `200`
- Creator page: `https://www.offkilter.tv/channel/channel-id/@FOOD_FOR_THOUGHT_313/` -> `200`

Comments remain authenticated (expected behavior):
- Video pages show login prompt for commenting to anonymous users.

## Rollback
If playback gating must be re-enabled:

1. Restore `beeteam368_video_settings` from:
- `docs/assets/discovery-first-sprint/beeteam_video_settings-before.json`

2. Reapply term-level membership mappings from:
- `docs/assets/discovery-first-sprint/public-video-access-term-membership-before.tsv`

3. Flush cache:
- `wp cache flush --allow-root`

## Current State
Anonymous visitors can:
- browse videos
- open video pages
- watch video content

Anonymous visitors cannot (by design):
- post comments
- use creator/member actions requiring account authentication
