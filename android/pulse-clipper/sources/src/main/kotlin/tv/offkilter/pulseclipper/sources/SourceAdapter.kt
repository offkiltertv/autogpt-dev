package tv.offkilter.pulseclipper.sources

import tv.offkilter.pulseclipper.core.PulseItem

/**
 * Plugin SPI for source attribution (docs/android-clipper-architecture.md §5).
 *
 * Adapters enrich a clip's [PulseItem.Source] metadata from what the OS and the
 * user already expose — foreground package, an opt-in accessibility hint, or a
 * URL the user shared. New adapters (YouTube, Reels, browser) drop in via the
 * [SourceAdapterRegistry] without touching the capture or editor pipelines.
 *
 * Adapters MUST NOT access another app's private API or content. Attribution is
 * best-effort and never fabricated.
 */
interface SourceAdapter {

    /** Stable id, e.g. "generic", "browser", "youtube". */
    val id: String

    /** True if this adapter recognizes the current capture context. */
    fun matches(context: CaptureContext): Boolean

    /** Best-effort metadata for the Pulse Item. */
    suspend fun resolve(context: CaptureContext): SourceMetadata
}

/** What we know at capture time. All fields optional / opt-in. */
data class CaptureContext(
    val foregroundPackage: String? = null,
    /** Opt-in accessibility-derived hint (privacy-reviewed before enabling). */
    val accessibilityHint: String? = null,
    /** URL the user pasted or shared into the app. */
    val userSuppliedUrl: String? = null,
)

data class SourceMetadata(
    val app: String? = null,
    val url: String? = null,
    val title: String? = null,
    val sourceTimestampMs: Long? = null,
) {
    fun toSource(): PulseItem.Source = PulseItem.Source(app = app, url = url, title = title)
}

/**
 * Registry of available adapters. Implementation may use Hilt multibinding or a
 * ServiceLoader-style discovery; resolution picks the first [SourceAdapter.matches].
 */
interface SourceAdapterRegistry {
    fun adapters(): List<SourceAdapter>

    suspend fun resolve(context: CaptureContext): SourceMetadata {
        val adapter = adapters().firstOrNull { it.matches(context) }
        return adapter?.resolve(context) ?: SourceMetadata(app = context.foregroundPackage)
    }
}

/**
 * MVP built-in: package-name-only attribution. Always matches (lowest priority —
 * place last in the registry so richer adapters win).
 */
class GenericScreenAdapter : SourceAdapter {
    override val id: String = "generic"
    override fun matches(context: CaptureContext): Boolean = true
    override suspend fun resolve(context: CaptureContext): SourceMetadata =
        SourceMetadata(
            app = context.foregroundPackage,
            url = context.userSuppliedUrl,
        )
}
