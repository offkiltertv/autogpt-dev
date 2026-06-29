package tv.offkilter.pulseclipper.core

/**
 * Canonical Pulse Item — the artifact every exported clip becomes.
 * Mirrors the schema in docs/pulse-clipper-product-spec.md §9 and the
 * Pulse API contract in docs/pulse-upload-flow.md §4.
 */
data class PulseItem(
    val id: String? = null,                 // server-assigned after create
    val creatorId: String? = null,          // OFFKILTER creator (Google Identity)
    val source: Source = Source(),
    val title: String = "",
    val tags: List<String> = emptyList(),
    val durationMs: Long = 0,
    val capturedAt: String = "",            // ISO-8601, device capture time
    val sourceTimestampMs: Long? = null,    // optional position within source
    val notes: String = "",                 // creator commentary / context
    val transformationType: TransformationType = TransformationType.OTHER,
    val mediaUrl: String? = null,           // set after upload
    val thumbnailUrl: String? = null,
    val discussionId: String? = null,       // FUTURE — SidebarChat thread
    val status: Status = Status.DRAFT,
) {
    /** Best-effort source attribution; never fabricated (see SourceAdapter). */
    data class Source(
        val app: String? = null,            // foreground package name
        val url: String? = null,            // only if creator supplied it
        val title: String? = null,
    )

    enum class TransformationType { COMMENTARY, CRITICISM, EDUCATION, PARODY, REACTION, OTHER }

    enum class Status { DRAFT, UPLOADING, PUBLISHED, FAILED }
}
