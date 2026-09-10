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
        <div class="full" id="bannerImageFields" @if(old('media_type',$banner->media_type)==='video') hidden @endif>
            <label>Image source</label>
            <div class="d-flex gap-3 mb-3">
                <label class="flex-row"><input type="radio" name="image_source" value="upload" @checked(old('image_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'upload')> Upload image</label>
                <label class="flex-row"><input type="radio" name="image_source" value="url" @checked(old('image_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'url')> Image URL</label>
            </div>
            <div id="bannerImageUpload" @if(old('image_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'url') hidden @endif><label>Banner image file<input class="form-control" id="bannerImageInput" type="file" name="image_media" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, maximum 5 MB.</small></label></div>
            <div id="bannerImageUrl" @if(old('image_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') !== 'url') hidden @endif><label>Image URL<input class="form-control" id="bannerImageUrlInput" name="media_url" value="{{ old('media_url',$banner->media_type==='image'?$banner->media_url:'') }}" placeholder="https://images.unsplash.com/..."></label></div>
        </div>
        <div class="full" id="bannerVideoFields" @if(old('media_type',$banner->media_type?:'image')!=='video') hidden @endif>
            <label>Video source</label><div class="d-flex gap-3 mb-3"><label class="flex-row"><input type="radio" name="video_source" value="upload" @checked(old('video_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'upload')> Upload video</label><label class="flex-row"><input type="radio" name="video_source" value="url" @checked(old('video_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'url')> Video URL</label></div>
            <div id="bannerVideoUpload" @if(old('video_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') === 'url') hidden @endif><label>Video file<input class="form-control" id="bannerVideoInput" type="file" name="video_media" accept="video/mp4,video/webm,video/quicktime"><small>MP4, WebM or MOV, maximum 20 MB.</small></label></div>
            <div id="bannerVideoUrl" @if(old('video_source', ($banner->media_url && !$banner->media_path) ? 'url' : 'upload') !== 'url') hidden @endif><label>Video URL<input class="form-control" id="bannerVideoUrlInput" name="media_url_video" value="{{ old('media_url_video',$banner->media_type==='video'?$banner->media_url:'') }}" placeholder="https://... (Direct MP4, WebM, YouTube or Vimeo URL)"></label></div>
        </div>
        <label>Show from (webinar local time)<input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at',$banner->starts_at?->timezone($banner->webinar?->timezone?:'UTC')->format('Y-m-d\TH:i')) }}"></label>
        <label>Show until (webinar local time)<input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at',$banner->ends_at?->timezone($banner->webinar?->timezone?:'UTC')->format('Y-m-d\TH:i')) }}"></label>
        <div class="full">
            <label class="d-inline-flex align-items-center gap-2 m-0 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$banner->exists?$banner->is_active:true))>
                <strong>Active banner</strong>
            </label>
        </div>
        <div class="full"><div class="banner-live-preview" id="bannerLivePreview">@php($previewMediaSrc=$banner->media_path?:$banner->media_url)@if($previewMediaSrc && $banner->media_type==='image')<img src="{{ $previewMediaSrc }}" alt="Banner preview">@elseif($previewMediaSrc && $banner->media_type==='video')@php(preg_match('/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/i',$previewMediaSrc,$previewYt))@php(preg_match('/vimeo\.com\/(?:video\/)?(\d{6,12})/i',$previewMediaSrc,$previewVim))@if(!empty($previewYt[1]))<iframe src="https://www.youtube-nocookie.com/embed/{{ $previewYt[1] }}?autoplay=1&mute=1&loop=1&playlist={{ $previewYt[1] }}&controls=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>@elseif(!empty($previewVim[1]))<iframe src="https://player.vimeo.com/video/{{ $previewVim[1] }}?autoplay=1&muted=1&loop=1&autopause=0&background=1" allow="autoplay; fullscreen" allowfullscreen></iframe>@else<video src="{{ $previewMediaSrc }}" controls></video>@endif @else<div><i class="bi bi-image"></i><span>Select or link a banner to preview it here.</span></div>@endif</div></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="{{ route('admin.general.banners') }}">Cancel</a><button class="btn btn-gradient">Save banner</button></div>
</form>
@endsection
