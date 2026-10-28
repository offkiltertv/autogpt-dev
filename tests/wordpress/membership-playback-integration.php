<?php
// Actual ARMember and VidMov; synthetic accounts/plans only in a separate offline clone.
global $wpdb, $ARMember, $arm_restriction, $lab, $checks, $captures;
$lab = dirname(rtrim(ABSPATH, '/'));
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local'
    || !defined('DB_ENGINE') || DB_ENGINE !== 'sqlite'
    || wp_parse_url(home_url('/'), PHP_URL_HOST) !== 'www.offkilter.test'
    || @trim(file_get_contents($lab . '/.oktv-membership-lab')) !== 'OKTV_MEMBERSHIP_SYNTHETIC_ONLY'
    || !defined('MEMBERSHIP_VERSION') || MEMBERSHIP_VERSION !== '7.9.1'
    || !defined('MEMBERSHIPLITE_VERSION') || MEMBERSHIPLITE_VERSION !== '6.1'
    || !class_exists('beeteam368_membership') || !class_exists('OKArcana_Playback_Help')
    || class_exists('OKTV_Editorial_Home')) throw new RuntimeException('Expected private membership/playback-only clone.');
umask(0077);
$runLock = fopen($lab . '/private/membership-run.lock', 'c');
if (!$runLock || !flock($runLock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Membership fixture run already active; refusing overlap.');
$runId = bin2hex(random_bytes(16));
$reportPath = $lab . '/private/membership-results.json';
$runStatus = array('status' => 'running', 'runId' => $runId, 'startedAt' => gmdate('c'));
if (file_put_contents($reportPath, json_encode($runStatus, JSON_THROW_ON_ERROR)) === false) throw new RuntimeException('Cannot invalidate prior membership result.');
// A fatal/exception cannot leave an older successful report looking current.
register_shutdown_function(function () use (&$runStatus, $reportPath, $runLock) {
    if ($runStatus['status'] !== 'passed') {
        $runStatus['status'] = 'failed';
        $runStatus['finishedAt'] = gmdate('c');
        file_put_contents($reportPath, json_encode($runStatus, JSON_THROW_ON_ERROR));
    }
    flock($runLock, LOCK_UN);
    fclose($runLock);
});
require dirname(__DIR__, 2) . '/deployment/playback-release/package.php';
$release = json_decode(file_get_contents($lab . '/private/playback-release.json'), true, 512, JSON_THROW_ON_ERROR);
if (\OKTV\PlaybackRelease\tree(WP_PLUGIN_DIR . '/offkilter-arcana') !== $release['files']) throw new RuntimeException('Package drift.');
$checks = 0;
$captures = array();
function member_check($condition, $description) {
    if (!$condition) throw new RuntimeException('FAIL: ' . $description);
    $GLOBALS['checks']++;
    echo 'PASS: ' . $description . PHP_EOL;
}
function member_http($route, $header = '') {
    $curl = curl_init('http://127.0.0.1:8794' . $route);
    curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => array_filter(array('Host: www.offkilter.test', $header))));
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException('Loopback membership request failed: ' . curl_error($curl));
    $dom = new DOMDocument(); @$dom->loadHTML($body !== '' ? $body : '<html></html>'); $x = new DOMXPath($dom);
    $inline = $x->evaluate('string(//script[@id="oktv-playback-help-js-before"])');
    $config = preg_match('~window\.OKTVPlaybackHelpConfig\s*=\s*(\{.*\});~s', $inline, $match)
        ? json_decode($match[1], true, 512, JSON_THROW_ON_ERROR) : null;
    $count = fn($name) => $x->query('//*[contains(concat(" ",normalize-space(@class)," ")," ' . $name . ' ")]')->length;
    return array('status' => curl_getinfo($curl, CURLINFO_RESPONSE_CODE), 'body' => $body, 'config' => $config,
        'redirect' => curl_getinfo($curl, CURLINFO_REDIRECT_URL),
        'player' => $count('beeteam368-player-control'), 'protected' => $count('beeteam368-player-protect'),
        'helperScripts' => $x->query('//script[@id="oktv-playback-help-js"]')->length,
        'identity' => array_map(fn($q) => $x->evaluate('string(' . $q . ')'), array('//title', '//h1', '//link[@rel="canonical"]/@href')));
}
function member_scenario($name, $route, $header, $allowed, $media = 'LOCAL_MEMBER_ONLY') {
    $switch = $GLOBALS['lab'] . '/private/disable-playback';
    file_put_contents($switch, 'LOCAL_TEST_ONLY');
    $off = member_http($route, $header);
    unlink($switch);
    $on = member_http($route, $header);
    member_check($off['status'] === 200 && $on['status'] === 200, $name . ': off/on requests complete 200');
    member_check(($off['player'] === 1) === $allowed && ($on['player'] === 1) === $allowed,
        $name . ': native player access is unchanged and correct');
    member_check($off['identity'] === $on['identity'], $name . ': native title/H1/canonical unchanged');
    member_check($off['helperScripts'] === 0 && $on['helperScripts'] === 1, $name . ': only on-state loads helper');
    if (!$allowed) {
        member_check($off['protected'] > 0 && $on['protected'] > 0, $name . ': membership placeholder retained');
        member_check(!str_contains($off['body'], $media) && !str_contains($on['body'], $media),
            $name . ': protected media sentinel absent from HTML');
    } else {
        member_check(str_contains($on['body'], $media), $name . ': expected synthetic media still belongs to native player');
    }
    $file = $GLOBALS['lab'] . '/private/membership-' . $name . '.html';
    file_put_contents($file, $on['body']); chmod($file, 0600);
    $GLOBALS['captures'][] = array('name' => $name, 'html' => basename($file), 'sha256' => hash_file('sha256', $file), 'allowed' => $allowed);
    return $on;
}

