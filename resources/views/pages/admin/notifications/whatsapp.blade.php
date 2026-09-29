@extends('layouts.portal')
@section('title', 'WhatsApp Queue')
@section('content')
    <div class="page-heading">
        <div><span class="eyebrow">WHATSAPP DELIVERY</span>
            <h1>{{ $campaign->subject }}</h1>
            <p>{{ $webinar?->title ?: 'All registered attendees' }} · Messages are personalized and ready to send.</p>
        </div><a class="btn btn-light" href="{{ route('admin.notifications.index') }}">Back</a>
    </div>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="alert alert-info"><strong>Why a queue?</strong> Automatic bulk delivery requires WhatsApp Business/Cloud API
        credentials. Until that is connected, each button safely opens the correct recipient and prefilled message without
        marking an unsent message as delivered.</div>
    <div class="panel-card table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Attendee</th>
                    <th>Mobile</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $row)
                    <tr>
                        <td><strong>{{ $row['user']->name }}</strong><br><small>{{ $row['user']->email }}</small></td>
                        <td>{{ $row['user']->mobile }}</td>
                        <td><a class="btn btn-sm btn-outline-success" href="{{ $row['url'] }}" target="_blank"
                                rel="noopener"><i class="bi bi-whatsapp"></i> Open WhatsApp</a></td>
                </tr>@empty<tr>
                        <td colspan="3" class="text-center text-muted py-5">No registered attendees have a mobile number.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
