<?php
// Seed only the marked, disposable SQLite lab. No editorial candidate dependency.
$lab = dirname(rtrim(ABSPATH, '/'));
if (wp_get_environment_type() !== 'local' || !defined('DB_ENGINE') || DB_ENGINE !== 'sqlite'
    || @trim(file_get_contents($lab . '/.oktv-lab')) !== 'OKTV_DISPOSABLE_LOCAL_LAB'
    || wp_parse_url(home_url('/'), PHP_URL_HOST) !== 'www.offkilter.test'
    || !post_type_exists('vidmov_video') || !class_exists('beeteam368_video_player')
    || class_exists('OKTV_Editorial_Home') || get_option('oktv_local_fixture_manifest')) {
    throw new RuntimeException('Expected an unseeded, playback-only real-theme lab.');
}
$users = array();
foreach (array('editor' => 'editor', 'creator' => 'author', 'member' => 'subscriber') as $name => $role) {
    $id = wp_insert_user(array('user_login' => 'local_' . $name, 'user_pass' => wp_generate_password(32),
        'user_email' => $name . '@offkilter.test', 'display_name' => 'LOCAL TEST ' . ucfirst($name), 'role' => $role));
    if (is_wp_error($id)) throw new RuntimeException($id->get_error_message());
    $users[$name] = $id;
}
$posts = array('companion' => wp_insert_post(array('post_type' => 'vidmov_video', 'post_status' => 'publish',
    'post_title' => 'LOCAL TEST Companion', 'post_name' => 'local-test-companion',
    'post_content' => 'Synthetic playback fixture only.', 'post_author' => $users['creator']), true));
if (is_wp_error($posts['companion'])) throw new RuntimeException('Cannot create synthetic video.');
update_post_meta($posts['companion'], 'beeteam368_video_url', 'https://www.youtube.com/watch?v=LOCAL_TEST');
$pages = array('home' => wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'LOCAL TEST Home', 'post_name' => 'local-test-home', 'post_content' => 'LEGACY_HOMEPAGE_SENTINEL'), true));
if (is_wp_error($pages['home'])) throw new RuntimeException('Cannot create synthetic homepage.');
update_option('show_on_front', 'page');
update_option('page_on_front', $pages['home']);
$options = get_option('beeteam368_theme_options', array());
$options['beeteam368_nav_layout'] = 'alyssa';
$options['beeteam368_light_dark_mode'] = 'light';
update_option('beeteam368_theme_options', $options);
update_option('oktv_local_fixture_manifest', compact('users', 'posts', 'pages'), false);
file_put_contents($lab . '/private/manifest.json', wp_json_encode(compact('users', 'posts', 'pages')));
foreach ($users as $name => $id) {
    $expires = time() + 3600;
    $token = WP_Session_Tokens::get_instance($id)->create($expires);
    $header = 'Cookie: ' . SECURE_AUTH_COOKIE . '=' . wp_generate_auth_cookie($id, $expires, 'secure_auth', $token)
        . '; ' . LOGGED_IN_COOKIE . '=' . wp_generate_auth_cookie($id, $expires, 'logged_in', $token);
    file_put_contents($lab . '/private/' . $name . '-header', $header);
    chmod($lab . '/private/' . $name . '-header', 0600);
}
flush_rewrite_rules(false);
echo "Seeded synthetic playback-only users, video and native homepage.\n";
