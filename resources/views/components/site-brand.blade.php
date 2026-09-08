@props(['light'=>false,'small'=>false,'useSmall'=>false])
@php($siteName=$siteSettings['site_name']??'Webinarly')
@php($logo=$useSmall?($siteSettings['small_logo']??$siteSettings['site_logo']??null):($siteSettings['site_logo']??null))
<a {{ $attributes->class(['brand','brand-light'=>$light]) }} href="/">@if($logo)<img src="{{ $logo }}" alt="{{ $siteName }} logo" style="max-width:{{ $small?'36px':'145px' }};max-height:38px;object-fit:contain">@else<span class="brand-mark"><i class="bi bi-camera-video-fill"></i></span>@endif @unless($small)<span class="site-brand-name" style="color:inherit">{{ $siteName }}</span>@endunless</a>
