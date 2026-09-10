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
            <label>Brand logo</label>
            <div class="d-flex gap-3 mb-2">
                <label class="flex-row cursor-pointer">
                    <input type="radio" name="logo_source" value="upload" id="logoSourceUpload" @checked(!filter_var($brand->logo_path, FILTER_VALIDATE_URL))> Upload image file
                </label>
                <label class="flex-row cursor-pointer">
                    <input type="radio" name="logo_source" value="url" id="logoSourceUrl" @checked((bool)filter_var($brand->logo_path, FILTER_VALIDATE_URL))> Logo image URL
                </label>
            </div>
            <div id="logoUploadBox" class="mb-2">
                <input class="form-control" type="file" name="logo" accept="image/*">
                <small class="text-muted">Upload PNG, SVG, or JPG (transparent background recommended, max 5MB).</small>
            </div>
            <div id="logoUrlBox" class="mb-2" hidden>
                <input class="form-control" type="url" name="logo_url" value="{{ old('logo_url', filter_var($brand->logo_path, FILTER_VALIDATE_URL) ? $brand->logo_path : '') }}" placeholder="https://cdn.example.com/logo.png">
                <small class="text-muted">Enter a direct HTTPS link to the logo image.</small>
            </div>
            @if($brand->logo_path)
                <div class="mt-2 p-3 border rounded bg-light d-flex align-items-center gap-3" style="max-width: 320px;">
                    <span class="text-muted small">Current logo:</span>
                    <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const uploadRadio = document.getElementById('logoSourceUpload');
        const urlRadio = document.getElementById('logoSourceUrl');
        const uploadBox = document.getElementById('logoUploadBox');
        const urlBox = document.getElementById('logoUrlBox');

        function toggle() {
            if (urlRadio && urlRadio.checked) {
                if (uploadBox) uploadBox.hidden = true;
                if (urlBox) urlBox.hidden = false;
            } else {
                if (uploadBox) uploadBox.hidden = false;
                if (urlBox) urlBox.hidden = true;
            }
        }

        uploadRadio?.addEventListener('change', toggle);
        urlRadio?.addEventListener('change', toggle);
        toggle();
    });
</script>
@endsection
