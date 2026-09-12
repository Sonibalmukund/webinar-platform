@extends('layouts.portal')
@section('title', 'Feedback')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow">AUDIENCE VOICE</span><h1>Feedback</h1><p>All feedback from enabled webinars in one listing.</p></div>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<x-admin-webinar-filter :webinars="$webinars" :selected="$webinarId" :search="$search" />
<section class="panel-card table-responsive">
    <table class="premium-table">
        <thead><tr><th>User</th><th>Webinar</th><th>Rating</th><th>Feedback</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td><strong>{{ $item->user_name ?: 'Guest' }}</strong><br><small>{{ $item->user_email }}</small></td>
                <td>{{ $item->webinar_title }}</td>
                <td><span class="text-warning">@for($i=1; $i<=5; $i++)<i class="bi bi-star{{ $i <= ($item->rating ?? 0) ? '-fill' : '' }}"></i>@endfor</span></td>
                <td>{{ $item->message }}</td>
                <td>{{ Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                <td><a class="btn btn-sm btn-light text-nowrap" href="{{ route('admin.feedback.show', $item->webinar_id) }}#feedback-{{ $item->id }}"><i class="bi bi-eye"></i> View</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No feedback received.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
<x-admin-pagination :paginator="$items" />
@endsection
