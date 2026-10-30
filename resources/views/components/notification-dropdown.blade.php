@props(['buttonClass' => 'icon-btn position-relative', 'badgeClass' => 'notification-count'])

@php
    $notifications = DB::table('user_notifications')
        ->where('user_id', auth()->id())
        ->latest()
        ->limit(6)
        ->get()
        ->map(function ($notification) {
            $notification->data = json_decode($notification->data, true) ?: [];

            return $notification;
        });
    $unreadNotifications = DB::table('user_notifications')
        ->where('user_id', auth()->id())
        ->whereNull('read_at')
        ->count();
@endphp

@once
    <style>
        .notification-dropdown-menu {
            width: min(360px, calc(100vw - 24px));
            max-height: min(470px, calc(100vh - 90px));
            overflow-y: auto;
            padding: 0;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            color: #182235;
            box-shadow: 0 18px 50px #0f172a2e
        }

        .notification-dropdown-heading {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            background: #fff;
            font-size: .82rem;
            font-weight: 800
        }

        .notification-dropdown-list {
            display: grid
        }

        .notification-dropdown-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9
        }

        .notification-dropdown-item:last-child {
            border-bottom: 0
        }

        .notification-dropdown-item>i {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #fff0f1;
            color: #ee1f2d
        }

        .notification-dropdown-item strong,
        .notification-dropdown-item span,
        .notification-dropdown-item small {
            display: block
        }

        .notification-dropdown-item strong {
            margin-bottom: 3px;
            color: #182235;
            font-size: .76rem
        }

        .notification-dropdown-item span {
            color: #64748b;
            font-size: .72rem;
            line-height: 1.45;
            overflow-wrap: anywhere
        }

        .notification-dropdown-item small {
            margin-top: 5px;
            color: #94a3b8;
            font-size: .64rem
        }

        .notification-dropdown-empty {
            padding: 28px 16px;
            color: #64748b;
            text-align: center;
            font-size: .76rem
        }

        .event-dashboard .notification-dropdown-menu {
            border-color: #ffffff1f;
            background: #101a2c;
            color: #f1f5f9;
            box-shadow: 0 20px 55px #02061773
        }

        .event-dashboard .notification-dropdown-heading {
            border-bottom-color: #ffffff14;
            background: #0c1525;
            color: #f8fafc
        }

        .event-dashboard .notification-dropdown-item {
            border-bottom-color: #ffffff12;
            background: #101a2c
        }

        .event-dashboard .notification-dropdown-item:hover {
            background: color-mix(in srgb, var(--event-primary) 9%, #101a2c)
        }

        .event-dashboard .notification-dropdown-item>i {
            background: color-mix(in srgb, var(--event-primary) 16%, transparent);
            color: var(--event-primary)
        }

        .event-dashboard .notification-dropdown-item strong {
            color: #f8fafc
        }

        .event-dashboard .notification-dropdown-item span {
            color: #aab7cf
        }

        .event-dashboard .notification-dropdown-item small,
        .event-dashboard .notification-dropdown-empty {
            color: #8290aa
        }
    </style>
@endonce

<div class="dropdown">
    <button type="button" {{ $attributes->merge(['class' => $buttonClass]) }} data-bs-toggle="dropdown"
        aria-expanded="false" aria-label="Notifications" title="Notifications"><i class="bi bi-bell"></i><span
            class="{{ $badgeClass }}" data-notification-badge
            @if (!$unreadNotifications) hidden @endif>{{ $unreadNotifications }}</span></button>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu">
        <div class="notification-dropdown-heading">Notifications</div>
        <div class="notification-dropdown-list" data-notification-list>
            @forelse ($notifications as $notification)
                <article class="notification-dropdown-item">
                    <i class="bi bi-bell-fill"></i>
                    <div><strong>{{ $notification->data['subject'] ?? 'Notification' }}</strong><span>{{ $notification->data['message'] ?? '' }}</span><small>{{ Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small></div>
                </article>
            @empty
                <div class="notification-dropdown-empty" data-notification-empty>No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
