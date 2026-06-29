# OFFKILTER Pulse Clipper — Product Specification

Date: 2026-06-29
Status: Draft v0.1 (design + begin-building)
Platform: Android-first → Android TV / Google TV → Tablets
Companion docs: [android-clipper-architecture.md](android-clipper-architecture.md) · [android-tv-ux.md](android-tv-ux.md) · [pulse-upload-flow.md](pulse-upload-flow.md) · [pulse-clipper-roadmap.md](pulse-clipper-roadmap.md)

---

## 1. What this is

The Pulse Clipper is a **creator tool**, not a screen recorder. It lets a creator capture a short moment from whatever they're watching, transform it into a clip with commentary and context, and publish it to **OFFKILTER Pulse** in under a minute.

The distinction matters legally and product-wise: a screen recorder redistributes; the Pulse Clipper exists to enable **transformation** — commentary, criticism, education, reaction, parody. The tool's defaults, copy, and workflow all bias toward *adding something*, not copying.

> **Core loop:** Capture → Transform → Discuss → Discover.

---

## 2. Philosophy

| Principle | What it means in the product |
|---|---|
| **Transformation over redistribution** | The export step nudges toward commentary/text/voice. A bare clip with nothing added is allowed but never the path of least resistance. |
| **One-button capture** | From "that's interesting" to a saved clip should be a single deliberate action. Everything else is optional. |
| **Under a minute** | Capture → trim → annotate → upload, end to end, in <60s for the common case. |
| **Couch-to-phone parity** | The same mental model works with a remote on a TV and a thumb on a phone. |
| **Respect the platform** | Follow Android MediaProjection rules. Never bypass DRM or platform protections. Protected surfaces simply produce black frames — we don't fight that. |
| **Creator owns it** | Clips are the creator's transformative work. Metadata, attribution, and the future opt-out/ownership flow reinforce that. |

---

## 3. Target devices (MVP)

- **Phones:** Android 13+ and Android 14+ (primary).
- **TV:** Android TV and Google TV, optimized for **Hisense Google TV** (landscape, 1080p and 4K, TV-safe margins).
- **Tablets:** supported via responsive phone UI (not separately optimized in MVP).

Android 14 (API 34) is the compliance baseline for MediaProjection foreground-service rules (see architecture doc §3).

---

## 4. Personas

1. **The Reactor (phone).** Watching Reels/Shorts, wants to grab a moment and add a hot take with voice + text, post to Pulse immediately.
2. **The Critic (phone/tablet).** Watching a longer video, clips a 60–90s segment to analyze with on-screen arrows/highlights and a written note.
3. **The Couch Creator (TV).** Watching on a Hisense Google TV, sees something remarkable, presses one button on the remote, trims with the D-pad, and queues it — minimal typing, voice commentary optional.
4. **The Educator.** Clips for teaching; relies on text overlays, blur (to redact), and notes; values the transformative-use reminders.

---

## 5. Sources

The creator may be watching in:
- YouTube, Instagram Reels, Facebook Reels
- A web browser
- Local video
- Any other app where Android permits screen capture

The app does **not** integrate with these apps' APIs. It captures the **screen surface** the OS exposes via MediaProjection, subject to each app's protected-content flags. Source attribution is captured opportunistically (foreground package name, and a browser URL if the user pastes/shares it) — see Source Adapters in the architecture doc.

---

## 6. Clip workflow

```
            ┌──────────┐
   watching │  CLIP ▶  │  (one button: overlay bubble on phone, remote key on TV)
            └────┬─────┘
                 ▼
            ┌──────────┐   Retroactive (ring buffer: last N s) OR Start/Stop
            │ CAPTURE  │
            └────┬─────┘
                 ▼
   ┌──────┐  ┌──────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
   │ TRIM │→ │SPLIT │→ │ ANNOTATE │→ │  VOICE   │→ │   TEXT   │
   │      │  │CROP  │  │ arrows / │  │(optional)│  │(optional)│
   │      │  │ROT.  │  │ blur /hl │  │          │  │ overlay  │
   └──────┘  └──────┘  └──────────┘  └──────────┘  └──────────┘
                 ▼
            ┌──────────┐
            │  EXPORT  │  (transformative-use reminder shown here)
            └────┬─────┘
                 ▼
            ┌──────────────────────┐
            │ UPLOAD → OFFKILTER    │  background, resumable
            │ PULSE (Pulse Item)    │
            └──────────────────────┘
```

The trim/annotate/voice/text steps are all **skippable**. The minimum viable path is Capture → Export → Upload.

---

## 7. MVP capabilities

### 7.1 Capture
- **Retroactive capture** ("clip the last few seconds"): a continuously-running ring buffer holds the most recent N seconds; pressing Clip flushes it to a file. This is the signature feature — you clip *after* the moment happened.
- **Start/Stop capture:** manual recording for a deliberate segment.
- **Configurable durations:** 15 / 30 / 60 / 90 seconds. Default 30s.
- Capture audio (internal app audio where permitted by `AudioPlaybackCapture`, Android 10+) is **architected but gated** — see audio section.

