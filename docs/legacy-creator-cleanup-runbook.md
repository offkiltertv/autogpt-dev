# Legacy Creator Cleanup — Operator Runbook

**Date:** 2026-06-29
**Audience:** Operator with production access (you). **These steps run against the LIVE site and are not performed from the repo.**
**Covers:** Sprint Phases 2 (reassign-then-archive @lipps), 3 (imports), 5 (discovery refresh), 6 (creator identity), 8 (QA).
**Companion docs:** `creator-audit.md` (what/why), `content-ingestion-policy.md` (rules), `launch-content-curation-report.md` (summary).

> 🛑 **Golden rules**
> 1. **Back up before every destructive step.** Restore path is defined in §1.
> 2. **`@lipps` (term 2055) is reassigned, then archived — never hard-deleted.** It holds legitimate launch content.
> 3. **Validate on staging first.** `gcloud` is currently authed to `rs-staging-2026`; run the whole flow there before prod.
> 4. **Dry-run first.** Every mutation script below has a `DRY_RUN` switch — run it `1` first, read the output, then `0`.
> 5. Prefer **WP-CLI** (hooks-aware) over raw SQL. Raw SQL is given only as a labeled fallback.

---

## 0. Access & environment

```bash
# Staging first (current gcloud project). Swap to the prod project/VM only after staging passes.
gcloud compute ssh oktv-main-deployment-vm --zone=us-west1-a --tunnel-through-iap

# On the VM (Bitnami stack). Run wp as the web user against the WP root.
export WP="sudo -u daemon wp --path=/opt/bitnami/wordpress"
$WP option get siteurl     # sanity: confirm you're on the right site
$WP core version
```

Confirm the creator taxonomy and the target term before touching anything:

```bash
$WP term list vidmov_video_category --fields=term_id,slug,name,count | grep -iE 'lipps|^2055|@LC|DaniElle'
```

---

## 1. Backups (do this first, every time)

```bash
STAMP=$(date +%Y%m%d-%H%M%S)
# Full logical backup (safest restore path)
$WP db export "/opt/bitnami/backups/pre-curation-${STAMP}.sql"

# Targeted dump of just the tables this runbook mutates (fast restore of a single concern)
$WP db export "/opt/bitnami/backups/pre-curation-tax-${STAMP}.sql" \
  --tables=$($WP db prefix --allow-root 2>/dev/null)terms,$($WP db prefix)term_taxonomy,$($WP db prefix)term_relationships,$($WP db prefix)posts,$($WP db prefix)postmeta

# Snapshot the affected post IDs (the @lipps set) as the reassignment worklist + restore reference
$WP post list --post_type=vidmov_video --vidmov_video_category=lipps --format=ids > "/opt/bitnami/backups/lipps-post-ids-${STAMP}.txt"
wc -w "/opt/bitnami/backups/lipps-post-ids-${STAMP}.txt"   # expect ~358
```

**Restore:** `$WP db import /opt/bitnami/backups/pre-curation-<STAMP>.sql`.

---

## 2. Phase 2 — Reassign, then archive `@lipps` (term 2055)

### 2a. Make sure each launch creator has a real creator term

```bash
# Example: create a term if missing (repeat per launch creator). Capture the term_id.
$WP term get vidmov_video_category food_for_thought_313 2>/dev/null \
  || $WP term create vidmov_video_category "FOOD FOR THOUGHT 313" --slug=food_for_thought_313 --porcelain
```

### 2b. Build an author→correct-term map

`@lipps` is a catch-all; the reliable signal for a post's *true* creator is its **author user** (the per-channel placeholder user). List the authors currently in the @lipps set, then map each to the correct creator term ID:

```bash
for pid in $(cat /opt/bitnami/backups/lipps-post-ids-*.txt | tr ' ' '\n' | sort -u); do
  $WP post get "$pid" --field=post_author
done | sort | uniq -c | sort -rn   # author_id => count, to build your map
```

Fill in the map (author user ID → correct creator term ID) from that output, e.g. `87 => <FFT313 term_id>`.

