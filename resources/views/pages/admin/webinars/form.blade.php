@extends('layouts.portal')
@section('title', $webinar->exists ? 'Edit Webinar' : 'Create Webinar')
@section('content')
<style>
/* Modern Glassmorphic / Card Wizard Container */
.webinar-stepper-wrap {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 8px 12px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
}
.webinar-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    overflow-x: auto;
}
.step-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 14px;
    background: transparent;
    border: 1px solid transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    flex: 1;
    min-width: 175px;
}
.step-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.step-item.active {
    background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
    box-shadow: 0 8px 22px rgba(124, 58, 237, 0.28);
    border-color: transparent;
}
.step-badge {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    display: grid;
    place-items: center;
    background: #f1f5f9;
    color: #64748b;
    font-weight: 800;
    font-size: 0.9rem;
    flex: none;
    transition: all 0.2s ease;
}
.step-item.active .step-badge {
    background: #ffffff;
    color: #7c3aed;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}
.step-item.completed .step-badge {
    background: #10b981;
    color: #ffffff;
}
.step-meta {
    display: flex;
    flex-direction: column;
}
.step-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #334155;
    line-height: 1.25;
}
.step-item.active .step-title {
    color: #ffffff;
}
.step-sub {
    font-size: 0.72rem;
    color: #94a3b8;
    margin-top: 2px;
}
.step-item.active .step-sub {
    color: rgba(255, 255, 255, 0.85);
}
.step-arrow {
    color: #cbd5e1;
    font-size: 0.9rem;
    flex: none;
}
@media (max-width: 820px) {
    .step-arrow { display: none; }
    .step-item { min-width: 140px; padding: 8px 10px; }
    .step-sub { display: none; }
}

/* Step Panes */
.wizard-step-pane {
    display: none;
}
.wizard-step-pane.active {
    display: block;
    animation: wizardStepFade 0.22s ease forwards;
}
@keyframes wizardStepFade {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Styles & Inline Left-Aligned Labels */
.form-field {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    text-align: left !important;
    justify-content: flex-start !important;
    gap: 6px !important;
    width: 100% !important;
}
.form-field.full {
    grid-column: 1 / -1 !important;
}
.form-field > .form-control,
.form-field > .form-select,
.form-field > .input-group,
.form-field > textarea {
    width: 100% !important;
    border-radius: 10px !important;
    border: 1.5px solid #cbd5e1 !important;
    padding: 10px 14px !important;
    font-size: 0.93rem !important;
    color: #0f172a !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease !important;
}
.form-field > .form-control:focus,
.form-field > .form-select:focus,
.form-field > .input-group:focus-within,
.form-field > textarea:focus {
    border-color: #7c3aed !important;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.14) !important;
    outline: none !important;
}
.form-grid label.form-label-custom,
.form-field label.form-label-custom,
.form-label-custom {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-start !important;
    text-align: left !important;
    width: fit-content !important;
    gap: 6px !important;
    font-size: 0.88rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    margin: 0 0 4px 0 !important;
}
.form-label-custom .req {
    color: #ef4444 !important;
    font-weight: 800 !important;
    font-size: 1rem !important;
    line-height: 1 !important;
    display: inline-block !important;
}
.wizard-field-error {
    color: #ef4444 !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    margin-top: 4px !important;
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
}

/* Section Dividers inside Cards */
.form-subhead {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 22px 0 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
    grid-column: 1 / -1;
}
.form-subhead i {
    font-size: 1.1rem;
    color: #7c3aed;
}
.form-subhead strong {
    font-size: 0.95rem;
    color: #0f172a;
}
.form-subhead small {
    color: #64748b;
    margin-left: auto;
    font-size: 0.75rem;
}

/* Segmented Choice Buttons (Registration Type, Players, Layouts) */
.choice-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    grid-column: 1 / -1;
}
.choice-card {
    position: relative;
    cursor: pointer;
    margin: 0;
}
.choice-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.choice-card-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.choice-card:hover .choice-card-box {
    border-color: #c4b5fd;
    background: #faf5ff;
}
.choice-card input[type="radio"]:checked + .choice-card-box {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 4px 14px rgba(124, 58, 237, 0.15);
}
.choice-card-box i {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 1.25rem;
    flex: none;
    transition: all 0.2s ease;
}
.choice-card input[type="radio"]:checked + .choice-card-box i {
    background: #7c3aed;
    color: #ffffff;
}
.choice-card-text {
    display: flex;
    flex-direction: column;
}
.choice-card-text strong {
    font-size: 0.88rem;
    color: #1e293b;
}
.choice-card-text small {
    font-size: 0.72rem;
    color: #64748b;
}

/* Floating / Sticky Navigation Footer */
.wizard-footer-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 24px;
    padding: 16px 22px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 6px 25px rgba(15, 23, 42, 0.05);
}
.wizard-step-info-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
}
.wizard-step-info-badge i {
    color: #7c3aed;
}

