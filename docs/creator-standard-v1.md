# Creator Standard V1

Date: 2026-06-13
Scope: Arcana creator profile standardization (planning only, no production edits)
Baseline Pilot: FOOD FOR THOUGHT 313

## Objective
Turn the pilot channel quality target into a repeatable standard for all Arcana creators before outreach and claim operations.

## Minimum Creator Standard (Required)
A creator is considered `Creator Standard V1` only when all required fields are complete.

| Field | Requirement | Pass Criteria |
|---|---|---|
| Avatar | Required | Creator avatar visible on creator page and creator rails |
| Banner | Required | Channel banner visible at top of creator page |
| Creator Title | Required | Canonical creator/channel name matches source identity |
| Creator Description | Required | 2-4 sentence bio with clear content positioning |
| Featured Video | Required | At least one pinned or featured Arcana video |
| Channel Ownership State | Required | Status recorded as one of: Imported-Unclaimed, Claim Pending, Claimed-Verified, Managed |

## Channel Ownership State Model
| State | Meaning | Operator Action |
|---|---|---|
| Imported-Unclaimed | Imported creator profile exists with no verified owner | Keep attribution neutral; allow claim intake |
| Claim Pending | Creator started claim process | Hold changes until identity verification completes |
| Claimed-Verified | Ownership verified and linked to account | Enable profile editing and creator-facing controls |
| Managed | Claimed creator actively maintains profile/channel | Include in Featured Readers rotation |

## Readiness Scoring Model (0-100)
Score each creator across 5 categories.

- `Identity` (0-20): canonical name consistency, account/channel linkage
- `Branding` (0-20): avatar, banner, polished description
- `Discoverability` (0-20): visible in rails, archives, and navigation paths
- `Content Depth` (0-20): source volume and posting depth
- `Claim Readiness` (0-20): claim workflow readiness and ownership linkage state

Formula:

`Readiness Score = Identity + Branding + Discoverability + Content Depth + Claim Readiness`

## Scoring Rubric
| Category | 0-6 | 7-13 | 14-20 |
|---|---|---|---|
| Identity | Name mismatch, no account linkage | Partial handle consistency | Canonical identity and linked owner/account |
| Branding | Missing avatar/banner/bio | One or two profile assets present | Full profile assets complete and polished |
| Discoverability | Hard to find outside direct URL | Found in some archives/search | Visible in homepage/menu/creator surfaces |
| Content Depth | Sparse catalog | Moderate catalog | Strong catalog and sustained publishing history |
| Claim Readiness | No defined path | Intake path exists but incomplete | End-to-end claim process operational |

## Standardization Thresholds
- `80-100`: Outreach-ready
- `60-79`: Near-ready, complete missing profile and claim prerequisites
- `40-59`: Foundational work needed
- `<40`: Not outreach-ready

## Operational Use
1. Score creators monthly.
2. Prioritize profile upgrades by readiness score + source video volume.
3. Promote only `80+` creators to outreach and Featured Readers by default.
4. Re-score after each profile/claim milestone.
