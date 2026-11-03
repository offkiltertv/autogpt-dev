<?php
declare(strict_types=1);
if (!defined('WP_CLI') || !WP_CLI) { throw new RuntimeException('WP-CLI only.'); }
require_once dirname(__DIR__,2).'/deployment/catalog-quality/oktv-catalog-quality.php';
use OffKilter\CatalogQuality\CatalogQuality as Quality;
global $wpdb;
if (wp_parse_url(home_url('/'),PHP_URL_HOST)!=='www.offkilter.tv') { throw new RuntimeException('Unexpected site.'); }
$passes=0;
$check=static function($ok,$label)use(&$passes){if(!$ok)throw new RuntimeException('FAIL '.$label);$passes++;echo 'PASS '.$label.PHP_EOL;};
$args=['post_type'=>'vidmov_video','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','cache_results'=>false,'update_post_meta_cache'=>false,'update_post_term_cache'=>false];
$before=(new WP_Query($args))->posts;
$placeholders=array_map('intval',$wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='vidmov_video' AND post_status='publish' AND LOWER(TRIM(post_title)) IN ('deleted video','private video')"));
$check(count($before)>count($placeholders)&&count($placeholders)>0,'Production catalog has both ordinary videos and placeholder fixtures');
$filter=static fn($where,$query)=>Quality::discovery_where($where,$query);
add_filter('posts_where',$filter,99,2);
try{
 $after=(new WP_Query($args))->posts;
 $check(count($after)<count($before),'Discovery excludes unusable records');
 $check(!array_intersect($placeholders,$after),'No placeholder record remains in the qualified discovery result');
 $check(!array_diff($after,$before),'Filtering cannot introduce a different record');
 $first=array_slice($after,0,1);$selected=(new WP_Query(array_merge($args,['post__in'=>[$placeholders[0],$first[0]]])))->posts;
 $check(array_values(array_map('intval',$selected))===$first,'Hand-selected recommendation excludes a placeholder and retains the valid selection');
 $single=(new WP_Query(array_merge($args,['p'=>$placeholders[0]])))->posts;
 $check(count($single)===1&&(int)$single[0]===$placeholders[0],'Original unavailable video lookup remains accessible');
 $draftArgs=array_merge($args,['post_status'=>'draft']);$draftFiltered=(new WP_Query($draftArgs))->posts;
 remove_filter('posts_where',$filter,99);
 $draftBefore=(new WP_Query($draftArgs))->posts;
 $check($draftFiltered===$draftBefore,'Draft and editorial workflows are unchanged');
}finally{remove_filter('posts_where',$filter,99);}
$check((new WP_Query($args))->posts===$before,'Qualification leaves subsequent CLI queries unchanged');
echo json_encode(['assertions'=>$passes,'published_before'=>count($before),'discoverable_after'=>count($after),'placeholder_records'=>count($placeholders),'live_flag_enabled'=>Quality::enabled()],JSON_PRETTY_PRINT).PHP_EOL;
