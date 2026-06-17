# OKTV Origin TLS Remediation (2026-06-17)

## Objective
Eliminate remaining origin TLS risk on `www.offkilter.tv` and restore compatibility with Cloudflare `Full (strict)` validation.

## Scope
- Origin certificate inspection and chain validation
- TLS architecture inspection (nginx + Bitnami tooling + renewal jobs)
- Safe remediation and automation fix
- Post-change validation (edge + origin)
- Quick performance review while touching nginx

## Phase 1: Origin Certificate Findings (Before)

Direct origin inspection (`35.233.161.118:443`, SNI `www.offkilter.tv`) before remediation:
- Subject: `CN=offkilter.tv`
- Issuer: `Let's Encrypt E6`
- Valid from: `2024-08-26 16:06:02 GMT`
- Expired on: `2024-11-24 16:06:01 GMT`
- Fingerprint (SHA-256): `FE:0D:9D:45:86:01:84:DB:71:02:FC:C9:F0:F0:F3:83:E1:DC:5F:8F:7D:6C:C7:1E:E3:6E:79:92:6C:AA:BC:A0`

Certificate source in nginx:
- `/opt/bitnami/letsencrypt/certificates/offkilter.tv.crt`
- `/opt/bitnami/letsencrypt/certificates/offkilter.tv.key`

## Phase 2: Current TLS Architecture (Before)

### Cloudflare path
- Edge remained healthy (`server: cloudflare`) while origin cert was expired.
- Cloudflare SSL mode could not be queried directly from this CLI session (no Cloudflare API access in this environment).
- Operational inference before remediation: Cloudflare was not enforcing strict origin cert validation.

### Origin + nginx
- Active HTTPS server blocks:
  - `/opt/bitnami/nginx/conf/server_blocks/wordpress-https-server-block.conf`
  - `/opt/bitnami/nginx/conf/server_blocks/wordpress-server-block.conf` (HTTP redirect)
- TLS cert paths were already set to Bitnami lego cert files.

### Bitnami certificate tooling
- Bitnami/lego tooling present:
  - `/opt/bitnami/letsencrypt/lego`
  - `/opt/bitnami/letsencrypt/scripts/renew-certificate.sh`
- `certbot` binary was **not** installed.

### Renewal automation state (root cause)
- Existing root crontab renewal entry was:
  - `/usr/bin/certbot renew --quiet --renew-hook "/opt/bitnami/ctlscript.sh restart nginx"`
- Since `certbot` was missing, renewal path was broken and origin cert expired.

## Phase 3: Remediation Plan

### Evaluated options
1. Cloudflare Origin Certificate
2. Let's Encrypt renewal
3. Bitnami `bncert-tool` renewal

### Selected option: **Let's Encrypt via Bitnami lego (HTTP-01 webroot)**
Reasoning:
- Publicly trusted cert chain (works for direct origin verification and strict origin validation).
- No Cloudflare account/dashboard dependency.
- Works with existing Bitnami stack and existing cert paths.
- Low-risk to automate via cron + deterministic script.

## Phase 4: Implementation

### 1) Backups
Created point-in-time backup:
- `/opt/bitnami/backups/origin-tls-20260617T103244Z/`

Included:
- previous cert/key/json/issuer files
- active nginx server block configs

### 2) ACME challenge routing fix
Updated nginx server blocks to explicitly serve:
- `/.well-known/acme-challenge/`
from:
- `/opt/bitnami/letsencrypt/acme-challenge`

This removed challenge dependence on WordPress routing and redirect behavior.

### 3) Certificate renewal
Executed production renewal:
- `/opt/bitnami/letsencrypt/lego --accept-tos --email admin@offkilter.tv --domains offkilter.tv --domains www.offkilter.tv --path /opt/bitnami/letsencrypt --http --http.webroot /opt/bitnami/letsencrypt/acme-challenge renew --days 30`

### 4) Reload + automation repair
- Reloaded nginx after successful cert issuance.
- Installed renewal script:
  - `/usr/local/sbin/oktv-renew-origin-tls.sh`
- Replaced broken certbot cron with working renewal job:
  - `17 3 * * * /usr/local/sbin/oktv-renew-origin-tls.sh >> /var/log/oktv-origin-tls-renew.log 2>&1`

## Phase 5: Verification (After)

## New origin certificate
Direct origin inspection after remediation:
- Subject: `CN=offkilter.tv`
- Issuer: `Let's Encrypt YE1`
- Valid from: `2026-06-17 09:36:09 GMT`
- Expires: `2026-09-15 09:36:08 GMT`
- Serial: `06D846F780EEF2743A50C654642B4A48BF1F`
- Fingerprint (SHA-256): `DD:1A:E1:AC:C7:79:BB:7E:7B:CA:34:3C:F7:20:8D:29:ED:1C:81:DC:6E:22:F3:B5:38:04:C3:5C:1C:EC:6E:A0`
- Chain presentation from origin now includes the renewed Let's Encrypt leaf and intermediates (full chain served by nginx).

### Strict-compatibility checks
- Direct origin verification with CA validation:
  - `curl --resolve www.offkilter.tv:443:35.233.161.118 https://www.offkilter.tv/`
  - Result: `HTTP 200`, `ssl_verify_result=0`
- Cloudflare edge:
  - `https://www.offkilter.tv/` returns `HTTP 200`, valid browser-facing TLS.

### Route health checks (post-remediation)
- `/` -> 200
- `/wp-admin` -> 301 (expected), 200 when followed
- `/video-category/arcana/` -> 200
- `/video-category/signals/` -> 200
- `/channel/` -> 200
- sample video `/video/the-youtube-streets/` -> 200

### URL integrity sanity
- Homepage still has `0` references to `34.105.65.179`.

## Phase 6: Performance Review (Nginx/CDN)

## Observed state
- `gzip on` is enabled in nginx (`/opt/bitnami/nginx/conf/nginx.conf`).
- Cloudflare edge serves Brotli (`content-encoding: br` observed for HTML).
- Static assets currently return long cache lifetime:
  - `cache-control: max-age=315360000`
  - `expires: Thu, 31 Dec 2037 23:55:55 GMT`
- Bunny usage:
  - No Bunny URLs in rendered homepage HTML during this pass.
  - Bunny-related config exists in `wp_options` (plugin settings).
- Homepage image formats are primarily JPEG/PNG (no webp/avif observed in rendered HTML sample).

## Low-risk optimization recommendations
1. Keep Cloudflare Brotli enabled and confirm it remains active after plugin/theme changes.
2. Add a documented cache rule set for static assets (`/wp-content/uploads`, `/wp-content/plugins`, `/wp-content/themes`) with immutable caching where versioned.
3. Audit and either fully enable or fully disable Bunny rewrite paths to avoid partial/inconsistent CDN usage.
4. Add WebP/AVIF generation + delivery policy for uploads to reduce image payload size.
5. Consider selective edge caching for anonymous archive/category pages with cookie bypass controls.
6. If Bunny remains unused in frontend rendering, rotate or remove stored Bunny API credentials from plugin settings.

## Operational Notes
- Renewal script was executed once after install as a smoke test; it correctly reported "no renewal" when cert was not within the 30-day window.
- Renewal cadence is now deterministic and actionable from server-side logs.

## Status
- Cloudflare edge healthy
- Origin certificate valid
- Direct origin CA validation passes
- Environment is now compatible with Cloudflare `Full (strict)` operation
