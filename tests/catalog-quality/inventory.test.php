<?php
declare(strict_types=1);
define('ABSPATH','/synthetic/');
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY',true);
function add_filter(...$args){}function add_action(...$args){}function wp_parse_url($url,$component=-1){return parse_url($url,$component);}
require dirname(__DIR__,2).'/deployment/catalog-quality/catalog-inventory.php';
use OffKilter\CatalogQuality\CatalogInventory as Inventory;
$passed=0;
function check($ok,$label){if(!$ok)throw new RuntimeException($label);$GLOBALS['passed']++;echo 'PASS '.$label.PHP_EOL;}
function fails(callable $action,string $message){try{$action();}catch(RuntimeException $e){check($e->getMessage()===$message,$message);return;}throw new RuntimeException('Expected '.$message);}
$config=['cg_yt_playlist'=>'PL'.str_repeat('a',24)];
check(Inventory::decode_config(serialize($config))===$config,'Unencoded supported campaign configuration is read');
check(Inventory::decode_config(base64_encode(serialize($config)))===$config,'Current encoded campaign configuration is read');
fails(fn()=>Inventory::decode_config('garbage'),'campaign_config_invalid');
fails(fn()=>Inventory::playlist_id('https://youtube.com.evil.test/playlist?list='.$config['cg_yt_playlist']),'playlist_config_invalid');
$calls=0;$client=new Inventory(function($resource,$params)use(&$calls){$calls++;$second=isset($params['pageToken']);$out=['pageInfo'=>['totalResults'=>2],'items'=>[['contentDetails'=>['videoId'=>$second?'bbbbbbbbbbb':'aaaaaaaaaaa']]]];if(!$second)$out['nextPageToken']='next';return $out;});
$playlist=$client->playlist($config['cg_yt_playlist']);
check($calls===2&&$playlist['ids']===['aaaaaaaaaaa','bbbbbbbbbbb'],'The inventory follows paging through the last page');
$client=new Inventory(fn()=>['pageInfo'=>['totalResults'=>2],'items'=>[['contentDetails'=>['videoId'=>'aaaaaaaaaaa']]]]);
fails(fn()=>$client->playlist($config['cg_yt_playlist']),'playlist_response_incomplete');
$client=new Inventory(fn()=>['pageInfo'=>['totalResults'=>2],'items'=>[],'nextPageToken'=>'same']);
fails(fn()=>$client->playlist($config['cg_yt_playlist']),'playlist_pagination_loop');
$client=new Inventory(fn()=>['pageInfo'=>['totalResults'=>9000],'items'=>[]]);
fails(fn()=>$client->playlist($config['cg_yt_playlist']),'playlist_changed_or_exceeds_bound');
$available=['status'=>['privacyStatus'=>'public','embeddable'=>true,'uploadStatus'=>'processed'],'snippet'=>['title'=>'A real title']];
$details=['aaaaaaaaaaa'=>$available,'bbbbbbbbbbb'=>$available,'ccccccccccc'=>$available,'ddddddddddd'=>$available,'eeeeeeeeeee'=>['status'=>['privacyStatus'=>'public','embeddable'=>false]],'fffffffffff'=>['status'=>['privacyStatus'=>'public']]];
$library=['aaaaaaaaaaa'=>[['id'=>1,'status'=>'draft'],['id'=>2,'status'=>'publish']],'bbbbbbbbbbb'=>[['id'=>3,'status'=>'pending']],'ddddddddddd'=>[['id'=>4,'status'=>'trash']]];
$result=Inventory::reconcile(['aaaaaaaaaaa','aaaaaaaaaaa','bbbbbbbbbbb','ccccccccccc','ddddddddddd','eeeeeeeeeee','fffffffffff','ggggggggggg'],$details,$library);
check($result['eligible']===4&&$result['published']===1&&$result['draft']===1&&$result['other']===1&&$result['missing_ids']===['ccccccccccc'],'Cross-source coverage deduplicates IDs and distinguishes published, draft, other, and missing');
check($result['not_embeddable']===1&&$result['unknown']===1&&$result['unavailable']===1,'Provider unavailability, embedding restrictions, and unknown state remain distinct');
check(Inventory::availability($available)==='available','Available source can recover after a previous unavailable result');
$client=new Inventory(fn()=>['error'=>['message'=>'not an inventory']]);
fails(fn()=>$client->videos(['aaaaaaaaaaa']),'video_response_incomplete');
echo $passed.' inventory checks passed.'.PHP_EOL;
