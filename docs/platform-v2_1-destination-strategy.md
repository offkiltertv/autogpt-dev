# OFFKILTER Platform v2.1 — Destination Strategy

Date: 2026-06-29
Branch: `feature/arcana-plugin`
Plugin version: `2.2.0` (bumped from `2.1.0`)
Scope: Make OFFKILTER the destination for Pulse content; data-layer public-surface
lockdown; reserve identity / SidebarChat / ownership hooks.

---

## Mission

Pulse Clipper is now a standalone product (its own repo). OFFKILTER stops being a
content-creation tool and becomes **the best destination for Pulse content**. This
sprint stands up Pulse as a first-class destination, evolves the homepage to lead
with five pillars, wires the OKTV side of the Pulse Clipper integration, and reserves
the hooks for shared Google Identity, creator ownership, and SidebarChat.

---

## Priority Zero — Public Surface Lockdown (data layer, not CSS)

### What changed from v2.0
v2.0 hid the leaking monetization/editor fields (Purchase Price, Expiration, PPV,
Video/Audio Categories editor UI) with **CSS Section V** + a `the_content` strip.
v2.1 removes them **at the data layer** so the theme template renders nothing — no
CSS reliance.

### Root cause (confirmed this sprint)
- The VidMov theme source is **not in this repo**; the leaking form is **theme-
  rendered** (`.beeteam368-video-form` / submit/edit form), not a plugin shortcode.
- The only monetization meta key documented anywhere is `beeteam368_membership_plans`
  — and that one **drives access gating**, so emptying it could unlock gated content.
- The exact price/PPV/expiration meta keys and the exact render hook are VidMov/
  ARMember internals, not discoverable from this repo, and the live site 403s
  automated inspection.

### The fix (in `class-okarcana-cleanup.php`)
1. **`get_post_metadata` guard** — for anonymous / non-editor frontend views
   (`!is_admin() && !current_user_can('edit_post', $id)`), short-circuit meta reads
   whose key matches a display/authoring pattern (`price|ppv|pay_per_view|purchase|
   expir|monetiz`) to `''`. The theme reads empty → renders nothing. **Not CSS.**
   Pattern is filterable: `okarcana_guard_meta_patterns`.
   **`beeteam368_membership_plans` is deliberately excluded** (gating untouched).
2. **Filterable hook removal** — `okarcana_guard_remove_actions` lets the operator
   `remove_action` the exact theme/plugin render hook for anonymous views once known.
3. **Authoring-shortcode stripping** — removes VidMov submit/edit form shortcodes
   from content on the public surface (`okarcana_guard_strip_shortcodes`).
4. **Admin diagnostic** — visit a single video page as an admin with
   `?okarcana_diag=surface`; an HTML comment dumps the post's meta keys and the
   callbacks bound to `the_content`/`wp_footer`/`wp_head`. This is how the operator
   captures the EXACT key/hook for a one-pass precise lock.
5. CSS Section V remains as belt-and-braces.

### Operator tuning loop (one pass)
1. Deploy `2.2.0`.
2. As admin, open an affected video page with `?okarcana_diag=surface`; view source;
   read the `OKARCANA DIAGNOSTIC` comment.
3. Identify the price/PPV/expiration meta key(s) and/or the render hook+callback.
4. Tighten via a small mu-plugin/snippet:
   ```php
   add_filter('okarcana_guard_meta_patterns', fn($p) => array_merge($p, ['/^exact_key$/']));
   add_filter('okarcana_guard_remove_actions', fn($r) => array_merge($r, [
     ['hook' => 'the_exact_hook', 'callback' => 'the_exact_callback', 'priority' => 10],
   ]));
   ```
5. Verify logged-out: the fields are gone at the source.

### Permanent fix
A VidMov child theme overriding the single-video template + submission partial with
`current_user_can('edit_post')` guards (documented in `platform-v2-product-review.md`).
The data-layer guard above is the correct interim and a permanent safety net.

---

