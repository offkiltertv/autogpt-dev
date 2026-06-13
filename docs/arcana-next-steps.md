# Arcana Next Steps

Date: 2026-06-12
Scope: Post-audit delivery plan based on current code in `plugins/offkilter-arcana/`.

## Immediate
1. Run an Alpha-safe dry run using a duplicated WP Automatic campaign targeting `arcana_entry` with draft-first publishing.
2. Verify enrichment outcomes on test entries: playlist detection, taxonomy mapping, disclaimer render, related IDs metadata.
3. Verify publish transition creates a single wpForo topic and stores `_arcana_wpforo_topic_id` / `_arcana_wpforo_topic_url`.
4. Add minimal admin visibility for topic creation failures and queue anomalies.

## 30 Days
1. Add operational queue tooling in admin: requeue, retry, inspect payload, and one-click forum reconciliation.
2. Add frontend related-entry rendering (shortcode or template helper) using `_arcana_related_post_ids`.
3. Harden WP Automatic interoperability checks (campaign metadata discovery, graceful fallback paths).
4. Add lightweight automated tests for mapping and publish transition hooks.

## 90 Days
1. Add production observability: structured logs, event counters, and audit exports.
2. Add idempotent background reconciliation for missing forum links and stale related metadata.
3. Add Arcana homepage components (featured/new/most-discussed) only after enrichment pipeline is stable.
4. Define promotion gate from Alpha -> Beta -> Production with explicit pass/fail criteria.

## Release Gate Recommendation

### Alpha (Recommended Now)
- Status: Ready
- Reason: Core code path exists for CPT/taxonomies, enrichment, disclaimer, scheduling, and wpForo topic creation.
- Constraint: Must be validated with a duplicated campaign and draft-first workflow.

### Beta
- Status: Not ready
- Blocking items:
1. Better operational controls and retry workflows.
2. Related-entry frontend rendering.
3. Broader compatibility/error visibility.

### Production
- Status: Not ready
- Blocking items:
1. Reconciliation and reliability hardening.
2. Observability and auditability.
3. Automated regression coverage for critical hooks.
