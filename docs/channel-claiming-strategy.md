# Channel Claiming Strategy

Date: 2026-06-13
Environment: Production (`oktv-main-deployment-vm`) read-only audit

## Objective
Define how imported creators can claim and manage their OffKilter creator presence without breaking existing imported content.

## 1) Current Authentication and Account Flows

### What exists now
- WordPress registration is enabled: `users_can_register=1`.
- Default role for new users: `armember`.
- ARMember is active (`armember`, `armember-membership`, `armembercommunity`).
- ARMember register/login pages are live:
  - Register: `/register/` (page `7695`, shortcode `[arm_form id="101"]`)
  - Login: `/login-2/` (page `7697`, shortcode `[arm_form id="102"]`)
  - Profile: `/arm_member_profile-2/` (page `7717`)
- Theme My Login is also active with routes:
  - `/main-login/`, `/main-register/`, `/main-lostpassword/`, `/main-resetpass/`

### Google/Social login status
- `arm_is_social_login_feature=0` (disabled).
- No active dedicated Google OAuth login plugin found (no Nextend/miniorange-style auth plugin active).
- `arm_is_social_feature=1` exists, but that is not the same as login enablement.

### ARMember integration notes
- ARMember controls registration/login/member profile surfaces.
- ARMember free plans exist:
  - Plan 1: Free Membership
  - Plan 2: Default Plan

## 2) VidMov Creator/Channel Architecture (Current)

### How video/channel identity is currently represented
- Imported videos are `post_type=vidmov_video`.
- Creator/channel discovery is split across two systems:
  1. WordPress `post_author` (user account)
  2. VidMov creator taxonomy terms in `vidmov_video_category` (e.g., `@lipps`, `@LC`)

### Existing creator taxonomy
- Taxonomy: `vidmov_video_category` (attached to `vidmov_video`).
- Top creator terms include:
  - `@lipps` (`count=358`)
  - `@LC` (`count=212`)
  - `@DaniElleLuminati` (`count=61`)

### Channel ownership fields currently observed
- User-level channel/profile metadata exists via `beeteam368_*` usermeta:
  - `beeteam368_user_profile_id` -> links user to `vidmov_user_profile` post
  - `beeteam368_user_avatar`, `beeteam368_user_channel_banner`, subscription/privacy meta
- For established users, `beeteam368_user_profile_id` points to `vidmov_user_profile` posts where `post_author` is that user.

### Important current mismatch
For Arcana test imports, author and creator term are not aligned:
- Posts `8093/8095/8096` are tagged `@lipps`, but authored by new placeholder contributor users (`87/88/89`).
- Placeholder users have empty email and minimal profile metadata.

Implication:
- “Creator identity” on site currently behaves more like a taxonomy label than guaranteed account ownership.

## 3) Can Creators Claim Channels Today?

### A. Sign in with Google
- Current state: **No** (not enabled).
- Requirement: enable ARMember social login feature and configure Google OAuth credentials.

### B. Create a new OffKilter account
- Current state: **Yes**.
- Best existing path: ARMember register/login pages (`/register/`, `/login-2/`).

### C. Claim an existing imported creator profile
- Current state: **Not self-service**.
- No native claim workflow/field found in current stack for term->user ownership handoff.
- Can be done **operationally/manual** by admin today (see proposed flow).

## 4) Recommended Low-Friction Claiming Flow (No New Plugin Required)

## Phase 1: Enable Discover + Intent Capture
1. Add “Claim this creator profile” CTA on creator/channel pages (can be menu/button/link to existing contact/form page initially).
2. Require claimant to authenticate first (ARMember login/register).
3. Collect claim request fields:
   - OffKilter account username/email
   - Claimed creator handle (e.g., `@lipps`)
   - Proof method selected

## Phase 2: Ownership Verification (Operational)
Use one of these proof methods:
1. YouTube channel proof token (preferred):
   - OffKilter generates one-time token.
   - Creator adds token to YouTube channel description/about or community post.
   - Admin verifies and records evidence timestamp.
2. Email-domain proof (secondary):
   - If channel business email is public and controllable.
3. Prior linked account proof (fallback):
   - Existing known OffKilter account history + manual review.

## Phase 3: Channel Handoff (Admin)
After verification:
1. Set claimant as canonical creator owner in operations ledger.
2. Reassign relevant `vidmov_video` posts from placeholder import users to claimant user where appropriate.
3. Ensure claimant has `beeteam368_user_profile_id` and profile assets (avatar/banner).
4. Keep creator taxonomy (`@handle`) consistent across old and new posts.
5. Add redirect/normalization rules operationally for handle variants (case/spacing) if needed.

## Phase 4: Creator Self-Management
Claimed creator can then:
- edit profile
- update avatar/banner/bio
- participate in comments/forums
- use member/community features

## 5) Operational Guardrails
1. Keep an internal claim ledger for every decision:
   - requested handle
   - claimant account
   - proof used
   - reviewer
   - decision date
2. Require two-person review for disputed/high-follower channels.
3. Do not delete imported content during claim transfer; only reassign ownership metadata/authorship.
4. Keep rollback capability (pre-change export of affected post IDs + authors).

## 6) Legal / Policy Considerations
1. Impersonation risk:
   - Never auto-grant ownership based on username similarity alone.
2. Right of publicity / trademark:
   - Creator naming/branding can implicate personality and mark rights.
3. Attribution and provenance:
   - Imported profiles should clearly indicate source (YouTube metadata import).
4. Dispute handling:
   - Publish a channel-claim dispute and appeal process.
5. Platform disclosures:
   - State that imported creator pages are index/aggregation pages until claimed.

## 7) Technical Considerations
1. Dual identity model currently exists (author user + creator taxonomy term).
2. Imported author accounts may be placeholders (no email/profile), so direct “forgot password” claim is unreliable.
3. Claim flow should standardize on one canonical owner model:
   - recommended: real OffKilter user account as owner + consistent creator term mapping.
4. Future automation can map campaign source handle -> canonical user ID once claims exist.

## 8) Minimum Viable Rollout (Recommended)
1. Keep current import pipeline unchanged.
2. Enable one canonical login path for creators (`/login-2/` + `/register/`).
3. Add manual claim intake + verification SOP.
4. Start with manual handoff for first 3-5 claimed creators.
5. After proven operations, automate parts of reassignment workflow.

## Success Path (Target)
Creator discovers OffKilter
-> signs in
-> submits claim
-> ownership verified
-> profile/channel assigned
-> creator manages channel and joins community

## Decision Summary
- Low-friction creator onboarding is already possible (ARMember registration/login).
- Self-service claim is not currently native.
- Immediate path is a manual verified-claim workflow with admin reassignment, then gradual automation.
