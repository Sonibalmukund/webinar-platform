@php
    $states = [
        '401' => ['eyebrow' => 'Authentication required', 'title' => 'Please sign in', 'copy' => 'Your session does not have access to this page.', 'icon' => 'person-lock'],
        '403' => ['eyebrow' => 'Permission required', 'title' => 'Access restricted', 'copy' => 'You do not have permission to open this page.', 'icon' => 'shield-lock'],
        '404' => ['eyebrow' => 'Page unavailable', 'title' => 'Page not found', 'copy' => 'The page may have moved, been removed, or the address may be incorrect.', 'icon' => 'compass'],
        '419' => ['eyebrow' => 'Session expired', 'title' => 'Please refresh and try again', 'copy' => 'Your session expired before this request could be completed.', 'icon' => 'clock-history'],
        '429' => ['eyebrow' => 'Too many requests', 'title' => 'Please slow down', 'copy' => 'There have been too many requests. Wait a moment and try again.', 'icon' => 'hourglass-split'],
        '500' => ['eyebrow' => 'System error', 'title' => 'Something went wrong', 'copy' => 'We could not complete this request. Please try again in a moment.', 'icon' => 'exclamation-triangle'],
        '503' => ['eyebrow' => 'Temporarily unavailable', 'title' => 'We will be back shortly', 'copy' => 'The platform is currently unavailable while maintenance is completed.', 'icon' => 'tools'],
        'empty' => ['eyebrow' => 'No records', 'title' => 'Nothing here yet', 'copy' => 'New content will appear here as soon as it is available.', 'icon' => 'inbox'],
        'loading' => ['eyebrow' => 'Please wait', 'title' => 'Preparing your experience', 'copy' => 'We are getting everything ready for you.', 'icon' => 'arrow-repeat'],
    ];
    $details = $states[$state] ?? $states['404'];
    $isLoading = $state === 'loading';
    $dashboardUrl = auth()->check()
        ? (request()->is('admin/*') ? '/admin/dashboard' : '/dashboard')
        : '/admin/login';
@endphp
<section class="admin-state-card" aria-labelledby="stateTitle">
    <div class="admin-state-visual" aria-hidden="true">
        <span class="admin-state-orbit"></span>
        <span class="admin-state-icon {{ $isLoading ? 'is-loading' : '' }}"><i class="bi bi-{{ $details['icon'] }}"></i></span>
        @if(ctype_digit((string) $state))<strong>{{ $state }}</strong>@endif
    </div>
    <div class="admin-state-copy">
        <span class="eyebrow">{{ $details['eyebrow'] }}</span>
        <h1 id="stateTitle">{{ $details['title'] }}</h1>
        <p>{{ $details['copy'] }}</p>
        @unless($isLoading)
            <div class="admin-state-actions">
                <a class="btn btn-gradient" href="{{ $dashboardUrl }}"><i class="bi bi-grid-1x2 me-2"></i>{{ auth()->check() ? 'Back to dashboard' : 'Admin sign in' }}</a>
                <button class="btn btn-light border" type="button" onclick="history.back()"><i class="bi bi-arrow-left me-2"></i>Go back</button>
            </div>
        @endunless
    </div>
</section>
