# OFFKILTER Pulse Clipper — Android Architecture

Date: 2026-06-29
Status: Draft v0.1
Companion docs: [pulse-clipper-product-spec.md](pulse-clipper-product-spec.md) · [android-tv-ux.md](android-tv-ux.md) · [pulse-upload-flow.md](pulse-upload-flow.md) · [pulse-clipper-roadmap.md](pulse-clipper-roadmap.md)

---

## 1. Stack decisions

| Concern | Choice | Why |
|---|---|---|
| Language | **Kotlin**, Coroutines + Flow | Standard for modern Android; structured concurrency suits the recording/upload pipelines. |
| Min/Target SDK | min **API 33** (Android 13), target **API 34** (Android 14) | API 34 is required for the MediaProjection foreground-service rules; 33 covers the install base. |
| UI (phone) | **Jetpack Compose** | Single toolkit, shared design system with TV. |
| UI (TV) | **Compose for TV** (Leanback interop where needed) | D-pad focus, large targets, shared components with phone. |
| Capture | **MediaProjection** → VirtualDisplay → Surface → **MediaCodec** → **MediaMuxer** | Lowest-latency, hardware-encoded path; full control of the ring buffer. |
| Editing | **Media3 Transformer** + custom **OpenGL ES** overlay shaders | Transformer for trim/transcode; GL for text/emoji/arrow/highlight/blur compositing. Preview via **Media3 ExoPlayer**. |
| Upload | **WorkManager** + **OkHttp** (chunked/resumable) | Survives process death, network changes, and Doze. |
| Auth | **Google Sign-In / Credential Manager** | YouTube/Gmail identity; ties to OFFKILTER "Google Identity readiness". |
| DI | **Hilt** | Conventional, testable. |
| Persistence | **Room** (clip drafts, upload queue) + **DataStore** (settings) | Drafts and queue must survive restarts. |

We deliberately use the lower-level **MediaCodec/MediaMuxer** path for *capture* (to own the ring buffer and minimize latency) and the higher-level **Media3 Transformer** for *editing/export* (where developer velocity matters more than microsecond latency).

---

## 2. Module graph

Gradle multi-module, package root `tv.offkilter.pulseclipper`.

```
              ┌─────────────┐        ┌─────────────┐
              │    :app     │        │   :app-tv   │   UI surfaces
              │  (phone)    │        │ (Google TV) │   (Compose / Compose-for-TV)
              └──────┬──────┘        └──────┬──────┘
                     │                      │
                     └──────────┬───────────┘
                                ▼
                         ┌─────────────┐
                         │   :design   │   design system, focus, TV-safe margins
                         └──────┬──────┘
                                ▼
                         ┌─────────────┐
                         │    :core    │   domain models (PulseItem, ClipSession),
                         │             │   use cases, result types
                         └──┬───┬───┬──┘
              ┌─────────────┘   │   └─────────────┐
              ▼                 ▼                 ▼
       ┌────────────┐   ┌────────────┐    ┌────────────┐
       │  :capture  │   │  :editor   │    │  :upload   │
       │ projection │   │ transformer│    │ workmanager│
       │ ring buffer│   │ GL overlays│    │ resumable  │
       │ fg service │   │ (+:audio*) │    │ api client │
       └─────┬──────┘   └─────┬──────┘    └─────┬──────┘
             │                │                 │
             ▼                ▼                 ▼
       ┌────────────┐   ┌────────────┐    ┌────────────┐
       │  :sources  │   │  :audio*   │    │  :data     │
       │ adapter SPI│   │ (future)   │    │ room/store │
       │ + builtins │   │ multi-track│    │ repos      │
       └────────────┘   └────────────┘    └────────────┘
       * :audio is reserved/stubbed in MVP
```

Dependency rule: UI → :core → feature modules → :data. Feature modules never depend on UI. `:core` has no Android-framework-heavy deps where avoidable (keeps domain testable).

---

## 3. Android 14 MediaProjection compliance

Non-negotiable requirements baked into `:capture`:

