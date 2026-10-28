<?php
// Real WordPress/VidMov with the playback-only package and synthetic fixture data.
global $lab, $passes;
require dirname(__DIR__, 2) . '/deployment/playback-release/package.php';
$lab = dirname(rtrim(ABSPATH, '/'));
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local'
    || !defined('DB_ENGINE') || DB_ENGINE !== 'sqlite' || wp_parse_url(home_url('/'), PHP_URL_HOST) !== 'www.offkilter.test'
    || @trim(file_get_contents($lab . '/.oktv-lab')) !== 'OKTV_DISPOSABLE_LOCAL_LAB'
    || !is_file($lab . '/private/playback-release.json') || !class_exists('beeteam368_video_player')
    || !class_exists('OKArcana_Playback_Help') || class_exists('OKTV_Editorial_Home')) throw new RuntimeException('Expected isolated playback-only lab.');
$manifest = json_decode(file_get_contents($lab . '/private/playback-release.json'), true, 512, JSON_THROW_ON_ERROR);
if (\OKTV\PlaybackRelease\tree(WP_PLUGIN_DIR . '/offkilter-arcana') !== $manifest['files']) throw new RuntimeException('Lab payload differs from candidate.');
$fixture = get_option('oktv_local_fixture_manifest');
$post = get_post($fixture['posts']['companion'] ?? 0);
if (!$post || $post->post_title !== 'LOCAL TEST Companion' || $post->post_type !== 'vidmov_video') throw new RuntimeException('Missing synthetic video.');
$switch = $lab . '/private/disable-playback';
if (file_exists($switch)) throw new RuntimeException('Existing flag override; do not overwrite.');
$passes = 0;
function release_check($ok, $message) {
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    $GLOBALS['passes']++; echo 'PASS: ' . $message . PHP_EOL;
}
function release_http($path, $editor = false) {
    $headers = array('Host: www.offkilter.test');
    if ($editor) $headers[] = trim(file_get_contents($GLOBALS['lab'] . '/private/editor-header'));
    $curl = curl_init('http://127.0.0.1:8793' . $path);
    curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_FOLLOWLOCATION => false));
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException('Loopback request failed.');
    $dom = new DOMDocument(); @$dom->loadHTML($body); $x = new DOMXPath($dom);
    $s = $x->evaluate('string(//script[@id="oktv-playback-help-js-before"])');
    $config = null;
    if (preg_match('~window\.OKTVPlaybackHelpConfig\s*=\s*(\{.*\});~s', $s, $m)) $config = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    return array('status' => curl_getinfo($curl, CURLINFO_RESPONSE_CODE), 'body' => $body, 'x' => $x, 'config' => $config);
}
function release_identity($result) {
    return array_map(fn($query) => $result['x']->evaluate('string(' . $query . ')'), array('//title', '//h1', '//link[@rel="canonical"]/@href'));
}
function release_scripts($result) {
    $urls = array(); foreach ($result['x']->query('//script[@src and not(@id="oktv-playback-help-js")]/@src') as $attr) $urls[] = $attr->nodeValue;
    return $urls;
}
$saved = array(); foreach (array('beeteam368_login_to_watch', 'beeteam368_video_url') as $key) $saved[$key] = get_post_meta($post->ID, $key, false);
$password = $post->post_password;
$url = wp_parse_url(get_permalink($post), PHP_URL_PATH);
try {
    release_check(OKARCANA_VERSION === '3.3.6', 'Versioned playback package is actually loaded');
    release_check(!class_exists('OKTV_Editorial_Home') && !class_exists('OKTV_Editorial_SEO') && !class_exists('OKTV_Editorial_Archives'), 'No unpublished editorial modules are loaded');
    file_put_contents($switch, 'LOCAL_TEST_ONLY');
    $before = array('home' => release_http('/'), 'video' => release_http($url));
    foreach ($before as $kind => $result) {
        release_check($result['status'] === 200 && $result['config'] === null, 'Feature off: native ' . $kind . ' remains 200 without helper configuration');
        release_check($result['x']->query('//script[@id="oktv-playback-help-js"]|//link[@id="oktv-playback-help-css"]')->length === 0, 'Feature off: no helper assets on ' . $kind);
    }
    unlink($switch);
    $after = array('home' => release_http('/'), 'video' => release_http($url));
    foreach ($after as $kind => $result) {
        release_check($result['status'] === 200 && $result['x']->query('//script[@id="oktv-playback-help-js"]')->length === 1, 'Feature on: exactly one helper on ' . $kind);
        release_check(release_identity($result) === release_identity($before[$kind]), 'Native title/H1/canonical unchanged: ' . $kind);
        release_check(release_scripts($result) === release_scripts($before[$kind]), 'Native script ownership/order unchanged: ' . $kind);
        release_check(!str_contains($result['body'], 'oktv-home-lead-grid'), 'No editorial homepage substitution: ' . $kind);
    }
    release_check(str_contains($after['home']['body'], 'LEGACY_HOMEPAGE_SENTINEL') && $after['home']['config']['video'] === false, 'Homepage only gets existing-footer preference bridge');
    release_check($after['video']['config']['video'] && $after['video']['config']['category'] === 'performance', 'YouTube context uses observed performance category');
    release_check(!str_contains(json_encode($after['video']['config']), 'LOCAL_TEST'), 'Help configuration exposes no provider URL or video identifier');
    $player = '//*[contains(concat(" ",normalize-space(@class)," ")," beeteam368-player-control ")]';
    release_check($after['video']['x']->query($player)->length === 1, 'Native anonymous player remains present');
    $trailer = release_http($url . '?trailer=1');
    release_check($trailer['status'] === 200 && $trailer['config']['category'] === '', 'Native trailer request gets generic help, not inherited main-provider consent');
    update_post_meta($post->ID, 'beeteam368_login_to_watch', 'on');
    $protected = release_http($url);
    release_check($protected['status'] === 200 && $protected['x']->query($player)->length === 0, 'Native login restriction still removes anonymous player');
    release_check(str_contains($protected['body'], 'beeteam368-player-login-protect'), 'Native protected placeholder remains visible');
    $editor = release_http($url, true);
    release_check($editor['x']->query($player)->length === 1 && str_contains($editor['x']->evaluate('string(//body/@class)'), 'logged-in'), 'Native editor access still uses real WordPress fixture session');
    delete_post_meta($post->ID, 'beeteam368_login_to_watch');
    wp_update_post(array('ID' => $post->ID, 'post_password' => 'LOCAL_TEST_PASSWORD'));
    $locked = release_http($url);
    release_check($locked['config']['video'] === false && $locked['config']['category'] === '', 'Password protection suppresses video/provider context');
    wp_update_post(array('ID' => $post->ID, 'post_password' => $password));
    update_post_meta($post->ID, 'beeteam368_video_url', 'https://media.example.invalid/local-test.mp4');
    $unknown = release_http($url);
    release_check($unknown['config']['video'] && $unknown['config']['category'] === '', 'Unknown direct-video provider remains generic');
    file_put_contents($switch, 'LOCAL_TEST_ONLY');
    $rollback = release_http($url);
    release_check($rollback['status'] === 200 && $rollback['config'] === null && release_identity($rollback) === release_identity($before['video']), 'Flag rollback removes helper and preserves native route identity');
} finally {
    if (is_file($switch)) unlink($switch);
    wp_update_post(array('ID' => $post->ID, 'post_password' => $password));
    foreach ($saved as $key => $values) { delete_post_meta($post->ID, $key); foreach ($values as $value) add_post_meta($post->ID, $key, $value); }
}
release_check(get_post($post->ID)->post_password === $password && get_post_meta($post->ID, 'beeteam368_video_url', false) === $saved['beeteam368_video_url']
    && get_post_meta($post->ID, 'beeteam368_login_to_watch', false) === $saved['beeteam368_login_to_watch'], 'All synthetic access/provider metadata restored');
echo $passes . ' playback-only real WordPress release checks passed.' . PHP_EOL;
