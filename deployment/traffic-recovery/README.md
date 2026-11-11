# Distributed request surge recovery

This configuration bounds dynamic work before it enters WordPress. Per-client
limits alone cannot protect the eight-worker PHP pool from thousands of clients.

The anonymous public-read budget is six requests/second with a ten-request burst
and twelve simultaneous requests. Anonymous `sort_by` / `random_query` variants
additionally share two requests/second, a four-request burst and four simultaneous
requests. Every non-cron PHP path has a separate 24-request ceiling; WordPress
cron admits one HTTP request at a time. Rejected work receives HTTP 429. These
are capacity guardrails, not authentication or a complete denial-of-service
defense. Cookie presence preserves the existing signed-in path; it does not
establish a verified identity. Static assets do not consume PHP budgets.

Keep the existing Cloudflare real-IP rules and per-client `oktv_dynamic` zone.
Install `01-oktv-admission-http.conf` in the existing `server_blocks` include
directory and replace only the reviewed `bitnami/php-fpm.conf`. Change the main
NGINX worker connection setting from 1,024 to 4,096 after confirming its process
file-descriptor limit supports this. This does not increase PHP worker count,
database limits or hosting costs.

Before changing live files, save exact originals outside the webroot with their
hashes. Require byte-exact current hashes, stage the candidate configuration,
run the installed NGINX configuration test, and reload NGINX gracefully. Restore
the exact originals immediately if the test or reload fails. The existing PHP
and database processes remain running.

Validate the budgets on a separate loopback-only NGINX instance with synthetic
upstreams, then check the actual HTTPS homepage, watch archive, video, creator,
community, login, API and canonical redirects. Confirm the existing recovery
timer still reports healthy. A single successful page is insufficient to
establish stability after a surge; inspect post-change timing logs and repeat
public probes. Avoid a production load test during recovery.
