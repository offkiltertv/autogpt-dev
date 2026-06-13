# Repository Audit - offkiltertv/autogpt-dev

Date: 2026-06-12
Branch audited: `feature/arcana-plugin`
Remote: `https://github.com/offkiltertv/autogpt-dev.git`

## Scope and Method
- Scanned tracked repository files and directories.
- Searched for AutoGPT indicators: `autogpt`, `auto-gpt`, `agpt`, `forge`, `benchmark`, `classic`, `platform`.
- Reviewed branch/commit lineage and remote branch list.

## Findings Summary
- No AutoGPT source code detected in current repository contents.
- No AutoGPT configuration files detected.
- No AutoGPT dependency manifests detected (no `package.json`, `pyproject.toml`, `requirements.txt`, etc.).
- No AutoGPT docs/workflows detected (no `.github/workflows` automation present).
- Repository appears to be a single-commit OffKilter Arcana codebase snapshot.

## A. Active OffKilter Assets
- `plugins/offkilter-arcana/offkilter-arcana.php`
- `plugins/offkilter-arcana/includes/class-okarcana-activator.php`
- `plugins/offkilter-arcana/includes/class-okarcana-admin.php`
- `plugins/offkilter-arcana/includes/class-okarcana-db.php`
- `plugins/offkilter-arcana/includes/class-okarcana-disclaimer.php`
- `plugins/offkilter-arcana/includes/class-okarcana-importer.php`
- `plugins/offkilter-arcana/includes/class-okarcana-post-types.php`
- `plugins/offkilter-arcana/includes/class-okarcana-scheduler.php`
- `plugins/offkilter-arcana/includes/class-okarcana-wpforo.php`
- `plugins/offkilter-arcana/arcana_disclaimer.md`
- `plugins/offkilter-arcana/README.md`
- `plugins/offkilter-arcana/docs/IMPLEMENTATION_NOTES.md`
- `plugins/offkilter-arcana/docs/sample-import.csv`
- `docs/arcana-plugin-installation.md`
- `deployment/offkilter-arcana-deployment.md`
- `scripts/deploy_offkilter_arcana.sh`

## B. Historical AutoGPT Assets
- None found in the audited branch/tree.
- Branch history check result: only `feature/arcana-plugin` exists remotely and locally; no additional branch containing AutoGPT assets was visible from this clone.

## C. Unused Files (Current State)
- `plugins/offkilter-arcana/assets/` (empty directory, no runtime impact).

## D. Safe-to-Remove Files (Conservative)
- `plugins/offkilter-arcana/assets/` (empty; safe to remove if you do not plan to add CSS/JS/images soon).

## Decision Analysis
1. Keep repository and rename purpose
- Viable: yes.
- Pros: zero migration effort, existing branch/push flow preserved.
- Cons: repository name still causes operational confusion.

2. Rename repository
- Viable: yes.
- Pros: fastest way to eliminate naming confusion while preserving issues, history, remotes (GitHub auto-redirects old URL).
- Cons: update local `origin` URL references for clarity.

3. Create new repository and migrate
- Viable: yes.
- Pros: clean canonical destination and naming from day one.
- Cons: more coordination overhead, branch/PR context split, unnecessary for current size.

4. Delete repository
- Viable: not recommended at this stage.
- Pros: removes confusion only if replacement already operational.
- Cons: high risk of accidental source loss and interruption.

## Recommendation
Recommended option: **2. Rename repository**.

Suggested target name examples:
- `offkilter-platform`
- `offkilter-wordpress`
- `offkilter-arcana`

Rationale:
- Current content is already OffKilter-focused.
- Renaming is the fastest low-risk fix for ownership/name ambiguity.
- No migration is required to secure current plugin work.

## No-Delete Status
- No files were deleted.
- No code was modified.
