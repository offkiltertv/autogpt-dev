<?php
// php -S 127.0.0.1:8793 -t /tmp/oktv-wp-integration.XXXXXX/wordpress this-file
// This is HTTP on loopback, emulating HTTPS URL context. It is NOT a TLS test.
$root = realpath($_SERVER['DOCUMENT_ROOT']);
$lab = dirname($root ?: '');
if (@trim(file_get_contents($lab . '/.oktv-lab')) !== 'OKTV_DISPOSABLE_LOCAL_LAB'
    || ($_SERVER['HTTP_HOST'] ?? '') !== 'www.offkilter.test') {
    http_response_code(403); exit('Disposable local lab only.');
}
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (str_contains($path, '..') || str_contains($path, '\\')
    || preg_match('~(?:^|/)(?:\.[^/]*|wp-config\.php|readme\.html|license\.txt)(?:/|$)~i', $path)) {
    http_response_code(404); exit('Not found');
}
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['SERVER_NAME'] = 'www.offkilter.test';
$_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '';
header('X-Robots-Tag: noindex, nofollow');
$file = realpath($root . $path);
if ($file && str_starts_with($file, $root . '/') && is_file($file)) {
    if (str_ends_with($file, '.php')) { require $file; return true; }
    return false;
}
if ($path === '/wp-admin/' || $path === '/wp-admin') {
    require $root . '/wp-admin/index.php'; return true;
}
require $root . '/index.php';
