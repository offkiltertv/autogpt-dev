# Incident Report: OKTV Outage Recovery (2026-06-17)

## Scope
Close out the June 17 OFFKILTER.TV outage after Jetpack downtime alert, verify hardening controls, and confirm public route stability through Cloudflare.

## Jetpack Alert Context
- Jetpack downtime alert correlated with user-facing outage.
- During initial triage, public probes returned Cloudflare origin errors (`522` then `521`) and origin connectivity failures.
- Origin host (`oktv-main-deployment-vm`) was reachable only after VM reset.

## Timeline (America/Los_Angeles)
- **2026-06-17 02:52:52 PDT**: VM reset issued on `oktv-main-deployment-vm` (`operation-1781689972154-6547005e3dec1-80cc4c0a-59d25f46`).
- **2026-06-17 02:53:00 PDT**: Reset operation completed (`DONE`).
- **2026-06-17 ~09:58-10:02 UTC**: Healthguard observed unhealthy state during early post-recovery period and restarted stack.
- **2026-06-17 10:02 UTC onward**: Healthguard logs remain continuously healthy and failcount returns to `0`.
- **2026-06-17 10:14 UTC**: `bitnami.service` restart override corrected (`StartLimit*` moved to `[Unit]`) and service restarted cleanly.

## VM Reset + Bitnami Recovery
- VM state after reset: `RUNNING`.
- Bitnami stack recovered and currently running:
  - `nginx already running`
  - `php-fpm already running`
  - `mariadb already running`
- `apache2` is not present on this stack (`Unit apache2.service could not be found.`), so web serving is nginx-only.

## Healthguard Install + Validation
- Timer:
  - `oktv-healthguard.timer` -> `enabled`, `active`
  - cadence observed in `systemctl list-timers --all`
- Failcount:
  - `/var/run/oktv-healthguard.failcount` -> `0`
- Log health:
  - `/var/log/oktv-healthguard.log` shows repeated `healthguard: healthy` entries after stabilization window.
- Current behavior:
  - thresholded recovery escalation is active (warning, restart, escalate, reboot tiers).

## systemd Restart Override Validation
- Active drop-in: `/etc/systemd/system/bitnami.service.d/override.conf`
- Effective settings:
  - `[Unit] StartLimitIntervalSec=300`
  - `[Unit] StartLimitBurst=5`
  - `[Service] Restart=always`
  - `[Service] RestartSec=20`
- Runtime confirms override is in effect:
  - `Restart=always`
  - `ActiveState=active`
  - `SubState=running`
  - `NRestarts=0`

## Swap Addition Validation
- Swap active:
  - `/swapfile` size `2G` (`swapon --show`)
- Persisted across reboot:
  - `/etc/fstab` contains `/swapfile none swap sw 0 0`
- Resource snapshot at validation:
  - Root disk: `42G total / 21G used / 20G free` (~52% used)
  - Memory: `3.8Gi total`, `1.8Gi available`

## SSH Hardening Validation (IAP-only)
- Firewall rules in place:
  - `oktv-main-deployment-allow-iap-ssh` (allow `35.235.240.0/20` to `tcp:22`, target tag `oktv-main-deployment-deployment`)
  - `oktv-main-deployment-deny-public-ssh` (deny `0.0.0.0/0` to `tcp:22`, same target tag)
- Validation:
  - direct SSH to NAT IP `35.233.161.118:22` -> timeout
  - IAP SSH path remains functional (`gcloud compute ssh --tunnel-through-iap`)

## Endpoint Validation
- Cloudflare-facing routes currently healthy:
  - `https://www.offkilter.tv/` -> `200`
  - `https://www.offkilter.tv/wp-admin` -> `301` (expected), `200` when followed
  - `https://www.offkilter.tv/video-category/arcana/` -> `200`
  - `https://www.offkilter.tv/video-category/signals/` -> `200`
  - `https://www.offkilter.tv/channel/` -> `200`
  - sample video (`/video/the-youtube-streets/`) -> `200`
- Repeated edge check (5 consecutive iterations) held stable with no 5xx on the primary visitor routes.
- Cloudflare headers present on edge responses (`server: cloudflare`, `cf-ray` observed).
- Direct origin test (bypassing Cloudflare via `--resolve`) returned `200` from `35.233.161.118`.

## URL Integrity Pass (2026-06-17)
- Backup created:
  - `/opt/bitnami/backups/oktv-url-pass-20260617T101540Z/pre-url-pass.sql`
- `34.105.65.179` scan results before and after remediation:
  - `wp_options`: `0`
  - `wp_posts`: `0`
  - `wp_postmeta`: `0`
  - `wp_usermeta`: `0`
  - `wp_termmeta`: `0`
  - nav menu items/meta: `0`
  - Elementor payloads: `0`
  - VidMov/Beeteam options: `0`
  - cached menu data patterns: `0`
- Replacement commands executed for `http://`, `https://`, and protocol-relative IP forms; all returned `Made 0 replacements`.
- Live HTML checks on homepage, channel, arcana, signals, and sample video all returned `ip_hits=0`.
- Anonymous channel page includes canonical tab entries for Videos, Audios, Playlists, Posts, Transfer History, Subscriptions, Watch Later, Notifications, History, Rated, Reacted, and About; Discussion did not render for anonymous context during this pass.

## Remaining Risks
1. Origin TLS certificate on `35.233.161.118` is expired (`notAfter Nov 24 2024`) while Cloudflare edge TLS is valid. This is not currently visitor-visible through Cloudflare, but remains a strict-origin validation risk.
2. Persistent internet scan traffic is high; continue WAF/rate-limit review to reduce noisy error volume.
3. Single-VM architecture remains a resilience bottleneck; watchdog reduces mean time to recovery but does not remove single-host failure risk.

## Current Status
- OFFKILTER.TV is online and serving public visitor paths through `https://www.offkilter.tv`.
- Healthguard is active with `failcount=0`.
- No stale `34.105.65.179` links were found in requested DB scope or validated public HTML.
