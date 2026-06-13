# Alpha Minimum Viable Arcana

Date: 2026-06-13
Mode: Reality check using current codebase and existing production audit artifacts.

## Executive Answer
Yes. The current codebase can support this Alpha pipeline:

YouTube Playlist
-> WP Automatic
-> `arcana_entry`
-> Arcana Category
-> Disclaimer
-> Optional wpForo Topic (selected entries)

This is feasible now with no new feature development, as long as execution follows draft-first and duplicate-campaign-only restrictions.

## What Is Actually Required for Alpha
1. Arcana plugin installed and activated.
2. WP Automatic duplicate test campaign targeting `arcana_entry`.
3. Controlled test playlist source (non-production, low volume).
4. Draft-first import of exactly one entry.
5. Manual publish of that one entry to validate core Arcana flow; optional wpForo creation can be validated on the same entry.
6. Validation of no impact on existing VidMov campaigns.

## What Can Be Deferred Until Beta
1. Related Arcana frontend rendering/widgets/homepage modules.
2. Queue operations UI hardening (requeue/retry/reconciliation controls).
3. Full observability and reporting automation.
4. Expanded mapping automation beyond initial test sources.
5. Multi-campaign Arcana rollout and 90-day scaling automation.

## Code-Level Capability Check (Current Plugin)
1. `arcana_entry` CPT exists (`class-okarcana-post-types.php`).
2. Arcana taxonomies exist (`arcana_category`, `arcana_tag`).
3. WP Automatic campaign linkage detection exists (`wp_automatic_camp` in `class-okarcana-enhancer.php`).
4. Playlist mapping exists via settings (`OKArcana_Settings::find_mapping`).
5. Disclaimer injection exists (`class-okarcana-disclaimer.php`, `the_content` filter).
6. wpForo topic creation on publish exists (`class-okarcana-wpforo.php`, transition hook).

## Existing Infrastructure Leverage (No Rebuild)
From existing production audits in repo:
1. WP Automatic is already active and proven in production.
2. Existing campaigns already ingest YouTube content.
3. Existing campaign management supports duplicate + target post type changes.
4. Arcana enhancement layer can sit on top without replacing current stack.

## Known Constraints for Alpha
1. WP Automatic detection depends on campaign metadata conventions.
2. wpForo topic creation is runtime-dependent and must be validated with one real publish transition.
3. Operational safety depends on strict discipline: duplicate-only, draft-first, one-entry test.

## Minimum-Change Alpha Execution Plan
1. Do not edit any active VidMov campaign.
2. Duplicate one safe draft/non-critical campaign.
3. Point duplicate to one dedicated test playlist.
4. Set target post type to `arcana_entry` and status to `draft`.
5. Run one cycle.
6. Validate Arcana metadata + taxonomy + disclaimer.
7. Publish one test entry manually.
8. If discussion routing is enabled for the test, validate one wpForo topic and stored discussion links.
9. Confirm VidMov remains unaffected.

## Conclusion
Alpha business validation is possible immediately with current infrastructure and current Arcana code. The smallest safe validation is one duplicated draft-only campaign importing one test playlist entry into `arcana_entry`, followed by one controlled publish transition.
