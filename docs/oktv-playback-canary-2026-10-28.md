# OKTV playback canary release receipt

Updated: 2026-10-28, 10:13 UTC verification window.

## Executive status

The owner-approved playback-only repair is committed, pushed and installed in
production. It is enabled only for anonymous HTTPS GET requests to the canonical
URL of public video 7917:

https://www.offkilter.tv/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/

The real deployed consent flow and advancing anonymous YouTube playback passed
desktop keyboard and mobile-emulated browser checks. No response overrides were
used for those live checks. The rest of the site remains disabled for the new
helper, including the homepage, channel/categories, other videos, logged-in users
and query-string variants. This is not a full-site rollout or completion of the
larger Phase 1 transformation.

## Source and release

- Repository: `offkiltertv/autogpt-dev`.
- Branch: `fix/oktv-playback-consent`.
- Existing live baseline reconciliation: `631120a`.
- Qualified runtime, tests and runbook: `90fdfc5`.
- Release ID: `oktv-playback-3.3.6-db31a5a937a7d1af`.
- ZIP SHA256: `0346c7de17b2d74caa02252eab2e40949a7cdc6a83cc5d4471966226299cc5c1`.
- Release manifest SHA256: `037c523ddd65d837d221705f280fb7358890c71a2e78b4f9160b6229fdc35706`.
- Operator MU-plugin SHA256: `6a99fd7e5cea812c0f9f9094411c394084c0160edef0c275929b4a939518c058`.

The owner explicitly approved publishing only the owned production baseline and
playback repair, then requalifying against production WordPress 7.1.3. No vendor
or licensed source, production data, private playlists, credentials, database,
uploads or logs were committed. The original shared dirty checkout was not used
for commits or deployment. A dedicated clean release checkout was used.

The deployed plugin changed from 3.3.5 to 3.3.6. Its 55-file inventory contains
51 byte-identical baseline files, a modified bootstrap and three new helper
files. No existing content, menu, homepage, theme, taxonomy, membership rule,
provider, consent category or WordPress version was changed. No service was
restarted. The MU-plugin is a separate operator scope restriction, not part of
the distributable plugin ZIP.

## Current-stack qualification

| Check | Result | Boundary |
| --- | --- | --- |
| Packaging and negative cases | 15 passed | Exact ID, hashes, delta, default-off, path/link rejection |
| JS state and DOM | 30 passed | No consent writes or native-player replacement |
| Isolated PHP enqueue/cache | 29 passed | Correct scope, provider context and cache identity |
| Operator canary scope | 18 passed | Exact route, anonymous, HTTPS, release marker, public/unlocked post |
| WordPress/VidMov integration | 26 passed | Native identity/scripts/access preserved; no unpublished homepage loaded |
| ARMember/VidMov access | 94 passed | Public, protected, plan variants, category/tag, trailer and global restriction |
| Offline membership DOM | 28 passed | Desktop/mobile DOM; explicitly not paid-account/browser E2E |
| Public browser candidate | 17 desktop, 16 mobile passed | Pre-deployment response override, not live-release proof |
| Production PHP lint | Passed | PHP 8.2.10 bootstrap/helper/MU-plugin parsing |
| Actual deployed public canary | 17 desktop, 16 mobile passed | No response replacement; real public CMP and provider |

The fresh selected-stack lab used WordPress 7.1.3, VidMov/Beeteam 2.3.8, AIOSEO
5.0.4, ARMember Pro 7.9.1 and Lite 6.1 from the approved private capture. Official
WordPress/SQLite checksums passed. The earlier 7.0.7/ARMember 7.5 lab was not
treated as current qualification. Synthetic fixtures were rebuilt without the
unpublished editorial-homepage dependency. Both local lab servers were stopped
after testing; no real accounts, payments or production data were imported.

Local PHP 8.5.7 and SQLite are not full production PHP 8.2.10/MariaDB parity. The
live canary is the independent current-production acceptance rung. Mobile Chrome
390x844 emulation is not a physical-device test.

## Deployment and rollback receipt

Target: `oktv-main-deployment-vm`, project `offkilter-tv`, zone `us-west1-a`, over IAP.

Protected backup, created 2026-10-28 at 10:07:20 UTC:

```text
/var/backups/oktv-playback/20261028T100720Z-3.3.6
```

