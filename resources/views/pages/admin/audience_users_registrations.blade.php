@extends('layouts.portal')
@section('title', $title)
@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $title }}</h1>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->hasRole('super-admin'))
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#attendeeImportModal">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import Attendees
                </button>
            @endif
            @if ($type === 'users' && (auth()->user()->hasRole('super-admin') || auth()->user()->hasPermission('users.export')))
                <a class="btn btn-light" href="{{ route('admin.users.export', request()->query()) }}"><i
                        class="bi bi-download"></i> Export Excel</a>
            @endif
        </div>
    </div>

    @if (auth()->user()->hasRole('super-admin'))
        @if ($errors->has('sheet') || $errors->has('webinar_id'))
            <div class="alert alert-danger">
                <strong>Import failed.</strong> {{ $errors->first('sheet') ?: $errors->first('webinar_id') }}
            </div>
        @endif
        @if (session('import_warnings'))
            <div class="alert alert-warning">
                <strong>Some rows were skipped:</strong>
                <ul class="mb-0 mt-1">
                    @foreach (session('import_warnings') as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="modal fade" id="attendeeImportModal" tabindex="-1" aria-labelledby="attendeeImportTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="attendeeImportTitle">Import attendees</h5>
                                <small class="text-muted">Super Admin only · CSV or XLSX · maximum 5,000 rows</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="importWebinar">Webinar</label>
                                <select class="form-select" id="importWebinar" name="webinar_id" required>
                                    <option value="">Select webinar</option>
                                    @foreach ($webinars as $webinar)
                                        <option value="{{ $webinar->id }}" @selected((int) old('webinar_id', request('webinar_id')) === $webinar->id)>{{ $webinar->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="attendeeSheet">Attendee sheet</label>
                                <input class="form-control" id="attendeeSheet" type="file" name="sheet" accept=".csv,.txt,.xlsx" required>
                            </div>
                            <div class="rounded border p-3 bg-light small">
                                <strong>Required:</strong> Email. <strong>Supported:</strong> Name, Mobile, Company and Designation.<br>
                                Extra columns such as <code>emp_id</code>, Department or Branch Code are saved automatically as hidden webinar fields.
                            </div>
                            <a class="d-inline-block mt-3" href="{{ route('admin.users.import-template') }}">
                                <i class="bi bi-download"></i> Download sample CSV
                            </a>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Import attendees</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($type === 'users')
        <div class="filter-bar">
            <form class="module-filter-bar w-100 d-flex gap-2 align-items-center flex-wrap" method="GET"
                action="{{ url()->current() }}">
                <div class="filter-search">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ request('search') }}"
                        placeholder="Search users by name, email, mobile..." aria-label="Search users">
                </div>
                @if (isset($webinars) && !auth()->user()->hasRole('sub-admin'))
                    <select class="form-select" name="webinar_id" onchange="this.form.submit()"
                        aria-label="Filter by webinar">
                        <option value="">All webinars</option>
                        @foreach ($webinars as $webinar)
                            <option value="{{ $webinar->id }}" @selected((int) request('webinar_id') === $webinar->id)>{{ $webinar->title }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="form-select" name="status" onchange="this.form.submit()" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
                @if (request()->hasAny(['search', 'webinar_id', 'status']))
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>
    @elseif($type === 'registrations')
        <div class="filter-bar">
            <form class="module-filter-bar w-100 d-flex gap-2 align-items-center flex-wrap" method="GET"
                action="{{ url()->current() }}">
                <div class="filter-search">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ request('search') }}"
                        placeholder="Search attendees by name, email, phone..." aria-label="Search registrations">
                </div>
                @if (isset($webinars) && !auth()->user()->hasRole('sub-admin'))
                    <select class="form-select" name="webinar_id" onchange="this.form.submit()"
                        aria-label="Filter by webinar">
                        <option value="">All webinars</option>
                        @foreach ($webinars as $webinar)
                            <option value="{{ $webinar->id }}" @selected((int) request('webinar_id') === $webinar->id)>{{ $webinar->title }}</option>
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
                @if (request()->hasAny(['search', 'webinar_id', 'status']))
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
                        @if ($type === 'users')
                            <th>Index</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Webinar</th>
                            @foreach ($dynamicCols as $col)
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
                            @foreach ($dynamicCols as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                            <th>Registered At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if (isset($databaseRows) && $type === 'users')
                        @forelse($databaseRows as $user)
                            @php($primaryReg = $user->registrations->first())
                            <tr>
                                <td>{{ $databaseRows->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="speaker-line">
                                        <span
                                            class="mini-avatar">{{ collect(explode(' ', $user->name))->map(fn($part) => $part[0] ?? '')->take(2)->join('') }}</span>
                                        <strong>{{ $user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->mobile ?: '—' }}</td>
                                <td>
                                    @if ($user->registrations->isNotEmpty())
                                        @foreach ($user->registrations->take(2) as $reg)
                                            <span class="d-inline-block text-truncate" style="max-width: 220px;"
                                                title="{{ $reg->webinar?->title }}">
                                                <i
                                                    class="bi bi-camera-video text-muted me-1"></i>{{ $reg->webinar?->title ?? 'Webinar' }}
                                            </span>
                                            @if (!$loop->last)
                                                <br>
                                            @endif
                                        @endforeach
                                        @if ($user->registrations->count() > 2)
                                            <br><small class="text-muted">+{{ $user->registrations->count() - 2 }}
                                                more</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @foreach ($dynamicCols as $col)
                                    <td>{{ $user->dynamic_fields[$col] ?? '—' }}</td>
                                @endforeach
                                <td>{{ optional($primaryReg?->registered_at ?? $user->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ ($user->status ?? 'active') === 'active' ? 'completed' : 'draft' }}">{{ ucfirst($user->status ?? 'active') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        @if ($primaryReg)
                                            <a class="icon-btn" href="{{ route('admin.registrations.show', $primaryReg) }}"
                                                title="View attendee journey"><i class="bi bi-eye"></i></a>
                                            <form method="POST"
                                                action="{{ route('admin.registrations.destroy', $primaryReg) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this registration?');"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_to" value="users">
                                                <button type="submit" class="icon-btn text-danger"
                                                    title="Delete registration"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        @else
                                            <span class="icon-btn disabled text-muted" title="No webinar registration"><i
                                                    class="bi bi-eye"></i></span>
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
                                        <span
                                            class="mini-avatar">{{ collect(explode(' ', $registration->user?->name ?? $registration->email))->map(fn($part) => $part[0])->take(2)->join('') }}</span>
                                        <strong>{{ $registration->user?->name ?? 'Guest attendee' }}</strong>
                                    </div>
                                </td>
                                <td>{{ $registration->email }}</td>
                                <td>{{ $registration->user?->mobile ?: ($registration->mobile ?: '—') }}</td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;"
                                        title="{{ $registration->webinar?->title }}">
                                        <i
                                            class="bi bi-camera-video text-muted me-1"></i>{{ $registration->webinar?->title ?? 'Removed webinar' }}
                                    </span>
                                </td>
                                @foreach ($dynamicCols as $col)
                                    <td>{{ $registration->dynamic_fields[$col] ?? '—' }}</td>
                                @endforeach
                                <td>{{ $registration->registered_at?->format('M d, Y g:i A') }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $registrationLabel === 'Registered' ? 'completed' : 'scheduled' }}">{{ $registrationLabel }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a class="icon-btn" href="{{ route('admin.registrations.show', $registration) }}"
                                            title="View audience journey"><i class="bi bi-eye"></i></a>
                                        <form method="POST"
                                            action="{{ route('admin.registrations.destroy', $registration) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this registration?');"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn text-danger"
                                                title="Delete registration"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 8 + count($dynamicCols) }}">
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-person-check fs-2 d-block mb-2"></i>No webinar registrations yet.
                                        Submit a webinar registration to create the first one.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
        @if (isset($databaseRows))
            <x-admin-pagination :paginator="$databaseRows" />
        @endif
    </div>
@endsection