1. **Foreground service type `mediaProjection`.** The capture service declares `android:foregroundServiceType="mediaProjection"` and is started as a foreground service *before* `MediaProjection` is used.
2. **User consent per session.** `MediaProjectionManager.createScreenCaptureIntent()` consent is requested each capture session (Android 14 invalidates reuse). We request it at the moment of the first Clip, then keep the session alive for the watching session via the foreground service.
3. **`MediaProjection.Callback` is mandatory** (Android 14) — we register it and stop cleanly on `onStop()`.
4. **Persistent notification** with a Stop action while capturing.
5. **No silent/background start.** Capture only begins from an explicit user gesture (overlay tap / remote key).
6. **Permissions:** `FOREGROUND_SERVICE`, `FOREGROUND_SERVICE_MEDIA_PROJECTION`, `POST_NOTIFICATIONS` (Android 13+), `RECORD_AUDIO` (only if mic/internal-audio capture enabled), `INTERNET`.

DRM-protected surfaces produce black frames by OS design — detected heuristically (all-black sampled frames) and surfaced as a friendly message. **No bypass attempted.**

---

## 4. Recording pipeline

```
 MediaProjection ──> VirtualDisplay ──> Surface
                                          │
                                          ▼
                                    MediaCodec (H.264/HEVC, surface input)
                                          │  encoded buffers
                                          ▼
                              ┌────────────────────────┐
                              │   RingBuffer (N sec)    │  retroactive capture
                              │  circular, GOP-aligned  │
                              └───────────┬────────────┘
                                          │ on CLIP press: flush
                                          ▼
                                    MediaMuxer ──> draft .mp4 (app-private)
```

### Retroactive ("last N seconds") design
- The encoder runs continuously while the user is in a watching session.
- Encoded samples are written into a **GOP-aligned circular buffer** sized for N seconds at the target bitrate (memory budget below).
- On **Clip**, the buffer is flushed starting at the **nearest preceding keyframe** so the MP4 is decodable, then muxed to a draft file.
- Force-keyframe cadence (e.g., every 1s) keeps the trim-in point tight.

### Start/Stop design
- Same encoder path, but samples write straight to the muxer between Start and Stop (no ring buffer), capped at the configured max duration.

