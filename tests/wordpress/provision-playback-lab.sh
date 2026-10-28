#!/usr/bin/env bash
# Creates only a synthetic, loopback lab. Never imports production data/config.
set -euo pipefail
umask 077
ROOT=$(cd "$(dirname "$0")/../.." && pwd -P)
SOURCE=${1:?Pass the freshly captured private approved-source path}
RELEASE=${2:?Pass the verified playback package}
ID=${3:?Pass the reviewed release ID}
php "$ROOT/deployment/playback-release/package.php" verify "$RELEASE" "$ID" >/dev/null
php "$ROOT/tests/wordpress/approved-source.php" "$SOURCE" >/dev/null
if lsof -nP -iTCP:8793 -sTCP:LISTEN >/dev/null 2>&1; then
  echo 'Port 8793 is occupied; refusing a second lab.' >&2; exit 1
fi
AVAILABLE=$(df -k /private/tmp | awk 'END {print $4}')
[[ "$AVAILABLE" -ge 786432 ]] || { echo 'Need at least 768 MiB free before provisioning.' >&2; exit 1; }
LAB=$(mktemp -d /private/tmp/oktv-vidmov-integration.XXXXXX)
printf '%s\n' OKTV_DISPOSABLE_LOCAL_LAB > "$LAB/.oktv-lab"
mkdir -m 700 "$LAB/wordpress" "$LAB/database" "$LAB/private" "$LAB/downloads"
printf 'Provisioning private selected-stack lab: %s\n' "$LAB"
trap 'echo "Provisioning failed; diagnostic files remain private in $LAB. No server was started." >&2' ERR
php "$ROOT/tests/wordpress/approved-source.php" "$SOURCE" > "$LAB/private/approved-source.json"
WP=(php -d memory_limit=512M /opt/homebrew/bin/wp --path="$LAB/wordpress")
# Native tar preserves the long core filenames that this host's PharData truncated.
curl --fail --silent --show-error --max-time 90 https://wordpress.org/wordpress-7.1.3.tar.gz \
  -o "$LAB/downloads/wordpress.tar.gz"
tar -tzf "$LAB/downloads/wordpress.tar.gz" > "$LAB/private/core-archive-paths.txt"
tar -tvzf "$LAB/downloads/wordpress.tar.gz" > "$LAB/private/core-archive-types.txt"
if grep -Ev '^wordpress(/|$)' "$LAB/private/core-archive-paths.txt" >/dev/null \
  || grep -E '(^/|(^|/)\.\.(/|$)|\\)' "$LAB/private/core-archive-paths.txt" >/dev/null \
  || grep -Ev '^[-d]' "$LAB/private/core-archive-types.txt" >/dev/null; then
  echo 'Unexpected core archive member; refusing extraction.' >&2; exit 1
fi
tar --no-same-owner --no-same-permissions --strip-components=1 -xzf "$LAB/downloads/wordpress.tar.gz" -C "$LAB/wordpress"
"${WP[@]}" core verify-checksums --version=7.1.3 --locale=en_US > "$LAB/private/core-checksums.log" 2>&1
curl --fail --silent --show-error --max-time 90 \
  https://downloads.wordpress.org/plugin/sqlite-database-integration.3.0.2.zip \
  -o "$LAB/downloads/sqlite.zip"
unzip -q "$LAB/downloads/sqlite.zip" -d "$LAB/wordpress/wp-content/plugins"
SQLITE="$LAB/wordpress/wp-content/plugins/sqlite-database-integration"
[[ -f "$SQLITE/db.copy" ]]
cp "$SQLITE/db.copy" "$LAB/wordpress/wp-content/db.php"
mkdir -p "$LAB/wordpress/wp-content/mu-plugins"
cp "$ROOT/tests/wordpress/lab-mu-plugin.php" "$LAB/wordpress/wp-content/mu-plugins/oktv-lab.php"
php -- "$LAB" <<'PHP'
<?php
$lab = $argv[1];
$config = "<?php\n";
$constants = array('DB_NAME' => 'local_only', 'DB_USER' => 'local_only', 'DB_PASSWORD' => bin2hex(random_bytes(24)),
    'DB_HOST' => '127.0.0.1', 'DB_CHARSET' => 'utf8', 'DB_COLLATE' => '',
    'WP_ENVIRONMENT_TYPE' => 'local', 'DB_ENGINE' => 'sqlite', 'DB_DIR' => $lab . '/database/',
    'WP_HOME' => 'https://www.offkilter.test', 'WP_SITEURL' => 'https://www.offkilter.test',
    'DISABLE_WP_CRON' => true, 'AUTOMATIC_UPDATER_DISABLED' => true, 'DISALLOW_FILE_MODS' => true,
    'DISALLOW_FILE_EDIT' => true, 'WP_DEBUG' => true, 'WP_DEBUG_DISPLAY' => false,
    'WP_DEBUG_LOG' => $lab . '/private/debug.log', 'OKTV_PLAYBACK_HELP_ENABLED' => true);
