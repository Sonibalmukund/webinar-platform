@extends('layouts.portal')
@php($title=ucfirst($type))
@section('title',$title)
@section('content')
<div class="page-heading"><div><span class="eyebrow">MY LEARNING</span><h1>{{ $title }}</h1><p>@if($type==='recordings')Recordings appear only after a completed webinar is published.@elseif($type==='certificates')Only your approved certificates appear here.@else Webinars you personally bookmarked appear here.@endif</p></div></div>
<div class="learner-library-grid">
@forelse($items as $item)
    @if($type==='recordings')
    <article class="learner-library-card"><div class="library-art"><i class="bi bi-play-circle-fill"></i><span>RECORDING</span></div><div><small>{{ $item->published_at?Carbon\Carbon::parse($item->published_at)->format('M d, Y'):'' }}</small><h3>{{ $item->title ?: $item->webinar_title }}</h3><p>{{ $item->webinar_title }}</p><a class="btn btn-gradient w-100" href="{{ $item->recording_url }}" target="_blank" rel="noopener">Watch recording <i class="bi bi-box-arrow-up-right"></i></a></div></article>
    @elseif($type==='certificates')
    <article class="learner-certificate"><i class="bi bi-award"></i><div><small>CERTIFICATE OF COMPLETION</small><h3>{{ $item->webinar_title }}</h3><p>Issued {{ $item->issued_at?Carbon\Carbon::parse($item->issued_at)->format('M d, Y'):'Pending' }} · {{ Str::limit($item->credential_id,18) }}</p></div><a class="icon-btn" href="{{ route('webinars.certificate.download',$item->webinar_slug) }}" title="Download certificate"><i class="bi bi-download"></i></a></article>
    @else
    <article class="learner-library-card"><div class="library-art bookmark"><i class="bi bi-bookmark-fill"></i><span>BOOKMARKED</span></div><div><small>{{ Carbon\Carbon::parse($item->bookmarked_at)->format('M d, Y') }}</small><h3>{{ $item->title }}</h3><p>{{ Str::limit($item->short_description,100) }}</p><a class="btn btn-gradient w-100" href="{{ route('webinars.show',$item->slug) }}">View webinar <i class="bi bi-arrow-right"></i></a></div></article>
    @endif
@empty
<div class="panel-card library-empty"><i class="bi bi-{{ $type==='recordings'?'play-btn':($type==='certificates'?'award':'bookmark') }}"></i><h3>No {{ strtolower($title) }} available</h3><p>@if($type==='recordings')A recording will appear after you attend a completed webinar and the host publishes it.@elseif($type==='certificates')Your approved webinar certificate will appear here once it is issued.@else You have not bookmarked any webinars yet.@endif</p>@if($type==='bookmarks')<a class="btn btn-gradient" href="{{ route('webinars.index') }}">Discover webinars</a>@endif</div>
@endforelse
</div>
@endsection
