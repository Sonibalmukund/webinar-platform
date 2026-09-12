@extends('layouts.portal')
@section('title','Webinar Portal Dashboard')
@section('content')
<div class="welcome-banner"><div><span>WEBINAR PORTAL · {{ now()->format('l, F j') }}</span><h1>Welcome, {{ Str::before(auth()->user()->name,' ') }} <span>👋</span></h1><p>Manage your registrations and see your complete webinar schedule in one place.</p><a href="{{ route('webinars.index') }}" class="btn btn-light">Discover webinars <i class="bi bi-arrow-right"></i></a></div><div class="welcome-art"><i class="bi bi-camera-video"></i><span></span></div></div>
<div class="stats-grid user-stats"><x-stat-card label="Registered" :value="$registrations->count()" icon="calendar-check" /><x-stat-card label="Upcoming" :value="$scheduledWebinars->where('webinar.status','scheduled')->count()" icon="broadcast" tone="red" /><x-stat-card label="Completed" :value="$registrations->filter(fn($r)=>$r->webinar?->status==='completed')->count()" icon="check2-circle" tone="blue" /><x-stat-card label="Access ready" :value="$registrations->count()" icon="person-check" tone="green" /></div>
<div class="content-grid mt-4"><section><div class="section-heading compact"><div><h2>My registrations</h2><p>Your latest webinar registrations</p></div><a href="/my-webinars">View all</a></div><div class="row g-3">@forelse($registrations->filter(fn($registration)=>$registration->webinar)->take(4) as $registration)<div class="col-md-6"><a href="{{ route('webinars.show',$registration->webinar) }}"><x-webinar-card :title="$registration->webinar->title" :speaker="$registration->webinar->creator?->name ?? 'Webinar host'" :date="$registration->webinar->starts_at?->format('M d, Y · g:i A') ?? 'Schedule pending'" /></a></div>@empty<div class="col-12"><div class="panel-card text-center py-5 text-muted">You have not registered for a webinar yet.</div></div>@endforelse</div></section><aside><div class="panel-card"><div class="panel-title"><h3>Next webinar</h3></div>@if($upcoming)<h4>{{ $upcoming->title }}</h4><p><i class="bi bi-calendar3"></i> {{ $upcoming->starts_at->timezone($upcoming->timezone)->format('M d, Y · g:i A') }}</p><a href="{{ route('webinars.dashboard',$upcoming) }}" class="btn btn-gradient w-100">Open webinar portal</a>@else<p class="text-muted mb-0">No upcoming registered webinar.</p>@endif</div></aside></div>
<section class="panel-card webinar-schedule-card mt-4">
    <div class="panel-title"><div><h3>Webinar schedule</h3><p>Your scheduled and live registered webinars</p></div><a href="{{ route('webinars.index') }}">Browse webinars</a></div>
    <div class="webinar-schedule-list">
        @forelse($scheduledWebinars as $registration)
            @php($scheduled=$registration->webinar)
            <article class="webinar-schedule-row">
                <time class="schedule-date" datetime="{{ $scheduled->starts_at->toIso8601String() }}"><strong>{{ $scheduled->starts_at->timezone($scheduled->timezone)->format('d') }}</strong><span>{{ $scheduled->starts_at->timezone($scheduled->timezone)->format('M') }}</span></time>
                <div class="schedule-details"><span class="status-badge {{ $scheduled->status }}">{{ strtoupper($scheduled->status) }}</span><h4>{{ $scheduled->title }}</h4><p><i class="bi bi-clock"></i> {{ $scheduled->starts_at->timezone($scheduled->timezone)->format('g:i A') }}@if($scheduled->ends_at) – {{ $scheduled->ends_at->timezone($scheduled->timezone)->format('g:i A') }}@endif <span>· {{ $scheduled->timezone }}</span></p></div>
                <div class="schedule-actions"><span class="status-badge active">Registered</span><a class="btn btn-sm btn-gradient" href="{{ route('webinars.dashboard',$scheduled) }}">Open dashboard <i class="bi bi-arrow-right"></i></a></div>
            </article>
        @empty
            <div class="schedule-empty"><i class="bi bi-calendar2-week"></i><h4>No webinars scheduled</h4><p>Register for a webinar and it will appear here automatically.</p><a class="btn btn-gradient" href="{{ route('webinars.index') }}">Find a webinar</a></div>
        @endforelse
    </div>
</section>
@endsection
