@extends('layouts.portal')
@section('title','Certificates')
@section('content')
<div class="page-heading"><div><span class="eyebrow">CERTIFICATE MODULE</span><h1>Webinar certificates</h1><p>Each webinar can use its own client-specific certificate design.</p></div><a class="btn btn-gradient" href="{{ route('admin.certificates.create') }}"><i class="bi bi-plus"></i> Add certificate</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="panel-card table-responsive"><table class="premium-table"><thead><tr><th>Webinar / client</th><th>Public link</th><th>Template</th><th>Visibility</th><th>Controls</th></tr></thead><tbody>
@forelse($webinars as $webinar)@php($template=$templates->firstWhere('id',data_get($webinar->settings,'certificate_template_id')))<tr><td><strong>{{ $webinar->title }}</strong></td><td><a href="{{ route('webinars.show',$webinar) }}" target="_blank">Open unique link <i class="bi bi-box-arrow-up-right"></i></a></td><td>{{ $template?->name ?? 'Not configured' }}</td><td><span class="status-badge {{ $webinar->certificate_enabled==='yes'?'live':'scheduled' }}">{{ $webinar->certificate_enabled==='yes'?'VISIBLE':'HIDDEN' }}</span></td><td><div class="d-flex gap-1"><a class="btn btn-sm btn-light" href="{{ route('admin.certificates.edit',$webinar) }}">{{ $template?'Edit template':'Add template' }}</a><form method="POST" action="{{ route('admin.certificates.visibility',$webinar) }}">@csrf @method('PATCH')<input type="hidden" name="enabled" value="{{ $webinar->certificate_enabled==='yes'?0:1 }}"><button class="btn btn-sm {{ $webinar->certificate_enabled==='yes'?'btn-secondary':'btn-success' }}">{{ $webinar->certificate_enabled==='yes'?'Hide':'Show' }}</button></form></div></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-5">Create a webinar first.</td></tr>@endforelse
</tbody></table></div><div class="mt-3">{{ $webinars->links() }}</div>
@endsection
