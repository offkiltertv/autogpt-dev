<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;
if(!defined('WP_CLI')||!WP_CLI)throw new \RuntimeException('WP-CLI only.');
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY',true);
require_once __DIR__.'/catalog-inventory.php';
global $wpdb;
if(wp_parse_url(home_url('/'),PHP_URL_HOST)!=='www.offkilter.tv')throw new \RuntimeException('Unexpected site.');
$mode=$args[0]??'';$directory=realpath($args[1]??'');$ids=array_slice($args??[],2);
if(!in_array($mode,['plan','apply'],true)||!$directory||!str_starts_with($directory,'/var/backups/oktv-recovery/')||!$ids||count($ids)>10)throw new \RuntimeException('Pass plan/apply, a private receipt directory and approved campaign IDs.');
$stat=stat($directory);if($stat['uid']!==0||($stat['mode']&0077)!==0)throw new \RuntimeException('Receipt directory must be root-only.');
$changes=[];
foreach($ids as $id){
 if(!ctype_digit((string)$id)||(int)$id<1||get_post_type((int)$id)!=='wp_automatic')throw new \RuntimeException('Invalid campaign.');
 $row=$wpdb->get_row($wpdb->prepare('SELECT camp_options,camp_post_status,camp_post_type FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',(int)$id),ARRAY_A);
 if(!$row||$row['camp_post_type']!=='vidmov_video')throw new \RuntimeException('Unexpected import target.');
 $options=CatalogInventory::decode_config($row['camp_options']);
 if(!in_array('OPT_YT_PLAYLIST',$options,true))throw new \RuntimeException('Only approved playlist campaigns are supported.');
 $after=array_values(array_filter($options,static fn($option)=>!in_array($option,['OPT_YT_AUTHOR','OPT_YT_COMMENT'],true)));
 $changes[(int)$id]=['before'=>$row,'options_after'=>serialize($after),'was_creating_authors'=>in_array('OPT_YT_AUTHOR',$options,true)];
}
$summary=['mode'=>$mode,'campaigns'=>array_map(static fn($id,$change)=>['id'=>$id,'new_video_status'=>'draft','display_name_account_creation'=>false,'copy_provider_comments'=>false,'previously_created_authors'=>$change['was_creating_authors']],array_keys($changes),$changes),'existing_accounts_changed'=>0];
if($mode==='plan'){echo json_encode($summary,JSON_PRETTY_PRINT).PHP_EOL;return;}
$backup=$directory.'/ingestion-policy-before.'.bin2hex(random_bytes(6)).'.json';$old=umask(0077);
$handle=fopen($backup,'x');if(!$handle)throw new \RuntimeException('Could not create policy backup.');
$bytes=json_encode($changes,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
if(fwrite($handle,$bytes)!==strlen($bytes)){fclose($handle);throw new \RuntimeException('Policy backup failed.');}fclose($handle);umask($old);
foreach($changes as $id=>$change){
 $before=$change['before'];
 if($before['camp_options']===$change['options_after']&&$before['camp_post_status']==='draft')continue;
 $changed=$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->prefix.'automatic_camps SET camp_options=%s,camp_post_status=%s WHERE camp_id=%d AND BINARY camp_options=BINARY %s AND camp_post_status=%s',$change['options_after'],'draft',$id,$before['camp_options'],$before['camp_post_status']));
 if($changed!==1)throw new \RuntimeException('Campaign changed; policy update stopped.');
 $check=$wpdb->get_row($wpdb->prepare('SELECT camp_options,camp_post_status FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$id),ARRAY_A);
 if($check['camp_options']!==$change['options_after']||$check['camp_post_status']!=='draft')throw new \RuntimeException('Policy readback failed.');
}
$summary['backup']=$backup;echo json_encode($summary,JSON_PRETTY_PRINT).PHP_EOL;