$fixture = get_option('oktv_membership_test_fixture_v1');
if (!$fixture) {
    $fixture = array('plans' => array(), 'users' => array());
    foreach (array('primary', 'other') as $name) {
        $prior = $wpdb->get_var($wpdb->prepare("SELECT arm_subscription_plan_id FROM {$ARMember->tbl_arm_subscription_plans} WHERE arm_subscription_plan_name=%s", 'LOCAL TEST ' . $name));
        if ($prior) { $fixture['plans'][$name] = (int)$prior; continue; }
        $ok = $wpdb->insert($ARMember->tbl_arm_subscription_plans, array(
            'arm_subscription_plan_name' => 'LOCAL TEST ' . $name,
            'arm_subscription_plan_type' => 'free', 'arm_subscription_plan_amount' => 0,
            'arm_subscription_plan_status' => 1, 'arm_subscription_plan_role' => 'subscriber',
            'arm_subscription_plan_options' => maybe_serialize(array('expiry_type' => 'infinite', 'payment_type' => 'one_time')),
        ));
        if (!$ok) throw new RuntimeException('Synthetic plan insert failed: ' . $wpdb->last_error);
        $fixture['plans'][$name] = (int)$wpdb->insert_id;
    }
    foreach (array('active', 'no_plan', 'wrong_plan', 'suspended', 'editor', 'admin') as $name) {
        $login = 'membership_fixture_' . $name;
        $prior = get_user_by('login', $login);
        if ($prior && ($prior->display_name !== 'LOCAL TEST ' . $name || !str_ends_with($prior->user_email, '@offkilter.test'))) throw new RuntimeException('Unexpected existing fixture identity.');
        $id = $prior ? $prior->ID : wp_insert_user(array('user_login' => $login,
            'user_pass' => wp_generate_password(40), 'user_email' => $login . '@offkilter.test',
            'display_name' => 'LOCAL TEST ' . $name,
            'role' => $name === 'admin' ? 'administrator' : ($name === 'editor' ? 'editor' : 'subscriber')));
        if (is_wp_error($id)) throw new RuntimeException($id->get_error_message());
        $fixture['users'][$name] = $id;
        update_user_meta($id, 'arm_user_plan_ids', in_array($name, array('active', 'suspended'), true)
            ? array($fixture['plans']['primary']) : ($name === 'wrong_plan' ? array($fixture['plans']['other']) : array()));
        update_user_meta($id, 'arm_user_suspended_plan_ids', $name === 'suspended' ? array($fixture['plans']['primary']) : array());
    }
    $fixture['post'] = wp_insert_post(array('post_type' => 'vidmov_video', 'post_status' => 'publish',
        'post_name' => 'local-membership-check', 'post_title' => 'LOCAL TEST Membership Access',
        'post_content' => 'Synthetic access fixture. Not a real offer or video.',
        'post_author' => $fixture['users']['editor']), true);
    if (is_wp_error($fixture['post'])) throw new RuntimeException('Synthetic post failed.');
    update_post_meta($fixture['post'], 'beeteam368_video_url', 'https://www.youtube.com/watch?v=LOCAL_MEMBER_ONLY');
    foreach (array('category' => 'vidmov_video_category', 'tag' => 'post_tag') as $name => $taxonomy) {
        $term = wp_insert_term('LOCAL Membership ' . $name, $taxonomy);
        if (is_wp_error($term)) throw new RuntimeException('Synthetic term failed.');
        $fixture[$name] = (int)$term['term_id'];
        wp_set_object_terms($fixture['post'], array($fixture[$name]), $taxonomy);
    }
    update_option('oktv_membership_test_fixture_v1', $fixture, false);
    flush_rewrite_rules(false);
}
$post = get_post($fixture['post']);
if (!$post || $post->post_title !== 'LOCAL TEST Membership Access') throw new RuntimeException('Unexpected fixture.');
$route = wp_parse_url(get_permalink($post), PHP_URL_PATH);
$key = 'beeteam368_membership_plans';
$saved = array('post' => get_post_meta($post->ID, $key, false),
    'category' => get_term_meta($fixture['category'], $key, false), 'tag' => get_term_meta($fixture['tag'], $key, false),
    'trailer' => get_post_meta($post->ID, 'beeteam368_video_url_preview', false),
    'global' => get_option('arm_global_settings'));
