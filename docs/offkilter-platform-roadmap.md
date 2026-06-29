# OFFKILTER Platform Roadmap (Authoritative)

Date: 2026-06-29
Status: Single source of truth — supersedes the scattered `oktv-*` roadmap/architecture
notes for forward planning. Plugin version at writing: `2.3.0`.

This document consolidates current state, milestones, vision, dependencies, and debt.
Companion canonical docs: `platform-language.md`, `pulse-editorial-guidelines.md`,
`pulse-integration.md` (backend contract), `creator-optout.md`,
`platform-v2-product-review.md`, `platform-v2_1-destination-strategy.md`. The Pulse
Clipper app lives in its own repo (`pulse-clipper`).

---

## 1. Current State

OFFKILTER is a WordPress + VidMov + ARMember + wpForo stack. The custom
`offkilter-arcana` plugin (v2.3.0) has, without touching the theme, built:

- **Design system** — `--ok-*` tokens, dark theme, five pillar accents incl. a
  dedicated **Pulse teal** (`--ok-pulse`). CSS sections A–Z + AA-era additions.
- **Platform vocabulary** — Pulse · Signals · Arcana · Creators · Community (no
  VidMov/WordPress terms on public surfaces).
- **Destinations & discovery** — `[oktv_platform_intro]`, `[oktv_platform_story]`,
  `[oktv_pillar_strip]`, `[oktv_destination_hero]`, `[oktv_curated_section]`,
  `[oktv_discover_page]` (leads with Featured Pulse), `[oktv_creator_page]`,
  `[oktv_featured_creator]`, `[oktv_recommended_next]`, `[oktv_watch_nav]`.
- **Pulse** — `pulse_item` CPT (archive `/pulse/`), `[oktv_pulse_feed]`,
  `[oktv_pulse_destination]` (hero · what-is-Pulse · Featured · Trending · Latest ·
  Creators · reserved Discuss), Pulse as a first-class pillar with its own
  iconography (`fa-wave-square`), accent, card style, and heartbeat motion.
- **Creator relations** — opt-out flow + "Why am I on OFFKILTER?" + brand footer.
- **Public Surface Lockdown** — data-layer guard (see §6 Priority Zero).
- **Accessibility** — focus rings, 44px targets, contrast, reduced-motion, ARIA.

**The honest gap:** the public site is strong, but several finishing items require
the **operator** (run the Priority Zero diagnostic; build the Pulse REST endpoints;
embed shortcodes; child theme) — not more plugin CSS.

---

## 2. Near-Term Milestones (next 1–2 sprints)

| # | Milestone | Owner | Notes |
|---|---|---|---|
| N1 | **Close Priority Zero** | Operator + dev | Run `?okarcana_diag=surface`, capture exact key/hook, lock precisely (§6). |
| N2 | **Pulse REST endpoints** in `offkilter-arcana` | Dev | create / tus-upload / finalize / read per `pulse-integration.md`. |
| N3 | **Pulse Clipper M1** wired to N2 | App dev | First real publish → `pulse_item` (processing→ready). |
| N4 | **Operator embeds** | Operator | `[oktv_platform_story]` + `[oktv_pillar_strip]` on home; `/pulse/` destination; nav "Pulse"; `show_pulse="1"` on creator pages; flush permalinks. |
| N5 | **Arcana single template** | Dev | Elementor single for `arcana_entry` (gem header, disclaimer, related). |
| N6 | **VidMov child theme** | Dev | Permanent fix for Priority Zero + card/single template ownership. |

---

## 3. Long-Term Vision

- **OFFKILTER = the destination** for creator Pulse content; the Pulse Clipper is the
  capture tool; SidebarChat is the conversation layer; Google Identity is the shared
  account; creators **own** their presence cross-surface.
- **Discovery is editorial-first**, algorithm-assisted (a future "Pulse" trend engine
  feeds Discover alongside human curation).
- **Creator ownership** — verified identity, self-managed profiles, unified avatar,
  cross-site identity, and a creator dashboard.
- **Community everywhere** — every Pulse, Signal, Arcana reading, and creator has a
  living discussion via SidebarChat.

---

## 4. Discovery Engine — distinct purposes (Phase 5)

Each destination has a job; they complement, not compete.

