# v3.0 Cross-Device Responsive Audit (Phase 6)

**Date:** 2026-07-02
**Method:** Playwright + system Chrome, real rendered captures against production. 7 device profiles × 5 pages = **35 screenshots** in `docs/assets/v3-responsive/` (`{page}--{profile}.png`).

| Profile | Emulation |
|---|---|
| `desktop` | 1440×900 |
| `ipad-portrait` / `ipad-landscape` | iPad Pro 11 device descriptors |
| `android-pixel7` | Pixel 7 device descriptor |
| `fold-folded` | 373×841 @3x, mobile UA (Pixel Fold folded) |
| `fold-unfolded` | 653×841 @3x (Pixel Fold unfolded) |
| `googletv` | 1920×1080, Android TV/CrKey UA |

Pages: home, `/discover/`, `/pulse/`, single video (FFT313), creator channel.

> Emulation caveat: viewport/UA emulation is faithful for layout but cannot capture D-pad focus navigation (Google TV) or fold-hinge behavior. A physical-device pass remains the final gate; every issue below is verifiable in the referenced PNG.

## What renders correctly (verified)

- **Hero** ("Off-script. On point." + CTAs) is legible and unbroken from 373 px folded up to 1920 px TV — no overflow, no clipped CTAs (`home--fold-folded.png`, `home--googletv.png`).
- **Pulse destination** identity (teal icon, pulsing dot lockup, explainer card, seeded cards with duration chips) renders on all profiles (`pulse--android-pixel7.png`).
- **Discover** sections (pillar intro, Editor's Picks, Trending Pulse, creators row, Recommended Creator) reflow correctly at tablet widths (`discover--ipad-landscape.png`).
- Zero Priority-Zero strings on any profile (re-swept during QA).
- Our components introduce ~0 CLS (Lighthouse: CLS 0–0.001 on home/discover mobile).

## Remaining issues (ranked by user impact)

1. **Termly cookie banner dominates small screens** — occupies ~50% of the folded-phone viewport and is literally the page's LCP element on mobile (`home--fold-folded.png`, `pulse--android-pixel7.png`). *Fix (operator, Termly dashboard):* compact/bottom-bar display mode; see also the performance note (its `resource-blocker` script is the #1 render-blocker).
2. **Zero-state trust noise on cards** — theme overlays show `0%` review bubbles and "0 Reactions" on most thumbnails (`discover--ipad-landscape.png`, right rail). Zeroes read as "nobody is here." *Fix (theme options):* hide review %/reaction counts until non-zero.
3. **Theme-injected social-share row ("Spread the love") at the top of page content** on Discover/Pulse pages pushes the actual content down a full block (`discover--ipad-landscape.png`). *Fix:* Sassy Social Share settings → disable auto-insert on pages (keep on videos).
4. **Side-menu panel renders expanded at TV width** occupying the right third (`home--googletv.png`). Acceptable on desktop; on TV it competes with content. *Fix (theme option/Customizer):* default side menu collapsed at ≥1600 px.
5. **Minor:** wpForo `colors.css` and Google Fonts load render-blocking on every page including non-forum pages (see performance section of the v3 report).

## Landscape

`ipad-landscape` (1194×834) verified — nav, hero, rails reflow correctly; no horizontal scroll detected in captures.
