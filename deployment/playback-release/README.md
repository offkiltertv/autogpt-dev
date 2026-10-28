# Playback-only release

## Authorization and boundary

The owner explicitly approved publishing the OffKilter-owned production baseline,
the playback repair, tests and runbook on `fix/oktv-playback-consent` to
`offkiltertv/autogpt-dev`, followed by a backed-up, rollback-ready public-video
canary after current-stack qualification. The owner separately approved matching
the lab to production WordPress 7.1.3 and the freshly captured plugin versions.

No vendor/licensed source, production database, uploads, secrets, private playlists,
homepage redesign, billing, channel-claim implementation or email configuration is
included. No production WordPress/plugin upgrade is part of this release.

Commit `631120a` reconciles the existing live 52-file owned plugin baseline. It is
not a design deployment. The runtime repair is then exactly four files: bootstrap
version/load hooks plus the new PHP, JS and CSS playback helper. The other 51
baseline files remain byte-identical. An additional operator MU-plugin restricts
activation; it is deliberately outside the plugin archive.

## Reviewed artifact

- Release ID: `oktv-playback-3.3.6-db31a5a937a7d1af`.
- Archive SHA256: `0346c7de17b2d74caa02252eab2e40949a7cdc6a83cc5d4471966226299cc5c1`.
- Baseline tree SHA256: `15b610972b88398090706bf97f98e3a0c9997cbdba12cc9d20058acad16e4989`.
- Baseline bootstrap SHA256: `088972693f16233a8f356f5326f7fc6428f8248d81fa6888556c26f9a87baee3`.
- Default disabled. A package verifier checks the externally supplied release ID,
  complete file inventories, exact delta, safe archive paths/types, archive digest,
  default-off behavior and baseline consistency.

The helper opens the existing Termly preference UI. It does not write consent,
unlock media, change membership rules, replace the native player, inject provider
URLs or treat an iframe's presence as proof of playback. Unknown providers and
trailers use generic language. Asset URLs contain content hashes.

## Reproduce qualification

Use a private, owner-approved source capture, never a production DB/config copy.
The source capture script is opt-in and read-only. It contains a fixed allowlist
and excludes keys, logs, uploads, backups and configuration. Do not commit its
output or the lab. On macOS the lab confines installed vendor PHP to loopback;
public core/SQLite downloads and checksum verification happen before qualification.

```sh
php tests/wordpress/approved-source.php "$SOURCE"
php deployment/playback-release/package.php build "$SOURCE"
php deployment/playback-release/package.php verify "$RELEASE" "$REVIEWED_ID"
php tests/playback-help/package.test.php "$SOURCE"
npm --prefix tests/playback-help ci --ignore-scripts --no-audit --no-fund
npm --prefix tests/playback-help test
php tests/playback-help/enqueue.test.php
php tests/playback-help/canary-scope.test.php
bash tests/wordpress/provision-playback-lab.sh "$SOURCE" "$RELEASE" "$REVIEWED_ID"
```

The provisioner prints its new private lab directory. It creates only synthetic
users/content. It verifies WordPress 7.1.3 and SQLite Integration 3.0.2 against
official checksums, then installs the exact reviewed plugin, captured AIOSEO,
VidMov, Beeteam, CMB2 and Redux. Do not use an old lab pointer without checking its
marker, release manifest and provision date.

```sh
sandbox-exec -f tests/wordpress/membership-lab.sb php -d memory_limit=512M \
  -S 127.0.0.1:8793 -t "$LAB/wordpress" tests/wordpress/lab-router.php
# Separate terminal, while that exact local server runs:
sandbox-exec -f tests/wordpress/membership-lab.sb php -d memory_limit=512M \
  /opt/homebrew/bin/wp --path="$LAB/wordpress" eval-file \
  tests/wordpress/playback-release-integration.php
node tests/playback-help/candidate-public-browser.mjs --browser-only-candidate --keyboard
node tests/playback-help/candidate-public-browser.mjs --browser-only-candidate --mobile
```

Those last two tests alter responses only inside their disposable browser. They
are explicitly NOT deployment evidence. They test the actual public CMP/provider
and store private screenshots/results. They do not use the owner's browser account.

Stop the exact lab server before cloning its database. Never kill unrelated PHP
processes. The membership provisioner refuses listeners, SQLite journal/WAL files,
symlinks or an unmarked lab.

```sh
bash tests/wordpress/provision-membership-lab.sh "$LAB" "$SOURCE" "$RELEASE" "$REVIEWED_ID"
sandbox-exec -f tests/wordpress/membership-lab.sb php -d memory_limit=512M \
  -S 127.0.0.1:8794 -t "$MEMBERSHIP_LAB/wordpress" tests/wordpress/lab-router.php
# Separate terminal:
sandbox-exec -f tests/wordpress/membership-lab.sb php -d memory_limit=512M \
  /opt/homebrew/bin/wp --path="$MEMBERSHIP_LAB/wordpress" eval-file \
  tests/wordpress/membership-playback-integration.php
node tests/playback-help/membership-snapshot-browser.mjs "$MEMBERSHIP_LAB" "$REVIEWED_ID"
```