### 2c. Reassign (DRY_RUN first)

Save as `/tmp/reassign-lipps.php` on the VM, edit `$MAP` and `$LIPPS_TERM`, then run:

```php
<?php
// wp eval-file /tmp/reassign-lipps.php
$DRY_RUN    = 1;            // set to 0 to apply
$LIPPS_TERM = 2055;
$TAX        = 'vidmov_video_category';
$MAP        = array(
    // author_user_id => correct_creator_term_id
    87 => 0,   // FOOD FOR THOUGHT 313 -> set real term_id
    // 88 => ..., 89 => ...,
);

$posts = get_posts(array(
    'post_type'   => 'vidmov_video',
    'post_status' => 'any',
    'numberposts' => -1,
    'fields'      => 'ids',
    'tax_query'   => array(array('taxonomy' => $TAX, 'field' => 'term_id', 'terms' => $LIPPS_TERM)),
));
echo "Found " . count($posts) . " posts tagged term {$LIPPS_TERM}\n";

$reassigned = 0; $skipped = 0;
foreach ($posts as $pid) {
    $author = (int) get_post_field('post_author', $pid);
    $target = isset($MAP[$author]) ? (int) $MAP[$author] : 0;
    if ($target <= 0) { $skipped++; echo "SKIP post {$pid} (author {$author}, no mapped term)\n"; continue; }
    echo ($DRY_RUN ? "[dry] " : "") . "post {$pid}: +term {$target}, -term {$LIPPS_TERM}\n";
    if (!$DRY_RUN) {
        wp_set_object_terms($pid, array($target), $TAX, true);   // append correct term
        wp_remove_object_terms($pid, array($LIPPS_TERM), $TAX);  // drop @lipps
    }
    $reassigned++;
}
echo "Reassigned: {$reassigned}, Skipped (need mapping): {$skipped}\n";
```

```bash
$WP eval-file /tmp/reassign-lipps.php   # DRY_RUN=1 → review → set 0 → re-run
```

Posts whose author has no mapped term are **skipped, not touched** — resolve them (assign the right author/term) and re-run until skipped = 0.

### 2d. Archive the now-empty term

```bash
$WP term get vidmov_video_category 2055 --field=count    # expect 0 after reassignment
# Archive (do NOT delete): rename so any lingering reference is obviously retired,
# and remove it from menus/featured. Keep the term row for audit/rollback.
$WP term update vidmov_video_category 2055 --name="Archived — Lipps" --slug=archived-lipps
```

> Only consider deletion **after** count is 0 and you've confirmed nothing links to it. Reassignment + archive satisfies the sprint goal without data loss. Repeat 2b–2d for `@LC` and `@DaniElleLuminati` if they likewise hold reassignable content; otherwise archive them directly once confirmed empty of launch content.

---

## 3. Phase 3 — Imports / automation

**Document then disable** (these live in the wp-automatic plugin UI / its `wp_automatic_camps` table — there is no Arcana code path for them):

1. **Stop the catch-all tagging** on Arcana campaigns `8089` (Outcome2026) and `8090` (Prem): edit each campaign's **category assignment** → remove `2055`, add the creator-correct term(s). This is the root-cause fix.
2. **Retire deprecated creator campaigns:** set campaigns `4206 @Lipps`, `4708 @LC`, `4168 @DaniElleLuminati` to inactive/draft (or delete in the wp-automatic UI).
3. **Remove the test campaign** `8092` after post `8093` is reassigned (Phase 2).

Record for the report — import source (wp-automatic → `vidmov_video`), automation path, scheduler (Arcana 15-min `okarcana_schedule_queue`), and the corrected campaign config.

**Plugin backstop (already shipped, v2.8.0):** set Arcana → *Launch Curation* → **Deprecated Creator Term IDs = 2055** so any still-mis-tagged import is *held* (never auto-published) until the campaign fix lands:

```bash
# Optional global kill switch for the Arcana publish scheduler while you work:
# add to a small mu-plugin or theme functions: add_filter('okarcana_enable_scheduler','__return_false');
```

