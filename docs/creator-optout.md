# Creator Opt-Out & "Why am I on OFFKILTER?"

Date: 2026-06-29
Plugin version: `2.1.0`
Class: `includes/class-okarcana-creator.php` (`OKArcana_Creator`)

---

## Purpose

Some creators whose videos surface on OFFKILTER (selected by Arcana) will want to
know **why they're here** and have a way to **control or remove** their content.
This adds three things:

1. Reimagined footer brand messaging (replaces the legacy `© 2023 OFFKILTER.TV`).
2. A "Why am I on OFFKILTER?" explainer page.
3. A working creator opt-out request form.

The guiding message: *life has a peculiar way of informing and connecting us all* —
content surfaced because a human felt it belonged, and the creator always decides.

---

## Shortcodes

### `[oktv_footer_brand]`
Footer brand block. Embed in the Elementor footer template.

| Attribute | Default |
|---|---|
| `tagline` | "Discover. Create. Discuss. Return." |
| `creator_href` | "/why-am-i-here/" |
| `wrapper_class` | "" |

Renders: OFFKILTER wordmark, tagline, one-line brand statement, pillar links,
a "Creators: Why am I on OFFKILTER?" link, and a dynamic-year copyright line
(`current_time('Y')` — no more hard-coded 2023).

### `[oktv_why_creator]`
The "Why am I on here?" page body. Includes the opt-out form by default.

| Attribute | Default |
|---|---|
| `headline` | "Why am I on OFFKILTER?" |
| `show_form` | `1` (set `0` to show explainer only) |
| `wrapper_class` | "" |

### `[oktv_creator_optout]`
The opt-out request form (standalone, or embedded automatically by `[oktv_why_creator]`).

| Attribute | Default |
|---|---|
| `title` | "Opt out of OFFKILTER" |
| `wrapper_class` | "" |

Form fields: YouTube channel URL/handle (required), email (required), creator name,
message, ownership confirmation checkbox (required), honeypot (anti-spam).

---

## How submissions are handled

- Form posts to `admin-post.php` with action `okarcana_creator_optout` (nonce-protected,
  honeypot-guarded). Works for logged-out visitors (`admin_post_nopriv`).
- Each valid submission is stored as a **private `ok_creator_optout` post** — review them
  in **WP Admin → Opt-Out Requests** (shield icon). Channel/email/name/timestamp are saved
  to post meta.
- The site admin (`get_option('admin_email')`) is emailed on every submission.
- The visitor is redirected back with a success or error notice (`?ok_optout=received|error`).

No content is auto-deleted. The form raises a **request**; the operator verifies ownership
and actions it.

---

## Operator setup

1. **Create the page.** WP Admin → Pages → Add New. Title: "Why am I here?" (slug `why-am-i-here`).
   Body:
   ```
   [oktv_why_creator]
   ```
2. **Footer.** In the Elementor footer template, replace the legacy copyright block with an
   HTML widget:
   ```
   [oktv_footer_brand]
   ```
   (If the page slug differs, pass it: `[oktv_footer_brand creator_href="/creators/opt-out/"]`.)
3. **Review inbox.** Watch **Opt-Out Requests** in wp-admin and the admin email.
4. **Verify deliverability.** Confirm `wp_mail` is configured (SMTP plugin / provider) so
   opt-out notifications actually arrive.

---

## Roadmap — automated Google (YouTube/Gmail) verification

Today, ownership is confirmed manually from the channel + email provided. The intended
next step is **Google sign-in (YouTube/Gmail OAuth)** so a creator can verify ownership
automatically and self-manage their content:

1. Add Google OAuth via an ARMember social-login add-on or `nextend-social-login`
   (auth layer — independent of this plugin). Ties into the "Google Identity readiness"
   note in `docs/platform-v2-product-review.md`.
2. On verified sign-in, match the Google account's YouTube channel to imported content
   and let the creator toggle visibility / opt out without manual review.
3. Replace the form's "coming soon" fineprint with a live "Verify with Google" button.

The current form is the foundation; the CTA copy already sets the expectation.
