# OFFKILTER Launch Content Curation — Report

**Date:** 2026-06-29
**Plugin version:** 2.8.0 (was 2.7.0)
**Branch:** feature/arcana-plugin
**Goal:** Move OFFKILTER from an experimental import environment to a deliberate, curated launch lineup — removing legacy/test creator imports (chiefly `@lipps` / Casey) and featuring the approved launch creators.

---

## Executive summary

The sprint split into two halves:

- **Repo (done in this branch):** a durable plugin curation layer so legacy creators can't dominate discovery, plus the audit, policy, runbook, and this report.
- **Live production (operator-executed):** the actual content reassignment, term archival, campaign fixes, homepage curation, and profile completion — all captured as a turnkey, backed-up runbook because this environment has no production credentials.

Two findings shaped the approach: (1) virtually all the content lives on the live site, not the repo; (2) **`@lipps` (term 2055, 358 posts) is a catch-all bucket holding legitimate launch content** — so it is **reassigned then archived, never deleted**.

---

## What shipped in the repo (plugin v2.8.0)

| Area | Change | File |
|---|---|---|
| Curation settings | `featured_creator_ids`, `deprecated_creator_user_ids`, `deprecated_creator_term_ids` + int[] helpers | `includes/class-okarcana-settings.php` |
| Discovery | "Creators to Watch" uses featured lineup first; else top-by-post-count **excluding deprecated** authors | `includes/class-okarcana-discovery.php` |
| Pulse | "Creators on Pulse" — same featured-first/exclude pattern | `includes/class-okarcana-pulse.php` |
| Scheduler | Holds (never publishes) imports tagged to a deprecated creator term; `okarcana_enable_scheduler` kill switch | `includes/class-okarcana-scheduler.php` |
| Admin UI | *Launch Curation* settings panel | `includes/class-okarcana-admin.php` |
| Version | 2.7.0 → 2.8.0 | `offkilter-arcana.php` |

All curation is at the **data/query layer — no CSS hiding**. Every edited PHP file passes `php -l`.

### Why a plugin layer, not just page edits

Discovery rails defaulted to `get_users(orderby=post_count DESC)`, so the highest-volume legacy creator wins automatically — and re-wins after every new import. The settings layer makes the launch lineup and the deprecated exclusions **stick** without re-editing pages each time content lands.

---

## What the operator must run on production

Full step-by-step with backups, dry-runs, and rollback: **`docs/legacy-creator-cleanup-runbook.md`**. Summary:

| Phase | Action | State |
|---|---|---|
| 2 | Reassign `@lipps` (2055) posts to correct creator terms by author, then archive the term | **Pending operator** |
| 3 | Drop term 2055 from wp-automatic campaigns 8089/8090; retire campaigns 4206/4708/4168; remove test campaign 8092 | **Pending operator** |
| 4–5 | Set v2.8 curation settings to the launch lineup; update Elementor/shortcode pages; flush caches / rebuild search index | **Pending operator** |
| 6 | Complete avatar/banner/bio/verified for each launch creator | **Pending operator** |
| 8 | QA checklist (no orphans, no broken links, no deprecated features/imports) | **Pending operator** |

This environment has no prod credentials (gcloud is on staging `rs-staging-2026`); the runbook is therefore the execution artifact, to be validated on staging first.

---

## Creators: removed/archived vs kept

(Full detail and counts in `docs/creator-audit.md`.)

- **ARCHIVE (reassign-then-retire):** `@lipps` / Casey (term 2055, 358 posts), `@LC`, `@DaniElleLuminati`.
- **Imports disabled:** wp-automatic campaigns 4206 (@Lipps), 4708 (@LC), 4168 (@DaniElleLuminati); test campaign 8092; and the catch-all term 2055 removed from Arcana campaigns 8089/8090.
- **KEEP (launch lineup, priority):** FOOD FOR THOUGHT 313, MADAMEBUTTERFLY444, POSHRANDY55, AllseeingisisOracle, Astraea 5D.
- **KEEP (secondary, data-driven):** TarasDivineTruth, The Bee Priestess, Intuitivegoddess333, BeyondTheVeilTarot777, Let's.Hear.It.Spirit, EMPRESS OF DIVINE JUSTICE, MEDVSA SUPREME, Straight From The Divine Tarot, and the rest of the Arcana top creators.
- **REMOVE:** `NA`/blank-channel source rows (unattributable); test artifacts.

---

## Homepage / discovery improvements

- Discovery and Pulse creator rails now honor a curated launch lineup and exclude deprecated creators (plugin), instead of ranking purely by legacy post volume.
- Operator sets the lineup once in *Arcana → Launch Curation*; optional explicit `creator_ids`/`featured_ids` on the Elementor/shortcode pages reinforce it.
- The scheduler hold guard prevents new mis-tagged `@lipps` content from surfacing during the transition.

---

## Remaining launch recommendations

1. **Thin backlog for 3 of 5 featured creators.** POSHRANDY55, AllseeingisisOracle, and MADAMEBUTTERFLY444 have ≤2 source rows in the current CSVs. Feature them, but source/import more of their content before heavy promotion, or the lineup reads sparse. FOOD FOR THOUGHT 313 (164 rows) and Astraea 5D (26) are launch-ready on depth.
2. **Fix the root cause, not just the symptom.** The single highest-leverage action is correcting campaigns 8089/8090 so no future Arcana import is stamped with the 2055 catch-all (`content-ingestion-policy.md` §3). Without it, mis-tagging recurs.
3. **Creator identity is the outreach gate.** FFT313 scores 52/100 readiness purely on empty profile + mis-mapping (`food-for-thought-313-readiness.md`). Complete Phase 6 for the lineup before any creator outreach.
4. **Deploy v2.8.0** via `scripts/deploy_offkilter_arcana.sh` → upload ZIP; then apply the curation settings (runbook §4b).
5. **Validate on staging first**, then promote — the curation layer behavior should be confirmed against real users/terms before prod.

---

## Constraints honored

- Discovery curation is **data-layer, never CSS** (Priority Zero).
- `@lipps` is **reassigned then archived, never hard-deleted**; backups precede every destructive live step; rollback documented.
- `beeteam368_membership_plans` untouched; no platform-protection bypass; no live production execution performed from this environment.
