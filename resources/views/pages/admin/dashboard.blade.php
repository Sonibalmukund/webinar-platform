@extends('layouts.portal')
@section('title','Admin Dashboard')
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ $subadmin?'ASSIGNED EVENTS':'GLOBAL OVERVIEW' }}</span><h1>Dashboard</h1><p>{{ $subadmin?'Live totals for webinars assigned to you.':'Live platform totals calculated from backend data.' }}</p></div>@unless($subadmin)<a href="{{ route('admin.webinars.create') }}" class="btn btn-gradient"><i class="bi bi-plus-lg"></i> Create webinar</a>@endunless</div>
<div class="stats-grid dashboard-metric-grid"><x-stat-card label="Events" :value="$webinars->count()" icon="calendar-event" meta="Total webinars" /><x-stat-card label="Registered users" :value="$registeredUsers" icon="people" tone="purple" meta="All registrations" /><x-stat-card label="New today" :value="$todayRegistrations" icon="person-plus" tone="orange" meta="Today's growth" /><x-stat-card label="Total attendees" :value="$totalAttendees" icon="person-check" tone="blue" meta="Joined sessions" /><x-stat-card label="Live now" :value="$liveNow" icon="broadcast" tone="green" :live="true" meta="Active webinars" /><x-stat-card label="Chat threads" :value="$chatMessages" icon="chat-dots" tone="blue" meta="Messages received" /><x-stat-card label="Comments" :value="$commentsCount" icon="chat-square-text" tone="orange" meta="Private attendee notes" /><x-stat-card label="Polls" :value="$pollsCount" icon="bar-chart" tone="red" meta="Created polls" /><x-stat-card label="Poll voters" :value="$pollVoters" icon="check2-square" tone="green" meta="Unique voters" /><x-stat-card label="Votes submitted" :value="$votesCount" icon="ui-checks-grid" tone="purple" meta="Total responses" /></div>
<section class="panel-card mt-4"><div class="panel-title"><div><span class="eyebrow">COMMAND CENTER</span><h3>Operational attention</h3><p>Live backend checks that may need an administrator.</p></div></div><div class="stats-grid"><x-stat-card label="Starting in 24h" :value="$upcomingSoon" icon="clock" tone="blue" /><x-stat-card label="Waitlisted" :value="$waitlistCount" icon="hourglass-split" tone="orange" /><x-stat-card label="Certificates pending" :value="$pendingCertificates" icon="patch-check" tone="purple" /><x-stat-card label="Missing video" :value="$missingVideo" icon="camera-video-off" tone="red" /></div></section>
@php
    $chartLabels = $chart->pluck('label')->all();
    $chartRegistrations = $chart->pluck('registrations')->all();
    $chartAttendees = $chart->pluck('attendees')->all();
