# Returning User Experience — Design (LC2 Phase 5, design only)

**Date:** 2026-07-02
**Status:** DESIGN DRAFT — not implemented. Per the LC2 brief ("draft only if backend work is required"), every feature here needs backend state that does not exist in the plugin or theme today, so this document defines the design and defers the build.

## Goal

Give a returning visitor an immediate reason to resume: pick up where they left off, see what the creators they care about published since their last visit, and reach their saved items in one click.

## What exists today (audit, plugin v2.9 + VidMov/beeteam368 theme)

- **No watch-progress tracking** — nothing records playback position or completed views per user.
- **No saved/bookmark system** in the plugin. (myCred and beeteam368 reaction/subscription systems exist on prod; none store per-user save lists for videos.)
- **Subscriptions exist in the theme** — beeteam368 has channel subscriptions (`beeteam368_subscribe_count` et al.), which is the natural backbone for "Following."
- **Pulse recency** — `[oktv_pulse_feed]` can filter by creator but has no "for the current user" variant.

## Feature designs

### 1. Continue Watching
- **Data:** user meta `okarcana_watch_progress` — bounded LRU map `post_id => {seconds, duration, updated_at}`, max 20 entries.
- **Write path:** small JS heartbeat on singular `vidmov_video` (throttled: every 15s + `visibilitychange`), REST endpoint `POST /okarcana/v1/progress` (nonce'd, logged-in only).
- **Read path:** shortcode `[oktv_continue_watching limit="6"]` — cards with resume position; excludes items >95% complete.
- **Effort:** S/M. Risk: player is theme-owned (beeteam368/videojs) — hooking the player API needs verification on prod.

### 2. Saved
- **Data:** user meta `okarcana_saved_posts` — ordered ID list, capped at 200.
- **Write path:** REST `POST/DELETE /okarcana/v1/saved/{post_id}`; a save toggle injected alongside the existing watch-next append block (data-layer, no theme edit).
- **Read path:** `[oktv_saved_list]` on a `/saved/` page (logged-in gate with a friendly logged-out CTA).
- **Effort:** S.

### 3. Following (feed of subscribed creators)
- **Backbone:** reuse beeteam368 subscriptions rather than building a parallel follow system. Discovery: enumerate the theme's subscription storage (usermeta/table — confirm on prod) and expose `okarcana_followed_creator_ids(user)`.
- **Read path:** `[oktv_following_feed limit="12"]` — latest published `vidmov_video`/`pulse_item` by followed creators, newest first, grouped by creator.
- **Effort:** M (depends on theme storage shape; must remain read-only toward theme data).

### 4. Recent Pulse (for the returning user)
- **Data:** reuse `okarcana_last_seen` user meta (set on login/heartbeat). "Recent" = pulse items since last visit by followed creators, else globally.
- **Read path:** extend `[oktv_pulse_feed]` with `since="last_visit"` + `scope="following"` attributes.
- **Effort:** S once (1) and (3) exist.

## Composition (the returning-user homepage row)

A single `[oktv_welcome_back]` orchestrator, rendered only for logged-in users with any state: Continue Watching (if any) → Following (if any) → Recent Pulse → Saved shortcut. Anonymous users see the standard editorial homepage — no layout shift.

## Sequencing recommendation

1. **Saved** (smallest, self-contained, immediate value)
2. **Continue Watching** (highest engagement value; needs player-hook verification first)
3. **Following feed** (depends on theme subscription storage audit)
4. **Recent Pulse** (composition of the above)

## Non-goals / guardrails

- No parallel follow system competing with theme subscriptions.
- No tracking for anonymous users (no fingerprinting/localStorage shadow profiles).
- All state additive user-meta — nothing touches theme/ARMember data (`beeteam368_membership_plans` untouched).
- Caps + LRU bounds on all lists (no unbounded meta growth).
