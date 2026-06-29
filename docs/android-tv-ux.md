# OFFKILTER Pulse Clipper — Android TV / Google TV UX

Date: 2026-06-29
Status: Draft v0.1
Optimized for: **Hisense Google TV** (landscape, 1080p & 4K)
Companion docs: [pulse-clipper-product-spec.md](pulse-clipper-product-spec.md) · [android-clipper-architecture.md](android-clipper-architecture.md)

---

## 1. The couch context

The defining constraint: **the user is on a couch, holding a remote, watching content.** There is no touchscreen, no precise pointer, and typing is painful. Every decision below follows from that.

Design tenets:
1. **One-button clipping.** The single most important action — capture — must be reachable from a dedicated/easy remote key without navigating any UI.
2. **D-pad first.** Every interaction is reachable and predictable with Up/Down/Left/Right + Select + Back. No gesture, no pointer dependency.
3. **Large focus targets.** Minimum focus target ~**160 dp** wide on TV; clear, high-contrast focus highlight.
4. **Minimal typing.** Avoid text entry; when unavoidable, prefer chips, presets, voice input, and "edit later on phone."
5. **Glanceable from 10 feet.** Type scale, spacing, and contrast tuned for the "10-foot UI."

---

## 2. One-button capture on TV

The creator is watching full-screen content. We cannot overlay arbitrary UI on top of another app on TV reliably, so the capture trigger is:

- **Primary:** a dedicated quick-clip path — the app registers as an **assistant/quick-action** target and listens for a configured trigger (e.g., long-press of a remote's assistant/custom key on supported remotes). Where the OEM allows a custom key mapping (Hisense), document the mapping in onboarding.
- **Fallback:** the app's own **always-available capture tile** on the Google TV home / app launcher, plus an in-app "Armed" mode: once the user starts a *watching session* inside our flow, a minimal, dismissible **focus-safe HUD** shows the Clip affordance and the ring-buffer state.
- **Confirmation:** pressing Clip gives immediate, unmistakable feedback (a brief full-bleed flash + "Clipped 30s" toast in a TV-safe position) so the couch user knows it worked without leaning in.

> MVP reality: reliable system-wide overlay over third-party full-screen apps is limited on TV. MVP ships the **Armed-session + capture tile** model; OEM custom-key mapping is a fast-follow where the platform permits.

---

## 3. D-pad navigation model

```
        [ UP ]
          │
[LEFT] ─ SELECT ─ [RIGHT]
          │
       [ DOWN ]            [ BACK ] = up one level / cancel
```

- **Focus order is explicit and linear** within each screen — no focus traps, no dead ends. Every screen has a single obvious default focus on entry.
- **Left/Right** scrubs the timeline and moves trim handles; **Up/Down** switches between controls (trim ↔ tools ↔ export).
- **Select** commits the focused action; **Back** always cancels/steps out predictably.
- **Long-press Select** on a trim handle = fine-scrub mode (frame step).
- Persistent **on-screen hint bar** maps the four D-pad directions to their current meaning (changes per screen), so the user never guesses.

---

## 4. TV editor (simplified)

The phone timeline is too fiddly for a remote. The TV editor is a **reduced, focus-driven** surface:

- **Trim:** a large timeline with two big handles. Left/Right moves the focused handle; switch handle with Up/Down or a Select toggle. Live preview frame above.
- **Quick tools** as a horizontal **focus rail** of large tiles: `Trim · Split · Rotate · Text · Blur · Done`. (Crop/arrow/highlight available but de-emphasized on TV; full fidelity lives on phone.)
- **Text overlay:** preset captions + on-screen keyboard *only if needed*; encourage **voice-to-text** and **"finish on phone."**
- **"Continue on phone"** hand-off: the draft (EditGraph + media) syncs to the creator's account so they can finish edits on the phone app. This is the pressure-release valve for anything fiddly.

---

## 5. Hisense Google TV optimization

| Aspect | Treatment |
|---|---|
| **Orientation** | Landscape-only on TV. |
| **Resolution** | Layouts validated at **1080p and 4K**; vector/Compose scales cleanly; raster assets provided at 4K density. |
| **TV-safe margins** | **5% overscan-safe padding** on all edges (≈ 48 dp at 1080p, scaled at 4K). No actionable element or critical text inside the unsafe border. |
| **Focus highlight** | High-contrast scale+glow on focus (respect reduced-motion → swap to a static high-contrast ring). |
| **Type scale** | 10-foot scale: body ≥ 18 sp equivalent, primary actions large; generous line height. |
| **Color/contrast** | OFFKILTER dark theme; ensure WCAG-AA contrast at distance; avoid thin strokes that vanish on some panels. |
| **Remote variance** | Don't assume a specific key beyond the standard D-pad + Select + Back + Play/Pause; treat assistant/custom keys as enhancement, not requirement. |
| **Performance** | Lean cold start; the capture path is pre-warmed on app focus because the user is reacting to live content. |

---

## 6. TV capture → publish flow (remote-only)

```
 1. Watching (in Armed session / app tile available)
 2. Press CLIP            → full-bleed flash + "Clipped 30s"
 3. App foregrounds editor with the draft, default focus on the trim timeline
 4. Left/Right trim, Select to confirm           (typing: none)
 5. (optional) Quick tool rail: add Text (voice-to-text) or Blur
 6. DOWN to EXPORT tile → Select
 7. Transformative-use reminder (Select to acknowledge, focusable, dismissible)
 8. "Upload to Pulse" → queued, background; HUD shows progress
 9. "Done" / "Continue on phone"
```

Target: complete steps 2–8 with **D-pad + Select only**, no text entry, in well under a minute.

---

## 7. Accessibility on TV

- Full **D-pad reachability** (no pointer-only controls).
- **TalkBack / accessibility** labels on every focusable; focus announcements match visual focus.
- **Reduced-motion** respected (static focus ring, no flashy transitions).
- High-contrast focus state; never rely on color alone to indicate focus.
- Large hit/focus targets (≥160 dp) ease motor and visual constraints.

---

## 8. Shared design system (`:design`)

Phone and TV share tokens and components, with TV-specific focus and sizing:
- Tokens mirror the web OFFKILTER system (dark surfaces, accent red, signal blue, arcana purple, community green) for brand continuity across web ↔ app.
- Components expose a `Surface` parameter (Phone | Tv) that adjusts target size, focus treatment, and density — one component, two presentations.
- TV-safe margin is a single layout primitive applied at the screen root.

---

## 9. Open TV questions (track in roadmap)

1. Feasibility/availability of a true system-wide one-press clip over third-party full-screen apps per OEM (Hisense first).
2. Compose-for-TV timeline ergonomics vs a custom focus timeline widget.
3. Voice-to-text reliability across Google TV remotes for captions.
4. Draft hand-off ("continue on phone") sync timing and account linking.
