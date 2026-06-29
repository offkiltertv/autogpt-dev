# OFFKILTER Pulse Clipper — Upload Flow & Pulse API

Date: 2026-06-29
Status: Draft v0.1
Companion docs: [pulse-clipper-product-spec.md](pulse-clipper-product-spec.md) · [android-clipper-architecture.md](android-clipper-architecture.md)

---

## 1. Goals

- Upload a clip + its metadata to **OFFKILTER Pulse** reliably, in the background, surviving process death and flaky networks.
- Make every exported clip a **Pulse Item** (canonical schema in the product spec §9).
- Keep the creator in control: visible queue, retry, cancel, Wi-Fi-only option.
- Reserve hooks for **SidebarChat** (`discussionId`) and **Google Identity** auth.

---

## 2. End-to-end flow

```
 Editor export ──> exported .mp4 + PulseItem(draft, status=draft)
        │
        ▼
 Repository.enqueue(clip)        → Room: upload_queue row (status=uploading)
        │
        ▼
 WorkManager (unique work per clip)
   constraints: CONNECTED (or UNMETERED if Wi-Fi-only), storage-not-low
        │
        ├─ 1. AUTH    : ensure valid OFFKILTER token (Google Sign-In refresh)
        ├─ 2. CREATE  : POST /pulse/items  → server returns {id, uploadUrl}
        ├─ 3. UPLOAD  : resumable chunked PUT of media to uploadUrl
        │               (resume offset on retry; per-chunk progress → Flow)
        ├─ 4. THUMB   : upload/generate thumbnail (client poster frame)
        ├─ 5. FINALIZE: POST /pulse/items/{id}/finalize  with metadata
        │
        ▼
 PulseItem.status = published; mediaUrl/thumbnailUrl set; queue row cleared
        │
        ▼ (on any failure)
 backoff retry (exponential, capped); after N attempts → status=failed (user can retry)
```

WorkManager uses **unique work** keyed by the clip's local id so the same clip is never uploaded twice and can be observed/cancelled.

---

## 3. Resumable upload

- **Chunked, resumable** transfer (tus-style or a custom chunked protocol — final choice pending backend, tracked in architecture §11).
- Client tracks the confirmed byte offset in Room; on retry it asks the server for the current offset (`HEAD`/status) and resumes from there.
- Chunk size adaptive to network (smaller on cellular). Memory is O(chunk), never the whole file.
- Integrity: per-chunk checksum + a final whole-file checksum verified server-side before finalize.

---

## 4. Pulse API contract (proposed)

Hosted by the OFFKILTER backend (WordPress REST namespace handled by the `offkilter-arcana` plugin, paralleling existing custom entities). All endpoints require a bearer token.

| Step | Method & path | Body / params | Returns |
|---|---|---|---|
| Create | `POST /wp-json/offkilter/v1/pulse/items` | partial PulseItem (title, source, durationMs, capturedAt, tags, notes, transformationType) | `{ id, uploadUrl, uploadProtocol }` |
| Upload | `PUT {uploadUrl}` (resumable) | media chunks | `{ offset }` / `200` on complete |
| Status | `HEAD {uploadUrl}` | — | `{ offset }` (for resume) |
| Thumbnail | `POST /pulse/items/{id}/thumbnail` | poster image | `{ thumbnailUrl }` |
| Finalize | `POST /pulse/items/{id}/finalize` | full PulseItem metadata | `{ id, status, mediaUrl, thumbnailUrl }` |
| Read | `GET /pulse/items/{id}` | — | PulseItem |

Server-side, a finalized item becomes a `pulse_item` entity (new custom post type or table in the plugin) and can feed `[oktv_curated_section]` / a future `[oktv_pulse_feed]` shortcode on the web platform — closing the loop between the Android tool and OFFKILTER.tv.

---

## 5. Authentication

- **Google Sign-In / Credential Manager** on device → OFFKILTER backend exchanges the Google token for an OFFKILTER session/bearer token (ties to the "Google Identity readiness" note in `docs/platform-v2-product-review.md`).
- Token refresh handled in the AUTH step of the worker; a hard auth failure surfaces a re-sign-in prompt and pauses the queue (doesn't lose the clip).
- `creatorId` on the Pulse Item is the OFFKILTER creator resolved from the verified Google identity — the same identity that powers the web creator-opt-out/ownership flow.

---

## 6. Pulse Item metadata (canonical)

See product spec §9 for the full schema. Upload-relevant notes:
- `source.app` / `source.url` / `source.title` are best-effort from the Source Adapter (architecture §5) — never fabricated.
- `transformationType` defaults from the editor (what the creator added) and is editable before finalize.
- `discussionId` is **always null at upload** in MVP — reserved for SidebarChat.
- `status` lifecycle: `draft → uploading → published | failed`.

---

## 7. Queue UX

- A **Uploads** screen lists in-flight and failed clips with per-item progress, pause/resume, cancel, and retry.
- Notifications: a single foreground/progress notification for active uploads; a completion notification linking to the published Pulse Item.
- Wi-Fi-only toggle (default ON) — clips wait for unmetered network unless the user overrides per-clip.
- Drafts persist indefinitely until uploaded or deleted; nothing is lost on app death.

---

## 8. SidebarChat reservation

- `discussionId` is carried through create→finalize→read but unused in MVP.
- When SidebarChat ships, finalize (or a later call) attaches/creates a thread and populates `discussionId`; the web and app render a **"Discuss in SidebarChat"** action. No chat code in MVP.

---

## 9. Failure handling matrix

| Failure | Behavior |
|---|---|
| Network drop mid-upload | Resume from confirmed offset on reconnect (WorkManager retry). |
| Process/app death | Queue + offsets in Room; WorkManager reschedules; resumes. |
| Auth expired | Refresh; if refresh fails, pause queue + prompt re-sign-in (clip retained). |
| Server 5xx | Exponential backoff, capped retries, then `failed` (manual retry). |
| Storage low | Constraint blocks work until resolved; user informed. |
| Protected/black-frame clip | Caught at capture, not upload — never enters the queue. |

---

## 10. Privacy & data

- Only what the creator captured + the metadata they see is uploaded. No silent telemetry of source content.
- Source URL/title are uploaded only when the user supplied or confirmed them.
- Transformative-use reminder is local; no content analysis is sent anywhere.

---

## 11. Open upload questions (track in roadmap)

1. Final resumable protocol (tus vs custom) + backend storage target (Bunny Stream / object storage / WP media).
2. Server-side transcode responsibility (device exports final, or server normalizes?).
3. Thumbnail: client poster frame vs server-generated.
4. Rate limiting / abuse controls on the create endpoint.
5. Pulse Item moderation/review state before public visibility on OFFKILTER.tv.
