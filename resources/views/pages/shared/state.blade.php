@extends(auth()->check() ? 'layouts.portal' : 'layouts.app')
@section('title', strtoupper($state))

@auth
    @section('content')
        <div class="admin-state-page">@include('pages.shared.partials.state-card')</div>
    @endsection
@else
    @section('body-class', 'portal-body')
@section('shell')
    <div class="portal-shell guest-state-shell">
        <aside class="sidebar" id="sidebar">
            <x-site-brand light class="sidebar-uploaded-brand" />
            <nav class="sidebar-nav">
                <a href="/admin/login"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a href="/admin/login"><i class="bi bi-camera-video"></i> Webinars</a>
                <a href="/admin/login"><i class="bi bi-ui-checks-grid"></i> Dynamic Fields</a>
                <a href="/admin/login"><i class="bi bi-mic"></i> Speakers</a>
            </nav>
        </aside>
        <div class="portal-main">
            <header class="topbar">
                <div class="ms-auto"><a class="btn btn-gradient" href="/admin/login">Admin sign in</a></div>
            </header>
            <main class="portal-content">
                <div class="admin-state-page">@include('pages.shared.partials.state-card')</div>
            </main>
        </div>
    </div>
@endsection
@endauth