/* Live Player & Embed Studio */
.live-embed-preview {
    min-height: 270px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: #090e1a;
    color: #94a3b8;
}
.live-embed-preview iframe {
    width: 100%;
    aspect-ratio: 16/9;
    border: 0;
}
.live-embed-preview>div {
    display: grid;
    justify-items: center;
    gap: 10px;
}
.live-embed-preview i {
    font-size: 3rem;
    color: #8b5cf6;
}
@media (max-width: 768px) {
    .wizard-footer-bar { flex-direction: column; align-items: stretch; }
    .choice-cards-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-heading">
    <div>
        <span class="eyebrow">ADMIN WORKSPACE</span>
        <h1>{{ $webinar->exists ? 'Edit webinar' : 'Create webinar' }}</h1>
        <p>Set up and customize your webinar with a streamlined step-by-step experience.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($webinar->exists)
            <a class="btn btn-danger" href="{{ route('admin.webinars.live',$webinar) }}"><i class="bi bi-broadcast me-1"></i> Live control</a>
        @endif
        <a class="btn btn-light" href="{{ route('admin.webinars.index') }}"><i class="bi bi-arrow-left me-1"></i> Back to webinars</a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please check the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- High-End Stepper Navigation -->
<div class="webinar-stepper-wrap">
    <div class="webinar-stepper" role="tablist">
        <button type="button" class="step-item active" data-step-nav="1">
            <span class="step-badge" data-badge="1">1</span>
            <div class="step-meta">
                <span class="step-title">1. Basic Info</span>
                <span class="step-sub">Title, timing, details</span>
            </div>
        </button>
        <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="2">
            <span class="step-badge" data-badge="2">2</span>
            <div class="step-meta">
                <span class="step-title">2. Stream & Access</span>
                <span class="step-sub">Player, resources, chat</span>
            </div>
        </button>
        <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="3">
            <span class="step-badge" data-badge="3">3</span>
            <div class="step-meta">
                <span class="step-title">3. Branding & Theme</span>
                <span class="step-sub">Logo, colors, preview</span>
            </div>
        </button>
        <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="4">
            <span class="step-badge" data-badge="4">4</span>
            <div class="step-meta">
                <span class="step-title">4. Agenda & Chapters</span>
                <span class="step-sub">Session schedule</span>
            </div>
        </button>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="webinarForm" action="{{ $webinar->exists ? route('admin.webinars.update', $webinar) : route('admin.webinars.store') }}" novalidate>
    @csrf
    @if($webinar->exists)
        @method('PUT')
    @endif

    {{-- ======================================================== --}}
    {{-- STEP 1: BASIC INFORMATION                                --}}
    {{-- ======================================================== --}}
    <div class="wizard-step-pane active" data-step-pane="1">
        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Primary Details & Schedule</h3>
                    <p>Core metadata and timing parameters for this event.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field full">
                    <label class="form-label-custom" for="webinarTitle">
                        <span>Webinar Title</span>
                        <span class="req">*</span>
                    </label>
                    <input class="form-control form-control-lg" id="webinarTitle" name="title" value="{{ old('title', $webinar->title) }}" placeholder="e.g. Future of Digital Healthcare 2026">
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="webinarSlug">
                        <span>Public URL Slug</span>
                        <span class="req">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted font-monospace">/webinars/</span>
                        <input class="form-control font-monospace" id="webinarSlug" name="slug" value="{{ old('slug', $webinar->slug) }}" placeholder="auto-generated-from-title">
                    </div>
                    <small class="text-muted">Title type karte hi slug automatically generate hoga. Aap ise manually bhi edit kar sakte hain.</small>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarIcon">
                        <span>Webinar Icon</span>
                    </label>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select" name="icon" id="webinarIcon">
                            @foreach(['camera-video'=>'Video camera','broadcast'=>'Broadcast','people'=>'Community','mortarboard'=>'Education','lightbulb'=>'Ideas','cpu'=>'Technology','heart-pulse'=>'Healthcare','graph-up'=>'Business growth','megaphone'=>'Marketing'] as $icon=>$label)
                                <option value="{{ $icon }}" @selected(old('icon', $webinar->icon ?: 'camera-video') === $icon)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="stat-icon tone-purple flex-none" style="width:42px;height:42px;border-radius:10px;display:grid;place-items:center;">
                            <i class="bi bi-{{ old('icon', $webinar->icon ?: 'camera-video') }}" id="iconPreviewEl"></i>
                        </span>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarStatus">
                        <span>Event Status</span>
                    </label>
                    <select class="form-select" name="status" id="webinarStatus">
                        @foreach(['draft'=>'Draft (Private)','scheduled'=>'Scheduled','live'=>'Live Now','completed'=>'Completed','cancelled'=>'Cancelled'] as $status=>$label)
                            <option value="{{ $status }}" @selected(old('status', $webinar->status ?: 'draft') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarLanguage">
                        <span>Language</span>
                        <span class="req">*</span>
                    </label>
                    <input class="form-control" id="webinarLanguage" name="language" value="{{ old('language', $webinar->language ?: 'en') }}" placeholder="e.g. en, hi, es">
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarTimezone">
                        <span>Timezone</span>
                        <span class="req">*</span>
                    </label>
                    <input class="form-control" id="webinarTimezone" name="timezone" value="{{ old('timezone', $webinar->timezone ?: 'Asia/Kolkata') }}" placeholder="e.g. Asia/Kolkata">
                </div>

                <div class="form-subhead">
                    <i class="bi bi-calendar-range"></i>
                    <strong>Event Timing & Access Capacity</strong>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarStartsAt">
                        <span>Starts At</span>
                    </label>
                    <input class="form-control" type="datetime-local" id="webinarStartsAt" name="starts_at" value="{{ old('starts_at', $webinar->starts_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                    <small class="text-muted">Entered in selected webinar timezone.</small>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarEndsAt">
                        <span>Ends At</span>
                    </label>
                    <input class="form-control" type="datetime-local" id="webinarEndsAt" name="ends_at" value="{{ old('ends_at', $webinar->ends_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarMaxAttendees">
                        <span>Maximum Attendees</span>
                    </label>
                    <input class="form-control" type="number" min="1" id="webinarMaxAttendees" name="max_attendees" value="{{ old('max_attendees', $webinar->max_attendees) }}" placeholder="e.g. 500 (leave blank for unlimited)">
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarContactMobile">
                        <span>Contact Mobile Number</span>
                    </label>
                    <input class="form-control" type="tel" id="webinarContactMobile" name="contact_mobile" maxlength="25" value="{{ old('contact_mobile', data_get($webinar->settings, 'contact_mobile')) }}" placeholder="e.g. +91 98765 43210">
                    <small class="text-muted">Shown in this webinar's public Support section.</small>
                </div>

                <input type="hidden" name="registration_type" id="registrationType" value="free">
                <input type="hidden" name="price" id="webinarPrice" value="0">

                <div class="form-subhead">
                    <i class="bi bi-card-text"></i>
                    <strong>Overview & Description</strong>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="shortDescription">
                        <span>Short Teaser Summary</span>
                    </label>
                    <textarea class="form-control" id="shortDescription" name="short_description" rows="2" placeholder="One or two compelling sentences displayed on landing cards">{{ old('short_description', $webinar->short_description) }}</textarea>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="fullDescription">
                        <span>Full Description & Key Takeaways</span>
                    </label>
                    <textarea class="form-control" id="fullDescription" name="description" rows="5" placeholder="Detailed event breakdown, objectives, and prerequisites">{{ old('description', $webinar->description) }}</textarea>
                </div>
            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 2: STREAM & ACCESS                                  --}}
    {{-- ======================================================== --}}
    <div class="wizard-step-pane" data-step-pane="2">
        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Live Video Player & Session Resources</h3>
                    <p>Choose your streaming provider, player source, and upload downloadable materials.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label-custom" for="earlyEntryMinutes">
                        <span>Early Room Access (minutes)</span>
                    </label>
                    <input class="form-control" type="number" min="0" max="240" id="earlyEntryMinutes" name="early_entry_minutes" value="{{ old('early_entry_minutes', $webinar->early_entry_minutes ?? 30) }}">
                    <small class="text-muted">How early approved learners can enter the room before starts_at.</small>
                </div>
                <div></div>

                <div class="form-subhead">
                    <i class="bi bi-play-circle"></i>
                    <strong>Video Player Provider</strong>
                </div>

                <div class="choice-cards-grid">
                    <label class="choice-card">
                        <input type="radio" name="live_provider_choice" value="" @checked(!old('live_provider', $webinar->live_provider))>
                        <span class="choice-card-box">
                            <i class="bi bi-dash-circle"></i>
                            <span class="choice-card-text">
                                <strong>No Player</strong>
                                <small>Interactive room without embed</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="live_provider_choice" value="youtube" @checked(old('live_provider', $webinar->live_provider) === 'youtube')>
                        <span class="choice-card-box">
                            <i class="bi bi-youtube text-danger"></i>
                            <span class="choice-card-text">
                                <strong>YouTube</strong>
                                <small>Live stream, unlisted or video ID</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="live_provider_choice" value="vimeo" @checked(old('live_provider', $webinar->live_provider) === 'vimeo')>
                        <span class="choice-card-box">
                            <i class="bi bi-vimeo text-primary"></i>
                            <span class="choice-card-text">
                                <strong>Vimeo</strong>
                                <small>High bitrate video embed</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="live_provider_choice" value="custom" @checked(old('live_provider', $webinar->live_provider) === 'custom')>
                        <span class="choice-card-box">
                            <i class="bi bi-code-slash text-success"></i>
                            <span class="choice-card-text">
                                <strong>Custom Iframe</strong>
                                <small>External player or iframe source</small>
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Hidden select for compatibility with app.js event handlers -->
                <select class="form-select" name="live_provider" id="liveProvider" hidden>
                    <option value="">No embedded player</option>
                    <option value="youtube" @selected(old('live_provider', $webinar->live_provider) === 'youtube')>YouTube</option>
                    <option value="vimeo" @selected(old('live_provider', $webinar->live_provider) === 'vimeo')>Vimeo</option>
                    <option value="custom" @selected(old('live_provider', $webinar->live_provider) === 'custom')>Custom iframe URL</option>
                </select>

                <div class="form-field full" id="liveSourceField">
                    <label class="form-label-custom" for="liveSource">
                        <span>Video URL, ID, or Iframe Code</span>
                    </label>
                    <textarea class="form-control" name="live_source" id="liveSource" rows="3" placeholder="Paste YouTube/Vimeo URL, video ID, or iframe code">{{ old('live_source', $webinar->live_url) }}</textarea>
                    <small class="text-muted">The secure responsive embed player is configured automatically.</small>
                </div>

                <div class="full live-embed-preview" id="liveEmbedPreview">
                    <div>
                        <i class="bi bi-play-btn"></i>
                        <span>Select a player and paste the video source to preview it.</span>
                    </div>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>Downloadable Session Materials</strong>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="sessionResources">
                        <span>External Resource Links</span>
                    </label>
                    <textarea class="form-control" id="sessionResources" name="session_resources" rows="3" placeholder="Session guide | https://example.com/guide.pdf&#10;Presentation slides | https://example.com/slides.pdf">{{ old('session_resources', $sessionResourcesText) }}</textarea>
                    <small class="text-muted">Add one public resource per line in <strong>Title | URL</strong> format.</small>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="resourcePdfs">
                        <span>Upload PDF Documents</span>
                    </label>
                    <input class="form-control" type="file" id="resourcePdfs" name="resource_pdfs[]" accept="application/pdf,.pdf" multiple>
                    <small class="text-muted">Select one or more PDF files. Uploaded PDFs appear in the Session Resources kit.</small>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-chat-square-dots"></i>
                    <strong>Attendee Engagement Features</strong>
                </div>

                <label class="setting-toggle">
                    <span>
                        <strong>Live Chat</strong>
                        <small>Display real-time community chat room in session.</small>
                    </span>
                    <input type="checkbox" name="chat_enabled" value="1" @checked(old('chat_enabled', $webinar->chat_enabled ?? true))>
                </label>

                <label class="setting-toggle">
                    <span>
                        <strong>Private Comments</strong>
                        <small>Allow attendees to send private messages directly to the host.</small>
                    </span>
                    <input type="checkbox" name="comments_enabled" value="1" @checked(old('comments_enabled', $webinar->comments_enabled ?? true))>
                </label>

                <label class="setting-toggle">
                    <span>
                        <strong>Attendee Feedback</strong>
                        <small>Collect star ratings and reviews in the Feedback module.</small>
                    </span>
                    <input type="checkbox" name="feedback_enabled" value="1" @checked(old('feedback_enabled', $webinar->feedback_enabled ?? false))>
                </label>
            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 3: BRANDING & THEME                                 --}}
    {{-- ======================================================== --}}
    @php($experience = (array) old('experience', data_get($webinar->settings, 'experience', [])))
    <div class="wizard-step-pane" data-step-pane="3">
        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Experience Studio & Room Branding</h3>
                    <p>Customize room layout, color palettes, custom logos, and attendee waiting messages.</p>
                </div>
                <span class="status-badge scheduled"><i class="bi bi-stars"></i> Live studio</span>
            </div>

            <div class="form-grid">
                <div class="form-subhead">
                    <i class="bi bi-grid-1x2"></i>
                    <strong>Room Layout Design</strong>
                </div>

                <div class="choice-cards-grid">
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="theater" @checked(old('room_layout', $experience['layout'] ?? 'presentation') === 'theater')>
                        <span class="choice-card-box">
                            <i class="bi bi-aspect-ratio"></i>
                            <span class="choice-card-text">
                                <strong>Theater Mode</strong>
                                <small>Full width cinematic screen</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="presentation" @checked(old('room_layout', $experience['layout'] ?? 'presentation') === 'presentation')>
                        <span class="choice-card-box">
                            <i class="bi bi-layout-sidebar-reverse"></i>
                            <span class="choice-card-text">
                                <strong>Presentation</strong>
                                <small>Video player + side interaction panel</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="interview" @checked(old('room_layout', $experience['layout'] ?? 'presentation') === 'interview')>
                        <span class="choice-card-box">
                            <i class="bi bi-people"></i>
                            <span class="choice-card-text">
                                <strong>Interview Mode</strong>
                                <small>Two-column balanced split</small>
                            </span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="panel" @checked(old('room_layout', $experience['layout'] ?? 'presentation') === 'panel')>
                        <span class="choice-card-box">
                            <i class="bi bi-grid-fill"></i>
                            <span class="choice-card-text">
                                <strong>Panel Discussion</strong>
                                <small>Multi-speaker stage format</small>
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Hidden select for compatibility with app.js #roomLayout -->
                <select class="form-select" name="room_layout" id="roomLayout" hidden>
                    @foreach(['theater'=>'Theater','presentation'=>'Presentation + sidebar','interview'=>'Interview','panel'=>'Panel discussion'] as $value=>$label)
                        <option value="{{ $value }}" @selected(old('room_layout', $experience['layout'] ?? 'presentation') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="form-field">
                    <label class="form-label-custom" for="registrationPreset">
                        <span>Registration Template Preset</span>
                    </label>
                    <select class="form-select" name="registration_preset" id="registrationPreset">
                        @foreach(['custom'=>'Custom','business'=>'Business & Executive','education'=>'Education & Academia','healthcare'=>'Healthcare & Medical','marketing'=>'Marketing & Growth'] as $value=>$label)
                            <option value="{{ $value }}" @selected(old('registration_preset', $experience['registration_preset'] ?? 'custom') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div></div>

                <div class="full theme-studio">
                    <div>
                        <strong>Theme Color Studio</strong>
                        <small class="text-muted">Choose a pre-made preset or customize your primary & accent colors.</small>
                    </div>
                    <div class="theme-presets mt-2">
                        <button type="button" data-theme-preset="#6d28d9|#2563eb"><i style="--a:#6d28d9;--b:#2563eb"></i>Signature</button>
                        <button type="button" data-theme-preset="#0f766e|#22c55e"><i style="--a:#0f766e;--b:#22c55e"></i>Healthcare</button>
                        <button type="button" data-theme-preset="#0f172a|#f59e0b"><i style="--a:#0f172a;--b:#f59e0b"></i>Luxury</button>
                        <button type="button" data-theme-preset="#be123c|#f97316"><i style="--a:#be123c;--b:#f97316"></i>Energy</button>
                        <button type="button" data-theme-preset="#1d4ed8|#06b6d4"><i style="--a:#1d4ed8;--b:#06b6d4"></i>Corporate</button>
                    </div>
                    <div class="theme-color-grid mt-3">
                        <label>
                            <span>Primary Brand Color</span>
                            <span class="professional-color">
                                <i class="color-swatch" data-color-swatch="primary"></i>
                                <input class="color-hex-input" type="text" name="brand_primary" id="brandPrimary" value="{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#6D28D9">
                            </span>
                        </label>
                        <label>
                            <span>Accent Glow Color</span>
                            <span class="professional-color">
                                <i class="color-swatch" data-color-swatch="secondary"></i>
                                <input class="color-hex-input" type="text" name="brand_secondary" id="brandSecondary" value="{{ old('brand_secondary', $experience['secondary'] ?? '#2563eb') }}" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#2563EB">
                            </span>
                        </label>
                    </div>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-image"></i>
                    <strong>Webinar Logo (Navbar & Footer Display)</strong>
                </div>

                <div class="full">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom"><span>Upload Custom Logo Image</span></label>
                                <input class="form-control" type="file" name="brand_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                <small class="text-muted">PNG, JPG, SVG or WebP, up to 5 MB.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom"><span>Or Direct Logo Image URL</span></label>
                                <input class="form-control" type="url" name="brand_logo_url" value="{{ old('brand_logo_url', $experience['logo_url'] ?? '') }}" placeholder="https://example.com/logo.png">
                                <small class="text-muted">Public https URL of your brand logo.</small>
                            </div>
                        </div>
                    </div>
                    @if(!empty($experience['logo_url']))
                        <div class="mt-3 p-3 bg-light border rounded-3 d-inline-flex align-items-center gap-3">
                            <span class="text-muted small">Current active logo:</span>
                            <img src="{{ $experience['logo_url'] }}" alt="Active logo preview" style="max-height:36px;max-width:160px;object-fit:contain;background:#fff;padding:4px 10px;border:1px solid #e2e8f0;border-radius:8px;">
                        </div>
                    @endif
                </div>

                <div class="form-subhead">
                    <i class="bi bi-chat-left-quote"></i>
                    <strong>Custom Attendee Messages</strong>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="waitingMessage">
                        <span>Waiting Room Greeting Message</span>
                    </label>
                    <textarea class="form-control" id="waitingMessage" name="waiting_message" rows="2">{{ old('waiting_message', $experience['waiting_message'] ?? 'The session will begin shortly. You are in the right place.') }}</textarea>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="waitingMediaUrl">
                        <span>Waiting Room Media URL (Optional Background Image/Video)</span>
                    </label>
                    <input class="form-control" type="url" id="waitingMediaUrl" name="waiting_media_url" value="{{ old('waiting_media_url', $experience['waiting_media_url'] ?? '') }}" placeholder="https://example.com/poster.jpg">
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="postMessage">
                        <span>Post-Webinar Farewell Message</span>
                    </label>
                    <textarea class="form-control" id="postMessage" name="post_message" rows="2">{{ old('post_message', $experience['post_message'] ?? 'Thank you for attending. Please share your feedback.') }}</textarea>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="regSuccessTitle">
                        <span>Registration Success Title</span>
                    </label>
                    <input class="form-control" id="regSuccessTitle" name="registration_success_title" value="{{ old('registration_success_title', $experience['registration_success_title'] ?? 'You are registered!') }}">
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="certificateMinAttendance">
                        <span>Certificate Minimum Watch Time (%)</span>
                    </label>
                    <input class="form-control" type="number" min="0" max="100" id="certificateMinAttendance" name="certificate_min_attendance" value="{{ old('certificate_min_attendance', $experience['certificate_min_attendance'] ?? 80) }}">
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="regSuccessMessage">
                        <span>Registration Confirmation Message</span>
                    </label>
                    <textarea class="form-control" id="regSuccessMessage" name="registration_success_message" rows="2">{{ old('registration_success_message', $experience['registration_success_message'] ?? 'Your seat is confirmed. Add the webinar to your calendar and return when the room opens.') }}</textarea>
                </div>

                <label class="setting-toggle full">
                    <span>
                        <strong>Require Poll Participation for Certificate</strong>
                        <small>Attendee must answer at least one live poll to unlock certificate download.</small>
                    </span>
                    <input type="checkbox" name="certificate_require_poll" value="1" @checked(old('certificate_require_poll', $experience['certificate_require_poll'] ?? false))>
                </label>

                <div class="full rounded-4 p-4 text-white mt-3" id="experiencePreview" style="background:linear-gradient(135deg,var(--preview-primary,#6d28d9),var(--preview-secondary,#2563eb))">
                    <small style="letter-spacing:0.1em;opacity:0.85;">ATTENDEE ROOM LIVE PREVIEW</small>
                    <h3 class="mt-2 mb-1" id="previewHeading">{{ $webinar->title ?: 'Your webinar title' }}</h3>
                    <span data-layout-preview>Presentation + sidebar layout</span>
                </div>
            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 4: AGENDA & CONTENT                                 --}}
    {{-- ======================================================== --}}
    <div class="wizard-step-pane" data-step-pane="4">
        <section class="panel-card mb-4">
            <div class="panel-title">
                <div>
                    <h3>Session Timeline & Agenda</h3>
                    <p>Schedule your keynote, topics, guest sessions, and breaks.</p>
                </div>
                <button class="btn btn-sm btn-light" type="button" id="addAgendaItem"><i class="bi bi-plus-lg me-1"></i> Add Agenda Session</button>
            </div>

            <div class="full agenda-builder">
                <input type="hidden" name="agenda_present" value="1">
                <?php
                    $agendaRows = collect(old('agenda', ($agendaItems ?? collect())->map(fn($item) => ['starts_at' => !empty($item->starts_at) ? substr($item->starts_at, 0, 5) : '', 'title' => $item->title ?? '', 'duration_minutes' => $item->duration_minutes ?? 30])->all()));
                    if ($agendaRows->isEmpty()) {
                        $agendaRows = collect([['starts_at' => '', 'title' => '', 'duration_minutes' => 30]]);
                    }
                ?>
                <div class="agenda-builder-rows" id="agendaBuilderRows">
                    @foreach($agendaRows as $index=>$item)
                        <div class="agenda-builder-row p-3 mb-2 bg-light border rounded-3 d-flex align-items-center gap-3" data-agenda-row>
                            <div style="width:130px;">
                                <label class="form-label-custom small mb-1"><span>Start Time</span></label>
                                <input class="form-control form-control-sm" type="time" name="agenda[{{ $index }}][starts_at]" value="{{ $item['starts_at'] ?? '' }}">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label-custom small mb-1"><span>Session Title</span></label>
                                <input class="form-control form-control-sm" name="agenda[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" maxlength="255" placeholder="e.g. Welcome & Keynote Speech">
                            </div>
                            <div style="width:110px;">
                                <label class="form-label-custom small mb-1"><span>Minutes</span></label>
                                <input class="form-control form-control-sm" type="number" min="1" max="1440" name="agenda[{{ $index }}][duration_minutes]" value="{{ $item['duration_minutes'] ?? '' }}" placeholder="30">
                            </div>
                            <div class="pt-4">
                                <button class="btn btn-sm btn-outline-danger" type="button" data-delete-agenda title="Delete agenda item"><i class="bi bi-trash3"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Video Chapter Timestamps</h3>
                    <p>Allow viewers to skip directly to timestamps in recorded or streamed sessions.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-field full">
                    <label class="form-label-custom" for="videoChapters">
                        <span>Chapter Timestamps</span>
                    </label>
                    <textarea class="form-control font-monospace" id="videoChapters" name="video_chapters" rows="4" placeholder="00:00 | Welcome & Opening&#10;12:30 | Keynote Presentation&#10;45:00 | Live Audience Q&A">{{ old('video_chapters', collect($experience['chapters'] ?? [])->map(fn($item) => ($item['time'] ?? '') . ' | ' . ($item['title'] ?? ''))->join("\n")) }}</textarea>
                </div>
                <div class="full p-3 bg-light border rounded-3 d-flex align-items-center gap-3">
                    <i class="bi bi-info-circle text-primary fs-4 flex-none"></i>
                    <span class="small text-muted"><strong>Format guide:</strong> Add one chapter per line as <code>time | title</code> (e.g. <code>05:30 | Product Demo</code>). Attendees can click chapters to scrub the player directly to that time.</span>
                </div>
            </div>
        </section>
    </div>

    <!-- Unified Floating Action Footer -->
    <div class="wizard-footer-bar">
        <div class="wizard-step-info-badge">
            <i class="bi bi-layers-fill"></i>
            <span id="footerStepIndicator">Step 1 of 4: Basic Info</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light" id="btnPrevStep" style="display:none;">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
            @if($webinar->exists)
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-cloud-check me-1"></i> Save changes
                </button>
            @endif
            <button type="button" class="btn btn-gradient px-4" id="btnNextStep">
                Next: Stream & Access <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="submit" class="btn btn-gradient px-4" id="btnFinalSubmit" style="display:none;">
                <i class="bi bi-check2-circle me-1"></i> {{ $webinar->exists ? 'Save webinar' : 'Create webinar' }}
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // Stepper Navigation Logic
    // -------------------------------------------------------------
    const stepTabs = document.querySelectorAll('.step-item');
    const stepPanes = document.querySelectorAll('.wizard-step-pane');
    const btnPrev = document.querySelector('#btnPrevStep');
    const btnNext = document.querySelector('#btnNextStep');
    const btnFinal = document.querySelector('#btnFinalSubmit');
    const footerIndicator = document.querySelector('#footerStepIndicator');

    const stepTitles = {
        1: { name: 'Basic Info', next: 'Stream & Access' },
        2: { name: 'Stream & Access', next: 'Branding & Theme' },
        3: { name: 'Branding & Theme', next: 'Agenda & Chapters' },
        4: { name: 'Agenda & Chapters', next: '' }
    };

    let activeStep = 1;

    function renderStep(step, shouldScroll = false) {
        step = parseInt(step, 10);
        if (isNaN(step) || step < 1 || step > 4) return;
        activeStep = step;

        // Update tabs
        stepTabs.forEach(tab => {
            const tabStep = parseInt(tab.dataset.stepNav, 10);
            const badge = tab.querySelector('.step-badge');
            if (tabStep === activeStep) {
                tab.classList.add('active');
                if (badge) badge.innerHTML = tabStep;
            } else {
                tab.classList.remove('active');
                if (tabStep < activeStep) {
                    tab.classList.add('completed');
                    if (badge) badge.innerHTML = '<i class="bi bi-check-lg"></i>';
                } else {
                    tab.classList.remove('completed');
                    if (badge) badge.innerHTML = tabStep;
                }
            }
        });

        // Update panes
        stepPanes.forEach(pane => {
            const paneStep = parseInt(pane.dataset.stepPane, 10);
            pane.classList.toggle('active', paneStep === activeStep);
        });

        // Update footer controls
        if (btnPrev) {
            btnPrev.style.display = activeStep > 1 ? 'inline-flex' : 'none';
        }
        if (btnNext && btnFinal) {
            if (activeStep === 4) {
                btnNext.style.display = 'none';
                btnFinal.style.display = 'inline-flex';
            } else {
                btnNext.style.display = 'inline-flex';
                btnNext.innerHTML = `Next: ${stepTitles[activeStep].next} <i class="bi bi-arrow-right ms-1"></i>`;
                btnFinal.style.display = 'none';
            }
        }
        if (footerIndicator) {
            footerIndicator.textContent = `Step ${activeStep} of 4: ${stepTitles[activeStep].name}`;
        }

        if (shouldScroll) {
            document.querySelector('.webinar-stepper-wrap')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Check if there are backend errors in any pane on load, else default to Step 1
    const firstInvalid = document.querySelector('.is-invalid, .invalid-feedback');
    if (firstInvalid) {
        const errorPane = firstInvalid.closest('.wizard-step-pane');
        if (errorPane && errorPane.dataset.stepPane) {
            renderStep(errorPane.dataset.stepPane, false);
        } else {
            renderStep(1, false);
        }
    } else {
        renderStep(1, false);
    }

    // Step validation helper (clean inline validation without native browser popups)
    function validateStep(step) {
        step = parseInt(step, 10);
        let isValid = true;
        const currentPane = document.querySelector(`.wizard-step-pane[data-step-pane="${step}"]`);
        if (!currentPane) return true;

        // Clear existing custom errors in this step
        currentPane.querySelectorAll('.wizard-field-error').forEach(el => el.remove());
        currentPane.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const markInvalid = (input, message) => {
            isValid = false;
            input.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'wizard-field-error text-danger small mt-1 fw-bold';
            err.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${message}`;
            const targetParent = input.closest('.input-group') || input;
            targetParent.parentNode.insertBefore(err, targetParent.nextSibling);

            input.addEventListener('input', () => {
                input.classList.remove('is-invalid');
                err.remove();
            }, { once: true });
        };

        if (step === 1) {
            const title = currentPane.querySelector('#webinarTitle');
            if (title && !title.value.trim()) {
                markInvalid(title, 'Webinar Title is required to proceed.');
            }
            const slug = currentPane.querySelector('#webinarSlug');
            if (slug && !slug.value.trim()) {
                markInvalid(slug, 'Public URL Slug is required to proceed.');
            }
            const startsAt = currentPane.querySelector('#webinarStartsAt');
            const endsAt = currentPane.querySelector('#webinarEndsAt');
            if (startsAt && endsAt && startsAt.value && endsAt.value) {
                if (new Date(endsAt.value) <= new Date(startsAt.value)) {
                    markInvalid(endsAt, 'End date and time must be after the start date and time.');
                }
            }
        }

        if (!isValid) {
            const firstErr = currentPane.querySelector('.is-invalid');
            if (firstErr) {
                firstErr.focus();
            }
        }

        return isValid;
    }

    // Tab buttons: allow clicking past steps freely, validate before jumping forward
    stepTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.stepNav, 10);
            if (target > activeStep) {
                if (!validateStep(activeStep)) {
                    return;
                }
            }
            renderStep(target, false);
        });
    });

    // Next button: strictly validate before advancing
    btnNext?.addEventListener('click', () => {
        if (!validateStep(activeStep)) {
            return;
        }
        if (activeStep < 4) {
            renderStep(activeStep + 1, true);
        }
    });

    // Previous button
    btnPrev?.addEventListener('click', () => {
        if (activeStep > 1) {
            renderStep(activeStep - 1, true);
        }
    });

    // -------------------------------------------------------------
    // Real-time Date Range Validation (ends_at must be after starts_at)
    // -------------------------------------------------------------
    const startsInput = document.querySelector('#webinarStartsAt');
    const endsInput = document.querySelector('#webinarEndsAt');

    function checkDateOrder() {
        if (!startsInput || !endsInput) return;
        if (startsInput.value) {
            endsInput.min = startsInput.value;
        }
        let err = endsInput.parentElement.querySelector('.wizard-field-error');
        if (startsInput.value && endsInput.value) {
            const startDate = new Date(startsInput.value);
            const endDate = new Date(endsInput.value);
            if (endDate <= startDate) {
                endsInput.classList.add('is-invalid');
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'wizard-field-error text-danger small mt-1 fw-bold';
                    endsInput.parentNode.insertBefore(err, endsInput.nextSibling);
                }
                err.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> End date and time must be after the start date and time.';
            } else {
                endsInput.classList.remove('is-invalid');
                if (err) err.remove();
            }
        } else {
            endsInput.classList.remove('is-invalid');
            if (err) err.remove();
        }
    }

    startsInput?.addEventListener('change', checkDateOrder);
    endsInput?.addEventListener('change', checkDateOrder);
    endsInput?.addEventListener('input', checkDateOrder);

    // -------------------------------------------------------------
    // Live Preview Heading Sync
    // -------------------------------------------------------------
    const titleInput = document.querySelector('#webinarTitle');
    const previewHeading = document.querySelector('#previewHeading');
    titleInput?.addEventListener('input', () => {
        if (previewHeading) {
            previewHeading.textContent = titleInput.value.trim() || 'Your webinar title';
        }
    });

    // -------------------------------------------------------------
    // Video Player Provider Segmented Cards Sync
    // -------------------------------------------------------------
    const providerRadios = document.querySelectorAll('input[name="live_provider_choice"]');
    const liveProviderSelect = document.querySelector('#liveProvider');

    providerRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (liveProviderSelect) {
                liveProviderSelect.value = radio.value;
                liveProviderSelect.dispatchEvent(new Event('change'));
            }
        });
    });

    // -------------------------------------------------------------
    // Room Layout Segmented Cards Sync
    // -------------------------------------------------------------
    const layoutRadios = document.querySelectorAll('input[name="room_layout_choice"]');
    const roomLayoutSelect = document.querySelector('#roomLayout');

    layoutRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (roomLayoutSelect) {
                roomLayoutSelect.value = radio.value;
                roomLayoutSelect.dispatchEvent(new Event('input'));
            }
        });
    });

    // -------------------------------------------------------------
    // Dynamic Icon Preview
    // -------------------------------------------------------------
    const iconSelect = document.querySelector('#webinarIcon');
    const iconPreviewEl = document.querySelector('#iconPreviewEl');
    iconSelect?.addEventListener('change', () => {
        if (iconPreviewEl) {
            iconPreviewEl.className = `bi bi-${iconSelect.value}`;
        }
    });

    // -------------------------------------------------------------
    // Auto-generate Slug from Title
    // -------------------------------------------------------------
    const slug = document.querySelector('#webinarSlug');
    if (titleInput && slug) {
        let manuallyEdited = Boolean(slug.value && slug.value !== '');
        const makeSlug = value => value.toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 180);
        titleInput.addEventListener('input', () => {
            if (!manuallyEdited) slug.value = makeSlug(titleInput.value);
        });
        slug.addEventListener('input', event => {
            manuallyEdited = event.isTrusted && Boolean(slug.value.trim());
            slug.value = makeSlug(slug.value);
        });
    }

    // -------------------------------------------------------------
    // Dynamic Agenda Rows
    // -------------------------------------------------------------
    const agendaRows = document.querySelector('#agendaBuilderRows');
    const addAgenda = document.querySelector('#addAgendaItem');
    const bindAgendaDelete = row => row.querySelector('[data-delete-agenda]')?.addEventListener('click', () => {
        const allRows = agendaRows ? agendaRows.querySelectorAll('[data-agenda-row]') : [];
        if (allRows.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(input => {
                if (input.type === 'number') {
                    input.value = '30';
                } else {
                    input.value = '';
                }
            });
        }
    });
    agendaRows?.querySelectorAll('[data-agenda-row]').forEach(bindAgendaDelete);

    addAgenda?.addEventListener('click', () => {
        const index = Date.now();
        const row = document.createElement('div');
        row.className = 'agenda-builder-row p-3 mb-2 bg-light border rounded-3 d-flex align-items-center gap-3';
        row.dataset.agendaRow = '';
        row.innerHTML = `
            <div style="width:130px;">
                <label class="form-label-custom small mb-1"><span>Start Time</span></label>
                <input class="form-control form-control-sm" type="time" name="agenda[${index}][starts_at]">
            </div>
            <div class="flex-grow-1">
                <label class="form-label-custom small mb-1"><span>Session Title</span></label>
                <input class="form-control form-control-sm" name="agenda[${index}][title]" maxlength="255" placeholder="e.g. Session Topic">
            </div>
            <div style="width:110px;">
                <label class="form-label-custom small mb-1"><span>Minutes</span></label>
                <input class="form-control form-control-sm" type="number" min="1" max="1440" name="agenda[${index}][duration_minutes]" placeholder="30">
            </div>
            <div class="pt-4">
                <button class="btn btn-sm btn-outline-danger" type="button" data-delete-agenda title="Delete agenda item"><i class="bi bi-trash3"></i></button>
            </div>
        `;
        agendaRows.appendChild(row);
        bindAgendaDelete(row);
        row.querySelector('input[type="time"]').focus();
    });
});
</script>
@endsection