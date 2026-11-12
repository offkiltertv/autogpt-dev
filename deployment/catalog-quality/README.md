# Catalog quality and complete source inventories

Implements the first slice of the owner-approved November 2, 2026 platform plan:
reliable source coverage and usable discovery, preserving existing video pages,
discussions, ownership, membership rules, and the native player.

This release is independent of the unfinished editorial-home and cross-product
SSO releases. The source branch starts at the installed playback baseline
`2b4e964`, rather than publishing another lane's unreleased changes.

## Runtime

Install only `oktv-catalog-quality.php` in WordPress's `mu-plugins` directory.
It defaults off. The exact release string in the root-controlled non-web marker
`/opt/bitnami/oktv-release-state/catalog-quality-enabled` enables discovery
protection. Removing that marker disables it without a code or database restore.
The marker must be readable by PHP; only root may write it.

The runtime excludes exact deleted/private placeholder titles and explicitly
checked unavailable sources from public video discovery queries. It retains
singular lookups, non-video content, administrative queries, cron, CLI and REST
behavior. The inspected `beeteam368_filter_posts` frontend AJAX action receives
the same protection. It preserves source pages and existing discussion URLs.
An availability notice on a retained video page does not assert permanent
deletion. Transport errors are never availability findings.

An administrator can read **Tools → OKTV Catalog Health**. It shows the last
completed inventory, counts and verified import timestamps; it exposes no
credentials or source playlist identifiers. A running/failed inventory must not
replace a prior completed inventory with zero or partial coverage.

## Inventory

`catalog-inventory.php` is an operator CLI tool, never a web endpoint or a
scheduled publisher. Keep it outside the webroot with a matching copy of the
runtime source. Run through WordPress CLI with explicit approved campaign IDs:

```text
php wp-cli.phar --path=WORDPRESS --allow-root --skip-plugins --skip-themes eval '$args=["audit","CAMPAIGN_ID"]; require "/PRIVATE_RELEASE/deployment/catalog-quality/catalog-inventory.php";'
php wp-cli.phar --path=WORDPRESS --allow-root --skip-plugins --skip-themes eval '$args=["snapshot","CAMPAIGN_ID"]; require "/PRIVATE_RELEASE/deployment/catalog-quality/catalog-inventory.php";'
```

Use the reviewed PHP binary and CLI phar directly for root-only receipts. Load
these strict, namespaced files with `eval` plus `require`; the installed CLI's
`eval-file` wrapper cannot execute their top-level strict declaration.

`audit` reads only. `snapshot` saves aggregate counts after every source has
completed successfully. It reads the existing configured YouTube credential
on the server, requests metadata only, follows every playlist page, checks video
availability/embedding status, and reconciles immutable video IDs across the
whole WordPress video catalog. Counts are not campaign-attribution totals and
do not establish anonymous playback or rights to rehost media.

`audit-observed` explicitly retains a terminal provider gap of one to five items
as `inventory_complete=false` and `unresolved_items`. It rejects other pagination
loops, changing totals and unidentified items. The identifiable eligible records
may be processed while that gap remains unresolved; it is never represented as
a complete inventory.

The inventory bounds each playlist to 5,000 entries and each video request to 50
IDs. It rejects changing/incomplete inventories, repeated page tokens, malformed
IDs and API failures. It retains the distinction between unavailable,
non-embeddable and unknown. It counts an existing draft separately from a truly
missing item. It does not publish, delete, reassign authors or download media.

`OKTV_CATALOG_RECEIPT` can name a new file in an existing root-only directory
beneath `/var/backups/oktv-recovery/`. This private receipt contains the selected
source inventory and provider metadata for reconciliation. Never commit it,
API responses, source playlist IDs, credentials or production records.

## Qualification and release

1. Run the isolated PHP tests under `tests/catalog-quality/`.
2. Commit the owned source. Build the deployment bundle from that exact commit;
   verify its file inventory and SHA-256 before staging it outside the webroot.
3. Check current WordPress/PHP versions, disk, services, release markers and
   public route health. Refuse an existing unrelated runtime file or marker.
4. Run `tests/catalog-quality/wordpress-readonly.php` against current production
   WordPress/MariaDB. It temporarily qualifies the query predicate in that CLI
   process only; it writes no records and does not enable the live feature.
5. Back up the existing aggregate snapshot and record before-state of the new
   runtime path and marker in a root-only release directory. Do not copy the
   production database or vendor source into this repository.
6. Install the one hashed MU-plugin file atomically, keeping the marker absent.
   Verify default-off public health, then enable the exact reviewed marker.
7. Require `X-OKTV-Catalog-Quality: oktv-catalog-quality-1.0.0` on a fresh public
   response. Inspect homepage, watch-next recommendations, pagination and an
   existing placeholder's direct URL. Verify the native playback canary and
   unaffected creator/category routes. Check desktop and phone layouts.
8. On a regression, remove only this release's exact marker and verify default
   behavior. Leave the inert source file in place during code-cache transition.

Source coverage repair and any backfill are separate operator operations with
their own before-state receipts and bounded import batches. Inventory completion
or importer activity is never a successful-publication receipt.

## Apply a reviewed inventory

