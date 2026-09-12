@extends('layouts.portal')
@section('title','Certificate Activity Logs')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">AUDIT & COMPLIANCE</span>
        <h1>Certificate activity logs</h1>
        <p>Direct audit trail of attendee certificate downloads and issuance events with IP details.</p>
    </div>
    <div>
        <a href="{{ route('admin.certificates.logs') }}" class="btn btn-outline-secondary"><i class="bi bi-journal-text me-1"></i> Full Download Logs</a>
    </div>
</div>
<div class="panel-card table-responsive">
    <table class="premium-table">
        <thead>
            <tr>
                <th>Index</th>
                <th>Action</th>
                <th>User / Attendee</th>
                <th>Details</th>
                <th>IP address</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $logs->firstItem() + $loop->index }}</td>
                    <td><span class="status-badge {{ str_contains($log->action, 'download') ? 'live' : 'scheduled' }}">{{ strtoupper(str_replace('.', ' · ', $log->action)) }}</span></td>
                    <td>
                        <strong>{{ $log->user?->name ?? 'Attendee' }}</strong>
                        @if($log->user?->email)<br><small class="text-muted">{{ $log->user->email }}</small>@endif
                    </td>
                    <td>{{ $log->description ?? '—' }}</td>
                    <td><code>{{ $log->ip_address ?? '—' }}</code></td>
                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No certificate activity recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-admin-pagination :paginator="$logs" />
@endsection
