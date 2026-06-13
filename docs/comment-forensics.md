# Comment Forensics

Date: 2026-06-13

## System In Use
- Stored in standard WordPress comments table: `wp_comments`
- `comment_type`: `comment`

## Are YouTube Comments Imported?
Yes, when enabled by campaign options.

Live test campaign `8092` output included:
- `Trying to post comments`
- `Found 29 comment to post`

Database verification for imported post `8093`:
- multiple records inserted in `wp_comments`
- author pattern matches YouTube handles (`@...`)
- content and timestamps correspond to YouTube comments

## Conclusion
Imported videos use WordPress comments storage, and WP Automatic can ingest YouTube comments into that system.
No separate VidMov-only comments table was required for this behavior.
