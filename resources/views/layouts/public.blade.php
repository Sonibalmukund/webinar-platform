@extends('layouts.app')
@section('shell')
<header class="public-header sticky-top"><nav class="navbar navbar-expand-lg"><div class="container py-2"><x-site-brand /><button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#publicNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="publicNav"><ul class="navbar-nav mx-auto gap-lg-3"><li><a class="nav-link" href="/">Home</a></li><li><a class="nav-link" href="/webinars">Webinars</a></li><li><a class="nav-link" href="/#speakers">Speakers</a></li><li><a class="nav-link" href="/#categories">Categories</a></li><li><a class="nav-link" href="/#about">About</a></li></ul><div class="d-flex gap-2">@guest<button type="button" class="btn btn-ghost" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Log in</button><button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Get started</button>@else<a class="btn btn-gradient" href="{{ route('dashboard') }}">Dashboard</a>@endguest</div></div></div></nav></header>
@php($authNotice=session('auth_status') ?: session('registration_status'))
@if(session('auth_redirect'))
<div class="modal fade show" id="authRedirectModal" tabindex="-1" style="display: block; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 5000;" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
            <div class="modal-body p-4 p-md-5 text-center">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: grid; place-items: center; margin: 0 auto 18px; font-size: 1.85rem; box-shadow: 0 10px 25px rgba(22, 163, 74, 0.2);">
                    <i class="bi bi-check2"></i>
                </div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                    {{ session('auth_status') ?: (session('registration_status') ?: 'Login successfully.') }}
                </h3>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 22px;">
                    Redirecting to your webinar dashboard...
                </p>
                <div style="height: 5px; width: 100%; background: #f1f5f9; border-radius: 999px; overflow: hidden; position: relative;">
                    <div style="height: 100%; width: 0%; background: linear-gradient(90deg, #7c3aed, #2563eb); border-radius: 999px; animation: authModalProgress 1.4s ease-in-out forwards;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes authModalProgress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        window.location.assign(@json(session('auth_redirect')));
    }, 1400);
});
</script>
@elseif($authNotice)
<div class="auth-redirect-notice" role="status" aria-live="polite"><span class="auth-redirect-icon"><i class="bi bi-check2"></i></span><div class="auth-redirect-content"><strong>{{ $authNotice }}</strong></div></div>
<style>.auth-redirect-notice{position:fixed;z-index:4000;top:90px;right:24px;max-width:min(420px,calc(100vw - 32px));display:flex;align-items:center;gap:14px;padding:16px 20px 20px;border:1px solid #10b98133;border-radius:14px;background:#fff;color:#172033;box-shadow:0 20px 50px rgba(15,23,42,.15);overflow:hidden}.auth-redirect-icon{width:38px;height:38px;display:grid;place-items:center;flex:none;border-radius:10px;background:#ecfdf5;color:#059669;font-size:1.25rem}.auth-redirect-content strong{font-size:.95rem;font-weight:600;color:#0f172a}@media(max-width:600px){.auth-redirect-notice{top:74px;right:16px;left:16px}}</style>
@endif
<main>@yield('content')</main>@hasSection('footer') @yield('footer') @endif
@hasSection('auth-modals')
@yield('auth-modals')
@else
@guest
@include('components.frontend-auth', ['authWebinar'=>null])
@endguest
@endif
@endsection
