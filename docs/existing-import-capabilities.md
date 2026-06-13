# Existing Import Capabilities Audit (Production)

Date: 2026-06-12
Environment audited: `offkilter-tv` GCP project, instance `oktv-main-deployment-vm` (`us-west1-a`)
Mode: Read-only (no plugin activation, no config changes)

## Scope
Objective: identify existing production capabilities for:
- YouTube imports
- Video imports
- RSS/feed imports
- Scheduled/drip publishing
- Content queues and auto-posting
- Bulk imports and CPT targeting

## Production Findings Summary
- Production WordPress has **one strong existing automation/import engine**: `wp-automatic` (active).
- `wp-automatic` is already in live use with existing campaigns and cron activity.
- Native WordPress scheduled publishing exists, but queue orchestration for 90-day staggered plans is limited without additional logic.
- ARMember provides drip-related membership timing, but not YouTube/feed ingestion.

## Infrastructure Context (Staging Check)
- `offkilter-tv` project instance list returns only:
  - `oktv-main-deployment-vm`
- No dedicated staging VM is visible in the `offkilter-tv` project.
- Conclusion: **no explicit OffKilter staging environment currently identified** in the authoritative project.

## Complete Plugin Inventory (Production)

| Plugin | Version | Status | Purpose | Relevant Features for This Audit |
|---|---:|---|---|---|
| all-in-one-seo-pack | 4.9.7.2 | Active | SEO | None for import automation |
| armember | 6.9.11 | Active | Membership/paywall | Drip/content availability by membership timing (not feed importer) |
| armembercommunity | 2.2 | Active | ARMember social addon | Community layer only |
| armember-membership | 4.0.66 | Active | ARMember lite package | Membership controls only |
| beeteam368-extensions | 2.3.8 | Active | VidMov media features | Video post ecosystem support; no direct evidence of YouTube/feed import engine |
| beeteam368-extensions-pro | 2.3.8 | Active | VidMov Pro extensions | Playlist/channel/reaction features; not a feed importer by itself |
| bp-better-messages-websocket | 2.5.19 | Inactive | Messaging | None |
| better-search-replace | 1.4.10 | Inactive | DB search/replace | None |
| bunnycdn | 3.0.1 | Active | CDN integration | None |
| cmb2 | 2.12.0 | Active | Custom metabox framework | None |
| code-snippets | 3.9.6 | Active | Runtime snippets | Could host custom glue code, not importer |
| custom-php-settings | 2.4.1 | Active | PHP setting control | None |
| disabled-source-disabled-right-click-and-content-protection | 1.7.5 | Active | Client-side content protection | None |
| elementor | 4.1.3 | Active | Page builder | None |
| elementor-pro | 3.33.2 | Active | Builder pro features | None |
| extendify | 3.1.0 | Active | AI/onboarding | None |
| google-analytics-for-wordpress | 10.2.2 | Active | Analytics | None |
| jetpack | 15.3.1 | Active | Security/performance/marketing | Syndication/outbound features possible; no core YouTube importer |
| mycred | 3.1 | Active | Points/rewards | Incentives after publish; not importer |
| one-click-demo-import | 3.4.1 | Active | Demo content import | Theme demo import only, not YouTube/feed automation |
| optinmonster | 2.16.24 | Active | Lead capture | None |
| redux-framework | 4.5.11 | Active | Theme options framework | None |
| sassy-social-share | 3.3.79 | Active | Social sharing | None |
| google-site-kit | 1.180.0 | Active | Google integrations | None |
| simple-tags (TaxoPress) | 3.37.3 | Inactive | Taxonomy tools | Could help tagging; not importer |
| uk-cookie-consent | 3.3.1 | Active | Consent banner | None |
| theme-my-login | 7.1.14 | Active | Auth UX | None |
| uncanny-automator | 6.8.0 | Inactive | Automation workflows | Potential integration layer if activated; currently not in use |
| updraftplus | 1.26.5 | Active | Backup/restore | None |
| userfeedback-lite | 1.7.0 | Inactive | Surveys | None |
| wp-automatic | 3.136.0 | Active | Automated content ingestion/posting | **Primary candidate**: YouTube/RSS/feed import, cron automation, post type targeting |
| wp-pagenavi | 2.94.5 | Active | Pagination | None |
| wp-file-manager | 8.0.4 | Active | File manager | None |
| wpforms-lite | 1.10.1.1 | Active | Forms | None |
| wpforo | 2.4.6 | Active | Forum/community | Discussion destination, not importer |
| wp-mail-smtp | 4.8.0 | Inactive | SMTP | None |

Must-use plugins: none detected.
Drop-ins: none detected.

## Relevant Plugin Deep Audit

