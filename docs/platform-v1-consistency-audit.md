# OFFKILTER Platform v1.0 — Consistency Audit

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Method: Route-by-route review against "Does this feel OFFKILTER or VidMov?"

## Scoring Key

| Score | Meaning |
|---|---|
| 1–3 | Clearly feels like a VidMov/WordPress template |
| 4–6 | Mixed — some OFFKILTER identity, some theme residue |
| 7–8 | Mostly OFFKILTER, minor rough edges |
| 9–10 | Unmistakably OFFKILTER, polished and intentional |

---

## Page 1 — Homepage (`/`)

**Score: 7/10**

**What feels OFFKILTER:**
- Arcana, Signals, Featured Creators, Watch Now rails with OFFKILTER-themed labels and icons
- Rail icons from Signal/Compass family (`fas fa-gem`, `fas fa-bolt`, etc.) — maple leaf removed
- Social share bar hidden above-the-fold
- Content hierarchy places Arcana + Signals first

**What still feels like VidMov:**
- Top chrome (header logo area + dual nav row) has default VidMov spacing
- Category pill colors on video cards are random VidMov auto-assigns
- Footer reads `© 2023 OFFKILTER.TV`
- Rail section title accent bar added by CSS (good), but VidMov structural padding around titles remains uneven

**Fixed by this sprint:** Section title accent bar, mobile header tightening, content card hover states
**Operator task remaining:** Footer year, category pill normalization

---

## Page 2 — Signals Archive (`/video-category/signals/`)

**Score: 6/10**

**What feels OFFKILTER:**
- Route itself is intentional (`/video-category/signals/` aliased to `/signals/`)
- Plugin shortcode `[oktv_signals_latest]` available for embedding
- Signal classification data exists in postmeta

**What still feels like VidMov:**
- Archive page header has no OFFKILTER accent treatment (now fixed via CSS Section C)
- Category description is empty — blank description renders as anonymous archive
- Video cards lack Signals-specific visual treatment (duration badge, signal pill)
- No "this is a fast clip" design signal — looks like a regular video archive

**Fixed by this sprint:** Archive header accent bar (`--ok-signal` blue), card duration badge styling, `[oktv_signals_latest layout="cards"]` available as editorial tool
**Remaining:** Add archive description via WP taxonomy settings; embed signals shortcode on Signals page; category pill normalization

---

## Page 3 — Arcana Archive (`/video-category/arcana/`)

**Score: 7/10**

**What feels OFFKILTER:**
- Route aliases active (`/arcana/` redirects properly)
- Arcana content is surfaced correctly with Premonitions / Outcomes sub-categories
- Disclaimer block on arcana_entry posts is branded (no inline styles)

**What still feels like VidMov:**
- Archive header lacks Arcana accent (now fixed via CSS Section C — purple `--ok-arcana`)
- Archive page itself uses generic VidMov category template
- Card thumbnails with no image show generic placeholder (now partially addressed via identity filter)

**Fixed by this sprint:** Archive header purple accent, card placeholder via `OKArcana_Identity` filter
**Remaining:** Upload rich Arcana category description in WP taxonomy admin; Arcana-specific empty state on card thumbnails

---

## Page 4 — Trending / Explore (`/trending/`)

**Score: 5/10**

**What feels OFFKILTER:**
- Surfaced in nav as `Explore` (not just `Trending`) — correct naming

**What still feels like VidMov:**
- Page itself uses VidMov's default trending template with no OFFKILTER treatment
- Section headers have no accent bar (now improved via CSS section title rules)
- No OFFKILTER editorial voice — just a sorted video feed
- Generic metadata layout on cards

**Fixed by this sprint:** Section title accent bar, card hover states
**Remaining:** Add editorial framing (brief description or label) via Elementor block on the trending page

---

## Page 5 — Creator Directory / Member List (`/member-list/`)

**Score: 6/10**

**What feels OFFKILTER:**
- Populated grid of creators — the content itself is real
- Correct nav label (`Creators`)

**What still feels like VidMov:**
- Grid layout is VidMov default — no OFFKILTER card treatment
- Avatar rings are generic (now partially addressed via CSS creator avatar rules)
- Hash-like creator handles (`@cea018...`) visible in grid — trust erosion
- No creator badges or Signals/Arcana affiliation indicators
- Empty state (if no creators) is generic

**Fixed by this sprint:** Avatar ring hover state, default avatar fallback via identity filter
**Remaining:** Clean up hash-like handles (operator task); add creator pills (Arcana, Signals) via taxonomy; unify with `/channel/`

---

## Page 6 — Creator Channel (`/channel/channel-id/@username/`)

**Score: 5/10** (without profile data) / **7/10** (with completed profile)

**What feels OFFKILTER:**
- Route structure is functional, channel URL handles work for priority creators
- Plugin identity filter will apply branded avatar fallback for missing avatars

**What still feels like VidMov:**
- Empty banner → large blank space (identity filter + CSS default-banner rule now addresses this)
- Missing avatar → grey silhouette (identity filter now addresses this)
- Channel page tabs (`Videos`, `About`, `Discussion`, `Reacted`) use VidMov default styling
- Creator name hierarchy unclear — no strong visual separation between name, handle, and stats
- No bio on most creator pages — page reads as abandoned

