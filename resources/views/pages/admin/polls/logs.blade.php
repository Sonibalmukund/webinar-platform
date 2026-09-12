@extends('layouts.portal')
@section('title','Poll Logs')
@section('content')
<div class="page-heading"><div><span class="eyebrow">POLL AUDIT</span><h1>Poll voter logs</h1><p>See exactly who answered each webinar poll and which option they selected.</p></div></div>
<x-admin-webinar-filter :webinars="$webinars" :selected="$selectedWebinarId" :search="$search" placeholder="Search attendee, poll, answer or webinar..." />
<div class="panel-card table-responsive"><table class="premium-table">
    <thead><tr><th>Attendee</th><th>Webinar</th><th>Poll question</th><th>Selected answer</th><th>Result</th><th>Submitted</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($logs as $log)
        <tr>
            <td><strong>{{ $log->user_name ?? 'Deleted user' }}</strong><br><small>{{ $log->user_email ?? '—' }}{{ $log->user_mobile ? ' · '.$log->user_mobile : '' }}</small></td>
            <td>{{ $log->webinar_title }}</td>
            <td>{{ Str::limit($log->poll_question, 70) }}</td>
            <td><strong>{{ $log->option_label }}</strong></td>
            <td>@if(is_null($log->is_correct))<span class="status-badge scheduled">POLL</span>@else<span class="status-badge {{ $log->is_correct ? 'live' : 'cancelled' }}">{{ $log->is_correct ? 'CORRECT' : 'INCORRECT' }}</span>@endif</td>
            <td>{{ optional($log->voted_at ?? $log->created_at)->format('d M Y, h:i A') }}</td>
            <td><a class="btn btn-sm btn-light text-nowrap" href="{{ route('admin.polls.show', $log->poll_id) }}"><i class="bi bi-eye"></i> View results</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-5">No poll responses found.</td></tr>
    @endforelse
    </tbody>
</table></div>
<x-admin-pagination :paginator="$logs" />
@endsection
