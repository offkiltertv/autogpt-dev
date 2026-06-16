# Incident Report: OKTV Jetpack Downtime Recovery (2026-06-16)

## Scope
Recover production availability for `https://www.offkilter.tv` after Jetpack downtime alert and verify origin + edge health.

## Timeline (America/Los_Angeles)
- **2026-06-16 14:05-14:10 PDT**: External probes from workstation to:
  - `/`
  - `/wp-admin`
  - `/video-category/arcana/`
  - `/video-category/signals/`
  - `/channel/`
  - sample video page
  returned **Cloudflare 521** with no app HTML payload.
- **2026-06-16 14:10 PDT**: Direct origin checks to `35.233.161.118` returned connection failure on ports `80/443`; SSH to `22` initially timed out/refused.
- **2026-06-16 14:10:39 PDT**: Reset issued for `oktv-main-deployment-vm` (`operation-1781644239193-654655ffe4595-14488684-6415a1c1`).
- **2026-06-16 14:13:14 PDT**: Controlled second reset with diagnostic startup script (`operation-1781644394026-654656938d43b-be7986d2-a4e5c6ca`) to confirm service bring-up path.
- **2026-06-16 14:16-14:20 PDT**: SSH restored, Bitnami services verified running, public endpoints returned 200 again.

## What Was Investigated

### 1) Public/Cloudflare health
Before recovery:
- Cloudflare edge returned **521** across key routes.

After recovery:
- `https://www.offkilter.tv/` -> 200
- `https://www.offkilter.tv/wp-admin` -> 200 (via expected login redirect chain)
- `https://www.offkilter.tv/video-category/arcana/` -> 200
- `https://www.offkilter.tv/video-category/signals/` -> 200
- `https://www.offkilter.tv/channel/` -> 200
- sample video page -> 200
- repeated 5-round check for `/` and `/wp-admin`: **all 200**.

### 2) Origin VM health (`oktv-main-deployment-vm`)
- Instance state: `RUNNING`
- SSH connectivity restored post-reset.
- Uptime after recovery check: ~7 minutes.
- Service status:
  - `nginx`: active (`nginx already running`)
  - `php-fpm`: active via Bitnami ctl (`php-fpm already running`)
  - `mariadb`: active via Bitnami ctl (`mariadb already running`)
- Resource snapshot:
  - Disk `/`: `42G total`, `19G used`, `22G free` (`47%` used)
  - Memory: `3.8Gi total`, `1.9Gi used`, `672Mi free`, `1.6Gi available`
  - Swap: `0B`

### 3) Error/log signals
- NGINX error tail captured active internet scanning traffic (`.env`, `.git`, config probing), mostly expected blocked/forbidden patterns.
- MariaDB log confirms boot-time crash recovery replay:
  - `InnoDB: Starting crash recovery ...`
  - `mysqld ... ready for connections`
- No persistent service-down state remained after reboot.

### 4) Cloudflare 5xx / 522 evidence
- During this incident window, direct evidence captured was **Cloudflare 521** on all monitored public routes before recovery actions.
- Historical June 14 incident report in-repo documents prior **522** behavior during origin unresponsiveness.

## Origin vs Edge Validation

### Direct origin response (`--resolve` to `35.233.161.118`)
- Origin now serves application responses (`200` / expected redirects).
- Example origin timings were in normal range (~1.0-1.7s total for tested pages).

### Cloudflare response
- Edge headers consistently show `server: cloudflare` with unique `cf-ray` IDs.
- Cloudflare path returned 200 across all required routes post-recovery.

## SSL Status
- **Edge cert (`www.offkilter.tv`)**: valid Let's Encrypt chain (`notAfter Aug 22, 2026`).
- **Origin cert (`35.233.161.118` with SNI `www.offkilter.tv`)**: expired (`notAfter Nov 24, 2024`), which fails strict verification unless `-k` is used.
- Public visitor path through Cloudflare currently has no browser SSL warning, but origin cert renewal is recommended if Cloudflare SSL mode is set/changed to strict validation.

## Recovery Actions Executed
1. Confirmed outage symptoms at edge and origin.
2. Reset VM from GCP twice (second pass included controlled diagnostic startup script).
3. Re-verified SSH, service status, and route health.
4. Restored original instance startup-script metadata after diagnostics.

## Current Status
- Production is reachable and stable at all required URLs.
- OFFKILTER.TV is publicly accessible again and responding with HTTP 200 through Cloudflare.

## Follow-up Recommendations
1. Renew/redeploy origin TLS cert for `www.offkilter.tv` to remove strict-validation risk.
2. Add alert thresholds for repeated origin port refusal / connection failures (80/443/22).
3. Maintain periodic review of NGINX error patterns and rate-limiting/WAF controls for scan traffic.
