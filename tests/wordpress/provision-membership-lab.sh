#!/usr/bin/env bash
# Clone only a marked synthetic lab; keep licensed source private and off the network.
set -euo pipefail
umask 077
ROOT=$(cd "$(dirname "$0")/../.." && pwd -P)
BASE=$(cd "${1:?Pass the existing synthetic selected-stack lab}" && pwd -P)
SOURCE=${2:?Pass the approved private source}
RELEASE=${3:?Pass the private playback-only package}
ID=${4:?Pass its independently reviewed release ID}
case "$BASE" in /private/tmp/oktv-vidmov-integration.*) ;; *) echo 'Expected selected synthetic lab.' >&2; exit 1;; esac
[[ $(cat "$BASE/.oktv-lab") == OKTV_DISPOSABLE_LOCAL_LAB ]]
[[ -f "$BASE/private/manifest.json" && -f "$BASE/database/.ht.sqlite" ]]
[[ ! -f "$BASE/database/.ht.sqlite-wal" && ! -f "$BASE/database/.ht.sqlite-journal" ]]
if lsof -nP -iTCP:8793 -sTCP:LISTEN >/dev/null 2>&1 || lsof -nP -iTCP:8794 -sTCP:LISTEN >/dev/null 2>&1; then
  echo 'Stop the verified local labs before cloning.' >&2; exit 1
fi
[[ -z $(find "$BASE/wordpress" "$BASE/database" -type l -print -quit) ]]
[[ $(df -k /private/tmp | awk 'END {print $4}') -ge 1048576 ]]
php "$ROOT/tests/wordpress/approved-source.php" "$SOURCE" >/dev/null
php "$ROOT/deployment/playback-release/package.php" verify "$RELEASE" "$ID" >/dev/null
[[ -x /usr/bin/sandbox-exec && -f "$SOURCE/files/plugins/armember/armember.php" ]]
/usr/bin/sandbox-exec -f "$ROOT/tests/wordpress/membership-lab.sb" /usr/bin/true
LAB=$(mktemp -d /private/tmp/oktv-membership-integration.XXXXXX)
printf '%s\n' OKTV_DISPOSABLE_LOCAL_LAB > "$LAB/.oktv-lab"
printf '%s\n' OKTV_MEMBERSHIP_SYNTHETIC_ONLY > "$LAB/.oktv-membership-lab"
mkdir -m 700 "$LAB/private"
trap 'echo "Membership lab not qualified; private diagnostics retained in $LAB. No server started." >&2' ERR
cp -R "$BASE/wordpress" "$BASE/database" "$LAB/"
cp "$BASE/private/manifest.json" "$LAB/private/manifest.json"
cp "$RELEASE/release.json" "$LAB/private/playback-release.json"
mv "$LAB/wordpress/wp-content/plugins/offkilter-arcana" "$LAB/private/broad-candidate-not-active"
cp -R "$RELEASE/offkilter-arcana" "$LAB/wordpress/wp-content/plugins/"
for plugin in armember-membership armember; do
  [[ ! -e "$LAB/wordpress/wp-content/plugins/$plugin" ]]
  cp -R "$SOURCE/files/plugins/$plugin" "$LAB/wordpress/wp-content/plugins/"
done
php -- "$BASE" "$LAB" <<'PHP'
<?php
[$script, $base, $lab] = $argv;
$file = $lab . '/wordpress/wp-config.php';
$config = file_get_contents($file);
if (!str_contains($config, "'DB_ENGINE', 'sqlite'") || !str_contains($config, "'WP_ENVIRONMENT_TYPE', 'local'")
    || !str_contains($config, "'WP_HOME', 'https://www.offkilter.test'")) throw new RuntimeException('Not a synthetic configuration.');
$config = str_replace($base . '/', $lab . '/', $config);
if (str_contains($config, $base)) throw new RuntimeException('Unmapped original lab reference.');
file_put_contents($file, $config);
chmod($file, 0600);
PHP
WP=(/usr/bin/sandbox-exec -f "$ROOT/tests/wordpress/membership-lab.sb" php -d memory_limit=512M /opt/homebrew/bin/wp --path="$LAB/wordpress")
"${WP[@]}" plugin activate armember-membership armember > "$LAB/private/membership-activation.log" 2>&1
"${WP[@]}" eval 'if (!defined("MEMBERSHIP_VERSION") || MEMBERSHIP_VERSION !== "7.9.1" || !defined("MEMBERSHIPLITE_VERSION") || MEMBERSHIPLITE_VERSION !== "6.1" || !class_exists("ARM_restriction") || !class_exists("beeteam368_membership") || OKARCANA_VERSION !== "3.3.6" || class_exists("OKTV_Editorial_Home")) throw new RuntimeException("Expected actual ARMember/VidMov/playback package."); echo "Expected membership stack loaded.\n";' > "$LAB/private/membership-boot.log" 2>&1
date -u '+%Y-%m-%dT%H:%M:%SZ' > "$LAB/private/provisioned-at.txt"
printf '%s\n' "$LAB" > /private/tmp/oktv-membership-lab-current-path
trap - ERR
printf 'Membership clone created: %s\nActivation is not access-parity qualification. No server started.\n' "$LAB"
