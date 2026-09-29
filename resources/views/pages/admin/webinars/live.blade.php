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
    <div class="control-grid" data-live-viewers data-webinar-id="{{ $webinar->id }}"
        data-live-viewers-url="{{ route('admin.webinars.live-viewers', $webinar) }}">
        <div class="control-stage">
            <div class="control-preview"><span
                    class="status-badge {{ $webinar->status }}">{{ strtoupper($webinar->status) }}</span><i
                    class="bi bi-camera-video display-1"></i>
                <h2>{{ $webinar->title }}</h2>
                <p>{{ $webinar->short_description }}</p>
            </div>
        </div>
        <div>
            <div class="stats-grid control-stats"><x-stat-card label="Live viewers" :value="$liveViewers" icon="eye"
                    :live="true" data-live-viewer-count /><x-stat-card label="Registered" :value="$webinar->registrations_count"
                    icon="people" /><x-stat-card label="Capacity" :value="$webinar->max_attendees ?: 'Unlimited'" icon="person-check" tone="blue" />
            </div>
            <form class="panel-card mt-3" method="POST" action="{{ route('admin.webinars.controls', $webinar) }}"
                data-admin-live-controls>@csrf @method('PUT')<div class="panel-title">
                    <h3>Webinar controls</h3>
                </div>
                <div class="switch-list"><label
                        style="display:flex;align-items:center;justify-content:space-between;gap:16px"><span
                            style="white-space:nowrap;font-weight:600">Status</span><select class="form-select"
                            name="status"
                            style="width:auto;min-width:160px;font-weight:650;padding:6px 36px 6px 14px;border-radius:10px">
                            @foreach (['scheduled', 'live', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected($webinar->status === $status)>{{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select></label><label
                        style="display:flex;align-items:center;justify-content:space-between;gap:16px"><span><strong>Early
                                Room Access (minutes)</strong><small class="d-block text-muted">How early registered
                                attendees may enter before the start time.</small></span><input class="form-control"
                            type="number" name="early_entry_minutes" min="0" max="240"
                            value="{{ $webinar->early_entry_minutes ?? 30 }}" required style="width:100px"></label><label
                        style="display:flex;align-items:center;justify-content:space-between;gap:16px"><span><strong>Certificate
                                Minimum Watch Time (%)</strong><small class="d-block text-muted">Download unlocks after this
                                attendance percentage.</small></span><input class="form-control" type="number"
                            name="certificate_min_attendance" min="0" max="100"
                            value="{{ data_get($webinar->settings, 'experience.certificate_min_attendance', 80) }}"
                            required style="width:100px"></label>
                    <div class="live-control-switch"><span><strong>Participants</strong><small class="text-muted">Show the
                                real-time participant list in the live room and online count on the landing
                                page.</small></span>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"
                                id="participantsEnabled" name="participants_enabled" value="1"
                                @checked(data_get($webinar->settings, 'experience.participants_enabled', false)) aria-label="Toggle participants"></div>
                    </div>
                    <div class="live-control-switch"><span><strong>Live chat</strong></span>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"
                                id="liveChatEnabled" name="chat_enabled" value="1" @checked($webinar->chat_enabled)
                                aria-label="Toggle live chat"></div>
                    </div>
                    <div class="live-control-switch"><span><strong>Comments</strong></span>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"
                                id="commentsEnabled" name="comments_enabled" value="1" @checked($webinar->comments_enabled)
                                aria-label="Toggle comments"></div>
                    </div>
                    <div class="live-control-switch"><span><strong>Polls</strong></span>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"
                                id="pollsEnabled" name="polls_enabled" value="1" @checked($webinar->polls_enabled)
                                aria-label="Toggle polls"></div>
                    </div>
                    <div class="live-control-switch"><span><strong>Feedback</strong></span>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                role="switch" id="feedbackEnabled" name="feedback_enabled" value="1"
                                @checked($webinar->feedback_enabled) aria-label="Toggle feedback"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3"><button class="btn btn-gradient">Save live
                        controls</button><a href="{{ route('admin.webinars.index') }}" class="btn btn-light">Back</a>
                </div>
            </form>
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
@endsection
