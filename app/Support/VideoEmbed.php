<?php
namespace App\Support;
final class VideoEmbed {
 public static function url(string $provider,?string $input):?string{$input=trim((string)$input);if($input==='')return null;if(preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i',$input,$m))$input=html_entity_decode($m[1]);if($provider==='youtube'){$id=self::youtubeId($input);return $id?'https://www.youtube-nocookie.com/embed/'.$id.'?rel=0':null;}if($provider==='vimeo'){$id=self::vimeoId($input);return $id?'https://player.vimeo.com/video/'.$id:null;}if($provider==='custom'&&filter_var($input,FILTER_VALIDATE_URL)&&in_array(parse_url($input,PHP_URL_SCHEME),['http','https'],true))return $input;return null;}
 private static function youtubeId(string $input):?string{if(preg_match('/^[A-Za-z0-9_-]{11}$/',$input))return $input;return preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~i',$input,$m)?$m[1]:null;}
 private static function vimeoId(string $input):?string{if(preg_match('/^\d{6,12}$/',$input))return $input;return preg_match('~vimeo\.com/(?:video/)?(\d{6,12})~i',$input,$m)?$m[1]:null;}
}
