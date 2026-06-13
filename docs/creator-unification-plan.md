# Creator Unification Plan

Date: 2026-06-13

## Objective
Make Arcana creators feel native to the OffKilter creator ecosystem, not a parallel system.

## Current System Inputs Reviewed
- Member List
- Channel System
- Creator Profiles
- Subscription Surfaces

## Unification Principles
1. One creator identity model across all silos.
2. No Arcana-only creator profile type.
3. No Arcana-only subscription logic.
4. Arcana is a content lens, not a separate creator platform.

## Implementation Model (No Code)

### 1) Channel/Profile Consistency
- Arcana imports continue to use existing VidMov/WordPress author + creator taxonomy patterns.
- Creator names/avatars on Arcana cards must link to existing channel/profile pages.

### 2) Mixed Creator Visibility
- Keep `Featured Creators` as a mixed rail (Arcana + Gaming + Commentary + Music + others).
- Sorting should remain engagement/subscription based to preserve platform-native behavior.

### 3) Member List Integration
- Do not create a separate “Arcana member list.”
- Arcana creators should appear in existing member list based on normal role/content activity rules.

### 4) Subscription Continuity
- Subscription buttons/actions must remain identical across Arcana and non-Arcana creator pages.
- Avoid custom membership gates specific to Arcana creators unless business policy changes later.

## Creator Surface Map
- Homepage:
  - `🔮 The Arcana` content rail
  - `Featured Creators` mixed creator rail immediately after Arcana rail
- Navigation:
  - `Arcana -> Readers` points to existing member/creator directory
- Content cards:
  - creator avatar/name visible and clickable

## Validation Checklist
1. Arcana card creator click opens standard creator profile.
2. Arcana creators appear in member/creator list when qualified.
3. Subscribe/follow behavior is identical across content silos.
4. No duplicated creator identity records introduced.
5. Mixed creator rail contains Arcana and non-Arcana creators together.

## Risks
- Low: widget/menu configuration only.
- Medium: broken manual URLs in menu or creator links (mitigated by link QA).

## Rollout Sequence
1. Launch Arcana homepage/menu discovery.
2. Verify creator links from Arcana cards.
3. Confirm Featured Creators rail remains mixed and functional.
4. Confirm member list and subscriptions behave uniformly.
