# OFFKILTER Pulse Clipper — Android (skeleton)

Native Android creator tool: capture a short moment you're watching, transform it
(trim / annotate / commentary), and publish it to **OFFKILTER Pulse**.

> **Status: M0 scaffold.** This is a contracts-and-structure skeleton, not a runnable
> app yet. It defines the module graph and the key interfaces from the design docs so
> implementation (M1 MVP) can begin against stable contracts.

## Design docs
- [`docs/pulse-clipper-product-spec.md`](../../docs/pulse-clipper-product-spec.md)
- [`docs/android-clipper-architecture.md`](../../docs/android-clipper-architecture.md)
- [`docs/android-tv-ux.md`](../../docs/android-tv-ux.md)
- [`docs/pulse-upload-flow.md`](../../docs/pulse-upload-flow.md)
- [`docs/pulse-clipper-roadmap.md`](../../docs/pulse-clipper-roadmap.md)

## Modules
```
:core      domain models — PulseItem, ClipSession, EditGraph (pure Kotlin)
:capture   MediaProjection + ring buffer + foreground service (recording)
:sources   SourceAdapter plugin SPI + built-in adapters
:editor    EditGraph ops, ClipExporter, reserved AudioGraph
:upload    PulseUploader, queue model, WorkManager worker (resumable)
:app       phone UI (Compose)          — added in M1
:app-tv    Google TV UI (Compose-for-TV) — added in M2
:design    shared design system         — added in M1
```
Dependency rule: UI → `:core` → feature modules. Feature modules never depend on UI.

## Build
This skeleton has no Gradle wrapper committed (the `gradle-wrapper.jar` is binary).
To make it buildable:
```bash
cd android/pulse-clipper
gradle wrapper --gradle-version 8.9   # generates ./gradlew + wrapper files
./gradlew :core:build                 # core is pure-Kotlin and builds first
```
Open the `android/pulse-clipper` folder in Android Studio (Koala+). Versions:
AGP 8.6, Kotlin 2.0.x, compileSdk 34, minSdk 33 (Android 13), targetSdk 34 (Android 14).

## Compliance
- Follows Android 14 MediaProjection foreground-service rules (see `:capture` manifest).
- **No DRM / platform-protection bypass.** Protected surfaces yield black frames by OS
  design and are surfaced as a friendly message — never worked around.
- Fair-use **awareness** only (optional transformative-content reminder). No filtering.