---

## 4. Phase 4–5 — Homepage & discovery refresh

### 4a. Resolve launch-creator user IDs

```bash
for u in FOOD_FOR_THOUGHT_313 MADAMEBUTTERFLY444 POSHRANDY55 AllseeingisisOracle Astraea_5D; do
  echo -n "$u => "; $WP user get "$u" --field=ID 2>/dev/null || echo "(not found — check exact login)"
done
```

### 4b. Set the durable curation settings (v2.8.0)

In **WP Admin → Arcana → Launch Curation**, or via CLI (patches the `okarcana_settings` option — values are re-sanitized on next save through the UI):

```bash
$WP option patch update okarcana_settings featured_creator_ids "87,<MB>,<PR>,<AO>,<A5>"
$WP option patch update okarcana_settings deprecated_creator_user_ids "<legacy @lipps author user id(s)>"
$WP option patch update okarcana_settings deprecated_creator_term_ids "2055"
$WP option get okarcana_settings --format=json | tr ',' '\n' | grep -iE 'creator'
```

This makes "Creators to Watch" / "Creators on Pulse" use the launch lineup instead of top-by-post-count — so legacy creators can't re-dominate even after new imports.

### 4c. Homepage (Elementor) + shortcode pages

- For any page rendering `[oktv_discover_page]` / `[oktv_pulse_destination]`, set explicit `creator_ids="…"` / `featured_ids="…"` to the launch IDs (belt-and-suspenders alongside 4b).
- Update Elementor homepage modules to feature the launch lineup; remove any module hardcoded to archived creators.

### 4d. Refresh caches / indexes

```bash
$WP cache flush
$WP transient delete --all
# If a search index plugin (e.g. SearchWP/Relevanssi) is active, rebuild it:
# $WP relevanssi index   (or the plugin's documented reindex command)
```

---

## 5. Phase 6 — Creator identity completeness

For each launch creator (rubric: `food-for-thought-313-readiness.md`), set avatar, banner, bio, and verification:

```bash
UID=87   # repeat per creator
$WP user meta update $UID okarcana_avatar "https://…/avatar.jpg"
$WP user meta update $UID okarcana_banner "https://…/banner.jpg"
$WP user meta update $UID description "2–4 sentence bio."
$WP user meta update $UID _ok_creator_verified 1
# Legacy theme equivalents, if the live theme reads them:
$WP user meta update $UID beeteam368_user_avatar "https://…/avatar.jpg"
$WP user meta update $UID beeteam368_user_channel_banner "https://…/banner.jpg"
```

Remove/disable empty placeholder accounts that are not part of the lineup.

---

## 6. Phase 8 — QA checklist

Run after Phases 2–5. Re-run on prod after promoting from staging.

```bash
# No posts still tagged to the archived term
$WP term get vidmov_video_category 2055 --field=count           # expect 0
# No imports queued/scheduled for deprecated terms (held instead)
$WP post list --post_type=vidmov_video --meta_key=_arcana_queue_state --meta_value=held --format=count
# No deprecated campaigns active (verify in wp-automatic UI)
```

- [ ] No orphaned creator pages (visit `/channel/channel-id/@lipps/` → should be archived/404/redirect, not a live featured channel).
- [ ] No broken links on homepage / featured rails.
- [ ] Homepage & discovery feature only launch creators; no archived-creator references.
- [ ] No scheduled imports on deprecated campaigns (4206/4708/4168 inactive; 8089/8090 no longer carry 2055).
- [ ] Search returns no archived creators in featured positions.
- [ ] Each featured creator has avatar + banner + bio + verified flag.
- [ ] `cache flush` + index rebuild done; surfaces reflect the new lineup.

---

## Rollback

If anything looks wrong: `$WP db import /opt/bitnami/backups/pre-curation-<STAMP>.sql`, then re-run from §1. Because Phase 2 archives (not deletes), the worst case before deletion is fully reversible from the taxonomy dump alone.
