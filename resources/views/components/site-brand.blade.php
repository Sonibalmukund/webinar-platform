@props(['light'=>false,'small'=>false,'useSmall'=>false])
@php($siteName=$siteSettings['site_name']??'Webinarly')
@php($logo=$useSmall?($siteSettings['small_logo']??$siteSettings['site_logo']??null):($siteSettings['site_logo']??null))
<a {{ $attributes->class(['brand','brand-light'=>$light,'brand-has-logo'=>(bool)$logo]) }} href="/">@if($logo)<img class="site-brand-logo {{ $small?'site-brand-logo-small':'' }}" src="{{ $logo }}" alt="{{ $siteName }} logo">@else<span class="brand-mark"><i class="bi bi-camera-video-fill"></i></span>@endif @unless($small||$logo)<span class="site-brand-name">{{ $siteName }}</span>@endunless</a>
