@extends('layouts.app')
@section('body-class', 'portal-body '.(auth()->user()->hasRole('sub-admin')?'sub-admin-portal':''))
@section('shell')
@php($isAdmin = request()->is('admin/*'))
@php($isSubAdmin = auth()->user()->hasRole('sub-admin'))
@php($notificationUrl = $isAdmin ? (auth()->user()->hasPermission('notifications.view') ? route('admin.notifications.index') : route('admin.dashboard')) : route('notifications.index'))
@php($profileUrl = $isAdmin ? '/admin/profile' : '/profile')
<style>.sidebar-section-title{padding:18px 18px 7px;color:#77839a;font-size:.62rem;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.sidebar-section-title:first-child{padding-top:6px}</style>
<style>.sidebar-nav{scrollbar-width:none;-ms-overflow-style:none}.sidebar-nav::-webkit-scrollbar{display:none}.registration-switches{display:grid;gap:10px}.registration-switches>label{display:flex!important;align-items:center;justify-content:space-between;padding:13px 15px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc}.registration-switches span{display:grid}.registration-switches small{color:#64748b;font-weight:400}.registration-switches .form-check-input{width:2.4em;height:1.25em;cursor:pointer}.status-toggle-button{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border:0;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.7rem;font-weight:800}.status-toggle-button span{width:8px;height:8px;border-radius:50%;background:#94a3b8}.status-toggle-button.active{background:#dcfce7;color:#15803d}.status-toggle-button.active span{background:#22c55e}.chat-pending-file{position:absolute;left:66px;bottom:2px;color:#6d28d9;font-size:.58rem;font-weight:700}</style>
<div class="portal-shell">
    <x-portal-sidebar :is-admin="$isAdmin" />
    <button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Close navigation" tabindex="-1" hidden></button>
    <div class="portal-main">
        <header class="topbar">
            <button class="icon-btn d-lg-none" id="menuToggle" type="button" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false"><i class="bi bi-list"></i></button>
            @unless($isAdmin)<div class="topbar-search"><i class="bi bi-search"></i><input placeholder="Search webinars, speakers and topics"></div>@endunless
            <div class="ms-auto d-flex align-items-center gap-2">@php($unreadNotifications=!$isAdmin?DB::table('user_notifications')->where('user_id',auth()->id())->whereNull('read_at')->count():0)<a class="icon-btn position-relative" href="{{ $notificationUrl }}" aria-label="Notifications"><i class="bi bi-bell"></i><span class="notification-count" data-notification-badge @if(!$unreadNotifications) hidden @endif>{{ $unreadNotifications }}</span></a><div class="dropdown"><button class="avatar-menu" data-bs-toggle="dropdown"><span class="avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn($part) => $part[0])->take(2)->join('') }}</span><span class="user-meta d-none d-md-flex"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->job_title ?? ($isAdmin ? 'Administrator' : 'Learner') }}</small></span><i class="bi bi-chevron-down"></i></button><ul class="dropdown-menu dropdown-menu-end border-0 shadow"><li><a class="dropdown-item" href="{{ $profileUrl }}">Profile</a></li><li><form method="POST" action="/logout">@csrf<button class="dropdown-item" type="submit">Sign out</button></form></li></ul></div></div>
        </header>
        <main class="portal-content">@yield('content')</main>
    </div>
</div>
<template id="sweetConfirmTemplate"><div class="sweet-confirm-backdrop"><div class="sweet-confirm-card"><div class="sweet-confirm-icon"><i class="bi bi-exclamation-lg"></i></div><h3 data-confirm-heading>Delete this item?</h3><p data-confirm-message>This action cannot be undone.</p><div class="d-flex justify-content-center gap-2 mt-4"><button type="button" class="btn btn-light" data-confirm-cancel>Cancel</button><button type="button" class="btn btn-danger" data-confirm-accept>Yes, delete</button></div></div></div></template>
@endsection
