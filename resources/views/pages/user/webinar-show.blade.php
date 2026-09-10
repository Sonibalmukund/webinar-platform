@extends('layouts.public')
@section('title',$webinar->title)
@section('body-class','webinar-microsite-page')
@php
$theme = data_get($webinar->settings, 'experience', []);
$pinnedAnnouncement = data_get($webinar->settings, 'pinned_announcement', []);
$defaultBannerObj = (object)[
    'media_type' => 'image',
    'media_path' => asset('images/default-banner.jpg'),
    'media_url' => asset('images/default-banner.jpg'),
    'title' => $webinar->title
];
$heroBanner = $banners->first() ?: $defaultBannerObj;
$bannerSlides = $banners->isNotEmpty()
    ? $banners->map(fn($banner) => ['type' => $banner->media_type, 'src' => $banner->media_path ?: $banner->media_url, 'title' => $banner->title])->filter(fn($slide) => filled($slide['src']))->values()
    : collect([['type' => 'image', 'src' => asset('images/default-banner.jpg'), 'title' => $webinar->title]]);
$speakerProfiles = $webinar->speakers->map(fn($speaker) => ['name' => $speaker->name, 'photo' => $speaker->photo_path, 'headline' => $speaker->headline, 'company' => $speaker->company, 'bio' => $speaker->bio])->values();
@endphp
@section('meta')
<meta name="description" content="{{ Str::limit(strip_tags($webinar->short_description ?: $webinar->description), 160) }}">
<meta property="og:title" content="{{ $webinar->title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($webinar->short_description ?: $webinar->description), 160) }}">
@if($heroBanner && $heroBanner->media_type === 'image')
<meta property="og:image" content="{{ $heroBanner->media_path ?: $heroBanner->media_url }}">
@endif
@endsection
@section('content')
<style>
:root{--webinar-primary:{{ $theme['primary']??'#6d28d9' }};--webinar-secondary:{{ $theme['secondary']??'#2563eb' }};--webinar-bg:{{ $theme['background']??'#f7f8fc' }};--webinar-text:{{ $theme['text']??'#101828' }}}
html,body.webinar-microsite-page{overflow-x:hidden!important;max-width:100vw!important;scrollbar-width:none!important;-ms-overflow-style:none!important;scrollbar-gutter:auto!important}
.webinar-microsite-page{background:var(--webinar-bg);color:var(--webinar-text);overflow-x:hidden!important;scrollbar-width:none!important;-ms-overflow-style:none!important}
html::-webkit-scrollbar,body::-webkit-scrollbar,.webinar-microsite-page::-webkit-scrollbar,*::-webkit-scrollbar{display:none!important;width:0!important;height:0!important;background:transparent!important}
.webinar-microsite-page *{box-sizing:border-box;scrollbar-width:none!important;-ms-overflow-style:none!important}
.webinar-microsite-page .public-header{display:none}
.webinar-microsite-page .container{max-width:1380px;width:100%;margin-left:auto;margin-right:auto;padding-left:clamp(16px,3.5vw,40px);padding-right:clamp(16px,3.5vw,40px)}
.webinar-microsite-page .btn-gradient{border:0;background:linear-gradient(135deg,var(--webinar-primary),var(--webinar-secondary));color:#fff}
.microsite-nav{background:#fff;border-bottom:1px solid #e7eaf1;position:sticky;top:0;z-index:1030;width:100%}
.microsite-nav-inner{width:100%;min-height:74px;padding:10px clamp(16px,3.5vw,48px);display:flex;align-items:center;justify-content:space-between;gap:20px}
.microsite-logo{display:flex;align-items:center;gap:12px;color:var(--webinar-text);font-weight:800;text-decoration:none;flex-shrink:0}
.microsite-logo img{max-width:180px;max-height:50px;object-fit:contain}
.microsite-links{display:flex;align-items:center;justify-content:center;gap:32px;flex:1}
.microsite-links a{color:#526079;text-decoration:none;font-weight:600;font-size:.92rem;transition:color .15s ease;white-space:nowrap}
.microsite-links a:hover{color:var(--webinar-primary)}
.microsite-nav-actions{display:flex;align-items:center;gap:12px;flex-shrink:0;margin-left:auto}
.btn-login-unique{display:inline-flex;align-items:center;justify-content:center;padding:8px 20px;border-radius:999px;border:1.5px solid color-mix(in srgb,var(--webinar-primary) 35%,transparent);background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);color:var(--webinar-primary);font-weight:750;font-size:.88rem;letter-spacing:.02em;box-shadow:0 2px 8px rgba(0,0,0,.04);transition:all .22s cubic-bezier(0.16,1,0.3,1);cursor:pointer;text-decoration:none}
.btn-login-unique:hover{background:var(--webinar-primary);color:#fff;border-color:var(--webinar-primary);transform:translateY(-2px);box-shadow:0 6px 18px color-mix(in srgb,var(--webinar-primary) 30%,transparent)}
.btn-register-nav{display:inline-flex;align-items:center;justify-content:center;padding:8px 22px;border-radius:999px;border:0;background:linear-gradient(135deg,var(--webinar-primary),var(--webinar-secondary));color:#fff!important;font-weight:750;font-size:.88rem;letter-spacing:.02em;box-shadow:0 4px 14px color-mix(in srgb,var(--webinar-primary) 32%,transparent);transition:all .22s cubic-bezier(0.16,1,0.3,1);cursor:pointer;text-decoration:none}
.btn-register-nav:hover{transform:translateY(-2px);color:#fff!important;box-shadow:0 8px 22px color-mix(in srgb,var(--webinar-primary) 42%,transparent)}
.microsite-menu-toggle{border:1px solid #e5e7eb;background:#fff;border-radius:10px;width:42px;height:42px}
.mobile-menu{background:#fff;border-top:1px solid #edf0f5}
.microsite-mobile-container{display:grid;gap:6px;padding:12px clamp(16px,3.5vw,48px)}
.microsite-mobile-container a{padding:10px 0;color:#526079;text-decoration:none;font-weight:600}

.landing-container{max-width:1180px;margin:0 auto;padding:0 20px}
.landing-hero-header{text-align:left;margin-bottom:18px;padding:0}
.hero-section.event-hero h1.landing-webinar-title,
.landing-webinar-title{font-size:clamp(1.2rem,1.85vw,1.55rem)!important;font-weight:800!important;color:var(--webinar-primary)!important;margin:0 0 0 0!important;letter-spacing:-.015em!important;line-height:1.26!important;max-width:100%!important;text-align:left!important}

.event-hero{padding:26px 0 32px!important;background:radial-gradient(circle at 86% 12%,color-mix(in srgb,var(--webinar-secondary) 14%,transparent),transparent 35%)}

/* Banner Card */
.event-banner-card{overflow:hidden;border-radius:24px;background:#fff;border:1px solid #e7ebf3;box-shadow:0 18px 55px color-mix(in srgb,var(--webinar-primary) 12%,transparent),0 4px 18px rgba(0,0,0,.03);transition:box-shadow .3s ease}
.event-banner-card:hover{box-shadow:0 24px 70px color-mix(in srgb,var(--webinar-primary) 18%,transparent),0 8px 24px rgba(0,0,0,.05)}

.event-banner{width:100%;height:clamp(340px,40vw,480px);min-height:0;border-radius:0;box-shadow:none;position:relative;background:linear-gradient(135deg,color-mix(in srgb,var(--webinar-primary) 14%,var(--webinar-bg)),color-mix(in srgb,var(--webinar-secondary) 16%,var(--webinar-bg)));overflow:hidden}
.event-banner .carousel,.event-banner .carousel-inner,.event-banner .carousel-item{width:100%;height:100%;position:relative}
.event-banner>iframe{width:100%;height:100%;border:0}
.banner-backdrop{position:absolute;inset:-25px;background-size:cover;background-position:center;filter:blur(25px) brightness(0.9) saturate(1.2);opacity:0.48;z-index:1;transform:scale(1.1);pointer-events:none}
.event-banner img,.event-banner video,.event-banner iframe,.event-banner .carousel-item img,.event-banner .carousel-item video,.event-banner .carousel-item iframe{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;border:0;display:block;margin:auto;z-index:2;filter:drop-shadow(0 4px 22px rgba(0,0,0,.18))}
.event-banner .carousel-control-prev,.event-banner .carousel-control-next{display:none!important}
.event-banner .carousel-indicators{z-index:4}
.banner-status-pill{position:absolute;top:16px;right:18px;z-index:10;display:inline-flex;align-items:center;gap:7px;background:#ef4444;color:#fff;border-radius:999px;padding:6px 14px;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;box-shadow:0 4px 14px rgba(239,68,68,.4);pointer-events:none}
.banner-status-pill b{width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite}
.banner-slide-caption{position:absolute;z-index:3;left:22px;bottom:22px;max-width:75%;padding:9px 13px;border:1px solid #ffffff45;border-radius:11px;background:#0b1228a8;color:#fff;font-size:.82rem;font-weight:750;backdrop-filter:blur(10px)}
.carousel-progress{position:absolute;z-index:5;left:0;right:0;bottom:0;height:4px;background:#ffffff38}
.carousel-progress span{display:block;width:0;height:100%;background:linear-gradient(90deg,#fff,var(--webinar-secondary));animation:bannerProgress 5s linear infinite}
@keyframes bannerProgress{from{width:0}to{width:100%}}

.banner-event-rail{display:grid;grid-template-columns:1fr 1fr 1.35fr;align-items:center;padding:18px 24px;background:#fff;border-top:1px solid #f1f3f7;gap:14px}
.banner-event-detail{display:flex;align-items:center;gap:13px;padding:4px 14px;border-right:1px solid #edf0f5;min-width:0}
.banner-event-detail:last-child{border-right:0}
.rail-icon{flex:0 0 42px;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:1.25rem;color:var(--webinar-primary);background:color-mix(in srgb,var(--webinar-primary) 10%,#fff);border:1px solid color-mix(in srgb,var(--webinar-primary) 16%,transparent)}
.rail-info{display:grid;min-width:0}
.rail-info strong{color:#0f172a;font-size:.95rem;font-weight:750;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3}
.rail-info small{font-size:.7rem;font-weight:800;letter-spacing:.06em;color:#8a94a8;text-transform:uppercase;margin-top:2px}
.badge-status-pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:color-mix(in srgb,var(--webinar-primary) 12%,#fff);color:var(--webinar-primary);font-size:.7rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;border:1px solid color-mix(in srgb,var(--webinar-primary) 22%,transparent)}
.banner-speakers{display:none!important}
.banner-timer-units{display:flex!important;align-items:center;gap:6px;overflow:visible!important}
.banner-timer-unit{display:grid!important;min-width:44px;padding:5px 6px;border-radius:8px;background:linear-gradient(135deg,color-mix(in srgb,var(--webinar-primary) 13%,#fff),color-mix(in srgb,var(--webinar-secondary) 10%,#fff));text-align:center;overflow:visible!important}
.banner-timer-unit b{font-size:.92rem;line-height:1;color:var(--webinar-primary)}
.banner-timer-unit em{margin-top:2px;font-size:.5rem;font-style:normal;font-weight:800;letter-spacing:.05em;color:#7a869c}

.event-action-bar{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:9px;padding:12px 24px 18px;background:#fff;border-top:1px solid #f8fafc}
.event-action-bar a,.event-action-bar button{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border:1px solid #e3e7ef;border-radius:10px;background:#fff;color:#44516a;text-decoration:none;font-size:.78rem;font-weight:750;transition:.2s ease}
.event-action-bar a:hover,.event-action-bar button:hover{border-color:var(--webinar-primary);color:var(--webinar-primary);background:color-mix(in srgb,var(--webinar-primary) 6%,#fff);box-shadow:0 0 0 1px var(--webinar-primary),0 4px 12px color-mix(in srgb,var(--webinar-primary) 18%,transparent)}

/* Sections General */
.content-section{padding:54px 0;scroll-margin-top:90px}
.section-head{max-width:700px;margin-bottom:30px}
.section-head .eyebrow{color:var(--webinar-primary);font-size:.82rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase}
.section-head h2{font-size:clamp(1.8rem,3.4vw,2.5rem);letter-spacing:-.04em;font-weight:850;color:var(--webinar-text)}

/* About Event Card */
.about-event-card{background:#fff;border:1px solid #e7ebf3;border-radius:24px;padding:clamp(26px,4vw,42px);box-shadow:0 12px 40px rgba(15,23,42,.04);position:relative;overflow:hidden}
.about-card-heading{font-size:clamp(1.4rem,2.5vw,1.85rem);font-weight:850;color:var(--webinar-primary);margin-bottom:16px;letter-spacing:-.02em}
.about-copy{font-size:1.05rem;line-height:1.8;color:#334155}

/* Speakers */
.speaker-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}
.webinar-microsite-page .speaker-profile{display:flex!important;flex-direction:column!important;align-items:center!important;padding:28px 24px;border:1.5px solid #e6e9f1;border-radius:24px;background:#fff;box-shadow:0 12px 35px #19213b0c;transition:all .28s cubic-bezier(0.16,1,0.3,1);text-align:center;overflow:visible}
.webinar-microsite-page .speaker-profile:hover{transform:translateY(-6px);border-color:var(--webinar-primary)!important;box-shadow:0 18px 45px color-mix(in srgb,var(--webinar-primary) 18%,transparent),0 4px 14px rgba(0,0,0,.04)}
.webinar-microsite-page .speaker-image{width:180px;height:180px;aspect-ratio:1;margin:0 auto 20px;border:4px solid #fff;border-radius:50%;overflow:hidden;box-shadow:0 0 0 2px #e6e9f1,0 10px 30px #17213a20;display:grid;place-items:center;background:linear-gradient(145deg,color-mix(in srgb,var(--webinar-primary) 12%,#fff),color-mix(in srgb,var(--webinar-secondary) 14%,#fff));font-size:2rem;font-weight:800;color:var(--webinar-primary);transition:all .28s cubic-bezier(0.16,1,0.3,1)}
.webinar-microsite-page .speaker-profile:hover .speaker-image{box-shadow:0 0 0 3.5px var(--webinar-primary),0 14px 35px color-mix(in srgb,var(--webinar-primary) 26%,transparent);transform:scale(1.03)}
.webinar-microsite-page .speaker-image img{width:100%;height:100%;object-fit:cover;border-radius:50%}
.webinar-microsite-page .speaker-info{width:100%;padding:0;text-align:center}
.webinar-microsite-page .speaker-info h3{font-size:1.14rem;margin:0 0 6px;font-weight:800}
.webinar-microsite-page .speaker-info .designation{display:block;color:var(--webinar-primary);font-weight:700;font-size:.88rem}
.webinar-microsite-page .speaker-info .organization{display:block;color:#667085;font-size:.86rem;margin-top:3px}
.webinar-microsite-page .speaker-info p{color:#667085;font-size:.87rem;line-height:1.55;max-width:320px;margin:14px auto 0}
.speaker-profile-button{margin-top:18px;padding:9px 15px;border:1px solid color-mix(in srgb,var(--webinar-primary) 28%,#e5e7eb);border-radius:999px;background:color-mix(in srgb,var(--webinar-primary) 7%,#fff);color:var(--webinar-primary);font-size:.76rem;font-weight:800;transition:.2s ease;cursor:pointer}
.speaker-profile-button:hover{transform:translateY(-2px);background:var(--webinar-primary);color:#fff;box-shadow:0 9px 25px color-mix(in srgb,var(--webinar-primary) 24%,transparent)}

/* Brands Section & Cards */
.brands-section-card{background:#fff;border:1.5px solid #e7ebf3;border-radius:26px;padding:clamp(28px,4.5vw,44px);box-shadow:0 16px 45px rgba(15,23,42,.04)}
.webinar-microsite-page .brand-grid{display:flex;flex-wrap:wrap;align-items:stretch;gap:20px;overflow:visible!important}
.webinar-microsite-page .brand-card{flex:0 0 230px;min-height:115px;padding:22px 30px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid #e5e9f2;border-radius:20px;background:#fff;color:var(--webinar-text);box-shadow:0 4px 18px rgba(15,23,42,.03);position:relative;overflow:hidden;cursor:default!important;user-select:none;transition:all .32s cubic-bezier(0.16,1,0.3,1);animation:none!important}
.webinar-microsite-page .brand-card::after{content:"";position:absolute;inset:0;border-radius:20px;background:radial-gradient(circle at 50% 0%,color-mix(in srgb,var(--webinar-primary) 12%,transparent),transparent 75%);opacity:0;transition:opacity .3s ease;pointer-events:none}
.webinar-microsite-page .brand-card img{max-width:170px;max-height:54px;object-fit:contain;transition:transform .3s cubic-bezier(0.16,1,0.3,1)}
.webinar-microsite-page .brand-card strong{font-size:1.05rem;font-weight:800;letter-spacing:-.01em;transition:color .2s ease}
.webinar-microsite-page .brand-card:hover{transform:translateY(-5px);border-color:color-mix(in srgb,var(--webinar-primary) 35%,#e5e9f2);box-shadow:0 14px 32px color-mix(in srgb,var(--webinar-primary) 12%,transparent)}
.webinar-microsite-page .brand-card:hover::after{opacity:1}
.webinar-microsite-page .brand-card:hover img{transform:scale(1.06)}
.webinar-microsite-page .brand-card:hover strong{color:var(--webinar-primary)}

/* Modals & Footer */
.speaker-detail-modal .modal-content{border:0;border-radius:26px;overflow:hidden;box-shadow:0 28px 80px #10182833}
.speaker-detail-hero{padding:34px;background:linear-gradient(135deg,color-mix(in srgb,var(--webinar-primary) 12%,#fff),color-mix(in srgb,var(--webinar-secondary) 13%,#fff));text-align:center}
.speaker-detail-hero img,.speaker-detail-avatar{width:150px;height:150px;margin:auto;border:7px solid #fff;border-radius:50%;object-fit:cover;box-shadow:0 12px 32px #11182724}
.speaker-detail-avatar{display:grid;place-items:center;background:linear-gradient(135deg,var(--webinar-primary),var(--webinar-secondary));color:#fff;font-size:2rem;font-weight:850}
.speaker-detail-modal .modal-body{padding:28px 32px 34px;text-align:center}
.speaker-detail-modal .modal-body h2{margin:0 0 6px;font-size:1.6rem}
.speaker-detail-role{color:var(--webinar-primary);font-weight:800}
.speaker-detail-company{color:#667085;margin-top:3px}
.speaker-detail-bio{margin:22px 0 0;padding-top:20px;border-top:1px solid #edf0f5;color:#53617a;line-height:1.75;text-align:left}

.event-footer{padding:52px 0 28px;background:#0c1324;color:#93a0b8;text-align:center}
.event-footer img{max-width:170px;max-height:56px;object-fit:contain;margin-bottom:16px}
.event-footer h3{color:#fff}
.event-footer a{color:#cad3e2;text-decoration:none}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:50px;text-align:left}
.footer-brand p{max-width:470px}
.footer-column{display:grid;align-content:start;gap:9px}
.footer-column strong{color:#fff;margin-bottom:6px}
.footer-bottom{display:flex;justify-content:space-between;gap:20px;margin-top:38px;padding-top:22px;border-top:1px solid #ffffff14}

/* Landing Important Note Announcement Bar / Ticker */
.landing-announcement-bar{position:relative;background:linear-gradient(90deg,#1e1b4b,#2e1065,#1e1b4b);border-bottom:1px solid rgba(139,92,246,.35);color:#fff;padding:10px 0;overflow:hidden;z-index:1025;box-shadow:0 4px 20px rgba(0,0,0,.15);width:100%;max-width:100vw}
.landing-announcement-inner{display:flex;align-items:center;gap:14px;max-width:1380px;width:100%;margin:0 auto;padding:0 clamp(16px,3.5vw,40px);overflow:hidden}
.announcement-pill{display:inline-flex;align-items:center;gap:6px;background:#f59e0b;color:#0f172a;font-size:.72rem;font-weight:850;letter-spacing:.06em;text-transform:uppercase;padding:4px 10px;border-radius:999px;flex-shrink:0;box-shadow:0 2px 8px rgba(245,158,11,.35)}
.announcement-marquee-track{flex:1;min-width:0;overflow:hidden;white-space:nowrap;position:relative;display:flex;align-items:center}
.announcement-marquee-text{display:inline-block;font-size:.88rem;font-weight:600;color:#e0e7ff;letter-spacing:.01em;animation:announcementScroll 25s linear infinite;padding-left:100%}
.landing-announcement-bar:hover .announcement-marquee-text{animation-play-state:paused}
@keyframes announcementScroll{0%{transform:translate(0,0)}100%{transform:translate(-100%,0)}}
.announcement-cta{display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.95);color:#4338ca;font-size:.78rem;font-weight:800;padding:5px 14px;border-radius:999px;text-decoration:none;flex-shrink:0;transition:all .2s ease;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.announcement-cta:hover{background:#fff;color:#3730a3;box-shadow:0 4px 14px rgba(0,0,0,.25)}

/* Floating Scroll to Top Button */
.scroll-to-top-btn{position:fixed;bottom:30px;right:30px;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--webinar-primary),var(--webinar-secondary));color:#fff;border:2px solid rgba(255,255,255,.25);box-shadow:0 8px 24px rgba(0,0,0,.2),0 0 16px color-mix(in srgb,var(--webinar-primary) 40%,transparent);display:flex;align-items:center;justify-content:center;font-size:1.35rem;cursor:pointer;z-index:1040;opacity:0;visibility:hidden;transform:translateY(20px) scale(.9);transition:all .3s cubic-bezier(0.16,1,0.3,1)}
.scroll-to-top-btn.show{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
.scroll-to-top-btn:hover{background:linear-gradient(135deg,var(--webinar-secondary),var(--webinar-primary));transform:translateY(-4px) scale(1.08);box-shadow:0 12px 30px rgba(0,0,0,.25),0 0 22px color-mix(in srgb,var(--webinar-primary) 60%,transparent);color:#fff}
.scroll-to-top-btn:active{transform:translateY(-1px) scale(.98)}

@media(max-width:900px){
    .speaker-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .microsite-links{display:none}
    .footer-grid{grid-template-columns:1fr 1fr}
    .footer-brand{grid-column:1/-1}
}
@media(max-width:700px){
    .banner-event-rail{grid-template-columns:1fr}
    .banner-event-detail{border-right:0;border-bottom:1px solid #edf0f5;padding:10px 4px}
    .banner-event-detail:last-of-type{border-bottom:0}
    .event-footer{padding-bottom:100px!important}
}
@media(max-width:600px){
    .microsite-nav-inner{min-height:62px;padding:8px 14px}
    .microsite-logo img{max-height:38px;max-width:130px}
    .microsite-nav-actions{gap:8px}
    .btn-login-unique{padding:6px 14px;font-size:.8rem}
    .btn-register-nav{padding:6px 14px;font-size:.8rem}
    .speaker-grid{grid-template-columns:1fr}
    .content-section{padding:52px 0}
    .footer-grid{grid-template-columns:1fr}
    .footer-brand{grid-column:auto}
    .footer-bottom{flex-direction:column}
    .landing-announcement-inner{gap:8px}
    .announcement-pill{font-size:.65rem;padding:3px 7px}
    .announcement-cta{font-size:.72rem;padding:4px 10px}
    .scroll-to-top-btn{bottom:20px;right:20px;width:42px;height:42px;font-size:1.15rem}
}
@media(prefers-reduced-motion:reduce){.microsite-nav .btn-gradient{animation:none}}
</style>

<header class="microsite-nav sticky-top">
    <div class="microsite-nav-inner">
        <a class="microsite-logo" href="{{ route('webinars.show',$webinar) }}">
            @if(!empty($theme['logo_url']))
                <img src="{{ $theme['logo_url'] }}" alt="{{ $webinar->title }}">
            @elseif(!empty($siteSettings['site_logo']))
                <img src="{{ $siteSettings['site_logo'] }}" alt="{{ $siteSettings['site_name'] ?? $webinar->title }}">
            @else
                <span>{{ $webinar->title }}</span>
            @endif
        </a>
        <nav class="microsite-links d-none d-lg-flex">
            @if(filled($webinar->description) || filled($webinar->short_description))
                <a href="#about-event">About Event</a>
            @endif
            @if($webinar->speakers->isNotEmpty())
                <a href="#speakers">Speakers</a>
            @endif
            @if($brands->isNotEmpty())
                <a href="#brands">Brands</a>
            @endif
        </nav>
        <div class="microsite-nav-actions">
            @if(!auth()->check() || !auth()->user()->hasRole('learner'))
                <button type="button" class="btn-login-unique" data-landing-login data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Login</button>
                <button type="button" class="btn btn-register-nav" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Register</button>
            @elseif($isRegistered)
                <a class="btn btn-register-nav" href="{{ route('webinars.dashboard',$webinar) }}">Open Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('webinars.show', $webinar) }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-1" title="Logout">Logout</button>
                </form>
            @else
                <button type="button" class="btn btn-register-nav" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Register</button>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('webinars.show', $webinar) }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-1" title="Logout">Logout</button>
                </form>
            @endif
            <button class="microsite-menu-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#micrositeMobileNav" aria-label="Open navigation"><i class="bi bi-list fs-4"></i></button>
        </div>
    </div>
    <div class="collapse mobile-menu" id="micrositeMobileNav">
        <nav class="microsite-mobile-container">
            @if(filled($webinar->description) || filled($webinar->short_description))
                <a href="#about-event" data-bs-toggle="collapse" data-bs-target="#micrositeMobileNav">About Event</a>
            @endif
            @if($webinar->speakers->isNotEmpty())
                <a href="#speakers" data-bs-toggle="collapse" data-bs-target="#micrositeMobileNav">Speakers</a>
            @endif
            @if($brands->isNotEmpty())
                <a href="#brands" data-bs-toggle="collapse" data-bs-target="#micrositeMobileNav">Brands</a>
            @endif
            <div class="d-flex gap-2 pt-2 border-top mt-1">
                @if(!auth()->check() || !auth()->user()->hasRole('learner'))
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-landing-login data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Login</button>
                    <button type="button" class="btn btn-sm btn-register-nav rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Register</button>
                @elseif($isRegistered)
                    <a class="btn btn-sm btn-register-nav rounded-pill px-3" href="{{ route('webinars.dashboard',$webinar) }}">Open Dashboard</a>
                @else
                    <button type="button" class="btn btn-sm btn-register-nav rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Register</button>
                @endif
            </div>
        </nav>
    </div>
</header>

<div class="landing-announcement-bar" id="landingAnnouncementBar" @if(empty($pinnedAnnouncement['enabled']) || empty($pinnedAnnouncement['message'])) style="display:none;" @endif>
    <div class="landing-announcement-inner">
        <span class="announcement-pill"><i class="bi bi-pin-angle-fill"></i> IMPORTANT NOTE</span>
        <div class="announcement-marquee-track">
            <span class="announcement-marquee-text" data-landing-announcement-message>{{ $pinnedAnnouncement['message'] ?? '' }}</span>
        </div>
        <a href="{{ $pinnedAnnouncement['button_url'] ?? '#' }}" target="_blank" rel="noopener" class="announcement-cta" data-landing-announcement-btn @if(empty($pinnedAnnouncement['button_url'])) style="display:none;" @endif>
            <span data-landing-announcement-btn-text>{{ ($pinnedAnnouncement['button_text'] ?? null) ?: 'Learn more' }}</span>
            <i class="bi bi-arrow-up-right"></i>
        </a>
    </div>
</div>

<main data-public-webinar="{{ $webinar->id }}">
    <section class="hero-section event-hero">
        <div class="container landing-container">
            <div class="landing-hero-header">
                <h1 class="landing-webinar-title">{{ $webinar->title }}</h1>
            </div>

            <div class="event-banner-card">
                <div class="event-banner">
                    <span class="banner-status-pill"><b></b> {{ strtoupper($webinar->status) }}</span>
                    @if($heroBanner)
                        @php
                        $heroMediaSrc = $heroBanner->media_path ?: $heroBanner->media_url;
                        $heroYt = [];
                        $heroVim = [];
                        if ($heroBanner->media_type === 'video') {
                            preg_match('/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/i', $heroMediaSrc, $heroYt);
                            preg_match('/vimeo\.com\/(?:video\/)?(\d{6,12})/i', $heroMediaSrc, $heroVim);
                        }
                        @endphp
                        @if($heroBanner->media_type==='video')
                            @if(!empty($heroYt[1]))
                                <iframe class="banner-main-media" src="https://www.youtube-nocookie.com/embed/{{ $heroYt[1] }}?autoplay=1&mute=1&loop=1&playlist={{ $heroYt[1] }}&controls=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                            @elseif(!empty($heroVim[1]))
                                <iframe class="banner-main-media" src="https://player.vimeo.com/video/{{ $heroVim[1] }}?autoplay=1&muted=1&loop=1&autopause=0&background=1" allow="autoplay; fullscreen" allowfullscreen></iframe>
                            @else
                                <video class="banner-main-media" src="{{ $heroMediaSrc }}" autoplay muted loop playsinline preload="auto"></video>
                            @endif
                        @else
                            <div class="banner-backdrop" style="background-image:url('{{ $heroMediaSrc }}')"></div>
                            <img class="banner-main-media" src="{{ $heroMediaSrc }}" alt="{{ $heroBanner->title }}">
                        @endif
                        <span class="banner-slide-caption">{{ $heroBanner->title ?: $webinar->title }}</span>
                    @else
                        <div class="banner-placeholder d-flex flex-column align-items-center justify-content-center text-center p-5 h-100" style="background: linear-gradient(135deg, color-mix(in srgb, var(--webinar-primary) 70%, #0f172a), color-mix(in srgb, var(--webinar-secondary) 70%, #0f172a));">
                            <i class="bi bi-camera-video text-white opacity-50 mb-3" style="font-size: 3.5rem;"></i>
                            <h2 class="text-white fw-bold mb-2">{{ $webinar->title }}</h2>
                            <span class="text-white-50">{{ $webinar->starts_at?->timezone($webinar->timezone)->format('F d, Y · g:i A') ?: 'Upcoming Webinar' }}</span>
                        </div>
                    @endif
                </div>

                <div class="banner-event-rail">
                    <div class="banner-event-detail">
                        <div class="rail-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="rail-info">
                            <strong>
                                {{ $webinar->starts_at?->timezone($webinar->timezone)->format('d F, Y') ?: 'Date to be announced' }}
                                @if($webinar->ends_at && $webinar->ends_at->format('Y-m-d') !== $webinar->starts_at?->format('Y-m-d'))
                                    - {{ $webinar->ends_at->timezone($webinar->timezone)->format('d F, Y') }}
                                @endif
                            </strong>
                            <small>SUMMIT DATE</small>
                        </div>
                    </div>
                    <div class="banner-event-detail">
                        <div class="rail-icon"><i class="bi bi-clock"></i></div>
                        <div class="rail-info">
                            <strong>
                                {{ $webinar->starts_at?->timezone($webinar->timezone)->format('g:i A') ?: 'Time to be announced' }}
                                @if($webinar->starts_at)
                                    Onwards
                                @endif
                            </strong>
                            <small>REPORTING</small>
                        </div>
                    </div>
                    <div class="banner-event-detail">
                        <div class="rail-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div class="rail-info">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong>{{ $webinar->status === 'live' ? 'Live Stream' : ($webinar->status === 'ended' ? 'Concluded' : 'Registration Open') }}</strong>
                                <span class="badge-status-pill" data-banner-countdown>{{ $webinar->status === 'live' ? 'LIVE NOW' : ($webinar->status === 'ended' ? 'CONCLUDED' : 'STARTING SOON') }}</span>
                            </div>
                            <small>EVENT STATUS</small>
                        </div>
                    </div>
                </div>

                @php
                $shareUrl = rawurlencode(url()->current());
                $shareText = rawurlencode($webinar->title);
                $googleCal = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.rawurlencode($webinar->title).'&dates='.($webinar->starts_at?->copy()->utc()->format('Ymd\THis\Z')??'').'/'.($webinar->ends_at?->copy()->utc()->format('Ymd\THis\Z')??'').'&details='.rawurlencode($webinar->short_description?:$webinar->description).'&location='.rawurlencode($webinar->venue?:'Online');
                @endphp
                <div class="event-action-bar">
                    <a href="{{ $googleCal }}" target="_blank" rel="noopener"><i class="bi bi-calendar-plus"></i> Add to Calendar</a>
                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i> LinkedIn</a>
                    <button type="button" data-copy-event-link><i class="bi bi-link-45deg"></i><span>Copy Link</span></button>
                </div>
            </div>
        </div>
    </section>

    <div class="container landing-container">
        @if($errors->any() && !old('_auth_modal'))
            <div class="alert alert-danger mt-4">{{ $errors->first() }}</div>
        @endif
    </div>

    @if(filled($webinar->description) || filled($webinar->short_description))
    <section class="content-section microsite-section" id="about-event">
        <div class="container landing-container">
            <div class="about-event-card">
                <h2 class="about-card-heading">About Us</h2>
                <div class="about-copy">{!! nl2br(e($webinar->description ?: $webinar->short_description)) !!}</div>
            </div>
        </div>
    </section>
    @endif

    @if($webinar->speakers->isNotEmpty())
    <section class="content-section microsite-section" id="speakers">
        <div class="container landing-container">
            <div class="section-head">
                <span class="eyebrow">MEET THE EXPERTS</span>
                <h2>Our speakers</h2>
            </div>
            <div class="speaker-grid">
                @foreach($webinar->speakers as $speaker)
                <article class="speaker-profile">
                    <div class="speaker-image">
                        @if($speaker->photo_path)
                            <img src="{{ $speaker->photo_path }}" alt="{{ $speaker->name }}" loading="lazy" onerror="this.remove()">
                        @else
                            {{ Str::of($speaker->name)->substr(0,2)->upper() }}
                        @endif
                    </div>
                    <div class="speaker-info">
                        <h3>{{ $speaker->name }}</h3>
                        @if($speaker->headline)<span class="designation">{{ $speaker->headline }}</span>@endif
                        @if($speaker->company)<span class="organization">{{ $speaker->company }}</span>@endif
                        @if($speaker->bio)<p>{{ Str::limit($speaker->bio,150) }}</p>@endif
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($brands->isNotEmpty())
    <section class="content-section microsite-section" id="brands">
        <div class="container landing-container">
            <div class="brands-section-card">
                <div class="section-head mb-4">
                    <span class="eyebrow"><i class="bi bi-award-fill me-1"></i> OUR PARTNERS</span>
                    <h2 class="mt-1">Presented by</h2>
                </div>
                <div class="brand-grid">
                    @foreach($brands as $brand)
                    <div class="brand-card">
                        @if($brand->logo_path)
                            <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" loading="lazy">
                        @else
                            <strong>{{ $brand->name }}</strong>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
</main>

<footer class="event-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                @if(!empty($theme['logo_url']))
                    <img src="{{ $theme['logo_url'] }}" alt="{{ $webinar->title }}">
                @elseif(!empty($siteSettings['site_logo']))
                    <img src="{{ $siteSettings['site_logo'] }}" alt="{{ $siteSettings['site_name'] ?? $webinar->title }}">
                @endif
                <h3>{{ $webinar->title }}</h3>
                @if($webinar->short_description)
                    <p>{{ $webinar->short_description }}</p>
                @endif
            </div>
            <nav class="footer-column">
                <strong>Quick Links</strong>
                @if(filled($webinar->description) || filled($webinar->short_description))
                    <a href="#about-event">About Event</a>
                @endif
                @if($webinar->speakers->isNotEmpty())
                    <a href="#speakers">Speakers</a>
                @endif
                @if($brands->isNotEmpty())
                    <a href="#brands">Brands</a>
                @endif
            </nav>
            <nav class="footer-column">
                <strong>Support</strong>
                @if(filled(data_get($webinar->settings,'contact_mobile')))
                    <a href="tel:{{ preg_replace('/[^0-9+]/','',data_get($webinar->settings,'contact_mobile')) }}"><i class="bi bi-telephone"></i> {{ data_get($webinar->settings,'contact_mobile') }}</a>
                @endif
                <a href="mailto:{{ $siteSettings['admin_email']??'support@example.com' }}">Contact Us</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
            </nav>
        </div>
        <div class="footer-bottom">
            <span>{{ $siteSettings['footer_text']??('© '.date('Y').' All rights reserved.') }}</span>
            <span>{{ $webinar->title }}</span>
        </div>
    </div>
</footer>

<button type="button" id="scrollToTopBtn" class="scroll-to-top-btn" aria-label="Scroll to top" title="Scroll to top">
    <i class="bi bi-arrow-up"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const banner=document.querySelector('.event-banner');
    const slides=@json($bannerSlides);
    if(banner && slides && slides.length > 1){
        const safe=value=>String(value??'').replace(/[&<>'"]/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
        const renderSlideMedia=(slide)=>{
            const src=String(slide.src||'').trim();
            if(slide.type==='video'){
                const ytMatch=src.match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/i)||src.match(/^([A-Za-z0-9_-]{11})$/);
                if(ytMatch) return `<iframe class="banner-main-media" src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}?autoplay=1&mute=1&loop=1&playlist=${ytMatch[1]}&controls=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
                const vimeoMatch=src.match(/vimeo\.com\/(?:video\/)?(\d{6,12})/i);
                if(vimeoMatch) return `<iframe class="banner-main-media" src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1&loop=1&autopause=0&background=1" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                return `<video class="banner-main-media" src="${safe(src)}" autoplay muted loop playsinline preload="auto"></video>`;
            }
            return `<div class="banner-backdrop" style="background-image:url('${safe(src)}')"></div><img class="banner-main-media" src="${safe(src)}" alt="${safe(slide.title||@json($webinar->title))}">`;
        };
        const carouselItems=slides.map((slide,index)=>`<div class="carousel-item ${index===0?'active':''}">${renderSlideMedia(slide)}<span class="banner-slide-caption">${safe(slide.title||@json($webinar->title))}</span></div>`).join('');
        const indicators=`<div class="carousel-indicators">${slides.map((_,index)=>`<button type="button" data-bs-target="#webinarBannerCarousel" data-bs-slide-to="${index}" class="${index===0?'active':''}" aria-label="Banner ${index+1}"></button>`).join('')}</div>`;
        const controls='<button class="carousel-control-prev" type="button" data-bs-target="#webinarBannerCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Previous</span></button><button class="carousel-control-next" type="button" data-bs-target="#webinarBannerCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>';
        banner.innerHTML=`<span class="banner-status-pill"><b></b> {{ strtoupper($webinar->status) }}</span><div id="webinarBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="false" data-bs-wrap="true">${indicators}<div class="carousel-inner">${carouselItems}</div>${controls}<div class="carousel-progress"><span></span></div></div>`;
        bootstrap.Carousel.getOrCreateInstance(banner.querySelector('#webinarBannerCarousel'),{interval:5000,pause:false,wrap:true}).cycle();
        const carouselElement=banner.querySelector('#webinarBannerCarousel');
        carouselElement?.addEventListener('slide.bs.carousel',()=>banner.querySelectorAll('video').forEach(video=>video.pause()));
        carouselElement?.addEventListener('slid.bs.carousel',()=>{const activeVideo=banner.querySelector('.carousel-item.active video');if(activeVideo){activeVideo.currentTime=0;activeVideo.play().catch(()=>{});}const progress=banner.querySelector('.carousel-progress span');if(progress){progress.style.animation='none';void progress.offsetWidth;progress.style.animation='bannerProgress 5s linear infinite';}});
    }
    const copyBtn=document.querySelector('[data-copy-event-link]');
    copyBtn?.addEventListener('click',async event=>{
        await navigator.clipboard.writeText(window.location.href);
        const span=event.currentTarget.querySelector('span');
        if(span){span.textContent='Copied';setTimeout(()=>span.textContent='Copy Link',1600);}
    });
    const countdown=document.querySelector('[data-banner-countdown]');
    @if($webinar->starts_at && !in_array($webinar->status,['live','completed','cancelled']))
    const startAt=new Date(@json($webinar->starts_at->copy()->utc()->toIso8601String())).getTime();
    const updateBannerTimer=()=>{
        const distance=startAt-Date.now();
        if(distance<=0){if(countdown)countdown.textContent='Starting now';return;}
        const days=Math.floor(distance/86400000),hours=Math.floor((distance%86400000)/3600000),minutes=Math.floor((distance%3600000)/60000),seconds=Math.floor((distance%60000)/1000);
        if(countdown){
            countdown.className='banner-timer-units';
            countdown.innerHTML=`<span class="banner-timer-unit"><b>${String(days).padStart(2,'0')}</b><em>DAYS</em></span><span class="banner-timer-unit"><b>${String(hours).padStart(2,'0')}</b><em>HOURS</em></span><span class="banner-timer-unit"><b>${String(minutes).padStart(2,'0')}</b><em>MIN</em></span><span class="banner-timer-unit"><b>${String(seconds).padStart(2,'0')}</b><em>SEC</em></span>`;
        }
    };
    updateBannerTimer(); setInterval(updateBannerTimer,1000);
    @endif
    const speakerProfiles=@json($speakerProfiles);
    if(speakerProfiles && speakerProfiles.length){
        const escapeProfile=value=>String(value??'').replace(/[&<>'"]/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
        const profileModal=document.createElement('div');
        profileModal.className='modal fade speaker-detail-modal';
        profileModal.id='speakerDetailModal';
        profileModal.tabIndex=-1;
        profileModal.innerHTML='<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0 position-absolute end-0 top-0 z-3"><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div data-speaker-detail></div></div></div>';
        document.body.appendChild(profileModal);
        document.querySelectorAll('.speaker-profile').forEach((card,index)=>{
            const profile=speakerProfiles[index];
            if(!profile) return;
            const button=document.createElement('button');
            button.type='button';button.className='speaker-profile-button';button.innerHTML='<i class="bi bi-person-vcard me-1"></i> View Full Profile';
            card.querySelector('.speaker-info')?.appendChild(button);
            button.addEventListener('click',()=>{
                const initials=profile.name.split(/\s+/).map(part=>part[0]).join('').slice(0,2).toUpperCase();
                const photo=profile.photo?`<img src="${escapeProfile(profile.photo)}" alt="${escapeProfile(profile.name)}">`:`<div class="speaker-detail-avatar">${escapeProfile(initials)}</div>`;
                profileModal.querySelector('[data-speaker-detail]').innerHTML=`<div class="speaker-detail-hero">${photo}</div><div class="modal-body"><h2>${escapeProfile(profile.name)}</h2>${profile.headline?`<div class="speaker-detail-role">${escapeProfile(profile.headline)}</div>`:''}${profile.company?`<div class="speaker-detail-company">${escapeProfile(profile.company)}</div>`:''}<div class="speaker-detail-bio">${escapeProfile(profile.bio||'Speaker profile details will be available soon.')}</div></div>`;
                bootstrap.Modal.getOrCreateInstance(profileModal).show();
            });
        });
    }

    // Floating Back to Top Button
    const scrollBtn = document.getElementById('scrollToTopBtn');
    if (scrollBtn) {
        const toggleScrollBtn = () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        };
        window.addEventListener('scroll', toggleScrollBtn, { passive: true });
        toggleScrollBtn();
        scrollBtn.addEventListener('click', () => {
            const startY = window.scrollY || window.pageYOffset;
            if (startY <= 0) return;
            const duration = 850;
            const startTime = performance.now();
            function step(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = progress < 0.5 ? 4 * progress * progress * progress : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                window.scrollTo(0, startY * (1 - ease));
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            requestAnimationFrame(step);
        });
    }

    // Live Pinned Announcement / Important Note updates on Landing Page
    if (window.Echo) {
        window.Echo.channel('webinar.public.{{ $webinar->id }}')
            .listen('.room.updated', event => {
                if (event.change === 'announcement' && event.state?.pinned_announcement) {
                    const banner = document.getElementById('landingAnnouncementBar');
                    const data = event.state.pinned_announcement;
                    if (banner) {
                        const isEnabled = Boolean(data.enabled && data.message);
                        banner.style.display = isEnabled ? '' : 'none';
                        const msgNode = banner.querySelector('[data-landing-announcement-message]');
                        if (msgNode && data.message) msgNode.textContent = data.message;
                        const btn = banner.querySelector('[data-landing-announcement-btn]');
                        const btnText = banner.querySelector('[data-landing-announcement-btn-text]');
                        if (btn) {
                            if (data.button_url) {
                                btn.href = data.button_url;
                                btn.style.display = '';
                                if (btnText) btnText.textContent = data.button_text || 'Learn more';
                            } else {
                                btn.style.display = 'none';
                            }
                        }
                    }
                }
            });
    }
});
</script>
@endsection

@section('auth-modals')
@include('components.frontend-auth', ['authWebinar'=>$webinar])
@endsection