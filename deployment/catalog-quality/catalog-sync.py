#!/usr/bin/python3
"""Bounded verified publishing for the two approved OKTV playlists.

Credentials stay in WordPress. Raw provider data and operator output stay in
root-private receipts. This runner never invokes the vendor import engine.
"""
import fcntl
import gzip
import hashlib
import json
import os
from pathlib import Path
import secrets
import subprocess
import sys
import time

SOURCES = (8089, 8090)
CONFIG = Path('/etc/oktv-catalog-sync.json')
RECEIPTS = Path('/var/backups/oktv-recovery/catalog-sync')
STATE = Path('/var/lib/oktv-catalog-sync')
RUNTIME = Path('/run/oktv-catalog-sync')
PHP = '/opt/bitnami/php/bin/php'
WP = '/opt/bitnami/wp-cli/bin/wp-cli.phar'
MAX_RECEIPTS = 2 * 1024 * 1024 * 1024
OPERATORS = ('catalog-inventory.php', 'catalog-backfill.php', 'catalog-reconcile.php',
             'catalog-verified-import.php', 'oktv-catalog-quality.php', 'oktv-source-attribution.php')


class SyncFailure(Exception):
    pass


def private_directory(path):
    path.mkdir(mode=0o700, parents=True, exist_ok=True)
    stat = path.stat()
    if path.is_symlink() or stat.st_uid != 0 or stat.st_mode & 0o077:
        raise SyncFailure('private_directory_invalid')


def write_json(path, value):
    temporary = path.with_name(path.name + '.' + secrets.token_hex(4) + '.new')
    with temporary.open('x') as handle:
        json.dump(value, handle, indent=2)
        handle.write('\n')
    os.replace(temporary, path)


def load_release():
    stat = CONFIG.stat()
    if CONFIG.is_symlink() or stat.st_uid != 0 or stat.st_mode & 0o022 or stat.st_size > 8192:
        raise SyncFailure('release_config_invalid')
    config = json.loads(CONFIG.read_text())
    release = Path(config['release'])
    if not str(release).startswith('/opt/bitnami/oktv-catalog-releases/') or release.resolve() != release:
        raise SyncFailure('release_path_invalid')
    for name in OPERATORS:
        file = release / name
        stat = file.stat()
        if file.is_symlink() or stat.st_uid != 0 or stat.st_mode & 0o022:
            raise SyncFailure('release_permissions_invalid')
        if hashlib.sha256(file.read_bytes()).hexdigest() != config['sha256'].get(name):
            raise SyncFailure('release_hash_mismatch')
    return release


def healthy():
    # This probe addresses only the local origin. Its certificate is issued by
    # the CDN's private origin CA; public TLS validation is never relaxed.
    result = subprocess.run(['curl', '--insecure', '--silent', '--show-error',
        '--resolve', 'www.offkilter.tv:443:127.0.0.1', '--max-time', '12',
        '-o', '/dev/null', '-w', '%{http_code}', 'https://www.offkilter.tv/'],
        capture_output=True, text=True, timeout=15)
    if result.returncode or result.stdout != '200':
        raise SyncFailure('site_health_failed')
    failures = Path('/run/oktv-healthguard.failcount')
    if failures.exists() and failures.read_text().strip() not in ('', '0'):
        raise SyncFailure('site_recovery_in_progress')
    disk = os.statvfs(RECEIPTS)
    if disk.f_bavail * disk.f_frsize < 1024 * 1024 * 1024:
        raise SyncFailure('disk_reserve_low')


def operator(release, run, name, args, *, readonly=False, receipt=None, timeout=180):
    # Paths and argument lists are JSON data, never interpolated into a shell.
    encoded_args = json.dumps(json.dumps(args)).replace('$', '\\$')
    encoded_file = json.dumps(str(release / name)).replace('$', '\\$')
    evaluation = f'$args=json_decode({encoded_args},true,512,JSON_THROW_ON_ERROR);require {encoded_file};'
    command = [PHP, WP, '--path=/opt/bitnami/wordpress', '--allow-root']
    if readonly:
        command += ['--skip-plugins', '--skip-themes']
    environment = dict(os.environ)
    environment.pop('OKTV_CATALOG_RECEIPT', None)
    if receipt:
        environment['OKTV_CATALOG_RECEIPT'] = str(receipt)
    result = subprocess.run(command + ['eval', evaluation], env=environment,
        capture_output=True, text=True, timeout=timeout)
    label = name.removesuffix('.php') + '.' + secrets.token_hex(3)
    (run / (label + '.stdout')).write_text(result.stdout)
    (run / (label + '.stderr')).write_text(result.stderr)
    if result.returncode:
        raise SyncFailure('operator_failed')
    try:
        value = json.loads(result.stdout)
    except (ValueError, TypeError):
        raise SyncFailure('operator_output_invalid') from None
    if not isinstance(value, dict):
        raise SyncFailure('operator_output_invalid')
    return value


