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

`catalog-backfill.php` accepts `plan`, `prepare` or `publish`, a root-only complete
inventory receipt, one approved campaign ID, and a batch limit of 1–20. Run with
the full native plugins and theme, through the same strict-file CLI entry point.
The receipt must be under four hours old and bound to the source's current
playlist fingerprint. Start with `prepare` and inspect a single draft before
enabling source presentation and running a one-item `publish` canary.

The backfill uses the installed native importer's single-video operation in an
internal draft configuration, preserving the original campaign's pagination.
It rejects paid processing templates, forces one draft per import, blocks new
member insertion and email in that request, and suppresses source-comment and
avatar importing. It refreshes provider availability before publication,
normalizes source credit, requires a thumbnail and valid immutable channel ID,
starts new local counters at zero, and checks for a published duplicate across
the entire catalog. Unrelated pre-existing drafts are preserved. Publication
requires the reviewed source presentation to be ready. Private per-batch
receipts record created drafts before later checks, allowing recovery without
exposing vendor output, credentials or playlist identifiers. No media is
rehosted. Removing a presentation marker is not a database rollback; any
publication rollback must name the exact new records and return them to draft.
