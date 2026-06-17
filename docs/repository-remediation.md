# Repository Remediation Plan

## Current Repository
- Current target name: `offkiltertv/offkilter-platform`
- Current content: OffKilter.TV platform and Arcana plugin assets
- Current issue: naming and path references must stay aligned with the OKTV Platform identity across all artifacts.

## Candidate Name Evaluation

### `offkilter-platform`
Pros:
- Best reflects multi-system scope (WordPress, plugins, infra docs, deployment)
- Scales cleanly as more systems are added
- Minimizes future renaming risk

Cons:
- Slightly generic

### `offkilter-core`
Pros:
- Signals central ownership
- Short and easy to reference

Cons:
- Can imply framework/library rather than full operations repo
- Less explicit about product/platform scope

### `offkilter-tv`
Pros:
- Closest to public brand
- Easy mapping for non-technical stakeholders

Cons:
- Name suggests only front-end site, not full ops/dev repository
- Less clear when infra/deployment/docs expand

## Recommended Final Name
Recommended: **`offkilter-platform`**

Reason:
- Most accurate long-term description of repository responsibility as canonical platform source of truth.

## Migration Plan (Do Not Execute Yet)
1. Freeze merges briefly during rename window.
2. Ensure GitHub repository canonical name is `offkilter-platform`.
3. Update local remotes:
   - `git remote set-url origin https://github.com/offkiltertv/offkilter-platform.git`
4. Validate access:
   - `gh repo view offkiltertv/offkilter-platform`
   - `git fetch origin`
   - `git push --dry-run`
5. Update references in docs and scripts.
6. Confirm branch protections and collaborator permissions survived rename.
7. Announce canonical repository URL to all operators.

## Risk and Mitigation
- Risk: stale URLs in scripts/docs.
  - Mitigation: global search and post-rename smoke test.
- Risk: contributor confusion during transition.
  - Mitigation: pinned repo notice and short migration message.
- Risk: automation breakage from hardcoded path.
  - Mitigation: validate CI/CD and deployment scripts immediately after rename.

## Success Criteria
- Repository name reflects OffKilter mission.
- All contributors can fetch/push without interruption.
- Documentation and deployment references match the new canonical URL.
