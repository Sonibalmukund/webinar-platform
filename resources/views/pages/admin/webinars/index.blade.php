@extends('layouts.portal')
@section('title','Manage Webinars')
@section('content')
<div class="page-heading"><div><span class="eyebrow">ADMIN WORKSPACE</span><h1>Webinars</h1><p>Every webinar has its own public client URL.</p></div><a class="btn btn-gradient" href="{{ route('admin.webinars.create') }}"><i class="bi bi-plus-lg"></i> Create webinar</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="filter-bar">
    <form class="module-filter-bar w-100 d-flex gap-2 align-items-center flex-wrap" method="GET" action="{{ route('admin.webinars.index') }}">
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search webinars by title, description or slug..." aria-label="Search webinars">
        </div>
        <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['draft','scheduled','live','completed','cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.webinars.index') }}" class="btn btn-outline-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="panel-card"><div class="table-responsive"><table class="premium-table"><thead><tr><th>Index</th><th>Webinar</th><th>Client URL</th><th>Starts</th><th>Registrations</th><th>Health</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($webinars as $webinar)
@php($publicUrl=route('webinars.show',$webinar))
<tr>
<td>{{ $webinars->firstItem()+$loop->index }}</td>
<td><div class="speaker-line"><span class="table-webinar purple"><i class="bi bi-{{ $webinar->icon ?: 'camera-video' }}"></i></span><span><strong>{{ $webinar->title }}</strong>@if($webinar->status === 'live')<span class="badge bg-danger ms-1 text-uppercase" style="font-size:0.62rem;letter-spacing:0.04em;"><i class="bi bi-broadcast me-1"></i>Live</span>@endif<small>{{ Str::limit($webinar->short_description,70) }}</small></span></div></td>
<td><div class="webinar-url-cell"><div class="webinar-url-value" title="{{ $publicUrl }}"><i class="bi bi-link-45deg"></i><span>/webinars/{{ $webinar->slug }}</span></div><div class="webinar-url-actions"><button type="button" class="url-action copy" data-copy-url="{{ $publicUrl }}"><i class="bi bi-copy"></i><span>Copy URL</span></button><a class="url-action open" href="{{ $publicUrl }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>Open page</span></a></div></div></td>
<td>{{ $webinar->starts_at?->timezone($webinar->timezone)->format('M d, Y g:i A') ?: 'Not scheduled' }}</td>
<td>{{ $webinar->registrations_count }}</td><td><span class="status-badge {{ $webinar->health['score']>=70?'active':($webinar->health['score']>=40?'scheduled':'draft') }}" title="Attendance {{ $webinar->health['attendanceRate'] }}% · Poll participation {{ $webinar->health['pollRate'] }}%">{{ $webinar->health['score'] }}/100</span></td>
<td><form class="webinar-status-form {{ $webinar->status }}" method="POST" action="{{ route('admin.webinars.status',$webinar) }}" data-no-validation>@csrf @method('PATCH')<i></i><select class="webinar-status-select" name="status" onchange="this.form.submit()" aria-label="Change webinar status">@foreach(['draft','scheduled','live','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($webinar->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><span class="bi bi-chevron-down"></span></form></td>
<td><div class="dropdown"><button class="icon-btn" data-bs-toggle="dropdown" aria-label="Actions"><i class="bi bi-three-dots"></i></button><ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"><li><a class="dropdown-item text-danger fw-bold d-flex align-items-center" href="{{ route('admin.webinars.live',$webinar) }}"><i class="bi bi-broadcast me-2"></i>Live control</a></li><li><a class="dropdown-item d-flex align-items-center" href="{{ route('admin.webinars.show',$webinar) }}"><i class="bi bi-eye me-2"></i>View overview</a></li><li><a class="dropdown-item d-flex align-items-center" href="{{ route('admin.webinars.edit',$webinar) }}"><i class="bi bi-pencil me-2"></i>Edit webinar</a></li><li><form method="POST" action="{{ route('admin.webinars.clone',$webinar) }}">@csrf<button class="dropdown-item d-flex align-items-center"><i class="bi bi-copy me-2"></i>Clone as draft</button></form></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="{{ route('admin.webinars.destroy',$webinar) }}" onsubmit="return confirm('Delete this webinar?')">@csrf @method('DELETE')<button class="dropdown-item text-danger d-flex align-items-center"><i class="bi bi-trash me-2"></i>Delete</button></form></li></ul></div></td>
</tr>
@empty<tr><td colspan="8" class="text-center py-5 text-muted">No webinars yet. Create the first webinar.</td></tr>@endforelse
</tbody></table></div><x-admin-pagination :paginator="$webinars" /></div>
@endsection