**Fixed by this sprint:** Default channel banner CSS, avatar sizing/ring, channel tab CSS, stats row styling, creator spotlight shortcode available
**Remaining:** Upload actual avatars/banners for top 5 Arcana creators (operator task); add bios; attach `vidmov_user_profile`

---

## Page 7 — Video Detail (`/video/[slug]/`)

**Score: 6/10**

**What feels OFFKILTER:**
- Video player renders via VidMov — functional
- Creator affiliation visible in post metadata

**What still feels like VidMov:**
- Related videos section uses VidMov's default "more like this" with no OFFKILTER curation logic
- Post meta below player is dense and lacks hierarchy (now improved via `entry-content` CSS rules)
- Arcana disclaimer block is the only branded element — and it's below the fold
- No Signals/Arcana category pill visible on video detail page

**Fixed by this sprint:** Entry content rhythm, blockquote accent, disclaimer CSS class treatment
**Remaining:** Add Arcana/Signals pill to video detail template (requires child theme or Elementor single template); curator note on Arcana videos

---

## Page 8 — Community (`/community/`)

**Score: 5/10**

**What feels OFFKILTER:**
- Route exists, forum accessible at `/community/`

**What still feels like VidMov/wpForo:**
- wpForo default styling has no OFFKILTER design language
- Forum header, thread list, reply UI all use wpForo theme defaults
- No connection between community discussions and the Arcana/Signals content being discussed

**Fixed by this sprint:** Nothing direct (wpForo CSS customization is a separate scope)
**Remaining:** wpForo child theme or custom CSS (Community visual identity sprint — scoped for v1.1)

---

## Page 9 — Search Results

**Score: 4/10**

**What feels OFFKILTER:**
- Functional — returns results

**What still feels like VidMov:**
- Search results page uses VidMov default template
- No OFFKILTER section header or empty state
- Results are not organized by content type (Signals vs Videos vs Arcana)

**Fixed by this sprint:** Partial — card hover states and section title improvements apply
**Remaining:** Search results page redesign is a v1.1 scope item

---

## Page 10 — Archives / Categories (general)

**Score: 6/10**

**What feels OFFKILTER:**
- Routes are clean and functional
- Arcana sub-categories (Premonitions, Outcomes) are surfaced

**What still feels like VidMov:**
- Generic archive template with no editorial header or platform framing
- `youtube` and `oktv-ftv` legacy category archives still public and accessible from some paths
- Term descriptions empty for most categories

**Fixed by this sprint:** Archive header CSS with accent treatment, body-class scoping for Signals/Arcana
**Remaining:** Add term descriptions via WP admin; deprecate/hide `youtube`/`oktv-ftv` archives from public discovery

---

## Page 11 — Authentication Pages (`/main-login/`, `/main-register/`)

**Score: 4/10**

**What feels OFFKILTER:**
- ARMember handles auth — functional

**What still feels like VidMov/ARMember:**
- Login/register forms use ARMember default styling
- Duplicate route variants (`/login/`, `/login-2/`, `/register/`, `/register-2/`) still exist
- No OFFKILTER branding on auth pages

**Fixed by this sprint:** Nothing direct
**Remaining:** ARMember custom theme or CSS injection; remove duplicate auth routes (operator task)

---

## Summary Table

| Page | Score | Sprint Delta | Operator Tasks |
|---|---|---|---|
| Homepage | 7/10 | +1 (section accent, card hover) | Footer year, pill colors |
| Signals Archive | 6/10 | +1 (accent header, cards shortcode) | Archive description, shortcode embed |
| Arcana Archive | 7/10 | +1 (accent header, placeholder filter) | Category description |
| Trending/Explore | 5/10 | +0.5 (section titles) | Editorial framing block |
| Creator Directory | 6/10 | +1 (avatar ring, fallback) | Handle cleanup, creator pills |
| Creator Channel | 5–7/10 | +1.5 (banner CSS, avatar CSS, spotlight) | Profile completions |
| Video Detail | 6/10 | +0.5 (content rhythm) | Arcana pill on video page |
| Community | 5/10 | 0 | wpForo CSS sprint (v1.1) |
| Search | 4/10 | +0.5 (card states) | Search redesign (v1.1) |
| Archives | 6/10 | +1 (archive header CSS) | Term descriptions |
| Auth Pages | 4/10 | 0 | ARMember CSS, route dedup |

**Platform average before sprint:** ~5.5/10
**Platform average after sprint:** ~6.3/10
**Target for v1.1:** 8+/10

---

## Highest-Impact Remaining Work (Post-Sprint)

1. Creator profile completions (avatar + banner + bio) — 5 creators → +1.5 average score
2. wpForo Community CSS sprint → +1 for community page alone
3. Video detail Arcana/Signals pill → +1 for video detail
4. Search results redesign → +2 for search
5. ARMember auth page branding → +1 for auth pages
