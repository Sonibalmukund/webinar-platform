@extends('layouts.app')
@section('title','Live Webinar Room')
@section('body-class','live-body')
@section('shell')
<div class="live-shell"><header class="live-header"><x-site-brand light /><div><span class="live-pill"><b></b> LIVE</span><strong>Live webinar room</strong></div><div class="live-header-meta"><a class="btn btn-sm btn-light" href="{{ route('webinars.index') }}">Browse webinars</a></div></header><main class="live-main"><section class="video-stage"><div class="video-bg"><div class="live-speaker"><i class="bi bi-broadcast display-1"></i><h2>{{ $siteSettings['site_name']??'Webinar platform' }}</h2><p>Select a scheduled webinar to enter its configured live room.</p><a class="btn btn-light mt-3" href="{{ route('webinars.index') }}">View webinars</a></div></div></section></main></div>
@endsection