### Audio (reserved)
- Optional `AudioPlaybackCapture` (internal app audio, Android 10+, subject to the source app's capture policy) and/or mic via `AudioRecord`, encoded with a second MediaCodec (AAC) and interleaved by the muxer. Gated behind a setting; off by default in MVP.

---

## 5. Source adapters (plugin architecture)

The brief calls for "plugin architecture for future source adapters." `:sources` defines a **Service Provider Interface**:

```kotlin
interface SourceAdapter {
    /** Stable id, e.g. "youtube", "browser", "generic". */
    val id: String
    /** True if this adapter recognizes the current foreground context. */
    fun matches(context: CaptureContext): Boolean
    /** Best-effort metadata for the Pulse Item (title, url, source timestamp). */
    suspend fun resolve(context: CaptureContext): SourceMetadata
}
```

- `CaptureContext` carries foreground package name, accessibility-derived hints (opt-in), and any user-supplied share URL.
- Adapters are discovered via a registry (ServiceLoader-style or Hilt multibinding) so new ones (YouTube, browser, Reels) drop in without touching capture/editor code.
- **MVP ships:** `GenericScreenAdapter` (package name only) and `ShareUrlAdapter` (URL the user pastes/shares in). Richer adapters are post-MVP.
- Adapters **never** access another app's private API or content — they only enrich metadata from what the OS/user already exposes.

---

## 6. Editing pipeline

```
 draft .mp4 ──> ExoPlayer (preview) ──┐
                                       │  user edits (timeline ops + overlays)
                                       ▼
                          EditGraph (immutable description)
                            • trims[], splits[], crop, rotate
                            • overlays[] (text/emoji/arrow/highlight/blur)
                            • audioGraph (RESERVED: tracks[], gains, music, voiceover)
                                       │  on Export
                                       ▼
                   Media3 Transformer  +  GL overlay effects
                                       │
                                       ▼
                              exported .mp4 (HW-encoded)
```

- **EditGraph** is a serializable, immutable edit description → drafts are resumable and the export is reproducible.
- **Overlays** render as a Media3 `Effect`/GL shader chain composited over frames: text/emoji (texture atlas), arrows/highlights (vector → texture), **blur** (separable Gaussian / downsample, also used for redaction).
- **AudioGraph** is present in the model but the mixer is a no-op pass-through in MVP — this is the reservation for voiceover/mic/external-mic/music/multi-track (spec §8). Adding real audio = implementing the mixer stage, not reshaping the pipeline.
- Preview uses ExoPlayer with the same effect chain so WYSIWYG holds.

---

## 7. Upload pipeline (summary; full detail in pulse-upload-flow.md)

```
 exported .mp4 + PulseItem(draft)
        │  enqueue
        ▼
 Room upload queue ──> WorkManager (constraints: network, optional Wi-Fi-only)
        │
        ▼
 Resumable upload (chunked / tus-style) ──> OFFKILTER Pulse API
        │  on success
        ▼
 PulseItem.status = published, mediaUrl/thumbnailUrl set
```

Survives process death and connectivity loss; retries with backoff; user sees a queue with per-item progress.

---

## 8. Performance, battery, memory, latency

| Vector | Target / approach |
|---|---|
| **Latency (Clip→saved draft)** | < 500 ms for retroactive (buffer already in memory; only mux + keyframe-align). Start/Stop bounded by user. |
| **Encode** | Hardware encoder only (MediaCodec surface input). Never software-encode in the hot path. |
| **Memory (ring buffer)** | Budget = bitrate × N. e.g. 1080p @ ~10 Mbps × 90 s ≈ ~112 MB worst case; default 30 s ≈ ~37 MB. Cap buffer by bytes, not just seconds; drop oldest GOP on overflow. Offer a 720p/lower-bitrate "long buffer" mode. |
| **Battery** | Continuous encode is the cost. Mitigations: only run the encoder while a watching session is active (not 24/7), pause on screen-off, prefer HEVC where supported, throttle to source frame rate. |
| **Thermals** | Watch for `PowerManager` thermal status; degrade bitrate/resolution under throttling. |
| **Editor memory** | Stream frames via Transformer; never decode the whole clip into memory. Overlay textures pooled/recycled. |
| **Upload** | Chunked so memory is O(chunk); Wi-Fi-only default to protect data; WorkManager respects Doze/standby. |
| **Cold start (TV)** | Keep `:app-tv` lean; defer heavy init; the Clip path must be ready fast since the user is reacting to live content. |

---

## 9. Threading & lifecycle

- Capture service owns a dedicated encoder thread + the projection callback; communicates state via a `StateFlow`.
- Editor runs Transformer on its own dispatcher; UI observes progress via Flow.
- Upload is entirely in WorkManager workers (out-of-process resilience).
- A single **ClipSession** aggregate in `:core` ties capture → edit → upload, persisted in Room so a draft survives app death at any stage.

---

## 10. Testing strategy

- **:core** pure-Kotlin unit tests (EditGraph operations, PulseItem mapping, ring-buffer math).
- **:capture** instrumented tests on a device matrix (Pixel A13/A14, Hisense Google TV) — projection consent, ring-buffer flush correctness, black-frame detection.
- **:editor** golden-frame tests for overlay shaders; export integrity (duration/keyframes).
- **:upload** WorkManager tests with simulated network failures + resume.
- TV: D-pad focus-traversal tests (espresso/leanback).

---

## 11. Open technical questions (track in roadmap)

1. HEVC vs H.264 default per-device (capability + Pulse playback compatibility).
2. Internal-audio capture policy variance across source apps (`AudioPlaybackCapture` allow/deny).
3. Exact resumable protocol on the OFFKILTER side (tus vs custom chunked) — see upload-flow doc.
4. Accessibility-hint sourcing for adapters (privacy review required before enabling).
5. Compose-for-TV maturity for the timeline editor on TV vs a simplified TV edit surface.