| Destination | Purpose | Time horizon | Form | Source |
|---|---|---|---|---|
| **Pulse** | Discovery of *current*, short-form, transformative moments | Now / breaking | ≤ short clips | Pulse Clipper uploads |
| **Signals** | The standout highlight reel — "what's worth your attention" | Recent / evergreen | ≤90s clips | Imported/curated video |
| **Arcana** | The intuitive vertical — readings, premonitions, outcomes | Evergreen | Readings/posts | Arcana creators |
| **Creators** | The people — destinations & ownership | Persistent | Profiles | Members |
| **Community** | The conversation — discuss what you discover | Continuous | Threads (wpForo→SidebarChat) | Everyone |

Rule of thumb: **Pulse = what's happening now**, **Signals = what's worth seeing**,
**Arcana = a distinct world**, **Creators = who**, **Community = the talk**.

---

## 5. Creator Publishing Flow (Phase 4)

```
Pulse Clipper (capture → transform)
        │  resumable tus upload (docs/pulse-integration.md)
        ▼
POST /pulse/items  →  pulse_item (status: processing)
        │  processing (thumbnail/normalize)
        ▼
finalize  →  status: ready
        │
        ├──► Pulse feed  (/pulse/, [oktv_pulse_feed], Discover "Featured Pulse")
        ├──► Creator profile  ([oktv_creator_page show_pulse="1"])
        └──► Discussion (FUTURE — _ok_pulse_discussion_id → SidebarChat)
```

OFFKILTER is the natural publishing destination because the Clipper's default backend
is OFFKILTER, identity is shared (Google), and the published Pulse immediately gains a
home (feed + profile) and a future conversation (SidebarChat). OKTV depends on the
Clipper **only** through the REST contract — no code coupling.

---

## 6. Priority Zero — Root Cause (documented)

**Symptom:** anonymous visitors see authoring/editor controls on video pages —
Purchase Price, Expiration, Pay Per View, Video Categories editor, Audio Categories editor.

**Root cause:** these are rendered by the **VidMov theme + ARMember PPV add-on**, in
the **single-video template / frontend submission-edit form**, *not* by this plugin.
The cluster (category-editor widgets + price/PPV/expiration) is the **VidMov frontend
video edit form** being output without a capability/`is_user_logged_in` guard. The
theme source is **not in this repo**, and the exact meta keys (beyond
`beeteam368_membership_plans`, which is gating and must not be touched) and the exact
render hook are VidMov internals; the live site blocks automated inspection.

**What the plugin does (data layer, not CSS):**
1. `get_post_metadata` guard empties display/authoring monetization meta
   (`price|ppv|pay_per_view|purchase|expir|monetiz|subscription|wallet|coin|token_price`)
   for anonymous/non-editor frontend views — the template reads empty, renders nothing.
2. `the_content` label-anchored strip removes blocks containing the known labels when
   the form is embedded in content.
3. Filterable `remove_action` list + authoring-shortcode stripping for theme-rendered forms.
4. **Diagnostic / culprit-finder:** `?okarcana_diag=surface` (admin) reports each
   label's exact wrapping element (tag/class/id) **and** bound hook callbacks.

**The last mile (operator — closes it permanently):**
1. As admin, load an affected video page with `?okarcana_diag=surface`; read the
   `OKARCANA SURFACE SCAN` + `OKARCANA DIAGNOSTIC` HTML comments.
2. Supply the exact key/hook back to the plugin filters (or to dev), OR
3. Ship a **VidMov child theme** overriding the single-video template / submit form
   with `current_user_can('edit_post')` guards — the definitive fix.

Until then, the data-layer guard removes the *values* from anonymous rendering; the
diagnostic gives the precise selector/hook for a one-pass close.

---

## 7. Dependencies

| Dependency | Needed for | Status |
|---|---|---|
| Operator runs Priority Zero diagnostic | Closing the metadata leak permanently | **Pending operator** |
| Pulse REST endpoints (offkilter-arcana) | Pulse Clipper publishing | Not built |
| Pulse Clipper M1 (standalone repo) | Real Pulse content | Scaffold only |
| Google Identity (ARMember/Nextend) | Verification, ownership, unified avatar | Reserved |
| SidebarChat provider (Stream/Crisp) | Discuss slots going live | Reserved (UI in place) |
| VidMov child theme | Permanent template control | Not built |
| Bunny Stream / object store | Pulse media storage | Operator/infra |
| Cloudflare cache rules | Anonymous performance | Operator/infra |

---

## 8. Outstanding Technical Debt

- **Theme not in repo** → every fix is a hook/CSS workaround until a child theme exists.
  Child theme is the single highest-leverage debt payoff (unlocks card/single/search/
  Priority Zero permanently).
