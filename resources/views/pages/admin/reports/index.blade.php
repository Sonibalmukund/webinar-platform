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
    <section class="panel-card report-chart-card" style="padding: 24px;">
        <div class="panel-title d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 style="font-size:1.15rem; font-weight:800; margin:0; color:#1e293b;">Audience momentum</h3>
                <p style="font-size:0.75rem; color:#64748b; margin:4px 0 0;">Last 14 days registration vs attendance trend</p>
            </div>
            <div class="d-flex align-items-center gap-3" style="font-size:0.75rem; font-weight:700;">
                <span class="d-inline-flex align-items-center gap-1"><span style="width:10px; height:10px; border-radius:50%; background:#ee1f2d; display:inline-block;"></span> <span style="color:#b91522;">Registrations</span></span>
                <span class="d-inline-flex align-items-center gap-1"><span style="width:10px; height:10px; border-radius:50%; background:#10b981; display:inline-block;"></span> <span style="color:#059669;">Attendees</span></span>
            </div>
        </div>
        <div style="position:relative; height:265px; width:100%;">
            <canvas id="reportAudienceChart"></canvas>
        </div>
    </section>
    <aside class="panel-card report-conversion-card" style="padding: 24px;">
        <div class="panel-title d-flex justify-content-between align-items-center mb-2">
            <div>
                <h3 style="font-size:1.05rem; font-weight:800; margin:0; color:#1e293b;">Conversion pulse</h3>
                <p style="font-size:0.72rem; color:#64748b; margin:3px 0 0;">Audience journey</p>
            </div>
            <span class="status-badge active" style="font-size:0.65rem; font-weight:800; padding:4px 10px; border-radius:999px; background:#dcfce7; color:#15803d;"><i class="bi bi-broadcast me-1"></i>LIVE DATA</span>
        </div>
        <div style="position:relative; height:160px; width:100%; margin: 8px auto;">
            <canvas id="reportConversionDonut"></canvas>
            <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                <strong style="font-size:1.6rem; font-weight:800; color:#1e293b; line-height:1;">{{ $attendanceRate }}%</strong>
                <small style="font-size:0.68rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-top:3px;">Attendance</small>
            </div>
        </div>
        <div class="report-funnel mt-2">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 13px; background:#f8fafc; border-radius:10px; font-size:0.75rem; border:1px solid #f1f5f9;">
                <span style="color:#475569; font-weight:600;"><i class="bi bi-person-plus text-primary me-1"></i> Registered</span>
                <strong style="font-weight:800; color:#1e293b;">{{ number_format($registrationCount) }}</strong>
            </div>
            <div style="text-align:center; color:#cbd5e1; font-size:0.75rem; margin:-3px 0;"><i class="bi bi-chevron-down"></i></div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 13px; background:#ecfdf5; border-radius:10px; font-size:0.75rem; border:1px solid #d1fae5;">
                <span style="color:#065f46; font-weight:600;"><i class="bi bi-person-check-fill text-success me-1"></i> Attended</span>
                <strong style="font-weight:800; color:#065f46;">{{ number_format($attendeeCount) }}</strong>
            </div>
            <div style="text-align:center; color:#cbd5e1; font-size:0.75rem; margin:-3px 0;"><i class="bi bi-chevron-down"></i></div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 13px; background:#faf5ff; border-radius:10px; font-size:0.75rem; border:1px solid #f3e8ff;">
                <span style="color:#6b21a8; font-weight:600;"><i class="bi bi-ui-checks-grid text-purple me-1"></i> Unique voters</span>
                <strong style="font-weight:800; color:#6b21a8;">{{ number_format($uniqueVoters) }}</strong>
            </div>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) return;

    const reportAudienceCtx = document.getElementById('reportAudienceChart')?.getContext('2d');
    if (reportAudienceCtx) {
        const gradPurple = reportAudienceCtx.createLinearGradient(0, 0, 0, 240);
        gradPurple.addColorStop(0, 'rgba(238, 31, 45, 0.28)');
        gradPurple.addColorStop(1, 'rgba(238, 31, 45, 0.00)');

        const gradTeal = reportAudienceCtx.createLinearGradient(0, 0, 0, 240);
        gradTeal.addColorStop(0, 'rgba(16, 185, 129, 0.24)');
        gradTeal.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        new window.Chart(reportAudienceCtx, {
            type: 'line',
            data: {
                labels: @json($trend->pluck('label')),
                datasets: [
                    {
                        label: 'Registrations',
                        data: @json($trend->pluck('registrations')),
                        borderColor: '#ee1f2d',
                        backgroundColor: gradPurple,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#ee1f2d',
                        pointBorderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#ee1f2d',
                        pointHoverBorderColor: '#ffffff'
                    },
                    {
                        label: 'Attendees',
                        data: @json($trend->pluck('attendees')),
                        borderColor: '#10b981',
                        backgroundColor: gradTeal,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2.5,
                        pointRadius: 4,
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
                        ticks: { color: '#64748b', font: { size: 11, weight: '600' } }
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

    const reportDonutCtx = document.getElementById('reportConversionDonut')?.getContext('2d');
    if (reportDonutCtx) {
        const total = {{ $registrationCount }};
        const attended = {{ $attendeeCount }};
        const voters = {{ $uniqueVoters }};
        const notAttended = Math.max(0, total - attended);

        new window.Chart(reportDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Attended', 'Not Attended', 'Unique Voters'],
                datasets: [{
                    data: total > 0 ? [attended, notAttended, voters] : [1, 0, 0],
                    backgroundColor: total > 0 ? ['#10b981', '#e2e8f0', '#ee1f2d'] : ['#e2e8f0', '#f8fafc', '#f1f5f9'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    }
});
</script>

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
