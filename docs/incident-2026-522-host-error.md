# Incident Report: Cloudflare 522 Host Error (2026-06-14)

## Summary
- **Incident**: `offkilter.tv` and `/wp-admin` returned Cloudflare **522 (Connection timed out)**.
- **Detection window**: around **2026-06-14 00:26–00:55 UTC**.
- **Primary impact**: public site and admin became unreachable through Cloudflare.
- **Recovery action**: low-risk origin VM reset (`oktv-main-deployment-vm`) and post-restart service validation.
- **Current status**: **Recovered**. Public site and admin login route are responding.

## What Was Investigated
Per incident scope, diagnostics covered:
- Origin VM health
- NGINX status
- PHP-FPM status
- Cloudflare edge connectivity
- Bunny connectivity
- Resource exhaustion signals
- Recent logs and monitoring timeline

## Findings

### 1) Cloudflare and Edge Behavior
- `offkilter.tv` and `www.offkilter.tv` resolve to Cloudflare edge IPs (proxied mode active).
- Post-recovery header chain is normal:
  - `https://offkilter.tv` -> `308` to `https://www.offkilter.tv/`
  - `https://www.offkilter.tv/` -> `200`
- `/wp-admin` post-recovery follows expected chain:
  - `308` -> `301` -> `302` (WordPress login redirect) -> `200`

### 2) Origin VM and Service Health (Post-Recovery)
- Instance: `oktv-main-deployment-vm` (RUNNING, zone `us-west1-a`, NAT `35.233.161.118`).
- Uptime after recovery showed recent reboot/reset.
- Disk/memory healthy after recovery:
  - `/` at **46%** used, ~**22G free**.
  - no swap configured, memory available >2.6G.
- Bitnami services running:
  - `nginx already running`
  - `php-fpm already running`
  - `mariadb already running`
- Direct-origin checks succeeded:
  - `https://www.offkilter.tv` to origin IP -> `200`
  - `http://www.offkilter.tv` to origin IP -> `308`

### 3) Application/Data Signals
- MariaDB log on restart shows **InnoDB crash recovery** completed successfully.
- Startup warning observed for `wp_wpforo_usergroups` table as crashed, then checked/recovered.
- `wp db check` now reports database tables as OK (including `wp_wpforo_usergroups`).

### 4) Resource/Monitoring Signals Around Incident
Cloud Monitoring (instance `4173183930944957307`, 00:00–01:00 UTC) shows:
- **Disk read bytes_count** surged and stayed very high for an extended window:
  - peak **951,974,718 bytes/min** at `00:43:00Z`
  - sustained ~`820MB–952MB/min` from ~`00:31Z` through ~`00:49Z`
- **Network rx/tx** dropped sharply after ~`00:28Z` to very low throughput during the same window.
- CPU had spikes but not a prolonged full-core saturation profile.

This pattern is consistent with an origin-side stall under heavy disk I/O pressure, not a Cloudflare edge routing issue.

### 5) Recent Log Behavior
- Previous-boot journal output in the incident window becomes sparse after ~`00:37Z`, consistent with degraded origin responsiveness before reset.
- NGINX logs after recovery show normal traffic and expected redirects.

### 6) Bunny Connectivity
- Bunny endpoint checks (`oktv.b-cdn.net`) responded quickly with `403` (expected policy response), indicating network path to Bunny was reachable during checks.

## Classification
- **Transient?** Yes (service recovered after VM reset).
- **Application level?** Contributing factor likely (database crash-recovery evidence, WordPress/DB stack under stress).
- **Network level?** Unlikely primary cause (Cloudflare edge and DNS behaved normally; direct origin reachable post-reset).
- **VM level?** **Yes, primary** (origin stall/unresponsiveness under sustained disk I/O pressure).

## Actions Performed
1. Confirmed outage symptoms through Cloudflare and direct-origin probes.
2. Verified VM state in GCP.
3. Performed low-risk reset:
   - `gcloud compute instances reset oktv-main-deployment-vm --zone us-west1-a --project offkilter-tv`
4. Validated services and data stack post-restart:
   - Bitnami service status (nginx/php-fpm/mariadb)
   - WordPress DB integrity check
   - Public route checks for site and `/wp-admin`

## Post-Recovery Validation
Repeated checks (5 rounds each) succeeded:
- `https://offkilter.tv` (follow redirects) -> `200`
- `https://offkilter.tv/wp-admin` (follow redirects) -> `200`

## Root Cause Statement
Most likely root cause is **origin VM-level service degradation** during a period of sustained high disk-read activity, which made origin requests time out from Cloudflare's perspective and surfaced as **522**.

## Remaining Risk
- Recurrence risk exists if the same high-I/O workload pattern repeats without guardrails.
- `wpForo` table had a crash/recovery event; currently healthy, but should be monitored.

## Immediate Guardrails Recommended
1. Add alerting on:
   - origin HTTP latency/timeouts
   - disk read surge thresholds
   - MariaDB restart/crash-recovery events
2. Add an operator runbook step for safe first-response recovery:
   - health checks -> service checks -> controlled instance reset.
3. Schedule a low-traffic maintenance check for wpForo tables and DB engine health.

## Change Control
- No WordPress content/menu/homepage edits were performed during this incident response.
- Recovery actions were limited to infrastructure/service health diagnostics and VM reset.
