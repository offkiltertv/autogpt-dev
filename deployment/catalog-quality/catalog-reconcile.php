<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

if (!defined('WP_CLI') || !WP_CLI) { throw new \RuntimeException('WP-CLI only.'); }
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY', true);
require_once __DIR__.'/catalog-inventory.php';
global $wpdb;
if (wp_parse_url(home_url('/'),PHP_URL_HOST)!=='www.offkilter.tv') { throw new \RuntimeException('Unexpected site.'); }
$mode=$args[0]??'';$path=$args[1]??'';
if(!in_array($mode,['plan','apply'],true)||!is_string($path)||!is_file($path)||is_link($path))throw new \RuntimeException('Pass plan or apply and a private inventory receipt.');
$real=realpath($path);$stat=stat($path);
if(!$real||!str_starts_with($real,'/var/backups/oktv-recovery/')||$stat['uid']!==0||($stat['mode']&0077)!==0||$stat['size']>32*1024*1024)throw new \RuntimeException('Inventory receipt must remain root-only and outside the webroot.');
$inventory=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
$checked=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',(string)($inventory['checked_at']??''),new \DateTimeZone('UTC'));
if(($inventory['version']??null)!==1||!$checked||time()-$checked->getTimestamp()>14400||$checked->getTimestamp()>time()+30||!is_array($inventory['sources']??null)||!is_array($inventory['details']??null))throw new \RuntimeException('Inventory receipt is incomplete or stale.');
$changes=[];$availablePlaceholders=[];$library=CatalogInventory::read_library();$allowed=[];
foreach($inventory['sources'] as $source)foreach(($source['playlist']['ids']??[]) as $id)$allowed[$id]=true;
foreach(array_keys($allowed) as $video){
 if(!preg_match('/^[A-Za-z0-9_-]{11}$/D',$video))throw new \RuntimeException('Invalid source identifier.');
 $state=CatalogInventory::availability($inventory['details'][$video]??null);
 if($state==='unknown')continue;
 foreach($library[$video]??[] as $record){
  $id=$record['id'];$before=[];
  foreach([CatalogQuality::AVAILABILITY,'_oktv_source_checked_at'] as $key){$before[$key]=get_post_meta($id,$key,false);if(count($before[$key])>1)throw new \RuntimeException('Ambiguous existing availability metadata.');}
  $changes[$id]=['before'=>$before,'state'=>$state,'video_id'=>$video];
  if($state==='available'&&CatalogQuality::placeholder((string)get_post_field('post_title',$id)))$availablePlaceholders[]=$id;
 }
}
$cadence=null;
if(isset($args[2])||isset($args[3])){
 if(!ctype_digit((string)($args[2]??''))||!ctype_digit((string)($args[3]??'')))throw new \RuntimeException('Pass an approved campaign and interval in minutes.');
 $id=(int)$args[2];$minutes=(int)$args[3];
 if(!isset($inventory['sources'][$id])||$minutes<15||$minutes>1440)throw new \RuntimeException('Cadence outside this source inventory or bound.');
 $row=$wpdb->get_row($wpdb->prepare('SELECT camp_general FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$id),ARRAY_A);
 $before=$row['camp_general']??'';$config=CatalogInventory::decode_config($before);
 $config['cg_update_every']=(string)$minutes;$config['cg_update_unit']='1';
 $after=base64_encode(serialize($config));
 $cadence=['campaign_id'=>$id,'before'=>$before,'after'=>$after,'minutes'=>$minutes];
}
$result=['mode'=>$mode,'checked_at'=>$inventory['checked_at'],'records_checked'=>count($changes),'available_placeholder_records'=>count($availablePlaceholders),'cadence_campaign'=>$cadence['campaign_id']??null,'cadence_minutes'=>$cadence['minutes']??null];
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
  $values=[CatalogQuality::AVAILABILITY=>$change['state'],'_oktv_source_checked_at'=>$inventory['checked_at']];
  foreach($values as $key=>$value){
   if(get_post_meta($id,$key,false)!==$change['before'][$key])throw new \RuntimeException('Availability metadata changed during reconciliation.');
   update_post_meta($id,$key,$value);
   if(get_post_meta($id,$key,true)!==$value)throw new \RuntimeException('Availability metadata readback failed.');
  }
 }
 if($cadence&&$cadence['before']!==$cadence['after']){
  $changed=$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->prefix.'automatic_camps SET camp_general=%s WHERE camp_id=%d AND BINARY camp_general=BINARY %s',$cadence['after'],$cadence['campaign_id'],$cadence['before']));
  if($changed!==1)throw new \RuntimeException('Campaign changed during reconciliation; cadence retained.');
 }
 $summary=['version'=>1,'checked_at'=>$inventory['checked_at'],'sources'=>[]];
 $current=CatalogInventory::read_library();
 foreach($inventory['sources'] as $id=>$source){
  $coverage=CatalogInventory::reconcile($source['playlist']['ids'],$inventory['details'],$current);unset($coverage['missing_ids']);
  $summary['sources'][]=['campaign_id'=>(int)$id,'label'=>$source['label'],'reported_total'=>$source['playlist']['reported_total'],'unique_videos'=>count($source['playlist']['ids']),'interval_minutes'=>($cadence&&$cadence['campaign_id']===(int)$id)?$cadence['minutes']:$source['interval_minutes']]+$coverage;
 }
 update_option(CatalogQuality::SNAPSHOT,$summary,false);
 if(get_option(CatalogQuality::SNAPSHOT)!==$summary)throw new \RuntimeException('Aggregate snapshot readback failed.');
 $result['backup']=$backup;$result['snapshot']=$summary;
 echo json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
}finally{$wpdb->get_var("SELECT RELEASE_LOCK('oktv_catalog_reconcile_v1')");}
