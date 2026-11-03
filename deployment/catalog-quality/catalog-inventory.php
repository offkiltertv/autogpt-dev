<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

if (!defined('ABSPATH')) { exit; }
if (!class_exists(CatalogQuality::class, false)) {
    require_once __DIR__ . '/oktv-catalog-quality.php';
}

/** Complete, bounded provider inventories; provider failures never become missing-video counts. */
final class CatalogInventory
{
    private $request;
    public function __construct(callable $request) { $this->request = $request; }

    public static function decode_config(string $raw): array
    {
        $value = @unserialize($raw, ['allowed_classes' => false]);
        if (!is_array($value)) {
            $bytes = base64_decode($raw, true);
            $value = $bytes === false ? false : @unserialize($bytes, ['allowed_classes' => false]);
        }
        if (!is_array($value)) { throw new \RuntimeException('campaign_config_invalid'); }
        return $value;
    }

    public static function playlist_id(string $value): string
    {
        if (str_starts_with($value, 'https://')) {
            $parts = wp_parse_url($value);
            if (!is_array($parts) || !in_array($parts['host'] ?? '', ['youtube.com','www.youtube.com'], true)
                || ($parts['path'] ?? '') !== '/playlist' || isset($parts['user']) || isset($parts['port'])) {
                throw new \RuntimeException('playlist_config_invalid');
            }
            parse_str($parts['query'] ?? '', $query);
            $value = is_string($query['list'] ?? null) ? $query['list'] : '';
        }
        if (!preg_match('/^PL[A-Za-z0-9_-]{20,}$/D', $value)) { throw new \RuntimeException('playlist_config_invalid'); }
        return $value;
    }

    public function playlist(string $id): array
    {
        $id = self::playlist_id($id);
        $items = []; $tokens = []; $token = ''; $total = null; $rows = 0;
        for ($page = 0; $page < 100; $page++) {
            $params = ['part'=>'snippet,contentDetails','playlistId'=>$id,'maxResults'=>50];
            if ($token !== '') { $params['pageToken'] = $token; }
            $body = ($this->request)('playlistItems', $params);
            if (!is_array($body['items'] ?? null) || !is_int($body['pageInfo']['totalResults'] ?? null)) {
                throw new \RuntimeException('playlist_response_incomplete');
            }
            $reported = $body['pageInfo']['totalResults'];
            if ($reported > 5000 || $reported < 0 || ($total !== null && $total !== $reported)) {
                throw new \RuntimeException('playlist_changed_or_exceeds_bound');
            }
            $total = $reported;
            foreach ($body['items'] as $item) {
                $video = $item['contentDetails']['videoId'] ?? $item['snippet']['resourceId']['videoId'] ?? '';
                if (!is_string($video) || !preg_match('/^[A-Za-z0-9_-]{11}$/D', $video)) {
                    throw new \RuntimeException('playlist_item_identifier_missing');
                }
                $items[$video] = true; $rows++;
            }
            $token = $body['nextPageToken'] ?? '';
            if (!is_string($token) || strlen($token) > 2048) { throw new \RuntimeException('playlist_page_token_invalid'); }
            if ($token === '') {
                if ($rows !== $total) { throw new \RuntimeException('playlist_response_incomplete'); }
                return ['reported_total'=>$total,'item_count'=>$rows,'ids'=>array_keys($items),'pages'=>$page+1];
            }
            if (isset($tokens[$token])) { throw new \RuntimeException('playlist_pagination_loop'); }
            $tokens[$token] = true;
        }
        throw new \RuntimeException('playlist_page_bound_exceeded');
    }

    public function videos(array $ids): array
    {
        $details = [];
        foreach (array_chunk(array_values(array_unique($ids)), 50) as $chunk) {
            $body = ($this->request)('videos', ['part'=>'snippet,status,contentDetails','id'=>implode(',', $chunk)]);
            if (!is_array($body['items'] ?? null)) { throw new \RuntimeException('video_response_incomplete'); }
            foreach ($body['items'] as $video) {
                $id = $video['id'] ?? '';
                if (!in_array($id, $chunk, true) || isset($details[$id])) { throw new \RuntimeException('video_response_identifier_mismatch'); }
                $details[$id] = $video;
            }
        }
        return $details;
    }