foreach (array('AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT') as $key) $constants[$key] = bin2hex(random_bytes(48));
foreach ($constants as $key => $value) $config .= 'define(' . var_export($key, true) . ', ' . var_export($value, true) . ");\n";
$config .= '$table_prefix = \'wp_\';' . "\n" . 'if (!defined(\'ABSPATH\')) define(\'ABSPATH\', __DIR__ . \'/\');' . "\nrequire_once ABSPATH . 'wp-settings.php';\n";
file_put_contents($lab . '/wordpress/wp-config.php', $config);
file_put_contents($lab . '/private/admin-password', bin2hex(random_bytes(24)) . "\n");
PHP
"${WP[@]}" core install --url=https://www.offkilter.test --title='OKTV Local Integration ONLY' \
  --admin_user=local_lab_admin --admin_email=admin@offkilter.test --skip-email --prompt=admin_password \
  < "$LAB/private/admin-password" > "$LAB/private/install.log" 2>&1
# WP-CLI echoes a reconstructed command when prompting through a pipe.
# Keep the password solely in its private credential file, not diagnostic logs.
php -- "$LAB/private/install.log" <<'PHP'
<?php
$file = $argv[1];
file_put_contents($file, preg_replace("/--admin_password='[^']*'/", '--admin_password=[REDACTED]', file_get_contents($file)));
PHP
"${WP[@]}" plugin verify-checksums sqlite-database-integration > "$LAB/private/sqlite-checksums.log" 2>&1
cp -R "$RELEASE/offkilter-arcana" "$LAB/wordpress/wp-content/plugins/"
cp "$RELEASE/release.json" "$LAB/private/playback-release.json"
cp -R "$SOURCE/files/plugins/all-in-one-seo-pack" "$LAB/wordpress/wp-content/plugins/"
WP=(/usr/bin/sandbox-exec -f "$ROOT/tests/wordpress/membership-lab.sb" php -d memory_limit=512M /opt/homebrew/bin/wp --path="$LAB/wordpress")
"${WP[@]}" plugin activate all-in-one-seo-pack offkilter-arcana > "$LAB/private/activation.log" 2>&1
"${WP[@]}" rewrite structure '/%postname%/' >> "$LAB/private/activation.log" 2>&1
for plugin in cmb2 redux-framework beeteam368-extensions beeteam368-extensions-pro; do
  cp -R "$SOURCE/files/plugins/$plugin" "$LAB/wordpress/wp-content/plugins/"
done
cp -R "$SOURCE/files/themes/vidmov" "$LAB/wordpress/wp-content/themes/"
"${WP[@]}" plugin activate cmb2 redux-framework beeteam368-extensions beeteam368-extensions-pro >> "$LAB/private/activation.log" 2>&1
"${WP[@]}" theme activate vidmov >> "$LAB/private/activation.log" 2>&1
"${WP[@]}" eval-file "$ROOT/tests/wordpress/playback-seed.php" > "$LAB/private/seed.log" 2>&1
[[ $(df -k /private/tmp | awk 'END {print $4}') -ge 524288 ]] || { echo 'Less than 512 MiB free; lab not declared ready.' >&2; exit 1; }
date -u '+%Y-%m-%dT%H:%M:%SZ' > "$LAB/private/provisioned-at.txt"
printf '%s\n' "$LAB" > /private/tmp/oktv-selected-lab-current-path
trap - ERR
echo 'Selected synthetic lab ready. No server started and no production data imported.'
