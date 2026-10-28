#!/usr/bin/env bash
# Owner-approved private integration source only. Never a production backup/deploy.
set -euo pipefail
umask 077
SNAPSHOT=$(mktemp -d /private/tmp/oktv-production-source.XXXXXX)
mkdir -m 700 "$SNAPSHOT/files"
printf '%s\n' OKTV_PRIVATE_APPROVED_SOURCE > "$SNAPSHOT/.oktv-source"

# Exact source families approved for local integration, plus public dependencies.
# No WP root, database, uploads, options, sessions, backups or live configuration.
gcloud compute ssh oktv-main-deployment-vm \
  --project=offkilter-tv --zone=us-west1-a --tunnel-through-iap --quiet \
  --command='sudo bash -s' > "$SNAPSHOT/source.tar.gz" 2> "$SNAPSHOT/capture.log" <<'REMOTE'
set -euo pipefail
cd /opt/bitnami/wordpress/wp-content
roots=(themes/vidmov plugins/beeteam368-extensions plugins/beeteam368-extensions-pro
  plugins/offkilter-arcana plugins/bunnycdn plugins/uk-cookie-consent
  plugins/elementor plugins/elementor-pro plugins/armember plugins/armembercommunity
  plugins/armember-membership plugins/wpforo plugins/cmb2 plugins/redux-framework
  plugins/all-in-one-seo-pack)
for directory in "${roots[@]}"; do test -d "$directory" && test ! -L "$directory"; done
find "${roots[@]}" \
  -type d \( -name uploads -o -name cache -o -name caches -o -name logs -o -name backups -o -name backup -o -name .git -o -name node_modules \) -prune -o \
  -type f ! -name '.*' ! -iname 'wp-config*' ! -iname '*credentials*' ! -iname '*secret*' \
  ! -iname '*.key' ! -iname '*.pem' ! -iname '*.p12' ! -iname '*.pfx' ! -iname '*.crt' \
  ! -iname '*.sql' ! -iname '*.sqlite*' ! -iname '*.db' ! -iname '*.log' ! -iname '*.bak' \
  ! -iname '*.zip' ! -iname '*.tar' ! -iname '*.gz' ! -iname '*.7z' ! -iname '*.env*' \
  -print0 | tar --null --no-recursion -czf - -T -
REMOTE

gzip -t "$SNAPSHOT/source.tar.gz"
tar -tzf "$SNAPSHOT/source.tar.gz" > "$SNAPSHOT/archive-paths.txt"
tar -tvzf "$SNAPSHOT/source.tar.gz" > "$SNAPSHOT/archive-types.txt"
# Only regular files beneath the exact roots may be extracted. No links/devices.
if grep -Ev '^-' "$SNAPSHOT/archive-types.txt" >/dev/null; then
  echo 'Non-regular archive member: refusing extraction.' >&2; exit 1
fi
if grep -E '(^/|(^|/)\.\.(/|$)|\\)' "$SNAPSHOT/archive-paths.txt" >/dev/null; then
  echo 'Unsafe archive member path: refusing extraction.' >&2; exit 1
fi
if grep -Ev '^(themes/vidmov|plugins/(beeteam368-extensions|beeteam368-extensions-pro|offkilter-arcana|bunnycdn|uk-cookie-consent|elementor|elementor-pro|armember|armembercommunity|armember-membership|wpforo|cmb2|redux-framework|all-in-one-seo-pack))/' "$SNAPSHOT/archive-paths.txt" >/dev/null; then
  echo 'Archive exceeded the source allowlist: refusing extraction.' >&2; exit 1
fi
tar --no-same-owner --no-same-permissions -xzf "$SNAPSHOT/source.tar.gz" -C "$SNAPSHOT/files"
(cd "$SNAPSHOT/files" && find . -type f -exec shasum -a 256 {} \;) > "$SNAPSHOT/files.sha256"
shasum -a 256 "$SNAPSHOT/source.tar.gz" > "$SNAPSHOT/archive.sha256"
date -u '+%Y-%m-%dT%H:%M:%SZ' > "$SNAPSHOT/captured-at.txt"
printf '%s\n' "$SNAPSHOT" > /private/tmp/oktv-production-source-current-path
printf 'Private approved source captured: %s\nFiles: ' "$SNAPSHOT"
wc -l < "$SNAPSHOT/archive-paths.txt"
echo 'No database, uploads, keys, wp-config, logs or backup archives copied. Keep this directory private and outside Git.'
