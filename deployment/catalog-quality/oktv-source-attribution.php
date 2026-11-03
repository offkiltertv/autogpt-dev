<?php
/**
 * Plugin Name: OKTV Source Attribution
 * Description: Credit imported YouTube sources without creating or claiming member accounts.
 * Version: 1.0.0
 */
declare(strict_types=1);
namespace OffKilter\CatalogQuality;
if(!defined('ABSPATH')){exit;}

final class SourceAttribution
{
    public const RELEASE='oktv-source-attribution-1.0.0';
    public const MARKER='/opt/bitnami/oktv-release-state/source-attribution-enabled';
    public const VERIFIED='_oktv_ingest_verified_v1';
    private static bool $wrapped=false;

    public static function init():void
    {
        add_action('wp_loaded',[self::class,'install'],100);
        add_action('wp_head',[self::class,'styles']);
        add_filter('query_vars',static function($vars){$vars[]='oktv_source_channel';return $vars;});
        add_action('pre_get_posts',[self::class,'source_collection']);
        add_filter('okarcana_watch_next_append',static function($on){return self::ready()&&self::context((int)get_the_ID())?false:$on;});
        add_filter('get_the_author_display_name',[self::class,'author_name'],30,2);
        add_filter('beeteam368_author_url',[self::class,'author_url'],30,2);
        add_filter('beeteam368_member_verification_icon',[self::class,'source_icon'],30,2);
        add_filter('beeteam368_member_verification_icon_in_single',[self::class,'source_icon'],30,2);
        add_filter('the_content',[self::class,'more_from_source'],45);
        add_filter('posts_where',[self::class,'account_library_where'],40,2);
        add_filter('beeteam368_post_listing_top_meta_params',static function($params,$id){if(self::ready()&&self::context((int)$id))$params['show_author']=false;return $params;},30,2);
        add_filter('do_shortcode_tag',[self::class,'story_copy'],30,2);
    }

    public static function enabled():bool
    {
        $bytes=is_file(self::MARKER)&&!is_link(self::MARKER)?@file_get_contents(self::MARKER,false,null,0,128):false;
        return is_string($bytes)&&trim($bytes)===self::RELEASE;
    }

    public static function supported():bool
    {
        return function_exists('beeteam368_post_listing_header')&&function_exists('beeteam368_author_single_element')
            &&get_template()==='vidmov'&&wp_get_theme('vidmov')->get('Version')==='2.3.8'
            &&has_action('beeteam368_post_listing_header','beeteam368_post_listing_header')===10
            &&has_action('beeteam368_after_player_in_single_video','beeteam368_author_single_element')===20;
    }

    public static function ready():bool {return self::enabled()&&(self::$wrapped||self::supported());}

    private static function public_request():bool
    {
        if((defined('WP_CLI')&&WP_CLI)||(defined('REST_REQUEST')&&REST_REQUEST)||(function_exists('wp_doing_cron')&&wp_doing_cron()))return false;
        if(function_exists('wp_doing_ajax')&&wp_doing_ajax())return ($_REQUEST['action']??'')==='beeteam368_filter_posts';
        return !is_admin();
    }

    public static function install():void
    {
        if(!self::enabled()||!self::public_request()||!self::supported())return;
        remove_action('beeteam368_post_listing_header','beeteam368_post_listing_header',10);
        add_action('beeteam368_post_listing_header',[self::class,'listing'],10,2);
        remove_action('beeteam368_after_player_in_single_video','beeteam368_author_single_element',20);
        add_action('beeteam368_after_player_in_single_video',[self::class,'single'],20,2);
        self::$wrapped=true;
    }

    public static function valid_channel(string $id):bool {return preg_match('/^UC[A-Za-z0-9_-]{22}$/D',$id)===1;}

    public static function context(int $postId):?array
    {
        if($postId<1||get_post_type($postId)!=='vidmov_video'||get_post_meta($postId,self::VERIFIED,true)!=='1')return null;
        $channel=(string)get_post_meta($postId,'channel_id',true);
        $title=sanitize_text_field((string)get_post_meta($postId,'channel_title',true));
        if(!self::valid_channel($channel)||$title==='')return null;
        return ['channel'=>$channel,'title'=>$title];
    }

    public static function collection_url(string $channel):string
    {
        return self::valid_channel($channel)?add_query_arg('oktv_source_channel',$channel,get_post_type_archive_link('vidmov_video')):'';
    }

    private static function author_context($authorId):?array
    {
        if(!self::ready()||!self::public_request())return null;
        $postId=(int)get_the_ID();
        if((int)get_post_field('post_author',$postId)!==(int)$authorId)return null;
        return self::context($postId);
    }

