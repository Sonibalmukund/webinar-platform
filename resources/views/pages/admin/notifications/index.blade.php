@extends('layouts.portal')
@section('title', 'Notifications')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Notifications</h1>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if (auth()->user()->hasRole('super-admin') || auth()->user()->hasPermission('notifications.create'))
                <a class="btn btn-gradient" href="{{ route('admin.notifications.create') }}"><i class="bi bi-plus"></i> Add
                    Notification</a>
            @endif
        </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="panel-card table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Channel</th>
                    <th>Delivery</th>
                    <th>Audience</th>
                    <th>Webinar</th>
                    <th>Status</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                    @php($channels = json_decode($campaign->channels, true) ?: [])
                    <tr>
                        <td><strong>{{ $campaign->subject }}</strong><br><small>{{ Str::limit(App\Support\RichText::plain($campaign->message), 80) }}</small>
                            @if ($campaign->attachment_name)
                                <br><small><i class="bi bi-paperclip"></i> {{ $campaign->attachment_name }}</small>
                            @endif
                        </td>
                        <td>{{ collect($channels)->map(fn($channel) => Str::headline($channel))->join(', ') }}</td>
                        <td>{{ $campaign->delivery_mode === 'save_template' ? 'Saved draft' : ($campaign->delivery_mode === 'reminder_now' ? 'Reminder now' : 'After registration') }}
                        </td>
                        <td>{{ Str::headline($campaign->audience) }}</td>
                        <td>{{ $campaign->webinar_title ?: 'All webinars' }}</td>
                        <td><span
                                class="status-badge {{ in_array($campaign->status, ['sent', 'active']) ? 'active' : 'scheduled' }}">{{ strtoupper($campaign->status) }}</span>
                        </td>
                        <td>{{ $campaign->sent_at ? Carbon\Carbon::parse($campaign->sent_at)->format('d M Y, h:i A') : ($campaign->delivery_mode === 'after_registration_email' ? 'On registration' : '—') }}<br><small>{{ number_format($campaign->sent_count ?? 0) }}
                                delivered</small></td>
                        <td>
                            @if (in_array('whatsapp', $channels, true) && $campaign->delivery_mode !== 'save_template')
                                <a class="btn btn-sm btn-outline-success"
                                    href="{{ route('admin.notifications.whatsapp', $campaign->id) }}"><i
                                        class="bi bi-whatsapp"></i> Open queue</a>
                            @endif
                        </td>
                </tr>@empty<tr>
                        <td colspan="8" class="text-center text-muted py-5">No communications configured yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div><x-admin-pagination :paginator="$campaigns" />
@endsection