    public static function availability(?array $video): string
    {
        if ($video === null) { return 'unavailable'; }
        $status = $video['status'] ?? [];
        if (!in_array($status['privacyStatus'] ?? '', ['public','unlisted'], true)) { return 'unavailable'; }
        if (($status['embeddable'] ?? null) === false) { return 'not_embeddable'; }
        if (($status['embeddable'] ?? null) !== true || empty($video['snippet']['title'])) { return 'unknown'; }
        if (isset($status['uploadStatus']) && $status['uploadStatus'] !== 'processed') { return 'unknown'; }
        return 'available';
    }

    public static function reconcile(array $ids, array $details, array $library): array
    {
        $result = ['eligible'=>0,'published'=>0,'draft'=>0,'other'=>0,'missing'=>0,'unavailable'=>0,'not_embeddable'=>0,'unknown'=>0,'missing_ids'=>[]];
        foreach (array_unique($ids) as $id) {
            $availability = self::availability($details[$id] ?? null);
            if ($availability !== 'available') { $result[$availability]++; continue; }
            $result['eligible']++;
            $records = $library[$id] ?? [];
            $statuses = array_column($records, 'status');
            if (in_array('publish', $statuses, true)) { $result['published']++; }
            elseif (in_array('draft', $statuses, true) || in_array('pending', $statuses, true)) { $result['draft']++; }
            elseif ($records) { $result['other']++; }
            else { $result['missing']++; $result['missing_ids'][]=$id; }
        }
        return $result;
    }

    public static function read_library(): array
    {
        global $wpdb;
        $count=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='vidmov_video'");
        if ($count > 10000 || $wpdb->last_error) { throw new \RuntimeException('catalog_read_bound_or_error'); }
        $rows=$wpdb->get_results("SELECT p.ID,p.post_status,pm.meta_value FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='beeteam368_video_url' WHERE p.post_type='vidmov_video'", ARRAY_A);
        if ($wpdb->last_error) { throw new \RuntimeException('catalog_read_failed'); }
        $library=[];
        foreach($rows as $row){$id=CatalogQuality::youtube_id((string)$row['meta_value']);if($id)$library[$id][]=['id'=>(int)$row['ID'],'status'=>$row['post_status']];}
        return $library;
    }
}

if (defined('OKTV_CATALOG_INVENTORY_LIBRARY_ONLY') && OKTV_CATALOG_INVENTORY_LIBRARY_ONLY) { return; }
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_parse_url(home_url('/'),PHP_URL_HOST), ['www.offkilter.tv','www.offkilter.test'],true)) {
    throw new \RuntimeException('Use WP-CLI on the reviewed site.');
}