@endphp
<div class="dashboard-main-grid mt-4">
    <section class="panel-card pro-chart-card">
        <div class="pro-chart-head">
            <div>
                <span class="pro-chart-kicker"><i class="bi bi-activity"></i> AUDIENCE ANALYTICS</span>
                <h3>Growth & attendance</h3>
                <p>Last 7 days performance</p>
            </div>
            <div class="pro-chart-totals">
                <span><i class="blue"></i><small>Registrations</small><strong>{{ $chart->sum('registrations') }}</strong></span>
                <span><i class="green"></i><small>Attendees</small><strong>{{ $chart->sum('attendees') }}</strong></span>
            </div>
        </div>
        <div class="chart-canvas-wrap" style="position: relative; height: 260px; width: 100%; padding-top: 10px;">
            <canvas id="dashboardAudienceChart"></canvas>
        </div>
    </section>
    <aside class="panel-card">
        <div class="panel-title">
            <h3>Engagement summary</h3>
        </div>
        <div style="position: relative; height: 110px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
            <canvas id="dashboardDonutChart" style="max-height: 105px;"></canvas>
        </div>
        <div class="engagement-list">
            <div><span>Registrations</span><strong>{{ $totalRegistrations }}</strong></div>
            <div><span>Chat messages</span><strong>{{ $chatMessages }}</strong></div>
            <div><span>Private comments</span><strong>{{ $commentsCount }}</strong></div>
            <div><span>Total watch time</span><strong>{{ number_format($watchSeconds/3600,1) }} hrs</strong></div>
            <div><span>Attendance rate</span><strong>{{ $totalRegistrations?round(($totalAttendees/$totalRegistrations)*100):0 }}%</strong></div>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) return;

    const ctx = document.getElementById('dashboardAudienceChart')?.getContext('2d');
    if (ctx) {
        const gradPurple = ctx.createLinearGradient(0, 0, 0, 240);
        gradPurple.addColorStop(0, 'rgba(124, 58, 237, 0.40)');
        gradPurple.addColorStop(1, 'rgba(124, 58, 237, 0.01)');

        const gradTeal = ctx.createLinearGradient(0, 0, 0, 240);
        gradTeal.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        gradTeal.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

        new window.Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Registrations',
                        data: @json($chartRegistrations),
                        borderColor: '#7c3aed',
                        backgroundColor: gradPurple,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.42,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#7c3aed',
                        pointBorderWidth: 2.5,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#7c3aed',
                        pointHoverBorderColor: '#ffffff'
                    },
                    {
                        label: 'Attendees',
                        data: @json($chartAttendees),
                        borderColor: '#10b981',
                        backgroundColor: gradTeal,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.42,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2.5,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#10b981',
                        pointHoverBorderColor: '#ffffff'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 10,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 12, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', borderDash: [4, 4] },
                        ticks: { precision: 0, color: '#94a3b8', font: { size: 11 } }
                    }
                }
            }
        });
    }

    const donutCtx = document.getElementById('dashboardDonutChart')?.getContext('2d');
    if (donutCtx) {
        const total = {{ $totalRegistrations }};
        const attended = {{ $totalAttendees }};
        const remaining = Math.max(0, total - attended);
        new window.Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Attended', 'Not attended yet'],
                datasets: [{
                    data: total > 0 ? [attended, remaining] : [1, 0],
                    backgroundColor: total > 0 ? ['#7c3aed', '#e2e8f0'] : ['#e2e8f0', '#f8fafc'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: total > 0, backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 }
                }
            }
        });
    }
});
</script>
<div class="dashboard-bottom-grid mt-4">
    <section class="panel-card">
        <div class="panel-title">
            <div><h3>Webinar performance</h3><p>Registration and engagement per event</p></div>
            @if($subadmin)
                <a href="{{ route('admin.chats.index') }}">Open assigned chats</a>
            @else
                <a href="{{ route('admin.webinars.index') }}">View all</a>
            @endif
        </div>
        <div class="table-responsive"><table class="premium-table"><thead><tr><th>Webinar</th><th>Registered</th><th>Attended</th><th>Polls</th><th>Votes</th><th>Status</th></tr></thead><tbody>@forelse($eventPerformance as $row)<tr><td><strong>{{ $row['webinar']->title }}</strong></td><td>{{ $row['webinar']->registrations_count }}</td><td>{{ $row['attendees'] }}</td><td>{{ $row['webinar']->polls_count }}</td><td>{{ $row['votes'] }}</td><td><span class="status-badge {{ $row['webinar']->status }}">{{ ucfirst($row['webinar']->status) }}</span></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No webinars found.</td></tr>@endforelse</tbody></table></div>
    </section>
    <aside class="panel-card">
        <div class="panel-title">
            <div><h3>Recent registrations</h3><p>Latest learners</p></div>
            @unless($subadmin)
                <a href="{{ route('admin.registrations') }}">View users</a>
            @endunless
        </div>
        <div class="recent-registration-list">@forelse($recentRegistrations as $registration)<div><span class="avatar">{{ Str::of($registration->user?->name??$registration->email)->substr(0,2)->upper() }}</span><span><strong>{{ $registration->user?->name??$registration->email }}</strong><small>{{ $registration->webinar?->title }} · {{ $registration->registered_at?->diffForHumans() }}</small></span></div>@empty<p class="text-muted">No registrations yet.</p>@endforelse</div>
    </aside>
</div>
@endsection