    public static function author_name($name,$authorId):string
    {
        $source=self::author_context($authorId);return $source?$source['title']:(string)$name;
    }

    public static function author_url($url,$authorId):string
    {
        $source=self::author_context($authorId);return $source?self::collection_url($source['channel']):(string)$url;
    }

    public static function source_icon($html,$authorId):string
    {
        return self::author_context($authorId)?'<span class="oktv-source-label">YouTube source · </span>':(string)$html;
    }

    public static function markup(int $postId,bool $single=false):string
    {
        $source=self::context($postId);if(!$source)return '';
        return '<div class="oktv-source-byline blog-author-element flex-row-control flex-vertical-middle" data-oktv-source="'.esc_attr($source['channel']).'">'
            .'<span class="oktv-source-symbol" aria-hidden="true">YT</span><div class="oktv-source-name">'
            .'<a class="author-avatar-name-link" href="'.esc_url(self::collection_url($source['channel'])).'">'.esc_html($source['title']).'</a>'
            .'<span class="oktv-source-label">YouTube source · curated on OKTV</span></div>'
            .($single?'<a class="oktv-source-original" href="'.esc_url('https://www.youtube.com/channel/'.$source['channel']).'" target="_blank" rel="noopener noreferrer">Original channel <span aria-hidden="true">↗</span></a>':'').'</div>';
    }

    public static function listing($postId=null,$params=[]):void
    {
        $postId=(int)($postId?:get_the_ID());
        if(self::context($postId)){if(($params['show_author']??true)!==false)echo self::markup($postId);return;}
        \beeteam368_post_listing_header($postId,$params);
    }

    public static function single($postId=null,$style='small'):void
    {
        $postId=(int)($postId?:get_the_ID());
        if(self::context($postId)){echo self::markup($postId,true);return;}
        \beeteam368_author_single_element($postId,$style);
    }

    public static function source_collection($query):void
    {
        if(!self::public_request()||!$query->is_main_query()||!$query->is_post_type_archive('vidmov_video'))return;
        $channel=$query->get('oktv_source_channel');
        if(!is_string($channel)||!self::valid_channel($channel))return;
        $existing=$query->get('meta_query');$condition=['key'=>'channel_id','value'=>$channel,'compare'=>'='];
        $query->set('meta_query',$existing?['relation'=>'AND',$existing,$condition]:[$condition]);
        $query->set('post_status','publish');
    }

    public static function more_from_source(string $content):string
    {
        if(!self::ready()||!self::public_request()||!is_singular('vidmov_video')||!in_the_loop()||!is_main_query())return $content;
        $source=self::context((int)get_the_ID());if(!$source)return $content;
        return $content.'<p class="oktv-source-more"><a href="'.esc_url(self::collection_url($source['channel'])).'">More from '.esc_html($source['title']).' on OKTV</a></p>';
    }

    public static function account_library_where(string $where,$query):string
    {
        global $wpdb;
        if(!self::public_request()||$query->is_singular())return $where;
        if(!$query->is_author()&&!$query->get('author')&&!$query->get('author_name')&&!$query->get('author__in'))return $where;
        return $where.$wpdb->prepare(" AND NOT ({$wpdb->posts}.post_type=%s AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} oktv_source_record WHERE oktv_source_record.post_id={$wpdb->posts}.ID AND oktv_source_record.meta_key=%s AND oktv_source_record.meta_value=%s))",'vidmov_video',self::VERIFIED,'1');
    }

    public static function story_copy($html,$tag):string
    {
        if(!self::enabled()||!in_array($tag,['oktv_platform_story','oktv_platform_intro'],true))return (string)$html;
        return str_replace([
            esc_html('Short-form Pulse, standout Signals, and intuitive Arcana — from independent creators who publish here first.'),
            esc_html("Independent creators publish here first — and keep their audience. This is their platform, not an algorithm's."),
        ],[
            esc_html('Independent voices and curated videos worth returning to.'),
            esc_html('Discover independent creators, explore their work, and follow the subjects that matter to you.'),
        ],(string)$html);
    }

    public static function styles():void
    {
        if(!self::ready()||!self::public_request())return;
        echo '<style id="oktv-source-attribution">.oktv-source-byline{gap:12px;flex-wrap:wrap;margin-bottom:18px}.oktv-source-symbol{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:50%;background:#b91c1c;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em}.oktv-source-name{display:flex;flex:1;min-width:0;flex-direction:column;gap:3px}.oktv-source-name>a{font-weight:700;line-height:1.35;overflow-wrap:anywhere}.oktv-source-label{font-size:12px;line-height:1.4;color:#bfc4cb}.oktv-source-original{font-size:12px}.oktv-source-more{margin:24px 0}</style>';
    }
}
SourceAttribution::init();
