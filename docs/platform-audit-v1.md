# OFFKILTER Platform Audit v1

Date: 2026-06-14  
Environment: Production (`https://www.offkilter.tv`)  
Branch: `feature/arcana-plugin`

## Scope
Public route audit focused on discovery friction, destination clarity, and legacy path cleanup.

## Current Public Destinations

### Primary discovery routes
- `/` (homepage)
- `/video/` (watch archive)
- `/video-category/signals/` (Signals archive)
- `/video-category/arcana/`
- `/video-category/arcana/premonitions/`
- `/video-category/arcana/outcomes/`
- `/member-list/`
- `/channel/`
- `/community/`
- `/trending/`

### Secondary/utility routes still public
- `/video-category/youtube/`
- `/video-category/oktv-ftv/`
- `/audio-category/music/`
- `/main-login/`, `/main-register/`, `/main-profile/`
- Multiple legacy auth/profile routes (`/login/`, `/login-2/`, `/register/`, `/register-2/`, `/edit_profile/`, `/edit_profile-2/`, etc.)
- Woo routes (`/shop/`, `/cart/`, `/checkout-2/`, `/my-account/`)

## Route Health Snapshot

All audited core routes returned `HTTP 200`:
- `/`
- `/video/`
- `/trending/`
- `/member-list/`
- `/channel/`
- `/community/`
- `/video-category/signals/`
- `/video-category/arcana/`
- `/video-category/arcana/premonitions/`
- `/video-category/arcana/outcomes/`

Notable access behavior:
- `/main-profile/` resolves to login redirect target (`/main-login/?redirect_to=%2Fmain-profile%2F`).
- Several profile-management pages are public URLs that redirect into login workflows.

## Navigation Inventory (Post-remediation)

### Main Menu
1. Home → `/`
2. Watch → `/video/`
3. ⚡ Signals → `/video-category/signals/`
4. Arcana → `/video-category/arcana/`
5. Creators → `/member-list/`
6. Community → `/community/`
7. Explore → `/trending/`
8. Premonitions (child of Arcana)
9. Outcomes (child of Arcana)

### Side Menu
1. ⚡ Signals → `/video-category/signals/`
2. Arcana → `/video-category/arcana/`
3. Premonitions (child)
4. Outcomes (child)
5. Community → `/community/`

## Legacy / Confusing Surface Areas

### A. Duplicate user flows
- Duplicate pairs exist for login/register/profile/password pages (`-2` variants and non-`-2` variants).
- User expectation mismatch: many profile routes look public but are effectively gated.

### B. Route ambiguity
- Creator discovery split across `/member-list/` and `/channel/`.
- Channel URLs include both readable handles and hashed handle routes (e.g., `@cea018...`), which reduces trust/readability.

### C. Mixed legacy destinations
- WooCommerce pages exist publicly even though current platform narrative is video + creators + community.
- Legacy sidebar/utility pages remain indexable (`/sidebar-left/`, `/sidebar-right/`, `/sidebar-hidden/`).

### D. Low-value exposed structures
- Historical taxonomy rails (`youtube`, `oktv-ftv`) are still prominent in metadata/category stacks.

## High-Value Cleanup Opportunities

### Safe now
- Keep main/side navigation focused on `Watch`, `Signals`, `Arcana`, `Creators`, `Community`, `Explore`.
- Keep Signals/Arcana as top-level discovery rails.
- Continue de-emphasizing legacy categories in homepage rails.

### Requires review
- Consolidate duplicate auth/profile routes to one canonical path family.
- Decide whether Woo pages remain first-class or are de-emphasized from discovery.
- Resolve hashed creator handles into readable canonical creator URLs.
- Audit whether `/member-list/` or `/channel/` should be the canonical creator index.

## Destination Strategy Mapping

| Current URL Pattern | Destination Pillar | Notes |
|---|---|---|
| `/video-category/signals/` | ⚡ Signals | Short-form discovery (0-90s) |
| `/video-category/arcana/*` | 🔮 Arcana | Long-form Arcana discovery |
| `/member-list/`, `/channel/*` | 👥 Creators | Needs canonical creator index decision |
| `/community/` | 💬 Community | Primary discussion entry point |
| `/video/`, `/trending/` | Explore/Watch blend | Supports broad discovery |

## v1 Audit Conclusion

Platform direction is now coherent at top navigation level, but legacy route surfaces still exist in parallel. The highest-impact next step is canonicalizing creator and auth routes while keeping Signals + Arcana as first-class discovery destinations.
