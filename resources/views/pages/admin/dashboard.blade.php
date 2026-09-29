@extends('layouts.portal')
@section('title', 'Admin Dashboard')
@section('content')
    <style>
        .dashboard-command {
            position: relative;
            isolation: isolate;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            overflow: hidden;
            padding: 26px 28px;
            border: 1px solid #1e293b;
            border-radius: 8px 26px 8px 26px;
            color: #fff;
            background: linear-gradient(118deg, #091222 0%, #14213a 62%, #30131b 100%);
            box-shadow: 0 18px 42px rgba(9, 18, 34, .16)
        }

        .dashboard-command:before {
            content: '';
            position: absolute;
            z-index: -1;
            right: -70px;
            top: -110px;
            width: 310px;
            height: 310px;
            border: 70px solid rgba(238, 31, 45, .12);
            border-radius: 50%
        }

        .dashboard-command-copy {
            min-width: 0
        }

        .dashboard-command-copy small {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: #fda4af;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase
        }

        .dashboard-command-copy h1 {
            margin: 0;
            color: #fff;
            font-size: clamp(1.55rem, 2.8vw, 2.2rem);
            letter-spacing: -.035em
        }

        .dashboard-command-copy p {
            margin: 7px 0 0;
            color: #aebacd;
            font-size: .82rem
        }

        .dashboard-command-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: none
        }

        .dashboard-command-actions .btn {
            white-space: nowrap
        }

        .dashboard-command-actions .btn-light {
            color: #172033;
            background: #fff
        }

        .dashboard-command-actions .btn-outline-light {
            border-color: #ffffff42;
            color: #fff
        }

        .dashboard-command-actions .btn-outline-light:hover {
            color: #111827;
            background: #fff
        }

        .dashboard-primary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px
        }

        .dashboard-primary-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            padding: 18px;
            border: 1px solid #dfe5ee;
            border-radius: 6px 20px 6px 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(9, 18, 34, .05);
            transition: transform .18s ease, box-shadow .18s ease
        }

        .dashboard-primary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(9, 18, 34, .09)
        }

        .dashboard-primary-icon {
            display: grid;
            place-items: center;
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            border-radius: 5px 15px 5px 15px;
            color: #d71927;
            background: #fff0f1;
            font-size: 1.15rem
        }

        .dashboard-primary-card.is-live .dashboard-primary-icon {
            color: #047857;
            background: #ecfdf5
        }

        .dashboard-primary-copy {
            display: flex;
            min-width: 0;
            flex-direction: column
        }

        .dashboard-primary-copy small {
            overflow: hidden;
            color: #718096;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap
        }

        .dashboard-primary-copy strong {
            margin-top: 3px;
            color: #091222;
            font: 800 1.55rem/1 'Manrope', sans-serif
        }

        .dashboard-primary-copy span {
            margin-top: 6px;
            color: #94a3b8;
            font-size: .65rem
        }

        .dashboard-live-dot {
            position: absolute;
            right: 16px;
            top: 16px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 5px #d1fae5
        }

        .dashboard-insight-strip {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            margin-top: 14px;
            overflow: hidden;
            border: 1px solid #dfe5ee;
            border-radius: 5px 18px 5px 18px;
            background: #fff
        }

        .dashboard-insight {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 13px 15px;
            border-right: 1px solid #edf0f4
        }

        .dashboard-insight:last-child {
            border-right: 0
        }

        .dashboard-insight i {
            color: #ee1f2d;
            font-size: .9rem
        }

        .dashboard-insight span {
            display: flex;
            min-width: 0;
            flex-direction: column
        }

        .dashboard-insight strong {
            color: #172033;
            font-size: .86rem
        }

        .dashboard-insight small {
            overflow: hidden;
            color: #8490a3;
            font-size: .62rem;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .dashboard-section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px
        }

        .dashboard-section-heading h2 {
            margin: 0;
            color: #172033;
            font-size: 1rem
        }

        .dashboard-section-heading p {
            margin: 4px 0 0;
            color: #8490a3;
            font-size: .72rem
        }

        .dashboard-section-heading>span {
            color: #94a3b8;
            font-size: .65rem;
            font-weight: 700
        }

        .dashboard-attention {
            margin-top: 24px
        }

        .dashboard-attention-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px
        }

        .attention-card {
            --attention: #2563eb;
            --attention-bg: #eff6ff;
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
            padding: 15px 16px;
            border: 1px solid #e1e6ee;
            border-left: 3px solid var(--attention);
            border-radius: 4px 15px 4px 15px;
            background: #fff
        }

        .attention-card.warning {
            --attention: #d97706;
            --attention-bg: #fffbeb
        }

        .attention-card.danger {
            --attention: #dc2626;
            --attention-bg: #fff1f2
        }

        .attention-card.purple {
            --attention: #7c3aed;
            --attention-bg: #f5f3ff
        }

        .attention-card>i {
            display: grid;
            place-items: center;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            border-radius: 4px 12px 4px 12px;
            color: var(--attention);
            background: var(--attention-bg)
        }

        .attention-card span {
            display: flex;
            min-width: 0;
            flex-direction: column
        }

        .attention-card strong {
            color: #172033;
            font-size: 1rem
        }

        .attention-card small {
            overflow: hidden;
            color: #7b8799;
            font-size: .65rem;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        @media(max-width:1100px) {

            .dashboard-primary-grid,
            .dashboard-attention-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .dashboard-insight-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr))
            }

            .dashboard-insight:nth-child(3) {
                border-right: 0
            }

            .dashboard-insight:nth-child(-n+3) {
                border-bottom: 1px solid #edf0f4
            }
        }

        @media(max-width:700px) {
            .dashboard-command {
                align-items: flex-start;
                flex-direction: column;
                padding: 22px
            }

            .dashboard-command-actions {
                width: 100%;
                flex-wrap: wrap
            }

            .dashboard-command-actions .btn {
                flex: 1
            }

            .dashboard-primary-grid,
            .dashboard-attention-grid {
                grid-template-columns: 1fr
            }

            .dashboard-insight-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .dashboard-insight:nth-child(3) {
                border-right: 1px solid #edf0f4
            }

            .dashboard-insight:nth-child(even) {
                border-right: 0
            }

            .dashboard-insight:nth-child(-n+4) {
                border-bottom: 1px solid #edf0f4
            }
        }
    </style>

    <section class="dashboard-command">
        <div class="dashboard-command-copy">
            <small><i class="bi bi-grid-1x2-fill"></i> Webinar control center</small>
            <h1>Dashboard</h1>
            <p>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                {{ Str::before(auth()->user()->name, ' ') }} · {{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="dashboard-command-actions">
            @if (!$subadmin || auth()->user()->hasPermission('webinars.view'))
                <a href="{{ route('admin.webinars.index') }}" class="btn btn-outline-light"><i
                        class="bi bi-calendar3 me-1"></i> Manage webinars</a>
            @endif
            @if (!$subadmin || auth()->user()->hasPermission('webinars.create'))
                <a href="{{ route('admin.webinars.create') }}" class="btn btn-light"><i class="bi bi-plus-lg me-1"></i> Create
                    webinar</a>
            @endif
        </div>
    </section>

    <div class="dashboard-primary-grid">
        <article class="dashboard-primary-card">
            <span class="dashboard-primary-icon"><i class="bi bi-calendar-event"></i></span>
            <span class="dashboard-primary-copy"><small>Total
                    events</small><strong>{{ $webinars->count() }}</strong><span>Across all statuses</span></span>
        </article>
        <article class="dashboard-primary-card">
            <span class="dashboard-primary-icon"><i class="bi bi-people"></i></span>
            <span
                class="dashboard-primary-copy"><small>Registrations</small><strong>{{ number_format($registeredUsers) }}</strong><span>+{{ number_format($todayRegistrations) }}
                    added today</span></span>
        </article>
        <article class="dashboard-primary-card">
            <span class="dashboard-primary-icon"><i class="bi bi-person-check"></i></span>
            <span class="dashboard-primary-copy"><small>Total
                    attendees</small><strong>{{ number_format($totalAttendees) }}</strong><span>{{ $totalRegistrations ? round(($totalAttendees / $totalRegistrations) * 100) : 0 }}%
                    attendance rate</span></span>
        </article>
        <article class="dashboard-primary-card is-live">
            <i class="dashboard-live-dot"></i>
            <span class="dashboard-primary-icon"><i class="bi bi-broadcast"></i></span>
            <span class="dashboard-primary-copy"><small>Live
                    now</small><strong>{{ number_format($liveNow) }}</strong><span>Active viewers</span></span>
        </article>
    </div>

    <div class="dashboard-insight-strip" aria-label="Engagement overview">
        <div class="dashboard-insight"><i
                class="bi bi-person-plus"></i><span><strong>{{ number_format($todayRegistrations) }}</strong><small>New
                    today</small></span></div>
        <div class="dashboard-insight"><i
                class="bi bi-chat-dots"></i><span><strong>{{ number_format($chatMessages) }}</strong><small>Chat
                    messages</small></span></div>
        <div class="dashboard-insight"><i
                class="bi bi-chat-square-text"></i><span><strong>{{ number_format($commentsCount) }}</strong><small>Private
                    comments</small></span></div>
        <div class="dashboard-insight"><i
                class="bi bi-bar-chart"></i><span><strong>{{ number_format($pollsCount) }}</strong><small>Polls
                    created</small></span></div>
        <div class="dashboard-insight"><i
                class="bi bi-check2-square"></i><span><strong>{{ number_format($pollVoters) }}</strong><small>Unique
                    voters</small></span></div>
        <div class="dashboard-insight"><i
                class="bi bi-ui-checks-grid"></i><span><strong>{{ number_format($votesCount) }}</strong><small>Votes
                    submitted</small></span></div>
    </div>

    <section class="dashboard-attention">
        <div class="dashboard-section-heading">
            <div>
                <h2>Needs your attention</h2>
                <p>Items that may affect upcoming sessions or attendee access.</p>
            </div>
            <span>Updated live</span>
        </div>
        <div class="dashboard-attention-grid">
            <article class="attention-card"><i
                    class="bi bi-clock"></i><span><strong>{{ $upcomingSoon }}</strong><small>Starting in the next 24
                        hours</small></span></article>
            <article class="attention-card warning"><i
                    class="bi bi-hourglass-split"></i><span><strong>{{ $waitlistCount }}</strong><small>People waiting for
                        approval</small></span></article>
            <article class="attention-card purple"><i
                    class="bi bi-patch-check"></i><span><strong>{{ $pendingCertificates }}</strong><small>Certificates
                        pending issue</small></span></article>
            <article class="attention-card danger"><i
                    class="bi bi-camera-video-off"></i><span><strong>{{ $missingVideo }}</strong><small>Webinars missing a
                        video source</small></span></article>
        </div>
    </section>
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
                    <span><i
                            class="blue"></i><small>Registrations</small><strong>{{ $chart->sum('registrations') }}</strong></span>
                    <span><i
                            class="green"></i><small>Attendees</small><strong>{{ $chart->sum('attendees') }}</strong></span>
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
            <div
                style="position: relative; height: 110px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
                <canvas id="dashboardDonutChart" style="max-height: 105px;"></canvas>
            </div>
            <div class="engagement-list">
                <div><span>Registrations</span><strong>{{ $totalRegistrations }}</strong></div>
                <div><span>Chat messages</span><strong>{{ $chatMessages }}</strong></div>
                <div><span>Private comments</span><strong>{{ $commentsCount }}</strong></div>
                <div><span>Total watch time</span><strong>{{ number_format($watchSeconds / 3600, 1) }} hrs</strong></div>
                <div><span>Attendance
                        rate</span><strong>{{ $totalRegistrations ? round(($totalAttendees / $totalRegistrations) * 100) : 0 }}%</strong>
                </div>
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
                        datasets: [{
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
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
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
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f1f5f9',
                                    borderDash: [4, 4]
                                },
                                ticks: {
                                    precision: 0,
                                    color: '#94a3b8',
                                    font: {
                                        size: 11
                                    }
                                }
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
                            backgroundColor: total > 0 ? ['#7c3aed', '#e2e8f0'] : ['#e2e8f0',
                                '#f8fafc'
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                display: false
                            },
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
    <div class="dashboard-bottom-grid mt-4">
        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Webinar performance</h3>
                    <p>Registration and engagement per event</p>
                </div>
                @if ($subadmin)
                    <a href="{{ route('admin.chats.index') }}">Open assigned chats</a>
                @else
                    <a href="{{ route('admin.webinars.index') }}">View all</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Webinar</th>
                            <th>Registered</th>
                            <th>Attended</th>
                            <th>Polls</th>
                            <th>Votes</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventPerformance as $row)
                            <tr>
                                <td><strong>{{ $row['webinar']->title }}</strong></td>
                                <td>{{ $row['webinar']->registrations_count }}</td>
                                <td>{{ $row['attendees'] }}</td>
                                <td>{{ $row['webinar']->polls_count }}</td>
                                <td>{{ $row['votes'] }}</td>
                                <td><span
                                        class="status-badge {{ $row['webinar']->status }}">{{ ucfirst($row['webinar']->status) }}</span>
                                </td>
                        </tr>@empty<tr>
                                <td colspan="6" class="text-center text-muted">No webinars found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <aside class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Recent registrations</h3>
                    <p>Latest registered attendees</p>
                </div>
                @if (
                    !$subadmin ||
                        auth()->user()->hasAnyPermission(['registrations.view', 'attendance.view']))
                    <a href="{{ route('admin.users') }}">View all</a>
                @endif
            </div>
            <div class="recent-registration-list">
                @forelse($recentRegistrations as $registration)
                    <div><span
                            class="avatar">{{ Str::of($registration->user?->name ?? $registration->email)->substr(0, 2)->upper() }}</span><span><strong>{{ $registration->user?->name ?? $registration->email }}</strong><small>{{ $registration->webinar?->title }}
                            · {{ $registration->registered_at?->diffForHumans() }}</small></span></div>@empty<p
                        class="text-muted">No registrations yet.</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection
