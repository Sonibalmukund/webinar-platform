@extends('layouts.portal')
@section('title', $speaker->exists ? 'Edit Speaker' : 'Add Speaker')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">WEBINAR MANAGEMENT</span>
        <h1>{{ $speaker->exists ? 'Edit' : 'Add' }} speaker</h1>
        <p>Manage keynote speakers, presenters, and panelists for your webinars.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form class="panel-card form-panel" method="POST" enctype="multipart/form-data" action="{{ $speaker->exists ? route('admin.speakers.update', $speaker) : route('admin.speakers.store') }}">
    @csrf
    @if($speaker->exists)
        @method('PUT')
    @endif
    <div class="form-grid">
        <label class="full">Webinar
            <select class="form-select" name="webinar_id" required>
                <option value="">Select webinar</option>
                @foreach($webinars as $webinar)
                    <option value="{{ $webinar->id }}" @selected((int)old('webinar_id', $speaker->webinars->first()?->id) === $webinar->id)>{{ $webinar->title }}</option>
                @endforeach
            </select>
        </label>

        <label>Full name
            <input class="form-control" name="name" value="{{ old('name', $speaker->name) }}" required placeholder="e.g. Dr. Jane Smith">
        </label>

        <label>Email address (optional)
            <input class="form-control" type="email" name="email" value="{{ old('email', $speaker->email) }}" placeholder="jane@example.com">
        </label>

        <label>Headline / Role
            <input class="form-control" name="headline" value="{{ old('headline', $speaker->headline) }}" placeholder="e.g. Chief Medical Officer">
        </label>

        <label>Company / Organization
            <input class="form-control" name="company" value="{{ old('company', $speaker->company) }}" placeholder="e.g. HealthCorp Global">
        </label>

        <label class="full">Bio
            <textarea class="form-control" name="bio" rows="4" placeholder="Short biography about the speaker...">{{ old('bio', $speaker->bio) }}</textarea>
        </label>

        <div class="full">
            <label>Speaker Photo
                <input class="form-control" type="file" name="photo" accept="image/*">
                <small class="text-muted">JPG or PNG, recommended square 400x400px.</small>
            </label>
            @if($speaker->photo_path)
                <div class="mt-2 d-flex align-items-center gap-3">
                    <img src="{{ $speaker->photo_path }}" alt="{{ $speaker->name }}" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color, #e2e8f0);">
                    <span class="text-muted small">Current photo</span>
                </div>
            @endif
        </div>

        <div class="full">
            <label class="d-inline-flex align-items-center gap-2 m-0 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $speaker->exists ? $speaker->is_active : true))>
                <strong>Active speaker (visible on frontend)</strong>
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="{{ route('admin.speakers.index') }}">Cancel</a>
        <button type="submit" class="btn btn-gradient">Save speaker</button>
    </div>
</form>
@endsection
