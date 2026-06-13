# OffKilter Arcana Deployment Plan

## Branch Strategy
- Feature development branch: `feature/arcana-plugin`
- Merge target: default branch (recommended `main`)
- Deploy only from reviewed/merged commits.

## Environment Strategy
1. Development (this repo branch)
2. Staging WordPress (recommended before production)
3. Production WordPress (`offkilter.tv`)

## Deployment Steps (Production)
1. Pull tagged/reviewed commit from GitHub.
2. Build plugin artifact:
   - `zip -r offkilter-arcana.zip plugins/offkilter-arcana`
3. Upload via WP admin plugin uploader or server-side sync.
4. Activate/update plugin.
5. Verify:
   - Arcana admin page loads.
   - Queue table exists.
   - Cron event scheduled.
   - Manual import works.
   - wpForo topic link created after publish.

## Rollback
- Keep previous plugin zip artifact.
- If regression appears:
  1. Deactivate current plugin.
  2. Reinstall previous zip artifact.
  3. Re-run verification checks.

## Operational Notes
- Production server is deployment target, not source of truth.
- All plugin changes must be committed/pushed to GitHub first.
