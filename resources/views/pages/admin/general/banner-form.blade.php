@extends('layouts.portal')
@section('title',$banner->exists?'Edit Banner':'Add Banner')
@section('content')
<div class="page-heading"><div><span class="eyebrow">GENERAL SETTINGS</span><h1>{{ $banner->exists?'Edit':'Add' }} banner</h1><p>Select a webinar and add an image or video banner.</p></div></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form class="panel-card form-panel" method="POST" enctype="multipart/form-data" action="{{ $banner->exists?route('admin.general.banners.update',$banner):route('admin.general.banners.store') }}">@csrf @if($banner->exists)@method('PUT')@endif
    <div class="form-grid">
        <label class="full">Webinar<select class="form-select" name="webinar_id" required><option value="">Select webinar</option>@foreach($webinars as $webinar)<option value="{{ $webinar->id }}" @selected((int)old('webinar_id',$banner->webinar_id)===$webinar->id)>{{ $webinar->title }}</option>@endforeach</select></label>
        <label>Title<input class="form-control" name="title" value="{{ old('title',$banner->title) }}" required></label>
        <label>Banner type<select class="form-select" name="media_type" id="bannerMediaType"><option value="image" @selected(old('media_type',$banner->media_type?:'image')==='image')>Image</option><option value="video" @selected(old('media_type',$banner->media_type)==='video')>Video</option></select></label>
        <div class="full" id="bannerImageFields">
            <label>Banner image<input class="form-control" id="bannerImageInput" type="file" name="image_media" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, maximum 5 MB.</small></label>
        </div>
        <div class="full" id="bannerVideoFields" hidden>
            <label>Video source</label><div class="d-flex gap-3 mb-3"><label class="flex-row"><input type="radio" name="video_source" value="upload" @checked(!$banner->media_url)> Upload video</label><label class="flex-row"><input type="radio" name="video_source" value="url" @checked((bool)$banner->media_url)> Video URL</label></div>
            <div id="bannerVideoUpload"><label>Video file<input class="form-control" id="bannerVideoInput" type="file" name="video_media" accept="video/mp4,video/webm,video/quicktime"><small>MP4, WebM or MOV, maximum 20 MB.</small></label></div>
            <div id="bannerVideoUrl" hidden><label>Video URL<input class="form-control" id="bannerVideoUrlInput" name="media_url" value="{{ old('media_url',$banner->media_url) }}" placeholder="https://..."></label></div>
        </div>
        <label>Show from<input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at',$banner->starts_at?->format('Y-m-d\TH:i')) }}"></label>
        <label>Show until<input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at',$banner->ends_at?->format('Y-m-d\TH:i')) }}"></label>
        <div class="full"><div class="banner-live-preview" id="bannerLivePreview">@if($banner->media_path && $banner->media_type==='image')<img src="{{ $banner->media_path }}" alt="Banner preview">@elseif(($banner->media_path||$banner->media_url) && $banner->media_type==='video')<video src="{{ $banner->media_path?:$banner->media_url }}" controls></video>@else<div><i class="bi bi-image"></i><span>Select a banner file to preview it here.</span></div>@endif</div></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="{{ route('admin.general.banners') }}">Cancel</a><button class="btn btn-gradient">Save banner</button></div>
</form>
@endsection
