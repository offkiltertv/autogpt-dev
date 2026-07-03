# YouTube Avatar / Profile Sync — Execution Report

**Date:** 2026-07-02
**Plugin:** v3.1.0 deployed and active
**Implements:** `docs/arcana-avatar-automation-roadmap.md` + `docs/avatar-remediation-execution.md`
**Backups:** `/opt/bitnami/backups/v3-20260702-121814/avatar-meta-before-v31.tsv` (pre-change avatar usermeta for users 87–91)

---

## What shipped

**`OKArcana_Avatar_Sync`** (`includes/class-okarcana-avatar-sync.php`) — post-import creator avatar enrichment, no VidMov/WP-Automatic core edits:

- **Trigger:** `save_post_vidmov_video` (priority 50). When an imported video's author has no avatar, enrichment runs once; guards skip revisions, autosaves, already-avatared users, and prior successes.
- **Resolver:** `channel_id` post meta → `youtube.com/channel/{id}` → `og:image` (yt3) — with a fallback chain of source video URL → YouTube **oEmbed** `author_url` → channel-page scrape. Avatar URL normalized to `=s512-c`.
- **Apply:** media sideload → Beeteam avatar size-set (`beeteam368_user_avatar` map of 28/56/50/100/61/122 + original) + `beeteam368_user_avatar_wd_bf`/`_id` → `vidmov_user_profile` linkage → **`okarcana_avatar` mirror** so plugin discovery surfaces and theme surfaces show the same avatar.
- **Backfill / pilot API:** `sync_user($id, $post, $dry_run)` (dry-run resolves + reports without writing); `mirror_from_beeteam($id)` reconciles pre-existing theme avatars to the plugin key with no network.
- **Safety:** `enable_avatar_sync` setting + `okarcana_avatar_sync_enabled` filter kill switch; per-user `_okarcana_avatar_sync` status (`synced` / `failed:<reason>`); failures are recorded, never retried in a loop, never fatal.

## Execution (gated, production)

1. **Egress verified** — server reaches YouTube oEmbed (HTTP 200, `author_url` resolved).
2. **Mirror pass (no network)** — `okarcana_avatar` populated for all 5 launch creators from their existing theme avatars, fixing the plugin/theme split immediately.
3. **Dry-run resolver** — all 5 resolved to real `yt3.googleusercontent.com` avatars, **including users 88 and 89 which have no `channel_id`** (proving the oEmbed→author_url→scrape fallback).
4. **Applied real sync** — replaced the prior placeholder avatars (`arcana-u88-avatar.jpg` etc.) with authentic 512×512 Google channel avatars (attachments 10760–10764). Old meta snapshotted first.

## Verification

- Avatar files: HTTP 200, real JPEGs 50–113 KB, EXIF `software=Google`, 512×512.
- FFT313 avatar visually confirmed as the creator's actual channel photo.
- Discover spotlights: 5/5 reference the new `*-yt-avatar` files; browser check shows all `complete:true`, natural 512×512, rendered 72×72 (no layout shift; empty circles in an early headless capture were lazy-load paint timing, not a defect).
- Channel (theme) pages render the synced avatar too (shared `beeteam368_user_avatar`).

## Result vs the roadmap

| Roadmap goal | Status |
|---|---|
| Post-import trigger, no core edits | ✅ `save_post_vidmov_video` |
| channel_id → avatar (primary) | ✅ |
| video → oEmbed author_url (fallback) | ✅ (used for 88/89) |
| Sideload + Beeteam size-set + profile linkage | ✅ |
| Validation + failure policy + rollback | ✅ status meta + snapshot |
| Future imports auto-populate | ✅ active for all new `vidmov_video` imports |

## Forward behavior

Any newly imported creator without an avatar is now auto-enriched from their YouTube channel on import — the manual remediation the roadmap described is no longer needed for new creators. Existing un-avatared site creators (the ~39 outside the launch lineup) can be backfilled on demand with `wp eval 'OKArcana_Avatar_Sync::sync_user(<id>);'`.

## Rollback

Per user: restore `beeteam368_user_avatar*` + `okarcana_avatar` from `avatar-meta-before-v31.tsv`; delete attachments 10760–10764; clear `_okarcana_avatar_sync`. Full DB dump in the same backup dir is the backstop.
