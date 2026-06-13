# OffKilter Development Standards

## Required Rules
1. All code goes into Git.
2. Production is never edited directly.
3. New plugins belong under `wordpress/plugins/`.
4. Documentation is required for all major systems.
5. Feature branches are required.

## Branch and Commit Policy
- Use feature branches for all non-trivial work.
- Keep commits scoped by subsystem.
- Use dedicated commits for documentation changes when possible.
- Merge only after review or explicit operator approval.

## Environment Policy
- Production is a deployment target, not a development workspace.
- Test in local/staging before production whenever possible.
- Preserve rollback artifacts for every production deployment.

## Documentation Policy
- `README.md` defines repo purpose and workflow.
- `docs/` contains operational and architecture references.
- Update docs in the same change set as any behavior or workflow change.

## Plugin Structure Policy
- Current plugin path in this repository: `plugins/offkilter-arcana/`
- Target normalized structure for future consolidation: `wordpress/plugins/`
- Any path normalization should be done as a controlled migration, not ad hoc.

## Security and Stability Policy
- Validate access control before push/deploy operations.
- Keep backups and snapshots current before high-impact changes.
- Prefer low-risk remediation paths in production incidents.
