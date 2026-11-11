#!/usr/bin/env python3
"""Exercise real NGINX admission semantics on loopback synthetic upstreams only."""
import concurrent.futures
import http.client
import http.server
import json
import os
from pathlib import Path
import re
import subprocess
import sys
import tempfile
import threading
import time

NGINX = '/opt/bitnami/nginx/sbin/nginx'
source = Path(__file__).resolve().parent
private = Path(tempfile.mkdtemp(prefix='qualification.', dir=source.parent.parent))
private.chmod(0o700)
port = 18881
backend_port = 18882


class Backend(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        time.sleep(0.4)
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b'synthetic upstream')

    do_POST = do_GET

    def log_message(self, *args):
        pass


class Server(http.server.ThreadingHTTPServer):
    request_queue_size = 128
    daemon_threads = True


backend = Server(('127.0.0.1', backend_port), Backend)
threading.Thread(target=backend.serve_forever, daemon=True).start()
(private / 'index.php').write_text('synthetic file')
(private / 'wp-cron.php').write_text('synthetic file')
(private / 'asset.txt').write_text('static asset')
php = (source / 'php-fpm.conf').read_text()
# Keep the admission and location directives exact; substitute HTTP for PHP.
php = re.sub(r'^\s*(fastcgi_|include fastcgi_params;).*$', '', php, flags=re.M)
php = php.replace('    try_files $uri =404;',
                  '    try_files $uri =404;\n    proxy_pass http://127.0.0.1:18882;')
php = php.replace('/opt/bitnami/nginx/logs/oktv-timing.log', str(private / 'timing.log'))
(private / 'locations.conf').write_text(php)
config = f'''user root;
worker_processes 1;
pid {private}/nginx.pid;
error_log {private}/error.log warn;
events {{ worker_connections 256; }}
http {{
    access_log off;
    limit_req_zone $binary_remote_addr zone=oktv_dynamic:1m rate=5r/s;
    limit_req_status 429;
    include {source}/01-oktv-admission-http.conf;
    server {{
        listen 127.0.0.1:{port};
        server_name qualification.invalid;
        set_real_ip_from 127.0.0.1;
        real_ip_header X-OKTV-Test-IP;
        root {private};
        location / {{ try_files $uri $uri/ /index.php?$args; }}
        # The actual Bitnami management location reuses the PHP include here.
        location ^~ /synthetic-admin {{
            include {private}/locations.conf;
        }}
        include {private}/locations.conf;
    }}
}}
'''
(private / 'nginx.conf').write_text(config)
command = [NGINX, '-p', str(private) + '/', '-c', str(private / 'nginx.conf')]
started = False
checks = []


def request(path, identity, cookie='', method='GET'):
    connection = http.client.HTTPConnection('127.0.0.1', port, timeout=8)
    try:
        headers = {'X-OKTV-Test-IP': f'192.0.2.{identity}'}
        if cookie:
            headers['Cookie'] = cookie
        connection.request(method, path, headers=headers)
        response = connection.getresponse()
        response.read()
        return response.status
    finally:
        connection.close()


def check(condition, label, **details):
    checks.append({'check': label, 'pass': bool(condition), **details})
    if not condition:
        raise RuntimeError(label)


def burst(paths, cookie='', method='GET'):
    barrier = threading.Barrier(len(paths))

    def run(item):
        identity, path = item
        barrier.wait(timeout=4)
        return request(path, identity + 1, cookie, method)

    with concurrent.futures.ThreadPoolExecutor(max_workers=len(paths)) as pool:
        return list(pool.map(run, enumerate(paths)))


try:
    subprocess.run(command + ['-t'], check=True, capture_output=True)
    subprocess.run(command, check=True, capture_output=True)
    started = True
    check(request('/index.php', 200) == 200, 'Ordinary public read succeeds')
    check(request('/index.php?sort_by=new', 201) == 200, 'Normal query variant succeeds')
    time.sleep(2)
    statuses = burst(['/index.php?sort_by=most_viewed'] * 12)
    check(set(statuses) <= {200, 429} and 1 <= statuses.count(200) <= 4
          and statuses.count(429) >= 8,
          'Distributed expensive reads are bounded before the upstream', statuses=statuses)
    check(request('/index.php', 202) == 200, 'Ordinary read retains capacity during variant rejection')
    time.sleep(2)
    statuses = burst(['/index.php?random_query=123'] * 10)
    check(set(statuses) <= {200, 429} and 1 <= statuses.count(200) <= 4,
          'Random query variants share the same budget', statuses=statuses)
    time.sleep(2)
    statuses = burst(['/index.php'] * 18)
    check(set(statuses) <= {200, 429} and 1 <= statuses.count(200) <= 12
          and statuses.count(429) >= 6,
          'Distributed anonymous reads have a shared ceiling', statuses=statuses)
    time.sleep(2)
    statuses = burst(['/index.php?sort_by=new'] * 8, cookie='wordpress_logged_in_synthetic=1')
    check(statuses == [200] * 8, 'Signed-in path avoids anonymous variant limits')
    statuses = burst(['/wp-cron.php'] * 3, method='POST')
    check(statuses.count(200) == 1 and statuses.count(429) == 2,
          'Duplicate cron requests cannot fill the PHP queue', statuses=statuses)
    check(request('/nonexistent.php', 203) == 404, 'Nonexistent PHP is rejected without upstream work')
    check(request('/synthetic-admin/missing.php', 205) == 404, 'Nested management includes remain valid')
    check(request('/asset.txt', 204) == 200, 'Static assets remain available')
    print(json.dumps({'passed': len(checks), 'checks': checks, 'private_receipt': str(private)}, indent=2))
finally:
    (private / 'results.json').write_text(json.dumps(checks, indent=2))
    os.chmod(private / 'results.json', 0o600)
    if started:
        subprocess.run(command + ['-s', 'quit'], capture_output=True)
    backend.shutdown()
    backend.server_close()
