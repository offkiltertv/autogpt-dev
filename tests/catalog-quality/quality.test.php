<?php
declare(strict_types=1);
define('ABSPATH', '/synthetic/');
$hooks=[];
function add_filter(...$args){$GLOBALS['hooks'][]=$args;}
function add_action(...$args){$GLOBALS['hooks'][]=$args;}
function wp_parse_url($value){return parse_url($value);}
$wpdb = new class {
    public $posts='wp_posts'; public $postmeta='wp_postmeta';
    public function prepare($query,...$values){foreach($values as $value){$query=preg_replace('/%s/',"'".str_replace("'","''",(string)$value)."'",$query,1);}return $query;}
};
require dirname(__DIR__,2).'/deployment/catalog-quality/oktv-catalog-quality.php';
use OffKilter\CatalogQuality\CatalogQuality as Quality;
$passed=0;
function check($condition,$label){if(!$condition)throw new RuntimeException($label);$GLOBALS['passed']++;echo 'PASS '.$label.PHP_EOL;}
function query($args=[],$singular=false){return new class($args,$singular){public function __construct(private array $args,private bool $single){}public function get($key){return $this->args[$key]??'';}public function is_singular(){return $this->single;}};}
check(!Quality::enabled(),'No marker means no live discovery change');
check(Quality::marker_valid(Quality::RELEASE."\n"),'Exact reviewed release marker is accepted');
check(!Quality::marker_valid(Quality::RELEASE.'-other'),'Another release cannot enable this one');
check(Quality::request_eligible([]),'Normal public page is eligible');
foreach(['cli','cron','rest','admin','ajax'] as $boundary)check(!Quality::request_eligible([$boundary=>true]),$boundary.' requests retain their query contract');
check(Quality::request_eligible(['admin'=>true,'ajax'=>true,'action'=>'beeteam368_filter_posts']),'The inspected frontend Load More action receives the same discovery protection');
check(!Quality::request_eligible(['admin'=>true,'ajax'=>true,'action'=>'beeteam368_handle_submit_upload_file_fe']),'Upload and unrelated administrative actions are unaffected');
$where=' AND wp_posts.post_status = \'publish\'';
$filtered=Quality::discovery_where($where,query(['post_type'=>'vidmov_video']));
check($filtered!==$where && str_contains($filtered,"wp_posts.post_type = 'vidmov_video'"),'Exclusion is restricted to video records');
check(str_contains($filtered,"'deleted video','private video'"),'Unusable placeholder titles are suppressed');
check(str_contains($filtered,"oktv_availability.post_id = wp_posts.ID") && str_contains($filtered,"'_oktv_source_availability'"),'Availability is checked against the same record');
check(Quality::discovery_where($filtered,query())===$filtered,'The SQL predicate is not applied twice');
check(Quality::discovery_where($where,query([],true))===$where,'Existing single pages remain accessible');
foreach(['p'=>42,'page_id'=>42,'name'=>'deleted-video','pagename'=>'existing-page','attachment_id'=>42,'attachment'=>'kept-file'] as $key=>$value)check(Quality::discovery_where($where,query([$key=>$value]))===$where,'Explicit '.$key.' lookup remains intact');
foreach(['draft','private','any',['publish','private']] as $status)check(Quality::discovery_where($where,query(['post_status'=>$status]))===$where,'Non-public status query is unchanged');
check(Quality::discovery_where($where,query(['post_type'=>'post']))===$where,'An article with the same title is unaffected');
check(Quality::discovery_where($where,query(['post_type'=>['post','vidmov_video']]))!==$where,'Mixed discovery query filters video records only');
check(Quality::discovery_where($where,query(['post__in'=>[1,2,3]]))!==$where,'Hand-selected recommendations also exclude unavailable videos');
check(Quality::placeholder('  Private Video  ') && Quality::placeholder('Deleted video'),'Placeholder presentation recognition is case-insensitive');
check(!Quality::placeholder('Why a private video disappeared'),'Ordinary titles are retained');
foreach(['https://www.youtube.com/watch?v=abcdefghijk&list=example','https://youtu.be/abcdefghijk','https://www.youtube.com/shorts/abcdefghijk','https://www.youtube-nocookie.com/embed/abcdefghijk'] as $url)check(Quality::youtube_id($url)==='abcdefghijk','Canonical provider ID is read safely');
foreach(['http://www.youtube.com/watch?v=abcdefghijk','https://youtube.com.evil.test/watch?v=abcdefghijk','https://evil-youtube.com/watch?v=abcdefghijk','https://user:pass@www.youtube.com/watch?v=abcdefghijk','https://www.youtube.com:8443/watch?v=abcdefghijk','https://www.youtube.com/watch?v[]=abcdefghijk','https://www.youtube.com/watch?v=short'] as $url)check(Quality::youtube_id($url)==='','Untrusted or malformed provider link is not reused');
echo $passed.' catalog quality checks passed.'.PHP_EOL;
