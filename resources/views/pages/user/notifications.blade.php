@extends('layouts.portal')
@section('title','Notifications')
@section('content')
<div class="page-heading"><div><span class="eyebrow">UPDATES</span><h1>Notifications</h1><p>Webinar announcements and account updates appear here instantly.</p></div></div>
<div class="panel-card"><div class="notification-list">@forelse($notifications as $notification)<article class="notification-item {{ $notification->read_at?'':'unread' }}"><span class="modal-icon"><i class="bi bi-bell-fill"></i></span><div><h3>{{ $notification->data['subject']??'Notification' }}</h3><p>{{ $notification->data['message']??'' }}</p><small>{{ Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small></div>@unless($notification->read_at)<form method="POST" action="{{ route('notifications.read',$notification->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-light">Mark read</button></form>@endunless</article>@empty<div class="text-center text-muted py-5"><i class="bi bi-bell-slash display-5"></i><p class="mt-2">No notifications yet.</p></div>@endforelse</div></div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
