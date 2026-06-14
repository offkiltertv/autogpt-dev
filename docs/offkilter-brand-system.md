# OFFKILTER Brand System (Research + Design)

Date: 2026-06-14
Scope: Discovery and design recommendations only. No production changes applied.

## 1) Default VidMov Asset Inventory

## Confirmed inherited/default elements

| Surface | Current Asset/Pattern | Evidence | Issue | Replacement Priority |
|---|---|---|---|---|
| Homepage rail icons | `fab fa-canadian-maple-leaf` on multiple rails | Elementor snapshot (`post_1244_elementor.*.json`) shows all key rails using the same icon | Strong stock-theme signal; no OFFKILTER meaning | Critical |
| Post image fallback | Theme placeholder image | `wp-content/themes/vidmov/css/images/placeholder.png` referenced in `inc/template-tags.php` | Generic fallback lacks platform identity | High |
| Theme visual presets | Default header/archive style packs (`header-*.png`, `archive-*.png`) | `wp-content/themes/vidmov/inc/theme-options/images/*` | Signals template inheritance | Medium |
| Sidebar widget icons | Generic Font Awesome set (`fa-thumbs-up`, `fa-headphones-alt`, `fa-tv`, `fa-star-half-alt`) | `widget_beeteam368_post_extensions`, `widget_beeteam368_channel_extensions` option payloads | Not aligned to OKTV pillars | High |
| Membership/profile defaults | ARMember default social badge set + default cover | `wp-content/uploads/armember/social_badges/*`, `profile_default_cover.png` | Generic badge language, inconsistent with Arcana/Signals tone | Medium |
| Commerce placeholder | WooCommerce placeholders | `wp-content/uploads/woocommerce-placeholder-*` | Stock ecom imagery visible if product/image missing | Medium |
| Legacy point icon assets | `default-point-type*.png` | `wp-content/uploads/*/default-point-type*.png` | Generic/default token assets | Medium |

## Additional visual debt indicators
- Footer still reads `Copyright © 2023 OFFKILTER.TV` (stale trust signal).
- Multiple rails repeat the same structure and icon motif, which reinforces “theme demo” perception.
- Existing side/top nav icon language is mixed (some icon-only affordances, some text-only menu).

## 2) Icon System - Three Visual Directions

All directions below are compatible with existing VidMov/Elementor icon fields (Font Awesome classes) for low-risk rollout.

## Direction A: Signal / Compass (recommended for rollout)
Theme: orientation, discovery, movement.

Visual tone:
- Clean, navigational, editorial.
- Strong fit for `Watch + Explore + Signals`.

Example icon language:
- OFFKILTER: `fa-compass`
- Signals: `fa-bolt`
- Arcana: `fa-sparkles` (or `fa-star` if unavailable)
- Community: `fa-comments`
- Creators: `fa-user-group`
- Explore: `fa-binoculars`

Pros:
- Broad audience fit.
- Works across Arcana and non-Arcana silos.
- Easy to keep neutral/professional.

## Direction B: Observer / Eye
Theme: witness, analysis, pattern recognition.

Visual tone:
- Investigative and commentary-forward.
- Good fit for `Investigations + Commentary + Discovery`.

Example icon language:
- OFFKILTER: `fa-eye`
- Signals: `fa-satellite-dish`
- Arcana: `fa-moon`
- Community: `fa-message`
- Creators: `fa-id-badge`
- Explore: `fa-magnifying-glass`

Pros:
- Distinctive identity versus generic video platforms.
- Strong narrative coherence for “watch + interpret”.

## Direction C: Crystal / Arcana
Theme: symbolic, mystical, interpretive.

Visual tone:
- Highly thematic and premium for Arcana.
- Best when Arcana is a flagship section.

Example icon language:
- OFFKILTER: `fa-gem`
- Signals: `fa-wand-magic-sparkles`
- Arcana: `fa-crystal-ball` (fallback `fa-circle-radiation` or `fa-star-and-crescent`)
- Community: `fa-comments`
- Creators: `fa-user-astronaut`
- Explore: `fa-route`

Pros:
- Highest uniqueness.
- Strong emotional recall.

Risk:
- Can over-tilt mystical if used globally; better as Arcana-forward accent system.

## 3) Navigation Icon Audit + Recommended Set

Current primary nav labels:
- Home
- Watch
- Creators
- Community
- Arcana
- Explore

Current state:
- Top menu is mostly text-first with inconsistent icon semantics elsewhere.
- Side/menu rails use generic FA icons not tied to platform pillars.

Recommended system (Direction A, baseline):

| Nav Item | Icon | Class |
|---|---|---|
| Home | Home beacon | `fas fa-house` |
| Watch | Play stack | `fas fa-play-circle` |
| Creators | Creator group | `fas fa-user-group` |
| Community | Chat bubbles | `fas fa-comments` |
| Arcana | Crystal/spark | `fas fa-gem` or `fas fa-sparkles` |
| Explore | Compass | `fas fa-compass` |
| Signals (new discovery entry) | Lightning | `fas fa-bolt` |

