# OKTV Platform Polish Sprint

Date: June 14, 2026 (America/Los_Angeles)
Branch: `feature/arcana-plugin`
Scope: Production UX polish using existing VidMov/Elementor/WordPress capabilities.

## Objectives
- Improve first impressions and discovery.
- Remove broken preview/logo references.
- Reduce stale creator exposure.
- Improve homepage relevance and navigation clarity.
- Validate mobile and anonymous viewer experience.

## Production Changes Applied

### Phase 1: Bunny / Preview Remediation
Applied:
- `bunnycdn_cdn_hostname` changed from broken `oktv.b-cdn.net` to canonical `www.offkilter.tv`.
- Canonicalized AIOSEO logo/social image references to HTTPS domain assets.

Results:
- `og:image` now resolves to canonical asset URL.
- `twitter:image` now resolves to canonical asset URL.
- `video_player_logo` in `vidmov_jav_js_object` now resolves to canonical asset URL.
- Broken Bunny 403 logo references removed from rendered homepage metadata/scripts.

Validation:
- `og:image` and `twitter:image` both return HTTP `200`.

Evidence:
- `docs/assets/platform-polish-sprint/state/option_bunnycdn_cdn_hostname.before.txt`

### Phase 2: Legacy Creator Cleanup
Applied:
- Replaced stale `Most Liked Videos` sidebar widget with `Arcana Highlights`.
- Updated widget query to Arcana categories (`arcana,premonitions,outcomes`).
- Removed obsolete sidebar blocks from `main-sidebar` (`New Playlists`, `News TV-Shows`).
- Removed stale right-side creator subscription widget from `sidemenu-sidebar` to prevent legacy-only creator exposure.

Results:
- Homepage sidebar now shows Arcana-relevant content only.
- Legacy creator ranking widget no longer dominates first impression.

Evidence:
- `docs/assets/platform-polish-sprint/state/widget_beeteam368_post_extensions.before.json`
- `docs/assets/platform-polish-sprint/state/widget_beeteam368_post_extensions.after.json`
- `docs/assets/platform-polish-sprint/state/sidebars-before-after.tsv`

### Phase 3: Channel Experience Polish
Applied:
- Navigation adjusted so top-level `Creators` points to populated `member-list` experience instead of sparse `/channel/` route.
- Removed duplicate child `Creator Directory` item to reduce confusion.

Result:
- First-click creator discovery now lands on a populated creator grid.

Unresolved:
- `/channel/` itself still renders sparse/placeholder behavior in current theme configuration and remains a Sprint 02 follow-up.

### Phase 4: Homepage Curation
Applied:
- Removed duplicate rail (`Latest on OKTV`) from homepage stack.
- Final rail order:
  1. `The Arcana`
  2. `Featured Readers`
  3. `Trending This Week`
  4. `Latest Uploads`
  5. `Recent Discussion`

Result:
- Unique purpose per rail.
- Cleaner content hierarchy, less duplicated feed behavior.

Evidence:
- `docs/assets/platform-polish-sprint/state/homepage-rails-before-after.tsv`

### Phase 5: Mobile Validation
Validated captures:
- iPhone width: `docs/assets/platform-polish-sprint/homepage-iphone-after.png`
- Android width: `docs/assets/platform-polish-sprint/homepage-android-after.png`
- Tablet width: `docs/assets/platform-polish-sprint/homepage-tablet-after.png`

Findings:
- Arcana remains visible near top of mobile scroll.
- Menu remains usable.
- Cookie banner is still heavy on small screens and remains a UX drag.

### Phase 6: Performance Quick Wins
Applied low-risk reductions:
- Removed one full homepage rail.
- Removed two stale left sidebar widgets.
- Removed right-side creator ranking widget.

Current measured payload snapshot:
- Homepage HTML: `581788` bytes
- Video page HTML: `302713` bytes

Current runtime snapshot:
- Homepage `TTFB`: `~1.41s`
- Homepage total transfer: `~1.54s`

## Navigation Outcome
Before (top-level order):
- Home, Explore, Creator Directory, Community, Arcana, Premonitions, Outcomes, Creators, Watch

After (top-level order):
- Home, Watch, Creators, Community, Arcana, Explore

Arcana children preserved:
- Premonitions
- Outcomes

Evidence:
- `docs/assets/platform-polish-sprint/state/main-menu-before-after.tsv`

## Validation Summary
Endpoint health:
- `https://www.offkilter.tv/` -> `200`
- sample Arcana video -> `200`
- `https://www.offkilter.tv/member-list/` -> `200`
- `https://www.offkilter.tv/channel/` -> `200`

Anonymous playback:
- login/paywall markers not present on validated video pages.
- YouTube source markers present.

Preview assets:
- OG/Twitter preview image URLs now canonical + `200`.

## Screenshots
- Homepage desktop: `docs/assets/platform-polish-sprint/homepage-desktop-after.png`
- Homepage iPhone: `docs/assets/platform-polish-sprint/homepage-iphone-after.png`
- Homepage Android: `docs/assets/platform-polish-sprint/homepage-android-after.png`
- Homepage tablet: `docs/assets/platform-polish-sprint/homepage-tablet-after.png`
- Channel page: `docs/assets/platform-polish-sprint/channel-desktop-after.png`
- Member list page: `docs/assets/platform-polish-sprint/member-list-desktop-after.png`

## Rollback Notes
Backups created on origin:
- `/opt/bitnami/backups/polish-sprint-20260614-131313`
- `/opt/bitnami/backups/polish-sprint-resume-20260614-131419`
- `/opt/bitnami/backups/polish-sprint-sideclean-20260614-132041`

Local state artifacts:
- `docs/assets/platform-polish-sprint/state/`

## Unresolved Issues
1. `/channel/` route still presents sparse content/placeholder behavior.
2. Member list cards still include low-quality/default-avatar creator entries.
3. Cookie banner consumes significant mobile viewport.

## Recommendations (Next Sprint)
1. Channel directory behavior remediation:
- determine exact inclusion criteria for `/channel/`.
- align channel source with active Arcana creator set.
2. Member profile quality pass:
- prioritize avatar/profile completeness for surfaced creators.
3. Cookie/consent UX tuning:
- reduce visual dominance on mobile while preserving compliance.
4. Controlled creator curation:
- promote high-readiness creators in discovery surfaces only.
