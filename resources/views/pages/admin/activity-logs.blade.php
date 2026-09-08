@extends('layouts.portal')
@section('title','Activity Logs')
@section('content')
<div class="page-heading"><div><span class="eyebrow">SECURITY & AUDIT</span><h1>Activity logs</h1><p>Review important administrative and attendee actions with source IP details.</p></div></div>
<div class="panel-card table-responsive"><table class="premium-table"><thead><tr><th>Index</th><th>Action</th><th>User</th><th>Description</th><th>IP address</th><th>Date</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $logs->firstItem()+$loop->index }}</td><td><span class="status-badge scheduled">{{ str_replace('.',' · ',$log->action) }}</span></td><td>{{ $log->user?->name??'System' }}</td><td>{{ $log->description??'—' }}</td><td><code>{{ $log->ip_address??'—' }}</code></td><td>{{ $log->created_at->format('d M Y, h:i A') }}</td></tr>@empty<tr><td colspan="6" class="text-center py-5 text-muted">No activity has been recorded yet.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $logs->links() }}</div>
@endsection
