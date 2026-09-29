@extends('layouts.portal')
@section('title', 'Email Delivery Logs')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Email Logs</h1>
        </div>
        <a class="btn btn-gradient" href="{{ route('admin.notifications.email-logs.export', request()->query()) }}"><i
                class="bi bi-download"></i> Export CSV</a>
    </div>

    <form class="panel-card mb-3" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label">Search</label><input class="form-control" name="search"
                    value="{{ request('search') }}" placeholder="Recipient, subject or attendee"></div>
            <div class="col-lg-3"><label class="form-label">Webinar</label><select class="form-select" name="webinar_id">
                    <option value="">All webinars</option>
                    @foreach ($webinars as $webinar)
                        <option value="{{ $webinar->id }}" @selected((string) request('webinar_id') === (string) $webinar->id)>{{ $webinar->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2"><label class="form-label">Status</label><select class="form-select" name="status">
                    <option value="">All statuses</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                </select></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-gradient flex-fill"><i class="bi bi-search"></i>
                    Filter</button><a class="btn btn-light" href="{{ route('admin.notifications.email-logs') }}">Reset</a>
            </div>
        </div>
    </form>

    <div class="panel-card table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Notification</th>
                    <th>Webinar</th>
                    <th>Status</th>
                    <th>Sent at</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td><strong>{{ $log->user_name ?: 'Registered attendee' }}</strong><br><small>{{ $log->recipient }}</small>
                        </td>
                        <td>{{ $log->subject }}</td>
                        <td>{{ $log->webinar_title ?: 'All webinars' }}</td>
                        <td><span
                                class="status-badge {{ $log->status === 'sent' ? 'active' : 'inactive' }}">{{ strtoupper($log->status) }}</span>
                        </td>
                        <td>{{ $log->sent_at ? Carbon\Carbon::parse($log->sent_at)->format('d M Y, h:i A') : '—' }}</td>
                        <td>
                            @if ($log->error_message)
                                <span class="text-danger"
                                title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 90) }}</span>@else<span
                                    class="text-success">Sent successfully</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No email delivery activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-admin-pagination :paginator="$logs" />
@endsection
