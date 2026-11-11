# OKTV availability recovery — November 11, 2026

## Outcome

The public site is available again. The existing healthguard restored PHP at
06:43 Pacific. The incident response then installed tested admission controls
at 06:57 Pacific to contain another distributed request surge before it fills
the PHP queue. Production release source: `0358786a53c6a3cdeba6a9ec019905a88f4cc227`.

## Verified evidence

- The browser displayed Cloudflare **502 Bad Gateway**, timestamped
  `2026-11-11 14:42:56 UTC`. Fresh browser navigation later loaded the actual
  homepage and the existing playback canary.
- The origin's timing log recorded 2,156 completed/aborted dynamic requests
  from 2,116 client addresses in the 14:39 UTC minute. The preceding minutes
  showed roughly 19–36 dynamic requests per minute. Completion timestamps are
  not an exact arrival-rate measurement.
- During that minute NGINX reported exhausted `worker_connections` at 1,024;
  PHP reported its eight-child limit reached. Expensive sorting/query variants
  and directory requests appeared among affected requests. The PHP slow log
  showed a WordPress database query waiting during the incident.
- Healthguard detected three consecutive failures, restarted **PHP only** at
  14:42:52 UTC, and reported recovery at 14:43:07. The brief missing PHP socket
  and upstream-reset messages align with that component restart. The operator
  did not reset the VM, restart the database, or trigger that recovery.
- The VM had memory and disk headroom. No OOM kill was found in the inspected
  kernel window. The origin certificate is currently valid through December 29,
  2026; the expired-certificate findings in the June incident reports are stale.

This supports distributed request overload as the incident mechanism. It does
not identify the people operating those clients or establish malicious intent.

## Applied repair

The existing per-client request limit remains in place. Shared budgets now
protect anonymous public PHP reads across all client addresses: six requests
per second, a ten-request burst and twelve simultaneous requests. Anonymous
`sort_by` / `random_query` variants additionally share two requests per second,
a four-request burst and four simultaneous requests. Other PHP work has a
24-request ceiling. The cron HTTP endpoint has a one-request in-flight limit.
Excess traffic receives **429**, keeping it out of the application queue.

NGINX's worker connection allowance increased from 1,024 to 4,096, within the
observed process file limit. PHP remains at eight workers. Static assets keep
their existing direct path. Cookie presence preserves the signed-in path but
is not an authentication check or a complete denial-of-service defense.

The cron limit applies to HTTP requests, not every background task's complete
lifetime: PHP can finish the HTTP response while continuing a task. WordPress's
own cron lock remains responsible for task execution coordination.

Route-family and admission-result logs omit query strings, cookies,
authorization headers, request bodies and account identifiers. Configuration
originals, source hashes and receipts are retained in a root-only recovery
directory outside the webroot. Deployment tested the full live configuration,
then reloaded NGINX gracefully; PHP and the database stayed running.

## Validation and limits

- Eleven isolated checks used the installed NGINX binary, loopback-only
  listeners and synthetic upstreams. Distributed query bursts received bounded
  200/429 responses while an ordinary read retained capacity. Static files,
  normal sorting, the signed-in path and nested management includes passed.
- The first full-configuration attempt caught an exact-location conflict in
  Bitnami's nested management include. Automatic rollback completed before
  activation. The final release uses a URI-based key and passed the same nested
  include structure and production configuration check.
- After deployment, two rounds across eight public routes returned **200**:
  homepage, Watch, video canary, Creators, Community, login, REST index and bare
  domain canonicalization. External management access remained **403**.
- The browser rendered the homepage and played the existing video canary.
- At 14:59 UTC the installed hashes matched the release. The first 74 logged
  post-change requests contained no 5xx responses and no requests over ten
  seconds. This is a short observation window, not production load acceptance.
- The healthguard timer stayed active and reported failcount zero. No content,
  account, plugin, media-import, authentication or billing changes were part of
  this incident repair.

For the deployed configuration, qualification and reversible installation
procedure, see `deployment/traffic-recovery/README.md`.
