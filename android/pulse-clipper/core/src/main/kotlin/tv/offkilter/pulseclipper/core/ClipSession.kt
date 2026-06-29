package tv.offkilter.pulseclipper.core

/**
 * Aggregate that ties capture → edit → upload for a single clip.
 * Persisted (Room) so a draft survives process death at any stage
 * (see docs/android-clipper-architecture.md §9).
 */
data class ClipSession(
    val localId: String,                    // device-local id (unique work key)
    val sourceMediaPath: String?,           // captured draft .mp4 (app-private)
    val edit: EditGraph = EditGraph(),
    val item: PulseItem = PulseItem(),
    val stage: Stage = Stage.CAPTURED,
) {
    enum class Stage { CAPTURING, CAPTURED, EDITING, EXPORTING, EXPORTED, UPLOADING, DONE, FAILED }
}

/** Configurable capture length presets (spec §7.1). */
enum class ClipLength(val seconds: Int) {
    S15(15), S30(30), S60(60), S90(90);

    companion object {
        val DEFAULT = S30
    }
}

/** How the clip was captured. */
enum class CaptureMode {
    /** Retroactive — flush the ring buffer's last N seconds. */
    RETROACTIVE,
    /** Manual start/stop recording. */
    START_STOP,
}