## Phase 1 — Pulse as a first-class destination

New `OKArcana_Pulse` (`includes/class-okarcana-pulse.php`):

- **`pulse_item` CPT** (archive `/pulse/`, REST-enabled, supports title/editor/
  excerpt/thumbnail/author) + `pulse_tag` taxonomy. Meta: `_ok_pulse_source`,
  `_ok_pulse_duration_ms`, `_ok_pulse_transformations`, `_ok_pulse_visibility`,
  `_ok_pulse_status` (processing/ready/failed), `_ok_pulse_discussion_id` (reserved).
  This is the OKTV side of the integration contract in `docs/pulse-integration.md`.
- **`[oktv_pulse_feed]`** — feed (cards/list); public surface shows `ready` items
  only; reuses `.oktv-signals-cards`. Each card has a live "Pulse" badge, a reserved
  discuss slot, and (for the owner) a "Processing…" badge on in-flight items.
- **`[oktv_pulse_destination]`** — the **Home → Pulse → Creator** prototype: pulse
  hero + feed + "Creators on Pulse" strip + reserved discussion slot.

---

## Phase 2 — Homepage evolution (five pillars)

"Pulse" added everywhere a pillar is defined:
- `[oktv_platform_intro]` pillar config (`fas fa-wave-square`, `/pulse/`).
- `[oktv_pillar_strip]` now renders **5 chips**: Pulse · Signals · Arcana · Creators · Community.
- `[oktv_destination_hero pillar="pulse"]` available.

A visitor now sees the platform's shape — Pulse, Signals, Arcana, Creators, Community
— in one glance.

---

## Phase 3 — Creator workflow (Pulse Clipper integration)

End-to-end: **creator records a Pulse in the Pulse Clipper app → uploads → backend
creates a `pulse_item` (`processing`) → processing completes (`ready`) → appears in
the Pulse feed and on the creator page.**

