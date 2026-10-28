<?php
// Test the exact operator template, substituting only its private marker path.
define('ABSPATH', '/synthetic-only/');
define('OKARCANA_VERSION', '3.3.6');
class OKArcana_Playback_Help {}
$flags = array('admin'=>false,'logged_in'=>false,'ajax'=>false,'cron'=>false,'ssl'=>true,'singular'=>true,'id'=>7917);
$post = (object) array('post_status'=>'publish','post_password'=>'','post_name'=>'why-i-want-youtube-to-be-my-main-platform-streaming-stream');
function add_filter(...$args) {}
function add_action(...$args) {}
function is_admin() { return $GLOBALS['flags']['admin']; }
function is_user_logged_in() { return $GLOBALS['flags']['logged_in']; }
function wp_doing_ajax() { return $GLOBALS['flags']['ajax']; }
function wp_doing_cron() { return $GLOBALS['flags']['cron']; }
function is_ssl() { return $GLOBALS['flags']['ssl']; }
function is_singular($type) { return $type === 'vidmov_video' && $GLOBALS['flags']['singular']; }
function get_queried_object_id() { return $GLOBALS['flags']['id']; }
function get_post($id) { return $id === 7917 ? $GLOBALS['post'] : null; }
umask(0077);
$marker = tempnam(sys_get_temp_dir(), 'oktv-canary-test-');
$source = file_get_contents(dirname(__DIR__, 2) . '/deployment/playback-release/oktv-playback-canary.php');
$source = str_replace("'/opt/bitnami/oktv-release-state/playback-canary-enabled'", var_export($marker, true), $source, $count);
if ($count !== 1) throw new RuntimeException('Marker substitution changed');
eval(substr($source, 5));
$_SERVER = array('REQUEST_METHOD'=>'GET','HTTP_HOST'=>'www.offkilter.tv',
    'REQUEST_URI'=>'/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/');
$checks = 0;
function scope_check($expected) {
    if (oktv_playback_canary_ready() !== $expected) throw new RuntimeException('Canary scope mismatch');
    $GLOBALS['checks']++;
}
try {
    scope_check(false);
    file_put_contents($marker, 'wrong-release'); scope_check(false);
    file_put_contents($marker, "oktv-playback-3.3.6-db31a5a937a7d1af\n"); scope_check(true);
    foreach ($flags as $key=>$old) {
        $flags[$key] = $key === 'id' ? 7918 : !$old; scope_check(false); $flags[$key] = $old;
    }
    foreach (array('REQUEST_METHOD'=>'POST','HTTP_HOST'=>'offkilter.tv',
        'REQUEST_URI'=>'/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/?trailer=1') as $key=>$value) {
        $old = $_SERVER[$key]; $_SERVER[$key]=$value; scope_check(false); $_SERVER[$key]=$old;
    }
    foreach (array('post_status'=>'private','post_password'=>'locked','post_name'=>'other-video') as $key=>$value) {
        $old=$post->$key; $post->$key=$value; scope_check(false); $post->$key=$old;
    }
    $old=$post; $post=null; scope_check(false); $post=$old;
    unlink($marker); clearstatcache(); scope_check(false);
} finally { if (is_file($marker)) unlink($marker); }
echo "$checks canary scope checks passed.\n";
