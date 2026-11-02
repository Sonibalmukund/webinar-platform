@extends('layouts.portal')
@section('title', 'Live Control · ' . $webinar->title)
@section('content')
    <style>
        .live-control-switch {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 13px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px
        }

        .live-control-switch>span {
            display: grid;
            gap: 2px;
            min-width: 0
        }

        .live-control-switch .form-switch {
            display: flex;
            align-items: center;
            min-height: 0;
            padding-left: 0
        }

        .live-control-switch .form-check-input {
            float: none;
            margin: 0;
            width: 2.75rem;
            height: 1.4rem;
            cursor: pointer;
            border-color: #cbd5e1;
            box-shadow: none
        }

        .live-control-switch .form-check-input:checked {
            background-color: #16a34a;
            border-color: #16a34a
        }

        .live-control-switch .form-check-input:focus {
            border-color: #fb7185;
            box-shadow: 0 0 0 .2rem rgba(255, 45, 68, .12)
        }

        #pinBannerSwitch:checked {
            background-color: #16a34a;
            border-color: #16a34a
        }

        #pinBannerSwitch:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 .2rem rgba(34, 197, 94, .13)
        }

        .control-preview {
            overflow: hidden;
            background: #020617
        }

        .control-preview iframe,
        .control-preview>img {
            width: 100%;
            height: 100%;
            border: 0;
            object-fit: cover
        }

        .control-preview .status-badge {
            z-index: 2;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .24)
        }

        .control-guide {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1px;
            margin-bottom: 18px;
            overflow: hidden;
            border: 1px solid #26344a;
            border-radius: 6px 18px;
            background: #26344a;
            box-shadow: 0 12px 30px rgba(9, 18, 34, .1)
        }

        .control-guide-step {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 15px 17px;
            color: #dbe3ee;
            background: #0b1526
        }

        .control-guide-step>span {
            display: grid;
            place-items: center;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            border-radius: 4px 10px;
            color: #fff;
            background: #ee1f2d;
            font-size: .75rem;
            font-weight: 800
        }

        .control-guide-step strong,
        .control-guide-step small {
            display: block
        }

        .control-guide-step strong {
            color: #fff;
            font-size: .78rem
        }

        .control-guide-step small {
            margin-top: 2px;
            color: #91a0b5;
            font-size: .68rem
        }

        .control-section+.control-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e9f0
        }

        .control-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px
        }

        .control-section-heading i {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 4px 11px;
            color: #ee1f2d;
            background: #fff0f1
        }

        .control-section-heading h4,
        .control-section-heading p {
            margin: 0
        }

        .control-section-heading h4 {
            font-size: .95rem
        }

        .control-section-heading p {
            color: #748096;
            font-size: .72rem
        }

        .control-fields-grid,
        .control-toggle-grid,
        .live-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px
        }

        .control-setting-field {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-width: 0;
            padding: 13px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff
        }

        .control-setting-field span {
            min-width: 0
        }

        .control-setting-field strong,
        .control-setting-field small {
            display: block
        }

        .control-setting-field small {
            margin-top: 3px;
            color: #748096;
            font-size: .68rem
        }

        .control-setting-field .form-select {
            width: 150px;
            flex: 0 0 150px
        }

        .control-setting-field .form-control {
            width: 88px;
            flex: 0 0 88px;
            text-align: center
        }

        .control-toggle-grid .live-control-switch {
            min-height: 72px
        }

        .control-save-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #e5e9f0
        }

        .control-save-bar small {
            color: #748096
        }

        .quick-control-card {
            margin-top: 12px !important
        }

        @media (max-width: 767px) {
            .control-guide,
            .control-fields-grid,
            .control-toggle-grid,
            .live-quick-grid {
                grid-template-columns: 1fr
            }

            .control-guide-step {
                padding: 12px 14px
            }

            .control-save-bar {
                align-items: stretch;
                flex-direction: column
            }

            .control-save-bar .btn {
                width: 100%
            }
        }
    </style>
    <div class="page-heading pt-2">
        <div><span class="eyebrow">LIVE CONTROL</span>
            <h1>{{ $webinar->title }}</h1>
            <p>These controls belong only to this webinar.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a class="btn btn-outline-danger" href="{{ route('webinars.show', $webinar) }}" target="_blank" rel="noopener"><i
                    class="bi bi-window me-1"></i>Preview Landing Page</a>
            <a class="btn btn-outline-danger" href="{{ route('admin.webinars.preview-room', $webinar) }}" target="_blank"
                rel="noopener"><i class="bi bi-play-btn me-1"></i>Preview Live Room</a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.webinars.index') }}"><i
                    class="bi bi-arrow-left me-1"></i>Webinars list</a>
            <a class="btn btn-light" href="{{ route('admin.webinars.show', $webinar) }}"><i
                    class="bi bi-eye me-1"></i>Overview</a>
        </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <div class="control-guide" aria-label="How to use live controls">
        <div class="control-guide-step"><span>1</span><div><strong>Set event access</strong><small>Choose status and room opening rules.</small></div></div>
        <div class="control-guide-step"><span>2</span><div><strong>Choose attendee features</strong><small>Turn chat, polls and feedback on or off.</small></div></div>
        <div class="control-guide-step"><span>3</span><div><strong>Save and verify</strong><small>Save controls, then preview the attendee room.</small></div></div>
    </div>
    <div class="control-grid" data-live-viewers data-webinar-id="{{ $webinar->id }}"
        data-live-viewers-url="{{ route('admin.webinars.live-viewers', $webinar) }}">
        <div class="control-stage">
            <div class="control-preview"><span
                    class="status-badge {{ $webinar->status }}">{{ strtoupper($webinar->status) }}</span>
                @if ($webinar->live_url)
                    <iframe src="{{ $webinar->live_url }}" title="{{ $webinar->title }} live room preview"
                        allow="autoplay; fullscreen; picture-in-picture"></iframe>
                @else
                    <img src="{{ $previewImage }}" alt="{{ $webinar->title }} live room preview">
                @endif
            </div>
        </div>
        <div>
            <div class="stats-grid control-stats"><x-stat-card label="Live viewers" :value="$liveViewers" icon="eye"
                    :live="true" data-live-viewer-count /><x-stat-card label="Registered" :value="$webinar->registrations_count"
                    icon="people" /><x-stat-card label="Capacity" :value="$webinar->max_attendees ?: 'Unlimited'" icon="person-check" tone="blue" />
            </div>
            <form class="panel-card mt-3" id="liveControlsForm" method="POST"
                action="{{ route('admin.webinars.controls', $webinar) }}" data-admin-live-controls>
                @csrf @method('PUT')
                <div class="panel-title">
                    <div><h3>Event Access &amp; Room Features</h3><p>Configure what attendees can access. Changes apply after you press Save controls.</p></div>
                    <span class="status-badge {{ $webinar->status }}">{{ strtoupper($webinar->status) }}</span>
                </div>

                <section class="control-section">
                    <div class="control-section-heading"><i class="bi bi-broadcast"></i><div><h4>Event status &amp; access</h4><p>Control when the room opens and who can enter.</p></div></div>
                    <div class="control-fields-grid">
                        <label class="control-setting-field"><span><strong>Event status</strong><small>Use Live only when the session is ready.</small></span><select class="form-select" name="status">
                            @foreach (['draft', 'scheduled', 'live', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected($webinar->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select></label>
                        <label class="live-control-switch"><span><strong>Open room now</strong><small class="text-muted">Allow registered attendees to enter immediately.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="earlyRoomOpen" name="early_room_open" value="1" @checked(data_get($webinar->settings, 'experience.early_room_open', false)) aria-label="Open room immediately"></span></label>
                        <label class="control-setting-field"><span><strong>Scheduled early access</strong><small>Minutes before start time that entry opens.</small></span><input class="form-control" type="number" name="early_entry_minutes" min="0" max="240" value="{{ $webinar->early_entry_minutes ?? 30 }}" required></label>
                        <label class="control-setting-field"><span><strong>Certificate watch requirement</strong><small>Minimum attendance percentage to unlock.</small></span><input class="form-control" type="number" name="certificate_min_attendance" min="0" max="100" value="{{ data_get($webinar->settings, 'experience.certificate_min_attendance', 80) }}" required></label>
                    </div>
                </section>

                <section class="control-section">
                    <div class="control-section-heading"><i class="bi bi-people"></i><div><h4>Audience features</h4><p>Choose the tools attendees can see inside the live room.</p></div></div>
                    <div class="control-toggle-grid">
                        <label class="live-control-switch"><span><strong>Participant list</strong><small class="text-muted">Show live viewers and participant names.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="participantsEnabled" name="participants_enabled" value="1" @checked(data_get($webinar->settings, 'experience.participants_enabled', false)) aria-label="Toggle participant list"></span></label>
                        <label class="live-control-switch"><span><strong>Live chat</strong><small class="text-muted">Allow attendees to send chat messages.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="liveChatEnabled" name="chat_enabled" value="1" @checked($webinar->chat_enabled) aria-label="Toggle live chat"></span></label>
                        <label class="live-control-switch"><span><strong>Comments</strong><small class="text-muted">Show the comments section to attendees.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="commentsEnabled" name="comments_enabled" value="1" @checked($webinar->comments_enabled) aria-label="Toggle comments"></span></label>
                        <label class="live-control-switch"><span><strong>Polls &amp; quizzes</strong><small class="text-muted">Allow active polls to appear in the room.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="pollsEnabled" name="polls_enabled" value="1" @checked($webinar->polls_enabled) aria-label="Toggle polls"></span></label>
                        <label class="live-control-switch"><span><strong>Feedback</strong><small class="text-muted">Let attendees submit session feedback.</small></span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="feedbackEnabled" name="feedback_enabled" value="1" @checked($webinar->feedback_enabled) aria-label="Toggle feedback"></span></label>
                    </div>
                </section>

                <div class="control-save-bar"><small><i class="bi bi-info-circle me-1"></i>Preview the live room after saving to confirm attendee visibility.</small><div class="d-flex gap-2"><a href="{{ route('admin.webinars.index') }}" class="btn btn-light">Back</a><button class="btn btn-gradient"><i class="bi bi-check2-circle me-1"></i>Save controls</button></div></div>
            </form>
            <form class="panel-card mt-3" method="POST"
                action="{{ route('admin.webinars.registration-visibility', $webinar) }}" data-registration-control>
                @csrf @method('PATCH')
                <div class="panel-title">
                    <div>
                        <h3>Registration Control</h3>
                        <p>Open or stop new registrations instantly. Existing registered attendees can still log in.</p>
                    </div>
                    <span class="status-badge {{ ($webinar->registrationForm?->is_active ?? true) ? 'live' : 'draft' }}">
                        {{ ($webinar->registrationForm?->is_active ?? true) ? 'ON' : 'OFF' }}
                    </span>
                </div>
                <div class="live-control-switch">
                    <span><strong>Accept new registrations</strong><small class="text-muted">This switch only controls the public registration form.</small></span>
                    <input type="hidden" name="enabled" value="0">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"
                            id="registrationEnabled" name="enabled" value="1"
                            @checked($webinar->registrationForm?->is_active ?? true)
                            onchange="this.form.submit()" aria-label="Toggle webinar registration"></div>
                </div>
            </form>
            <div class="panel-card mt-3" data-live-room-certificate>
                <div class="panel-title">
                    <div>
                        <h3>Live Room Certificate</h3>
                        <p>Control whether eligible attendees can see and download their certificate in the live room.</p>
                    </div>
                </div>
                <div class="live-control-switch">
                    <span><strong>Certificate download</strong><small class="text-muted">ON shows the download option in the attendee live room; OFF hides it immediately.</small></span>
                    <input type="hidden" name="certificate_enabled" value="0" form="liveControlsForm">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                            role="switch" id="certificateEnabled" name="certificate_enabled" value="1"
                            form="liveControlsForm" @checked($webinar->certificate_enabled === 'yes')
                            aria-label="Toggle live room certificate download"></div>
                </div>
            </div>
            <div class="panel-card mt-3" data-live-poll-controls>
                <div class="panel-title">
                    <div><h3>Poll &amp; Quiz controls</h3><p>Use the switch to show or hide a poll manually on the attendee frontend.</p></div>
                    <a class="btn btn-sm btn-light" href="{{ route('admin.webinars.edit', $webinar) }}#poll-builder"><i class="bi bi-plus-lg me-1"></i>Add Poll</a>
                </div>
                <div class="switch-list">
                    @forelse ($webinar->polls->sortByDesc('created_at') as $poll)
                        @php($pollRunning = $poll->status === 'active')
                        <form class="live-control-switch" method="POST" action="{{ route('admin.polls.status', $poll) }}" data-live-poll-switch>
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $pollRunning ? 'ended' : 'active' }}" data-poll-status>
                            <span><span class="d-flex align-items-center gap-2 flex-wrap"><strong>{{ $poll->question }}</strong><span class="badge {{ $poll->type === 'Quiz' ? 'text-bg-primary' : 'text-bg-secondary' }}">{{ $poll->type }}</span></span>
                                <small class="text-muted">{{ $pollRunning ? 'Visible to attendees now' : ucfirst($poll->status) }}</small></span>
                            <span class="form-check form-switch flex-shrink-0"><input class="form-check-input" type="checkbox" role="switch" @checked($pollRunning) aria-label="Show {{ $poll->type }} on attendee frontend" data-poll-start-switch></span>
                        </form>
                    @empty
                        <div class="text-center py-4 text-muted">No poll or quiz has been configured for this webinar.</div>
                    @endforelse
                </div>
            </div>
            @php($pinned = data_get($webinar->settings, 'pinned_announcement', []))
            <form class="panel-card mt-3" method="POST" action="{{ route('admin.webinars.announcement', $webinar) }}"
                data-admin-announcement-form>@csrf @method('PUT')<div class="panel-title">
                    <h3>Important Note / Announcement Banner (Landing Page & Live Room)</h3>
                </div>
                <div class="mb-3"><label class="form-label small text-muted">Announcement message</label>
                    <textarea class="form-control form-control-sm" name="message" rows="2" data-announcement-message
                        placeholder="e.g. Special offer: Use coupon LIVE50 to get 50% off!">{{ $pinned['message'] ?? '' }}</textarea>
                    <div class="d-flex justify-content-between mt-1"><small class="text-danger" data-announcement-error
                            hidden>The announcement message cannot be more than 50 characters.</small><small
                            class="text-muted ms-auto" data-announcement-count>0 / 50</small></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label small text-muted">Button label (optional)</label><input
                            type="text" class="form-control form-control-sm" name="button_text"
                            value="{{ $pinned['button_text'] ?? '' }}" placeholder="e.g. Claim Offer"></div>
                    <div class="col-6"><label class="form-label small text-muted">Button URL (optional)</label><input
                            type="url" class="form-control form-control-sm" name="button_url"
                            value="{{ $pinned['button_url'] ?? '' }}" placeholder="https://example.com/offer"></div>
                </div>
                <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="enabled"
                        value="1" id="pinBannerSwitch" @checked(!empty($pinned['enabled']))><label
                        class="form-check-label small" for="pinBannerSwitch">Show important note banner on landing page &
                        live room</label></div><button class="btn btn-gradient btn-sm">Update pinned banner</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-live-poll-switch]').forEach(form => {
                form.querySelector('[data-poll-start-switch]')?.addEventListener('change', event => {
                    form.querySelector('[data-poll-status]').value = event.target.checked ? 'active' : 'ended';
                    form.submit();
                });
            });
        });
    </script>
@endsection