- **CSS file size** (~3,900 lines, sections A–Z) — consider a build step (split +
  minify) and de-duplication pass.
- **Version-catalog migration** (Pulse Clipper) and a `libs.versions.toml` for any
  future build tooling.
- **Pulse "trending"** uses a comment-count proxy — needs a real views/score metric.
- **Search** still excludes `pulse_item` and lacks content-type grouping.
- **Scattered docs** — many `oktv-*` strategy docs predate this consolidation; treat
  this roadmap as authoritative and archive the rest.
- **No automated tests** for the plugin (PHP). A minimal PHPUnit/wp-env harness would
  de-risk the growing shortcode surface.

---

## 9. Operator Critique — Top 25 (Phase 9, ranked by user impact)

External-consultant view: the 25 highest-impact moves toward an investor-ready,
polished creator platform. Ranked by expected **user impact** (1 = highest).

1. **Close Priority Zero on production** — editor/monetization controls visible to
   anonymous users is the single biggest credibility killer. Run the diagnostic; lock it.
2. **Ship the VidMov child theme** — converts a dozen CSS/hook workarounds into clean
   templates; unlocks single-video, cards, search, and the permanent Priority Zero fix.
3. **Make Pulse real end-to-end** — REST endpoints + Clipper M1 so creators actually
   publish. Pulse is the strategic bet; right now it's scaffolding.
4. **Embed the new homepage story** (`[oktv_platform_story]`) — 5-second "what is this"
   comprehension is the difference between bounce and explore.
5. **Activate SidebarChat** — community is the retention engine; the UI is reserved
   everywhere, the provider just needs flipping on.
6. **Google sign-in** — kill signup friction; a manual ARMember form loses casual users.
7. **Search redesign** — highest-intent users; group by Pulse/Signals/Arcana/Creators.
8. **Arcana single template** — the differentiated vertical still renders as a generic post.
9. **Staff weekly editorial curation** — Featured Pulse/Signals/Arcana only feel
   hand-made if a human curates. Tooling exists; the *taste* must be applied.
10. **Complete the top creator profiles** — avatar, bio, featured, verified badge —
    so Creators reads as a real network, not stubs.
11. **Creator verification flow** — turn the reserved badge into earned trust.
12. **Pulse destination live** — publish `/pulse/` with `[oktv_pulse_destination]`;
    make it a nav anchor.
13. **Footer + nav polish** — retire the 2023 copyright; lead nav with the five pillars.
14. **Recommended-Next on every video** — embed `[oktv_recommended_next]` to drive depth.
15. **Mobile pass** — verify the five-pillar nav, Pulse cards, and forms on small screens.
16. **Cloudflare "cache everything" for anonymous** — perceived speed = perceived quality.
17. **Pulse processing pipeline** — real thumbnails, normalize, status transitions.
18. **Real Pulse trending metric** — replace the comment-count proxy with views/score.
19. **Creator dashboard (lite)** — let creators edit bio/featured; ownership recruits creators.
20. **Duplicate auth-route cleanup** — `/login-2/` etc.; 301 + delete.
21. **Moderation/review state** for Pulse before public visibility.
22. **wpForo → OFFKILTER vocabulary** — rename categories; bridge toward SidebarChat.
23. **Image/CDN optimization** — Bunny for all media; lazy + sized everywhere.
24. **Analytics/observability** — time-to-first-Pulse, transformation-attach rate,
    return rate, TV completion (when the Clipper TV ships).
25. **Consolidate/retire legacy docs** — one authoritative roadmap (this), archive the rest.

**The through-line:** OFFKILTER's code is ~85% of the way to "modern creator platform."
The remaining distance is **operator execution** (Priority Zero, embeds, endpoints,
curation) and **two architectural unlocks** (child theme, real Pulse pipeline) — plus
the product *decisions* (monetization model, editorial staffing) that no sprint can make.

---

## 10. Score trajectory

| Sprint | Version | Avg | Lift |
|---|---|---|---|
| v2.0 | 2.0.0 | ~8.2 | Priority Zero (CSS) + a11y + review |
| v2.1 | 2.2.0 | ~8.5 | Pulse destination + data-layer lockdown |
| **v2.2 (this)** | **2.3.0** | **~8.7** | Pulse productization, premium destination, own visual identity, homepage story, consolidated roadmap |
| + operator (Priority Zero, endpoints, embeds, curation) | 2.3.0 | ~9.0 | Pulse live end-to-end |
| Target | — | ~9.3 | Child theme + SidebarChat + Google Identity + search |