`catalog-reconcile.php` accepts `plan` or `apply`, a root-only inventory receipt,
and optionally an approved campaign ID plus its new interval in minutes. Load
it through the same strict-file CLI entry point above. It rejects stale or
unprotected receipts. `plan` reads only; `apply` takes a database advisory lock,
writes a root-only before-state receipt, updates only the owned availability
fields and aggregate snapshot, and verifies readback. A source-confirmed restored
video can recover its placeholder title without changing its slug, dates or
discussion. The optional cadence change uses a byte-exact compare-and-swap of
the original campaign configuration and is bounded to 15–1,440 minutes.

Alternatively pass `verify-source-credit` as the third argument. This credits
existing available imports from the receipt's approved campaigns (including
owned backfill campaigns) using fresh provider metadata. It backs up and verifies
source channel ID, source title, the owned verification field and availability.
Authors, memberships, local engagement, status, slugs and dates are retained.
Unrelated member posts are not relabeled. Aggregate reconciliation retains
provider gaps and actual verified import timestamps.

## Source credit and controlled backfill

`catalog-ingestion-policy.php` has `plan` and `apply` modes with a private
release directory and explicit campaign IDs. It backs up the exact prior
configuration, changes future imports to drafts, and removes the vendor's
automatic member-account and comment-copying options. Existing member accounts,
roles, posts, comments and source campaign pagination are preserved.

Install `oktv-source-attribution.php` as a separate MU plugin, default off. Its
exact release marker is `/opt/bitnami/oktv-release-state/source-attribution-enabled`.
The reviewed presentation supports VidMov 2.3.8 and its inspected native hooks.
Only records carrying the owned verification field and a valid provider channel
ID receive source presentation. They use “YouTube source · curated on OKTV”,
an original-channel link and a local collection. This is source attribution,
not evidence of a creator's OKTV membership or account ownership. Ordinary
member posts retain native presentation. Source collections and exclusion from
the editorial curator's member library remain correct if presentation is off.
The old homepage “publish here first” claim is replaced with truthful curation
copy while this presentation is enabled.

`catalog-backfill.php` accepts `plan`, `prepare` or `publish`, a root-only reviewed
inventory receipt, one approved campaign ID, and a batch limit of 1–20. Run with
the full native plugins and theme, through the same strict-file CLI entry point.
The receipt must be under four hours old and bound to the source's current
playlist fingerprint. Start with `prepare` and inspect a single draft before
enabling source presentation and running a one-item `publish` canary.

The optional fifth argument `adopt-managed-drafts` includes untouched native
drafts created since the November 3 safeguard under the approved source's curator
and campaign. Older editorial holds, revised drafts and pending posts remain
untouched. Only identifiable eligible items in a fresh receipt are processed,
including when an explicitly observed terminal provider gap remains unresolved.

The backfill uses checked provider metadata with WordPress's standard draft,
thumbnail and publishing APIs and the installed native player fields. It does
not execute vendor processing templates or copy provider comments. Original
campaign pagination is preserved. It blocks new member insertion and email in
that request and suppresses avatar importing. It refreshes availability before publication,
normalizes source credit, requires a thumbnail and valid immutable channel ID,
accepts thumbnails only from the provider's fixed image host for that video,
starts new local counters at zero, and checks for a published duplicate across
the entire catalog. Unrelated pre-existing drafts are preserved. Publication
requires the reviewed source presentation to be ready. Private per-batch
receipts record created drafts before later checks, allowing recovery without
exposing credentials or playlist identifiers. Only the thumbnail is copied;
video playback remains the provider embed. Removing a presentation marker is not a database rollback; any
publication rollback must name the exact new records and return them to draft.

## Dedicated verified publishing job

`catalog-sync.py` and the matching systemd service/timer compose the verified
operators. The two approved campaign IDs are fixed in source. Each hourly run:

1. Takes a nonblocking lock, verifies installed release hashes and checks origin
   health, the existing recovery failure counter and at least 1 GiB of free disk.
   A 256 MiB private archive ceiling stops unchecked receipt growth.
2. Inventories both approved playlists into a new root-only receipt, preserving
   unresolved provider counts. A failed request never becomes a zero snapshot.
3. Publishes at most 20 eligible items per source, with current provider checks
   and site health between batches. Vendor templates are never invoked.
4. Reconciles source credit and coverage with before-state receipts. Large files
   created in that run are compressed with exact byte recovery verified.

Install committed operator files root-owned and not writable by others beneath
`/opt/bitnami/oktv-catalog-releases/COMMIT/`. The root-owned configuration
`/etc/oktv-catalog-sync.json` names that directory in `release` and a `sha256`
map for the six PHP files listed in the runner. It contains no credentials.
Install the runner at `/usr/local/sbin/oktv-catalog-sync.py` and the units in
`/etc/systemd/system/`.

Run the installed runner with `--check`, validate the units, then start one actual
service run and inspect its private status and the public site before enabling
the timer. Enable only the timer. Root-private status is under
`/var/lib/oktv-catalog-sync/`; receipts are under
`/var/backups/oktv-recovery/catalog-sync/`. The journal receives aggregate counts
and fixed failure labels only. An administrator sees a stale-source notice if
the last completed check is over four hours old.

Pause with `systemctl disable --now oktv-catalog-sync.timer`. Allow an active
bounded run to finish before maintenance. Native draft intake, source display
and the outage healthguard remain intact. Revert only the named release and
receipt-owned records; never restore a whole production database over later work.
