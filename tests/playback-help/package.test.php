<?php
require dirname(__DIR__, 2) . '/deployment/playback-release/package.php';
use function OKTV\PlaybackRelease\{build, verify, bootstrap};

$source = $argv[1] ?? '';
$roots = array();
$passes = 0;
function package_check($condition, $message) {
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $GLOBALS['passes']++; echo 'PASS: ' . $message . PHP_EOL;
}
function package_reject(callable $operation, string $message) {
    try { $operation(); } catch (RuntimeException $error) { package_check(true, $message); return; }
    package_check(false, $message);
}
try {
    $root = build($source); $roots[] = $root;
    $raw = file_get_contents($root . '/release.json');
    $manifest = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    $id = $manifest['release_id'];
    $result = verify($root, $id);
    package_check($result['files'] === 55 && $result['unchanged'] === 51 && !$result['default_enabled'], 'Exactly 55 files; 51 unchanged; default off');
    $second = build($source); $roots[] = $second;
    package_check(hash_file('sha256', $second . '/plugin.zip') === hash_file('sha256', $root . '/plugin.zip'), 'Identical inputs reproduce archive bytes on this host');
    package_reject(fn() => verify($root, 'unreviewed'), 'Wrong externally supplied release identity rejected');
    package_reject(fn() => bootstrap('unexpected baseline'), 'Unknown bootstrap rejected');
    $php = $root . '/offkilter-arcana/offkilter-arcana.php'; $before = file_get_contents($php);
    file_put_contents($php, $before . "\n// unrelated\n");
    package_reject(fn() => verify($root, $id), 'Altered bootstrap rejected'); file_put_contents($php, $before);
    $extra = $root . '/offkilter-arcana/includes/unrelated.php'; file_put_contents($extra, '<?php');
    package_reject(fn() => verify($root, $id), 'Unexpected payload file rejected'); unlink($extra);
    $asset = $root . '/offkilter-arcana/' . \OKTV\PlaybackRelease\ADDED[0]; $css = file_get_contents($asset);
    unlink($asset); package_reject(fn() => verify($root, $id), 'Missing helper asset rejected');
    symlink($source . '/files/plugins/offkilter-arcana/offkilter-arcana.php', $asset);
    package_reject(fn() => verify($root, $id), 'Symlink payload rejected'); unlink($asset); file_put_contents($asset, $css);
    $manifest['baseline_files']['README.md'] = str_repeat('0', 64);
    file_put_contents($root . '/release.json', json_encode($manifest));
    package_reject(fn() => verify($root, $id), 'Tampered baseline inventory rejected'); file_put_contents($root . '/release.json', $raw);
    $archive = file_get_contents($root . '/plugin.zip'); file_put_contents($root . '/plugin.zip', 'not a zip');
    package_reject(fn() => verify($root, $id), 'Corrupt archive rejected'); file_put_contents($root . '/plugin.zip', $archive);
    foreach (array('path', 'link') as $case) {
        $zip = new ZipArchive(); $zip->open($root . '/plugin.zip');
        if ($case === 'path') $zip->renameName('offkilter-arcana/README.md', '../escape.php');
        else $zip->setExternalAttributesName('offkilter-arcana/README.md', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $zip->close();
        $changed = json_decode($raw, true); $changed['archive_sha256'] = hash_file('sha256', $root . '/plugin.zip');
        file_put_contents($root . '/release.json', json_encode($changed));
        package_reject(fn() => verify($root, $id), 'Unsafe archive ' . $case . ' rejected even with updated archive checksum');
        file_put_contents($root . '/plugin.zip', $archive); file_put_contents($root . '/release.json', $raw);
    }
    file_put_contents($asset, $css . "\n/* different release */\n");
    $forged = json_decode($raw, true); $forged['files'][\OKTV\PlaybackRelease\ADDED[0]] = hash_file('sha256', $asset);
    $forged['release_id'] = 'oktv-playback-3.3.6-' . substr(hash('sha256', \OKTV\PlaybackRelease\canonical($forged['files'])), 0, 16);
    file_put_contents($root . '/release.json', json_encode($forged));
    package_reject(fn() => verify($root, $id), 'Self-rewritten manifest cannot replace the externally reviewed identity');
    file_put_contents($asset, $css); file_put_contents($root . '/release.json', $raw);
    $manifest = json_decode($raw, true); $manifest['default_enabled'] = true;
    file_put_contents($root . '/release.json', json_encode($manifest));
    package_reject(fn() => verify($root, $id), 'Unexpected enabled-by-default release rejected'); file_put_contents($root . '/release.json', $raw);
    package_check(verify($root, $id)['release_id'] === $id, 'Restored fixture verifies');
    echo $passes . ' packaging checks passed.' . PHP_EOL;
} finally {
    foreach ($roots as $root) {
        \OKTV\PlaybackRelease\privateRoot($root);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) {
            if ($entry->isDir() && !$entry->isLink()) rmdir($entry->getPathname()); else unlink($entry->getPathname());
        }
        rmdir($root);
    }
}
