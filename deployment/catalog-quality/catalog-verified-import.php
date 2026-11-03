<?php
declare(strict_types=1);
namespace OffKilter\CatalogQuality;

/** Provider data is never an account, an arbitrary download URL, or local views. */
final class VerifiedImport
{
    public static function source(array $detail,string $video):array
    {
        if(!preg_match('/^[A-Za-z0-9_-]{11}$/D',$video)||($detail['id']??'')!==$video||CatalogInventory::availability($detail)!=='available')throw new \RuntimeException('source_not_available');
        $snippet=$detail['snippet'];$channel=(string)($snippet['channelId']??'');
        $name=sanitize_text_field((string)($snippet['channelTitle']??''));
        $title=wp_strip_all_tags((string)($snippet['title']??''),true);
        if(!SourceAttribution::valid_channel($channel)||$name===''||$title===''||CatalogQuality::placeholder($title))throw new \RuntimeException('source_metadata_invalid');
        $published=(string)($snippet['publishedAt']??'');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D',$published)||strtotime($published)===false||strtotime($published)>time()+300)throw new \RuntimeException('source_date_invalid');
        return ['video'=>$video,'channel'=>$channel,'channel_title'=>$name,'title'=>$title,'description'=>(string)($snippet['description']??''),'published_gmt'=>gmdate('Y-m-d H:i:s',strtotime($published)),'thumbnail'=>self::thumbnail($snippet['thumbnails']??[],$video)];
    }

    public static function thumbnail(array $thumbnails,string $video):string
    {
        foreach(['maxres','standard','high','medium','default'] as $size){
            $url=$thumbnails[$size]['url']??'';
            if(!is_string($url)||strlen($url)>512)continue;
            $parts=wp_parse_url($url);
            if(!is_array($parts)||($parts['scheme']??'')!=='https'||($parts['host']??'')!=='i.ytimg.com'||isset($parts['user'])||isset($parts['pass'])||isset($parts['port'])||isset($parts['query'])||isset($parts['fragment']))continue;
            if(preg_match('~^/vi(?:_webp)?/'.preg_quote($video,'~').'/[A-Za-z0-9_-]+\.(?:jpg|webp)$~D',$parts['path']??''))return $url;
        }
        throw new \RuntimeException('trusted_source_thumbnail_missing');
    }

    public static function content(array $source):string
    {
        return '<p><strong>Source:</strong> <a href="'.esc_url('https://www.youtube.com/channel/'.$source['channel']).'">'.esc_html($source['channel_title']).' on YouTube</a>. Curated on OKTV.</p>'.wpautop(esc_html($source['description']));
    }
}
