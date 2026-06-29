package tv.offkilter.pulseclipper.editor

import kotlinx.coroutines.flow.Flow
import tv.offkilter.pulseclipper.core.EditGraph

/**
 * Renders an [EditGraph] over a source clip into a hardware-encoded MP4
 * (docs/android-clipper-architecture.md §6). Backed by Media3 Transformer plus a
 * GL overlay effect chain (text/emoji/arrow/highlight/blur). The reserved
 * [EditGraph.audio] mixer is a no-op pass-through in MVP.
 */
interface ClipExporter {

    /**
     * Export [sourcePath] with [edit] applied to [outputPath].
     * Emits [Progress] updates; terminal emission is [Progress.Done] or [Progress.Failed].
     */
    fun export(
        sourcePath: String,
        edit: EditGraph,
        outputPath: String,
        options: ExportOptions = ExportOptions(),
    ): Flow<Progress>

    data class ExportOptions(
        val resolution: Resolution = Resolution.P1080,
        val codec: Codec = Codec.AUTO,
    ) {
        enum class Resolution { P720, P1080, SOURCE }
        /** AUTO prefers HEVC where supported with H.264 fallback (Pulse-compatible). */
        enum class Codec { AUTO, H264, HEVC }
    }

    sealed interface Progress {
        data class Running(val fraction: Float) : Progress
        data class Done(val outputPath: String, val durationMs: Long) : Progress
        data class Failed(val message: String) : Progress
    }
}

/**
 * Generates a poster thumbnail for the Pulse Item (client-side; see
 * docs/pulse-upload-flow.md §4 THUMB step).
 */
interface ThumbnailGenerator {
    suspend fun poster(sourcePath: String, atMs: Long): Result<String>
}
