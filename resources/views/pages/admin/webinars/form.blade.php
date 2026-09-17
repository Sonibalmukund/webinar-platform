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

.wizard-poll-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.wizard-poll-row:focus-within {
    border-color: #7c3aed;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.08);
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

/* Schedule date/time controls */
.schedule-picker-shell {
    position: relative;
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr) 44px;
    align-items: center;
    width: 100%;
    min-height: 58px;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.schedule-picker-shell:focus-within {
    border-color: #ee1f2d;
    box-shadow: 0 0 0 4px rgba(238, 31, 45, .12), 0 12px 28px rgba(185, 21, 34, .1);
    transform: translateY(-1px);
}
.schedule-picker-shell.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 4px rgba(220, 53, 69, .15) !important;
}
.schedule-picker-icon {
    align-self: stretch;
    display: grid;
    place-items: center;
    color: #ee1f2d;
    background: linear-gradient(145deg, #fff5f5, #ffe4e6);
    border-right: 1px solid #fecdd3;
    font-size: 1.15rem;
}
.schedule-picker-shell input[type="datetime-local"],
.schedule-picker-shell .flatpickr-input {
    width: 100%;
    min-width: 0;
    height: 56px;
    padding: 8px 12px;
    border: 0 !important;
    outline: 0 !important;
    box-shadow: none !important;
    background: transparent !important;
    color: #0f172a;
    font-weight: 700;
    color-scheme: light;
    accent-color: #ee1f2d;
}
.schedule-picker-shell > input[type="hidden"] { display: none; }
.flatpickr-calendar {
    overflow: hidden;
    border: 1px solid #fecdd3 !important;
    border-radius: 16px !important;
    box-shadow: 0 24px 55px rgba(15, 23, 42, .2) !important;
    font-family: inherit;
}
.flatpickr-months { padding: 7px 4px; background: linear-gradient(135deg, #ff3341, #ee1f2d); }
.flatpickr-months .flatpickr-month,
.flatpickr-current-month,
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month { color: #fff !important; fill: #fff !important; }
.flatpickr-weekdays { background: #fff1f2; }
span.flatpickr-weekday { color: #9f1239 !important; background: #fff1f2 !important; }
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange,
.flatpickr-day.selected:hover,
.flatpickr-day.selected:focus {
    border-color: #ee1f2d !important;
    background: #ee1f2d !important;
}
.flatpickr-day.today { border-color: #fb7185 !important; color: #be123c; }
.flatpickr-day:hover { border-color: #ffe4e6 !important; background: #ffe4e6 !important; }
.flatpickr-time input:hover,
.flatpickr-time input:focus,
.flatpickr-time .flatpickr-am-pm:hover,
.flatpickr-time .flatpickr-am-pm:focus { background: #fff1f2 !important; }
.schedule-picker-shell input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    opacity: 0;
    width: 0;
    padding: 0;
}
.schedule-picker-open {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border: 0;
    border-radius: 10px;
    color: #ffffff;
    background: linear-gradient(135deg, #ff3341, #ee1f2d);
    box-shadow: 0 5px 12px rgba(238, 31, 45, .25);
}
.schedule-picker-open:hover { transform: translateY(-1px); filter: brightness(1.05); }
.schedule-field-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
}
.schedule-timezone-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 999px;
    color: #be123c;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    font-size: .7rem;
    font-weight: 800;
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
.stream-source-studio {
    display:grid;
    grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr);
    gap:18px;
    grid-column:1/-1;
    align-items:stretch;
}
.stream-source-studio .live-embed-preview { min-height:360px; }
.stream-source-controls { display:flex; flex-direction:column; gap:14px; }
.stream-source-studio .stream-source-controls { grid-column:1; grid-row:1; }
.stream-source-studio .live-embed-preview { grid-column:2; grid-row:1; }
.stream-provider-buttons { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
.stream-provider-buttons .choice-card-box { height:100%; padding:12px; }
@media (max-width:900px) { .stream-source-studio { grid-template-columns:1fr; } }
@media (max-width:900px) { .stream-source-studio .stream-source-controls,.stream-source-studio .live-embed-preview { grid-column:1; grid-row:auto; } }
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

/* Photoshop-inspired brand color workspace */
.brand-studio-grid{display:grid;grid-template-columns:minmax(320px,.78fr) minmax(420px,1.22fr);gap:18px;grid-column:1/-1;align-items:stretch}
.ps-color-panel{overflow:hidden;border:1px solid #263244;border-radius:18px;background:#111827;color:#e5e7eb;box-shadow:0 18px 45px rgba(15,23,42,.16)}
.brand-color-launcher{padding:18px;border:1px solid #dfe5ee;border-radius:18px;background:linear-gradient(145deg,#fff,#f8fafc);box-shadow:0 18px 45px rgba(15,23,42,.08)}.brand-color-launcher>small{display:block;margin:5px 0 16px;color:#64748b}.brand-color-buttons{display:grid;gap:10px}.brand-color-open{display:grid;grid-template-columns:44px 1fr auto;align-items:center;gap:12px;padding:12px;border:1px solid #dfe5ee;border-radius:13px;background:#fff;text-align:left;transition:.2s}.brand-color-open:hover{border-color:#fb7185;box-shadow:0 0 0 3px #ffe4e6}.brand-color-open>i{width:44px;height:44px;border:4px solid #fff;border-radius:12px;box-shadow:0 0 0 1px #cbd5e1}.brand-color-open span{display:grid}.brand-color-open strong{font-size:.8rem}.brand-color-open code{color:#64748b;font-size:.72rem}.brand-color-open>.bi{width:auto;height:auto;border:0;box-shadow:none;color:#94a3b8}
.theme-picker-modal .modal-content{overflow:hidden;border:0;border-radius:20px;background:#111827;box-shadow:0 30px 90px #0008}.theme-picker-modal .modal-header{border-bottom-color:#2b3648;background:#0b1220;color:#fff}.theme-picker-modal .btn-close{filter:invert(1)}
.ps-color-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #2b3648;background:#0b1220}.ps-color-head strong{font-size:.88rem}.ps-color-head small{color:#8290a5;font-size:.67rem;letter-spacing:.08em}
.ps-target-tabs{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px}.ps-target-tab{display:flex;align-items:center;gap:9px;padding:10px;border:1px solid #303b4d;border-radius:11px;background:#172033;color:#aeb9ca;font-size:.73rem;font-weight:800}.ps-target-tab.active{border-color:#fb7185;background:#2a1720;color:#fff}.ps-target-tab i{width:22px;height:22px;border:3px solid #fff;border-radius:7px;box-shadow:0 0 0 1px #475569}
.ps-picker-body{padding:0 12px 12px}.ps-sv-field{position:relative;height:220px;overflow:hidden;border:1px solid #475569;border-radius:10px;cursor:crosshair;background:linear-gradient(to top,#000,transparent),linear-gradient(to right,#fff,hsl(var(--picker-hue,260),100%,50%));touch-action:none}.ps-picker-marker{position:absolute;left:75%;top:35%;width:16px;height:16px;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 1px #000,0 2px 7px #0008;transform:translate(-50%,-50%);pointer-events:none}
.ps-hue-row{display:grid;grid-template-columns:1fr 34px;gap:10px;align-items:center;margin-top:12px}.ps-hue-slider{width:100%;height:14px;padding:0;border:0;border-radius:999px;appearance:none;background:linear-gradient(90deg,#f00,#ff0,#0f0,#0ff,#00f,#f0f,#f00)}.ps-hue-slider::-webkit-slider-thumb{width:17px;height:22px;appearance:none;border:2px solid #fff;border-radius:4px;background:transparent;box-shadow:0 0 0 1px #111;cursor:ew-resize}.ps-native-color{width:34px;height:30px;padding:2px;border:1px solid #475569;border-radius:7px;background:#0b1220;cursor:pointer}
.ps-value-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.ps-value-grid label{color:#8290a5;font-size:.65rem}.ps-value-grid .professional-color{margin-top:5px;border-color:#344054;background:#0b1220}.ps-value-grid .professional-color .color-hex-input{color:#f8fafc!important}.ps-rgb-readout{padding:8px 10px;border:1px solid #344054;border-radius:10px;background:#0b1220;color:#9ba8ba;font-family:monospace;font-size:.72rem}
.brand-live-preview{display:flex;flex-direction:column;overflow:hidden;min-height:430px;border:1px solid #dfe5ee;border-radius:18px;background:#f8fafc;box-shadow:0 18px 45px rgba(15,23,42,.08)}.brand-live-preview header{display:flex;align-items:center;justify-content:space-between;padding:12px 15px;border-bottom:1px solid #e5e7eb;background:#fff}.brand-live-preview header strong{font-size:.78rem}.brand-live-preview header span{display:flex;align-items:center;gap:6px;color:#16a34a;font-size:.66rem;font-weight:800}.brand-live-preview header span:before{content:'';width:7px;height:7px;border-radius:50%;background:#22c55e}.brand-preview-frame{width:100%;min-height:390px;flex:1;border:0;background:#fff}
@media(max-width:1000px){.brand-studio-grid{grid-template-columns:1fr}.brand-live-preview{min-height:360px}.brand-preview-frame{min-height:320px}}
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
        <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="fields">
            <span class="step-badge" data-badge="fields">5</span>
            <div class="step-meta">
                <span class="step-title">5. Dynamic Fields</span>
                <span class="step-sub">Registration questions</span>
            </div>
        </button>
        <div class="step-arrow" id="arrowAfterFields" style="display:none;"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="poll" id="navStepPoll" style="display:none;">
            <span class="step-badge" data-badge="poll">6</span>
            <div class="step-meta">
                <span class="step-title" id="titleStepPoll">6. Polls & Quizzes</span>
                <span class="step-sub">Interactive questions</span>
            </div>
        </button>
        <div class="step-arrow" id="arrowAfterPoll" style="display:none;"><i class="bi bi-chevron-right"></i></div>
        <button type="button" class="step-item" data-step-nav="certificate" id="navStepCertificate" style="display:none;">
            <span class="step-badge" data-badge="certificate">7</span>
            <div class="step-meta">
                <span class="step-title" id="titleStepCertificate">7. Certificate</span>
                <span class="step-sub">Template & criteria</span>
            </div>
        </button>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="webinarForm" action="{{ $webinar->exists ? route('admin.webinars.update', $webinar) : route('admin.webinars.store') }}" novalidate data-no-validation>
    @csrf
    @if($webinar->exists)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4 p-3 d-flex align-items-start gap-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading fw-bold mb-1 fs-6">Please resolve the following errors:</h5>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @php
        $experience = (array) old('experience', data_get($webinar->settings, 'experience', []));
        $selectedRoomLayout = old('room_layout', in_array($experience['layout'] ?? 'presentation', ['theater', 'presentation'], true) ? ($experience['layout'] ?? 'presentation') : 'presentation');
    @endphp

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
                    <input class="form-control @error('title') is-invalid @enderror" id="webinarTitle" name="title" value="{{ old('title', $webinar->title) }}" placeholder="e.g. Strategic Global Leadership Summit 2026" required>
                    @error('title')<div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="webinarSlug">
                        <span>Public URL Slug</span>
                        <span class="req">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">/webinars/</span>
                        <input class="form-control @error('slug') is-invalid @enderror" id="webinarSlug" name="slug" value="{{ old('slug', $webinar->slug) }}" placeholder="slug-auto-generates">
                    </div>
                    <small class="text-muted">Title type karte hi slug automatically generate hoga. Aap ise manually bhi edit kar sakte hain.</small>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="brandLogoFile">
                        <span>Client / Webinar Logo</span>
                    </label>
                    <input class="form-control @error('brand_logo_file') is-invalid @enderror" type="file" id="brandLogoFile" name="brand_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                    @error('brand_logo_file')<div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>@enderror
                    <small class="text-muted">This client-specific logo is shown on the webinar landing page and attendee room. PNG, JPG, SVG or WebP, up to 5 MB.</small>
                    @if(!empty($experience['logo_url']))
                        <div class="mt-3 p-3 bg-light border rounded-3 d-inline-flex align-items-center gap-3">
                            <span class="text-muted small">Current client logo:</span>
                            <img src="{{ $experience['logo_url'] }}" alt="Current client logo" style="max-height:36px;max-width:160px;object-fit:contain;background:#fff;padding:4px 10px;border:1px solid #e2e8f0;border-radius:8px;">
                        </div>
                    @endif
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
                    <select class="form-select" id="webinarLanguage" name="language">
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" @selected(old('language', $webinar->language ?: 'en') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Stored as webinar metadata. This selection does not automatically translate page content.</small>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="webinarTimezone">
                        <span>Timezone</span>
                        <span class="req">*</span>
                    </label>
                    <select class="form-select" id="webinarTimezone" name="timezone">
                        @foreach($timezones as $timezone => $label)
                            <option value="{{ $timezone }}" @selected(old('timezone', $webinar->timezone ?: 'Asia/Kolkata') === $timezone)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Schedule fields and attendee dates use this timezone.</small>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-calendar-range"></i>
                    <strong>Event Timing & Access Capacity</strong>
                </div>

                <div class="form-field">
                    <div class="schedule-field-meta">
                        <label class="form-label-custom mb-0" for="webinarStartsAt"><span>Starts At</span></label>
                        <span class="schedule-timezone-chip" data-schedule-timezone><i class="bi bi-globe2"></i> {{ old('timezone', $webinar->timezone ?: 'Asia/Kolkata') }}</span>
                    </div>
                    <div class="schedule-picker-shell">
                        <span class="schedule-picker-icon"><i class="bi bi-calendar-event"></i></span>
                        <input class="form-control" type="datetime-local" id="webinarStartsAt" name="starts_at" data-webinar-datetime-picker value="{{ old('starts_at', $webinar->starts_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                        <button class="schedule-picker-open" type="button" data-open-picker="webinarStartsAt" aria-label="Open start date and time picker"><i class="bi bi-calendar3"></i></button>
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Date and time selected webinar timezone mein save honge.</small>
                </div>

                <div class="form-field">
                    <div class="schedule-field-meta">
                        <label class="form-label-custom mb-0" for="webinarEndsAt"><span>Ends At</span></label>
                        <span class="schedule-timezone-chip" data-schedule-timezone><i class="bi bi-globe2"></i> {{ old('timezone', $webinar->timezone ?: 'Asia/Kolkata') }}</span>
                    </div>
                    <div class="schedule-picker-shell">
                        <span class="schedule-picker-icon"><i class="bi bi-calendar-check"></i></span>
                        <input class="form-control" type="datetime-local" id="webinarEndsAt" name="ends_at" data-webinar-datetime-picker value="{{ old('ends_at', $webinar->ends_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                        <button class="schedule-picker-open" type="button" data-open-picker="webinarEndsAt" aria-label="Open end date and time picker"><i class="bi bi-calendar3"></i></button>
                    </div>
                </div>

                <div class="form-field full">
                    <div class="alert alert-light border mb-0" id="scheduleTimezonePreview" aria-live="polite">
                        Select a start time to preview the webinar schedule in the chosen timezone.
                    </div>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-grid-1x2"></i>
                    <strong>Room Layout Design</strong>
                    <small>Controls the attendee live-room layout</small>
                </div>

                <div class="choice-cards-grid">
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="theater" @checked($selectedRoomLayout === 'theater')>
                        <span class="choice-card-box">
                            <i class="bi bi-aspect-ratio"></i>
                            <span class="choice-card-text"><strong>Theater Mode</strong><small>Full-screen iframe, followed by all webinar content</small></span>
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="room_layout_choice" value="presentation" @checked($selectedRoomLayout === 'presentation')>
                        <span class="choice-card-box">
                            <i class="bi bi-layout-sidebar-reverse"></i>
                            <span class="choice-card-text"><strong>Interactive Mode</strong><small>Current video + right-side interaction panel</small></span>
                        </span>
                    </label>
                </div>
                <select class="form-select" name="room_layout" id="roomLayout" hidden>
                    <option value="theater" @selected($selectedRoomLayout === 'theater')>Theater</option>
                    <option value="presentation" @selected($selectedRoomLayout === 'presentation')>Interactive</option>
                </select>

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
                    <small class="text-muted">How early registered attendees can enter the room before starts_at. Attendance tracking begins at the same time.</small>
                </div>
                <div></div>

                <div class="form-subhead"><i class="bi bi-play-circle"></i><strong>Iframe / Video Source</strong></div>
                <div class="stream-source-studio">
                    <div class="live-embed-preview" id="liveEmbedPreview">
                        <div><i class="bi bi-play-btn"></i><span>Select a player and paste the video source to preview it.</span></div>
                    </div>
                    <div class="stream-source-controls">
                <div class="stream-provider-buttons">
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

                <div class="form-field" id="liveSourceField">
                    <label class="form-label-custom" for="liveSource">
                        <span>Video URL, ID, or Iframe Code</span>
                        <span class="req" id="liveSourceRequiredMark" hidden>*</span>
                    </label>
                    <textarea class="form-control @error('live_source') is-invalid @enderror" name="live_source" id="liveSource" rows="3" placeholder="Paste YouTube/Vimeo URL, video ID, or iframe code">{{ old('live_source', $webinar->live_url) }}</textarea>
                    @error('live_source')<div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>@enderror
                    <small class="text-muted" id="liveSourceHelp">Player select karne par valid video source required hai. Secure responsive embed automatically configure hoga.</small>
                </div>
                    </div>
                </div>

                <div class="form-subhead">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>Downloadable Session Materials</strong>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="sessionResources">
                        <span>Resource Links <span class="badge bg-light text-secondary ms-1">Optional</span></span>
                    </label>
                    <textarea class="form-control" id="sessionResources" name="session_resources" rows="3" placeholder="Session guide | https://example.com/guide.pdf&#10;Presentation slides | https://example.com/slides.pdf">{{ old('session_resources', $sessionResourcesText) }}</textarea>
                    <small class="text-muted">Links attendees can open or download inside the webinar, such as slides or a guide. Add one per line as <strong>Title | URL</strong>.</small>
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
                        <strong>Live Q&amp;A</strong>
                        <small>Allow attendees to ask questions and vote on questions during the session.</small>
                    </span>
                    <input type="hidden" name="qa_enabled" value="0">
                    <input type="checkbox" name="qa_enabled" value="1" @checked(old('qa_enabled', $webinar->qa_enabled ?? true))>
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

                <label class="setting-toggle">
                    <span>
                        <strong>Live Polls & Quizzes</strong>
                        <small>Enable interactive audience polls & quizzes. Adds Step 6 for configuring questions.</small>
                    </span>
                    <input type="hidden" name="polls_enabled" value="0">
                    <input type="checkbox" name="polls_enabled" id="togglePollsEnabled" value="1" @checked(old('polls_enabled', (bool) ($webinar->polls_enabled ?? false)))>
                </label>

                <label class="setting-toggle">
                    <span>
                        <strong>Attendee Certificate</strong>
                        <small>Provide certificate upon meeting attendance rules. Adds dedicated Step for certificate template & rules.</small>
                    </span>
                    <input type="hidden" name="certificate_enabled" value="0">
                    <input type="checkbox" name="certificate_enabled" id="toggleCertificateEnabled" value="1" @checked(old('certificate_enabled', ($webinar->certificate_enabled ?? 'no') === 'yes'))>
                </label>

                <div class="form-subhead">
                    <i class="bi bi-image"></i>
                    <strong>No Player / Waiting Room Image</strong>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="waitingMediaFile"><span>Waiting Room Background Image</span></label>
                    <input class="form-control" type="file" id="waitingMediaFile" name="waiting_media_file" accept="image/png,image/jpeg,image/webp">
                    <small class="text-muted">No Player mode mein ye image webinar room mein dikhegi. Image na dene par webinar banner, phir default image use hogi. PNG, JPG or WebP, up to 8 MB.</small>
                    @if(!empty($experience['waiting_media_url']))<img class="mt-2 rounded border" src="{{ $experience['waiting_media_url'] }}" alt="Current waiting room background" style="max-height:120px;max-width:240px;object-fit:cover">@endif
                </div>

            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 3: BRANDING & THEME                                 --}}
    {{-- ======================================================== --}}
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
                <div class="brand-studio-grid">
                    <section class="brand-color-launcher" aria-label="Theme Color Studio">
                        <strong><i class="bi bi-palette2 me-2"></i>Theme Color Studio</strong>
                        <small>Click a color to open the Photoshop-style picker.</small>
                        <input type="hidden" name="brand_primary" id="brandPrimary" value="{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}">
                        <input type="hidden" name="brand_secondary" id="brandSecondary" value="{{ old('brand_secondary', $experience['secondary'] ?? '#2563eb') }}">
                        <div class="brand-color-buttons">
                            <button type="button" class="brand-color-open" data-color-open="primary"><i data-color-swatch="primary"></i><span><strong>Primary Brand</strong><code data-color-code="primary">{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}</code></span><i class="bi bi-chevron-right"></i></button>
                            <button type="button" class="brand-color-open" data-color-open="secondary"><i data-color-swatch="secondary"></i><span><strong>Accent Glow</strong><code data-color-code="secondary">{{ old('brand_secondary', $experience['secondary'] ?? '#2563eb') }}</code></span><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </section>
                    <section class="brand-live-preview">
                        <header><strong><i class="bi bi-window me-2"></i>Attendee Room Preview</strong><span>LIVE PREVIEW</span></header>
                        <iframe class="brand-preview-frame" id="experiencePreviewFrame" title="Attendee room color preview" data-preview-title="{{ $webinar->title ?: 'Your webinar title' }}"></iframe>
                    </section>
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
                    <label class="form-label-custom" for="postMessage">
                        <span>Post-Webinar Farewell Message</span>
                    </label>
                    <textarea class="form-control" id="postMessage" name="post_message" rows="2">{{ old('post_message', $experience['post_message'] ?? 'Thank you for attending. Please share your feedback.') }}</textarea>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="regSuccessTitle">
                        <span>Registration Success Title</span>
                    </label>
                    <input class="form-control" id="regSuccessTitle" name="registration_success_title" value="{{ old('registration_success_title', $experience['registration_success_title'] ?? 'You are registered!') }}">
                </div>

                <div class="form-field full">
                    <label class="form-label-custom" for="regSuccessMessage">
                        <span>Registration Confirmation Message</span>
                    </label>
                    <textarea class="form-control" id="regSuccessMessage" name="registration_success_message" rows="2">{{ old('registration_success_message', $experience['registration_success_message'] ?? 'Your seat is confirmed. Add the webinar to your calendar and return when the room opens.') }}</textarea>
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

    {{-- ======================================================== --}}
    {{-- STEP 5: DYNAMIC REGISTRATION FIELDS                      --}}
    {{-- ======================================================== --}}
    <div class="wizard-step-pane" data-step-pane="fields">
        <section class="panel-card">
            <div class="panel-title">
                <div><h3>Dynamic Registration Fields</h3><p>Build the questions shown on this webinar's registration page.</p></div>
                <button type="button" class="btn btn-sm btn-light" id="addDynamicField"><i class="bi bi-plus-lg me-1"></i> Add Field</button>
            </div>
            <div class="form-grid mb-4">
                <input type="hidden" name="registration_enabled" value="0">
                <label class="setting-toggle full"><span><strong>Enable Registration Form</strong><small>Collect these fields before giving the attendee access.</small></span><input type="checkbox" name="registration_enabled" value="1" @checked(old('registration_enabled', $webinar->registrationForm?->is_active ?? true))></label>
                <label class="form-field"><span class="form-label-custom">Form Title</span><input class="form-control" name="form_title" value="{{ old('form_title', $webinar->registrationForm?->title ?? 'Register for this webinar') }}"></label>
                <label class="form-field"><span class="form-label-custom">Success Message</span><input class="form-control" name="success_message" value="{{ old('success_message', $webinar->registrationForm?->success_message ?? 'Your registration is confirmed.') }}"></label>
            </div>
            @php
                $dynamicRows = collect(old('fields', $webinar->registrationForm?->fields?->map(fn($field) => [
                    'id'=>$field->id, 'label'=>$field->label, 'field_type'=>$field->field_type,
                    'placeholder'=>$field->placeholder, 'is_required'=>$field->is_required ? '1' : null,
                    'is_enabled'=>$field->is_enabled ? '1' : null,
                    'options'=>$field->options->pluck('label')->join("\n"),
                ])->all() ?? []));
                if ($dynamicRows->isEmpty()) $dynamicRows = collect([
                    ['label'=>'Full name','field_type'=>'text','placeholder'=>'Enter your full name','is_required'=>'1','is_enabled'=>'1'],
                    ['label'=>'Email address','field_type'=>'text','placeholder'=>'you@example.com','is_required'=>'1','is_enabled'=>'1'],
                    ['label'=>'Organization','field_type'=>'text','placeholder'=>'Company or institution','is_enabled'=>'1'],
                ]);
            @endphp
            <div id="dynamicFieldsList" class="d-flex flex-column gap-3">
                @foreach($dynamicRows as $index=>$field)
                <div class="p-3 border rounded-3 bg-light" data-dynamic-field>
                    @if(!empty($field['id']))<input type="hidden" data-field-name="id" name="fields[{{ $index }}][id]" value="{{ $field['id'] }}">@endif
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3"><label class="form-label">Field label</label><input class="form-control" data-field-name="label" name="fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}" placeholder="e.g. Department"></div>
                        <div class="col-lg-2"><label class="form-label">Type</label><select class="form-select" data-field-name="field_type" name="fields[{{ $index }}][field_type]">@foreach(['text'=>'Text','dropdown'=>'Dropdown','radio'=>'Radio','checkbox'=>'Checkboxes','country'=>'Country','state'=>'State','city'=>'City'] as $value=>$label)<option value="{{ $value }}" @selected(($field['field_type'] ?? 'text')===$value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-lg-3"><label class="form-label">Placeholder</label><input class="form-control" data-field-name="placeholder" name="fields[{{ $index }}][placeholder]" value="{{ $field['placeholder'] ?? '' }}"></div>
                        <div class="col-lg-3 d-flex gap-3 pb-2"><label class="form-check"><input class="form-check-input" type="checkbox" data-field-name="is_required" name="fields[{{ $index }}][is_required]" value="1" @checked(!empty($field['is_required']))> Required</label><label class="form-check"><input class="form-check-input" type="checkbox" data-field-name="is_enabled" name="fields[{{ $index }}][is_enabled]" value="1" @checked(!empty($field['is_enabled']))> Active</label></div>
                        <div class="col-lg-1 text-end"><button type="button" class="btn btn-outline-danger" data-remove-dynamic-field title="Remove field"><i class="bi bi-trash3"></i></button></div>
                        <div class="col-12"><label class="form-label">Options <small class="text-muted">(one per line; used by dropdown, radio and checkbox)</small></label><textarea class="form-control" rows="2" data-field-name="options" name="fields[{{ $index }}][options]">{{ $field['options'] ?? '' }}</textarea></div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 5 / POLLS & QUIZZES                                 --}}
    {{-- ======================================================== --}}
    <div class="wizard-step-pane" data-step-pane="poll">
        @if(isset($existingPolls) && $existingPolls->isNotEmpty())
            <section class="panel-card mb-4">
                <div class="panel-title">
                    <div>
                        <h3>Configured Polls for this Webinar</h3>
                        <p>Polls already linked to this webinar room.</p>
                    </div>
                    <span class="badge bg-purple-subtle text-purple fs-6 px-3 py-2 rounded-pill">{{ $existingPolls->count() }} Active/Saved</span>
                </div>
                <div class="row g-3">
                    @foreach($existingPolls as $p)
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge {{ $p->status === 'active' ? 'bg-success' : 'bg-secondary' }} text-uppercase">{{ $p->status }}</span>
                                        <small class="text-muted">{{ $p->options->count() }} options</small>
                                    </div>
                                    <strong class="d-block text-dark fs-6 mb-2">{{ $p->question }}</strong>
                                    <ul class="list-unstyled mb-0 small text-muted">
                                        @foreach($p->options as $opt)
                                            <li class="d-flex align-items-center gap-1 mb-1">
                                                <i class="bi {{ $opt->is_correct ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                                                <span>{{ $opt->label }}</span>
                                                @if($opt->is_correct)
                                                    <span class="badge bg-success-subtle text-success ms-auto small">Correct</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="mt-3 pt-2 border-top d-flex justify-content-end">
                                    <a href="{{ route('admin.polls.edit', $p) }}" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit in Polls Manager</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="panel-card">
            <div class="panel-title">
                <div>
                    <h3>Create Live Poll or Interactive Quiz</h3>
                    <p>Configure poll questions and options. Attendees can vote or submit answers live.</p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="addWizardPollAnswer"><i class="bi bi-plus-lg me-1"></i> Add Option</button>
            </div>

            <div class="form-grid">
                <div class="form-field full">
                    <label class="form-label-custom" for="pollQuestion">
                        <span>Poll / Quiz Question</span>
                    </label>
                    <input class="form-control form-control-lg" id="pollQuestion" name="poll_question" value="{{ old('poll_question') }}" placeholder="e.g. Which technology stack does your team primarily use?">
                </div>

                <div class="full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <strong>Answer Options</strong>
                            <small class="d-block text-muted">Add the choices, then select the correct answer from the dropdown below.</small>
                        </div>
                    </div>

                    <div id="wizardPollAnswersList" class="d-flex flex-column gap-2">
                        @php($wizardAnswers = old('poll_answers', ['', '', '', '']))
                        @php($wizardCorrect = old('poll_correct_index', null))
                        @foreach($wizardAnswers as $idx => $ans)
                            <div class="wizard-poll-row" data-option-row>
                                <span class="badge bg-secondary-subtle text-secondary fw-bold flex-none" style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;">{{ chr(65 + $idx) }}</span>
                                <input class="form-control" name="poll_answers[]" value="{{ $ans }}" placeholder="Option {{ chr(65 + $idx) }} text">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-poll-answer" title="Delete option" style="width:36px;height:36px;display:grid;place-items:center;border-radius:8px;">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-field mt-3" style="max-width:420px"><label class="form-label-custom" for="pollCorrectIndex"><span>Correct Answer</span></label><select class="form-select" name="poll_correct_index" id="pollCorrectIndex"><option value="">No correct answer (standard poll)</option>@foreach($wizardAnswers as $idx=>$ans)<option value="{{ $idx }}" @selected((string)$wizardCorrect===(string)$idx)>Option {{ chr(65+$idx) }}{{ filled($ans) ? ' — '.$ans : '' }}</option>@endforeach</select><small class="text-muted">Quiz answers and live results are broadcast to the room through Reverb.</small></div>
                    <div class="form-field mt-3" style="max-width:420px" id="wizardAnswerRevealField">
                        <label class="form-label-custom" for="pollAnswerReveal"><span>Correct Answer Visibility</span></label>
                        <select class="form-select" name="poll_answer_reveal" id="pollAnswerReveal">
                            <option value="immediate" @selected(old('poll_answer_reveal') === 'immediate')>Show immediately after attendee answers</option>
                            <option value="after_webinar" @selected(old('poll_answer_reveal', 'after_webinar') === 'after_webinar')>Show after the webinar finishes</option>
                            <option value="never" @selected(old('poll_answer_reveal') === 'never')>Don't show the correct answer</option>
                        </select>
                        <small class="text-muted">Quiz percentages are always hidden. This only controls correct-answer highlighting.</small>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="pollStatus">
                        <span>Poll Initial Status</span>
                    </label>
                    <select class="form-select" name="poll_status" id="pollStatus">
                        <option value="draft" @selected(old('poll_status') === 'draft')>Draft (Hidden until launched)</option>
                        <option value="active" @selected(old('poll_status') === 'active')>Published / Active Now</option>
                    </select>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="pollAllowMultiple">
                        <span>Answer Selection Mode</span>
                    </label>
                    <select class="form-select" name="poll_allow_multiple" id="pollAllowMultiple">
                        <option value="0" @selected(old('poll_allow_multiple') !== '1')>Single Choice (One answer only)</option>
                        <option value="1" @selected(old('poll_allow_multiple') === '1')>Multiple Choice (Checkboxes)</option>
                    </select>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="pollStartedAt">
                        <span>Schedule Start Time (Optional)</span>
                    </label>
                    <input class="form-control" type="datetime-local" id="pollStartedAt" name="poll_started_at" value="{{ old('poll_started_at') }}">
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="pollEndedAt">
                        <span>Schedule Auto-End Time (Optional)</span>
                    </label>
                    <input class="form-control" type="datetime-local" id="pollEndedAt" name="poll_ended_at" value="{{ old('poll_ended_at') }}">
                </div>
            </div>
        </section>
    </div>

    {{-- ======================================================== --}}
    {{-- STEP 6 / CERTIFICATE SETTINGS                            --}}
    {{-- ======================================================== --}}
    @php($selectedTemplateId = old('certificate_template_id', data_get($webinar->settings, 'certificate_template_id')))
    @php($activeTemplateDesign = $activeCertificateTemplate?->design ?? [])
    @php($certDefaults = ['headline'=>['x'=>50,'y'=>15,'width'=>70,'scale'=>100],'recipient'=>['x'=>50,'y'=>44,'width'=>55,'scale'=>100],'webinar'=>['x'=>50,'y'=>61,'width'=>55,'scale'=>100],'date'=>['x'=>20,'y'=>84,'width'=>25,'scale'=>100],'signature'=>['x'=>80,'y'=>76,'width'=>22,'scale'=>100],'signatory'=>['x'=>80,'y'=>86,'width'=>30,'scale'=>100]])
    @php($certPositions = old('positions', data_get($activeTemplateDesign, 'positions', $certDefaults)))
    @php($certImage = data_get($activeTemplateDesign, 'template_image'))
    @php($certSignature = data_get($activeTemplateDesign, 'signature_image'))
    @php($certAspect = \App\Support\WebinarCertificateTemplate::aspectRatio($activeCertificateTemplate))
    @php($certVisibleElements = old('certificate_visible_elements', \App\Support\WebinarCertificateTemplate::visibleElements($activeTemplateDesign)))
    <div class="wizard-step-pane" data-step-pane="certificate">
        <section class="panel-card mb-4">
            <div class="panel-title">
                <div>
                    <h3>Certificate Template & Criteria</h3>
                    <p>Issue automated verifiable certificates of completion to attendees.</p>
                </div>
                <a href="{{ route('admin.certificates.index') }}" target="_blank" class="btn btn-sm btn-light">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Full Certificate Studio
                </a>
            </div>

            <div class="form-grid">
                <div class="form-field full">
                    <label class="form-label-custom" for="certTemplateSelect">
                        <span>Choose Certificate Template</span>
                    </label>
                    <select class="form-select form-select-lg" name="certificate_template_id" id="certTemplateSelect">
                        <option value="custom" @selected(!$selectedTemplateId || $selectedTemplateId === 'custom')>-- Create / Customize Template for this Webinar --</option>
                        @if(isset($certificateTemplates) && $certificateTemplates->isNotEmpty())
                            <optgroup label="Saved Certificate Templates">
                                @foreach($certificateTemplates as $tpl)
                                    <option value="{{ $tpl->id }}" @selected((string)$selectedTemplateId === (string)$tpl->id)
                                        data-headline="{{ data_get($tpl->design, 'headline', 'Certificate of Completion') }}"
                                        data-signatory="{{ data_get($tpl->design, 'signatory', '') }}"
                                        data-orientation="{{ $tpl->orientation }}"
                                        data-aspect="{{ \App\Support\WebinarCertificateTemplate::aspectRatio($tpl) }}"
                                        data-visibility='@json(\App\Support\WebinarCertificateTemplate::visibleElements($tpl->design ?? []))'
                                        data-positions='@json(data_get($tpl->design, 'positions', $certDefaults))'
                                        data-image="{{ data_get($tpl->design, 'template_image', '') }}"
                                        data-signature="{{ data_get($tpl->design, 'signature_image', '') }}">
                                        {{ $tpl->name }} ({{ ucfirst($tpl->orientation) }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="full" id="customCertFieldsSection">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="certificateName">
                                    <span>Internal Template Name</span>
                                    <span class="req">*</span>
                                </label>
                                <input class="form-control" id="certificateName" name="certificate_name" value="{{ old('certificate_name', $activeCertificateTemplate?->name ?? ($webinar->title ? $webinar->title.' Certificate' : 'Webinar Completion Certificate')) }}" placeholder="e.g. Masterclass Completion Certificate">
                                <small class="text-muted">Admin identification only; this name is never printed on the certificate.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="certificateHeadline">
                                    <span>Certificate Headline</span>
                                    <span class="req">*</span>
                                </label>
                                <input class="form-control" id="certificateHeadline" name="certificate_headline" value="{{ old('certificate_headline', data_get($activeTemplateDesign, 'headline', 'Certificate of Completion')) }}" placeholder="e.g. Certificate of Completion">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="certificateSignatory">
                                    <span>Signatory Name & Title</span>
                                </label>
                                <input class="form-control" id="certificateSignatory" name="certificate_signatory" value="{{ old('certificate_signatory', data_get($activeTemplateDesign, 'signatory', 'Authorized Director')) }}" placeholder="e.g. Dr. John Doe, Director">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="certificateOrientation">
                                    <span>Orientation</span>
                                </label>
                                <select class="form-select" id="certificateOrientation" name="certificate_orientation">
                                    <option value="landscape" @selected(old('certificate_orientation', $activeCertificateTemplate?->orientation ?? 'landscape') === 'landscape')>Landscape (Horizontal - Recommended)</option>
                                    <option value="portrait" @selected(old('certificate_orientation', $activeCertificateTemplate?->orientation) === 'portrait')>Portrait (Vertical)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="certificateImageInput">
                                    <span>Upload Certificate Template Background</span>
                                </label>
                                <input class="form-control" type="file" id="certificateImageInput" name="certificate_template_image" accept="image/png,image/jpeg">
                                <small class="text-muted">PNG or JPG, up to 10 MB. The original image ratio is preserved without stretching.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-field">
                                <label class="form-label-custom" for="signatureImageInput">
                                    <span>Upload Signatory Signature Image</span>
                                </label>
                                <input class="form-control" type="file" id="signatureImageInput" name="certificate_signature_image" accept="image/png,image/jpeg">
                                <small class="text-muted">Transparent PNG recommended, up to 5 MB.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-field">
                    <label class="setting-toggle h-100" style="margin-top:28px;">
                        <span>
                            <strong>Require Poll Participation</strong>
                            <small>Attendee must submit at least one poll answer to unlock certificate.</small>
                        </span>
                        <input type="checkbox" name="certificate_require_poll" id="wizardCertRequirePoll" value="1" @checked(old('certificate_require_poll', (bool) data_get($webinar->settings, 'experience.certificate_require_poll', false)))>
                    </label>
                </div>

                <div class="form-field">
                    <label class="form-label-custom" for="certificateMinAttendance"><span>Minimum Attendance / Watch Time (%)</span></label>
                    <input class="form-control" type="number" min="0" max="100" id="certificateMinAttendance" name="certificate_min_attendance" value="{{ old('certificate_min_attendance', data_get($webinar->settings, 'experience.certificate_min_attendance', 80)) }}">
                    <small class="text-muted">Attendee ko certificate unlock karne ke liye itna webinar attend karna hoga.</small>
                </div>

                <div class="form-field full">
                    <label class="form-label-custom"><span>Show on Certificate</span></label>
                    <div class="certificate-visibility-grid w-100">
                        @foreach(['headline'=>'Headline','recipient'=>'Attendee name','webinar'=>'Webinar title','date'=>'Issue date','signature'=>'Signature image','signatory'=>'Signatory name'] as $key=>$label)
                            <label class="certificate-visibility-option">
                                <input type="hidden" name="certificate_visible_elements[{{ $key }}]" value="0">
                                <input class="form-check-input" type="checkbox" name="certificate_visible_elements[{{ $key }}]" value="1" data-certificate-visibility="{{ $key }}" @checked((bool)data_get($certVisibleElements,$key,true))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small class="text-muted">Only checked elements will appear in the preview and attendee PDF.</small>
                </div>

                <!-- Element Position & Coordinates (X, Y) Control Panel -->
                <div class="full mt-3 p-3 border rounded-3 bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div>
                            <strong class="d-block text-dark"><i class="bi bi-arrows-move me-1 text-primary"></i> Element Position & Coordinates (X, Y)</strong>
                            <small class="text-muted">Select an element to set exact X (%) and Y (%) coordinates, or drag it directly on the canvas.</small>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="resetCertCoordinatesBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                        </button>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label-custom small mb-1" for="certificateElementSelect"><span>Target Element</span></label>
                            <select class="form-select form-select-sm" id="certificateElementSelect">
                                <option value="headline">Headline</option>
                                <option value="recipient">Attendee Name</option>
                                <option value="webinar">Webinar Title</option>
                                <option value="date">Issue Date</option>
                                <option value="signature">Signature Image</option>
                                <option value="signatory">Signatory Name</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label-custom small mb-1" for="certificateX"><span>X (%)</span></label>
                            <input class="form-control form-control-sm" id="certificateX" type="number" min="0" max="100" step="0.1" placeholder="50">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label-custom small mb-1" for="certificateY"><span>Y (%)</span></label>
                            <input class="form-control form-control-sm" id="certificateY" type="number" min="0" max="100" step="0.1" placeholder="50">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label-custom small mb-1" for="certificateWidth"><span>Box Width (%)</span></label>
                            <input class="form-control form-control-sm" id="certificateWidth" type="number" min="5" max="90" step="1" placeholder="55">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label-custom small mb-1" for="certificateScale"><span>Size (%)</span></label>
                            <input class="form-control form-control-sm" id="certificateScale" type="number" min="50" max="200" step="5" placeholder="100">
                        </div>
                    </div>
                </div>

                @foreach($certDefaults as $key => $default)
                    <input type="hidden" data-position-x="{{ $key }}" name="positions[{{ $key }}][x]" value="{{ data_get($certPositions, $key.'.x', $default['x']) }}">
                    <input type="hidden" data-position-y="{{ $key }}" name="positions[{{ $key }}][y]" value="{{ data_get($certPositions, $key.'.y', $default['y']) }}">
                    <input type="hidden" data-position-width="{{ $key }}" name="positions[{{ $key }}][width]" value="{{ data_get($certPositions, $key.'.width', $default['width']) }}">
                    <input type="hidden" data-position-scale="{{ $key }}" name="positions[{{ $key }}][scale]" value="{{ data_get($certPositions, $key.'.scale', $default['scale']) }}">
                @endforeach

                <!-- Visual Certificate Designer & Canvas -->
                <div class="full mt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label-custom mb-0"><span>Visual Certificate Designer & Real-time Canvas</span></label>
                        <span class="badge bg-purple-subtle text-purple small"><i class="bi bi-hand-index me-1"></i> Drag to reposition or edit X/Y inputs</span>
                    </div>
                    <div class="certificate-preview designer-preview p-3 rounded-4 bg-light border d-flex justify-content-center overflow-hidden">
                        <div class="cert-inner draggable-certificate bg-white shadow-sm" id="certificateCanvas" style="max-width:700px;width:100%;aspect-ratio:{{ $certAspect }};--cert-accent:#6d28d9;border:3px solid #ddd6fe;position:relative;background-size:contain;background-position:center;background-repeat:no-repeat;background-image:{{ $certImage ? "url('$certImage')" : 'none' }};min-height:360px;">

                            <div class="certificate-drag-item webinar {{ data_get($certVisibleElements,'headline',true)?'':'d-none' }}" data-certificate-element="headline" style="left:{{ data_get($certPositions,'headline.x',50) }}%;top:{{ data_get($certPositions,'headline.y',15) }}%;width:{{ data_get($certPositions,'headline.width',70) }}%;--element-scale:{{ data_get($certPositions,'headline.scale',100)/100 }};opacity:.35;text-transform:uppercase">
                                <h3 class="fw-bold text-uppercase m-0" style="letter-spacing: 0.12em; font-size: 1.35rem; color: #475569;" id="certPreviewHeadlineWatermark">{{ old('certificate_headline', data_get($activeTemplateDesign, 'headline', 'Certificate of Completion')) }}</h3>
                            </div>

                            <div class="certificate-drag-item recipient {{ data_get($certVisibleElements,'recipient',true)?'':'d-none' }}" data-certificate-element="recipient" style="left:{{ data_get($certPositions,'recipient.x',50) }}%;top:{{ data_get($certPositions,'recipient.y',44) }}%;width:{{ data_get($certPositions,'recipient.width',55) }}%;--element-scale:{{ data_get($certPositions,'recipient.scale',100)/100 }}">
                                Attendee Name
                            </div>

                            <div class="certificate-drag-item webinar {{ data_get($certVisibleElements,'webinar',true)?'':'d-none' }}" data-certificate-element="webinar" style="left:{{ data_get($certPositions,'webinar.x',50) }}%;top:{{ data_get($certPositions,'webinar.y',61) }}%;width:{{ data_get($certPositions,'webinar.width',55) }}%;--element-scale:{{ data_get($certPositions,'webinar.scale',100)/100 }}" id="certPreviewWebinarTitle">
                                {{ $webinar->title ?: 'Your Webinar Title' }}
                            </div>

                            <div class="certificate-drag-item meta {{ data_get($certVisibleElements,'date',true)?'':'d-none' }}" data-certificate-element="date" style="left:{{ data_get($certPositions,'date.x',20) }}%;top:{{ data_get($certPositions,'date.y',84) }}%;width:{{ data_get($certPositions,'date.width',25) }}%;--element-scale:{{ data_get($certPositions,'date.scale',100)/100 }}">
                                {{ now()->format('F d, Y') }}
                            </div>

                            <div class="certificate-drag-item signature-image {{ $certSignature && data_get($certVisibleElements,'signature',true) ? '' : 'd-none' }}" data-certificate-element="signature" style="left:{{ data_get($certPositions,'signature.x',80) }}%;top:{{ data_get($certPositions,'signature.y',76) }}%;width:{{ data_get($certPositions,'signature.width',22) }}%;--element-scale:{{ data_get($certPositions,'signature.scale',100)/100 }}">
                                <img id="signaturePreview" src="{{ $certSignature ?: '' }}" alt="Signature" style="max-width:100%;max-height:50px;object-fit:contain;">
                            </div>

                            <div class="certificate-drag-item meta {{ data_get($certVisibleElements,'signatory',true)?'':'d-none' }}" data-certificate-element="signatory" style="left:{{ data_get($certPositions,'signatory.x',80) }}%;top:{{ data_get($certPositions,'signatory.y',86) }}%;width:{{ data_get($certPositions,'signatory.width',30) }}%;--element-scale:{{ data_get($certPositions,'signatory.scale',100)/100 }}" id="certPreviewSignatoryText">
                                {{ old('certificate_signatory', data_get($activeTemplateDesign, 'signatory', 'Authorized Director')) }}
                            </div>
                        </div>
                    </div>
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

<div class="modal fade theme-picker-modal" id="themeColorPickerModal" tabindex="-1" aria-labelledby="themeColorPickerTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><h2 class="modal-title fs-6" id="themeColorPickerTitle"><i class="bi bi-palette2 me-2"></i>Theme Color Picker</h2><small class="text-white-50">Photoshop-style color lab</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body p-0">
            <div class="ps-target-tabs"><button type="button" class="ps-target-tab active" data-color-target="primary"><i data-color-swatch="primary"></i><span>Primary Brand</span></button><button type="button" class="ps-target-tab" data-color-target="secondary"><i data-color-swatch="secondary"></i><span>Accent Glow</span></button></div>
            <div class="ps-picker-body"><div class="ps-sv-field" id="brandSvField" aria-label="Saturation and brightness picker"><span class="ps-picker-marker" id="brandPickerMarker"></span></div><div class="ps-hue-row"><input class="ps-hue-slider" id="brandHueSlider" type="range" min="0" max="360" value="260" aria-label="Hue"><input class="ps-native-color" id="brandNativeColor" type="color" value="{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}" title="Open system color picker"></div><div class="ps-value-grid"><div class="ps-rgb-readout" id="brandActiveHex">HEX #6D28D9</div><div class="ps-rgb-readout" id="brandRgbReadout">RGB 109 · 40 · 217</div><div class="ps-rgb-readout" id="brandHsvReadout">HSV 263° · 82% · 85%</div><button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Apply color</button></div></div>
        </div>
    </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // Dynamic Stepper Navigation Logic (4, 5, or 6 Steps)
    // -------------------------------------------------------------
    const stepTabs = document.querySelectorAll('.step-item');
    const stepPanes = document.querySelectorAll('.wizard-step-pane');
    const btnPrev = document.querySelector('#btnPrevStep');
    const btnNext = document.querySelector('#btnNextStep');
    const btnFinal = document.querySelector('#btnFinalSubmit');
    const footerIndicator = document.querySelector('#footerStepIndicator');

    const togglePolls = document.querySelector('#togglePollsEnabled');
    const toggleCert = document.querySelector('#toggleCertificateEnabled');
    const navStepPoll = document.querySelector('#navStepPoll');
    const navStepCert = document.querySelector('#navStepCertificate');
    const arrowAfterFields = document.querySelector('#arrowAfterFields');
    const arrowAfterPoll = document.querySelector('#arrowAfterPoll');
    const titleStepPoll = document.querySelector('#titleStepPoll');
    const titleStepCert = document.querySelector('#titleStepCertificate');

    let currentStepKey = '1';

    function getActiveSteps() {
        const steps = [
            { key: '1', name: 'Basic Info' },
            { key: '2', name: 'Stream & Access' },
            { key: '3', name: 'Branding & Theme' },
            { key: '4', name: 'Agenda & Chapters' },
            { key: 'fields', name: 'Dynamic Fields' },
        ];

        const pollsOn = Boolean(togglePolls && togglePolls.checked);
        const certOn = Boolean(toggleCert && toggleCert.checked);

        if (pollsOn) {
            steps.push({ key: 'poll', name: 'Polls & Quizzes' });
        }
        if (certOn) {
            steps.push({ key: 'certificate', name: 'Certificate' });
        }

        return steps;
    }

    function renderStep(targetKey = null, shouldScroll = false) {
        const activeSteps = getActiveSteps();
        const pollsOn = Boolean(togglePolls && togglePolls.checked);
        const certOn = Boolean(toggleCert && toggleCert.checked);

        // Update tab display & step numbers
        if (pollsOn && navStepPoll) {
            navStepPoll.style.display = 'flex';
            const pollStepNum = 6;
            if (titleStepPoll) titleStepPoll.textContent = `${pollStepNum}. Polls & Quizzes`;
            const badge = navStepPoll.querySelector('.step-badge');
            if (badge) badge.dataset.badge = pollStepNum;
        } else if (navStepPoll) {
            navStepPoll.style.display = 'none';
        }

        if (certOn && navStepCert) {
            navStepCert.style.display = 'flex';
            const certStepNum = pollsOn ? 7 : 6;
            if (titleStepCert) titleStepCert.textContent = `${certStepNum}. Certificate`;
            const badge = navStepCert.querySelector('.step-badge');
            if (badge) badge.dataset.badge = certStepNum;
        } else if (navStepCert) {
            navStepCert.style.display = 'none';
        }

        // Configure separator arrows
        if (arrowAfterFields) {
            arrowAfterFields.style.display = (pollsOn || certOn) ? 'block' : 'none';
        }
        if (arrowAfterPoll) {
            arrowAfterPoll.style.display = (pollsOn && certOn) ? 'block' : 'none';
        }

        // Validate or fallback targetKey
        if (targetKey !== null && targetKey !== undefined) {
            currentStepKey = String(targetKey);
        }
        let currentIndex = activeSteps.findIndex(s => s.key === currentStepKey);
        if (currentIndex === -1) {
            currentIndex = activeSteps.length - 1;
            currentStepKey = activeSteps[currentIndex].key;
        }

        // Update tabs active / completed states
        stepTabs.forEach(tab => {
            const tabKey = tab.dataset.stepNav;
            const badge = tab.querySelector('.step-badge');
            const stepIndex = activeSteps.findIndex(s => s.key === tabKey);

            if (stepIndex === -1) {
                tab.classList.remove('active', 'completed');
                return;
            }

            const stepDisplayNum = stepIndex + 1;

            if (tabKey === currentStepKey) {
                tab.classList.add('active');
                tab.classList.remove('completed');
                if (badge) badge.innerHTML = stepDisplayNum;
            } else {
                tab.classList.remove('active');
                if (stepIndex < currentIndex) {
                    tab.classList.add('completed');
                    if (badge) badge.innerHTML = '<i class="bi bi-check-lg"></i>';
                } else {
                    tab.classList.remove('completed');
                    if (badge) badge.innerHTML = stepDisplayNum;
                }
            }
        });

        // Update step panes visibility
        stepPanes.forEach(pane => {
            pane.classList.toggle('active', pane.dataset.stepPane === currentStepKey);
        });

        // Update footer controls
        if (btnPrev) {
            btnPrev.style.display = currentIndex > 0 ? 'inline-flex' : 'none';
        }

        const isLastStep = (currentIndex === activeSteps.length - 1);
        if (btnNext && btnFinal) {
            if (isLastStep) {
                btnNext.style.display = 'none';
                btnFinal.style.display = 'inline-flex';
            } else {
                btnNext.style.display = 'inline-flex';
                const nextStepObj = activeSteps[currentIndex + 1];
                btnNext.innerHTML = `Next: ${nextStepObj.name} <i class="bi bi-arrow-right ms-1"></i>`;
                btnFinal.style.display = 'none';
            }
        }

        if (footerIndicator) {
            const currentStepObj = activeSteps[currentIndex];
            footerIndicator.textContent = `Step ${currentIndex + 1} of ${activeSteps.length}: ${currentStepObj.name}`;
        }

        if (shouldScroll) {
            document.querySelector('.webinar-stepper-wrap')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Step validation helper (clean inline validation without native browser popups)
    function validateStep(stepKey) {
        stepKey = String(stepKey);
        let isValid = true;
        const currentPane = document.querySelector(`.wizard-step-pane[data-step-pane="${stepKey}"]`);
        if (!currentPane) return true;

        // Clear existing custom errors in this step
        currentPane.querySelectorAll('.wizard-field-error').forEach(el => el.remove());
        currentPane.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        currentPane.querySelectorAll('.schedule-picker-shell').forEach(el => el.classList.remove('is-invalid'));

        const markInvalid = (input, message) => {
            isValid = false;
            const visibleInput = input._flatpickr?.altInput || input;
            visibleInput.classList.add('is-invalid');
            const shell = visibleInput.closest('.schedule-picker-shell');
            if (shell) shell.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'wizard-field-error text-danger small mt-1 fw-bold';
            err.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${message}`;
            const targetParent = shell || visibleInput.closest('.input-group') || visibleInput;
            targetParent.parentNode.insertBefore(err, targetParent.nextSibling);

            const clearErr = () => {
                visibleInput.classList.remove('is-invalid');
                if (shell) shell.classList.remove('is-invalid');
                err.remove();
            };
            input.addEventListener('input', clearErr, { once: true });
            input.addEventListener('change', clearErr, { once: true });
        };

        if (stepKey === '1') {
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
            const status = currentPane.querySelector('#webinarStatus')?.value;
            if (['scheduled', 'live'].includes(status)) {
                if (startsAt && !startsAt.value) markInvalid(startsAt, 'Scheduled or Live webinar ke liye start date and time required hai.');
                if (endsAt && !endsAt.value) markInvalid(endsAt, 'Scheduled or Live webinar ke liye end date and time required hai.');
            }
            if (startsAt && endsAt && startsAt.value && endsAt.value) {
                if (new Date(endsAt.value) <= new Date(startsAt.value)) {
                    markInvalid(endsAt, 'End date and time must be after the start date and time.');
                }
            }
        }

        if (stepKey === '2') {
            const provider = currentPane.querySelector('#liveProvider');
            const source = currentPane.querySelector('#liveSource');
            if (provider?.value && source && !source.value.trim()) {
                markInvalid(source, 'Video URL, ID, or iframe code is required for the selected player.');
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
            const activeSteps = getActiveSteps();
            const targetKey = tab.dataset.stepNav;
            const targetIdx = activeSteps.findIndex(s => s.key === targetKey);
            const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);

            if (targetIdx === -1) return;
            if (targetIdx > curIdx) {
                if (!validateStep(currentStepKey)) {
                    return;
                }
            }
            renderStep(targetKey, false);
        });
    });

    // Next button: strictly validate before advancing
    btnNext?.addEventListener('click', () => {
        if (!validateStep(currentStepKey)) {
            return;
        }
        const activeSteps = getActiveSteps();
        const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);
        if (curIdx < activeSteps.length - 1) {
            renderStep(activeSteps[curIdx + 1].key, true);
        }
    });

    // Previous button
    btnPrev?.addEventListener('click', () => {
        const activeSteps = getActiveSteps();
        const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);
        if (curIdx > 0) {
            renderStep(activeSteps[curIdx - 1].key, true);
        }
    });

    // Dynamic step updates when toggling Polls or Certificate
    togglePolls?.addEventListener('change', () => renderStep(null, false));
    toggleCert?.addEventListener('change', () => renderStep(null, false));

    // Form submit listener: validate all active steps before submitting
    const form = document.getElementById('webinarForm');
    form?.addEventListener('submit', (e) => {
        const activeSteps = getActiveSteps();
        for (const step of activeSteps) {
            if (!validateStep(step.key)) {
                e.preventDefault();
                e.stopPropagation();
                renderStep(step.key, false);
                const firstInvalid = document.querySelector(`.wizard-step-pane[data-step-pane="${step.key}"] .is-invalid`);
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                return false;
            }
        }
    });

    @if($errors->any())
        const firstErrField = document.querySelector('.wizard-step-pane .is-invalid');
        if (firstErrField) {
            const parentPane = firstErrField.closest('.wizard-step-pane');
            if (parentPane && parentPane.dataset.stepPane) {
                renderStep(parentPane.dataset.stepPane, true);
                firstErrField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    @endif

    // Poll options builder logic
    const pollList = document.getElementById('wizardPollAnswersList');
    const btnAddOption = document.getElementById('addWizardPollAnswer');
    const pollCorrectSelect = document.getElementById('pollCorrectIndex');

    function reindexPollOptions() {
        if (!pollList) return;
        pollList.querySelectorAll('[data-option-row]').forEach((row, index) => {
            const letter = String.fromCharCode(65 + index);
            const badge = row.querySelector('.badge');
            if (badge) badge.textContent = letter;
            const input = row.querySelector('input.form-control');
            if (input && !input.value) input.placeholder = `Option ${letter} text`;
        });
        if (pollCorrectSelect) {
            const selected = pollCorrectSelect.value;
            pollCorrectSelect.innerHTML = '<option value="">No correct answer (standard poll)</option>';
            pollList.querySelectorAll('[data-option-row] input.form-control').forEach((input, index) => {
                const option = document.createElement('option');
                option.value = String(index);
                option.textContent = `Option ${String.fromCharCode(65 + index)}${input.value.trim() ? ` — ${input.value.trim()}` : ''}`;
                option.selected = selected === String(index);
                pollCorrectSelect.appendChild(option);
            });
        }
    }

    btnAddOption?.addEventListener('click', () => {
        if (!pollList) return;
        const currentCount = pollList.querySelectorAll('[data-option-row]').length;
        const letter = String.fromCharCode(65 + currentCount);
        const newRow = document.createElement('div');
        newRow.className = 'wizard-poll-row';
        newRow.dataset.optionRow = '';
        newRow.innerHTML = `
            <span class="badge bg-secondary-subtle text-secondary fw-bold flex-none" style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;">${letter}</span>
            <input class="form-control" name="poll_answers[]" placeholder="Option ${letter} text">
            <button type="button" class="btn btn-sm btn-outline-danger remove-poll-answer" title="Delete option" style="width:36px;height:36px;display:grid;place-items:center;border-radius:8px;">
                <i class="bi bi-trash3"></i>
            </button>
        `;
        pollList.appendChild(newRow);
        newRow.querySelector('input.form-control')?.focus();
        reindexPollOptions();
    });

    pollList?.addEventListener('click', e => {
        const btn = e.target.closest('.remove-poll-answer');
        if (btn) {
            const allRows = pollList.querySelectorAll('[data-option-row]');
            if (allRows.length > 2) {
                btn.closest('[data-option-row]').remove();
                reindexPollOptions();
            } else {
                alert('A poll must have at least 2 answer options.');
            }
        }
    });

    pollList?.addEventListener('input', reindexPollOptions);

    // Dynamic registration field builder
    const dynamicFieldsList = document.getElementById('dynamicFieldsList');
    const addDynamicField = document.getElementById('addDynamicField');
    const reindexDynamicFields = () => dynamicFieldsList?.querySelectorAll('[data-dynamic-field]').forEach((row, index) => {
        row.querySelectorAll('[data-field-name]').forEach(input => input.name = `fields[${index}][${input.dataset.fieldName}]`);
    });
    addDynamicField?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'p-3 border rounded-3 bg-light';
        row.dataset.dynamicField = '';
        row.innerHTML = `<div class="row g-3 align-items-end">
            <div class="col-lg-3"><label class="form-label">Field label</label><input class="form-control" data-field-name="label" placeholder="e.g. Department"></div>
            <div class="col-lg-2"><label class="form-label">Type</label><select class="form-select" data-field-name="field_type"><option value="text">Text</option><option value="dropdown">Dropdown</option><option value="radio">Radio</option><option value="checkbox">Checkboxes</option><option value="country">Country</option><option value="state">State</option><option value="city">City</option></select></div>
            <div class="col-lg-3"><label class="form-label">Placeholder</label><input class="form-control" data-field-name="placeholder"></div>
            <div class="col-lg-3 d-flex gap-3 pb-2"><label class="form-check"><input class="form-check-input" type="checkbox" data-field-name="is_required" value="1"> Required</label><label class="form-check"><input class="form-check-input" type="checkbox" data-field-name="is_enabled" value="1" checked> Active</label></div>
            <div class="col-lg-1 text-end"><button type="button" class="btn btn-outline-danger" data-remove-dynamic-field><i class="bi bi-trash3"></i></button></div>
            <div class="col-12"><label class="form-label">Options <small class="text-muted">(one per line)</small></label><textarea class="form-control" rows="2" data-field-name="options"></textarea></div>
        </div>`;
        dynamicFieldsList?.appendChild(row);
        reindexDynamicFields();
        row.querySelector('[data-field-name="label"]')?.focus();
    });
    dynamicFieldsList?.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-dynamic-field]');
        if (!button) return;
        button.closest('[data-dynamic-field]')?.remove();
        reindexDynamicFields();
    });

    // Certificate preview live sync
    const certHeadlineInput = document.getElementById('certificateHeadline');
    const certSignatoryInput = document.getElementById('certificateSignatory');
    const certPreviewHeadlineWatermark = document.getElementById('certPreviewHeadlineWatermark');
    const certPreviewSignatory = document.getElementById('certPreviewSignatoryText');
    const certPreviewWebinar = document.getElementById('certPreviewWebinarTitle');

    certHeadlineInput?.addEventListener('input', () => {
        if (certPreviewHeadlineWatermark) certPreviewHeadlineWatermark.textContent = certHeadlineInput.value.trim() || 'Certificate of Completion';
    });
    certSignatoryInput?.addEventListener('input', () => {
        if (certPreviewSignatory) certPreviewSignatory.textContent = certSignatoryInput.value.trim() || 'Authorized Director';
    });

    // When selecting saved template, populate headline, signatory, X/Y positions & images
    const certTplSelect = document.getElementById('certTemplateSelect');
    certTplSelect?.addEventListener('change', () => {
        const opt = certTplSelect.options[certTplSelect.selectedIndex];
        if (!opt) return;
        if (opt.value !== 'custom') {
            if (opt.dataset.headline && certHeadlineInput) {
                certHeadlineInput.value = opt.dataset.headline;
                if (certPreviewHeadlineWatermark) certPreviewHeadlineWatermark.textContent = opt.dataset.headline;
            }
            if (opt.dataset.signatory && certSignatoryInput) {
                certSignatoryInput.value = opt.dataset.signatory;
                if (certPreviewSignatory) certPreviewSignatory.textContent = opt.dataset.signatory;
            }
            if (opt.dataset.positions) {
                try {
                    const pos = JSON.parse(opt.dataset.positions);
                    Object.entries(pos).forEach(([k, v]) => {
                        if (window.setCertificatePosition && v.x !== undefined && v.y !== undefined) {
                            window.setCertificatePosition(k, v.x, v.y);
                        }
                        if (window.setCertificateSize && v.width !== undefined && v.scale !== undefined) {
                            window.setCertificateSize(k, v.width, v.scale);
                        }
                    });
                    const curKey = document.querySelector('#certificateElementSelect')?.value;
                    if (curKey && window.selectCertificateElement) {
                        window.selectCertificateElement(curKey);
                    }
                } catch(e) {}
            }
            const canvas = document.querySelector('#certificateCanvas');
            if (canvas) {
                canvas.style.backgroundImage = opt.dataset.image ? `url('${opt.dataset.image}')` : 'none';
                if (opt.dataset.aspect) canvas.style.aspectRatio = opt.dataset.aspect;
            }
            if (opt.dataset.visibility) {
                try {
                    const visibility = JSON.parse(opt.dataset.visibility);
                    Object.entries(visibility).forEach(([key, shown]) => {
                        const checkbox = document.querySelector(`[data-certificate-visibility="${key}"]`);
                        if (checkbox) {
                            checkbox.checked = Boolean(shown);
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    });
                } catch (e) {}
            }
            const sigEl = document.querySelector('[data-certificate-element="signature"]');
            const sigImg = document.querySelector('#signaturePreview');
            if (sigEl && sigImg) {
                const signatureVisible = document.querySelector('[data-certificate-visibility="signature"]')?.checked ?? true;
                if (opt.dataset.signature && signatureVisible) {
                    sigImg.src = opt.dataset.signature;
                    sigEl.classList.remove('d-none');
                } else {
                    sigImg.src = opt.dataset.signature || '';
                    sigEl.classList.add('d-none');
                }
            }
        }
    });

    // Reset Coordinates to defaults
    document.getElementById('resetCertCoordinatesBtn')?.addEventListener('click', () => {
        const defs = {
            recipient: { x: 50, y: 44, width: 55, scale: 100 },
            webinar: { x: 50, y: 61, width: 55, scale: 100 },
            date: { x: 20, y: 84, width: 25, scale: 100 },
            signature: { x: 80, y: 76, width: 22, scale: 100 },
            signatory: { x: 80, y: 86, width: 30, scale: 100 }
        };
        Object.entries(defs).forEach(([k, v]) => {
            if (window.setCertificatePosition) window.setCertificatePosition(k, v.x, v.y);
            if (window.setCertificateSize) window.setCertificateSize(k, v.width, v.scale);
        });
        const curKey = document.querySelector('#certificateElementSelect')?.value;
        if (curKey && window.selectCertificateElement) {
            window.selectCertificateElement(curKey);
        }
    });

    // Initial render on load: check if errors exist in any pane, else Step 1
    const firstInvalid = document.querySelector('.is-invalid, .invalid-feedback');
    if (firstInvalid) {
        const errorPane = firstInvalid.closest('.wizard-step-pane');
        if (errorPane && errorPane.dataset.stepPane) {
            renderStep(errorPane.dataset.stepPane, false);
        } else {
            renderStep('1', false);
        }
    } else {
        renderStep('1', false);
    }

    // -------------------------------------------------------------
    // Real-time Date Range Validation (ends_at must be after starts_at)
    // -------------------------------------------------------------
    const startsInput = document.querySelector('#webinarStartsAt');
    const endsInput = document.querySelector('#webinarEndsAt');

    function formatDatetimeLocal(d) {
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function checkDateOrder(isStartsChange = false) {
        if (!startsInput || !endsInput) return;
        if (startsInput.value) {
            endsInput.min = startsInput.value;
            const startDate = new Date(startsInput.value);
            if (isStartsChange && (!endsInput.value || new Date(endsInput.value) <= startDate)) {
                const autoEnd = new Date(startDate.getTime() + 60 * 60 * 1000);
                endsInput.value = formatDatetimeLocal(autoEnd);
                if (endsInput._flatpickr) {
                    endsInput._flatpickr.setDate(autoEnd, false);
                }
            }
        }
        const endsShell = endsInput.closest('.schedule-picker-shell') || endsInput;
        const visibleEnds = endsInput._flatpickr?.altInput || endsInput;
        const formField = endsInput.closest('.form-field') || endsShell.parentNode;
        let err = formField.querySelector('.wizard-field-error-date');

        if (startsInput.value && endsInput.value) {
            const startDate = new Date(startsInput.value);
            const endDate = new Date(endsInput.value);
            if (endDate <= startDate) {
                endsInput.classList.add('is-invalid');
                visibleEnds.classList.add('is-invalid');
                endsShell.classList.add('is-invalid');
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'wizard-field-error wizard-field-error-date text-danger small mt-1 fw-bold';
                    endsShell.parentNode.insertBefore(err, endsShell.nextSibling);
                }
                err.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> End date and time must be after the start date and time.';
            } else {
                endsInput.classList.remove('is-invalid');
                visibleEnds.classList.remove('is-invalid');
                endsShell.classList.remove('is-invalid');
                if (err) err.remove();
            }
        } else {
            endsInput.classList.remove('is-invalid');
            visibleEnds.classList.remove('is-invalid');
            endsShell.classList.remove('is-invalid');
            if (err) err.remove();
        }
    }

    startsInput?.addEventListener('change', () => checkDateOrder(true));
    endsInput?.addEventListener('change', () => checkDateOrder(false));
    endsInput?.addEventListener('input', () => checkDateOrder(false));

    const timezoneInput = document.querySelector('#webinarTimezone');
    const schedulePreview = document.querySelector('#scheduleTimezonePreview');
    const languageInput = document.querySelector('#webinarLanguage');

    document.querySelectorAll('[data-open-picker]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.openPicker);
            if (!input) return;
            if (input._flatpickr) input._flatpickr.open();
            else if (typeof input.showPicker === 'function') input.showPicker();
            else input.focus();
        });
    });

    function readableLocalDate(value) {
        if (!value) return null;
        const [date, time] = value.split('T');
        const [year, month, day] = date.split('-').map(Number);
        const [hour, minute] = time.split(':').map(Number);
        return new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
            year: 'numeric', month: 'short', day: '2-digit', hour: 'numeric', minute: '2-digit'
        }).format(new Date(year, month - 1, day, hour, minute));
    }

    function updateSchedulePreview() {
        if (!schedulePreview) return;
        const timezone = timezoneInput?.value || 'UTC';
        document.querySelectorAll('[data-schedule-timezone]').forEach(chip => {
            chip.innerHTML = `<i class="bi bi-globe2"></i> ${timezone}`;
        });
        const start = readableLocalDate(startsInput?.value);
        const end = readableLocalDate(endsInput?.value);
        const language = languageInput?.selectedOptions?.[0]?.text || 'English';
        schedulePreview.innerHTML = start
            ? `<i class="bi bi-clock-history me-1"></i> <strong>${start}${end ? ` – ${end}` : ''}</strong> in <strong>${timezone}</strong> · ${language}`
            : `Select a start time to preview the webinar schedule in <strong>${timezone}</strong>.`;
    }

    [startsInput, endsInput, timezoneInput, languageInput].forEach(input => {
        input?.addEventListener('change', updateSchedulePreview);
        input?.addEventListener('input', updateSchedulePreview);
    });
    updateSchedulePreview();

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
    const liveSourceInput = document.querySelector('#liveSource');
    const liveSourceRequiredMark = document.querySelector('#liveSourceRequiredMark');
    const liveSourceHelp = document.querySelector('#liveSourceHelp');

    function syncLiveSourceRequirement() {
        const provider = liveProviderSelect?.value || '';
        const isRequired = provider !== '';
        if (liveSourceInput) {
            liveSourceInput.required = isRequired;
            liveSourceInput.setAttribute('aria-required', isRequired ? 'true' : 'false');
            liveSourceInput.placeholder = isRequired
                ? `Paste ${provider === 'custom' ? 'a secure iframe URL or iframe code' : `${provider[0].toUpperCase() + provider.slice(1)} URL or ID`}`
                : 'No player selected — video source is not required';
        }
        if (liveSourceRequiredMark) liveSourceRequiredMark.hidden = !isRequired;
        if (liveSourceHelp) {
            liveSourceHelp.textContent = isRequired
                ? 'Required: valid source dene par secure responsive player automatically configure hoga.'
                : 'No Player mode mein video source required nahi hai.';
        }
    }

    providerRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (liveProviderSelect) {
                liveProviderSelect.value = radio.value;
                liveProviderSelect.dispatchEvent(new Event('change'));
            }
            syncLiveSourceRequirement();
        });
    });
    syncLiveSourceRequirement();

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
