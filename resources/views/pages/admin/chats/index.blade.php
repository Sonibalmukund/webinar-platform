@extends('layouts.portal')
@section('title','Live Chat')
@section('content')
<div class="page-heading"><div><span class="eyebrow">COMMUNICATION</span><h1>Live chat</h1><p>Only webinars with Live Chat enabled are listed.</p></div></div>
<x-admin-webinar-filter :webinars="$filterWebinars" :selected="$webinarId" :search="$search" />
<section class="panel-card table-responsive"><table class="premium-table"><thead><tr><th>Webinar</th><th>Status</th><th>Chatters</th><th>Messages</th><th>Last message</th><th>Action</th></tr></thead><tbody>@forelse($webinars as $webinar)@php($stat=$stats->get($webinar->id))<tr><td><strong>{{ $webinar->title }}</strong><br><small>{{ $webinar->starts_at?->format('d M Y · h:i A') ?: 'Schedule pending' }}</small></td><td><span class="status-badge {{ $webinar->status }}">{{ ucfirst($webinar->status) }}</span></td><td>{{ $stat->participants_count??0 }}</td><td>{{ $stat->messages_count??0 }}</td><td>{{ $stat?->last_message_at?Carbon\Carbon::parse($stat->last_message_at)->diffForHumans():'No messages yet' }}</td><td><a class="btn btn-sm btn-gradient" href="{{ route($routePrefix.'.chats.show',$webinar) }}"><i class="bi bi-eye"></i> View chat</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">No Live Chat enabled webinar.</td></tr>@endforelse</tbody></table></section>
@endsection
