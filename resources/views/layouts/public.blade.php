@extends('layouts.app')
@section('shell')
    <header class="public-header sticky-top">
        <nav class="navbar navbar-expand-lg">
            <div class="container py-2"><x-site-brand /><button class="navbar-toggler border-0" data-bs-toggle="collapse"
                    data-bs-target="#publicNav"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="publicNav">
                    <ul class="navbar-nav mx-auto gap-lg-3">
                        <li><a class="nav-link" href="/">Home</a></li>
                    </ul>
                    <div class="d-flex gap-2">@guest<button type="button" class="btn btn-ghost" data-bs-toggle="modal"
                                data-bs-target="#micrositeLoginModal">Log in</button><button type="button"
                                class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Get
                            started</button>@else<a class="btn btn-gradient"
                            href="{{ route('dashboard') }}">Dashboard</a>@endguest
                    </div>
                </div>
            </div>
        </nav>
    </header>
    @php($authNotice = session('auth_status') ?: session('registration_status'))
    @php($targetWebinar = isset($webinar) && $webinar instanceof \App\Models\Webinar ? $webinar : null)
    @php($isRoomOpen = !$targetWebinar || $targetWebinar->canEnter())
    @php($modalOpensAtUtc = $targetWebinar?->opensAt()?->toIso8601String() ?: session('room_opens_at_utc'))
    @php($modalEventTz = $targetWebinar?->timezone ?: session('room_timezone'))
    @php($modalOpensAtFormatted = $targetWebinar && $targetWebinar->opensAt() ? $targetWebinar->opensAt()->timezone($modalEventTz)->format('M d, Y · g:i A') : session('room_opens_at'))
    @if (session('auth_redirect') && $isRoomOpen)
        <div class="modal fade show" id="authRedirectModal" tabindex="-1"
            style="display: block; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 5000;"
            aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-2xl"
                    style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                    <div class="modal-body p-4 p-md-5 text-center">
                        <div
                            style="width: 60px; height: 60px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: grid; place-items: center; margin: 0 auto 18px; font-size: 1.85rem; box-shadow: 0 10px 25px rgba(22, 163, 74, 0.2);">
                            <i class="bi bi-check2"></i>
                        </div>
                        <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                            {{ session('auth_status') ?: (session('registration_status') ?: 'Login successfully.') }}
                        </h3>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 22px;">
                            Redirecting to your webinar dashboard...
                        </p>
                        <div
                            style="height: 5px; width: 100%; background: #f1f5f9; border-radius: 999px; overflow: hidden; position: relative;">
                            <div
                                style="height: 100%; width: 0%; background: linear-gradient(90deg, #7c3aed, #2563eb); border-radius: 999px; animation: authModalProgress 1.4s ease-in-out forwards;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes authModalProgress {
                0% {
                    width: 0%;
                }

                50% {
                    width: 70%;
                }

                100% {
                    width: 100%;
                }
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.location.assign(@json(session('auth_redirect')));
                }, 1400);
            });
        </script>
    @elseif((session('auth_redirect') && !$isRoomOpen) || session('room_opens_at'))
        <div class="room-opening-backdrop" role="dialog" aria-modal="true" aria-labelledby="roomOpeningTitle"
            data-room-opening-modal>
            <div class="room-opening-card">
                <div class="room-opening-glow"></div>
                <div class="room-clock-mark" aria-hidden="true">
                    <svg viewBox="0 0 64 64" role="img">
                        <circle cx="32" cy="32" r="23"></circle>
                        <path d="M32 19v14l10 6"></path>
                        <path class="room-clock-spark" d="M49 11l2-4m5 11l4-2M15 11l-2-4M8 18l-4-2"></path>
                    </svg>
                </div>
                <span class="room-opening-kicker">YOU'RE REGISTERED</span>
                <h3 id="roomOpeningTitle">
                    {{ session('auth_status') ?: (session('registration_status') ?: 'Registration confirmed') }}</h3>
                <p class="room-opening-copy">Your seat is saved. The interactive webinar room will unlock automatically at
                    the scheduled time.</p>
                <div class="room-opening-timer" data-room-countdown data-utc="{{ $modalOpensAtUtc }}"
                    aria-label="Time remaining until the room opens">
                    @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Minutes', 'seconds' => 'Seconds'] as $unit => $label)
                        <span><b data-room-countdown-{{ $unit }}>00</b><small>{{ $label }}</small></span>
                    @endforeach
                </div>
                <p class="room-opening-time" data-room-time-display data-utc="{{ $modalOpensAtUtc }}"
                    data-event-tz="{{ $modalEventTz }}" data-event-formatted="{{ $modalOpensAtFormatted }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="3"></rect>
                        <path d="M8 3v4m8-4v4M3 10h18"></path>
                    </svg>
                    <span class="room-time-text">Room opens at {{ $modalOpensAtFormatted }} ({{ $modalEventTz }})</span>
                </p>
                <button type="button" class="room-opening-button"
                    onclick="this.closest('[data-room-opening-modal]').remove()">Got it <span
                        aria-hidden="true">→</span></button>
            </div>
        </div>
        <style>
            .room-opening-backdrop {
                position: fixed;
                inset: 0;
                z-index: 5000;
                display: grid;
                place-items: center;
                padding: 20px;
                background: rgba(7, 12, 24, .78);
                backdrop-filter: blur(14px)
            }

            .room-opening-card {
                position: relative;
                width: min(520px, 100%);
                overflow: hidden;
                padding: 38px;
                text-align: center;
                border: 1px solid rgba(255, 255, 255, .72);
                border-radius: 28px;
                background: linear-gradient(150deg, #fff 0%, #fbfaff 62%, #f0f5ff 100%);
                box-shadow: 0 35px 100px rgba(4, 10, 25, .42);
                font-family: 'DM Sans', sans-serif
            }

            .room-opening-glow {
                position: absolute;
                top: -130px;
                right: -90px;
                width: 270px;
                height: 270px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(124, 58, 237, .2), transparent 68%);
                pointer-events: none
            }

            .room-clock-mark {
                position: relative;
                width: 82px;
                height: 82px;
                margin: 0 auto 18px;
                display: grid;
                place-items: center;
                border: 1px solid #ddd6fe;
                border-radius: 25px;
                background: linear-gradient(145deg, #f5f3ff, #eef2ff);
                color: #6d28d9;
                box-shadow: 0 14px 34px rgba(109, 40, 217, .17);
                transform: rotate(-3deg)
            }

            .room-clock-mark svg {
                width: 55px;
                height: 55px;
                overflow: visible;
                fill: none;
                stroke: currentColor;
                stroke-width: 3;
                stroke-linecap: round;
                stroke-linejoin: round
            }

            .room-clock-mark circle {
                fill: #fff;
                stroke-width: 2.5
            }

            .room-clock-mark path:not(.room-clock-spark) {
                transform-origin: 32px 32px;
                animation: roomClockTick 8s linear infinite
            }

            .room-clock-spark {
                stroke: #2563eb;
                stroke-width: 2.5
            }

            .room-opening-kicker {
                display: inline-flex;
                padding: 6px 10px;
                border-radius: 999px;
                background: #ede9fe;
                color: #6d28d9;
                font-size: .64rem;
                font-weight: 800;
                letter-spacing: .14em
            }

            .room-opening-card h3 {
                margin: 14px 0 8px;
                color: #111827;
                font-family: 'Manrope', sans-serif;
                font-size: clamp(1.35rem, 4vw, 1.75rem);
                font-weight: 800;
                letter-spacing: -.035em
            }

            .room-opening-copy {
                max-width: 410px;
                margin: 0 auto 22px;
                color: #64748b;
                font-size: .88rem;
                line-height: 1.65
            }

            .room-opening-timer {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 9px;
                margin-bottom: 18px
            }

            .room-opening-timer>span {
                padding: 12px 5px;
                border: 1px solid #e8e5f1;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 7px 22px rgba(15, 23, 42, .05)
            }

            .room-opening-timer b {
                display: block;
                color: #1e293b;
                font-family: 'Space Grotesk', sans-serif;
                font-size: 1.35rem;
                line-height: 1
            }

            .room-opening-timer small {
                display: block;
                margin-top: 6px;
                color: #94a3b8;
                font-size: .56rem;
                font-weight: 800;
                letter-spacing: .08em;
                text-transform: uppercase
            }

            .room-opening-time {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin: 0 0 22px;
                color: #475569;
                font-size: .78rem;
                line-height: 1.45
            }

            .room-opening-time svg {
                width: 18px;
                height: 18px;
                flex: none;
                fill: none;
                stroke: #7c3aed;
                stroke-width: 1.8;
                stroke-linecap: round
            }

            .room-opening-button {
                width: 100%;
                min-height: 48px;
                border: 0;
                border-radius: 14px;
                background: linear-gradient(135deg, #6d28d9, #2563eb);
                color: #fff;
                font-size: .86rem;
                font-weight: 800;
                box-shadow: 0 13px 28px rgba(79, 70, 229, .25);
                transition: .2s
            }

            .room-opening-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 17px 34px rgba(79, 70, 229, .32)
            }

            .room-opening-button span {
                display: inline-block;
                margin-left: 6px;
                transition: .2s
            }

            .room-opening-button:hover span {
                transform: translateX(3px)
            }

            @keyframes roomClockTick {
                to {
                    transform: rotate(360deg)
                }
            }

            @media(max-width:520px) {
                .room-opening-card {
                    padding: 28px 20px;
                    border-radius: 23px
                }

                .room-clock-mark {
                    width: 70px;
                    height: 70px
                }

                .room-opening-timer {
                    gap: 6px
                }

                .room-opening-timer>span {
                    padding: 10px 2px
                }

                .room-opening-timer b {
                    font-size: 1.1rem
                }
            }

            @media(prefers-reduced-motion:reduce) {
                .room-clock-mark path:not(.room-clock-spark) {
                    animation: none
                }

                .room-opening-button {
                    transition: none
                }
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                function getFriendlyTzLabel(date) {
                    try {
                        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                        if (tz === 'Asia/Kolkata' || tz === 'Asia/Calcutta' || tz === 'IST') return 'IST';
                        if (-date.getTimezoneOffset() === 330) return 'IST';
                        const parts = new Intl.DateTimeFormat('en-US', {
                            timeZoneName: 'short'
                        }).formatToParts(date);
                        const tzPart = parts.find(p => p.type === 'timeZoneName');
                        let label = tzPart ? tzPart.value : (tz || '');
                        if (label === 'GMT+5:30' || label === 'GMT+05:30' || label === 'UTC+5:30' || label ===
                            'UTC+05:30') return 'IST';
                        return label;
                    } catch (e) {
                        return 'IST';
                    }
                }

                document.querySelectorAll('[data-room-time-display]').forEach(el => {
                    const utcStr = el.dataset.utc;
                    const eventTz = el.dataset.eventTz;
                    const eventFormatted = el.dataset.eventFormatted;
                    if (!utcStr) return;
                    try {
                        const utcDate = new Date(utcStr);
                        if (isNaN(utcDate.getTime())) return;
                        const userTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                        const tzLabel = getFriendlyTzLabel(utcDate);
                        const dateFormatted = utcDate.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        const timeFormatted = utcDate.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                        const userFormatted = dateFormatted + ' · ' + timeFormatted + ' ' + tzLabel;
                        const textSpan = el.querySelector('.room-time-text') || el;
                        if (eventTz && (userTz !== eventTz || tzLabel !== eventTz)) {
                            textSpan.textContent = 'Room opens at ' + userFormatted;
                        } else {
                            textSpan.textContent = 'Room opens at ' + eventFormatted + ' (' + eventTz + ')';
                        }
                    } catch (e) {}
                });

                document.querySelectorAll('[data-room-countdown]').forEach(countdown => {
                    const target = new Date(countdown.dataset.utc).getTime();
                    if (!Number.isFinite(target)) return;
                    const render = () => {
                        const remaining = Math.max(0, target - Date.now());
                        const values = {
                            days: Math.floor(remaining / 86400000),
                            hours: Math.floor(remaining / 3600000) % 24,
                            minutes: Math.floor(remaining / 60000) % 60,
                            seconds: Math.floor(remaining / 1000) % 60,
                        };
                        Object.entries(values).forEach(([unit, value]) => {
                            const output = countdown.querySelector(`[data-room-countdown-${unit}]`);
                            if (output) output.textContent = String(value).padStart(2, '0');
                        });
                        return remaining > 0;
                    };
                    render();
                    const interval = window.setInterval(() => {
                        if (!render()) window.clearInterval(interval);
                    }, 1000);
                });
            });
        </script>
    @elseif($authNotice)
        <div class="auth-redirect-notice" role="status" aria-live="polite"><span class="auth-redirect-icon"><i
                    class="bi bi-check2"></i></span>
            <div class="auth-redirect-content"><strong>{{ $authNotice }}</strong></div>
        </div>
        <style>
            .auth-redirect-notice {
                position: fixed;
                z-index: 4000;
                top: 90px;
                right: 24px;
                max-width: min(420px, calc(100vw - 32px));
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 16px 20px 20px;
                border: 1px solid #10b98133;
                border-radius: 14px;
                background: #fff;
                color: #172033;
                box-shadow: 0 20px 50px rgba(15, 23, 42, .15);
                overflow: hidden
            }

            .auth-redirect-icon {
                width: 38px;
                height: 38px;
                display: grid;
                place-items: center;
                flex: none;
                border-radius: 10px;
                background: #ecfdf5;
                color: #059669;
                font-size: 1.25rem
            }

            .auth-redirect-content strong {
                font-size: .95rem;
                font-weight: 600;
                color: #0f172a
            }

            @media(max-width:600px) {
                .auth-redirect-notice {
                    top: 74px;
                    right: 16px;
                    left: 16px
                }
            }
        </style>
    @endif
    <main>@yield('content')</main>
    @hasSection('footer')
        @yield('footer')
    @endif
    @hasSection('auth-modals')
        @yield('auth-modals')
    @else
        @guest
            @include('components.frontend-auth', ['authWebinar' => null])
        @endguest
    @endif
@endsection
