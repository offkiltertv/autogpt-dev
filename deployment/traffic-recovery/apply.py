#!/usr/bin/env python3
"""Apply the reviewed recovery delta with before-state checks and rollback."""
import hashlib
import json
import os
from pathlib import Path
import subprocess
import sys
import tempfile
import time

NGINX = '/opt/bitnami/nginx/sbin/nginx'
if os.geteuid() != 0:
    raise SystemExit('Run as root on the reviewed origin.')
stage = Path(sys.argv[1]).resolve(strict=True)
if not str(stage).startswith('/var/backups/oktv-recovery/traffic-recovery.'):
    raise SystemExit('Unexpected release location.')
manifest = json.loads((stage / 'manifest.json').read_text())
for name, expected in manifest['files'].items():
    path = stage / name
    if path.is_symlink() or hashlib.sha256(path.read_bytes()).hexdigest() != expected:
        raise SystemExit('Release source drifted.')
qualifications = list(stage.glob('qualification.*/results.json'))
if not any(len(results := json.loads(p.read_text())) == 11
           and all(r.get('pass') is True for r in results) for p in qualifications):
    raise SystemExit('Require the completed isolated NGINX qualification.')
source = stage / 'deployment/traffic-recovery'
main = Path('/opt/bitnami/nginx/conf/nginx.conf')
php = Path('/opt/bitnami/nginx/conf/bitnami/php-fpm.conf')
existing = Path('/opt/bitnami/nginx/conf/server_blocks/00-oktv-resilience-http.conf')
new = Path('/opt/bitnami/nginx/conf/server_blocks/01-oktv-admission-http.conf')
before_hashes = {
    main: 'dbbbe1e8b90640f713c21d6e20b56216e7e83b88eaff440cfe94ab89c489bbd3',
    php: '43b91e463d1b1d4eadf9f2f384b40bec07e0eacd6d1af1bf168a53a4266fc0c5',
    existing: '72c186beecde7fbb32c3961d1d308ffb6571b191d64311f8366380f0a2a00973',
}
for path, expected in before_hashes.items():
    if path.is_symlink() or hashlib.sha256(path.read_bytes()).hexdigest() != expected:
        raise SystemExit('Live configuration changed; review before retrying.')
if new.exists() or new.is_symlink():
    raise SystemExit('Refusing an existing admission configuration.')
masters = subprocess.check_output(['pgrep', '-f', 'nginx: master process'], text=True).splitlines()
if not masters or not any('65536' in (Path('/proc') / p / 'limits').read_text()
                          for p in masters if (Path('/proc') / p / 'limits').exists()):
    raise SystemExit('The reviewed process file limit is unavailable.')
old_main = main.read_bytes()
if old_main.count(b'worker_connections  1024;') != 1:
    raise SystemExit('Unexpected worker setting.')
changes = {
    main: old_main.replace(b'worker_connections  1024;', b'worker_connections  4096;'),
    php: (source / 'php-fpm.conf').read_bytes(),
    new: (source / '01-oktv-admission-http.conf').read_bytes(),
}
backup = stage / 'before'
backup.mkdir(mode=0o700)
states = {}
for number, path in enumerate(changes):
    data = path.read_bytes() if path.exists() else None
    stat = path.stat() if data is not None else None
    states[path] = (data, stat)
    if data is not None:
        saved = backup / f'{number}.conf'
        saved.write_bytes(data)
        saved.chmod(0o600)
receipt = {'commit': manifest['commit'], 'started_at': time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime()),
           'paths': {str(p): {'before': hashlib.sha256(s[0]).hexdigest() if s[0] is not None else None,
                             'after': hashlib.sha256(changes[p]).hexdigest()} for p, s in states.items()}}
(backup / 'receipt.json').write_text(json.dumps(receipt, indent=2))
(backup / 'receipt.json').chmod(0o600)


def atomic(path, data, stat=None):
    descriptor, temp = tempfile.mkstemp(prefix='.oktv-recovery.', dir=path.parent)
    try:
        with os.fdopen(descriptor, 'wb') as handle:
            handle.write(data)
            handle.flush()
            os.fsync(handle.fileno())
        os.chmod(temp, stat.st_mode & 0o777 if stat else 0o644)
        if stat:
            os.chown(temp, stat.st_uid, stat.st_gid)
        os.replace(temp, path)
    finally:
        if os.path.exists(temp):
            os.unlink(temp)


written = []
try:
    for path, data in changes.items():
        old, stat = states[path]
        if (path.read_bytes() if path.exists() else None) != old:
            raise RuntimeError('Concurrent configuration edit.')
        atomic(path, data, stat)
        written.append(path)
    subprocess.run([NGINX, '-t'], check=True)
    subprocess.run([NGINX, '-s', 'reload'], check=True, capture_output=True)
    probes = []
    for route in ['/', '/video/', '/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/']:
        code = subprocess.check_output([
            'curl', '--insecure', '--silent', '--show-error', '--connect-timeout', '3', '--max-time', '15',
            '--resolve', 'www.offkilter.tv:443:127.0.0.1', '-o', '/dev/null', '-w', '%{http_code}',
            'https://www.offkilter.tv' + route], text=True)
        probes.append({'route': route, 'status': code})
        if code != '200':
            raise RuntimeError('Local post-reload probe failed.')
    receipt['result'] = 'applied'
    receipt['probes'] = probes
    receipt['finished_at'] = time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
    (stage / 'applied.json').write_text(json.dumps(receipt, indent=2))
    (stage / 'applied.json').chmod(0o600)
    print(json.dumps({'result': 'applied', 'backup': str(backup), 'commit': manifest['commit'], 'probes': probes}, indent=2))
except BaseException:
    for path in reversed(written):
        if path.is_symlink() or path.read_bytes() != changes[path]:
            raise RuntimeError('Rollback refused a concurrent edit; inspect the private receipt.')
        data, stat = states[path]
        if data is None:
            path.unlink()
        else:
            atomic(path, data, stat)
    subprocess.run([NGINX, '-t'], check=True)
    subprocess.run([NGINX, '-s', 'reload'], check=True, capture_output=True)
    raise
