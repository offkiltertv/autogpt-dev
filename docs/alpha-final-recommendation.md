# Arcana Alpha Final Recommendation

Date: 2026-06-13
Basis: Code audit + alpha planning docs + go/no-go restrictions.

## Decision Summary
A. Safe to Install: **Yes**
B. Safe to Activate: **Yes, with restrictions**
C. Safe to Run Single Test Import: **Yes, with restrictions**
D. Safe to Begin Arcana Campaign Creation: **Not yet (after single-test pass only)**

## Justification

### A) Safe to Install = Yes
1. Package structure is valid (`offkilter-arcana/offkilter-arcana.php` present).
2. Plugin PHP files lint clean.
3. Install alone does not alter campaigns until activation and test execution.

### B) Safe to Activate = Yes, with restrictions
1. Core Arcana systems are implemented (CPT, taxonomies, settings, hooks).
2. Activation must happen in controlled window with rollback operator ready.
3. Do not perform any campaign edits during initial activation verification.

### C) Safe to Run Single Test Import = Yes, with restrictions
1. Use duplicate campaign only; never edit original campaign.
2. Use dedicated test playlist only.
3. Force output to `draft` and import a single entry.
4. Manual checkpoint required before publish transition.

### D) Safe to Begin Arcana Campaign Creation = Not yet
1. First prove one successful end-to-end result:
- import -> enrich -> disclaimer -> publish (with optional wpForo topic on selected entry)
2. Confirm no VidMov regression.
3. Then authorize creation of recurring Arcana campaigns.

## Required Restrictions (Non-Negotiable)
1. No modification of existing active VidMov campaigns.
2. No use of `Prem and Outcome 2026` for first live test.
3. No use of `Poem` for first live test.
4. One test campaign, one test playlist, one imported entry.
5. Immediate rollback on any no-go trigger.

## Final Recommendation
Proceed to first live Alpha execution only under this order:
1. Install
2. Activate
3. Validate registration/settings
4. Duplicate safe campaign
5. Import one draft entry from test playlist
6. Validate enrichment/disclaimer
7. Publish single test entry
8. If enabled for that test entry, validate wpForo linkage
9. Verify VidMov unaffected

If any step fails, stop and rollback before proceeding.