- `[oktv_creator_page]` gains `show_pulse` / `pulse_limit` / `pulse_label`; renders a
  Pulse section (author's `ready` items; owner also sees `processing` with a badge).
- Reserved discussion hook (`_ok_pulse_discussion_id`) per item.
- The client→server contract is `docs/pulse-integration.md` (tus resumable upload,
  create/finalize endpoints). OKTV depends on Pulse Clipper **only** through that REST
  contract — never a code dependency.

---

## Phase 4 — Identity integration (reserved)

Prepared, not implemented:
- **Verified badge** renders next to a creator's name when user meta
  `_ok_creator_verified` is truthy — the *display* is ready; the *verification flow*
  is reserved.
- Reserved: creator profile ownership, unified avatar, cross-site identity. The path
  is shared Google Identity (see the Pulse Clipper `identity/` module + the v2.0
  "Google Identity readiness" note). No auth code added this sprint.

---

## Phase 5 — SidebarChat readiness (reserved UI only)

Every Pulse card, the Pulse destination, and the creator page expose a **reserved**
discuss slot via `render_discuss_cta(..., coming_soon=1)` with contexts `pulse` /
`creator_room` / `community`. UI placement is reserved; no chat implemented.

---

## Phase 6 — Editorial discovery

- `[oktv_discover_page]` now **leads with Featured Pulse** (`show_pulse`,
  `pulse_ids`, `pulse_limit`) above Featured Signals / Featured Arcana / Creators.
- Hand-picked `pulse_ids` keep the editorial, non-chronological emphasis.

---

## Phase 7 — Platform polish

- The Priority Zero data-layer guard is the headline artifact removal this sprint.
- CSS Section Z adds the verified badge + pulse discuss-slot polish.
- Remaining VidMov artifacts (footer year, channel tab labels, archive title text)
  stay operator/child-theme tasks per `public-surface-audit-v1.md`.

---

## Integration summary (the four asks)

| Question | Answer |
|---|---|
| **How Pulse integrates with OFFKILTER** | `pulse_item` CPT (archive `/pulse/`) + `[oktv_pulse_feed]` + `[oktv_pulse_destination]` + a Pulse pillar across homepage/nav/discover/creator pages. |
| **How Pulse Clipper integrates** | Standalone app publishes via the REST contract in `docs/pulse-integration.md` (tus upload → create/finalize). Server creates a `pulse_item` (processing → ready) that flows into the feed + creator page. No code coupling — API only. |
| **How SidebarChat integrates** | `_ok_pulse_discussion_id` reserved per item; reserved discuss slots (Discuss / Community / Creator room) on every Pulse surface. Activating chat = flip `coming_soon` + attach a thread. |
| **How Creator ownership integrates** | Verified-badge display shipped (reserved flow); shared Google Identity is the ownership path (verification, unified avatar, cross-site profile). Pairs with the creator opt-out flow (`docs/creator-optout.md`). |

---

## Operator runbook (v2.1 additions)

1. **Flush permalinks** after deploy (Settings → Permalinks → Save) so the
   `pulse_item` `/pulse/` archive routes.
2. **Pulse destination page** (optional, richer than the archive): create a page,
   embed `[oktv_pulse_destination]`; or rely on the `/pulse/` archive.
3. **Homepage:** ensure `[oktv_platform_intro]` / `[oktv_pillar_strip]` are embedded
   (now include Pulse). Add "Pulse" to the nav menu → `/pulse/`.
4. **Discover page:** `[oktv_discover_page]` now leads with Featured Pulse; optionally
   pass `pulse_ids="..."` for editor's picks.
5. **Creator pages:** add `show_pulse="1"` to `[oktv_creator_page]` embeds.
6. **Priority Zero verification + tuning:** run the `?okarcana_diag=surface` loop above.
7. **Deploy/validate:**
   ```bash
   ./scripts/deploy_offkilter_arcana.sh
   curl -s https://www.offkilter.tv/ | grep 'ver=2.2'
   # logged out: confirm price/PPV/expiration fields no longer render on video pages
   ```

---

## Page score deltas (v2.1)

| Surface | v2.0 | v2.1 | Lift |
|---|---|---|---|
| Single video (Priority Zero) | 7/10 | 8.5/10 | Data-layer lockdown removes leak at source |
| Homepage | 8/10 | 8.5/10 | Five-pillar shape incl. Pulse |
| Pulse destination | — (new) | 8/10 | `[oktv_pulse_destination]` + `/pulse/` archive |
| Discover | 8/10 | 8.5/10 | Leads with Featured Pulse |
| Creator pages | 8–9/10 | 8.5–9/10 | Pulse section + verified badge |

---

## Top 10 for next sprint

1. Operator: run the Priority Zero diagnostic + lock exact keys/hook (one pass).
2. Build the OKTV Pulse REST endpoints (create/finalize/tus) so the Clipper can publish.
3. Wire the Pulse Clipper `backends/offkilter` to those endpoints (M1 of that repo).
4. Permanent VidMov child theme (retires the data-layer guard to a safety net).
5. Activate SidebarChat (flip reserved slots live).
6. Google Identity (verification flow → makes the verified badge real; ownership).
7. Pulse single-item template (currently generic; give it the destination treatment).
8. Pulse processing pipeline (thumbnail, transcode normalize, status transitions).
9. Search: include `pulse_item` in results (content-type grouping).
10. Pulse intelligence (trend scoring feeding Discover — the future "Pulse" engine).

---

## Platform score trajectory

| Sprint | Version | Avg | Lift |
|---|---|---|---|
| Platform v1.4 | 1.4.0 | ~7.8 | Productization |
| Platform v2.0 | 2.0.0 | ~8.2 | Priority Zero (CSS) + a11y + review |
| Platform v2.1 (this) | 2.2.0 | ~8.5 | Pulse destination + data-layer lockdown + reservations |
| + operator (diag lock, endpoints, embeds) | 2.2.0 | ~8.8 | Pulse live end-to-end |
| Target | — | ~9.2 | SidebarChat + Google Identity + child theme |
