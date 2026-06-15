# OFFKILTER Engagement Sprint

Date: 2026-06-14  
Branch: `feature/arcana-plugin`  
Reference baseline: `2f502d7`

## Objective
Advance OFFKILTER from separate destinations into a coherent engagement platform with clear discovery paths for Signals, Arcana, Creators, and Community.

## Phase 1 - Destination Audit

| Destination | Purpose Statement | Featured Content Strategy | Community Strategy |
|---|---|---|---|
| ⚡ Signals | Fast, high-signal clips (0-90 seconds) for immediate updates and momentum moments. | Prioritize newest short-form clips with clear creator attribution and visible category identity. | Route viewers to `#signals` as the default discussion path. |
| 🔮 Arcana | Interpretation-first readings focused on premonitions, outcomes, symbolism, and pattern analysis. | Keep Arcana rail curated by Arcana terms (`arcana`, `premonitions`, `outcomes`) and active creators. | Route viewers to `#arcana` as the default interpretation/discussion path. |
| 👥 Creators | Editorially featured active creators, not purely algorithmic ordering. | Present a curated creator list with direct channel links and clear identity. | Route creator traffic toward community participation and future creator-room ownership. |
| 💬 Community | The platform’s shared discussion layer across content types. | Treat community as a destination, not a footer utility. | Keep room mapping explicit: Signals/Arcana/Cases/Creator rooms. |

## Phase 2 - Signals Experience (Implemented)

### Changes
- Signals archive description upgraded for immediate clarity:
  - `Signals are fast, high-signal clips (0-90 seconds)...`
  - Includes direct `#signals` discussion callout.
- Homepage now includes explicit Signals intro copy block:
  - `What Is A Signal?`
  - Definition + CTA links.
- Signals rail promoted in homepage hierarchy:
  - Signals intro block
  - `⚡ Signals` rail
  - then Arcana
- Signals rail tuned to native Signals category query (`term_id=2429`), no static ID lock.

### Result
Visitors can now understand what a Signal is before encountering the feed.

## Phase 3 - Creators Experience (Implemented)

### Changes
- Created editorial creators destination page:
  - `/creators/` (Page ID `8339`)
- Main menu `Creators` now points to `/creators/` instead of direct theme member list.
- Featured creator list intentionally curated:
  - FOOD FOR THOUGHT 313
  - MADAMEBUTTERFLY444
  - POSHRANDY55
  - AllseeingisisOracle
  - Astraea 5D
- Homepage `👥 Featured Creators` rail subtitle updated:
  - `Editorial picks: active OFFKILTER creators`
- Rail IDs set to representative posts from the active creator set.

### Result
Creator discovery is editorially guided and platform-native, not purely theme-generated listing behavior.

## Phase 4 - Community Readiness (Implemented)

SidebarChat mapping has been made explicit in homepage copy:
- `Signal -> #signals`
- `Arcana -> #arcana`
- `Case -> #cases`
- `Creator -> creator room`

No live chat integration was introduced in this sprint.

## Phase 5 - Legacy Surface Reduction (Implemented)

### Changes
- Main navigation remains focused on OFFKILTER destinations:
  - Home, Watch, Signals, Arcana, Creators, Community, Explore
- Side navigation reduced to core destination set:
  - Signals, Arcana (+ children), Community
- Removed stale side-menu `Music` item.
- Reduced direct dependence on theme-generated `member-list` by introducing `/creators/` as the public entry.

### Remaining legacy surfaces (intentional for now)
- `/trending/`, `/member-list/`, `/channel/` remain accessible but are no longer primary creator-entry UX.

## Phase 6 - Mobile Engagement (Implemented)

Homepage discovery order now front-loads engagement:
1. Signals explainer
2. ⚡ Signals rail
3. 🔮 Arcana rail
4. 👥 Featured Creators
5. Community pathway via SidebarChat mapping

This reduces scroll-to-value for first-time mobile visitors.

## Validation

### Route health
- `/` -> `200`
- `/video-category/signals/` -> `200`
- `/video-category/arcana/` -> `200`
- `/creators/` -> `200`
- `/community/` -> `200`
- `/member-list/` -> `200`
- `/channel/` -> `200`
- `/trending/` -> `200`

### Homepage/source checks
Confirmed in public HTML:
- Signals intro block (`What Is A Signal?`)
- Signals rail appears before Arcana rail
- Featured Creators rail present with editorial copy
- SidebarChat mapping updated with room mapping language
- Main nav Creators points to `/creators/`

## Backup / Safety
- Pre-change backup snapshot:
  - `/opt/bitnami/backups/offkilter-engagement-20260615-024343/`
  - Includes homepage Elementor JSON, menu snapshots, and destination term snapshots.

## Outcome
OFFKILTER now communicates engagement intent clearly:
- What OFFKILTER is: a destination-led platform.
- What a Signal is: explicit and visible.
- Who the creators are: editorially curated entry point.
- Where community lives: directly mapped from content type to future rooms.
