# Arcana Public Discovery Strategy

Date: 2026-06-13
Scope: Discovery + ownership policy for Arcana using current production behavior

## Phase 1: Access Model Audit (Current Behavior)

Validated anonymous access (no login cookie):
- Arcana video page: `HTTP 200`
- Arcana category archive: `HTTP 200`
- Premonitions archive: `HTTP 200`
- Outcomes archive: `HTTP 200`
- Creator profile page (`/channel/channel-id/@.../`): `HTTP 200`
- Community forum landing (`/community/`): `HTTP 200` (readable)

Current practical behavior:
- Anonymous visitors can watch Arcana videos.
- Anonymous visitors can read imported comments.
- Anonymous visitors can browse creator profiles.
- Anonymous visitors can browse Arcana archives.

Related site options:
- `comment_registration = 0`
- `default_comment_status = open`
- `users_can_register = 1`

## Phase 2: Login Gate Review

### PUBLIC
- Arcana video pages
- Arcana/Premonitions/Outcomes category archives
- Creator profile pages and creator archives
- Community/forum browsing (read access)

### OPTIONAL LOGIN
- Registration/login pages (`/main-login/`, `/main-register/`)
- Community participation prompts (sign-in CTAs visible on forum)
- Commenting/community actions where UX encourages sign-in

### RESTRICTED
- `/wp-admin/` redirects to login (`302` to `/main-login/`)
- Account-level management and administrative operations
- Future ownership-claim approval actions (admin/reviewer functions)

## Phase 3: Creator Ownership Model

### Visitor
- Permissions: view videos, view comments, browse creators and archives
- Visibility: full public discovery surfaces
- Ownership rights: none

### Member
- Permissions: account access, community participation, subscriptions/follows
- Visibility: all public + member features
- Ownership rights: none by default

### Creator
- Permissions: publish/manage own OffKilter account profile and content (where assigned)
- Visibility: creator profile and channel surfaces
- Ownership rights: account-owned content and profile fields

### Claimed Creator
- Permissions: creator + verified control over imported creator identity mapping
- Visibility: same public discovery, plus ownership badge/state
- Ownership rights: manage claimed channel identity and future creator controls

## Phase 4: Future Creator Control Workflow

Imported Creator
-> Claim Request
-> Verified Creator
-> Channel Ownership

Recommended control logic:
1. Imported profiles remain publicly discoverable by default.
2. Claim request binds applicant to `channel_id` (canonical key).
3. Reviewer verifies proof of control.
4. On approval, ownership state changes to claimed without removing public pages.
5. Public discoverability remains intact before and after claim.

## Phase 5: Recommendation

## Always Public (discovery layer)
- Arcana videos and archives
- Creator profile pages (imported and claimed)
- Imported comments (read access)
- Search/indexable metadata and category pages

## Optionally Creator-Gated (monetization layer)
- Premium interpretations / extended analysis posts
- Creator-only bonus playlists or companion posts
- Members-only community threads/Q&A sessions
- Creator subscriber perks/badges

## Require Login
- Comment/reply/create-topic actions (recommended policy)
- Subscription/follow actions
- Claim request submission and creator profile management
- Any ownership verification or administrative workflow

## Strategic Position
Maintain an open top-of-funnel:
- Public video + archive + creator discovery drives SEO and first-time reach.

Reserve control and monetization for authenticated workflows:
- Login required for participation, ownership operations, and premium community features.

This preserves discoverability while protecting future creator ownership and monetization options.
