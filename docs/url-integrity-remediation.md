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

## 2026-06-17 Closeout Revalidation

### Backup
- `/opt/bitnami/backups/oktv-url-pass-20260617T101540Z/pre-url-pass.sql`

### Recheck Scope
- `wp_options`
- `wp_posts`
- `wp_postmeta`
- `wp_usermeta`
- `wp_termmeta`
- nav menu items + nav menu meta
- Elementor payloads (`_elementor_data`, `_elementor_page_settings`)
- VidMov/Beeteam option names
- cached menu/transient option patterns

### Database Results
Pre and post counts for `34.105.65.179` were all `0`:
- `wp_options`
- `wp_posts`
- `wp_postmeta`
- `wp_usermeta`
- `wp_termmeta`
- nav menu items
- nav menu meta
- Elementor data
- VidMov/Beeteam options
- cached menu data

Executed search-replace for:
- `http://34.105.65.179` -> `https://www.offkilter.tv`
- `https://34.105.65.179` -> `https://www.offkilter.tv`
- `//34.105.65.179` -> `//www.offkilter.tv`

Result:
- `Success: Made 0 replacements.` for all three patterns.

### Public HTML + Route Validation
- `https://www.offkilter.tv/` -> `200`, `ip_hits=0`
- `https://www.offkilter.tv/channel/` -> `200`, `ip_hits=0`
- `https://www.offkilter.tv/video-category/arcana/` -> `200`, `ip_hits=0`
- `https://www.offkilter.tv/video-category/signals/` -> `200`, `ip_hits=0`
- sample video `https://www.offkilter.tv/video/the-youtube-streets/` -> `200`, `ip_hits=0`

### Channel Tabs (Anonymous View)
- Channel tab entries for Videos, Audios, Playlists, Posts, Transfer History, Subscriptions, Watch Later, Notifications, History, Rated, Reacted, and About are present on `/channel/` with canonical `https://www.offkilter.tv` links/redirects.
- Discussion was not rendered in anonymous `/channel/` HTML during this pass (likely auth/context-gated).
- No rendered `/channel/` HTML references to `34.105.65.179` were found.
