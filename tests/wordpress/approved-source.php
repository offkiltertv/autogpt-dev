<?php
// Private source provenance, not authorization to deploy or redistribute it.
function oktv_approved_source(string $path): array {
    if (!preg_match('~^/private/tmp/oktv-production-source\.[A-Za-z0-9]+$~D', $path)
        || realpath($path) !== $path || is_link($path)
        || (fileperms($path) & 0077) !== 0
        || @trim(file_get_contents($path . '/.oktv-source')) !== 'OKTV_PRIVATE_APPROVED_SOURCE') {
        throw new RuntimeException('Expected a private marked approved-source directory.');
    }
    $manifest = $path . '/files.sha256';
    if (!is_file($manifest) || is_link($manifest)) throw new RuntimeException('Missing source manifest.');
    $lines = file($manifest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) throw new RuntimeException('Empty source manifest.');
    $seen = array();
    foreach ($lines as $line) {
        if (!preg_match('~^([a-f0-9]{64})  \./((?:themes/vidmov|plugins/(?:beeteam368-extensions|beeteam368-extensions-pro|offkilter-arcana|bunnycdn|uk-cookie-consent|elementor|elementor-pro|armember|armembercommunity|armember-membership|wpforo|cmb2|redux-framework|all-in-one-seo-pack))/[^\\\\]+)$~D', $line, $match)
            || preg_match('~(?:^|/)\.\.(?:/|$)~', $match[2]) || isset($seen[$match[2]])) {
            throw new RuntimeException('Invalid or duplicate approved-source manifest entry.');
        }
        $file = $path . '/files/' . $match[2];
        if (realpath($file) !== $file || !is_file($file) || is_link($file)
            || !hash_equals($match[1], hash_file('sha256', $file))) {
            throw new RuntimeException('Approved source changed: ' . $match[2]);
        }
        $seen[$match[2]] = true;
    }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path . '/files', FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $relative = substr($file->getPathname(), strlen($path . '/files/'));
        if ($file->isLink() || !$file->isFile() || !isset($seen[$relative])) {
            throw new RuntimeException('Unmanifested source entry.');
        }
    }
    foreach (array('themes/vidmov/style.css', 'plugins/offkilter-arcana/offkilter-arcana.php',
        'plugins/uk-cookie-consent/includes/controllers/class-frontend.php',
        'plugins/bunnycdn/vendor/autoload.php', 'plugins/bunnycdn/src/functions.php') as $required) {
        if (!isset($seen[$required])) throw new RuntimeException('Required source absent from manifest.');
    }
    return array('path' => $path, 'manifest_sha256' => hash_file('sha256', $manifest), 'files' => count($seen));
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo json_encode(oktv_approved_source($argv[1] ?? ''), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
}