Guideline:
- One icon family only (solid style) for consistency.
- Avoid brand icons for system navigation (e.g., no `fab` icon usage for core rails).

## 4) Homepage Identity Pass (Opportunities)

References:
- `docs/assets/platform-polish-sprint/homepage-desktop-after.png`
- `docs/assets/platform-polish-sprint/homepage-iphone-after.png`
- `docs/assets/platform-polish-sprint/channel-desktop-after.png`

## High-impact opportunities
1. Replace repeated maple leaf icon on all major rails with OFFKILTER system icons.
2. Give each rail a semantic icon and subtitle style tied to pillar intent.
3. Elevate Arcana and Signals headers with distinct but coherent accent colors.
4. Reduce visual duplication between adjacent rails (same cards repeated in sequence).
5. Standardize creator chip styling (avatar ring, name weight, metadata contrast).
6. Improve hierarchy between hero and first two discovery rails (Arcana + Signals).
7. Replace generic placeholders in any empty card/state with branded OFFKILTER fallback image.

## Identity issues visible now
- Repeated icon class creates “template clone” look.
- Heavy reliance on neutral dark cards without branded accent rhythm.
- Arcana feels present, but not yet visually “owned” by a custom system.

## 5) Color Recommendations

Current settings signal:
- Theme color slots are largely default/empty in `beeteam368_theme_options`.

Recommended token set (dark-first, consistent with current UI):

```css
--oktv-bg-0: #07090D;
--oktv-bg-1: #11151D;
--oktv-bg-2: #1A2230;
--oktv-text-0: #F3F6FB;
--oktv-text-1: #B8C1D1;
--oktv-line: #273247;

--oktv-brand: #E50914;      /* OFFKILTER core red */
--oktv-brand-alt: #FF3B30;  /* action/hover */

--oktv-arcana: #8B5CF6;     /* Arcana accent */
--oktv-signals: #00C2FF;    /* Signals accent */
--oktv-community: #22C55E;  /* Community accent */
--oktv-creators: #F59E0B;   /* Creator accent */
```

Usage rule:
- Keep cards dark and neutral.
- Use accent color only in headings, chips, and icon pills (not full backgrounds).

## 6) Typography Notes

Current perception issue:
- Headlines and metadata are readable but not branded enough.

Recommended hierarchy (no immediate font migration required):
- H1/H2 rail titles: semibold/700 with tighter tracking.
- Creator names: medium/600.
- Metadata row: smaller but higher contrast than current muted gray.
- Keep one sans-serif family for system UI; avoid mixing decorative display fonts in nav/cards.

If custom font is introduced later:
- Headline candidate: `Sora` or `Space Grotesk`.
- Body/system candidate: `Inter`.

## 7) Visual Examples (Icon + Header Patterns)

## Example A: Signals rail header
- Icon: `fas fa-bolt`
- Title: `⚡ Signals`
- Subtitle: `0-90 second updates`
- Accent: `--oktv-signals`

## Example B: Arcana rail header
- Icon: `fas fa-gem`
- Title: `🔮 The Arcana`
- Subtitle: `Premonitions · Outcomes · Readings`
- Accent: `--oktv-arcana`

## Example C: Creators rail header
- Icon: `fas fa-user-group`
- Title: `Featured Creators`
- Subtitle: `Active voices this week`
- Accent: `--oktv-creators`

## 8) Replacement Roadmap (No Production Changes in this Sprint)

## Stage 0 - Inventory lock (done)
- Capture current icon and placeholder usage.
- Freeze baseline screenshots.

## Stage 1 - Icon swap only (low risk)
- Replace `fab fa-canadian-maple-leaf` across homepage rails.
- Normalize widget icon classes in sidebar blocks.

## Stage 2 - Brand token pass (low/medium risk)
- Apply OFFKILTER color tokens to headers/chips/icon pills.
- Keep structure and widget logic unchanged.

## Stage 3 - Placeholder and badge polish (medium risk)
- Introduce branded fallback thumbnail/avatar/cover assets.
- Replace generic ARMember visual defaults where exposed publicly.

## Stage 4 - Navigation coherence pass (medium risk)
- Align top nav, side nav, and rail headers to same icon system.
- Add `Signals` as explicit discovery destination.

## Stage 5 - QA + rollback safety
- Validate desktop/mobile/tablet.
- Keep snapshots of Elementor JSON before each change.
- Rollback by restoring prior widget icon settings and style tokens.

## Recommendation
- Adopt **Direction A (Signal / Compass)** as the platform-wide base system.
- Layer **Direction C (Crystal / Arcana)** as a sectional accent for Arcana-specific surfaces.
- This gives OFFKILTER a unique identity without over-rotating the entire platform into one thematic niche.