Membership tests load captured ARMember Pro 7.9.1 and Lite 6.1; they do not mock
the vendor's access decision. Temporary synthetic rules are restored in finally
blocks. The snapshot browser checks are offline DOM checks, not paid-account E2E.

## Qualification observed, 2026-10-28 UTC

- 15 packaging positive/negative checks passed.
- 30 JS state/DOM tests passed; 29 isolated PHP enqueue/cache assertions passed.
- 18 operator-canary scope checks passed.
- 26 WordPress 7.1.3/native VidMov playback integration checks passed.
- 94 ARMember/VidMov access checks passed; 28 offline desktop/mobile DOM scenarios passed.
- Actual public-CMP candidate: desktop keyboard 17 assertions; mobile emulation
  16 assertions. Denial, functionality-only consent, advancing YouTube video clock,
  withdrawal, reload and footer preference action passed. No page errors recorded.
- Production PHP 8.2.10 accepted the combined bootstrap/helper source under `php -l`.
- Source/current-version checks showed WordPress 7.1.3, VidMov/Beeteam 2.3.8,
  AIOSEO 5.0.4, ARMember 7.9.1/Lite 6.1, consent plugin 3.3.1.

The local runtime is PHP 8.5.7/SQLite, not production PHP 8.2.10/MariaDB. These
checks are selected-stack qualification, not complete infrastructure parity.
Mobile Chrome emulation is not a physical iPhone/Android acceptance test.
Live canary verification is a separate mandatory gate below.

## Controlled production sequence

Target only `oktv-main-deployment-vm`, project `offkilter-tv`, zone `us-west1-a`,
over IAP. Never inherit a default GCP project. No plugin activation/deactivation,
DB restore, vendor upgrade, service restart or infrastructure change is required.

1. Recheck stack versions, service health, healthguard failcount, disk/RAM,
   canonical origin TLS and public routes. Abort on drift or degraded health.
2. Verify the current owned plugin against all 52 baseline file hashes and reject
   extra files/symlinks. Verify the separately supplied archive/release hashes.
3. Create a root-only, non-web backup of the complete owned plugin with permissions,
   plus inventories and operator files. Record its exact path. Prepare rollback
   before writing live files. Refuse to overwrite an existing canary MU-plugin or marker.
4. Stage the four changed files on the same filesystem outside the webroot.
   Install the three helper files first, then atomically replace the bootstrap.
   Preserve existing bootstrap owner/mode; new helper files are readable by PHP.
5. Install `oktv-playback-canary.php` as an MU-plugin with its marker absent.
   Verify the entire installed 55-file tree. The plugin remains disabled by default.
6. Verify default-off homepage, video, channel and categories through Cloudflare;
   require 200, no helper and no stale origin IP references. Compare native title,
   H1 and canonical before/after. Do not infer FPM code loading from CLI OPcache.
7. Only after those gates, create the root-controlled, non-web marker
   `/opt/bitnami/oktv-release-state/playback-canary-enabled` containing the exact
   reviewed release ID. The MU-plugin permits only anonymous HTTPS GET requests
   to published video 7917 at the exact canonical path, with no query string.
   Logged-in, admin, REST, AJAX, cron, other domains/routes and password/private
   content remain disabled. No visitor cookie/query can enable it.
8. Require the live response's `X-OKTV-Playback-Release` header, no-store and exact
   public JS/CSS hashes. This proves the FPM-loaded release, not just CLI/files.
   Run the live tests below without any response replacement. Check unaffected
   routes and healthguard again. The marker can be removed to disable immediately.

```sh
node tests/playback-help/live-canary-browser.mjs --live-canary "$RELEASE" "$REVIEWED_ID" --keyboard
node tests/playback-help/live-canary-browser.mjs --live-canary "$RELEASE" "$REVIEWED_ID" --mobile
```

## Rollback

Remove/move only the exact root-controlled marker first. Confirm fresh canary
HTML no longer has helper assets. If code rollback is necessary, atomically
restore the backed-up bootstrap with its original owner/mode, then confirm the
baseline bootstrap hash, route identity and health. Never restore the database.

**Do not immediately delete the three helper files:** a worker may still hold the
new bootstrap in OPcache for the observed 60-second revalidation interval. Keeping
the now-inert helpers avoids missing-include failures during rollback. Remove
them only after code-cache transition is independently confirmed. The inert
MU-plugin can stay with its marker absent; do not delete unrelated MU-plugins.

Any failure of TLS, route health, native access or actual consent/playback stops
the rollout and triggers marker removal. No full-site activation is authorized
by successful canary tests. Billing, email/contact, verified channel claims and
homepage/Arcana changes remain separate releases.
