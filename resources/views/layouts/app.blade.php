<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="broadcast-auth-url" content="{{ url('/broadcasting/auth') }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#6d28d9">
<title>@yield('title', $siteSettings['site_name']??'Webinarly') - {{ $siteSettings['site_name']??'Webinarly' }}</title>
@yield('meta')
@if($siteSettings['favicon']??null)<link rel="icon" href="{{ $siteSettings['favicon'] }}">@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
/* Hide scrollbar track/column across all browsers while allowing natural scrolling */
html, body {
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
  scrollbar-gutter: auto !important;
  overflow-x: hidden !important;
}
html::-webkit-scrollbar,
body::-webkit-scrollbar,
*::-webkit-scrollbar {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
  background: transparent !important;
}
* {
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}
</style>
</head>
<body class="@yield('body-class')" @auth data-auth-user-id="{{ auth()->id() }}" @endauth>
@yield('shell')
@yield('toast')
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4"><div class="modal-body p-4 text-center"><div class="modal-icon danger mx-auto"><i class="bi bi-exclamation-triangle"></i></div><h4 class="mt-3">Are you sure?</h4><p class="text-muted">Please confirm this action.</p><div class="d-flex gap-2 justify-content-center"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger" data-bs-dismiss="modal" data-demo-toast>Confirm</button></div></div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