### 1) WordPress Automatic (`wp-automatic`) — Active
Evidence from production:
- Plugin description explicitly includes YouTube + RSS auto-posting.
- Cron hook active: `wp_automatic_hook` every minute.
- Existing campaign data present: `wp_automatic_camps` table has 18 campaigns.
- Existing campaign types in DB: `Youtube` (17), `Single` (1).
- Current campaigns target `vidmov_video` CPT and publish status.
- Source code confirms:
  - feed support (`core.feeds.php`, `camp_type = 'Feeds'`)
  - YouTube playlist/user parsing logic (`inc/youtube_class.php`)
  - selectable post type (`camp_post_type`)
  - custom update frequency (`cg_update_every` + unit minutes/hours/days)
  - custom posting windows and cron controls

Capability matrix:

| Question | Result | Notes |
|---|---|---|
| Can it import YouTube URLs? | Yes | YouTube campaign types and existing production campaigns |
| Can it import playlists? | Yes | Playlist parsing logic found in plugin source |
| Can it import RSS feeds? | Yes | `Feeds` campaign type and feed core modules |
| Can it schedule posts? | Partial | Supports recurring frequency + posting windows; no deterministic 90-day queue calendar out of box |
| Can it drip-feed over time? | Yes | Frequency controls + cron-based staggered posting |
| Can it create WordPress posts automatically? | Yes | Core behavior |
| Can it integrate with custom post types? | Yes | `camp_post_type` selector and usage in core |

### 2) ARMember (`armember`, `armember-membership`) — Active
Capability matrix:

| Question | Result | Notes |
|---|---|---|
| YouTube import | No | Not an ingestion engine |
| Playlist import | No | Not an ingestion engine |
| RSS import | No | Not an ingestion engine |
| Schedule posts | Partial | Membership drip timing events exist, not import scheduling |
| Drip-feed content over time | Yes (membership access) | Access drip, not content harvesting |
| Auto-create WP posts | No | Not its role |
| CPT integration | Partial | Access rules can apply to CPT content |

### 3) Uncanny Automator — Inactive
Capability matrix (inactive state):

| Question | Result | Notes |
|---|---|---|
| YouTube import | Unknown/No direct | Not active; typically automation glue |
| Playlist import | Unknown | Not active |
| RSS import | Possible via app/webhook recipes | Requires activation/config |
| Schedule posts | Possible via recipes | Requires activation/config |
| Drip-feed content over time | Possible workflows | Requires activation/config |
| Auto-create WP posts | Possible with recipe actions | Requires activation/config |
| CPT integration | Possible | Requires activation/config |

### 4) BeeTeam368/VidMov Extensions — Active
Capability matrix:

| Question | Result | Notes |
|---|---|---|
| YouTube import | Not evidenced as primary importer | Provides media feature layer |
| Playlist import | Not evidenced as ingestion engine | Native playlist/channel presentation |
| RSS import | No evidence found | |
| Schedule posts | Uses WP post model | Not a dedicated queue/import tool |
| Drip-feed content | No dedicated evidence | |
| Auto-create posts | Not primary purpose | |
| CPT integration | Yes | VidMov video/channel ecosystem |

## Special Goal Assessment

Target sources:
- `Prem and Outcome 2026`
- `Liked Videos`

### Can existing tools ingest and schedule over 90 days?

#### Prem and Outcome 2026
- If this is a public/unlisted YouTube playlist/channel feed, **yes** via `wp-automatic`.
- It can auto-create posts and drip over time via frequency/cron windows.
- For strict 90-day deterministic spacing, use Arcana scheduler layer on top.

#### Liked Videos
- **Not reliably with API-key-only YouTube ingestion** because Liked Videos is typically private account data.
- Practical options:
  - Convert to/export as accessible playlist URL(s), then import.
  - Use manual CSV/URL list ingestion.

## Solution Ranking

### A. Existing plugin only
Best candidate: `wp-automatic` only.
- Pros: already active, already posting to `vidmov_video`, no new plugin required.
- Cons: less deterministic control for a strict 90-day queue calendar; private Liked Videos limitation.

### B. Existing plugin + Arcana integration (**Recommended**)
- Use `wp-automatic` for ingestion (YouTube/feed acquisition).
- Route into Arcana post type (`arcana_entry`) and use Arcana scheduler/queue for deterministic 90-day release cadence + wpForo linkage/disclaimer.
- This minimizes new importer work while preserving Arcana-specific publishing logic.

### C. Custom Arcana importer required
Required only for gaps:
- private `Liked Videos` ingestion without converting to accessible source list
- strict custom ingest/transformation rules not supported by `wp-automatic`

## Fastest Path Recommendation
1. Leverage existing `wp-automatic` for YouTube/feed ingestion immediately.
2. Configure campaigns to target Arcana CPT once Arcana plugin is activated.
3. Use Arcana scheduler for 90-day queued publish control.
4. Handle `Liked Videos` via exported playlist/CSV fallback.

This path delivers fastest automation with current owned tooling and avoids rebuilding core ingestion features.
