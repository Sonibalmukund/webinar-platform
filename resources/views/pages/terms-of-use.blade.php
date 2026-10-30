@extends('layouts.public')
@section('title', 'Terms of Use')
@section('content')
    <main class="container py-5" style="max-width:900px">
        <article class="panel-card p-4 p-md-5">
            <span class="text-uppercase text-danger fw-bold small">VirtualPortal</span>
            <h1 class="mt-2 mb-3">Terms of Use</h1>
            <p class="text-muted">Last updated: October 8, 2026</p>
            <h2 class="h5 mt-4">Event access</h2>
            <p>Use your own registration details and follow the access rules shown for the event. Access may be limited by capacity, schedule, approval status or cancellation.</p>
            <h2 class="h5 mt-4">Acceptable use</h2>
            <p>Do not disrupt a session, misuse attendee information, upload harmful material or attempt to access another event's private data.</p>
            <h2 class="h5 mt-4">Content and availability</h2>
            <p>Event organisers are responsible for their event content, schedules and recordings. Live services may be affected by provider or network availability.</p>
            <h2 class="h5 mt-4">Support</h2>
            <p>For access or registration issues, use the support details displayed on the relevant event page.</p>
            <a class="btn btn-gradient mt-3" href="{{ route('home') }}">Back to VirtualPortal</a>
        </article>
    </main>
@endsection