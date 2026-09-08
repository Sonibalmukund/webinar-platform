@extends('layouts.portal')
@section('title','Site Settings')
@section('content')
<div class="page-heading"><div><span class="eyebrow">GENERAL SETTINGS</span><h1>Site settings</h1><p>Manage the platform name, logos, favicon, footer and administrator contact.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form class="panel-card form-panel" method="POST" enctype="multipart/form-data" action="{{ route('admin.general.site.update') }}">@csrf @method('PUT')
    <div class="form-grid">
        <label class="full">Site name<input class="form-control" name="site_name" value="{{ old('site_name',$settings['site_name']??'Webinarly') }}" required></label>
        @foreach(['site_logo'=>'Site logo','small_logo'=>'Small site logo','favicon'=>'Favicon'] as $key=>$label)
            <label class="full">{{ $label }}<input class="form-control" type="file" name="{{ $key }}" accept="image/*" data-site-preview-input="{{ $key }}"><img class="site-setting-preview" data-site-preview="{{ $key }}" src="{{ $settings[$key]??'' }}" alt="{{ $label }} preview" @if(!($settings[$key]??null)) hidden @endif></label>
        @endforeach
        <label class="full">Footer text<textarea class="form-control" name="footer_text" required>{{ old('footer_text',$settings['footer_text']??'Copyright reserved.') }}</textarea></label>
        <label class="full">Admin email<input class="form-control" type="email" name="admin_email" value="{{ old('admin_email',$settings['admin_email']??auth()->user()->email) }}" required></label>
    </div>
    <button class="btn btn-gradient mt-4">Save site settings</button>
</form>
@endsection
