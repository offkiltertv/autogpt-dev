package tv.offkilter.pulseclipper.upload

import kotlinx.coroutines.flow.Flow
import tv.offkilter.pulseclipper.core.PulseItem

/**
 * Uploads a clip + metadata to OFFKILTER Pulse (docs/pulse-upload-flow.md).
 * The concrete implementation runs inside a WorkManager worker keyed by the
 * clip's local id (unique work), so an upload survives process death and
 * resumes from the confirmed byte offset after failures.
 *
 * Flow: AUTH → CREATE → resumable UPLOAD → THUMB → FINALIZE.
 */
interface PulseUploader {

    /** Enqueue a clip for background upload. Returns the queue entry id. */
    suspend fun enqueue(request: UploadRequest): String

    /** Observe progress for a queued clip. */
    fun progress(queueId: String): Flow<UploadState>

    suspend fun retry(queueId: String)
    suspend fun cancel(queueId: String)

    data class UploadRequest(
        val localId: String,
        val mediaPath: String,
        val thumbnailPath: String?,
        val item: PulseItem,
        val wifiOnly: Boolean = true,
    )

    sealed interface UploadState {
        data object Queued : UploadState
        data object Authenticating : UploadState
        data object Creating : UploadState
        data class Uploading(val fraction: Float, val confirmedBytes: Long) : UploadState
        data object Finalizing : UploadState
        data class Published(val item: PulseItem) : UploadState
        /** Distinct so the queue can prompt re-sign-in without losing the clip. */
        data object AuthRequired : UploadState
        data class Failed(val message: String, val retryable: Boolean) : UploadState
    }
}

/**
 * Persistent queue entry (Room). Holds the confirmed offset so resumable uploads
 * can continue after process death (docs/pulse-upload-flow.md §3, §9).
 */
data class UploadQueueEntry(
    val queueId: String,
    val localId: String,
    val mediaPath: String,
    val serverItemId: String?,      // set after CREATE
    val uploadUrl: String?,         // resumable target
    val confirmedBytes: Long = 0,
    val attempts: Int = 0,
    val status: PulseItem.Status = PulseItem.Status.UPLOADING,
)
