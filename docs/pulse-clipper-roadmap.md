# OFFKILTER Pulse Clipper — Implementation Roadmap

Date: 2026-06-29
Status: Draft v0.1
Companion docs: [pulse-clipper-product-spec.md](pulse-clipper-product-spec.md) · [android-clipper-architecture.md](android-clipper-architecture.md) · [android-tv-ux.md](android-tv-ux.md) · [pulse-upload-flow.md](pulse-upload-flow.md)

---

## Milestone overview

| Milestone | Theme | Exit criterion |
|---|---|---|
| **M0 — Scaffold** | Project skeleton & contracts | Modules build; interfaces defined (this commit). |
| **M1 — MVP** | One-button clip → trim → upload (phone) | A creator clips, trims, and uploads a Pulse Item in <1 min on a phone. |
| **M2 — Beta** | Editor depth + TV + reliability | TV remote-only flow works on Hisense; full editor; resilient resumable upload; closed beta. |
| **M3 — Public Launch** | Polish, scale, store-ready | Play Store launch (phone + TV), Pulse feed live on OFFKILTER.tv, observability. |
| **M4 — Version 2** | Audio, SidebarChat, richer sources | Voiceover/mic/music architecture realized; SidebarChat; advanced source adapters; montage. |

---

## M0 — Scaffold (now)

- Gradle multi-module skeleton under `android/pulse-clipper/` (package `tv.offkilter.pulseclipper`).
- `:core` domain models: `PulseItem`, `ClipSession`, `EditGraph` (data classes / contracts).
- `:capture` interfaces: `ClipRecorder`, `RingBuffer`, capture service shell + manifest declarations.
- `:sources` SPI: `SourceAdapter`, `CaptureContext`, `SourceMetadata` + `GenericScreenAdapter` stub.
- `:editor` contracts: `EditGraph` ops, `ClipExporter` interface, `AudioGraph` (reserved) types.
- `:upload` contracts: `PulseUploader`, queue model, WorkManager worker shell.
- Manifest with Android 14 MediaProjection foreground-service declarations + permissions.
- README with build instructions (developer adds Gradle wrapper).

**Deliverable:** compiles to a buildable skeleton once a Gradle wrapper is generated; contracts match the architecture doc.

---

## M1 — MVP (phone)

**Capture**
- MediaProjection consent + foreground service (Android 14 compliant).
- Hardware-encoded recording pipeline (MediaCodec → MediaMuxer).
- **Retroactive ring buffer** (last N s) + Start/Stop; durations 15/30/60/90 s.
- Black-frame (protected content) detection + friendly messaging.
- On-screen overlay bubble (phone) for one-tap Clip.

**Editor (core)**
- ExoPlayer preview; timeline with trim handles; split; rotate; crop.
- Text overlay + emoji; arrow; highlight; **blur**.
- EditGraph model; Media3 Transformer export (HW-encoded MP4).

**Upload**
- Google Sign-In → OFFKILTER token.
- Create → resumable upload → finalize; Room queue + WorkManager; Wi-Fi-only default.
- Pulse Item created on backend; Uploads screen with progress/retry.

**Compliance/UX**
- Transformative-use reminder at export (toggleable).
- Settings (defaults, buffer, resolution, account).

**Backend (parallel, in `offkilter-arcana`)**
- `pulse_item` entity + REST endpoints (create/upload/finalize/read).

**Exit:** end-to-end <1-minute clip→Pulse on Pixel (A13 + A14). Internal dogfood.

---

## M2 — Beta

**TV (Hisense Google TV)**
- `:app-tv` Compose-for-TV surface; D-pad nav; large focus targets; TV-safe margins.
- Armed-session + capture-tile model; one-press clip; full-bleed confirmation.
- Simplified TV editor (big trim timeline, quick-tool rail, voice-to-text captions).
- "Continue on phone" draft hand-off.

**Editor depth**
- Frame-step fine trim; overlay editing polish; redaction-blur UX; poster-frame thumbnail picker.

**Reliability & scale**
- Resumable-upload hardening (offset negotiation, integrity checks).
- Thermal/battery adaptation (degrade bitrate under throttling; pause on screen-off).
- Crash/ANR budget; device matrix QA.

**Source adapters**
- `ShareUrlAdapter` (user-supplied URL) productionized; adapter registry stable.

**Exit:** closed beta on phone + Hisense TV; remote-only TV completion measured; resumable upload survives induced failures.

---

## M3 — Public Launch

- Play Store listings (phone + Android TV), store policy review (MediaProjection disclosure, no-DRM-bypass statement).
- Onboarding (incl. TV remote key mapping guidance) and first-run permission education.
- **Pulse feed live on OFFKILTER.tv** (`[oktv_pulse_feed]` or curated section consuming `pulse_item`).
- Observability: capture-success rate, time-to-first-clip, transformation-attach rate, upload success, TV completion rate.
- Performance budgets enforced; accessibility audit (phone + TV) passed.
- Moderation/review state for Pulse Items before public visibility.

**Exit:** GA on Play Store; Pulse content flowing to web; dashboards green.

---

## M4 — Version 2

- **Audio (architecture realized):** voiceover recording, live mic commentary, external mic selection, music library slot (still **no licensing logic**), multi-track mixer + mixdown.
- **SidebarChat:** `discussionId` activated; "Discuss in SidebarChat" on every Pulse clip (web + app).
- **Richer source adapters:** YouTube/Reels/browser metadata enrichment (privacy-reviewed, no private-API use).
- **Montage/compilation editor:** multi-clip sequencing.
- **Tablets:** dedicated layout pass.
- **Pulse intelligence:** trend/recommendation scoring feeding Discover (ties to "Pulse integration readiness").

---

## Cross-cutting workstreams

| Workstream | Spans |
|---|---|
| Compliance (MediaProjection, no-DRM-bypass, store policy) | M0→M3 |
| Accessibility (D-pad, TalkBack, contrast, reduced-motion) | M1→M3 |
| Performance/battery/thermal | M1→M4 |
| Backend Pulse API + web feed | M1→M3 |
| Privacy (source metadata, no silent telemetry) | M0→M4 |

---

## Risks & mitigations

| Risk | Mitigation |
|---|---|
| System-wide one-press clip over 3rd-party full-screen apps limited on TV | MVP uses Armed-session + tile; OEM custom-key mapping as fast-follow; "continue on phone" valve. |
| Internal-audio capture blocked by source apps | Default to no internal audio; lean on voiceover/mic (V2); never bypass. |
| DRM/protected surfaces → black frames | Detect + message; **never** attempt bypass (legal + policy). |
| Battery cost of continuous encode | Encode only during active watching sessions; pause on screen-off; HEVC; thermal degradation. |
| Resumable protocol/backend uncertainty | Define API contract early (upload-flow doc §4); spike tus vs custom in M1. |
| Compose-for-TV editor ergonomics | Simplified TV editor + "continue on phone"; custom focus-timeline if needed. |

---

## Definition of done (per milestone)

A milestone is done when its exit criterion is met **and**: builds are green, the device matrix passes, accessibility checks pass for any new surface, no DRM/protection bypass exists anywhere, and the relevant docs in this set are updated to match shipped reality.
