# OFFKILTER Content Ingestion Policy (Launch Curation Sprint — Phase 7)

**Date:** 2026-06-29
**Owner:** OFFKILTER editorial / platform ops
**Purpose:** Define how content enters OFFKILTER so the platform presents a deliberate, curated creator lineup rather than historical test imports. This policy is the standing reference for every future import.

---

## 1. Ingestion architecture (how content actually arrives)

- **Import engine:** the **wp-automatic** plugin (production only) pulls YouTube content into the `vidmov_video` post type. It is the *only* sanctioned ingestion path.
- **Enhancement layer:** the **OffKilter Arcana** plugin (this repo) enriches and schedules wp-automatic output — disclaimers, Signals classification, 90-day publish scheduling, wpForo topics, discovery curation. **Arcana does not import and must never be treated as an importer.**
- **Creator identity:** every imported post must carry a **creator-specific term** in the `vidmov_video_category` taxonomy, matching its true channel. A placeholder author user is created/assigned per creator.

---

## 2. Approved sources

A source is approved for ingestion only when **all** of the following hold:

1. It maps to a single, identifiable creator with a dedicated `vidmov_video_category` creator term.
2. The creator is on the KEEP list in `creator-audit.md` (or has been added via the approval process in §6).
3. The wp-automatic campaign's **category assignment names that creator's term** — never a catch-all term.

Currently approved: the launch lineup and the data-driven Arcana top creators listed in `creator-audit.md`.

---

## 3. Deprecated sources (do not re-enable)

The following are deprecated. Their campaigns must stay paused/retired and their terms archived:

- `@Lipps` / Casey (Lipps) — term **2055**, campaign **4206**.
- `@LC` — campaign **4708**.
- `@DaniElleLuminati` — campaign **4168**.

**Hard rule — no catch-all tagging:** no campaign may assign term **2055** (or any creator term that is not the post's true creator). The Arcana campaigns `8089`/`8090` must be corrected to drop 2055 and assign creator-correct terms. This single rule is the root-cause fix for the mis-attribution that motivated this sprint.

The plugin enforces a backstop: any imported post tagged to a **deprecated creator term** (configured in Arcana settings → *Launch Curation → Deprecated Creator Term IDs*) is **held** by the scheduler and never auto-published.

---

## 4. Test imports

- Test imports are allowed **only** on a clearly named test campaign (e.g. `… TEST …`) and **only** against a staging environment where possible.
- Test campaigns must be **retired and their artifacts removed/reassigned** before launch (e.g. campaign `8092` + post `8093`).
- Test content must never be assigned a real creator term or surface in discovery.
- `NA` / blank-channel source rows are not importable — they are unattributable and must be excluded from import scope.

---

## 5. Editorial review workflow

1. **Propose** — editor identifies a creator + source (channel/playlist) and confirms backlog depth.
2. **Verify identity** — confirm the creator's `vidmov_video_category` term exists (create if missing); confirm/assign the placeholder author user.
3. **Configure campaign** — create the wp-automatic campaign with category assignment = the creator's term (+ structural terms like Arcana/Outcomes/Premonitions as appropriate); never 2055.
4. **Stage** — run on staging or a small batch; confirm Signals classification, scheduling, and correct creator attribution.
5. **Review** — spot-check 3–5 imported posts: correct term, correct author, disclaimer present, no catch-all tag.
6. **Promote** — enable scheduled publishing; optionally feature via the curation layer (§7).

---

## 6. Creator approval process

A creator is added to the platform only after:

1. **Editorial approval** recorded against the KEEP list in `creator-audit.md`.
2. **Identity provisioning** — dedicated creator term + author user.
3. **Profile minimum** — avatar, banner, and a 2–4 sentence bio set (`okarcana_avatar`, `okarcana_banner`, `description`; legacy theme equivalents `beeteam368_user_avatar` / `beeteam368_user_channel_banner` where used). See `food-for-thought-313-readiness.md` for the readiness rubric.
4. **Verification flag** (`_ok_creator_verified`) set once the creator is claimed/confirmed.

Featuring a creator on the homepage/discovery rails is done via the curation layer, not by editing queries.

---

## 7. Discovery curation (durable enforcement)

Plugin **v2.8.0** adds an Arcana settings panel (*Launch Curation*) that governs discovery without per-page edits:

- **Featured Creator User IDs** — the ordered launch lineup used by "Creators to Watch" and "Creators on Pulse". When set, it overrides the default top-by-post-count behavior so legacy high-volume creators cannot dominate.
- **Deprecated Creator User IDs** — excluded from the discovery/pulse fallback.
- **Deprecated Creator Term IDs** — held by the scheduler (never auto-published).

Curation is enforced at the **data/query layer**, never by hiding content with CSS.

---

## 8. Future import guidelines (checklist)

- [ ] Source maps to one identifiable, approved creator.
- [ ] Dedicated creator term exists; campaign assigns it (not 2055 / not a catch-all).
- [ ] Author user assigned; profile minimum met before featuring.
- [ ] Test imports named/staged and cleaned up before launch.
- [ ] `NA`/blank rows excluded.
- [ ] New featured creators added to the *Featured Creator User IDs* setting; deprecated ones added to the deprecated lists.
- [ ] Post-import spot-check: correct term + author + disclaimer, no catch-all tag.
