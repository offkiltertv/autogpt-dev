# Arcana Alpha Go / No-Go Decision

Date: 2026-06-13
Decision Scope: OffKilter Arcana plugin alpha deployment test readiness.

## Classification
**Go with Restrictions**

## Justification
Why not No-Go:
1. Core implementation exists and is code-verified: CPT, taxonomies, settings, enrichment hooks, disclaimer, scheduler, wpForo publish hook.
2. Plugin package is buildable and PHP files lint clean.
3. Integration path is scoped to `arcana_entry`, reducing cross-impact risk on existing VidMov content.

Why not unrestricted Go:
1. WP Automatic integration is convention-based (`wp_automatic_camp` + campaign table), so environment differences can reduce mapping reliability.
2. wpForo topic creation is best-effort and depends on runtime API behavior/version.
3. Operational guardrails (detailed retry/requeue/reconciliation UI) are still limited.

## Restrictions Required for Alpha
1. Use duplicated test campaign only; do not alter existing active campaigns.
2. First run must publish to `draft` only.
3. One test source playlist only.
4. Manual verification checkpoint required before first publish transition.
5. Keep quick rollback path ready (deactivate plugin + disable test campaign).

## Go/No-Go Gates

### Go Gates (must all pass)
1. Plugin installs and activates cleanly.
2. `arcana_entry` and Arcana taxonomies register correctly.
3. Arcana settings page loads/saves correctly.
4. One test campaign creates one `arcana_entry` draft.
5. Arcana enrichment metadata and taxonomy mapping are applied.
6. Disclaimer renders on Arcana single page.
7. Publishing test entry creates one wpForo topic and stores links.
8. Existing VidMov content/campaigns unaffected.

### No-Go Triggers
1. Activation fatal errors or recurring warnings.
2. Test campaign causes changes to existing VidMov campaign behavior.
3. Arcana enrichment mutates non-Arcana content.
4. wpForo integration errors that block publishing flow.

## Safest Campaign to Duplicate (from existing repo audit)
Based on prior read-only production campaign snapshots already documented in repo:
- Preferred duplicate template candidate: `camp_id 6986` (`Bob Farrell`) due to draft/non-critical context in prior audit docs.

If that campaign is no longer low-risk at execution time:
1. Choose current draft/non-active campaign with same source type.
2. Duplicate only; do not edit original.
3. Preserve original schedule/status.

## Final Recommendation
Proceed with an Alpha deployment test only under the restrictions above.
