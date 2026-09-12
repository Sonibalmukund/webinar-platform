@extends('layouts.portal')
@section('title','My Webinars')
@section('content')
<div class="page-heading"><div><span class="eyebrow">MY LEARNING</span><h1>My webinars</h1><p>Only webinars registered to your account appear here.</p></div><a class="btn btn-gradient" href="{{ route('webinars.index') }}"><i class="bi bi-search"></i> Discover webinars</a></div>
<div class="my-webinar-grid">
@forelse($rows as $row)
@php($webinar=$row['webinar'])
@php($canEnter=!in_array($row['registration']->status,['waitlisted','cancelled','rejected'],true))
<article class="my-webinar-card">
    <div class="my-webinar-accent {{ $webinar->status }}"><span class="status-badge {{ $webinar->status }}">{{ strtoupper($webinar->status) }}</span><i class="bi bi-camera-video-fill"></i></div>
    <div class="my-webinar-copy"><small>{{ $webinar->starts_at?->copy()->timezone($webinar->timezone)->format('M d, Y · g:i A') ?? 'Schedule pending' }}</small><h3>{{ $webinar->title }}</h3><p>{{ Str::limit($webinar->short_description ?: 'Your registered webinar experience.',110) }}</p></div>
    <div class="attendance-summary"><div class="attendance-ring" style="--attendance:{{ $row['metrics']['attendancePercent'] }}"><span>{{ $row['metrics']['attendancePercent'] }}%</span></div><div><strong>Attendance</strong><small>{{ gmdate('H:i:s',$row['metrics']['watch']) }} watched</small></div><span class="status-badge {{ $canEnter?'active':'scheduled' }}">{{ $canEnter?'Registered':ucfirst($row['registration']->status) }}</span></div>
    <div class="attendance-progress"><i style="width:{{ $row['metrics']['attendancePercent'] }}%"></i></div>
    <footer><span>@if($row['attendance']?->last_seen_at)<i class="bi bi-activity"></i> Last active {{ Carbon\Carbon::parse($row['attendance']->last_seen_at)->diffForHumans() }} @else <i class="bi bi-clock-history"></i> Not attended yet @endif</span>@if($canEnter)<a class="btn btn-sm btn-gradient" href="{{ route('webinars.dashboard',$webinar) }}">Open dashboard <i class="bi bi-arrow-right"></i></a>@else<span class="status-badge scheduled">{{ ucfirst($row['registration']->status) }}</span>@endif</footer>
</article>
@empty
<div class="panel-card my-webinar-empty"><i class="bi bi-calendar2-plus"></i><h3>No registered webinars</h3><p>After you register for a webinar, it will appear here automatically.</p><a class="btn btn-gradient" href="{{ route('webinars.index') }}">Browse webinars</a></div>
@endforelse
</div>
@endsection
