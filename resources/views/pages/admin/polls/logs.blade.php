@extends('layouts.portal')
@section('title','Poll Logs')
@section('content')
<div class="page-heading"><div><span class="eyebrow">POLL AUDIT</span><h1>Poll voter logs</h1><p>See exactly who answered each webinar poll and which option they selected.</p></div></div>
<x-admin-webinar-filter :webinars="$webinars" :selected="$selectedWebinarId" :search="$search" placeholder="Search attendee, poll, answer or webinar..." />
@php($dynamicCols = $dynamicColumns ?? ($logs->dynamic_columns ?? []))
<div class="panel-card table-responsive"><table class="premium-table">
    <thead><tr><th>Index</th><th>Attendee</th><th>Email</th><th>Mobile</th><th>Webinar</th><th>Poll question</th><th>Selected answer</th><th>Result</th>@foreach($dynamicCols as $col)<th>{{ $col }}</th>@endforeach<th>Submitted</th></tr></thead>
    <tbody>
    @forelse($logs as $log)
        <tr>
            <td>{{ $logs->firstItem() + $loop->index }}</td>
            <td><strong>{{ $log->user_name ?? 'Deleted user' }}</strong></td>
            <td>{{ $log->user_email ?? '—' }}</td>
            <td>{{ $log->user_mobile ?: '—' }}</td>
            <td>{{ $log->webinar_title }}</td>
            <td>{{ Str::limit($log->poll_question, 70) }}</td>
            <td><strong>{{ $log->option_label }}</strong></td>
            <td>@if(is_null($log->is_correct))<span class="status-badge scheduled">POLL</span>@else<span class="status-badge {{ $log->is_correct ? 'live' : 'cancelled' }}">{{ $log->is_correct ? 'CORRECT' : 'INCORRECT' }}</span>@endif</td>
            @foreach($dynamicCols as $col)<td>{{ $log->dynamic_fields[$col] ?? '—' }}</td>@endforeach
            <td>{{ optional($log->voted_at ?? $log->created_at)->format('d M Y, h:i A') }}</td>
        </tr>
    @empty
        <tr><td colspan="{{ 9 + count($dynamicCols) }}" class="text-center text-muted py-5">No poll responses found.</td></tr>
    @endforelse
    </tbody>
</table></div>
<x-admin-pagination :paginator="$logs" />
@endsection
