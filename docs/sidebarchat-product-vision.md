# SidebarChat Product Vision

Date: June 14, 2026 (America/Los_Angeles)
Status: Architecture concept only (no implementation)

## Purpose
SidebarChat is a persistent, real-time community layer for OKTV that complements VidMov video consumption with lightweight conversation, creator rooms, and live social context.

## Product Intent
- Keep users on-platform longer with always-available chat context.
- Turn passive viewing into active participation.
- Support creator-led micro-communities without forcing full forum navigation.

## Inspiration Model
- AIM: lightweight buddy-presence and direct messaging behavior.
- mIRC: persistent topical channels and rapid room culture.
- Destiny.gg style real-time stream-adjacent community loops.

## Core Experience
1. Persistent room sidebar available across OKTV pages.
2. Room types:
- Global Lobby
- Arcana Lounge
- Creator-specific rooms
- Topic rooms (Gaming, Commentary, Reality TV, Music, Investigations)
3. Presence state:
- Online users count
- Active rooms
- Creator online indicator (future)
4. Message features:
- Text-first chat
- Link previews
- Basic moderation controls
- Pinned room messages (future)

## Creator Community Layer
- Each creator can have a canonical room slug.
- Creator profile -> room deep-link.
- Video page -> room context pivot (optional join prompt).
- Claimed creators can moderate their own rooms (future role model).

## Integration Surfaces
- VidMov video pages: contextual room CTA.
- Arcana pages: Arcana Lounge + Featured Readers room links.
- Member/creator pages: room presence badge.
- wpForo: long-form thread continuity from live chat highlights (future bridge).

## Browser Extension Concept
- Lightweight extension for:
- Room notifications
- Creator live room alerts
- Quick-join from YouTube/other references back into OKTV rooms
- Privacy-safe scope: explicit domain allowlist + user opt-in.

## AI Helper Concept
- AI room assistant for moderation and navigation support:
- FAQ routing
- Rule reminders
- Link/context summarization
- Highlight extraction for forum thread seeding
- AI assistant remains assistive, never the primary speaker of record.

## DifficultResearch Integration Concept
- Allow optional cross-community room federation patterns:
- Shared events
- Scheduled topic exchanges
- Cross-domain identity mapping by explicit consent only
- No implicit account linking.

## Safety and Moderation Model
- Role tiers: Visitor, Member, Moderator, Creator, Admin.
- Room-level moderation actions:
- Mute timeout
- Slow mode
- Link throttling
- Keyword filters
- Audit logs retained for trust and enforcement.

## Phased Rollout
1. MVP:
- Global Lobby + Arcana Lounge
- Basic room list and message stream
- Authenticated posting
2. Phase 2:
- Creator rooms
- Presence and room discovery
- Moderation toolset
3. Phase 3:
- AI helper
- wpForo bridge
- Notification/extension pathways

## Non-Goals (for initial rollout)
- Replacing wpForo.
- Full Discord clone parity.
- Multi-protocol chat federation.

## Success Metrics
- Session duration lift.
- Return visitor frequency.
- Creator room participation.
- Conversion from viewer -> logged-in participant.
- Forum thread growth sourced from live-room discussions.
