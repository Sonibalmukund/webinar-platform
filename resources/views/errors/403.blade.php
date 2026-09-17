@php
    $request = request();
    $isWebinarDashboard = false;
    $targetUrl = '/';

    if ($request->is('*/dashboard') && ! $request->is('admin/*') && ! $request->is('sub-admin/*')) {
        $slug = $request->segment(1);
        if ($slug && $slug !== 'dashboard') {
            $isWebinarDashboard = true;
            $targetUrl = '/' . $slug;
        }
    }
@endphp
@if($isWebinarDashboard)
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0; url={{ $targetUrl }}">
    <title>Redirecting to Webinar...</title>
    <script>
        window.location.replace(@json($targetUrl));
    </script>
</head>
<body style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #0b1222; color: #f8fafc; text-align: center; padding: 20px;">
    <div style="max-width: 420px; width: 100%; background: #111a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 32px 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <div style="width: 44px; height: 44px; border: 3px solid rgba(139,92,246,0.25); border-top-color: #8b5cf6; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 18px;"></div>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 8px; color: #fff;">Webinar Room is Not Open</h3>
        <p style="color: #94a3b8; font-size: 0.875rem; margin: 0 0 20px; line-height: 1.5;">Redirecting you back to the webinar landing page...</p>
        <a href="{{ $targetUrl }}" style="display: inline-block; padding: 10px 20px; border-radius: 10px; background: linear-gradient(135deg, #6d28d9, #2563eb); color: #fff; text-decoration: none; font-size: 0.85rem; font-weight: 700; transition: 0.2s;">Go to Webinar Page</a>
    </div>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</body>
</html>
@else
@php($state = '403')
@include('pages.shared.state')
@endif
