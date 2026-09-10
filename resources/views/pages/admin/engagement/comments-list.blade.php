@extends('layouts.portal')
@section('title', 'Comments')
@section('content')
<div class="page-heading"><div><span class="eyebrow">AUDIENCE VOICE</span><h1>Comments</h1><p>Private attendee comments across webinars.</p></div></div>
<x-admin-webinar-filter :webinars="$webinars" :selected="$webinarId" :search="$search" />
<section class="panel-card table-responsive"><table class="premium-table">
<thead><tr><th>User</th><th>Webinar</th><th>Comment</th><th>Received</th><th>Action</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td><strong>{{ $item->user_name ?: 'Guest' }}</strong><br><small>{{ $item->user_email }}</small></td><td>{{ $item->webinar_title }}</td><td class="admin-comment-copy">{{ $item->comment }}</td><td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y, h:i A') }}</td><td><form method="POST" action="{{ route('admin.comments.destroy', [$item->webinar_id, $item->id]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash3"></i> Remove</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-5">No comments found.</td></tr>@endforelse
</tbody></table></section><x-admin-pagination :paginator="$items" />
@endsection