### 7.2 Editor (MVP)
- Simple **timeline** with **trim handles**.
- **Split**, **crop**, **rotate**.
- **Text overlays** + **emoji**.
- **Arrow tool**, **highlight tool**, **blur tool** (blur doubles as a redaction tool).
- Single video track in MVP; overlay layer composited on top.

### 7.3 Export
- H.264/HEVC MP4, hardware-encoded.
- Resolution presets: 1080p (default), 720p (data-saver), source-match.
- **Transformative-use reminder** dialog (optional, dismissible, can be disabled in settings) — encourages commentary/criticism/education/parody and respecting copyright. **No filtering, no fair-use determination.**

### 7.4 Upload
- Background, resumable upload to OFFKILTER Pulse (see [pulse-upload-flow.md](pulse-upload-flow.md)).
- Each clip becomes a **Pulse Item** with metadata.

---

## 8. Future audio architecture (do NOT implement licensing)

Architected now, implemented later. The editing pipeline reserves a **multi-track audio mixer** stage so these slot in without rework:
- **Voice-over recording** (post-capture narration over the clip).
- **Microphone commentary** (live mic during capture).
- **External microphone support** (USB/Bluetooth audio input device selection).
- **Music library** (track selection + ducking) — **no licensing logic in MVP**; the slot exists, the catalog/licensing is future.
- **Multiple audio tracks** with per-track gain, mute, and a final mixdown.

See architecture doc §6 (Editing pipeline) for the `AudioGraph` reservation.

---

## 9. Pulse integration

Every exported clip becomes a **Pulse Item**. Canonical metadata schema (shared across all docs):

```jsonc
PulseItem {
  "id": "string",                 // server-assigned
  "creatorId": "string",          // OFFKILTER creator (Google Identity)
  "source": {
    "app": "string",              // foreground package, e.g. com.google.android.youtube
    "url": "string|null",         // if the creator supplied it (browser/share)
    "title": "string|null"        // best-effort source title
  },
  "title": "string",
  "tags": ["string"],
  "durationMs": 0,
  "capturedAt": "ISO-8601",       // device capture time
  "sourceTimestampMs": 0,          // optional position within source
  "notes": "string",              // creator commentary/context
  "transformationType": "commentary|criticism|education|parody|reaction|other",
  "mediaUrl": "string",           // set after upload
  "thumbnailUrl": "string",
  "discussionId": "string|null",  // FUTURE — SidebarChat thread
  "status": "draft|uploading|published|failed"
}
```

This maps to a future `pulse_item` entity on the OFFKILTER backend (a custom post type or dedicated table in the `offkilter-arcana` plugin, paralleling how `arcana_entry`/`vidmov_video` are handled today).

---

## 10. SidebarChat (reserved)

Every Pulse Item carries a nullable `discussionId`. When SidebarChat ships (see `docs/platform-v2-product-review.md` → readiness), each clip gains a **"Discuss in SidebarChat"** affordance that lazily creates/links a thread. **Not implemented now** — the field and UI slot are reserved only.

---

## 11. Fair-use awareness (not enforcement)

- The app **never** decides whether a clip is fair use.
- The app **never** filters or blocks content.
- The app **optionally reminds** users to make transformative content (commentary, criticism, education, parody) and to respect applicable copyright law.
- The reminder appears at Export, is dismissible, and remembers the user's preference.
- Capturing DRM-protected surfaces yields black frames by OS design; the app surfaces a friendly "this surface can't be captured" message rather than attempting any workaround.

---

## 12. Settings (MVP)

- Default clip length (15/30/60/90s).
- Ring-buffer on/off (retroactive capture) and its length.
- Export resolution preset.
- Capture quality / bitrate hint.
- Transformative-use reminder on/off.
- Account (Google Sign-In) + Pulse upload Wi-Fi-only toggle.

---

## 13. Success criteria

A creator watching something interesting can:
1. Press one button (overlay bubble on phone / a single remote key on TV).
2. Capture the last few seconds (ring buffer) or start/stop a short recording.
3. Trim it.
4. Add commentary or context (voice and/or text) — optional.
5. Upload it to OFFKILTER Pulse in **under one minute**.

The result should feel like a **native creator tool**, not a generic screen recorder — measured by: time-to-first-clip, % of clips with a transformation added, and couch (TV) completion rate with remote-only input.

---

## 14. Explicit non-goals (MVP)

- No DRM/protection bypass of any kind.
- No copyright filtering or fair-use determination.
- No music licensing.
- No live chat (SidebarChat reserved only).
- No iOS (Android-first; revisit post-launch).
- No multi-clip compilation/montage editor (Version 2 candidate).
