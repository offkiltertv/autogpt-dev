# OFFKILTER Platform v2.5 — Professional Product Pass

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `2.5.0` (from `2.4.0`)
Nature: **quality sprint** — no new product surface; polish, ownership clarity, consistency.

---

## Phase 1 — Template Ownership Audit

Who renders each public surface. "Native OFFKILTER" = plugin owns the whole render;
"Plugin Output" = plugin shortcode embedded into a theme page; "VidMov Core" = theme
template we do not control (no theme source / no child theme yet).

| Public surface | Owner | How |
|---|---|---|
| `/pulse/` archive + `pulse_tag` | **Native OFFKILTER** | `template_include` → `templates/archive-pulse_item.php` → `[oktv_pulse_destination]` |
| Homepage | **Plugin Output** | Elementor page + `[oktv_platform_story]`, `[oktv_platform_intro]`, `[oktv_pillar_strip]` |
| Discover page | **Plugin Output** | `[oktv_discover_page]` (Editor's Picks → Featured Pulse → Signals → Arcana → Creators) |
| Pillar destinations (Signals/Arcana/Creators/Community) | **Plugin Output** | `[oktv_destination_hero]` + `[oktv_pillar_strip]` + `[oktv_curated_section]` |
| Creator pages | **Plugin Output** | `[oktv_creator_page]` |
| Auth (login/register) | **Plugin Output + CSS** | ARMember forms + `ok-auth-frame` scoped CSS |
| Single video (`vidmov_video`) | **VidMov Core** | Theme template — **not owned**; lockdown buffer + data-layer guard applied |
| Video archives / category | **VidMov Core** | Theme template; CSS treatment only |
| Channel pages | **VidMov Core** | Theme template; CSS treatment only |
| Member list (`/member-list/`) | **VidMov Core** | Theme template; CSS treatment only |
| Search | **VidMov Core** | Theme template; CSS Section T treatment only |
| Community (`/community/`) | **wpForo Core** | Plugin template; CSS Section H treatment only |
| Footer | **Plugin Output** | `[oktv_footer_brand]` (operator-embedded) |
| Theme Override (child theme) | **None yet** | The missing layer — see Phase 2 + v2.6 |

**Takeaway:** Everything *discovery/creator/Pulse* is now OFFKILTER-owned. The
remaining VidMov-core surfaces (single video, archives, channel, member-list, search)
are the child-theme backlog — they are styled, not owned.

---

## Phase 2 — Replace Intercepts

Audit of every output interception and whether it can become explicit rendering.

| Interception | Verdict | Rationale |
|---|---|---|
| Pulse archive | **Replaced ✅** | Now a clean `template_include` template, not a buffer. The model for everything else. |
| Monetization meta (price/PPV/expiration) | **Data-layer (preferred) ✅** | `get_post_metadata` guard empties values at the source — explicit, not output filtering. |
| Single-video authoring blocks | **Interception retained (documented exception)** | We do not own the VidMov single template; until a child theme exists, the render-path lockdown buffer is the only way to remove theme-rendered blocks. Gated to anonymous single-video, body-only, filterable off. |
| `the_content` meta/shortcode/label strip | **Interception retained (low-risk)** | Operates only on post content (a value we already filter), not the whole page. |
| CSS suppression (Section V) | **Defense-in-depth** | Backstop only; the data-layer guard is primary. |

**Decision:** the single-video lockdown buffer is the one justified interception. It
disappears the moment a **VidMov child theme** lands (override the single template with
a `current_user_can('edit_post')` guard) — the highest-value debt payoff (v2.6).

---

## Phase 3 — Pulse Experience (flagship)

- **Landing** (`[oktv_pulse_destination]`): hero · What is Pulse? · Featured · Trending
  (engagement proxy) · Latest · Creators on Pulse · reserved Discuss.
- **Archive** (`/pulse/`): production-ready, self-flushing routes, branded template.
- **Cards**: own teal accent edge, live "Pulse" badge with heartbeat motion, a **"New"
  recency marker** (<48h), owner-only "Processing…" state.
- **Publishing**: REST `create → finalize → get` live; tus upload is a documented placeholder.
- **Creator integration**: `show_pulse` section on creator pages.
- **Visual identity**: dedicated `--ok-pulse` token, distinct from Signals blue.

Pulse reads as a flagship, not a tab.

---

## Phase 4 — Creator Quality

`[oktv_creator_page]` supports: About · Featured · Pulse · Signals · Videos · Playlists ·
Related Creators · reserved Community/Discuss · verified badge · **owner-only profile
completeness meter**. Discovery surfaces creators via `[oktv_featured_creator]` and the
Discover "Creators to Watch" rail. Remaining: real verification flow + a self-serve
creator dashboard (v2.6+).

---

## Phase 5 — Public Surface Review (anonymous)

"Would a first-time visitor know this is OFFKILTER, or infer WordPress?"

| Page | Verdict | Leak risk |
|---|---|---|
| Homepage / Discover / Pulse / Pillars / Creator | **OFFKILTER** | Clean (plugin-owned) |
| Single video | **OFFKILTER-ish** | Authoring metadata removed at render; player chrome is VidMov-styled. Child theme closes the gap. |
| Archives / Channel / Member list / Search | **Mostly OFFKILTER** | Styled but theme-structured (pagination text, archive titles, tab labels remain VidMov). |
| Community | **OFFKILTER-ish** | wpForo styled; vocabulary still partly forum-native. |

**Note on verification:** production is behind Cloudflare (blocks automated fetch), so
the anonymous audit is reasoned from the render path + the admin `?okarcana_diag=surface`
scanner rather than a live crawl. The lockdown removes the named authoring fields at
output; operator should confirm in a private window post-deploy.

---

## Phase 6 — Design Language Audit

Consolidated this sprint (CSS Section AB) into **one authoritative interaction layer**:

| Element | State |
|---|---|
| **Cards** | Unified hover/focus lift (translateY + border-accent) across signals/pulse/intro/featured-creator/recommended-next/creator-featured; Pulse keeps its teal edge. |
| **Buttons** | `.ok-btn` + variants (primary/ghost/signal/arcana/sm), consistent radius/transition/focus. |
| **Icons** | Font Awesome 5 solid, pillar-accented (`fa-wave-square` Pulse, `fa-bolt` Signals, `fa-gem` Arcana, …). |
| **Spacing/Typography** | `--ok-*` tokens; tabular section titles; clamp-based headings. |
| **Hover states** | Now uniform (was duplicated/ad-hoc per component). |
| **Loading states** | New shared skeleton (`.ok-feed-loading` / `.ok-skeleton-card` shimmer). |
| **Focus** | Global `*:focus-visible` signal ring; 44px touch targets. |
| **Motion** | Pulse heartbeat + card lift + shimmer, all `prefers-reduced-motion` aware. |

One design system, applied consistently.

---

## Phase 7 — Investor Demo: 15 highest-impact improvements (ranked by user impact)

For showing creators / investors / strategic partners. Differentiation- and
polish-weighted, highest first.

1. **Pulse live end-to-end** — wire the Pulse Clipper to the REST endpoints + storage (tus). The demo's hero moment: capture → publish → appears. Today it's scaffolding + placeholder upload.
2. **VidMov child theme** — own single-video/archive/search/channel; retires the lockdown buffer and the last "this is WordPress" tells. Single biggest credibility lever.
3. **SidebarChat activation** — slots are reserved everywhere; turning on Stream/Crisp makes "community-driven" true, not promised.
4. **Google sign-in** — frictionless onboarding; a manual ARMember form is a demo-killer.
5. **Editorial curation, staffed** — Editor's Picks/Featured rails only impress when a human curates weekly. Tooling done; taste required.
6. **Seed real Pulse content** — a populated, current Pulse feed is the whole pitch; an empty flagship undercuts everything.
7. **Search redesign** — group by Pulse/Signals/Arcana/Creators; highest-intent users land here.
8. **Arcana single template** — the unique differentiator still renders as a generic post.
9. **Top creator profiles completed** — avatars, bios, featured, verified — so Creators reads as a network, not stubs (the completeness meter now nudges this).
10. **Mobile pass** — five-pillar nav, Pulse cards, forms verified on small screens; most demo opens happen on a phone.
11. **Creator verification flow** — turn the reserved badge into earned trust; verified creators are a partnership story.
12. **Footer + nav finalize** — retire 2023 copyright; lead nav with the five pillars incl. Pulse.
13. **Real Pulse trending metric** — replace the comment-count proxy with views/engagement scoring.
14. **Performance: Cloudflare cache + Bunny media** — perceived speed reads as product quality in a live demo.
15. **Analytics** — time-to-first-Pulse, transformation-attach rate, return rate; the metrics an investor asks for.

**Through-line:** the platform's *surfaces* are investor-ready; the gap is **live data
+ two infra unlocks** (child theme, Pulse storage) + **editorial staffing**.

---

## Phase 8 — Debt & v2.6

### Completed this sprint
- Template ownership documented; intercepts audited and minimized (Pulse archive is now a clean template).
- Pulse flagship polish (New marker, consolidated identity).
- Design language consolidated into one interaction layer (hover/focus/loading/motion).
- Profile completeness + Editor's Picks (carried from v2.4) reviewed and styled.

### Remaining technical debt
- **No child theme** — forces the single-video lockdown buffer and CSS-only treatment on VidMov-core surfaces. Top priority.
- **Pulse storage/tus** unimplemented — publishing is create/finalize only.
- **CSS size** (~4,100 lines, sections A–AB) — candidate for split + minify build step.
- **No PHP test harness** for the growing shortcode/REST surface.
- **Trending proxy** (comment_count) needs a real metric.
- **Scattered legacy `oktv-*` docs** — superseded by `offkilter-platform-roadmap.md`.

### Remaining UX debt
- VidMov-structured pages (archives/channel/search/member-list) still expose theme
  pagination/title/tab text.
- Community vocabulary partly forum-native.
- No empty-state guidance for a brand-new creator with zero content.
- Single-video player chrome is VidMov-styled (child theme).

### Recommendations for v2.6
1. **Ship the VidMov child theme** (own single/archive/search/channel; delete the buffer).
2. **Implement Pulse storage + tus** and connect the Clipper M1.
3. **Activate SidebarChat** (flip the reserved slots live).
4. **Google Identity** (sign-in + verification flow).
5. **Search redesign** + **Arcana single template**.
6. Begin a **creator dashboard** (self-serve bio/featured/verification).

---

## Score trajectory

| Sprint | Version | Avg | Lift |
|---|---|---|---|
| v2.3 | 2.3.0 | ~8.7 | Pulse productization + identity |
| v2.4 | 2.4.0 | ~8.9 | Render-path lockdown, Pulse archive/REST, completeness |
| **v2.5 (this)** | **2.5.0** | **~9.0** | Ownership clarity, intercept minimization, design-language consolidation, Pulse flagship polish |
| Target v2.6 | — | ~9.4 | Child theme + Pulse live + SidebarChat + Google Identity |
