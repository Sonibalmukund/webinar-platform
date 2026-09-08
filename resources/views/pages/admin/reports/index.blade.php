@extends('layouts.portal')
@section('title','Analytics Reports')
@section('content')
@php
    $registrationCount=$registrations->count();
    $attendeeCount=$attendees->count();
    $attendanceRate=$registrationCount?round(($attendeeCount/$registrationCount)*100):0;
    $uniqueVoters=$votes->pluck('registration_id')->filter()->unique()->count();
    $averageWatch=$attendeeCount?round($watchSeconds/$attendeeCount/60):0;
    $registrationPoints=$trend->map(fn($row,$i)=>(24+$i*(752/13)).','.(204-($row['registrations']/$maxTrend)*170))->join(' ');
    $attendancePoints=$trend->map(fn($row,$i)=>(24+$i*(752/13)).','.(204-($row['attendees']/$maxTrend)*170))->join(' ');
@endphp

<section class="report-hero">
    <div class="report-hero-copy">
        <span class="report-kicker"><i class="bi bi-stars"></i> INTELLIGENCE CENTER</span>
        <h1>{{ $selected?->title ?? 'All Events' }} report</h1>
        <p>Registrations, attendance aur engagement ka complete real-time overview.</p>
    </div>
    <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filter">
        <label for="reportWebinar">View report for</label>
        <div><i class="bi bi-calendar3"></i><select id="reportWebinar" name="webinar_id" onchange="this.form.submit()"><option value="">All Events</option>@foreach($webinars as $webinar)<option value="{{ $webinar->id }}" @selected($selected?->id===$webinar->id)>{{ $webinar->title }}</option>@endforeach</select></div>
    </form>
    <div class="report-orb report-orb-one"></div><div class="report-orb report-orb-two"></div>
</section>

<div class="report-metrics">
    <article><span class="report-metric-icon purple"><i class="bi bi-people"></i></span><div><small>Registrations</small><strong>{{ number_format($registrationCount) }}</strong><em>Audience acquired</em></div></article>
    <article><span class="report-metric-icon blue"><i class="bi bi-person-check"></i></span><div><small>Attendees</small><strong>{{ number_format($attendeeCount) }}</strong><em>Joined webinar</em></div></article>
    <article><span class="report-metric-icon green"><i class="bi bi-bullseye"></i></span><div><small>Attendance rate</small><strong>{{ $attendanceRate }}%</strong><em>Registration conversion</em></div></article>
    <article><span class="report-metric-icon orange"><i class="bi bi-clock-history"></i></span><div><small>Watch time</small><strong>{{ number_format($watchSeconds/3600,1) }}h</strong><em>{{ $averageWatch }} min average</em></div></article>
</div>

<div class="report-main-grid">
    <section class="panel-card report-chart-card">
        <div class="panel-title"><div><h3>Audience momentum</h3><p>Last 14 days registration vs attendance trend</p></div><div class="chart-key"><span class="blue">Registrations</span><span class="green">Attendees</span></div></div>
        <div class="report-chart-scale">@foreach([100,75,50,25,0] as $scale)<span>{{ round($maxTrend*$scale/100) }}</span>@endforeach</div>
        <div class="report-line-chart"><svg viewBox="0 0 800 235" preserveAspectRatio="none"><defs><linearGradient id="reportArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7c3aed" stop-opacity=".24"/><stop offset="1" stop-color="#7c3aed" stop-opacity="0"/></linearGradient></defs>@foreach([34,76,119,161,204] as $y)<line x1="24" y1="{{ $y }}" x2="776" y2="{{ $y }}" class="chart-grid-line"/>@endforeach<polygon points="24,204 {{ $registrationPoints }} 776,204" fill="url(#reportArea)"/><polyline points="{{ $registrationPoints }}" class="report-chart-line report-registration-line"/><polyline points="{{ $attendancePoints }}" class="report-chart-line report-attendance-line"/>@foreach($trend as $row)<circle cx="{{ 24+$loop->index*(752/13) }}" cy="{{ 204-($row['registrations']/$maxTrend)*170 }}" r="4.5" class="report-dot registration-dot"><title>{{ $row['label'] }}: {{ $row['registrations'] }} registrations</title></circle><circle cx="{{ 24+$loop->index*(752/13) }}" cy="{{ 204-($row['attendees']/$maxTrend)*170 }}" r="4.5" class="report-dot attendance-dot"><title>{{ $row['label'] }}: {{ $row['attendees'] }} attendees</title></circle>@endforeach</svg><div class="report-chart-labels">@foreach($trend as $row)<span>{{ $loop->index%2===0?$row['label']:'' }}</span>@endforeach</div></div>
    </section>
    <aside class="panel-card report-conversion-card">
        <div class="panel-title"><div><h3>Conversion pulse</h3><p>Audience journey</p></div><span class="status-badge active">LIVE DATA</span></div>
        <div class="report-ring" style="--report-progress:{{ min(100,$attendanceRate) }}"><div><strong>{{ $attendanceRate }}%</strong><small>attendance</small></div></div>
        <div class="report-funnel"><div><span>Registered</span><strong>{{ $registrationCount }}</strong></div><i></i><div><span>Attended</span><strong>{{ $attendeeCount }}</strong></div><i></i><div><span>Unique voters</span><strong>{{ $uniqueVoters }}</strong></div></div>
    </aside>
</div>

<div class="report-insights">
    <article><i class="bi bi-bar-chart-fill"></i><span><small>Total polls</small><strong>{{ $polls->count() }}</strong></span></article>
    <article><i class="bi bi-check2-square"></i><span><small>Votes submitted</small><strong>{{ $votes->count() }}</strong></span></article>
    <article><i class="bi bi-person-vcard"></i><span><small>Unique voters</small><strong>{{ $uniqueVoters }}</strong></span></article>
    <article><i class="bi bi-stopwatch"></i><span><small>Average watch</small><strong>{{ $averageWatch }} min</strong></span></article>
</div>

<section class="panel-card mt-4 report-performance">
    <div class="panel-title"><div><h3>Webinar performance</h3><p>{{ $selected ? 'Selected webinar breakdown' : 'Compare every event at a glance' }}</p></div><span class="report-table-count">{{ $performance->count() }} webinar{{ $performance->count()===1?'':'s' }}</span></div>
    <div class="table-responsive"><table class="premium-table"><thead><tr><th>Webinar</th><th>Registered</th><th>Attended</th><th>Conversion</th><th>Polls</th><th>Votes</th><th>Watch time</th></tr></thead><tbody>@forelse($performance as $row)<tr><td><div class="report-event-name"><span>{{ Str::of($row['webinar']->title)->substr(0,2)->upper() }}</span><div><strong>{{ $row['webinar']->title }}</strong><small>{{ ucfirst($row['webinar']->status) }}</small></div></div></td><td>{{ $row['registered'] }}</td><td>{{ $row['attended'] }}</td><td><div class="report-progress"><span style="width:{{ min(100,$row['rate']) }}%"></span></div><strong>{{ $row['rate'] }}%</strong></td><td>{{ $row['polls'] }}</td><td>{{ $row['votes'] }}</td><td>{{ number_format($row['watch']/3600,1) }}h</td></tr>@empty<tr><td colspan="7"><div class="report-empty"><i class="bi bi-graph-up-arrow"></i><strong>No report data yet</strong><span>Create a webinar and collect registrations to see analytics.</span></div></td></tr>@endforelse</tbody></table></div>
</section>
@endsection