$mode=$args[0]??'audit';
$campaignIds=array_slice($args??[],1);
if(!in_array($mode,['audit','snapshot'],true)||!$campaignIds||count($campaignIds)>10){throw new \RuntimeException('Pass audit or snapshot and one to ten campaign IDs.');}
foreach($campaignIds as $id)if(!ctype_digit((string)$id)||(int)$id<1)throw new \RuntimeException('Invalid campaign identifier.');
$pool=(string)get_option('wp_automatic_yt_tocken','');
preg_match_all('/AIza[A-Za-z0-9_-]{35}/',$pool,$keys);
$apiKey=$keys[0][0]??'';
if($apiKey==='')throw new \RuntimeException('youtube_provider_credential_not_configured');
$requests=0;
$client=new CatalogInventory(static function(string $resource,array $params) use($apiKey,&$requests):array{
    if(!in_array($resource,['playlistItems','videos'],true))throw new \RuntimeException('provider_resource_not_allowed');
    $params['key']=$apiKey;
    $response=wp_remote_get(add_query_arg($params,'https://www.googleapis.com/youtube/v3/'.$resource),['timeout'=>25,'redirection'=>0]);
    $requests++;
    if(is_wp_error($response))throw new \RuntimeException('youtube_transport_error');
    $body=json_decode(wp_remote_retrieve_body($response),true);
    $http=wp_remote_retrieve_response_code($response);
    if($http!==200||!is_array($body)||isset($body['error'])){
        $reason=$body['error']['errors'][0]['reason']??'invalid_response';
        if(!is_string($reason)||!preg_match('/^[A-Za-z0-9_-]{1,80}$/D',$reason))$reason='provider_error';
        throw new \RuntimeException('youtube_http_'.$http.'_'.$reason);
    }
    return $body;
});
$sources=[];$all=[];
foreach($campaignIds as $rawId){
    $id=(int)$rawId;
    if(get_post_type($id)!=='wp_automatic'||get_post_status($id)!=='publish')throw new \RuntimeException('source_campaign_not_active');
    $row=$wpdb->get_row($wpdb->prepare('SELECT camp_general,camp_options FROM '.$wpdb->prefix.'automatic_camps WHERE camp_id=%d',$id),ARRAY_A);
    if(!$row||$wpdb->last_error)throw new \RuntimeException('source_campaign_missing');
    $config=CatalogInventory::decode_config($row['camp_general']);
    $options=CatalogInventory::decode_config($row['camp_options']);
    if(!in_array('OPT_YT_PLAYLIST',$options,true))throw new \RuntimeException('source_is_not_playlist');
    $playlist=$client->playlist(CatalogInventory::playlist_id((string)($config['cg_yt_playlist']??'')));
    $sources[$id]=['label'=>get_the_title($id),'playlist'=>$playlist,'interval_minutes'=>(int)($config['cg_update_every']??0)*(int)($config['cg_update_unit']??0)];
    foreach($playlist['ids'] as $video)$all[$video]=true;
}
$details=$client->videos(array_keys($all));
$library=CatalogInventory::read_library();
$checked=gmdate('Y-m-d H:i:s');$summary=['version'=>1,'checked_at'=>$checked,'sources'=>[],'provider_requests'=>$requests];
foreach($sources as $id=>$source){
    $coverage=CatalogInventory::reconcile($source['playlist']['ids'],$details,$library);
    $sources[$id]['coverage']=$coverage;
    unset($coverage['missing_ids']);
    $summary['sources'][]=['campaign_id'=>$id,'label'=>$source['label'],'reported_total'=>$source['playlist']['reported_total'],'unique_videos'=>count($source['playlist']['ids']),'interval_minutes'=>$source['interval_minutes']]+$coverage;
}
$receipt=getenv('OKTV_CATALOG_RECEIPT');
if($receipt!==false&&$receipt!==''){
    $parent=realpath(dirname($receipt));
    if(!$parent||!str_starts_with($parent,'/var/backups/oktv-recovery/')||is_link($parent)||file_exists($receipt))throw new \RuntimeException('private_receipt_location_invalid');
    $old=umask(0077);$handle=fopen($receipt,'x');
    if(!$handle)throw new \RuntimeException('private_receipt_create_failed');
    $payload=json_encode(['version'=>1,'checked_at'=>$checked,'sources'=>$sources,'details'=>$details,'library'=>$library],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
    if(fwrite($handle,$payload)!==strlen($payload)){fclose($handle);throw new \RuntimeException('private_receipt_write_failed');}
    fclose($handle);umask($old);
}
if($mode==='snapshot'){
    $old=get_option(CatalogQuality::SNAPSHOT,null);
    if(!update_option(CatalogQuality::SNAPSHOT,$summary,false)&&get_option(CatalogQuality::SNAPSHOT)!==$summary)throw new \RuntimeException('snapshot_write_failed');
}
echo json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL;
