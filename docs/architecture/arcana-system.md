# Arcana System Architecture

## Flow
YouTube
-> WP Automatic
-> VidMov (`vidmov_video`)
-> Creator Metadata
-> Creator Profiles
-> Future Channel Claiming

## Components

### YouTube
- Source-of-truth content host.
- Curated playlists act as editorial approval gate.

### WP Automatic
- Ingestion engine.
- Handles playlist polling, content import, scheduling, dedupe, and comments import.

### VidMov
- Presentation layer for imported videos.
- Renders archives, search, single video pages, and category rails.

### Creator Metadata Layer
Stored on imported posts through WP Automatic custom fields:
- `channel_id`
- `channel_title`
- `youtube_publish_date`
- (phase 2 planned: likes/tags raw)

### Creator Profiles
- Uses identity signals from `channel_id` + `channel_title`.
- Supports directory, archive grouping, and Featured Readers.

### Future Channel Claiming
- Claim workflows map OffKilter user accounts to imported creator identities.
- `channel_id` is the canonical external identity key.

## Design Constraints
- No custom importer required.
- No VidMov core modification required.
- No architecture replacement.
- Build on existing production stack.
