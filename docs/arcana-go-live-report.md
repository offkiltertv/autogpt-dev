# Arcana Go-Live Report

Date: 2026-06-13
Environment: Production validation (read-only)

## Decision
**GO WITH MINOR CHANGES**

## Why Not Full GO Yet
Core pipeline is healthy, but discovery surfaces are still underexposed:
- Main navigation has no Arcana tree.
- Main menu still contains legacy `34.105.65.179` links.
- Homepage has no direct Arcana archive links.
- Arcana creator profiles are mostly unpopulated.

## Phase Findings

### 1) Homepage block validation
- Homepage `1244` and widget `d826690` are present and stable.
- Widget is clone-safe for Arcana rail rollout.
- No structural blocker found.

### 2) Arcana category health
- Archives all return HTTP 200:
  - `/video-category/arcana/`
  - `/video-category/premonitions/`
  - `/video-category/outcomes/`
- Counts are low but valid (`3`, `1`, `2`).
- Category descriptions are empty; no category image metadata identified in term meta.

### 3) Creator/channel readiness
- Arcana-related channel URLs return HTTP 200.
- Arcana authors exist and publish correctly.
- Creator profile metadata quality is low (empty descriptions/metadata).

### 4) Discovery gap analysis (2-click test)
Current first-time visitor path:
- Homepage: no direct links to Arcana archives found.
- Menu: no Arcana entries; contains legacy channel-tab links to old IP.
- Search: works (HTTP 200) but requires intent/keyword.
- Categories: archives work if user already has URL.
- Creator pages: available, but not an obvious Arcana entry point.

Conclusion:
- Arcana is **not reliably discoverable within 2 clicks** for a new visitor today.

## Exact Remaining Actions Before Homepage Rollout
1. Main menu cleanup:
   - remove/replace all `34.105.65.179` menu links.
2. Main menu Arcana tree:
   - `Arcana`
   - `Premonitions`
   - `Outcomes`
   - `Latest Readings`
   - `Readers`
3. Homepage Arcana rail:
   - clone `d826690`
   - filter duplicate to Arcana category (`2258`)
   - position above current global feed
4. Side menu quick links:
   - Arcana, Premonitions, Outcomes
5. Creator polish:
   - add minimal profile metadata (avatar + description) for Arcana import authors.
6. Post-change QA:
   - desktop/mobile menu
   - homepage block rendering
   - Arcana archive navigation
   - creator click-through + subscribe behavior

## Risk Level
- Overall: Low
- Highest risk: operator menu mistakes (wrong URL/ordering)
- Mitigation: apply changes in order, then run QA before sign-off.

## Go-Live Readiness Summary
- Infrastructure: ready
- Content ingestion: ready
- Discovery UX: needs minor admin updates
- Recommendation: execute runbook changes, then proceed live.
