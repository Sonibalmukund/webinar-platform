@extends('layouts.app')
@section('shell')
<header class="public-header sticky-top"><nav class="navbar navbar-expand-lg"><div class="container py-2"><x-site-brand /><button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#publicNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="publicNav"><ul class="navbar-nav mx-auto gap-lg-3"><li><a class="nav-link" href="/">Home</a></li><li><a class="nav-link" href="/webinars">Webinars</a></li><li><a class="nav-link" href="/#speakers">Speakers</a></li><li><a class="nav-link" href="/#categories">Categories</a></li><li><a class="nav-link" href="/#about">About</a></li></ul><div class="d-flex gap-2">@guest<button type="button" class="btn btn-ghost" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Log in</button><button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Get started</button>@else<a class="btn btn-gradient" href="{{ route('dashboard') }}">Dashboard</a>@endguest</div></div></div></nav></header><main>@yield('content')</main>@hasSection('footer') @yield('footer') @endif
@guest
@hasSection('auth-modals') @yield('auth-modals') @else @include('components.frontend-auth', ['authWebinar'=>null]) @endif
@endguest
@endsection

