# OFFKILTER Platform Brand Review — v3.2

**Date:** 2026-07-02
**Plugin:** v3.2.1 (live)
**Method:** real Playwright captures (desktop + mobile) of every public surface, before and after the identity work.

---

## Phase 1 — Platform Identity Audit (before)

The question for each page: *does this feel like OFFKILTER, or any WordPress video site?*

| Surface | Verdict (before) | Root cause |
|---|---|---|
| Homepage | Mixed — plugin hero good, but theme rails + zero-state noise below | Theme chrome |
| Discover | ✅ On-brand (plugin-owned) | — |
| Pulse | ✅ On-brand (plugin-owned) | — |
| **Signals** | ❌ Generic — raw theme archive | Nav pointed at `/video-category/signals/` |
| **Arcana** | ❌ Generic — raw theme archive, chronological, "0 Subscribers", "View Full Transcript" | Nav pointed at `/video-category/arcana/`; no branded landing |
| Creators | ⚠️ Plugin spotlights clean, but theme author cards noisy | Theme reaction/subscribe overlays |
| Watch | ✅ Plugin post-watch module strong | — |
| Search | ⚠️ Theme grid + zero-state noise | Theme |
| Footer | Neutral | — |
| Navigation | ⚠️ Text-only, generic; logo blurry | Theme menu + low-res logo |

**Conclusion:** the plugin's design system was already coherent — the "generic" feeling came almost entirely from (a) two nav items bypassing the plugin into raw theme archives, and (b) theme-chrome noise. Not a design-token problem.

---

## What shipped (v3.2.0 → v3.2.1)

- **Arcana flagship destination** (`/arcana/`): crystal-ball SVG hero, premium purple gradient headline + gentle hover glow, italic explainer, and curated collections (Arcana Spotlight, Premonitions, Outcomes, Readings, Arcana Readers). Routed via the `arcana_entry` archive template (same pattern as Pulse), so `/arcana/` is first-class. Nav "Arcana" → 🔮 and repointed.
- **Signals destination** (`/signals/`): blue hero + explainer + Breaking Signals + Arcana Signals. Nav "⚡ Signals" repointed.
- **Named editorial collections** (`[oktv_collection preset=…]`): breaking_signals, arcana_spotlight, featured_pulse, recently_discovered, editors_picks, creator_of_the_week.
- **Homepage story** reordered (Phase 4): creators-first ("publish here + keep their audience") → Pulse → Signals → Arcana → Community, with a values line beneath product clarity.
- **Nav identity:** emoji per destination — 🏠 Home, ▶️ Watch, ⚡ Signals, 🔮 Arcana, 🎬 Creators, 💬 Community, 🧭 Discover.
- **Logo cleanup:** the header/side/mobile logo was a lossy 251px JPEG/PNG with **empty retina keys** (the blur cause) and a red JPEG on mobile. Rebuilt a crisp 760px white transparent wordmark (chroma-keyed from the high-res source), imported as a real attachment, repointed all six dark + retina logo keys, and capped display size via CSS. Now razor-sharp on desktop, side menu, and mobile.
- **Theme-noise reduction (Phase 5):** scoped CSS removal of the "Spread the love" share row inside plugin destinations + zero-value reaction bubbles on plugin creator rows. Nav/counter theme-option toggles are the remaining operator items (below).
- **CSS Section AF + AF-7:** destination heroes, creators pillar variant, skeleton loading affordance, focus/polish, logo sizing.

Verification: P0 strings = 0 across home/arcana/signals/discover/pulse; all destinations render; crystal orb + emoji nav confirmed on live captures.

---

## Phase 8 — First-visitor walk (after)

Ten-minute anonymous browse, desktop + mobile. Findings:

**Moments of delight**
- The Arcana crystal-ball hero reads instantly as "this section is special."
- Crisp logo + emoji nav make the top of every page feel intentional.
- Discover/Pulse/Arcana now share one visual language — the site feels authored.

**Confusion / dead ends**
- The Termly cookie banner still dominates first paint (esp. mobile) and is the LCP element.
- `/privacy/` and `/terms/` still don't exist — the banner's own policy links dead-end.
- Premonitions/Outcomes sub-nav still points at raw theme taxonomy archives (not yet branded).
- The theme's right-rail "Arcana Highlights" widget still surfaces some mainstream clips.

**Missed opportunities**
- Pulse is still thin (8 seeded items) — the signature type needs the native upload pipeline.
- No "Continue Watching"/returning-user rail (designed, not built — `returning-user-design.md`).

---

## Top 25 remaining improvements (ranked by user impact)

1. Fix mobile performance — Termly resource-blocker is the LCP + top render-blocker (operator: Termly config).
2. Create `/privacy/` + `/terms/` pages (compliance + banner links).
3. Retarget/disable the theme "Arcana Highlights" widget (still shows mainstream clips).
4. Turn off theme reaction-score + subscribe-count displays globally (theme options).
5. Brand the Premonitions/Outcomes sub-destinations (route like Arcana).
6. Ship the Pulse native upload pipeline (tus) — real signature content.
7. Add skip-to-content link + `aria-live` on feeds (a11y).
8. BunnyCDN dashboard purge for asset caches.
9. Real banner art for the launch five (replace platform-default SVGs).
10. Returning-user rail (Continue Watching / Saved / Following).
11. Convert thumbnails to WebP (image weight).
12. Consolidate theme author cards to the plugin's clean spotlight everywhere.
13. Reduce homepage right-rail redundancy ("Arcana Highlights" vs Discover).
14. Verified badges once creators claim accounts.
15. Search redesign — group by pillar (Pulse/Signals/Arcana/Creators).
16. Font loading — self-host or preload the display font (render-blocking).
17. Trim unused theme JS on non-forum pages (wpForo CSS loads everywhere).
18. Homepage hero A/B — test a content-forward variant.
19. Creator "Featured playlist" once playlists exist.
20. Pulse trending signal — real engagement metric vs comment-count proxy.
21. Consistent empty states across every theme surface (not just plugin).
22. Dark/light logo parity (light-mode keys still old).
23. Deep-link Arcana sub-collections from the hero.
24. Add a footer that reflects the pillar system (currently generic).
25. Micro-interaction polish pass on theme buttons (outside plugin scope).

Items 1–5 are the highest-leverage; 1, 2, 4, and 8 are operator/theme-config, not plugin code.

---

## Launch-readiness note

Identity is the strongest it's been: the platform now reads as a curated creator network, not a video-embed site. The remaining gap to "green" is dominated by **operator/theme-config items** (performance, legal pages, theme counters) rather than plugin work.
