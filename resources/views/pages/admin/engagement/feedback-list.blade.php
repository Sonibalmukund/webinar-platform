@extends('layouts.portal')
@section('title', 'Feedback')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow">AUDIENCE VOICE</span><h1>Feedback</h1><p>All feedback from enabled webinars in one listing.</p></div>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<section class="panel-card table-responsive">
    <table class="premium-table">
        <thead><tr><th>User</th><th>Webinar</th><th>Rating</th><th>Feedback</th><th>Received</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td><strong>{{ $item->user_name ?: 'Guest' }}</strong><br><small>{{ $item->user_email }}</small></td>
                <td>{{ $item->webinar_title }}</td>
                <td><span class="text-warning">@for($i=1; $i<=5; $i++)<i class="bi bi-star{{ $i <= ($item->rating ?? 0) ? '-fill' : '' }}"></i>@endfor</span></td>
                <td>{{ $item->message }}</td>
                <td>{{ Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.feedback.update', [$item->webinar_id, $item->id]) }}">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach(['new','reviewed','resolved'] as $status)
                                <option value="{{ $status }}" @selected($item->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No feedback received.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
