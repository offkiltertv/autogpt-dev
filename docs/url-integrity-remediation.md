# URL Integrity Remediation: 34.105.65.179 (2026-06-16)

## Objective
Ensure no public-facing OFFKILTER.TV content links to legacy IP `34.105.65.179` and validate channel/video navigation integrity.

## Database Inspection Scope
Checked exactly as requested:
- `wp_options`
- `wp_posts`
- `wp_postmeta`
- `wp_usermeta`
- `wp_termmeta`
- nav menu items (`nav_menu_item` + `_menu_item_url`)
- Elementor payloads (`_elementor_data`, `_elementor_page_settings`)
- VidMov/Beeteam option patterns

## Backup Created
- `/opt/bitnami/backups/oktv-jetpack-recovery-20260616T211839Z/pre-url-remediation.sql`

## Findings (Pre-Remediation)
`34.105.65.179` hit counts:
- `wp_options`: 0
- `wp_posts`: 0
- `wp_postmeta`: 0
- `wp_usermeta`: 0
- `wp_termmeta`: 0
- nav menu query: no rows
- Elementor query: no rows
- VidMov/Beeteam option query: no rows

## Remediation Executed
Ran safe replacement commands across requested public-facing WordPress tables:
- `http://34.105.65.179` -> `https://www.offkilter.tv`
- `https://34.105.65.179` -> `https://www.offkilter.tv`

Result:
- `Success: Made 0 replacements.` for both commands (no stale values remained in targeted DB scope).

## Post-Remediation Validation
### Database
Counts remained zero across all requested tables.

### Live HTML checks
Checked rendered HTML for `34.105.65.179` on:
- homepage
- `/wp-admin` route output chain
- `/video-category/arcana/`
- `/video-category/signals/`
- `/channel/`
- sample video page: `https://www.offkilter.tv/video/beer-oclock-ayo-lets-goooooooo-2/`
- channel tab pages (videos, audios, playlists, posts, transfer-history, subscriptions, watch-later, notifications, history, rated, reacted, about, discussion)

Result:
- `ip_hits=0` across all validated responses.

### Menu link validation
Validated main menu links currently rendered on homepage:
- `/`
- `/video/`
- `/video-category/signals/`
- `/video-category/arcana/`
- `/video-category/arcana/premonitions/`
- `/video-category/arcana/outcomes/`
- `/creators/`
- `/community/`
- `/trending/`

Result:
- All returned HTTP 200 and no IP leakage in HTML.

### Videos + channel tabs
- Videos tab URL returned 200.
- Tested channel-tab routes returned 200 and no IP leakage.

### Anonymous playback sanity
- Sample public video page returned 200.
- Rendered page contains active player initialization (`beeteam368_pro_player`) and YouTube source payload.

## Conclusion
No live `34.105.65.179` references were found in requested DB scope or validated production HTML outputs. Public navigation and channel/video tab routes are functioning and canonicalized to `https://www.offkilter.tv`.
