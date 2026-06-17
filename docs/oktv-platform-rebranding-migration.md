# OKTV Platform Rebranding + De-Legacy Migration Plan

Date: 2026-06-17
Owner: Platform
Status: In progress

## Objective
Remove legacy framework identity from platform-facing assets and standardize the product as **OKTV Platform**, a unified media, research, and community system across:
- OffKilter.tv
- DifficultResearch.com
- RocketServe AI tooling

## 1) Audit: Legacy Branding References

## Audit method
- Full-text scan across repository artifacts (excluding `.git` and binary images/archives).
- Searched for legacy repository/framework naming markers and stale naming conventions in docs, metadata, and artifact paths.
- Reviewed runtime/plugin code for user-facing labels and architecture assumptions.

## Findings (this repository state)
- No active runtime/plugin UI copy contained legacy framework branding.
- Legacy identity appeared in:
  - repository naming references in docs
  - local absolute artifact paths
  - historical audit/remediation docs

## Remediation performed in this pass
- Updated repository-facing docs to neutral/OKTV naming.
- Removed legacy path naming from artifact references by replacing with `<repo-root>`.
- Updated top-level repository identity language to OKTV Platform.

## Validation
- Post-change scan should return no legacy framework naming markers in tracked docs/code.

## 2) Branding Replacement Standard

Use these terms platform-wide:
- `OKTV Platform`
- `Research Workspace`
- `Knowledge Hub`
- `Creator Studio`
- `Community Intelligence`
- `Research Assistant`

Avoid these terms in user-facing copy:
- agent workspace
- autonomous missions
- agent swarms
- marketplace language tied to framework tooling

## 3) Navigation Architecture (Target)

## Global top-level
1. Home
2. Watch
3. Research
4. Community
5. Intelligence
6. Creator Studio

## Area-level structure
### Home
- Featured stories
- Featured videos
- Live community activity
- Editor picks

### Watch
- Video categories
- Channels
- Series
- Live streams

### Research
- DifficultResearch integration
- Knowledge collections
- Evidence repositories
- Source archives
- Timelines

### Community
- Discussions
- Groups
- Direct messaging
- Events

### Intelligence
- Sidebar Research Assistant
- Cross-site search
- Recommendations
- Related evidence graph

### Creator Studio
- Uploads
- Publishing
- Analytics
- Monetization
- Audience tools

## 4) Homepage Layout (Target)

1. Hero lead: major investigation + CTA to watch/read.
2. Featured investigations rail (editorial cards with source confidence tags).
3. Featured video strip (watch-first row with live/series badges).
4. Research spotlight (dossiers, timelines, source bundles).
5. Community pulse (high-signal discussions + live threads).
6. Intelligence panel preview (assistant prompt starter + related topics).
7. Creator highlights (new channels, top contributors, upcoming live sessions).
8. Footer utility (about, standards, submission policy, transparency).

## 5) Color System (Target)

## Core palette
- `--oktv-charcoal: #171A1F`
- `--oktv-slate: #2B3440`
- `--oktv-ink: #111318`
- `--oktv-offwhite: #F5F4EF`

## Accent palette
- `--oktv-electric-blue: #1D6BFF`
- `--oktv-teal: #1FA7A1`
- `--oktv-signal-orange: #F26B2C`

## Semantic palette
- `--oktv-success: #199E73`
- `--oktv-warning: #D18A1A`
- `--oktv-danger: #C23E3A`
- `--oktv-border: #D8DDE5`

## Rules
- No bright neon/terminal aesthetics.
- No matrix green defaults.
- Keep contrast AA+ for body text and controls.

## 6) Iconography System (Target)

## Style
- Monoline + geometric fill hybrid
- Rounded corners, medium stroke
- 20/24px standard sizes

## Icon families
- Media: play, live, series, channel
- Research: document, source, citation, timeline, evidence
- Community: discussion, group, event, message
- Intelligence: search, related, summarize, explain, connect
- Creator: upload, publish, analytics, revenue

## Rules
- No robot mascots.
- No terminal/hacker glyph sets.
- Keep icon semantics literal and editorially neutral.

## 7) Legacy Architectural Dependencies to Remove

## Confirmed removable dependencies
1. Legacy repository naming references in docs and deployment notes.
2. Local absolute paths tied to prior repository name.
3. Any user-facing copy implying autonomous framework behavior.

## Confirmed absent in runtime
- No framework-specific runtime modules detected in plugin source.
- No framework package manifests or orchestration workflows detected.

## 8) Migration Plan: Platform Rebrand to OKTV Platform

## Phase A: Naming + Copy Baseline
1. Replace residual legacy naming in all tracked docs and metadata.
2. Standardize product naming to OKTV Platform across internal docs and onboarding artifacts.
3. Add copy lint rules to block disallowed terms in user-facing text.

## Phase B: Information Architecture Rollout
1. Implement new global nav labels.
2. Map existing routes into new IA without breaking URLs.
3. Add redirects only where labels/routes change.

## Phase C: UX Surface Modernization
1. Apply new color tokens and typography rules.
2. Introduce revised homepage section model.
3. Update icon set and component primitives.

## Phase D: Shared Identity (SSO)
1. Introduce single identity provider (Google OAuth/OIDC) as primary sign-in.
2. Implement shared session strategy for OffKilter.tv and DifficultResearch.com.
3. Unify profile model and cross-property permissions.

## Phase E: Shared Intelligence Layer
1. Deploy sidebar **Research Assistant** service contract:
   - summarize
   - explain
   - locate sources
   - connect related discussions
2. Remove autonomous framing from prompts, labels, and docs.
3. Use explicit assistant/guide language everywhere.

## Phase F: Shared Memory + Search Layer
1. Build unified index over:
   - articles
   - videos
   - discussions
   - dossiers
   - source collections
2. Attach provenance metadata and confidence attributes.
3. Provide cross-property search API for OKTV surfaces.

## Phase G: Expansion Enablement
1. Add property registry for OffKilter.tv, DifficultResearch.com, and future OKTV properties.
2. Keep shared auth/chat/search as platform services.
3. Keep brand tokens centralized and property themes configurable.

## 9) Expansion Architecture Requirements

The platform must support:
- shared authentication and profile state across properties
- shared assistant backend and prompt policy
- shared knowledge graph/search index
- property-specific presentation layers with common core services

Recommended service domains:
- `identity.oktv.network`
- `intelligence.oktv.network`
- `search.oktv.network`
- `memory.oktv.network`

## Success Criteria
- No user-visible legacy framework naming in code/docs/navigation/metadata.
- Navigation reflects media + research + community model.
- Sidebar is framed as research assistant, not autonomous agent.
- Shared SSO and shared memory/search are explicitly represented in architecture.
- A new visitor infers an independent OKTV Platform identity.
