# Manual Claim Playbook

Date: 2026-06-13
Scope: No-code/manual creator claim workflow using existing VidMov + ARMember architecture.

## Objective
Operationally support:
Creator registers -> admin verifies identity -> admin links channel/profile -> creator manages channel.

## 1) Preconditions
1. Creator can register/login via ARMember pages:
   - Register: `/register/`
   - Login: `/login-2/`
2. Claim intake path exists (form/email/helpdesk).
3. Admin has WP access to users, posts, and profile metadata.

## 2) Intake Checklist
Collect from creator:
1. OffKilter username/email used to register.
2. Claimed channel identity (e.g., `FOOD FOR THOUGHT 313`).
3. Primary source URL(s): YouTube channel and/or playlist.
4. Preferred display name and handle.

## 3) Verification Checklist
Use one primary + one secondary proof where possible.

### Primary proof (preferred)
- One-time token in YouTube channel description/about/community post.

### Secondary proofs
- Reply from public business/contact email associated with channel.
- Cross-post verification on known social account linked to channel brand.

### Verification outcome
- `Approved`
- `Needs more proof`
- `Denied`

Record evidence timestamp and reviewer.

## 4) Channel Linking Procedure (Admin)
After approval:
1. Confirm canonical OffKilter user account for creator.
2. Create/attach `vidmov_user_profile` (`beeteam368_user_profile_id`).
3. Populate minimum profile fields:
   - avatar
   - banner
   - description/bio
4. Normalize creator taxonomy mapping for future imports:
   - ensure campaign/category mapping points to canonical creator identity.
5. Reassign previously imported placeholder-authored posts when applicable.
6. Verify frontend channel URL renders correctly.

## 5) Post-Claim QA
1. Creator can log in and edit profile.
2. Creator channel page shows updated avatar/banner/bio.
3. Target Arcana videos appear under correct creator identity.
4. Membership visibility remains unchanged.
5. No post playback/content regression.

## 6) Rollback Procedure
If a claim is disputed or mislinked:
1. Revert affected post authorship to prior user IDs.
2. Restore prior profile meta snapshot.
3. Flag channel as pending dispute review.

## 7) Service Levels (recommended)
1. Intake acknowledgment: within 24h.
2. Verification decision: within 3 business days.
3. Handoff completion after approval: within 2 business days.

## 8) Audit Log Fields
For every claim:
- claimant user ID
- claimed creator/channel
- proof artifacts
- reviewer
- decision
- affected post IDs
- before/after ownership mapping
- completion timestamp
