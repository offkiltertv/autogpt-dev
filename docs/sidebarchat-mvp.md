# SidebarChat MVP

Date: June 14, 2026 (America/Los_Angeles)
Status: Planning only (no implementation in this sprint)

## Product Goal
Add a lightweight, persistent community layer that increases session time and return visits without replacing VidMov or wpForo.

## MVP Scope
1. Persistent sidebar available on video pages, Arcana category pages, and creator pages.
2. Room model:
- `#lobby` (global)
- `#arcana`
- `#creator-{slug}` (creator-specific)
3. Identity model:
- Read access for anonymous visitors.
- Post access for authenticated users.
- Role badges: Member, Creator, Moderator, Admin.
4. Creator community support:
- Creator profile includes “Join Room” CTA.
- Video pages include “Discuss Live” CTA linking to relevant room.
5. Stream integration (MVP-lite):
- Manual “Live now” room flag.
- Optional pinned room message with current stream/video link.

## Room Rules
- Anonymous: view only.
- Logged-in members: post/reply/react.
- Creator role: pin messages and slow mode in own room.
- Moderator/Admin: mute, delete message, temporary room lock.

## Data and Routing Model
- Room slug is canonical key.
- Message record fields:
- `room_slug`
- `author_id`
- `body`
- `created_at`
- `moderation_state`
- Optional `related_video_id`
- Optional `related_creator_id`

## Discovery Surfaces
- Homepage rail: “Live Rooms” (top active rooms).
- Arcana category page: fixed `#arcana` room link.
- Creator pages: creator room link and presence count.

## MVP Success Metrics
- % of video sessions with room open.
- Median session duration lift.
- Return visitor rate after first room interaction.
- Posts/day in `#arcana` and creator rooms.

## Guardrails
- Do not replace wpForo.
- Keep long-form discourse in wpForo.
- SidebarChat is for real-time context and discovery, not archival threads.

## Post-MVP Extensions
- Highlights to wpForo thread exporter.
- Room notification preferences.
- AI helper for moderation assistance and FAQ routing.
- Browser extension for room alerts and quick joins.