$switch = $lab . '/private/disable-playback';
if (file_exists($switch)) throw new RuntimeException('Existing flag override; refusing to overwrite.');
$headers = array('anonymous' => '');
foreach ($fixture['users'] as $name => $id) {
    $expires = time() + 3600;
    $token = WP_Session_Tokens::get_instance($id)->create($expires);
    $headers[$name] = 'Cookie: ' . SECURE_AUTH_COOKIE . '=' . wp_generate_auth_cookie($id, $expires, 'secure_auth', $token)
        . '; ' . LOGGED_IN_COOKIE . '=' . wp_generate_auth_cookie($id, $expires, 'logged_in', $token);
}
try {
    member_check(OKARCANA_VERSION === '3.3.6' && MEMBERSHIP_VERSION === '7.9.1', 'Exact playback candidate and captured ARMember are loaded');
    member_check(shortcode_exists('arm_restrict_content') && has_filter('beeteam368_media_protect_html'), 'Native membership integration is registered');
    delete_post_meta($post->ID, $key);
    delete_term_meta($fixture['category'], $key);
    delete_term_meta($fixture['tag'], $key);
    member_scenario('public-anonymous', $route, '', true);
    update_post_meta($post->ID, $key, array($fixture['plans']['primary']));
    foreach ($headers as $name => $header) {
        $allowed = in_array($name, array('active', 'admin'), true);
        wp_set_current_user($fixture['users'][$name] ?? 0);
        member_check($arm_restriction->arm_check_content_hasaccess(array($fixture['plans']['primary']), 'show') === $allowed,
            $name . ': actual ARMember decision matches fixture expectations');
        wp_set_current_user(0);
        member_scenario('direct-' . $name, $route, $header, $allowed);
    }
    delete_post_meta($post->ID, $key);
    foreach (array('category', 'tag') as $source) {
        update_term_meta($fixture[$source], $key, array($fixture['plans']['primary']));
        member_check(beeteam368_membership::get_post_protect_plans($post->ID) === array($fixture['plans']['primary']), $source . ': native inheritance selects the real plan');
        member_scenario($source . '-anonymous', $route, '', false);
        member_scenario($source . '-active', $route, $headers['active'], true);
        delete_term_meta($fixture[$source], $key);
    }
    update_post_meta($post->ID, $key, array($fixture['plans']['primary']));
    update_post_meta($post->ID, 'beeteam368_video_url_preview', 'https://www.youtube.com/watch?v=LOCAL_TRAILER_ONLY');
    $trailer = member_scenario('trailer-anonymous', $route . '?trailer=1', '', true, 'LOCAL_TRAILER_ONLY');
    member_check(!str_contains($trailer['body'], 'LOCAL_MEMBER_ONLY'), 'Trailer does not expose the protected main-media sentinel');
    member_check($trailer['config']['category'] === '', 'Trailer helper uses generic context, not the main provider classification');
    $restricted = maybe_unserialize($saved['global']);
    if (!is_array($restricted)) throw new RuntimeException('Unexpected synthetic ARMember global settings.');
    $restricted['general_settings']['restrict_site_access'] = 1;
    $restricted['page_settings']['guest_page_id'] = 0;
    update_option('arm_global_settings', $restricted);
    file_put_contents($switch, 'LOCAL_TEST_ONLY');
    $off = member_http($route);
    unlink($switch);
    $on = member_http($route);
    member_check($off['status'] === 302 && $on['status'] === 302, 'Global site restriction still redirects guests before rendering');
    member_check($off['redirect'] === $on['redirect'] && str_starts_with($on['redirect'], home_url('/wp-login.php')), 'Global native login destination unchanged');
    member_check($on['player'] === 0 && $on['config'] === null && !str_contains($on['body'], 'LOCAL_MEMBER_ONLY'), 'Globally restricted guest response has no player, media or helper configuration');
    member_scenario('global-active', $route, $headers['active'], true);
} finally {
    wp_set_current_user(0);
    if (file_exists($switch)) unlink($switch);
    delete_post_meta($post->ID, $key);
    foreach ($saved['post'] as $value) add_post_meta($post->ID, $key, $value);
    foreach (array('category', 'tag') as $source) {
        delete_term_meta($fixture[$source], $key);
        foreach ($saved[$source] as $value) add_term_meta($fixture[$source], $key, $value);
    }
    delete_post_meta($post->ID, 'beeteam368_video_url_preview');
    foreach ($saved['trailer'] as $value) add_post_meta($post->ID, 'beeteam368_video_url_preview', $value);
    update_option('arm_global_settings', $saved['global']);
}
member_check(get_post_meta($post->ID, $key, false) === $saved['post']
    && get_term_meta($fixture['category'], $key, false) === $saved['category']
    && get_term_meta($fixture['tag'], $key, false) === $saved['tag']
    && get_post_meta($post->ID, 'beeteam368_video_url_preview', false) === $saved['trailer']
    && get_option('arm_global_settings') === $saved['global'] && !file_exists($switch), 'All temporary access/feature/global/trailer settings restored');
$result = array_merge($runStatus, array('status' => 'passed', 'mode' => 'PRIVATE_SYNTHETIC_ARMember_NOT_PRODUCTION',
    'at' => gmdate('c'), 'release' => $release['release_id'], 'checks' => $checks, 'captures' => $captures));
if (file_put_contents($reportPath, wp_json_encode($result, JSON_PRETTY_PRINT)) === false) throw new RuntimeException('Cannot publish membership result.');
$runStatus['status'] = 'passed';
echo $checks . ' real ARMember/VidMov playback access checks passed.' . PHP_EOL;