def archive_owned_receipts(run):
    """Preserve exact bytes in verified gzip archives; never touch earlier runs."""
    for file in run.iterdir():
        if not file.is_file() or file.is_symlink() or file.stat().st_size < 4096:
            continue
        if file.suffix not in ('.json', '.stdout', '.stderr'):
            continue
        before = file.read_bytes()
        compressed = file.with_name(file.name + '.gz')
        with gzip.open(compressed, 'xb') as handle:
            handle.write(before)
        if gzip.decompress(compressed.read_bytes()) != before:
            raise SyncFailure('receipt_archive_verification_failed')
        file.unlink()


def synchronize(release, run):
    healthy()
    receipt = run / 'inventory.json'
    inventory = operator(release, run, 'catalog-inventory.php',
        ['audit-observed', *map(str, SOURCES)], readonly=True, receipt=receipt, timeout=300)
    if {source['campaign_id'] for source in inventory.get('sources', [])} != set(SOURCES):
        raise SyncFailure('inventory_source_mismatch')
    published = 0
    for source in SOURCES:
        healthy()
        result = operator(release, run, 'catalog-backfill.php',
            ['publish', str(receipt), str(source), '20', 'adopt-managed-drafts'])
        if result.get('users_created') != 0 or not isinstance(result.get('published'), int) or not 0 <= result['published'] <= 20:
            raise SyncFailure('publication_result_invalid')
        published += result['published']
    healthy()
    reconciled = operator(release, run, 'catalog-reconcile.php',
        ['apply', str(receipt), 'verify-source-credit'])
    snapshot = reconciled['snapshot']
    archive_owned_receipts(run)
    return {'ok': True, 'completed_at': time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime()),
        'published_this_run': published, 'receipt_directory': str(run),
        'checked_at': snapshot['checked_at'], 'sources': snapshot['sources']}


def main():
    if os.geteuid() != 0:
        raise SyncFailure('root_operator_required')
    os.umask(0o077)
    for directory in (RECEIPTS, STATE, RUNTIME):
        private_directory(directory)
    with (RUNTIME / 'sync.lock').open('a') as lock:
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            print(json.dumps({'ok': True, 'skipped': 'already_running'}))
            return 0
        release = load_release()
        if sys.argv[1:] == ['--check']:
            healthy()
            print(json.dumps({'ok': True, 'release_verified': True, 'site_healthy': True, 'sources': list(SOURCES), 'batch_per_source': 20}))
            return 0
        if sys.argv[1:]:
            raise SyncFailure('unexpected_arguments')
        size = sum(file.stat().st_size for file in RECEIPTS.rglob('*') if file.is_file())
        if size > MAX_RECEIPTS:
            raise SyncFailure('private_receipt_reserve_reached')
        run = RECEIPTS / ('sync.' + time.strftime('%Y%m%d-%H%M%S', time.gmtime()) + '.' + secrets.token_hex(3))
        run.mkdir(mode=0o700)
        result = synchronize(release, run)
        write_json(STATE / 'status.json', result)
        print(json.dumps({key: result[key] for key in ('ok', 'completed_at', 'published_this_run', 'checked_at', 'sources')}))
        return 0


if __name__ == '__main__':
    try:
        sys.exit(main())
    except Exception as error:
        # Provider/vendor exception text may contain credentials. Never copy it
        # into the journal, status option, or an administrator-facing response.
        reason = str(error) if isinstance(error, SyncFailure) else 'sync_failed'
        failure = {'ok': False, 'reason': reason, 'failed_at': time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime())}
        if os.geteuid() == 0 and STATE.is_dir() and not STATE.is_symlink():
            try:
                write_json(STATE / 'status.json', failure)
            except OSError:
                pass
        print(json.dumps(failure))
        sys.exit(1)
