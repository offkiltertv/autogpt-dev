# Moderation and Approval

Date: 2026-06-13
Purpose: Define review, approval, and moderation controls before publication and after forum launch.

## End-to-End Approval Flow
Curated Playlist / Collection / Source List
-> Approved at source level
-> Import Queue
-> Classification
-> Schedule for Publish
-> Optional Discussion Routing
-> Forum Moderation (selected posts only)

Exception flow:
User Submission / AI Discovery / Auto-Harvest / Third-Party Feed
-> AI Classification (optional)
-> Human Editorial Review
-> Approval / Reject / Hold
-> Import Queue
-> Classification
-> Schedule for Publish
-> Optional Discussion Routing
-> Forum Moderation (selected posts only)

## AI Classification Policy
1. AI outputs required:
- suggested silo
- confidence score
- tag suggestions
- safety/moderation risk flags
2. For curated OffKilter playlists, approval is inherited from playlist inclusion.
3. Item-level human approval is required for exception sources only:
- user-submitted content
- AI-discovered content
- automatically harvested content
- third-party feed imports

## Human Editorial Review
Checklist before approval:
1. Correct silo assignment
2. Duplicate check (URL/title/history)
3. Metadata quality (title, summary, tags, source attribution)
4. Discussion potential score
5. Policy/risk review (defamation/harassment/extreme claims)

Decision states:
- Approved
- Rework Required
- Rejected
- Hold for Review

## Publication Approval Rules
1. Curated OffKilter playlists/collections/source lists are pre-approved sources.
2. Arcana items from pre-approved curated sources do not require per-item approval.
3. Investigations from exception sources require secondary fact/risk review.
4. Commentary/Reality/Gaming/Music from exception sources require editor sign-off.
5. Any high-risk item (any source) requires escalation review before scheduling.

## Forum Moderation Framework
1. Do not auto-create discussion topic for every published entry.
2. Create discussion topics only for selected posts/campaigns.
3. For selected topics, apply starter prompts and moderation presets.
4. First-post moderation for new accounts.
5. Rate limits for new users and repeat offenders.
6. Escalation ladder:
- Warning
- Temporary restriction
- Permanent ban

## SLA Targets
1. Flagged content triage: <24h
2. Critical legal/safety escalations: <4h
3. Thread cleanup for spam/abuse: same day

## Weekly Moderation Review
1. Volume by silo
2. Flag rates and repeat abuse sources
3. Exception-source approval rejection reasons
4. Adjust classification prompts/rules from observed failures

## Governance Metrics
1. Approval rate by silo
2. Rejection rate by exception source type
3. Moderation incidents per 100 posts
4. Time-to-resolution for flagged topics

This process keeps publish velocity high without sacrificing trust, legal safety, or forum quality.
