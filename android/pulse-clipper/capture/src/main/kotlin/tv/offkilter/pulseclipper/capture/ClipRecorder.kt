package tv.offkilter.pulseclipper.capture

import kotlinx.coroutines.flow.StateFlow
import tv.offkilter.pulseclipper.core.CaptureMode
import tv.offkilter.pulseclipper.core.ClipLength

/**
 * Recording contract over the MediaProjection → VirtualDisplay → MediaCodec →
 * MediaMuxer pipeline (docs/android-clipper-architecture.md §4).
 *
 * Two capture modes:
 *  - RETROACTIVE: a continuously-running [RingBuffer] holds the last N seconds;
 *    [clip] flushes it to a draft file (the signature "clip what already happened").
 *  - START_STOP: [startManual]/[stopManual] record a deliberate segment.
 *
 * Implementations must NOT attempt to bypass DRM/protected surfaces. Protected
 * content yields black frames by OS design; surface that via [State.ProtectedSurface].
 */
interface ClipRecorder {

    val state: StateFlow<State>

    /** Begin a watching session: starts the FGS + encoder + ring buffer. */
    suspend fun startSession(length: ClipLength)

    /** Retroactive capture: flush the ring buffer to a draft file. Returns its path. */
    suspend fun clip(): Result<String>

    /** Manual recording. */
    suspend fun startManual()
    suspend fun stopManual(): Result<String>

    /** End the watching session: stops encoder + FGS, releases the projection. */
    suspend fun endSession()

    sealed interface State {
        data object Idle : State
        data class Armed(val mode: CaptureMode, val bufferedMs: Long) : State
        data object Recording : State
        data class Saved(val path: String) : State
        /** Captured surface is protected (black frames) — not an error to work around. */
        data object ProtectedSurface : State
        data class Error(val message: String) : State
    }
}

/**
 * GOP-aligned circular buffer of encoded samples for retroactive capture.
 * Capped by bytes (not just seconds) to bound memory; oldest GOP is dropped on
 * overflow (docs/android-clipper-architecture.md §8 memory budget).
 */
interface RingBuffer {
    val capacityBytes: Long
    val bufferedDurationMs: Long

    /** Append an encoded sample; drops oldest GOP(s) if over capacity. */
    fun append(sample: EncodedSample)

    /** Flush from the nearest preceding keyframe so the output is decodable. */
    fun flushFromKeyframe(): List<EncodedSample>

    fun clear()

    data class EncodedSample(
        val data: ByteArray,
        val presentationTimeUs: Long,
        val isKeyframe: Boolean,
    )
}
