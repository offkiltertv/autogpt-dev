<?php
// Offline packaging only. No SSH, deployment, flags, activation or Git mutation.
namespace OKTV\PlaybackRelease;

require_once dirname(__DIR__, 2) . '/tests/wordpress/approved-source.php';

const BASE_BOOTSTRAP = '088972693f16233a8f356f5326f7fc6428f8248d81fa6888556c26f9a87baee3';
const BASE_TREE = '15b610972b88398090706bf97f98e3a0c9997cbdba12cc9d20058acad16e4989';
const VERSION = '3.3.6';
const ADDED = array('assets/css/ok-playback-help.css', 'assets/js/ok-playback-help.js', 'includes/class-okarcana-playback-help.php');

function canonical(array $value): string {
    return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

function tree(string $directory): array {
    if (!is_dir($directory) || realpath($directory) !== $directory || is_link($directory)) throw new \RuntimeException('Expected a canonical regular directory.');
    $files = array();
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $entry) {
        $name = substr($entry->getPathname(), strlen($directory) + 1);
        if ($entry->isLink() || !$entry->isFile() || realpath($entry->getPathname()) !== $entry->getPathname()
            || !preg_match('~^[A-Za-z0-9@._/-]+$~D', $name) || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $name)) throw new \RuntimeException('Unsafe package member.');
        $files[$name] = hash_file('sha256', $entry->getPathname());
    }
    ksort($files, SORT_STRING);
    return $files;
}

function bootstrap(string $before): string {
    if (hash('sha256', $before) !== BASE_BOOTSTRAP) throw new \RuntimeException('Unknown production bootstrap; review baseline drift first.');
    $changes = array(
        ' * Version: 3.3.5' => ' * Version: ' . VERSION,
        "define('OKARCANA_VERSION', '3.3.5');" => "define('OKARCANA_VERSION', '" . VERSION . "');",
        "    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-frontend.php';" => "    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-frontend.php';\n    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-playback-help.php';",
        '    OKArcana_Frontend::init();' => "    OKArcana_Frontend::init();\n    OKArcana_Playback_Help::init();",
    );
    foreach ($changes as $old => $new) {
        if (substr_count($before, $old) !== 1) throw new \RuntimeException('Unexpected bootstrap anchor.');
        $before = str_replace($old, $new, $before);
    }
    return $before;
}

function privateRoot(string $root): void {
    if (!preg_match('~^/private/tmp/oktv-playback-release\.[a-f0-9]+$~D', $root)
        || realpath($root) !== $root || is_link($root) || (fileperms($root) & 0077) !== 0
        || @trim(file_get_contents($root . '/.oktv-release')) !== 'OKTV_PRIVATE_PLAYBACK_CANDIDATE') throw new \RuntimeException('Expected private marked playback release.');
}

function verify(string $root, string $expectedId): array {
    privateRoot($root);
    foreach (array('release.json', 'plugin.zip') as $name) {
        if (!is_file($root . '/' . $name) || is_link($root . '/' . $name)) throw new \RuntimeException('Missing regular release metadata/archive.');
    }
    $manifest = json_decode(file_get_contents($root . '/release.json'), true, 512, JSON_THROW_ON_ERROR);
    if (($manifest['format'] ?? '') !== 'oktv-playback-candidate-v1' || ($manifest['version'] ?? '') !== VERSION
        || ($manifest['default_enabled'] ?? null) !== false) throw new \RuntimeException('Unexpected release kind or feature default.');
    $baseline = $manifest['baseline_files'] ?? array();
    if (count($baseline) !== 52 || hash('sha256', canonical($baseline)) !== BASE_TREE) throw new \RuntimeException('Unrecognized production baseline.');
    $files = tree($root . '/offkilter-arcana');
    if ($files !== ($manifest['files'] ?? null)) throw new \RuntimeException('Payload differs from reviewed manifest.');
    $id = 'oktv-playback-' . VERSION . '-' . substr(hash('sha256', canonical($files)), 0, 16);
    if ($id !== $expectedId || $id !== ($manifest['release_id'] ?? null)) throw new \RuntimeException('Release identity mismatch.');
    $added = array_keys(array_diff_key($files, $baseline));
    $removed = array_keys(array_diff_key($baseline, $files));
    $changed = array_keys(array_diff_assoc(array_intersect_key($files, $baseline), $baseline));
    if ($added !== ADDED || $removed || $changed !== array('offkilter-arcana.php')) throw new \RuntimeException('Change exceeded playback-only scope.');
    if (file_get_contents($root . '/offkilter-arcana/offkilter-arcana.php') !== bootstrap($manifest['baseline_bootstrap'] ?? '')) throw new \RuntimeException('Bootstrap has changes beyond the approved transform.');
    if (hash_file('sha256', $root . '/plugin.zip') !== ($manifest['archive_sha256'] ?? null)) throw new \RuntimeException('Archive digest mismatch.');
    $zip = new \ZipArchive();
    if ($zip->open($root . '/plugin.zip', \ZipArchive::RDONLY) !== true) throw new \RuntimeException('Invalid archive.');
    try {
        if ($zip->numFiles !== count($files)) throw new \RuntimeException('Archive member count mismatch.');
        $seen = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, 'offkilter-arcana/')) throw new \RuntimeException('Unexpected archive root.');
            $relative = substr($name, strlen('offkilter-arcana/'));
            if (isset($seen[$name]) || !isset($files[$relative])) throw new \RuntimeException('Unexpected/duplicate archive member.');
            $zip->getExternalAttributesIndex($i, $opsys, $attrs);
            if ($opsys !== \ZipArchive::OPSYS_UNIX || (($attrs >> 16) & 0170000) !== 0100000) throw new \RuntimeException('Non-regular archive member.');
            if (hash('sha256', $zip->getFromIndex($i)) !== $files[$relative]) throw new \RuntimeException('Archive content mismatch.');
            $seen[$name] = true;
        }
    } finally { $zip->close(); }
    return array('release_id' => $id, 'files' => count($files), 'unchanged' => 51,
        'added' => $added, 'modified' => $changed, 'archive_sha256' => $manifest['archive_sha256'], 'default_enabled' => false);
}

