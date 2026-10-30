@extends('layouts.portal')
@section('title', 'Add Notification')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Add Notification</h1>
        </div><a class="btn btn-light" href="{{ route('admin.notifications.index') }}">Back</a>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form class="panel-card form-panel notification-form-panel" method="POST" enctype="multipart/form-data"
        action="{{ route('admin.notifications.store') }}" data-notification-builder>@csrf<div class="form-grid">
            <label><span class="notification-label">Delivery trigger <span class="text-danger">*</span></span><select
                    class="form-select" name="delivery_mode" id="notificationDelivery" required>
                    <option value="reminder_now" @selected(old('delivery_mode', 'reminder_now') === 'reminder_now')>Reminder now</option>
                    <option value="scheduled_reminder" @selected(old('delivery_mode') === 'scheduled_reminder')>Schedule reminder</option>
                    <option value="after_registration_email" @selected(old('delivery_mode') === 'after_registration_email')>After registration — send email
                    </option>
                    <option value="save_template" @selected(old('delivery_mode') === 'save_template')>Save as template — do not send</option>
                </select><small class="text-muted" data-delivery-help></small></label>
            <label><span class="notification-label">Send using <span class="text-danger">*</span></span><select
                    class="form-select" name="channel" id="notificationChannel" required>
                    <option value="email" @selected(old('channel', 'email') === 'email')>Email</option>
                    <option value="whatsapp" @selected(old('channel') === 'whatsapp')>WhatsApp</option>
                </select><small class="text-muted" data-channel-help></small></label>
            <label><span class="notification-label">Audience <span class="text-danger">*</span></span><select
                    class="form-select" name="audience" id="notificationAudience" required>
                    <option value="all_registrations" @selected(old('audience', 'all_registrations') === 'all_registrations')>All registered attendees</option>
                    <option value="webinar" @selected(old('audience') === 'webinar')>Registered for a webinar</option>
                </select></label>
            <label data-webinar-field><span class="notification-label">Webinar <span class="text-danger"
                        data-webinar-required>*</span></span><select class="form-select" name="webinar_id"
                    id="notificationWebinar">
                    <option value="">Select webinar</option>
                    @foreach ($webinars as $webinar)
                        <option value="{{ $webinar->id }}" data-recipient-count="{{ $webinar->registrations_count }}" data-timezone="{{ $webinar->timezone }}" data-start="{{ $webinar->starts_at?->timezone($webinar->timezone)->format('M d, Y · g:i A') }}" @selected(old('webinar_id') == $webinar->id)>{{ $webinar->title }}</option>
                    @endforeach
                </select><small class="text-muted">Required for webinar-specific reminders and registration
                    emails.</small></label>
            <label data-scheduled-field hidden><span class="notification-label">Send at <span class="text-danger">*</span></span><input
                    class="form-control" type="datetime-local" name="scheduled_at" id="notificationScheduledAt"
                    value="{{ old('scheduled_at') }}"><small class="text-muted">Uses the selected webinar timezone. Due email reminders are processed every minute.</small></label>
            <label class="full"><span class="notification-label">Subject <span class="text-danger">*</span></span><input
                    class="form-control" name="subject" id="notificationSubject" value="{{ old('subject') }}" maxlength="255" required
                    placeholder="e.g. Your webinar starts shortly"></label>
            <label class="full"><span class="notification-label">Message <span class="text-danger">*</span></span>
                <textarea class="form-control" name="message" id="notificationMessage" rows="9" maxlength="20000" required>{{ old('message') }}</textarea>
                <div class="invalid-feedback" data-message-error>Please enter a message.</div><small class="text-muted">Rich
                    text is enabled. You can use <strong>{name}</strong>, <strong>{webinar}</strong> and
                    <strong>{date}</strong>.</small>
            </label>
            <label class="full">Attachment <span class="text-muted fw-normal">(optional)</span><input class="form-control"
                    type="file" name="attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"><small
                    class="text-muted">Image or document up to 10 MB. Email receives the file; WhatsApp receives its
                    link.</small></label>
        </div>
        <section class="mt-4 p-3 rounded-3 border bg-light" aria-label="Notification review">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div><small class="text-muted d-block">Eligible recipients</small><strong data-recipient-summary data-all-recipients="{{ $allRecipients }}">{{ number_format($allRecipients) }} people</strong></div>
                <div><small class="text-muted d-block">Timing and timezone</small><strong data-timing-summary>Send immediately</strong></div>
                <a class="btn btn-sm btn-outline-secondary align-self-center" href="{{ route('admin.notifications.email-logs') }}"><i class="bi bi-clock-history me-1"></i> Delivery logs</a>
            </div>
            <div class="mt-3 p-3 bg-white border rounded-3" aria-live="polite">
                <small class="text-muted text-uppercase fw-bold">Attendee preview - no message is sent</small>
                <h3 class="h6 mt-2 mb-2" data-preview-subject>Your subject will appear here</h3>
                <p class="mb-0 text-muted" style="white-space:pre-wrap" data-preview-message>Hello Sample Attendee, your message preview will appear here.</p>
            </div>
        </section>
        <button class="btn btn-gradient mt-4" data-submit-label><i class="bi bi-envelope-check"></i> Send email now</button></form>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-notification-builder]');
            const delivery = document.querySelector('#notificationDelivery');
            const channel = document.querySelector('#notificationChannel');
            const audience = document.querySelector('#notificationAudience');
            const webinar = document.querySelector('#notificationWebinar');
            const webinarField = document.querySelector('[data-webinar-field]');
            const webinarRequired = document.querySelector("[data-webinar-required]");
            const scheduledField = document.querySelector("[data-scheduled-field]");
            const scheduledAt = document.querySelector("#notificationScheduledAt");
            const deliveryHelp = document.querySelector('[data-delivery-help]');
            const channelHelp = document.querySelector('[data-channel-help]');
            const submit = document.querySelector('[data-submit-label]');
            const messageError = document.querySelector('[data-message-error]');
            const subject = document.querySelector('#notificationSubject');
            const previewSubject = document.querySelector('[data-preview-subject]');
            const previewMessage = document.querySelector('[data-preview-message]');
            const recipientSummary = document.querySelector('[data-recipient-summary]');
            const timingSummary = document.querySelector('[data-timing-summary]');
            let editorInstance = null;
            const sync = () => {
                const afterRegistration = delivery.value === 'after_registration_email';
                const scheduledReminder = delivery.value === "scheduled_reminder";
                const saveTemplate = delivery.value === "save_template";
                if (afterRegistration || scheduledReminder) {
                    channel.value = "email";
                    channel.disabled = true;
                    if (afterRegistration) {
                        audience.value = "webinar";
                        audience.disabled = true;
                    } else {
                        audience.disabled = false;
                    }
                } else {
                    channel.disabled = false;
                    audience.disabled = false;
                }
                webinarField.hidden = audience.value !== 'webinar' && !afterRegistration;
                webinar.required = audience.value === 'webinar' || afterRegistration;
                webinarRequired.hidden = !webinar.required;
                scheduledField.hidden = !scheduledReminder;
                scheduledAt.required = scheduledReminder;
                deliveryHelp.textContent = saveTemplate
                    ? "Only the reusable template will be saved. Nothing will be sent."
                    : (afterRegistration ? "Email is sent immediately after every new registration."
                        : (scheduledReminder ? "Email will be sent automatically at the selected date and time."
                            : "This communication is prepared or sent immediately."));
                channelHelp.textContent = channel.value === "whatsapp"
                    ? "A recipient queue opens with prefilled WhatsApp messages."
                    : (scheduledReminder ? "Scheduled reminders are delivered automatically by email." : "The email is sent immediately when you submit.");
                const selectedEvent = webinar.selectedOptions[0];
                const recipientCount = audience.value === 'webinar' ? Number(selectedEvent?.dataset.recipientCount || 0) : Number(recipientSummary.dataset.allRecipients || 0);
                recipientSummary.textContent = `${recipientCount.toLocaleString()} ${recipientCount === 1 ? 'person' : 'people'}`;
                const eventTimezone = selectedEvent?.dataset.timezone || @json(config('app.timezone'));
                timingSummary.textContent = scheduledReminder && scheduledAt.value
                    ? `${scheduledAt.value.replace('T', ' ')} (${eventTimezone})`
                    : (afterRegistration ? `After registration (${eventTimezone})` : (saveTemplate ? 'Not scheduled' : 'Send immediately'));
                submit.innerHTML = saveTemplate ? "<i class=\"bi bi-bookmark-check\"></i> Save template"
                    : (afterRegistration ? "<i class=\"bi bi-envelope-check\"></i> Save registration email"
                        : (scheduledReminder ? "<i class=\"bi bi-clock-history\"></i> Schedule email"
                            : (channel.value === "whatsapp" ? "<i class=\"bi bi-whatsapp\"></i> Prepare WhatsApp queue"
                                : "<i class=\"bi bi-envelope-check\"></i> Send email now")));
            };
            delivery.addEventListener('change', sync);
            channel.addEventListener('change', sync);
            audience.addEventListener('change', sync);
            webinar.addEventListener('change', sync);
            scheduledAt.addEventListener('input', sync);

            const updatePreview = () => {
                previewSubject.textContent = subject.value.trim() || 'Your subject will appear here';
                const html = editorInstance ? editorInstance.getData() : document.querySelector('#notificationMessage').value;
                const holder = document.createElement('div'); holder.innerHTML = html;
                previewMessage.textContent = holder.textContent.trim() || 'Hello Sample Attendee, your message preview will appear here.';
            };
            subject.addEventListener('input', updatePreview);
            document.querySelector('#notificationMessage').addEventListener('input', updatePreview);
            sync();
            updatePreview();
            const validateMessage = () => {
                const html = editorInstance ? editorInstance.getData() : document.querySelector(
                    '#notificationMessage').value;
                const blank = !html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                const editable = document.querySelector('.ck-editor__editable');
                editable?.classList.toggle('is-invalid', blank);
                document.querySelector('#notificationMessage').classList.toggle('is-invalid', blank);
                messageError.style.display = blank ? 'block' : '';
                return !blank;
            };
            form.addEventListener('submit', event => {
                if (!validateMessage()) {
                    event.preventDefault();
                    event.stopPropagation();
                    document.querySelector('.ck-editor__editable, #notificationMessage')?.focus();
                }
            });
            if (window.ClassicEditor) {
                ClassicEditor.create(document.querySelector('#notificationMessage'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                        'blockQuote', 'undo', 'redo'
                    ]
                }).then(editor => {
                    editorInstance = editor;
                    editor.model.document.on('change:data', () => { validateMessage(); updatePreview(); });
                    updatePreview();
                }).catch(() => {});
            }
        });
    </script>
    <style>
        .notification-form-panel {
            width: 100%;
            max-width: none
        }

        .notification-label {
            display: inline-flex;
            align-items: center;
            gap: 4px
        }

        .notification-label small {
            font-weight: 500;
            color: var(--muted)
        }

        .notification-form-panel .ck-editor__editable_inline {
            min-height: 280px;
            max-height: 520px;
            overflow-y: auto
        }

        .notification-form-panel .ck-editor__editable_inline.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .1)
        }

        .notification-form-panel #notificationMessage {
            min-height: 280px
        }

        @media(max-width:767.98px) {

            .notification-form-panel .ck-editor__editable_inline,
            .notification-form-panel #notificationMessage {
                min-height: 220px
            }
        }
    </style>
@endsection
