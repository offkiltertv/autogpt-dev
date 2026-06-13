# Arcana Alpha Execution Summary

Date: 2026-06-13
Inputs reviewed:
- `docs/alpha-test-plan.md`
- `docs/alpha-go-no-go.md`

## Highest Risks
1. WP Automatic source detection is convention-based (`wp_automatic_camp` + campaign table naming); mapping can fail if campaign metadata differs.
2. wpForo API behavior can vary by version; topic creation is best-effort and may produce partial success (entry published, topic missing).
3. Operator error during campaign duplication (editing an existing active campaign instead of duplicate) could affect live VidMov publishing.
4. First publish transition (`draft -> publish`) is the highest-impact step because it triggers wpForo creation.

## Lowest Risks
1. Plugin upload package integrity: ZIP structure is valid and PHP linting is clean.
2. CPT/taxonomy registration is isolated to Arcana (`arcana_entry`, `arcana_category`, `arcana_tag`).
3. Test can begin with draft-only import, avoiding immediate public content and minimizing blast radius.
4. Existing VidMov content path remains separate if WP Automatic duplicate is constrained to `arcana_entry`.

## Required Validation Steps
1. Install plugin ZIP and activate once during approved test window.
2. Verify core registration:
- `arcana_entry` exists.
- `arcana_category` and `arcana_tag` exist.
- Arcana settings page loads.
3. Duplicate one safe WP Automatic campaign (do not edit original).
4. Configure duplicate campaign only:
- source = dedicated test playlist
- target post type = `arcana_entry`
- post status = `draft`
5. Run one import cycle only.
6. Validate imported draft has Arcana enrichment metadata and taxonomy mapping.
7. Validate disclaimer renders on Arcana single view.
8. Manually publish that single draft and validate optional wpForo topic linkage only if discussion routing is enabled for that entry.
9. Verify no changes to existing VidMov campaigns/content behavior.

## Minimum Safe Scope
- One duplicated test campaign.
- One test playlist.
- One imported draft entry.
- One manual publish transition.
- Immediate rollback if any go/no-go trigger is observed.
