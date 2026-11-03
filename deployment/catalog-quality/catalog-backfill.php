<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

if(!defined('WP_CLI')||!WP_CLI)throw new \RuntimeException('WP-CLI only.');
define('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY',true);
require_once __DIR__.'/catalog-inventory.php';
if(!class_exists(SourceAttribution::class,false))require_once __DIR__.'/oktv-source-attribution.php';
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
$result=['version'=>1,'started_at'=>gmdate('c'),'source_id'=>$sourceId,'mode'=>$mode,'items'=>[],'users_created'=>0,'emails_blocked'=>0];
$save=static function()use(&$result,$receipt):void{$old=umask(0077);$tmp=$receipt.'.tmp';file_put_contents($tmp,json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));chmod($tmp,0600);rename($tmp,$receipt);umask($old);};
$blockUsers=static function($data,$update){if(!$update)throw new \RuntimeException('member_account_creation_blocked');return $data;};
$blockMail=static function()use(&$result){$result['emails_blocked']++;return false;};
add_filter('wp_pre_insert_user_data',$blockUsers,PHP_INT_MAX,2);
add_filter('pre_wp_mail',$blockMail,PHP_INT_MAX);
add_filter('okarcana_avatar_sync_enabled','__return_false',PHP_INT_MAX);
add_filter('publicize_should_publicize_published_post','__return_false',PHP_INT_MAX);
$created=[];$activeVideo='';$capture=static function($id,$post,$update)use(&$created,&$result,&$activeVideo,$save){if(!$update&&$post->post_type==='vidmov_video'){$created[]=(int)$id;$result['created_drafts'][]=['post_id'=>(int)$id,'requested_video_id'=>$activeVideo];$save();}};
$draft=static function($post)use(&$created){if(($post['post_type']??'')!=='vidmov_video')throw new \RuntimeException('unexpected_import_post_type');if($created)throw new \RuntimeException('one_draft_per_import_bound');$post['post_status']='draft';return $post;};
add_action('wp_insert_post',$capture,PHP_INT_MAX,3);add_filter('wp_automatic_before_insert',$draft,PHP_INT_MAX);
register_shutdown_function($save);
try{
 $original=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$sourceId));
 if(!$original||$original->camp_post_type!=='vidmov_video'||get_post_status($sourceId)!=='publish')throw new \RuntimeException('source_campaign_not_active');
 $general=CatalogInventory::decode_config($original->camp_general);
 $fingerprint=hash('sha256',CatalogInventory::playlist_id((string)($general['cg_yt_playlist']??'')));
 if(!is_string($source['source_fingerprint']??null)||!hash_equals($source['source_fingerprint'],$fingerprint))throw new \RuntimeException('source_configuration_changed_or_unbound_receipt');
 $options=CatalogInventory::decode_config($original->camp_options);
 if(in_array('OPT_YT_AUTHOR',$options,true)||in_array('OPT_YT_COMMENT',$options,true)||$original->camp_post_status!=='draft')throw new \RuntimeException('ingestion_policy_not_installed');
 foreach([$original->camp_post_title,$original->camp_post_content,$original->camp_post_custom_v] as $template)if(preg_match('/\[gpt|\[openai|\[claude|\[gemini/i',(string)$template))throw new \RuntimeException('paid_processing_template_not_allowed');
 $curator=(int)$original->camp_post_author;if(!$curator||!get_userdata($curator))throw new \RuntimeException('curator_missing');
 $shadowIds=get_posts(['post_type'=>'wp_automatic','post_status'=>'draft','posts_per_page'=>2,'fields'=>'ids','meta_key'=>'_oktv_backfill_source','meta_value'=>(string)$sourceId,'suppress_filters'=>true]);
 if(count($shadowIds)>1)throw new \RuntimeException('ambiguous_backfill_configuration');
 $shadowId=$shadowIds?(int)$shadowIds[0]:0;
 if(!$shadowId){
  $shadowId=wp_insert_post(['post_type'=>'wp_automatic','post_status'=>'draft','post_author'=>$curator,'post_title'=>'OKTV internal source backfill'],true);
  if(is_wp_error($shadowId))throw new \RuntimeException('backfill_configuration_create_failed');
  update_post_meta($shadowId,'_oktv_backfill_source',(string)$sourceId);
 }
 $config=(array)$original;$config['camp_id']=$shadowId;$config['camp_name']='OKTV internal source backfill';$config['camp_post_status']='draft';
 $options=array_values(array_diff($options,['OPT_YT_AUTHOR','OPT_YT_COMMENT','OPT_YT_PLAYLIST','OPT_YT_USER','OPT_YT_TRENDING']));$options[]='YT_ID';
 $config['camp_options']=serialize(array_values(array_unique($options)));
 $exists=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$shadowId));
 if($exists)$wpdb->update($wpdb->prefix.'automatic_camps',$config,['camp_id'=>$shadowId]);else $wpdb->insert($wpdb->prefix.'automatic_camps',$config);
 if($wpdb->last_error)throw new \RuntimeException('backfill_configuration_write_failed');
 $result['shadow_campaign']=$shadowId;$save();
 if(!class_exists('wp_automatic',false))require_once WP_PLUGIN_DIR.'/wp-automatic/core.php';
 $pool=(string)get_option('wp_automatic_yt_tocken','');preg_match_all('/AIza[A-Za-z0-9_-]{35}/',$pool,$keys);$key=$keys[0][0]??'';
 if(!$key)throw new \RuntimeException('provider_credential_missing');
 $done=0;
 foreach($targets as $video){
  if($done>=$limit)break;
  $current=CatalogInventory::read_library();$records=$current[$video]??[];
  if(in_array('publish',array_column($records,'status'),true))continue;
  $postId=0;
  foreach($records as $record)if($record['status']==='draft'&&get_post_meta($record['id'],'_oktv_backfill_source',true)===(string)$sourceId){$postId=$record['id'];break;}
  if(!$postId&&$records){$result['items'][]=['video_id'=>$video,'status'=>'existing_draft_preserved'];continue;}
  if(!$postId){
   $created=[];$activeVideo=$video;$config['camp_keywords']=$video;$wpdb->update($wpdb->prefix.'automatic_camps',['camp_keywords'=>$video],['camp_id'=>$shadowId]);
   $_SERVER['SERVER_NAME']='www.offkilter.tv';
   ob_start();try{$engine=new \wp_automatic();$engine->process_campaign((object)$config);}finally{ob_end_clean();}
   if(count($created)!==1)throw new \RuntimeException('bounded_import_did_not_create_one_draft');
   $postId=$created[0];
  }
  $done++;
  if(get_post_status($postId)!=='draft'||CatalogQuality::youtube_id((string)get_post_meta($postId,'beeteam368_video_url',true))!==$video)throw new \RuntimeException('imported_draft_source_mismatch');
  update_post_meta($postId,'_oktv_backfill_source',(string)$sourceId);
  $response=wp_remote_get(add_query_arg(['part'=>'snippet,status,contentDetails','id'=>$video,'key'=>$key],'https://www.googleapis.com/youtube/v3/videos'),['timeout'=>20,'redirection'=>0]);
  if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200)throw new \RuntimeException('provider_refresh_failed');
  $body=json_decode(wp_remote_retrieve_body($response),true);$detail=$body['items'][0]??null;
  if(!$detail||($detail['id']??'')!==$video||CatalogInventory::availability($detail)!=='available'){$result['items'][]=['post_id'=>$postId,'video_id'=>$video,'status'=>'source_changed_draft_retained'];$save();continue;}
  $channel=(string)($detail['snippet']['channelId']??'');$channelTitle=sanitize_text_field((string)($detail['snippet']['channelTitle']??''));
  if(!SourceAttribution::valid_channel($channel)||$channelTitle==='')throw new \RuntimeException('provider_channel_invalid');
  $description=(string)($detail['snippet']['description']??'');
  $content='<p><strong>Source:</strong> <a href="'.esc_url('https://www.youtube.com/channel/'.$channel).'">'.esc_html($channelTitle).' on YouTube</a>. Curated on OKTV.</p>'.wpautop(esc_html($description));
  $updated=wp_update_post(['ID'=>$postId,'post_status'=>'draft','post_author'=>$curator,'post_title'=>wp_strip_all_tags($detail['snippet']['title'],true),'post_content'=>$content,'post_excerpt'=>wp_trim_words(wp_strip_all_tags($description,true),28,'…')],true);
  if(is_wp_error($updated))throw new \RuntimeException('draft_normalization_failed');
  foreach(['channel_id'=>$channel,'channel_title'=>$channelTitle,'wp_automatic_camp'=>(string)$sourceId,'_oktv_source_availability'=>'available','_oktv_source_checked_at'=>gmdate('Y-m-d H:i:s'),'_oktv_imported_at'=>gmdate('Y-m-d H:i:s'),SourceAttribution::VERIFIED=>'1'] as $meta=>$value)update_post_meta($postId,$meta,$value);
  foreach(['beeteam368_views_counter_totals','beeteam368_reviews_data_count','beeteam368_reviews_data_percent','beeteam368_reactions_total','beeteam368_reactions_like','beeteam368_reactions_dislike'] as $meta)update_post_meta($postId,$meta,'0');
  if(!SourceAttribution::context($postId)||!get_post_thumbnail_id($postId))throw new \RuntimeException('draft_presentation_incomplete');
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
}catch(\Throwable $error){$message=$error->getMessage();$result['error']=preg_match('/^[a-z_]{1,100}$/D',$message)?$message:'vendor_processing_failed';$result['error_class']=get_class($error);$result['error_at']=basename($error->getFile()).':'.$error->getLine();$save();throw new \RuntimeException($result['error'].' ('.$result['error_class'].' at '.$result['error_at'].')');}
finally{
 remove_filter('wp_pre_insert_user_data',$blockUsers,PHP_INT_MAX);remove_filter('pre_wp_mail',$blockMail,PHP_INT_MAX);remove_filter('okarcana_avatar_sync_enabled','__return_false',PHP_INT_MAX);remove_filter('publicize_should_publicize_published_post','__return_false',PHP_INT_MAX);remove_action('wp_insert_post',$capture,PHP_INT_MAX);remove_filter('wp_automatic_before_insert',$draft,PHP_INT_MAX);$wpdb->get_var("SELECT RELEASE_LOCK('oktv_catalog_backfill_v1')");
}
