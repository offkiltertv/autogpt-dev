# OffKilter.TV Infrastructure

## Google Cloud

### Compute
- Authoritative production WordPress VM: `oktv-main-deployment-vm`
- Historical/non-authoritative environment referenced during recovery: `difficultresearch-nginx-vm`

### Storage and Backups
- Boot disk snapshot recovery point created during incident response (June 2026).
- Additional SQL and `wp-content` backups exist from recovery actions.
- Ongoing requirement: maintain snapshot + database dump + content archive cadence.

### Snapshots
- Known recovery snapshot identifier referenced in incident notes:
  - `oktv-main-deployment-vm-20260612-172058`

## Cloudflare

### DNS
- Active records point `offkilter.tv` and `www.offkilter.tv` to the authoritative VM public IP.
- DNS cutover to production VM was completed during recovery.

### SSL/TLS
- Cloudflare is active for edge TLS and request proxying.
- Site should always be validated at both origin and proxied edge during incidents.

### Proxy
- Cloudflare proxy remains part of normal traffic path.
- Redirect and connectivity troubleshooting must validate: DNS, Cloudflare, origin NGINX, and WordPress settings.

## WordPress Application Layer

### Theme
- VidMov-based site presentation is in use (retain existing architecture).

### Plugins (Core to Product)
- VidMov ecosystem
- Elementor Pro
- wpForo
- ARMember
- myCred
- OffKilter Arcana plugin (`plugins/offkilter-arcana`)

### Database
- MariaDB (Bitnami stack)
- Health risk from disk pressure identified during recovery process
- Mandatory pre-change checks:
  - free disk space
  - MariaDB status
  - backup recency and integrity

## Operational Baseline
- Source of truth: GitHub repository
- Production server: deployment target only
- No direct unmanaged production edits
