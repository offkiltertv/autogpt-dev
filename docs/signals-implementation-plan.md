# OKTV Signals Implementation Plan

Date: 2026-06-14
Branch: `feature/arcana-plugin`
Scope: low-risk Signals discovery layer on top of existing `vidmov_video` imports.

## Objectives
- Do not create a new importer.
- Do not create a new post type.
- Use existing WP Automatic -> VidMov ingestion.
- Classify imported videos as `Signal` (0-90s) or `Video` (90+s).
- Add discovery surfaces using existing WordPress/VidMov-compatible mechanisms.

## Current Inventory (Production Audit)
Source artifact: `docs/assets/signals/signal-classification-2026-06-14.json`

### Totals
- Total imported `vidmov_video`: `691`
- Signals (0-90s): `180`
- Videos (90+s): `262`
- Unknown duration: `249`

### Top creators by signal volume
- `💋Casey (Lipps)💋`: 113 signals
- `💋Lipps💋`: 46 signals
- `DaniElle Luminati`: 13 signals

### Category distribution
- `YouTube Embedded Library`: 180 signals
- `OffKilter.TV Creator - Free To View`: 180 signals
- `@lipps`: 167 signals
- Arcana categories currently have `0` signals (`Arcana`, `Premonitions`, `Outcomes` are mostly long-form today).

## Detection Strategy (Implemented)
Implementation location: `plugins/offkilter-arcana/includes/class-okarcana-signals.php`

Classification order:
1. Duration metadata keys (configurable; default `beeteam368_video_duration`)
2. Runtime parsed from post content (`Runtime: HH:MM:SS` or `Runtime: MM:SS`)
3. Fallback to `unknown`

Stored metadata:
- `_oktv_content_class` = `signal | video | unknown`
- `_oktv_is_signal` = `1 | 0`
- `_oktv_duration_seconds` = integer
- `_oktv_duration_source` = detection source
- `_oktv_signals_classified_at` = timestamp

## Low-Risk Changes Applied

### Plugin wiring
- Added `OKArcana_Signals` bootstrap/init in `offkilter-arcana.php`.
- Bumped plugin version to `0.1.1`.

### Automatic classification
- Hook: `save_post_vidmov_video` classifies imported WP Automatic posts.
- Scope-limited to imported posts (`wp_automatic_camp` exists).

### Backfill support
- New cron hook: `okarcana_signals_backfill` (hourly) to classify older imports in batches.
- Manual backfill action in Arcana admin: **Run Signals Backfill Batch**.

### Admin controls
Added settings under Arcana:
- Enable Signals classification
- Signal threshold (seconds)
- Backfill batch size
- Duration meta keys list

### Discovery shortcodes
- `[oktv_signals_latest]`
- `[oktv_arcana_signals_latest]`

These can be placed in Elementor Shortcode widgets without theme-core changes.

## Homepage Discovery Plan

## Rail: `⚡ Signals`
Recommended placement:
1. Hero
2. `🔮 The Arcana`
3. `⚡ Signals`
4. `👥 Featured Readers`
5. Trending / Latest

Widget path (no custom importer required):
- Elementor Shortcode widget
- Shortcode: `[oktv_signals_latest limit="10" show_creator="1" show_duration="1" show_date="1"]`

Duplicate reduction guidance:
- Keep Signals rail to 8-10 cards.
- Keep adjacent rails focused on long-form or creator-based queries.

## Creator Experience Plan
Goal tabs (VidMov-native where possible):
- Videos (existing)
- Signals (new filtered list by `_oktv_content_class=signal`)
- Discussion (existing/community)

Implementation-safe first step:
- Add Signals section on creator pages via shortcode/query block using author + signal class filter.
- No theme-core tab rewrites in Sprint 1.

## Arcana Integration Plan
Use only if/when Arcana sources include short-form clips:
- Shortcode: `[oktv_arcana_signals_latest limit="8"]`
- Scope categories: `Arcana, Premonitions, Outcomes`

Current state:
- Arcana inventory is predominantly long-form; Arcana Signals rail can be enabled once short clips accumulate.

## Navigation Placement
Primary recommendation:
- `Explore -> ⚡ Signals`

Optional secondary links:
- `Arcana -> Latest Signals`
- `Creators -> Signals`

Rationale:
- Keeps Watch/Arcana/Creators clean while making short-form discovery explicit.

## Architecture
YouTube
-> WP Automatic
-> `vidmov_video`
-> Signals classifier (`_oktv_content_class`)
-> Discovery rails (homepage / creator / Arcana)

## Rollout Plan
1. Deploy plugin update (`0.1.1`) to production.
2. Confirm Arcana settings include Signals controls.
3. Run one manual backfill batch.
4. Verify counts in Signals status table.
5. Add homepage `⚡ Signals` rail via Elementor shortcode widget.
6. Validate desktop/mobile/anonymous viewing.

## Rollback Plan
1. Remove homepage shortcode widget(s) for Signals.
2. Disable **Signals Classification** in Arcana settings.
3. Optional cleanup (non-destructive): leave metadata in place; no content deletion required.
4. If plugin rollback is needed, revert to previous plugin version and clear scheduled hook `okarcana_signals_backfill`.

## Screenshots (Baseline References)
These are the current UI baselines used for placement planning:
- Desktop homepage: `docs/assets/platform-polish-sprint/homepage-desktop-after.png`
- iPhone homepage: `docs/assets/platform-polish-sprint/homepage-iphone-after.png`
- Android homepage: `docs/assets/platform-polish-sprint/homepage-android-after.png`
- Tablet homepage: `docs/assets/platform-polish-sprint/homepage-tablet-after.png`

Post-implementation captures to add after production widget placement:
- Homepage with `⚡ Signals` rail (desktop/mobile)
- Creator page with Signals surface
- Arcana page with `🔮 Latest Signals` (once available)

## Risks
- `unknown_duration` currently high for some creators (notably `@LC`) because runtime data is absent.
- Short-form detection quality depends on upstream metadata consistency from imports.

## Next Safe Iteration
- Add one additional duration source field from WP Automatic if available in campaign mappings.
- Re-run backfill and measure unknown-duration reduction.
