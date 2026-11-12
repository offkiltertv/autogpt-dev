<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

if (!defined('WP_CLI') || !WP_CLI) { throw new \RuntimeException('WP-CLI only.'); }
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY', true);
require_once __DIR__.'/catalog-inventory.php';
if(!class_exists(SourceAttribution::class,false))require_once __DIR__.'/oktv-source-attribution.php';
global $wpdb;
if (wp_parse_url(home_url('/'),PHP_URL_HOST)!=='www.offkilter.tv') { throw new \RuntimeException('Unexpected site.'); }
$mode=$args[0]??'';$path=$args[1]??'';
if(!in_array($mode,['plan','apply'],true)||!is_string($path)||!is_file($path)||is_link($path))throw new \RuntimeException('Pass plan or apply and a private inventory receipt.');
$real=realpath($path);$stat=stat($path);
if(!$real||!str_starts_with($real,'/var/backups/oktv-recovery/')||$stat['uid']!==0||($stat['mode']&0077)!==0||$stat['size']>32*1024*1024)throw new \RuntimeException('Inventory receipt must remain root-only and outside the webroot.');
$inventory=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
$checked=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',(string)($inventory['checked_at']??''),new \DateTimeZone('UTC'));
if(($inventory['version']??null)!==1||!$checked||time()-$checked->getTimestamp()>14400||$checked->getTimestamp()>time()+30||!is_array($inventory['sources']??null)||!is_array($inventory['details']??null))throw new \RuntimeException('Inventory receipt is incomplete or stale.');
$verifyCredit=($args[2]??'')==='verify-source-credit';
$changes=[];$availablePlaceholders=[];$credits=0;$library=CatalogInventory::read_library();$allowed=[];
foreach($inventory['sources'] as $sourceId=>$source)foreach(($source['playlist']['ids']??[]) as $id)$allowed[$id][]=(int)$sourceId;
foreach(array_keys($allowed) as $video){
 if(!preg_match('/^[A-Za-z0-9_-]{11}$/D',$video))throw new \RuntimeException('Invalid source identifier.');
 $state=CatalogInventory::availability($inventory['details'][$video]??null);
 if($state==='unknown')continue;
 foreach($library[$video]??[] as $record){
  $id=$record['id'];$before=[];$values=[CatalogQuality::AVAILABILITY=>$state,'_oktv_source_checked_at'=>$inventory['checked_at']];
  $campaign=(int)get_post_meta($id,'wp_automatic_camp',true);
  $parent=(int)get_post_meta($campaign,'_oktv_backfill_source',true);
  $owned=in_array($campaign,$allowed[$video],true)||(get_post_type($campaign)==='wp_automatic'&&in_array($parent,$allowed[$video],true));
  if($verifyCredit&&$state==='available'&&$owned){
   $snippet=$inventory['details'][$video]['snippet'];$channel=(string)($snippet['channelId']??'');
   $title=sanitize_text_field(html_entity_decode((string)($snippet['channelTitle']??''),ENT_QUOTES|ENT_HTML5,'UTF-8'));
   if(!SourceAttribution::valid_channel($channel)||$title==='')throw new \RuntimeException('Verified source credit is incomplete.');
   $values+=['channel_id'=>$channel,'channel_title'=>$title,SourceAttribution::VERIFIED=>'1'];
   if(get_post_meta($id,SourceAttribution::VERIFIED,true)!=='1'||get_post_meta($id,'channel_id',true)!==$channel||get_post_meta($id,'channel_title',true)!==$title)$credits++;
  }
  foreach(array_keys($values) as $key){$before[$key]=get_post_meta($id,$key,false);if(count($before[$key])>1)throw new \RuntimeException('Ambiguous existing source metadata.');}
  $changes[$id]=['before'=>$before,'values'=>$values,'state'=>$state,'video_id'=>$video];
  $oldTitle=(string)get_post_field('post_title',$id);
  if($state==='available'&&CatalogQuality::placeholder($oldTitle)){
   $newTitle=wp_strip_all_tags((string)($inventory['details'][$video]['snippet']['title']??''),true);
   if($newTitle===''||CatalogQuality::placeholder($newTitle))throw new \RuntimeException('Restored source title is invalid.');
   $availablePlaceholders[]=$id;$changes[$id]['title_before']=$oldTitle;$changes[$id]['title_after']=$newTitle;
  }
 }
}
$cadence=null;
if(!$verifyCredit&&(isset($args[2])||isset($args[3]))){
 if(!ctype_digit((string)($args[2]??''))||!ctype_digit((string)($args[3]??'')))throw new \RuntimeException('Pass an approved campaign and interval in minutes.');
 $id=(int)$args[2];$minutes=(int)$args[3];
 if(!isset($inventory['sources'][$id])||$minutes<15||$minutes>1440)throw new \RuntimeException('Cadence outside this source inventory or bound.');
 $row=$wpdb->get_row($wpdb->prepare('SELECT camp_general FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$id),ARRAY_A);
 $before=$row['camp_general']??'';$config=CatalogInventory::decode_config($before);
 $config['cg_update_every']=(string)$minutes;$config['cg_update_unit']='1';
 $after=base64_encode(serialize($config));
 $cadence=['campaign_id'=>$id,'before'=>$before,'after'=>$after,'minutes'=>$minutes];
}
$result=['mode'=>$mode,'checked_at'=>$inventory['checked_at'],'records_checked'=>count($changes),'source_credits_to_update'=>$credits,'available_placeholder_records'=>count($availablePlaceholders),'cadence_campaign'=>$cadence['campaign_id']??null,'cadence_minutes'=>$cadence['minutes']??null];
if($mode==='plan'){echo json_encode($result,JSON_PRETTY_PRINT).PHP_EOL;return;}
if((int)$wpdb->get_var("SELECT GET_LOCK('oktv_catalog_reconcile_v1',0)")!==1)throw new \RuntimeException('Another catalog reconciliation is running.');
try{
 $backup=dirname($real).'/reconcile-before.'.bin2hex(random_bytes(6)).'.json';
 $old=umask(0077);$handle=fopen($backup,'x');
 if(!$handle)throw new \RuntimeException('Backup could not be created.');
 $before=json_encode(['receipt_sha256'=>hash_file('sha256',$real),'metadata'=>$changes,'snapshot_before'=>get_option(CatalogQuality::SNAPSHOT,null),'cadence'=>$cadence],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
 if(fwrite($handle,$before)!==strlen($before)){fclose($handle);throw new \RuntimeException('Backup write failed.');}
 fclose($handle);umask($old);
 foreach($changes as $id=>$change){
  $values=$change['values'];
  foreach($values as $key=>$value){
   if(get_post_meta($id,$key,false)!==$change['before'][$key])throw new \RuntimeException('Availability metadata changed during reconciliation.');
   update_post_meta($id,$key,$value);
   if(get_post_meta($id,$key,true)!==$value)throw new \RuntimeException('Availability metadata readback failed.');
  }
  if(isset($change['title_after'])){
   $changed=$wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_title=%s WHERE ID=%d AND post_type='vidmov_video' AND BINARY post_title=BINARY %s",$change['title_after'],$id,$change['title_before']));
   if($changed!==1)throw new \RuntimeException('Placeholder title changed during reconciliation.');
   clean_post_cache($id);
  }
 }
 if($cadence&&$cadence['before']!==$cadence['after']){
  $changed=$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->prefix.'automatic_camps SET camp_general=%s WHERE camp_id=%d AND BINARY camp_general=BINARY %s',$cadence['after'],$cadence['campaign_id'],$cadence['before']));
  if($changed!==1)throw new \RuntimeException('Campaign changed during reconciliation; cadence retained.');
 }
 $summary=['version'=>1,'checked_at'=>$inventory['checked_at'],'sources'=>[]];
 $current=CatalogInventory::read_library();
 foreach($inventory['sources'] as $id=>$source){
  if($cadence&&$cadence['campaign_id']===(int)$id)$source['interval_minutes']=$cadence['minutes'];
  $summary['sources'][]=CatalogInventory::summary((int)$id,$source,$inventory['details'],$current,CatalogInventory::latest_verified_import((int)$id));
 }
 update_option(CatalogQuality::SNAPSHOT,$summary,false);
 if(get_option(CatalogQuality::SNAPSHOT)!==$summary)throw new \RuntimeException('Aggregate snapshot readback failed.');
 $result['backup']=$backup;$result['titles_restored']=count($availablePlaceholders);$result['snapshot']=$summary;
 echo json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
}finally{$wpdb->get_var("SELECT RELEASE_LOCK('oktv_catalog_reconcile_v1')");}
