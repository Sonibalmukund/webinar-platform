@extends('layouts.portal')
@section('title', 'Comments')
@section('content')
<div class="page-heading"><div><span class="eyebrow">AUDIENCE VOICE</span><h1>Comments</h1><p>Private attendee comments across webinars.</p></div></div>
<x-admin-webinar-filter :webinars="$webinars" :selected="$webinarId" :search="$search" />
@php($dynamicCols = $dynamicColumns ?? ($items->dynamic_columns ?? []))
<section class="panel-card table-responsive"><table class="premium-table">
<thead><tr><th>User</th><th>Email</th><th>Mobile</th><th>Webinar</th>@foreach($dynamicCols as $col)<th>{{ $col }}</th>@endforeach<th>Comment</th><th>Received</th><th>Action</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td><strong>{{ $item->user_name ?: 'Guest' }}</strong></td><td>{{ $item->user_email ?: '—' }}</td><td>{{ $item->user_mobile ?: '—' }}</td><td>{{ $item->webinar_title }}</td>@foreach($dynamicCols as $col)<td>{{ $item->dynamic_fields[$col] ?? '—' }}</td>@endforeach<td class="admin-comment-copy">{{ $item->comment }}</td><td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y, h:i A') }}</td><td><form method="POST" action="{{ route('admin.comments.destroy', [$item->webinar_id, $item->id]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash3"></i> Remove</button></form></td></tr>
@empty<tr><td colspan="{{ 7 + count($dynamicCols) }}" class="text-center text-muted py-5">No comments found.</td></tr>@endforelse
</tbody></table></section><x-admin-pagination :paginator="$items" />
@endsection
