@extends('layouts.portal')
@section('title', $brand->exists ? 'Edit Brand' : 'Add Brand')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">GENERAL SETTINGS</span>
        <h1>{{ $brand->exists ? 'Edit' : 'Add' }} brand</h1>
        <p>Manage event sponsors, partner brands, and logos displayed across the webinar.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form class="panel-card form-panel" method="POST" enctype="multipart/form-data" action="{{ $brand->exists ? route('admin.general.brands.update', $brand) : route('admin.general.brands.store') }}">
    @csrf
    @if($brand->exists)
        @method('PUT')
    @endif
    <div class="form-grid">
        <label class="full">Webinar
            <select class="form-select" name="webinar_id" required>
                <option value="">Select webinar</option>
                @foreach($webinars as $webinar)
                    <option value="{{ $webinar->id }}" @selected((int)old('webinar_id', $brand->webinar_id) === $webinar->id)>{{ $webinar->title }}</option>
                @endforeach
            </select>
        </label>

        <label>Brand name
            <input class="form-control" name="name" value="{{ old('name', $brand->name) }}" required placeholder="e.g. Acme Corp">
        </label>

        <label>Website URL (optional)
            <input class="form-control" name="website_url" value="{{ old('website_url', $brand->website_url) }}" placeholder="https://example.com">
        </label>

        <div class="full">
            <label>Choose brand logo</label>
            <div class="mb-2">
                <input class="form-control" type="file" name="logo" accept="image/*">
                <small class="text-muted">Upload PNG, SVG, or JPG (transparent background recommended, max 5MB).</small>
            </div>
            @if($brand->logo_path)
                <div class="mt-2 p-3 border rounded bg-light d-flex align-items-center gap-3" style="max-width: 320px;">
                    <span class="text-muted small">Current logo:</span>
                    <button type="button" class="btn p-0 border-0 bg-transparent" data-media-popup data-media-src="{{ $brand->logo_path }}" data-media-type="image" data-media-title="{{ $brand->name }}" data-media-badge="Brand Logo" title="Click to view logo in pop-up">
                        <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
                    </button>
                </div>
            @endif
        </div>

        <div class="full">
            <label class="d-inline-flex align-items-center gap-2 m-0 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand->exists ? $brand->is_active : true))>
                <strong>Active brand (visible on frontend)</strong>
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="{{ route('admin.general.brands') }}">Cancel</a>
        <button type="submit" class="btn btn-gradient">Save brand</button>
    </div>
</form>

@endsection
