<?php
// Isolated WordPress boundary checks. This does not bootstrap or modify a site.
define('ABSPATH', __DIR__);
$runtime = dirname(__DIR__, 2) . '/plugins/offkilter-arcana/';
$asset_fixture = ($argv[1] ?? '') === '--asset-cache-fixture';
$assets = $runtime;
if ($asset_fixture) {
    umask(0077);
    $assets = '/private/tmp/oktv-playback-assets.' . bin2hex(random_bytes(8)) . '/';
    mkdir($assets . 'assets/css', 0700, true); mkdir($assets . 'assets/js', 0700, true);
    file_put_contents($assets . 'assets/css/ok-playback-help.css', 'SYNTHETIC_CSS_1');
    file_put_contents($assets . 'assets/js/ok-playback-help.js', 'SYNTHETIC_JS_1');
    register_shutdown_function(static function () use ($assets) {
        unlink($assets . 'assets/css/ok-playback-help.css'); unlink($assets . 'assets/js/ok-playback-help.js');
        rmdir($assets . 'assets/css'); rmdir($assets . 'assets/js'); rmdir($assets . 'assets'); rmdir($assets);
    });
}
define('OKARCANA_PLUGIN_DIR', $assets);
define('OKARCANA_PLUGIN_URL', 'https://fixture.invalid/plugin/');
require $runtime . 'includes/class-okarcana-playback-help.php';

$env = array();
function apply_filters($name, $value) { return $GLOBALS['env']['enabled'] ?? $value; }
function is_admin() { return $GLOBALS['env']['admin'] ?? false; }
function is_singular($type) { return $type === ($GLOBALS['env']['type'] ?? 'page'); }
function get_queried_object_id() { return 42; }
function get_post_status($id) { return $GLOBALS['env']['status'] ?? 'publish'; }
function post_password_required() { return $GLOBALS['env']['password'] ?? false; }
function get_post_meta($id, $key, $single) {
    if ($key !== 'beeteam368_video_url') throw new RuntimeException('Unexpected metadata read');
    $GLOBALS['env']['meta_reads'] = ($GLOBALS['env']['meta_reads'] ?? 0) + 1;
    return $GLOBALS['env']['url'] ?? 'https://www.youtube.com/watch?v=fixture';
}
function wp_parse_url($url) { return parse_url($url); }
function __($value, $domain) { return $value; }
function wp_json_encode($value) { return json_encode($value); }
function wp_enqueue_style(...$args) { $GLOBALS['env']['css'][] = $args; }
function wp_enqueue_script(...$args) { $GLOBALS['env']['js'][] = $args; }
function wp_add_inline_script($handle, $data, $position) { $GLOBALS['env']['inline'] = $data; }
function check($condition, $name) {
    if (!$condition) throw new RuntimeException($name);
    echo 'PASS ' . $name . PHP_EOL;
}
function run_case($values) {
    $GLOBALS['env'] = $values;
    $_GET = $values['query'] ?? array();
    OKArcana_Playback_Help::enqueue_assets();
    $result = $GLOBALS['env'];
    if (isset($result['inline'])) {
        $json = substr($result['inline'], strlen('window.OKTVPlaybackHelpConfig = '), -1);
        $result['config'] = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
    return $result;
}

check(!isset(run_case(array())['js']), 'Feature is off by default');
check(!isset(run_case(array('enabled' => true, 'admin' => true))['js']), 'No admin assets');
$page = run_case(array('enabled' => true));
check(!$page['config']['video'] && !isset($page['meta_reads']), 'Non-video pages only get preference bridge');
foreach (array(array('password' => true), array('status' => 'draft'), array('status' => 'private')) as $extra) {
    $result = run_case(array_merge(array('enabled' => true, 'type' => 'vidmov_video'), $extra));
    check(!$result['config']['video'] && !isset($result['meta_reads']), 'Protected or unpublished content excluded: ' . json_encode($extra));
}
$video = run_case(array('enabled' => true, 'type' => 'vidmov_video'));
check($video['config']['video'] && $video['config']['category'] === 'performance', 'Published VidMov YouTube video identified');
check(!str_contains($video['inline'], 'watch?v=fixture'), 'Source URL is never exposed by help config');
check(count($video['css']) === 1 && count($video['js']) === 1, 'One stylesheet and script');
check(str_contains($video['config']['strings']['preferencesRequested'], 'If privacy controls do not open'), 'Preference action copy does not promise a successful dialog');
check($video['js'][0][3] === substr(hash_file('sha256', OKARCANA_PLUGIN_DIR . 'assets/js/ok-playback-help.js'), 0, 16)
    && $video['js'][0][4] === true, 'Content-versioned footer script');
check($video['css'][0][3] === substr(hash_file('sha256', OKARCANA_PLUGIN_DIR . 'assets/css/ok-playback-help.css'), 0, 16), 'Stylesheet has its own content version');
foreach (array('1', array('1')) as $trailer) {
    $result = run_case(array('enabled' => true, 'type' => 'vidmov_video', 'query' => array('trailer' => $trailer)));
    check($result['config']['video'] && $result['config']['category'] === '' && !isset($result['meta_reads']), 'Trailer request stays generic rather than inheriting the main provider category');
}
foreach (array('https://www.youtube.com/watch?v=x', 'https://youtu.be/x', 'https://www.youtube-nocookie.com/embed/x', 'https://M.YOUTUBE.COM/watch?v=x') as $url) {
    check(OKArcana_Playback_Help::consent_category($url) === 'performance', 'Known provider: ' . $url);
}
foreach (array(null, array(), '<iframe src="https://youtube.com/embed/x">', '//youtube.com/x', 'javascript:alert(1)',
    'https://youtube.com.attacker.test/x', 'https://youtube.com@attacker.test/x', 'https://user@youtube.com/x',
    ' https://youtube.com/x', 'https://bunny.net/video', '/video/local.mp4') as $url) {
    check(OKArcana_Playback_Help::consent_category($url) === '', 'Unknown or malformed source stays generic: ' . json_encode($url));
}
if ($asset_fixture) {
    $values = array('enabled' => true, 'type' => 'vidmov_video');
    $js = OKARCANA_PLUGIN_DIR . 'assets/js/ok-playback-help.js';
    $css = OKARCANA_PLUGIN_DIR . 'assets/css/ok-playback-help.css';
    touch($js, 315532800); touch($css, 315532800); clearstatcache();
    $first = run_case($values);
    touch($js, 1700000000); clearstatcache();
    check(run_case($values)['js'][0][3] === $first['js'][0][3], 'Timestamp-only packaging change does not change script identity');
    file_put_contents($js, 'SYNTHETIC_JS_2'); touch($js, 315532800); clearstatcache();
    $changed = run_case($values);
    check($changed['js'][0][3] !== $first['js'][0][3], 'Changed script bytes invalidate cache even at identical archive timestamps');
    check($changed['css'][0][3] === $first['css'][0][3], 'Script change does not churn the unchanged stylesheet identity');
    file_put_contents($css, 'SYNTHETIC_CSS_2'); touch($css, 315532800); clearstatcache();
    check(run_case($values)['css'][0][3] !== $first['css'][0][3], 'Changed stylesheet bytes invalidate cache at identical timestamps');
}
