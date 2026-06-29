# OFFKILTER ↔ Pulse Clipper — Backend Integration Contract

The **Pulse Clipper** is now a standalone product in its own repository
(`pulse-clipper`). OKTV is **one backend** for it. This doc is what OKTV owns: the
server-side contract the `offkilter-arcana` plugin must implement so the clipper
can publish to OFFKILTER Pulse.

> The Android client lives in the `pulse-clipper` repo (`backends/offkilter/`).
> OKTV depends on Pulse Clipper **only through this REST contract** — never a code
> dependency, never the reverse. The seam is the Pulse Item schema + API version.

## Responsibilities (OKTV / offkilter-arcana plugin)

1. **`pulse_item` entity** — a new custom post type (or table), paralleling how
   `arcana_entry` / `vidmov_video` are handled today. Stores the platform-neutral
   Pulse Item fields.
2. **Pulse REST API** under `/wp-json/offkilter/v1/pulse/` (bearer auth).
3. **Resumable upload** endpoint (tus-capable — see the client's
   `docs/upload-resumable-recommendation.md`). Store media in the platform object
   store / Bunny Stream.
4. **Identity exchange** — accept a Google ID token, resolve/return the OFFKILTER
   creator (ties to the "Google Identity readiness" item in
   `docs/platform-v2-product-review.md`).
5. **Web surface** — render published Pulse Items on OFFKILTER.tv (a future
   `[oktv_pulse_feed]` shortcode or a curated section consuming `pulse_item`).
6. **Moderation/review state** before a Pulse Item is publicly visible.

## REST contract (mirror of the client expectation)

| Step | Method & path | Body | Returns |
|---|---|---|---|
| Create | `POST /wp-json/offkilter/v1/pulse/items` | partial Pulse Item | `{ id, uploadUrl, uploadProtocol: "tus" }` |
| Upload | tus `PATCH {uploadUrl}` | media chunks | `Upload-Offset` |
| Status | `HEAD {uploadUrl}` | — | `Upload-Offset` (resume) |
| Thumbnail | `POST /pulse/items/{id}/thumbnail` | poster image | `{ thumbnailUrl }` |
| Finalize | `POST /pulse/items/{id}/finalize` | full Pulse Item | `{ id, status, mediaUrl, thumbnailUrl }` |
| Read | `GET /pulse/items/{id}` | — | Pulse Item |

## Pulse Item fields (platform-neutral — defined by the client)

`creator`, `title`, `description`, `durationMs`, `source` (adapterId/app/url/title/
timestampMs), `tags`, `transformations`, `visibility` (private/unlisted/public),
`discussionId` (reserved for SidebarChat), `capturedAt`, plus server-set
`id`/`mediaUrl`/`thumbnailUrl`/`status`.

## Versioning
- API namespaced `/v1/`; bump to `/v2/` only for breaking changes. The client
  negotiates via the path.
- Keep additive: new fields default-valued; never remove/repurpose a field.

## Non-dependency invariant
OKTV implements this contract; the clipper consumes it. Neither repo imports the
other's code. Both evolve independently behind the schema + API version.
