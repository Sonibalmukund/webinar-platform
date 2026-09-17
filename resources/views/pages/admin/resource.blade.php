@extends('layouts.portal')
@section('title', $title)
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">ADMIN WORKSPACE</span>
        <h1>{{ $title }}</h1>
        <p>Manage event operations with clear context and confident actions.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-light"><i class="bi bi-download"></i> Export</button>
    </div>
</div>

@if($type === 'users')
<div class="filter-bar">
    <form class="module-filter-bar w-100 d-flex gap-2 align-items-center flex-wrap" method="GET" action="{{ url()->current() }}">
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search users by name, email, mobile..." aria-label="Search users">
        </div>
        @if(isset($webinars) && !auth()->user()->hasRole('sub-admin'))
            <select class="form-select" name="webinar_id" onchange="this.form.submit()" aria-label="Filter by webinar">
                <option value="">All webinars</option>
                @foreach($webinars as $webinar)
                    <option value="{{ $webinar->id }}" @selected((int)request('webinar_id') === $webinar->id)>{{ $webinar->title }}</option>
                @endforeach
            </select>
        @endif
        <select class="form-select" name="status" onchange="this.form.submit()" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search', 'webinar_id', 'status']))
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
        @endif
    </form>
</div>
@elseif($type === 'registrations')
<div class="filter-bar">
    <form class="module-filter-bar w-100 d-flex gap-2 align-items-center flex-wrap" method="GET" action="{{ url()->current() }}">
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search attendees by name, email, phone..." aria-label="Search registrations">
        </div>
        @if(isset($webinars) && !auth()->user()->hasRole('sub-admin'))
            <select class="form-select" name="webinar_id" onchange="this.form.submit()" aria-label="Filter by webinar">
                <option value="">All webinars</option>
                @foreach($webinars as $webinar)
                    <option value="{{ $webinar->id }}" @selected((int)request('webinar_id') === $webinar->id)>{{ $webinar->title }}</option>
                @endforeach
            </select>
        @endif
        <select class="form-select" name="status" onchange="this.form.submit()" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search', 'webinar_id', 'status']))
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
        @endif
    </form>
</div>
@endif

@php($dynamicCols = $dynamicColumns ?? ($databaseRows->dynamic_columns ?? []))
<div class="panel-card">
    <div class="table-responsive">
        <table class="premium-table" id="resourceTable">
            <thead>
                <tr>
                    @if($type === 'users')
                        <th>Index</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Webinar</th>
                        @foreach($dynamicCols as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    @elseif($type === 'registrations')
                        <th>Index</th>
                        <th>Attendee</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Webinar</th>
                        @foreach($dynamicCols as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                        <th>Registered At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(isset($databaseRows) && $type === 'users')
                    @forelse($databaseRows as $user)
                        @php($primaryReg = $user->registrations->first())
                        <tr>
                            <td>{{ $databaseRows->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="speaker-line">
                                    <span class="mini-avatar">{{ collect(explode(' ', $user->name))->map(fn($part) => $part[0] ?? '')->take(2)->join('') }}</span>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->mobile ?: '—' }}</td>
                            <td>
                                @if($user->registrations->isNotEmpty())
                                    @foreach($user->registrations->take(2) as $reg)
                                        <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $reg->webinar?->title }}">
                                            <i class="bi bi-camera-video text-muted me-1"></i>{{ $reg->webinar?->title ?? 'Webinar' }}
                                        </span>
                                        @if(!$loop->last)<br>@endif
                                    @endforeach
                                    @if($user->registrations->count() > 2)
                                        <br><small class="text-muted">+{{ $user->registrations->count() - 2 }} more</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @foreach($dynamicCols as $col)
                                <td>{{ $user->dynamic_fields[$col] ?? '—' }}</td>
                            @endforeach
                            <td>{{ ($primaryReg?->registered_at ?? $user->created_at)?->format('M d, Y') }}</td>
                            <td>
                                <span class="status-badge {{ ($user->status ?? 'active') === 'active' ? 'completed' : 'draft' }}">{{ ucfirst($user->status ?? 'active') }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @if($primaryReg)
                                        <a class="icon-btn" href="{{ route('admin.registrations.show', $primaryReg) }}" title="View attendee journey"><i class="bi bi-eye"></i></a>
                                        <form method="POST" action="{{ route('admin.registrations.destroy', $primaryReg) }}" onsubmit="return confirm('Are you sure you want to delete this registration?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn text-danger" title="Delete registration"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    @else
                                        <span class="icon-btn disabled text-muted" title="No webinar registration"><i class="bi bi-eye"></i></span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 8 + count($dynamicCols) }}">
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>No registered users found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @elseif(isset($databaseRows) && $type === 'registrations')
                    @forelse($databaseRows as $registration)
                        @php($registrationLabel = in_array($registration->status, ['approved', 'pending'], true) ? 'Registered' : ucfirst($registration->status))
                        <tr>
                            <td>{{ $databaseRows->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="speaker-line">
                                    <span class="mini-avatar">{{ collect(explode(' ', $registration->user?->name ?? $registration->email))->map(fn($part) => $part[0])->take(2)->join('') }}</span>
                                    <strong>{{ $registration->user?->name ?? 'Guest attendee' }}</strong>
                                </div>
                            </td>
                            <td>{{ $registration->email }}</td>
                            <td>{{ $registration->user?->mobile ?: ($registration->mobile ?: '—') }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $registration->webinar?->title }}">
                                    <i class="bi bi-camera-video text-muted me-1"></i>{{ $registration->webinar?->title ?? 'Removed webinar' }}
                                </span>
                            </td>
                            @foreach($dynamicCols as $col)
                                <td>{{ $registration->dynamic_fields[$col] ?? '—' }}</td>
                            @endforeach
                            <td>{{ $registration->registered_at?->format('M d, Y g:i A') }}</td>
                            <td>
                                <span class="status-badge {{ $registrationLabel === 'Registered' ? 'completed' : 'scheduled' }}">{{ $registrationLabel }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <a class="icon-btn" href="{{ route('admin.registrations.show', $registration) }}" title="View audience journey"><i class="bi bi-eye"></i></a>
                                    <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" onsubmit="return confirm('Are you sure you want to delete this registration?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn text-danger" title="Delete registration"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 8 + count($dynamicCols) }}">
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-person-check fs-2 d-block mb-2"></i>No webinar registrations yet. Reserve a seat from a learner account to create the first one.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
    @if(isset($databaseRows))
        <x-admin-pagination :paginator="$databaseRows" />
    @endif
</div>
@endsection
