@extends('layouts.portal')
@section('title','Certificate Download Logs')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">AUDIT & COMPLIANCE</span>
        <h1>Certificate download logs</h1>
        <p>Real-time audit log of all attendee certificate downloads with IP address and timestamps.</p>
    </div>
</div>


@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<x-admin-webinar-filter :webinars="$webinars" :selected="$webinarId" :search="$search" placeholder="Search attendee name, email, credential ID, or IP..." />

<div class="panel-card table-responsive">
    <table class="premium-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Attendee</th>
                <th>Webinar</th>
                <th>Credential ID</th>
                <th>IP Address</th>
                <th>Downloaded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $logs->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $log->user_name }}</strong><br>
                        <small class="text-muted">{{ $log->user_email }}</small>
                    </td>
                    <td>
                        <strong>{{ $log->webinar_title }}</strong>
                    </td>
                    <td>
                        <code>{{ $log->credential_id ? Str::limit($log->credential_id, 18) : 'N/A' }}</code>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $log->ip_address ?: 'Unknown' }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span>{{ Carbon\Carbon::parse($log->downloaded_at)->format('d M Y, h:i A') }}</span>
                            <small class="text-muted">{{ Carbon\Carbon::parse($log->downloaded_at)->diffForHumans() }}</small>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                        No certificate download records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$logs" />
@endsection