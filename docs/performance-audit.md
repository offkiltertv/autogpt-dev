# Performance Audit

Date: 2026-06-13
Scope: CDN and delivery path review (Cloudflare, Bunny, WordPress assets, VidMov delivery)

## Observed Delivery Path
Current effective path for most assets:
Visitor
-> Cloudflare (`server: cloudflare`)
-> Origin (`www.offkilter.tv`)

Configured but inconsistent secondary CDN path detected:
- `oktv.b-cdn.net` appears in page URLs/options
- direct checks to `oktv.b-cdn.net` and sample media path return `403` (Bunny edge)

## Evidence Snapshot
- OffKilter static assets return Cloudflare headers with long cache control (`max-age=315360000`).
- Mixed cache outcomes observed (`cf-cache-status: HIT` and `MISS`).
- Homepage/video HTML still references legacy origin IP host (`34.105.65.179`) and `oktv.b-cdn.net` in places.

## Bunny/CDN Configuration Notes
WordPress options indicate Bunny integration is enabled (Beeteam + BunnyCDN plugin settings present), but runtime behavior indicates misalignment:
- Bunny hostname configured (`oktv.b-cdn.net`, Beeteam hostname also present)
- Bunny direct asset requests returning `403`
- legacy `bunnycdn_cdn_url` still points to `http://34.105.65.179`

This suggests partial/misaligned CDN migration state.

## High Impact Recommendations
1. Remove/replace all `34.105.65.179` URL references in menus/content/options.
2. Correct Bunny origin/CDN URL configuration to canonical host (`https://www.offkilter.tv`) or disable Bunny rewrite until validated.
3. Ensure one authoritative asset rewrite layer (avoid conflicting Beeteam Bunny + BunnyCDN plugin behavior).
4. Validate that top media and thumbnail URLs return `200` via intended CDN path (not `403`).

## Medium Impact Recommendations
1. Warm Cloudflare cache for high-traffic Arcana thumbnails/video pages after campaign runs.
2. Audit featured image sizes for Arcana rails and enforce right-sized derivatives for homepage.
3. Reduce third-party request overhead where possible (social widgets/scripts on critical pages).

## Low Impact Recommendations
1. Standardize cache-check routine (sample HIT/MISS checks across homepage + Arcana pages).
2. Document canonical asset host policy for operators.
3. Add periodic link/header sanity checks for CDN hostnames.

## Risk Notes
- Current stack functions, but mixed CDN layers and stale URL references introduce avoidable latency and reliability risk.
- Immediate priority is consistency, not adding new delivery complexity.
