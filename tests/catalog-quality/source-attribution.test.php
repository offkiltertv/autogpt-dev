<?php
declare(strict_types=1);
define('ABSPATH','/synthetic/');
function add_filter(...$args){}function add_action(...$args){}
function get_post_type($id){return $GLOBALS['types'][$id]??'vidmov_video';}
function get_post_meta($id,$key,$single){return $GLOBALS['meta'][$id][$key]??'';}
function get_post_type_archive_link($type){return 'https://www.offkilter.test/video/';}
function add_query_arg($key,$value,$url){return $url.'?'.http_build_query([$key=>$value]);}
function esc_html($v){return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}function esc_attr($v){return esc_html($v);}function esc_url($v){return esc_html($v);}
function sanitize_text_field($v){return trim(strip_tags($v));}
require dirname(__DIR__,2).'/deployment/catalog-quality/oktv-source-attribution.php';
use OffKilter\CatalogQuality\SourceAttribution as Source;
$passed=0;function check($ok,$name){if(!$ok)throw new RuntimeException($name);$GLOBALS['passed']++;echo 'PASS '.$name.PHP_EOL;}
$channel='UC'.str_repeat('a',22);
$meta=[1=>['channel_id'=>$channel,'channel_title'=>'A source'],2=>[Source::VERIFIED=>'1','channel_id'=>$channel,'channel_title'=>'A <script>source</script>']];
check(Source::context(1)===null,'Ordinary creator/member posts are not relabeled from matching source fields');
check(Source::context(2)['channel']===$channel,'Verified import attribution uses the provider channel ID');
$html=Source::markup(2,true);
check(str_contains($html,'YouTube source')&&!str_contains($html,'Subscribers')&&!str_contains($html,'author-verified'),'A source does not become a verified member or inherit follower counts');
check(!str_contains($html,'<script>')&&str_contains($html,'A source'),'Untrusted source names are rendered as plain text');
check(str_contains($html,'oktv_source_channel=')&&str_contains($html,'https://www.youtube.com/channel/'.$channel),'Local source collection and original channel are distinct links');
$meta[2]['channel_id']='UC'.str_repeat('a',21).'"';check(Source::context(2)===null,'Malformed provider identifiers cannot form attribution URLs');
check(Source::collection_url('attacker')==='','Invalid collection input has no destination');
$meta[2]['channel_id']=$channel;$types[2]='post';check(Source::context(2)===null,'An article is not treated as an imported video');
check(!Source::enabled(),'Source presentation defaults off until its reviewed marker is present');
echo $passed.' source-attribution checks passed.'.PHP_EOL;
