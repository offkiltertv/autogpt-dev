# OFFKILTER Editorial Experience Roadmap

**Date:** 2026-07-02
**Status:** the collection system + per-destination identity below are live in plugin v3.2.1.

---

## The editorial collection system

Named, curated collections replace chronological feeds. One shortcode, preset-driven:

`[oktv_collection preset="…" ids="…" limit="…" title="…"]`

| Preset | Renders | Source |
|---|---|---|
| `breaking_signals` | Latest signal-class clips | Signals renderer |
| `arcana_spotlight` | Hand-picked or latest Arcana | curated_section / arcana signals |
| `featured_pulse` | Editorial Pulse | Pulse feed |
| `recently_discovered` | Fresh across Arcana + Signals | curated_section |
| `editors_picks` | Hand-picked cross-type | curated_section |
| `creator_of_the_week` | Featured creator callout | featured_creator |

Each maps to an existing renderer and inherits pillar styling (accent bar, branded header). New named collections are added by extending the `render_collection` switch — no new rendering code.

## Per-destination identity spec (current)

| Destination | Icon | Accent | Hero | Empty state | Nav |
|---|---|---|---|---|---|
| Pulse | wave-square | teal `--ok-pulse` | dot lockup | ✅ | (Discover/Pulse) |
| Signals | bolt | blue `--ok-signal` | ✅ blue hero | ✅ | ⚡ Signals → /signals/ |
| Arcana | crystal-ball SVG | purple `--ok-arcana` | ✅ orb + gradient | ✅ `--arcana` | 🔮 Arcana → /arcana/ |
| Creators | user-group | red `--ok-accent` | spotlight rows | ✅ `--creators` | 🎬 Creators |
| Community | comments | green `--ok-community` | discuss CTAs | (wpForo) | 💬 Community |

Coherence rule: every destination = icon + accent + branded section headers + branded empty state, one token system (`--ok-*`), reduced-motion guarded.

## Loading states

A skeleton affordance (`.ok-feed-skeleton`, Section AF-4) exists as a reusable placeholder. **Honest status:** server-rendered feeds have no async "loading" moment, so skeletons are cosmetic today. They become meaningful when feeds load asynchronously — which arrives with the **Pulse tus upload pipeline** and any future infinite-scroll/AJAX feed. Wire skeletons into those when they ship.

## What's next (sequenced)

1. **Brand the Arcana sub-destinations** — Premonitions / Outcomes still route to raw theme taxonomy archives. Add archive routing (same template pattern) or dedicated `[oktv_collection]` landings.
2. **Pulse native pipeline (tus)** — turns Pulse from 8 seeded embeds into the real signature type; unlocks live loading states.
3. **Returning-user rails** — Continue Watching / Saved / Following (design: `returning-user-design.md`). Backend work required.
4. **Search redesign** — group results by pillar instead of one chronological grid.
5. **Theme-config handoff** — reaction/subscribe counters, the "Arcana Highlights" widget, and the cookie banner are theme/operator settings (see `platform-brand-review-v3_2.md` Top 25 #1–4). Editorial identity can't fully land until those are tuned.
6. **Editorial calendar** — rotate `creator_of_the_week` + `editors_picks` on a schedule (currently manual/auto-latest).

## Guardrails

- Collections ride existing renderers — no parallel rendering paths.
- Pillar styling is data-driven via the `pillar` attribute; never hardcode per-section CSS.
- Theme-chrome suppression stays theme-settings-first, scoped-CSS fallback, documented per item.
- `beeteam368_membership_plans` and the Priority-Zero data-layer guards are never touched by identity work.