The root-only backup directory and rollback script are mode 0700. The complete
52-file owned baseline was copied with original permissions, then verified by
hash. Staged archive, manifest and MU-plugin hashes matched the reviewed values.
The three helper files were installed before an atomic bootstrap replacement.
All 55 installed plugin files were verified after deployment and again during
final health checks.

The default-off deployment was verified before enablement: five public routes
returned 200 with valid TLS, no helper assets, no stale IP literal and unchanged
title, H1 and canonical values. The new root-controlled marker was created at
**10:10:12 UTC**:

```text
/opt/bitnami/oktv-release-state/playback-canary-enabled
```

It contains only the exact reviewed release ID. Directory ownership/mode is
root:root 0755; marker is root:root 0644. No visitor parameter, cookie or user
account can set it. The active canary responds with a matching
`X-OKTV-Playback-Release` header and no-store; live JS/CSS bytes match the reviewed
manifest. This verifies actual FPM-loaded code and edge delivery, rather than
assuming CLI OPcache state proves browser behavior.

To disable the canary, a privileged operator can remove/move only that marker.
For code rollback, the prepared script is:

```sh
sudo /var/backups/oktv-playback/20261028T100720Z-3.3.6/rollback.sh
```

The script disables the marker first, refuses an unexpected bootstrap hash,
then atomically restores the verified original bootstrap and its permissions.
It deliberately retains the three inert helper files to avoid missing-include
failures while FPM workers revalidate OPcache. The observed revalidation interval
is 60 seconds. No database restore is involved. Backup hashes and script syntax
were verified; a live bootstrap rollback was not exercised or represented as a
fault-injection test.

## Live verification

The live runs used fresh unauthenticated browser contexts and no page/asset
response replacement:

- Desktop keyboard run began at 10:10:45 UTC: 17/17 checks passed, zero page errors.
- Mobile-emulated run began at 10:11:10 UTC: 16/16 checks passed, zero page errors.
- Denying optional consent left the provider blocked.
- The helper opened the site's actual Termly preference dialog after denial.
- Functionality-only consent enabled playback while analytics, advertising,
  social-networking and unclassified consent stayed false.
- Desktop video clock advanced from 4.16 to 8.17 seconds; mobile from 4.13 to
  8.15 seconds, unpaused and without a media error.
- Withdrawal through the vendor UI and an explicit reload blocked the provider.
- The existing footer preference link opened the real dialog on the canary.
- Screenshots were retained privately; the mobile preference dialog was visually
  inspected. Existing vendor styling was not redesigned.

After enablement, `/`, `/channel/`, `/video-category/arcana/`,
`/video-category/signals/`, the canary and its `?trailer=1` variant all returned
200 with TLS verification result 0. Only the exact canonical canary contained
the helper. The five normal routes retained their pre-release title/H1/canonical
values. All six sampled responses contained zero `34.105.65.179` literals.

These are bounded route checks, not a new exhaustive database/whole-site URL audit.

## Final production health

At 10:13:47 UTC:

- nginx, PHP-FPM and MariaDB running.
- Healthguard timer enabled and active; failcount 0; recent service runs succeeded.
- VM uptime about 127 days; load averages 0.64, 0.50, 0.48.
- Approximately 2.7 GiB RAM available; 2 GiB swap active.
- Root disk 54% used, approximately 19 GiB free.
- Canonical direct-origin HTTPS returned 200, TLS verification 0, TTFB 0.75 s.
- Final installed 55-file and backed-up 52-file hashes both matched their manifests.

This is current observed health, not a guarantee of future uptime or an assertion
that the previous 30-day Cloudflare error count was zero.

## Remaining boundaries

1. Full-site helper activation is not included. The footer repair is active only
   on the canary; site-wide rollout needs a separate scope decision.
2. Physical iOS/Android and real paid-account browser E2E remain unverified. No
   billing offer or subscription flow was introduced.
3. Contact/email delivery and verified channel claims remain separate work.
   Earlier mail configuration concerns were not fixed by this playback release,
   and no test email, OAuth change or DNS change was sent/applied.
4. Arcana/Then-and-Now content, private playlists, homepage redesign and commercial
   packaging were not published or changed.
5. The larger OKTV Phase 1 goal remains incomplete. This closes the authorized
   playback-canary release, not the modernization or monetization program.
