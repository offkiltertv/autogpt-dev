# OFFKILTER Launch Content Curation — Report

**Dates:** 2026-06-29 (repo sprint) / **2026-07-01 (live production execution)**
**Plugin version in repo:** 2.8.0 (deployment to prod deliberately deferred — see below)
**Branch:** feature/arcana-plugin
**Goal:** Move OFFKILTER from an experimental import environment to a deliberate, curated launch lineup — removing legacy/test creator imports (chiefly `@lipps` / Casey) and featuring the approved launch creators.

---

## Executive summary

The sprint ran in two halves, **both now complete**:

- **Repo (2026-06-29):** durable plugin curation layer (v2.8.0) + audit/policy/runbook docs.
- **Live production (2026-07-01):** executed directly against `oktv-main-deployment-vm` (project `offkilter-tv`) via SSH-over-IAP + WP-CLI, following the runbook with three user-approved gates (mutation manifest, mass unpublish, plugin-deploy decision). Everything below is verified with post-change checks; every destructive step has a snapshot/restore path.

**Restore root:** `/opt/bitnami/backups/curation-20260701-152352/` — full DB dump (`full-db.sql.gz`, gzip-verified), `wp_automatic_camps` dump, taxonomy-tables dump, and per-worklist ID snapshots (`term2055-post-ids.txt` 1480, `unpublish-lipps-ids.txt` 308, `unpublish-lc-ids.txt` 218, `unpublish-danielle-ids.txt` 61, `unpublish-caseysue-ids.txt` 2, `unpublish-profile-ids.txt` 8, `subscribe-count-before.txt`).

---

## What the live state actually was (deviations from the June docs)

The read-only audit found the production reality had moved past the June documentation:

| Item | June docs | Found live (2026-07-01) |
|---|---|---|
| Term 2055 `@lipps` | 358 posts | **1,480 published posts** — imports had continued |
| Lipps-family accounts | users 17/49/54 | **+ user 48 “Casey Sue has some shyt to say”** (discovered during QA via search results) |
| Arcana plugin | assumed live | **never installed on prod** |
| Homepage | shortcode risk | Elementor front page (1244) with **launch-creator posts already pinned**; the deprecated-creator exposure came from the theme's "Most Subscriptions" channels widget + dynamic feeds |
| Launch creators | empty profiles | users 87–91 confirmed; **avatars already set** (prior sprint); bios/banners empty |
| wp-automatic campaigns | table-only | campaigns are **also `wp_automatic` posts** — post status `publish` = campaign active |

---

## Executed changes (production, all verified)

### Phase 3 — Import automation ✅
- Campaigns **8089/8090/8092**: category assignment **no longer contains 2055** (`2258,2260,968` / `2258,2259,968` / `2258,2260,968`). Root cause of mis-tagging eliminated.
- Deprecated campaigns **4206 (@Lipps), 4708 (@LC), 4168 (@DaniElleLuminati), 8092 (TEST)** doubly disabled: `camp_post_status='draft'` (any future import arrives unpublished) **and** the campaign posts themselves set to `draft` (campaign paused).
- Verified: no active campaign references term 2055; only 8089/8090 (corrected Arcana feeds) remain active.

### Phase 2 — Legacy creator cleanup ✅
- **Reassignment (1,480 posts processed, 0 errors):** kept 2055 on the 308 genuine Lipps posts; **stripped 2055 from 1,172 mis-tagged posts**; appended correct creator terms to 110 launch-creator posts.
- **New launch creator terms:** `@FOOD_FOR_THOUGHT_313` (4727, 78 posts), `@AllseeingisisOracle` (4728, 1), `@MADAMEBUTTERFLY444` (4729, 1), `@POSHRANDY55` (4730, 2), `@Astraea_5D` (4731, 28).
- **Unpublished to draft (restorable): 589 posts** — Lipps family 308 + Casey Sue 2 + @LC 218 + @DaniElle 61. Verified 0 published `vidmov_video` by any deprecated author.
- **Channel records:** the 8 `vidmov_user_profile` posts of deprecated accounts drafted.
- **Terms archived by rename (never deleted):** 2055 → `Archived - Lipps`, 2056 → `Archived - LC`, 2059 → `Archived - DaniElleLuminati`; all now count 0 published posts.
- **Discovery ranking meta:** deprecated users' `beeteam368_subscribe_count` rows snapshotted then deleted (they powered the "Most Subscriptions" channels widget independent of post status); launch creators added at an honest `0`.

