@extends('layouts.portal')
@section('title',$webinar->exists?'Edit Webinar':'Create Webinar')
@section('content')
<style>.live-embed-preview{min-height:290px;display:grid;place-items:center;overflow:hidden;border:2px dashed #d9deea;border-radius:16px;background:#0f172a;color:#94a3b8}.live-embed-preview iframe{width:100%;aspect-ratio:16/9;border:0}.live-embed-preview>div{display:grid;justify-items:center;gap:10px}.live-embed-preview i{font-size:3rem;color:#8b5cf6}</style>
<div class="page-heading"><div><span class="eyebrow">ADMIN WORKSPACE</span><h1>{{ $webinar->exists?'Edit webinar':'Create webinar' }}</h1><p>Manage only the webinar's core information here.</p></div><a class="btn btn-light" href="{{ route('admin.webinars.index') }}">Back to webinars</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the form.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $webinar->exists?route('admin.webinars.update',$webinar):route('admin.webinars.store') }}">@csrf @if($webinar->exists)@method('PUT')@endif
<section class="panel-card mb-4"><div class="form-grid"><label>Webinar icon<select class="form-select" name="icon">@foreach(['camera-video'=>'Video camera','broadcast'=>'Broadcast','people'=>'Community','mortarboard'=>'Education','lightbulb'=>'Ideas','cpu'=>'Technology','heart-pulse'=>'Healthcare','graph-up'=>'Business growth','megaphone'=>'Marketing'] as $icon=>$label)<option value="{{ $icon }}" @selected(old('icon',$webinar->icon?:'camera-video')===$icon)>{{ $label }}</option>@endforeach</select></label><div><span class="form-label d-block">Icon preview</span><span class="stat-icon tone-purple"><i class="bi bi-{{ old('icon',$webinar->icon?:'camera-video') }}"></i></span></div></div></section>
<section class="panel-card"><div class="panel-title"><div><h3>Webinar information</h3><p>Polls, certificates and registration forms are managed from their own modules.</p></div></div><div class="form-grid">
<label class="full">Title<input class="form-control" id="webinarTitle" name="title" value="{{ old('title',$webinar->title) }}" required></label>
<label class="full">Public URL slug<div class="input-group"><span class="input-group-text">/webinars/</span><input class="form-control" id="webinarSlug" name="slug" value="{{ old('slug',$webinar->slug) }}" placeholder="Generated automatically from title" pattern="[a-z0-9]+(?:-[a-z0-9]+)*"></div><small class="text-muted">Title type ya edit karte hi slug automatically update hoga. Zarurat ho to slug manually bhi edit kar sakte hain.</small></label>
<label>Status<select class="form-select" name="status">@foreach(['draft','scheduled','live','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status',$webinar->status?:'draft')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
<label>Language<input class="form-control" name="language" value="{{ old('language',$webinar->language?:'en') }}" required></label>
<label>Timezone<input class="form-control" name="timezone" value="{{ old('timezone',$webinar->timezone?:'Asia/Kolkata') }}" required></label>
<label>Maximum attendees<input class="form-control" type="number" min="1" name="max_attendees" value="{{ old('max_attendees',$webinar->max_attendees) }}"></label>
<label>Starts at<input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at',$webinar->starts_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}"><small class="text-muted">Time is entered in the selected webinar timezone.</small></label>
<label>Ends at<input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at',$webinar->ends_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}"></label>
<label>Registration type<select class="form-select" name="registration_type" id="registrationType"><option value="free" @selected(old('registration_type',$webinar->registration_type?:'free')==='free')>Free</option><option value="paid" @selected(old('registration_type',$webinar->registration_type)==='paid')>Paid</option></select></label>
<label id="priceField">Price<input class="form-control" type="number" min="0" step="0.01" name="price" id="webinarPrice" value="{{ old('price',$webinar->price) }}"></label>
<label class="full">Short description<textarea class="form-control" name="short_description" rows="2">{{ old('short_description',$webinar->short_description) }}</textarea></label>
<label class="full">Description<textarea class="form-control" name="description" rows="5">{{ old('description',$webinar->description) }}</textarea></label>
</div></section>
<section class="panel-card mt-4"><div class="panel-title"><div><h3>Access and live experience</h3><p>Only webinar-level access controls are configured here.</p></div></div><div class="form-grid">
<label>Early room access (minutes)<input class="form-control" type="number" min="0" max="240" name="early_entry_minutes" value="{{ old('early_entry_minutes',$webinar->early_entry_minutes ?? 30) }}" required><small class="text-muted">How early approved learners can enter.</small></label><div></div>
<label>Video player<select class="form-select" name="live_provider" id="liveProvider"><option value="">No embedded player</option><option value="youtube" @selected(old('live_provider',$webinar->live_provider)==='youtube')>YouTube</option><option value="vimeo" @selected(old('live_provider',$webinar->live_provider)==='vimeo')>Vimeo</option><option value="custom" @selected(old('live_provider',$webinar->live_provider)==='custom')>Custom iframe URL</option></select></label>
<label class="full" id="liveSourceField">Video URL, ID, or iframe code<textarea class="form-control" name="live_source" id="liveSource" rows="3" placeholder="Paste YouTube/Vimeo URL, video ID, or iframe code">{{ old('live_source',$webinar->live_url) }}</textarea><small class="text-muted">The secure iframe is generated automatically.</small></label>
<div class="full live-embed-preview" id="liveEmbedPreview"><div><i class="bi bi-play-btn"></i><span>Select a player and paste the video source to preview it.</span></div></div>
<label class="full">Session resources<textarea class="form-control" name="session_resources" rows="4" placeholder="Session guide | https://example.com/guide.pdf&#10;Presentation slides | https://example.com/slides.pdf">{{ old('session_resources',$sessionResourcesText) }}</textarea><small class="text-muted">Add one public resource per line in <strong>Title | URL</strong> format. It appears on the landing page and inside the webinar room.</small></label>
<label class="full">Upload PDF resources<input class="form-control" type="file" name="resource_pdfs[]" accept="application/pdf,.pdf" multiple><small class="text-muted">Choose one or more PDF files. Uploaded PDFs appear in the same Session Resources area on both the landing page and webinar room.</small></label>
<div class="full agenda-builder">
    <input type="hidden" name="agenda_present" value="1">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div><strong>Webinar agenda</strong><small class="d-block text-muted">Optional. Empty agenda dashboard par nahi dikhega.</small></div>
        <button class="btn btn-sm btn-light" type="button" id="addAgendaItem"><i class="bi bi-plus-lg"></i> Add agenda item</button>
    </div>
    @php($agendaRows=collect(old('agenda',$agendaItems->map(fn($item)=>['starts_at'=>$item->starts_at?substr($item->starts_at,0,5):'','title'=>$item->title,'duration_minutes'=>$item->duration_minutes])->all())))
    <div class="agenda-builder-rows" id="agendaBuilderRows">
        @foreach($agendaRows as $index=>$item)
            <div class="agenda-builder-row" data-agenda-row>
                <label>Time<input class="form-control" type="time" name="agenda[{{ $index }}][starts_at]" value="{{ $item['starts_at']??'' }}"></label>
                <label>Title<input class="form-control" name="agenda[{{ $index }}][title]" value="{{ $item['title']??'' }}" maxlength="255" placeholder="Session title"></label>
                <label>Minutes<input class="form-control" type="number" min="1" max="1440" name="agenda[{{ $index }}][duration_minutes]" value="{{ $item['duration_minutes']??'' }}" placeholder="30"></label>
                <button class="icon-btn text-danger" type="button" data-delete-agenda title="Delete agenda item"><i class="bi bi-trash3"></i></button>
            </div>
        @endforeach
    </div>
</div>
<label class="setting-toggle"><span><strong>Live chat</strong><small>Show chat in the webinar room.</small></span><input type="checkbox" name="chat_enabled" value="1" @checked(old('chat_enabled',$webinar->chat_enabled ?? true))></label>
<label class="setting-toggle"><span><strong>Comments</strong><small>Let attendees send private comments to the host.</small></span><input type="checkbox" name="comments_enabled" value="1" @checked(old('comments_enabled',$webinar->comments_enabled ?? true))></label>
<label class="setting-toggle"><span><strong>Feedback</strong><small>Collect ratings and show it in Feedback module.</small></span><input type="checkbox" name="feedback_enabled" value="1" @checked(old('feedback_enabled',$webinar->feedback_enabled ?? false))></label>
</div></section>
@php($experience=(array)old('experience',data_get($webinar->settings,'experience',[])))
<section class="panel-card mt-4"><div class="panel-title"><div><h3>Experience studio</h3><p>Brand the room and control what attendees see before, during and after the webinar.</p></div><span class="status-badge scheduled"><i class="bi bi-stars"></i> Live preview</span></div><div class="form-grid">
<label>Room layout<select class="form-select" name="room_layout" id="roomLayout">@foreach(['theater'=>'Theater','presentation'=>'Presentation + sidebar','interview'=>'Interview','panel'=>'Panel discussion'] as $value=>$label)<option value="{{ $value }}" @selected(old('room_layout',$experience['layout']??'presentation')===$value)>{{ $label }}</option>@endforeach</select></label>
<label>Registration template<select class="form-select" name="registration_preset">@foreach(['custom'=>'Custom','business'=>'Business','education'=>'Education','healthcare'=>'Healthcare','marketing'=>'Marketing'] as $value=>$label)<option value="{{ $value }}" @selected(old('registration_preset',$experience['registration_preset']??'custom')===$value)>{{ $label }}</option>@endforeach</select></label>
<div class="full theme-studio"><div><strong>Theme preset</strong><small>Choose a ready style or enter a HEX color directly.</small></div><div class="theme-presets"><button type="button" data-theme-preset="#6d28d9|#2563eb"><i style="--a:#6d28d9;--b:#2563eb"></i>Signature</button><button type="button" data-theme-preset="#0f766e|#22c55e"><i style="--a:#0f766e;--b:#22c55e"></i>Healthcare</button><button type="button" data-theme-preset="#0f172a|#f59e0b"><i style="--a:#0f172a;--b:#f59e0b"></i>Luxury</button><button type="button" data-theme-preset="#be123c|#f97316"><i style="--a:#be123c;--b:#f97316"></i>Energy</button><button type="button" data-theme-preset="#1d4ed8|#06b6d4"><i style="--a:#1d4ed8;--b:#06b6d4"></i>Corporate</button></div><div class="theme-color-grid"><label><span>Primary color</span><span class="professional-color"><i class="color-swatch" data-color-swatch="primary"></i><input class="color-hex-input" type="text" name="brand_primary" id="brandPrimary" value="{{ old('brand_primary',$experience['primary']??'#6d28d9') }}" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#6D28D9"></span></label><label><span>Accent color</span><span class="professional-color"><i class="color-swatch" data-color-swatch="secondary"></i><input class="color-hex-input" type="text" name="brand_secondary" id="brandSecondary" value="{{ old('brand_secondary',$experience['secondary']??'#2563eb') }}" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#2563EB"></span></label></div></div>
<label class="full">Room logo URL<input class="form-control" type="url" name="brand_logo_url" value="{{ old('brand_logo_url',$experience['logo_url']??'') }}" placeholder="https://example.com/logo.png"></label>
<label class="full">Waiting room message<textarea class="form-control" name="waiting_message" rows="2">{{ old('waiting_message',$experience['waiting_message']??'The session will begin shortly. You are in the right place.') }}</textarea></label><label class="full">Waiting room media URL<input class="form-control" type="url" name="waiting_media_url" value="{{ old('waiting_media_url',$experience['waiting_media_url']??'') }}" placeholder="Optional image or video URL"></label>
<label class="full">Post-webinar message<textarea class="form-control" name="post_message" rows="2">{{ old('post_message',$experience['post_message']??'Thank you for attending. Please share your feedback.') }}</textarea></label>
<label>Registration success title<input class="form-control" name="registration_success_title" value="{{ old('registration_success_title',$experience['registration_success_title']??'You are registered!') }}"></label><label>Registration success message<textarea class="form-control" name="registration_success_message" rows="3">{{ old('registration_success_message',$experience['registration_success_message']??'Your seat is confirmed. Add the webinar to your calendar and return when the room opens.') }}</textarea></label>
<details class="full chapter-builder" @if(!empty($experience['chapters'])) open @endif><summary><span><i class="bi bi-list-ol"></i><strong>Video chapter navigation</strong><small>Optional · Let attendees jump directly to important parts of the video.</small></span><i class="bi bi-chevron-down"></i></summary><div><label>Chapter timestamps<textarea class="form-control" name="video_chapters" rows="4" placeholder="00:00 | Welcome&#10;12:30 | Main topic&#10;45:00 | Questions and answers">{{ old('video_chapters',collect($experience['chapters']??[])->map(fn($item)=>($item['time']??'').' | '.($item['title']??''))->join("\n")) }}</textarea></label><div class="chapter-help"><i class="bi bi-info-circle"></i><span><strong>How it works</strong> Add one chapter per line using <code>time | title</code>. In the attendee room, clicking a chapter moves the YouTube or Vimeo player to that timestamp.</span></div></div></details>
<label>Certificate minimum attendance (%)<input class="form-control" type="number" min="0" max="100" name="certificate_min_attendance" value="{{ old('certificate_min_attendance',$experience['certificate_min_attendance']??80) }}"></label><label class="setting-toggle"><span><strong>Require poll participation</strong><small>Attendee must answer at least one poll.</small></span><input type="checkbox" name="certificate_require_poll" value="1" @checked(old('certificate_require_poll',$experience['certificate_require_poll']??false))></label>
<div class="full rounded-4 p-4 text-white" id="experiencePreview" style="background:linear-gradient(135deg,var(--preview-primary,#6d28d9),var(--preview-secondary,#2563eb))"><small>ATTENDEE ROOM PREVIEW</small><h3 class="mt-2 mb-1">{{ $webinar->title ?: 'Your webinar title' }}</h3><span data-layout-preview>Presentation + sidebar layout</span></div>
</div></section>
<div class="d-flex justify-content-end mt-4"><button class="btn btn-gradient btn-lg">{{ $webinar->exists?'Save webinar':'Create webinar' }}</button></div>
</form>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const agendaRows=document.querySelector('#agendaBuilderRows');
    const addAgenda=document.querySelector('#addAgendaItem');
    const bindAgendaDelete=row=>row.querySelector('[data-delete-agenda]')?.addEventListener('click',()=>row.remove());
    agendaRows?.querySelectorAll('[data-agenda-row]').forEach(bindAgendaDelete);
    addAgenda?.addEventListener('click',()=>{
        const index=Date.now();
        const row=document.createElement('div');
        row.className='agenda-builder-row';row.dataset.agendaRow='';
        row.innerHTML=`<label>Time<input class="form-control" type="time" name="agenda[${index}][starts_at]"></label><label>Title<input class="form-control" name="agenda[${index}][title]" maxlength="255" placeholder="Session title"></label><label>Minutes<input class="form-control" type="number" min="1" max="1440" name="agenda[${index}][duration_minutes]" placeholder="30"></label><button class="icon-btn text-danger" type="button" data-delete-agenda title="Delete agenda item"><i class="bi bi-trash3"></i></button>`;
        agendaRows.appendChild(row);bindAgendaDelete(row);row.querySelector('input[type="time"]').focus();
    });
    const title=document.querySelector('#webinarTitle');
    const slug=document.querySelector('#webinarSlug');
    if(!title||!slug)return;
    let manuallyEdited=false;
    const makeSlug=value=>value.toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,180);
    title.addEventListener('input',()=>{if(!manuallyEdited)slug.value=makeSlug(title.value)});
    slug.addEventListener('input',event=>{manuallyEdited=event.isTrusted;slug.value=makeSlug(slug.value)});
});
</script>
@endsection
