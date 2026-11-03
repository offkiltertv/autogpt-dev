<?php
declare(strict_types=1);
define('ABSPATH','/synthetic/');define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY',true);
function add_filter(...$args){}function add_action(...$args){}
function wp_parse_url($url,$component=-1){return parse_url($url,$component);}
function sanitize_text_field($v){return trim(strip_tags($v));}
function wp_strip_all_tags($v,$removeBreaks=false){return trim(strip_tags($v));}
function esc_html($v){return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}function esc_url($v){return esc_html($v);}
function wpautop($v){return '<p>'.$v.'</p>';}
require dirname(__DIR__,2).'/deployment/catalog-quality/catalog-inventory.php';
require dirname(__DIR__,2).'/deployment/catalog-quality/oktv-source-attribution.php';
require dirname(__DIR__,2).'/deployment/catalog-quality/catalog-verified-import.php';
use OffKilter\CatalogQuality\VerifiedImport as Import;
$passed=0;
function check($ok,$name){if(!$ok)throw new RuntimeException($name);$GLOBALS['passed']++;echo 'PASS '.$name.PHP_EOL;}
function rejects(callable $run,string $reason){try{$run();}catch(RuntimeException $e){check($e->getMessage()===$reason,$reason);return;}throw new RuntimeException('Expected '.$reason);}
$id='aaaaaaaaaaa';$channel='UC'.str_repeat('b',22);
$video=['id'=>$id,'snippet'=>['title'=>'A source video','description'=>'<script>alert(1)</script>','channelId'=>$channel,'channelTitle'=>'<b>Source name</b>','publishedAt'=>'2026-01-01T12:00:00Z','thumbnails'=>['high'=>['url'=>'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg']]],'status'=>['privacyStatus'=>'public','embeddable'=>true,'uploadStatus'=>'processed']];
$source=Import::source($video,$id);
check($source['channel']===$channel&&$source['channel_title']==='Source name','Provider source is a channel identifier with a plain-text name');
check($source['published_gmt']==='2026-01-01 12:00:00','Provider publication time is distinct from import time');
check(!str_contains(Import::content($source),'<script>')&&str_contains(Import::content($source),'&lt;script&gt;'),'Provider descriptions cannot introduce active markup');
check(str_contains(Import::content($source),'Curated on OKTV')&&str_contains(Import::content($source),'youtube.com/channel/'.$channel),'Source credit survives presentation rollback in the post body');
rejects(fn()=>Import::source($video,'bbbbbbbbbbb'),'source_not_available');
$bad=$video;$bad['status']['embeddable']=false;rejects(fn()=>Import::source($bad,$id),'source_not_available');
$bad=$video;$bad['snippet']['channelId']='malformed';rejects(fn()=>Import::source($bad,$id),'source_metadata_invalid');
$bad=$video;$bad['snippet']['title']='Deleted video';rejects(fn()=>Import::source($bad,$id),'source_metadata_invalid');
$bad=$video;$bad['snippet']['publishedAt']='2099-01-01T00:00:00Z';rejects(fn()=>Import::source($bad,$id),'source_date_invalid');
foreach(['http://i.ytimg.com/vi/'.$id.'/hqdefault.jpg','https://evil.test/vi/'.$id.'/hqdefault.jpg','https://i.ytimg.com@evil.test/vi/'.$id.'/hqdefault.jpg','https://i.ytimg.com:443/vi/'.$id.'/hqdefault.jpg','https://i.ytimg.com/vi/bbbbbbbbbbb/hqdefault.jpg','https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg?redirect=1'] as $url)rejects(fn()=>Import::thumbnail(['high'=>['url'=>$url]],$id),'trusted_source_thumbnail_missing');
echo $passed.' verified-import checks passed.'.PHP_EOL;