### Phases 4–5 — Homepage & discovery ✅
- Front page's "🔮 The Arcana" and "👥 Featured Creators" blocks already pin launch-creator posts (8093/8095/8096/8102/8184/8226/8255 — all published, all authors 87–91). No Elementor edit needed.
- The "Most Subscriptions" side widget now lists real members + launch creators; **zero deprecated author-cards on the homepage** (verified by HTML inspection).
- Caches: `wp cache flush` ×3, 1,519 transients deleted. Cloudflare serves pages `DYNAMIC` (uncached); BunnyCDN has no WP-CLI purge — **operator: purge via BunnyCDN dashboard** if stale assets linger.

### Phase 6 — Creator identity ✅ (partial by design)
- Bios (`description`) set for users 87–91 — **first-pass editorial copy, only where empty; review/replace freely.**
- Avatars: already present (prior remediation, attachments 8107–8115).
- **Banners: no brand assets exist in the media library — gap.** Theme default covers meanwhile.

### Phase 8 — QA ✅
- Archived terms: 0 published posts each.
- Drafted content 404s publicly (spot-checked).
- Search `?s=lipps`: no removed-creator content (Casey Sue birthday videos gone); remaining hits are other creators' videos with incidental text mentions.
- Homepage: 0 deprecated author-cards; launch creators present (FFT313 ×24 links, each other launch creator ×12).
- Launch channel pages all HTTP 200; deprecated channel URLs still resolve (accounts exist) but are linked from nowhere — see Remaining items.

---

## Deliberately deferred / not done

1. **Arcana plugin v2.8.0 not deployed** (user decision): prod has no page using its shortcodes, so its curation settings would be inert. Ship as its own rollout; the v2.8 settings then formalize the featured/deprecated lists.
2. **Deprecated user accounts kept** (not deleted): they own the 589 drafted posts; deletion would force reattribution and break restorability. Revisit only after a retention decision.
3. **"Archived -" options in the category-filter dropdown:** the theme widget renders empty terms. Harmless and clearly labeled; hide via the widget's settings in Elementor/Customizer if desired (terms must not be deleted).
4. **@lipps/@LC/@DaniElle channel URLs still 200:** account shells exist. Optional operator follow-ups: redirect rules, or account-level `spam`/deactivation via theme options.

---

## Remaining launch recommendations

1. **Editorial triage of the mainstream-import sprawl.** Stripping 2055 exposed ~1,000+ published videos from ~350 incidental channels (Fox News, CNBC, penguinz0, Tucker Carlson…) imported from personal playlists. They're now correctly *unattributed to Lipps* but still published under placeholder authors. Decide keep/curate/unpublish per channel — this is an editorial call, not done unilaterally in this sprint.
2. **Thin backlogs:** MADAMEBUTTERFLY444 (1 post), AllseeingisisOracle (1), POSHRANDY55 (2) are featured but shallow — source more content before heavy promotion. FFT313 (78) and Astraea 5D (28) are launch-ready.
3. **Banner assets** for the launch five; then set `beeteam368_user_channel_banner`.
4. **Review the drafted bios** (first-pass copy).
5. **"🔮 Featured Readers" widget instance** exists (inactive) — activating it in a sidebar is a one-click editorial upgrade.
6. **Deploy plugin v2.8.0** when the platform layer (Pulse/Signals/Discovery pages) is ready; its settings-driven curation replaces today's meta-level arrangement.
7. **BunnyCDN dashboard purge** for asset caches.

---

## Rollback

Everything is reversible from `/opt/bitnami/backups/curation-20260701-152352/`:
- Full restore: `wp db import` of `full-db.sql.gz` (gunzip first).
- Surgical: republish any ID list (`unpublish-*.txt`) via `wp post update <id> --post_status=publish`; campaign rows restorable from `wp_automatic_camps.sql`; term names/slugs trivially renamed back; `subscribe-count-before.txt` holds the widget-meta originals.

## Constraints honored

- All curation at the **data/query layer — no CSS hiding** (Priority Zero).
- `@lipps` **reassigned then archived — nothing deleted**; every destructive step snapshotted first; gates approved by the user before mutations.
- `beeteam368_membership_plans` untouched. No platform-protection bypass.
