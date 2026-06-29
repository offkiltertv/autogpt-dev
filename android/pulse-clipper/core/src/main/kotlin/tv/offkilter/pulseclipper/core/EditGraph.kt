package tv.offkilter.pulseclipper.core

/**
 * Immutable, serializable description of all edits applied to a clip.
 * The exporter (see :editor ClipExporter) renders this deterministically,
 * which makes drafts resumable and exports reproducible
 * (docs/android-clipper-architecture.md §6).
 */
data class EditGraph(
    val trimStartMs: Long = 0,
    val trimEndMs: Long? = null,            // null = end of media
    val splits: List<Long> = emptyList(),   // split points (ms)
    val rotationDegrees: Int = 0,           // 0/90/180/270
    val crop: Crop? = null,
    val overlays: List<Overlay> = emptyList(),
    /** RESERVED for V2 — multi-track audio. No-op pass-through in MVP. */
    val audio: AudioGraph = AudioGraph(),
) {
    data class Crop(val left: Float, val top: Float, val right: Float, val bottom: Float) // normalized 0..1

    sealed interface Overlay {
        val startMs: Long
        val endMs: Long

        data class Text(
            override val startMs: Long, override val endMs: Long,
            val text: String, val x: Float, val y: Float, val sizeSp: Float, val colorArgb: Int,
        ) : Overlay

        data class Emoji(
            override val startMs: Long, override val endMs: Long,
            val emoji: String, val x: Float, val y: Float, val sizeSp: Float,
        ) : Overlay

        data class Arrow(
            override val startMs: Long, override val endMs: Long,
            val fromX: Float, val fromY: Float, val toX: Float, val toY: Float, val colorArgb: Int,
        ) : Overlay

        data class Highlight(
            override val startMs: Long, override val endMs: Long,
            val x: Float, val y: Float, val radius: Float, val colorArgb: Int,
        ) : Overlay

        /** Doubles as a redaction tool. */
        data class Blur(
            override val startMs: Long, override val endMs: Long,
            val x: Float, val y: Float, val width: Float, val height: Float, val strength: Float,
        ) : Overlay
    }
}

/**
 * RESERVED — future multi-track audio (spec §8). Present so the editing
 * pipeline carries it without rework; the mixer is a no-op in MVP.
 */
data class AudioGraph(
    val tracks: List<Track> = emptyList(),
) {
    data class Track(
        val kind: Kind,
        val sourcePath: String? = null,     // voiceover/music file
        val gain: Float = 1.0f,
        val muted: Boolean = false,
    )

    /** No licensing logic anywhere — MUSIC is a structural slot only. */
    enum class Kind { ORIGINAL, VOICEOVER, MIC_COMMENTARY, EXTERNAL_MIC, MUSIC }
}
