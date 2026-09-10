@extends('layouts.portal')
@section('title','Notifications')
@section('content')
<div class="page-heading"><div><span class="eyebrow">COMMUNICATION</span><h1>Notifications</h1><p>Send realtime announcements to learners.</p></div><a class="btn btn-gradient" href="{{ route('admin.notifications.create') }}"><i class="bi bi-send"></i> Create notification</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="panel-card table-responsive"><table class="premium-table"><thead><tr><th>Subject</th><th>Audience</th><th>Webinar</th><th>Status</th><th>Sent</th></tr></thead><tbody>@forelse($campaigns as $campaign)<tr><td><strong>{{ $campaign->subject }}</strong><br><small>{{ Str::limit($campaign->message,80) }}</small></td><td>{{ Str::headline($campaign->audience) }}</td><td>{{ $campaign->webinar_title?:'All webinars' }}</td><td><span class="status-badge active">SENT</span></td><td>{{ $campaign->sent_at?Carbon\Carbon::parse($campaign->sent_at)->format('d M Y, h:i A'):'—' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">No notifications sent yet.</td></tr>@endforelse</tbody></table></div><x-admin-pagination :paginator="$campaigns" />
@endsection
