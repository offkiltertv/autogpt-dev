<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

if(!defined('WP_CLI')||!WP_CLI)throw new \RuntimeException('WP-CLI only.');
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY',true);
require_once __DIR__.'/catalog-inventory.php';
if(!class_exists(SourceAttribution::class,false))require_once __DIR__.'/oktv-source-attribution.php';
require_once __DIR__.'/catalog-verified-import.php';
global $wpdb;
if(wp_parse_url(home_url('/'),PHP_URL_HOST)!=='www.offkilter.tv')throw new \RuntimeException('Unexpected site.');
$mode=$args[0]??'';$path=$args[1]??'';$sourceId=(int)($args[2]??0);$limit=(int)($args[3]??1);
if(!in_array($mode,['plan','prepare','publish'],true)||$limit<1||$limit>20||!is_file($path)||is_link($path))throw new \RuntimeException('Pass plan/prepare/publish, a private receipt, approved source and batch size 1–20.');
$stat=stat($path);$real=realpath($path);
if(!$real||!str_starts_with($real,'/var/backups/oktv-recovery/')||$stat['uid']!==0||($stat['mode']&0077)!==0||$stat['size']>32*1024*1024)throw new \RuntimeException('Receipt must remain root-only.');
$inventory=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
$time=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',(string)($inventory['checked_at']??''),new \DateTimeZone('UTC'));
if(($inventory['version']??null)!==1||!$time||time()-$time->getTimestamp()>14400||$time->getTimestamp()>time()+30||!isset($inventory['sources'][$sourceId]))throw new \RuntimeException('Receipt is stale or source is outside the reviewed inventory.');
$source=$inventory['sources'][$sourceId];$library=CatalogInventory::read_library();
$eligible=[];foreach($source['playlist']['ids'] as $id)if(CatalogInventory::availability($inventory['details'][$id]??null)==='available')$eligible[$id]=true;
$targets=[];foreach(array_keys($eligible) as $id){
 $records=$library[$id]??[];
 if(in_array('publish',array_column($records,'status'),true))continue;
 if($mode==='prepare'&&array_filter($records,static fn($record)=>$record['status']==='draft'&&get_post_meta($record['id'],SourceAttribution::VERIFIED,true)==='1'&&get_post_meta($record['id'],'_oktv_backfill_source',true)===(string)$sourceId))continue;
 $targets[]=$id;
}
if($mode==='plan'){echo json_encode(['eligible'=>count($eligible),'not_yet_published'=>count($targets),'batch_limit'=>$limit,'creates_member_accounts'=>false],JSON_PRETTY_PRINT).PHP_EOL;return;}
if($mode==='publish'&&!SourceAttribution::ready())throw new \RuntimeException('source_presentation_not_ready');
if((int)$wpdb->get_var("SELECT GET_LOCK('oktv_catalog_backfill_v1',0)")!==1)throw new \RuntimeException('Another backfill is running.');
$receipt=dirname($real).'/backfill.'.gmdate('Ymd-His').'.'.bin2hex(random_bytes(4)).'.json';
$result=['version'=>1,'started_at'=>gmdate('c'),'source_id'=>$sourceId,'mode'=>$mode,'items'=>[],'users_created'=>0,'emails_blocked'=>0,'created_drafts'=>[]];
$save=static function()use(&$result,$receipt):void{$old=umask(0077);$tmp=$receipt.'.tmp';$json=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);if(file_put_contents($tmp,$json)!==strlen($json))throw new \RuntimeException('journal_write_failed');chmod($tmp,0600);if(!rename($tmp,$receipt))throw new \RuntimeException('journal_commit_failed');umask($old);};
$blockUsers=static function($data,$update){if(!$update)throw new \RuntimeException('member_account_creation_blocked');return $data;};
$blockMail=static function()use(&$result){$result['emails_blocked']++;return false;};
add_filter('wp_pre_insert_user_data',$blockUsers,PHP_INT_MAX,2);add_filter('pre_wp_mail',$blockMail,PHP_INT_MAX);
add_filter('okarcana_avatar_sync_enabled','__return_false',PHP_INT_MAX);
register_shutdown_function($save);
try{
 $original=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$sourceId));
 if(!$original||$original->camp_post_type!=='vidmov_video'||get_post_status($sourceId)!=='publish')throw new \RuntimeException('source_campaign_not_active');
 $general=CatalogInventory::decode_config($original->camp_general);$options=CatalogInventory::decode_config($original->camp_options);
 $fingerprint=hash('sha256',CatalogInventory::playlist_id((string)($general['cg_yt_playlist']??'')));
 if(!is_string($source['source_fingerprint']??null)||!hash_equals($source['source_fingerprint'],$fingerprint))throw new \RuntimeException('source_configuration_changed_or_unbound_receipt');
 if(in_array('OPT_YT_AUTHOR',$options,true)||in_array('OPT_YT_COMMENT',$options,true)||$original->camp_post_status!=='draft')throw new \RuntimeException('ingestion_policy_not_installed');
 $curator=(int)$original->camp_post_author;if(!$curator||!get_userdata($curator))throw new \RuntimeException('curator_missing');
 $taxonomy=(string)($general['cg_camp_tax']??'');$tax=get_taxonomy($taxonomy);
 if(!$tax||!in_array('vidmov_video',$tax->object_type,true))throw new \RuntimeException('source_taxonomy_invalid');
 $categories=@unserialize((string)$original->camp_post_category,['allowed_classes'=>false]);
 if(!is_array($categories))$categories=preg_split('/[,\s]+/',(string)$original->camp_post_category,-1,PREG_SPLIT_NO_EMPTY);
 $categories=array_values(array_unique(array_map('intval',$categories)));
 if(!$categories||count($categories)>20)throw new \RuntimeException('source_categories_invalid');
 foreach($categories as $term)if($term<1||!term_exists($term,$taxonomy))throw new \RuntimeException('source_category_missing');
 // Recover only drafts named by this operator's private creation journals.
 $recoverable=[];
 foreach(glob(dirname($real).'/backfill.*.json') as $journal){
  if(is_link($journal))continue;$journalStat=stat($journal);
  if($journalStat['uid']!==0||($journalStat['mode']&0077)!==0||$journalStat['size']>1024*1024)continue;
  $old=json_decode(file_get_contents($journal),true);
  if(($old['source_id']??0)!==$sourceId)continue;
  foreach($old['created_drafts']??[] as $draft)$recoverable[(int)$draft['post_id']]=(string)$draft['requested_video_id'];
 }
 $pool=(string)get_option('wp_automatic_yt_tocken','');preg_match_all('/AIza[A-Za-z0-9_-]{35}/',$pool,$keys);$key=$keys[0][0]??'';
 if(!$key)throw new \RuntimeException('provider_credential_missing');
 require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
 $done=0;$save();
 foreach($targets as $video){
  if($done>=$limit)break;
  $current=CatalogInventory::read_library();$records=$current[$video]??[];
  if(in_array('publish',array_column($records,'status'),true))continue;
  $postId=0;
  foreach($records as $record)if($record['status']==='draft'&&(get_post_meta($record['id'],'_oktv_backfill_source',true)===(string)$sourceId||($recoverable[$record['id']]??'')===$video)){
   if((int)get_post_field('post_author',$record['id'])!==$curator)throw new \RuntimeException('recoverable_draft_author_changed');
   $postId=$record['id'];break;
  }
  if(!$postId&&$records){$result['items'][]=['video_id'=>$video,'status'=>'existing_draft_preserved'];continue;}
  $response=wp_remote_get(add_query_arg(['part'=>'snippet,status,contentDetails','id'=>$video,'key'=>$key],'https://www.googleapis.com/youtube/v3/videos'),['timeout'=>20,'redirection'=>0]);
  if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200)throw new \RuntimeException('provider_refresh_failed');
  $body=json_decode(wp_remote_retrieve_body($response),true);$detail=$body['items'][0]??null;
  if(!$detail||($detail['id']??'')!==$video||CatalogInventory::availability($detail)!=='available'){$result['items'][]=['post_id'=>$postId,'video_id'=>$video,'status'=>'source_changed_not_published'];$save();continue;}
  $checked=VerifiedImport::source($detail,$video);$content=VerifiedImport::content($checked);
  $values=['post_type'=>'vidmov_video','post_status'=>'draft','post_author'=>$curator,'post_title'=>$checked['title'],'post_content'=>$content,'post_excerpt'=>wp_trim_words(wp_strip_all_tags($checked['description'],true),28,'…'),'post_date_gmt'=>$checked['published_gmt'],'post_date'=>get_date_from_gmt($checked['published_gmt'])];
  if($postId){
   $result['drafts_before'][$postId]=['post'=>(array)get_post($postId),'meta'=>get_post_meta($postId)];$save();
   $values['ID']=$postId;$updated=wp_update_post(wp_slash($values),true);
  }else{
   $values['meta_input']=['_oktv_backfill_source'=>(string)$sourceId,'beeteam368_video_url'=>'https://www.youtube.com/watch?v='.$video];
   $updated=wp_insert_post(wp_slash($values),true);
   if(!is_wp_error($updated)&&$updated){$postId=(int)$updated;$result['created_drafts'][]=['post_id'=>$postId,'requested_video_id'=>$video];$save();}
  }
  if(is_wp_error($updated)||!$postId||get_post_status($postId)!=='draft')throw new \RuntimeException('draft_write_failed');
  $done++;
  $terms=wp_set_object_terms($postId,$categories,$taxonomy,false);if(is_wp_error($terms))throw new \RuntimeException('draft_category_write_failed');
  foreach(['_oktv_backfill_source'=>(string)$sourceId,'beeteam368_video_url'=>'https://www.youtube.com/watch?v='.$video,'beeteam368_video_mode'=>'pro','beeteam368_video_formats'=>'auto','beeteam368_video_formats_preview'=>'auto','beeteam368_video_ratio'=>'16:9','channel_id'=>$checked['channel'],'channel_title'=>$checked['channel_title'],'wp_automatic_camp'=>(string)$sourceId,'youtube_publish_date'=>$checked['published_gmt'],CatalogQuality::AVAILABILITY=>'available','_oktv_source_checked_at'=>gmdate('Y-m-d H:i:s')] as $meta=>$value)update_post_meta($postId,$meta,$value);
  if(!get_post_meta($postId,'_oktv_imported_at',true))update_post_meta($postId,'_oktv_imported_at',gmdate('Y-m-d H:i:s'));
  foreach(['beeteam368_views_counter_totals','beeteam368_reviews_data_count','beeteam368_reviews_data_percent','beeteam368_reactions_total','beeteam368_reactions_like','beeteam368_reactions_dislike'] as $meta)update_post_meta($postId,$meta,'0');
  if(!get_post_thumbnail_id($postId)){$attachment=media_sideload_image($checked['thumbnail'],$postId,$checked['title'],'id');if(is_wp_error($attachment)||!set_post_thumbnail($postId,(int)$attachment))throw new \RuntimeException('draft_thumbnail_failed');update_post_meta((int)$attachment,'_wp_attachment_image_alt',$checked['title']);}
  update_post_meta($postId,SourceAttribution::VERIFIED,'1');
  if(!SourceAttribution::context($postId)||!get_post_thumbnail_id($postId)||CatalogQuality::youtube_id((string)get_post_meta($postId,'beeteam368_video_url',true))!==$video)throw new \RuntimeException('draft_presentation_incomplete');
  $current=CatalogInventory::read_library();$duplicates=array_filter($current[$video]??[],static fn($record)=>$record['id']!==$postId&&$record['status']==='publish');
  if($duplicates){$result['items'][]=['post_id'=>$postId,'video_id'=>$video,'status'=>'duplicate_draft_retained'];$save();continue;}
  if($mode==='publish'){
   if(!SourceAttribution::ready())throw new \RuntimeException('source_presentation_not_ready');
   $published=wp_update_post(['ID'=>$postId,'post_status'=>'publish'],true);
   if(is_wp_error($published)||get_post_status($postId)!=='publish')throw new \RuntimeException('publication_readback_failed');
  }
  $result['items'][]=['post_id'=>$postId,'video_id'=>$video,'status'=>$mode==='publish'?'published':'verified_draft'];$save();
 }
 $result['finished_at']=gmdate('c');$result['processed']=$done;$save();
 echo json_encode(['mode'=>$mode,'processed'=>$done,'published'=>count(array_filter($result['items'],static fn($item)=>$item['status']==='published')),'verified_drafts'=>count(array_filter($result['items'],static fn($item)=>$item['status']==='verified_draft')),'users_created'=>0,'receipt'=>$receipt,'post_ids'=>array_column($result['items'],'post_id')],JSON_PRETTY_PRINT).PHP_EOL;
}catch(\Throwable $error){$message=$error->getMessage();$result['error']=preg_match('/^[a-z_]{1,100}$/D',$message)?$message:'import_failed';$result['error_class']=get_class($error);$result['error_at']=basename($error->getFile()).':'.$error->getLine();$save();throw new \RuntimeException($result['error'].' ('.$result['error_class'].' at '.$result['error_at'].')');}
finally{
 remove_filter('wp_pre_insert_user_data',$blockUsers,PHP_INT_MAX);remove_filter('pre_wp_mail',$blockMail,PHP_INT_MAX);remove_filter('okarcana_avatar_sync_enabled','__return_false',PHP_INT_MAX);$wpdb->get_var("SELECT RELEASE_LOCK('oktv_catalog_backfill_v1')");
}
