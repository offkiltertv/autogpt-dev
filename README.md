# OffKilter.TV Platform Repository

This repository is the canonical source of truth for OffKilter.TV development.

## What Is OffKilter.TV
OffKilter.TV is an independent creator platform focused on video discovery, interpretation, community discussion, and creator-owned audience growth.

## Project Vision
Build a creator-first ecosystem that combines:
- video publishing and curation
- forum-led discussion and community identity
- memberships and monetization
- long-term knowledge vaults (starting with Arcana)

## Current Stack
- WordPress (Bitnami on GCP)
- VidMov theme ecosystem
- wpForo (forums/community)
- ARMember (memberships/paywalls)
- myCred (points/ranks/rewards)
- Elementor Pro (layout/content tooling)
- Cloudflare (DNS/SSL/proxy)
- Bunny.net (video/CDN services)
- Google Analytics / Site Kit (measurement)

## Architecture Overview
- Platform runtime: WordPress application server on Google Cloud VM.
- Edge/network: Cloudflare DNS + TLS + proxy controls.
- Content/community: VidMov + wpForo + ARMember + myCred.
- Arcana module: custom plugin in `plugins/offkilter-arcana/`.
- Documentation: repository `docs/` directory.

## Development Workflow
1. Create or update a feature branch.
2. Implement changes in Git (never only on production).
3. Keep plugin code under repository paths (target standard: `wordpress/plugins/`).
4. Add/update documentation with each major system change.
5. Open PR for review and merge.

## Deployment Workflow
1. Build artifacts from committed Git state only.
2. Validate on staging first whenever possible.
3. Deploy to production WordPress with rollback artifact available.
4. Verify health checks (HTTP, wp-admin, plugin activation, cron behavior).
5. Record deployment notes in `deployment/` and/or release notes.

## Contribution Guidelines
- Use descriptive feature branch names.
- Keep commits scoped and reviewable.
- Avoid direct production edits.
- Include operational impact and rollback notes for non-trivial changes.
- Preserve existing stack unless a migration is explicitly approved.
