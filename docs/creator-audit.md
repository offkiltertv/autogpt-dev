# OFFKILTER Creator Audit (Launch Curation Sprint — Phase 1)

**Date:** 2026-06-29
**Scope:** Inventory of imported creators ahead of the curated public launch, with a KEEP / ARCHIVE / REMOVE recommendation per creator.
**Method:** Repo-side audit from the source backlog CSVs (`data/arcana-master.csv`, `data/arcana-prem.csv`, `data/arcana-outcome2026.csv`) cross-referenced with the production read-only audit docs (`arcana-creators.md`, `arcana-channel-audit.md`, `wp-automatic-live-audit.md`, `food-for-thought-313-readiness.md`).

> ⚠️ **Counts come from two different worlds.** "Source rows" = entries in the backlog CSVs (what *can* be imported). "Live posts/term count" = what is *currently published* on offkilter.tv, taken from the production audit docs. They do not match because most of the backlog is not yet imported. Live mutations are not performed from this repo — see `legacy-creator-cleanup-runbook.md`.

---

## How creators are modeled (critical context)

Each creator exists in **two parallel systems**:

1. **Creator term** — a `vidmov_video_category` taxonomy term (e.g. `@lipps` = term **2055**). Drives channel pages and taxonomy/discovery surfaces.
2. **Author user** — a placeholder WordPress user that owns the `vidmov_video` posts (e.g. `FOOD_FOR_THOUGHT_313` = user **87**). Drives the "Creators to Watch" / "Creators on Pulse" rails (sorted by author post count).

**The core defect:** wp-automatic campaigns `8089`/`8090` (Arcana) hardcode category `2055` onto *every* Arcana import regardless of the true creator, so `@lipps` has become a **catch-all bucket** that legitimate launch content is filed into. Example: FOOD FOR THOUGHT 313's only live post (`8093`) is tagged `@lipps`. This is why `@lipps` must be **reassigned then archived**, never hard-deleted.

---

## Active import campaigns (production, from `wp-automatic-live-audit.md`)

| Campaign | Name | Source | Imported | Disposition |
|---|---|---|---|---|
| 4206 | `@Lipps` | youtube.com/@Lipps5 | 287 | **ARCHIVE** — deprecated launch/test creator |
| 4708 | `@LC` | youtube.com/@ShaniM4 | 213 | **ARCHIVE** — deprecated launch/test creator |
| 4168 | `@DaniElleLuminati` | youtube.com/@DaniElleLuminati | 61 | **ARCHIVE** — deprecated launch/test creator |
| 8089 | Arcana Outcome2026 | playlist `PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | live | **FIX** — remove term 2055 from category assignment |
| 8090 | Arcana Prem | playlist `PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | live | **FIX** — remove term 2055 from category assignment |
| 8092 | Arcana Outcome2026 **TEST** Single Video | test | 1 (post 8093) | **REMOVE** — test campaign artifact |

---

## Legacy creator terms (production, from `arcana-creators.md` / `arcana-channel-audit.md`)

| Creator term | Slug | Term ID | Live posts | Recommendation |
|---|---|---|---|---|
| `@lipps` / 💋Casey (Lipps)💋 | `lipps` | **2055** | 358 | **ARCHIVE** (reassign mis-tagged launch content first) |
| `@LC` | `lc` | — | 212 | **ARCHIVE** (reassign first if any legit content) |
| `@DaniElleLuminati` | — | — | 61 | **ARCHIVE** (reassign first if any legit content) |

These three are the historical test/launch imports that "no longer reflect the platform vision." None should be deleted outright — `@lipps` in particular holds reassignable launch content.

---

## Approved launch lineup (KEEP — priority)

User-designated launch creators. Source-row counts indicate available backlog depth.

| Creator | Source rows (master+prem) | Live state | Recommendation |
|---|---|---|---|
| FOOD FOR THOUGHT 313 | 164 | user 87 exists; 1 live post mis-tagged @lipps; profile empty | **KEEP** — flagship; needs own term + profile |
| Astraea 5D | 26 | backlog only | **KEEP** — needs term + import + profile |
| POSHRANDY55 | 2 | thin | **KEEP** — featured but backlog is thin; source more before heavy promotion |
| AllseeingisisOracle | 2 | thin | **KEEP** — same caveat |
| MADAMEBUTTERFLY444 | 1 | thin | **KEEP** — same caveat |

> Note: three of the five named launch creators (POSHRANDY55, AllseeingisisOracle, MADAMEBUTTERFLY444) have almost no backlog in the current CSVs. They can be *featured* via the curation layer, but the lineup will look thin until more of their content is sourced/imported. Flagged in the final report's recommendations.

---

## Data-driven Arcana top creators (KEEP — secondary lineup)

High-volume legitimate creators in the backlog, available to round out discovery once imported with correct creator terms (never 2055):

| Creator | Source rows |
|---|---|
| FOOD FOR THOUGHT 313 | 164 |
| TarasDivineTruth | 55 |
| The Bee Priestess | 52 |
| Intuitivegoddess333 | 45 |
| 🧿BeyondTheVeilTarot777🧿 | 41 |
| Let's.Hear.It.Spirit | 40 |
| EMPRESS OF DIVINE JUSTICE👑⚖️ | 40 |
| MEDVSA SUPREME | 31 |
| Straight From The Divine Tarot | 28 |
| Astraea 5D | 26 |

(Full distribution is derivable from `data/arcana-master.csv` + `data/arcana-prem.csv`, column `channel`.)

---

## REMOVE (test artifacts / non-creators)

| Item | Where | Recommendation |
|---|---|---|
| `NA` / blank channel rows | ~203 rows across master+prem | **REMOVE** from import scope — unattributable; do not import |
| Campaign 8092 "TEST Single Video" + post 8093 | production | **REMOVE/reassign** — post 8093 should be reassigned to FFT313's term, then the test campaign retired |

---

## Summary of recommendations

- **ARCHIVE (reassign-then-retire):** `@lipps` (2055), `@LC`, `@DaniElleLuminati`, and their campaigns 4206 / 4708 / 4168.
- **FIX upstream:** campaigns 8089 / 8090 — drop term 2055 from category assignment; assign creator-correct terms.
- **KEEP & promote:** the 5 named launch creators (priority) + the data-driven Arcana top creators (secondary).
- **REMOVE:** `NA`/blank source rows; the 8092 test campaign (after reassigning post 8093).

Execution of every live action above is documented step-by-step, with backups, in **`docs/legacy-creator-cleanup-runbook.md`**. The plugin-side durable enforcement (featured lineup + deprecated exclusion + scheduler hold) shipped in plugin **v2.8.0**.
