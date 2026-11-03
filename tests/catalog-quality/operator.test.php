<?php
declare(strict_types=1);
define('ABSPATH','/synthetic/');define('WP_CLI',true);define('ARRAY_A','ARRAY_A');
$writes=0;
function add_filter(...$args){}function add_action(...$args){}
function wp_parse_url($url,$component=-1){return parse_url($url,$component);}
function home_url($path){return 'https://www.offkilter.test/';}
function get_post_type($id){return 'wp_automatic';}function get_post_status($id){return 'publish';}function get_the_title($id){return 'Synthetic approved source';}
function get_option($key,$default=''){return $key==='wp_automatic_yt_tocken'?'AIza'.str_repeat('A',35):$default;}
function update_option(...$args){$GLOBALS['writes']++;return true;}
function add_query_arg($params,$url){return $url.'?'.http_build_query($params);}
function is_wp_error($value){return false;}
function wp_remote_get($url,$options){
 if(parse_url($url,PHP_URL_HOST)!=='www.googleapis.com')throw new RuntimeException('Unexpected provider host.');
 $video=fn($id)=>['id'=>$id,'snippet'=>['title'=>'Synthetic video'],'status'=>['privacyStatus'=>'public','embeddable'=>true,'uploadStatus'=>'processed']];
 $body=str_contains($url,'/playlistItems?')?['pageInfo'=>['totalResults'=>2],'items'=>[['contentDetails'=>['videoId'=>'aaaaaaaaaaa']],['contentDetails'=>['videoId'=>'bbbbbbbbbbb']]]]:['items'=>[$video('aaaaaaaaaaa'),$video('bbbbbbbbbbb')]];
 return ['body'=>json_encode($body),'code'=>200];
}
function wp_remote_retrieve_body($response){return $response['body'];}function wp_remote_retrieve_response_code($response){return $response['code'];}
$wpdb=new class {
 public string $prefix='wp_';public string $posts='wp_posts';public string $postmeta='wp_postmeta';public string $last_error='';
 public function prepare($query,...$values){return $query;}
 public function get_row(...$args){return ['camp_general'=>base64_encode(serialize(['cg_yt_playlist'=>'PL'.str_repeat('a',24),'cg_update_every'=>'15','cg_update_unit'=>'1'])),'camp_options'=>base64_encode(serialize(['OPT_YT_PLAYLIST']))];}
 public function get_var($query){return 1;}
 public function get_results(...$args){return [['ID'=>5,'post_status'=>'publish','meta_value'=>'https://www.youtube.com/watch?v=aaaaaaaaaaa']];}
};
function run_from_cli_scope():string {
 $args=['audit','100'];ob_start();
 require dirname(__DIR__,2).'/deployment/catalog-quality/catalog-inventory.php';
 return ob_get_clean();
}
$result=json_decode(run_from_cli_scope(),true,512,JSON_THROW_ON_ERROR);
if($result['sources'][0]['published']!==1||$result['sources'][0]['missing']!==1||$writes!==0||$result['provider_requests']!==2)throw new RuntimeException('Operator entry-point regression.');
echo 'PASS operator runs from a CLI function scope, completes both provider reads, reconciles coverage, and keeps audit mode read-only.'.PHP_EOL;
