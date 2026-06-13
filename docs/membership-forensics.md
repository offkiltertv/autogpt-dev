# Membership Forensics (ARMember + VidMov)

Date: 2026-06-13

## Observed Plans
- `1`: Free Membership (active)
- `2`: Default Plan (active)

## Effective Visibility Control Path
Primary control for VidMov video gating is from VidMov membership metadata, not WP Automatic directly:
- Post-level: `beeteam368_membership_plans` (post meta)
- Category/tag-level fallback: `beeteam368_membership_plans` (term meta)

VidMov extension code resolves post access by reading these values and displaying membership badge/gate.

## Arcana Category Membership Configuration Applied
Term meta set on:
- `Arcana` (`2258`)
- `Premonitions` (`2259`)
- `Outcomes` (`2260`)

Value:
- `beeteam368_membership_plans = a:1:{i:0;s:1:"1";}`

## Membership Assignment Source For Imported Videos
For imported videos through WP Automatic:
- WP Automatic assigns taxonomy categories from campaign `camp_post_category`
- VidMov/extension reads category membership plan meta
- Resulting post shows Free Membership gating/badge

## Live Test Evidence
Imported Arcana test video `8093` renders with Free Membership gate/badge and plan id `1` markers in page HTML.
