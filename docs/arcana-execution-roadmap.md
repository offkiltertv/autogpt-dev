# Arcana Execution Roadmap

Date: 2026-06-13

## Objective
Convert approved Arcana rollout plans into a staged, low-risk implementation roadmap using only WordPress admin capabilities.

## Priority 1 - Discovery Lift (Immediate)
Goal: make Arcana visible to every visitor within one homepage load and one menu click.

Tasks:
1. Main menu upgrade (`Appearance -> Menus`):
   - Add top-level `Arcana` and children `Premonitions`, `Outcomes`, `Latest Readings`, `Readers`.
2. Legacy menu cleanup:
   - Replace/remove `34.105.65.179` links in menu items.
3. Homepage Arcana rail:
   - Duplicate Beeteam block `d826690` on homepage (`page 1244`) and filter to Arcana category.

Estimated effort: 1.5-2.5 hours
Risk: Low
Impact: Very High (largest discovery and UX gain)

## Priority 2 - Creator Unification (Short Window)
Goal: ensure Arcana creators are discoverable inside the existing creator ecosystem.

Tasks:
1. Keep Arcana cards displaying creator avatar/name in homepage Arcana rail.
2. Keep `Featured Creators` immediately below Arcana rail.
3. Confirm creator click-through routes to standard channel/member pages.
4. Ensure no Arcana-only creator silo is introduced in menus/widgets.

Estimated effort: 1-2 hours
Risk: Low
Impact: High (creator retention and cross-silo discovery)

## Priority 3 - Platform Polish + QA (Stabilization)
Goal: lock in reliability and clean legacy references without code edits.

Tasks:
1. Sidebar quick links for Arcana (Arcana/Premonitions/Outcomes).
2. Search/archive verification for Arcana routes.
3. Legacy host remediation phase 2:
   - published hard links
   - option-level URL fixes (manual review where serialized)
4. Post-change QA pass on desktop/mobile.

Estimated effort: 2-4 hours
Risk: Medium (data hygiene changes need caution)
Impact: Medium-High (trust and consistency)

## Fastest Path To Make Arcana Obvious To New Visitors
1. Add `Arcana` main menu entry with child links.
2. Add `🔮 The Arcana` rail above the current global homepage feed.
3. Keep `Featured Creators` directly below Arcana.
4. Add side-menu Arcana quick links.
5. Remove legacy IP links from navigation.

## Production Readiness (Admin-Only, No Code)
If no code changes are made, these provide the biggest improvements in order:
1. Menu: add Arcana tree + remove legacy IP menu links.
2. Homepage: clone Beeteam rail and filter to Arcana category.
3. Sidebar: add Arcana quick links.
4. QA: verify search/archive/playback/creator links on imported Arcana posts.
5. Cleanup: replace remaining published hard-coded old-host URLs.

## Stage Gates
Gate A (after Priority 1):
- Arcana visible on homepage above fold.
- Arcana reachable in one click from main nav.
- Zero legacy IP links in primary navigation.

Gate B (after Priority 2):
- Arcana creators appear in same creator surfaces as other silos.
- Creator click-through and subscription surfaces function normally.

Gate C (after Priority 3):
- No critical old-host references in visitor-facing pages.
- Mobile and desktop QA pass complete.
