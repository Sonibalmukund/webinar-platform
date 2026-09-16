@extends('layouts.portal')
@section('title', 'Audience Journey - ' . ($registration->user?->name ?? $registration->email))

@section('content')
@php($registrationLabel=in_array($registration->status,['approved','pending'],true)?'Registered':ucfirst($registration->status))
<style>
.journey-shell { display: grid; gap: 24px; }
.attendee-hero-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 24px 28px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}
.attendee-profile-box {
    display: flex;
    align-items: center;
    gap: 18px;
}
.attendee-avatar {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 1.5rem;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    flex-shrink: 0;
}
.attendee-info h1 {
    margin: 0 0 4px;
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.attendee-info p {
    margin: 0;
    color: #64748b;
    font-size: 0.92rem;
    font-weight: 500;
}
.attendee-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.attendee-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.detail-item-box {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.detail-item-box i {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    color: #7c3aed;
    flex-shrink: 0;
}
.detail-item-box strong {
    display: block;
    font-size: 0.95rem;
    color: #0f172a;
    line-height: 1.3;
}
.detail-item-box small {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Timeline */
.journey-timeline-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 26px 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.journey-timeline-card h2 {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.journey-timeline-card h2 i {
    color: #7c3aed;
}
.journey-line {
    position: relative;
    display: grid;
    gap: 18px;
    padding-left: 10px;
}
.journey-line::before {
    content: '';
    position: absolute;
    left: 31px;
    top: 24px;
    bottom: 24px;
    width: 2px;
    background: #e2e8f0;
}
.journey-event {
    position: relative;
    display: flex;
    gap: 18px;
    align-items: center;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.journey-event:hover {
    transform: translateX(4px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.04);
}
.journey-event > i {
    z-index: 2;
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #ede9fe;
    color: #6d28d9;
    font-size: 1.25rem;
    flex-shrink: 0;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px #ddd6fe;
}
.journey-event.join > i {
    background: #dcfce7;
    color: #16a34a;
    box-shadow: 0 0 0 1px #bbf7d0;
}
.journey-event.leave > i {
    background: #fee2e2;
    color: #dc2626;
    box-shadow: 0 0 0 1px #fecaca;
}
.journey-event.poll > i {
    background: #e0f2fe;
    color: #0284c7;
    box-shadow: 0 0 0 1px #bae6fd;
}
.journey-event.cert > i {
    background: #fef3c7;
    color: #d97706;
    box-shadow: 0 0 0 1px #fde68a;
}
.journey-event span {
    display: grid;
    gap: 2px;
}
.journey-event strong {
    color: #1e293b;
    font-size: 0.95rem;
}
.journey-event small {
    color: #64748b;
    font-size: 0.82rem;
}
</style>

<div class="journey-shell">
    <div class="attendee-hero-card">
        <div class="attendee-profile-box">
            <div class="attendee-avatar">
                {{ collect(explode(' ', $registration->user?->name ?? $registration->email))->map(fn($part) => $part[0] ?? '')->take(2)->join('') ?: 'U' }}
            </div>
            <div class="attendee-info">
                <h1>{{ $registration->user?->name ?? $registration->email }}</h1>
                <p>
                    <i class="bi bi-camera-video text-muted me-1"></i>
                    <strong>{{ $registration->webinar?->title ?? 'Webinar' }}</strong>
                    <span class="mx-2 text-muted">·</span>
                    <span class="badge bg-light text-dark border">{{ $registrationLabel }}</span>
                </p>
            </div>
        </div>
        <div class="attendee-actions">
            <a class="btn btn-light d-inline-flex align-items-center gap-1" href="{{ route('admin.users') }}">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
            <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" onsubmit="return confirm('Are you sure you want to permanently delete this registration?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-1">
                    <i class="bi bi-trash3"></i> Delete Registration
                </button>
            </form>
        </div>
    </div>

    <div class="attendee-details-grid">
        <div class="detail-item-box">
            <i class="bi bi-envelope"></i>
            <div>
                <small>Email Address</small>
                <strong>{{ $registration->email }}</strong>
            </div>
        </div>
        <div class="detail-item-box">
            <i class="bi bi-telephone"></i>
            <div>
                <small>Mobile Phone</small>
                <strong>{{ $registration->user?->mobile ?: ($registration->mobile ?: '—') }}</strong>
            </div>
        </div>
        <div class="detail-item-box">
            <i class="bi bi-calendar-check"></i>
            <div>
                <small>Registration Date</small>
                <strong>{{ $registration->registered_at?->format('d M Y, h:i A') ?: ($registration->created_at?->format('d M Y, h:i A') ?: '—') }}</strong>
            </div>
        </div>
        <div class="detail-item-box">
            <i class="bi bi-shield-check"></i>
            <div>
                <small>Registration Status</small>
                <strong class="text-success">{{ $registrationLabel }}</strong>
            </div>
        </div>
    </div>

    <div class="journey-timeline-card">
        <h2><i class="bi bi-clock-history"></i> Attendee Journey & Activity Timeline</h2>
        <div class="journey-line">
            <div class="journey-event">
                <i class="bi bi-person-check"></i>
                <span>
                    <strong>Registered for Event ({{ $registrationLabel }})</strong>
                    <small>{{ $registration->registered_at?->format('d M Y, h:i A') ?: ($registration->created_at?->format('d M Y, h:i A') ?: 'Timestamp recorded') }}</small>
                </span>
            </div>

            @forelse($events as $event)
                <div class="journey-event {{ $event->event_type === 'join' ? 'join' : ($event->event_type === 'leave' ? 'leave' : '') }}">
                    <i class="bi bi-{{ $event->event_type === 'join' ? 'box-arrow-in-right' : ($event->event_type === 'leave' ? 'box-arrow-left' : 'activity') }}"></i>
                    <span>
                        <strong>Session {{ ucfirst($event->event_type) }}</strong>
                        <small>{{ \Carbon\Carbon::parse($event->occurred_at)->format('d M Y, h:i:s A') }}</small>
                    </span>
                </div>
            @empty
                @if(!$pollAnswers && !$certificate)
                    <div class="journey-event">
                        <i class="bi bi-info-circle"></i>
                        <span>
                            <strong>No live attendance events recorded yet</strong>
                            <small>Attendance heartbeats will appear here once the attendee joins the room.</small>
                        </span>
                    </div>
                @endif
            @endforelse

            @if($pollAnswers)
                <div class="journey-event poll">
                    <i class="bi bi-bar-chart"></i>
                    <span>
                        <strong>{{ $pollAnswers }} Poll Responses Submitted</strong>
                        <small>Active participant engagement recorded</small>
                    </span>
                </div>
            @endif

            @if($certificate)
                <div class="journey-event cert">
                    <i class="bi bi-award"></i>
                    <span>
                        <strong>Certificate {{ ucfirst($certificate->status) }}</strong>
                        <small>Credential ID: {{ $certificate->credential_id }}</small>
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