function build(string $snapshot): string {
    umask(0077);
    $source = \oktv_approved_source($snapshot);
    $base = $snapshot . '/files/plugins/offkilter-arcana';
    $baseline = tree($base);
    if (count($baseline) !== 52 || hash('sha256', canonical($baseline)) !== BASE_TREE) throw new \RuntimeException('Production tree drift; do not build from local HEAD.');
    $before = file_get_contents($base . '/offkilter-arcana.php');
    $after = bootstrap($before);
    $root = '/private/tmp/oktv-playback-release.' . bin2hex(random_bytes(8));
    if (!mkdir($root, 0700)) throw new \RuntimeException('Cannot create private release.');
    file_put_contents($root . '/.oktv-release', 'OKTV_PRIVATE_PLAYBACK_CANDIDATE');
    $payload = $root . '/offkilter-arcana';
    mkdir($payload, 0700);
    foreach ($baseline as $name => $hash) {
        $dest = $payload . '/' . $name;
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0700, true);
        if (!copy($base . '/' . $name, $dest) || hash_file('sha256', $dest) !== $hash) throw new \RuntimeException('Baseline copy failed.');
    }
    foreach (ADDED as $name) {
        $owned = dirname(__DIR__, 2) . '/plugins/offkilter-arcana/' . $name;
        if (!is_file($owned) || is_link($owned) || realpath($owned) !== $owned || isset($baseline[$name])) throw new \RuntimeException('Invalid owned helper input.');
        if (!copy($owned, $payload . '/' . $name)) throw new \RuntimeException('Owned file copy failed.');
    }
    file_put_contents($payload . '/offkilter-arcana.php', $after);
    $files = tree($payload);
    $zip = new \ZipArchive();
    if ($zip->open($root . '/plugin.zip', \ZipArchive::CREATE | \ZipArchive::EXCL) !== true) throw new \RuntimeException('Cannot create archive.');
    foreach ($files as $name => $hash) {
        $entry = 'offkilter-arcana/' . $name;
        if (!$zip->addFile($payload . '/' . $name, $entry)
            || !$zip->setMtimeName($entry, 315532800)
            || !$zip->setExternalAttributesName($entry, \ZipArchive::OPSYS_UNIX, 0100644 << 16)) throw new \RuntimeException('Cannot package regular source.');
    }
    if (!$zip->close()) throw new \RuntimeException('Cannot finalize archive.');
    $id = 'oktv-playback-' . VERSION . '-' . substr(hash('sha256', canonical($files)), 0, 16);
    $manifest = array('format' => 'oktv-playback-candidate-v1', 'version' => VERSION, 'release_id' => $id,
        'default_enabled' => false, 'baseline_files' => $baseline, 'baseline_bootstrap' => $before,
        'source_manifest_sha256' => $source['manifest_sha256'], 'files' => $files,
        'archive_sha256' => hash_file('sha256', $root . '/plugin.zip'));
    file_put_contents($root . '/release.json', json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
    verify($root, $id);
    return $root;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        if (($argv[1] ?? '') === 'build' && count($argv) === 3) {
            $root = build($argv[2]);
            $manifest = json_decode(file_get_contents($root . '/release.json'), true, 512, JSON_THROW_ON_ERROR);
            echo json_encode(array('private_release' => $root) + verify($root, $manifest['release_id']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
        } elseif (($argv[1] ?? '') === 'verify' && count($argv) === 4) {
            echo json_encode(verify($argv[2], $argv[3]), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
        } else { throw new \RuntimeException('Usage: package.php build APPROVED_SOURCE | verify PRIVATE_RELEASE REVIEWED_RELEASE_ID'); }
    } catch (\Throwable $error) { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); }
}
