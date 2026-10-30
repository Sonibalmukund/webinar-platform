@extends('layouts.public')
@section('title', 'Privacy Policy')
@section('content')
    <main class="container py-5" style="max-width:900px">
        <article class="panel-card p-4 p-md-5">
            <span class="text-uppercase text-danger fw-bold small">VirtualPortal</span>
            <h1 class="mt-2 mb-3">Privacy Policy</h1>
            <p class="text-muted">Last updated: October 8, 2026</p>
            <h2 class="h5 mt-4">Information we collect</h2>
            <p>We collect the registration details requested by an event organiser, along with attendance, engagement and technical records needed to operate the event.</p>
            <h2 class="h5 mt-4">How event data is used</h2>
            <p>Data is used to confirm registration, provide event access, send authorised communications, measure participation and issue certificates when enabled.</p>
            <h2 class="h5 mt-4">Sharing and retention</h2>
            <p>Information is available only to authorised portal administrators and the team assigned to the relevant event. We retain records according to the event organiser's operational and legal requirements.</p>
            <h2 class="h5 mt-4">Your choices</h2>
            <p>You may contact the event support team to request access, correction or deletion where applicable.</p>
            <a class="btn btn-gradient mt-3" href="{{ route('home') }}">Back to VirtualPortal</a>
        </article>
    </main>
@endsection