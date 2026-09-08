@extends('layouts.portal')
@section('title','Add Certificate')
@section('content')
<div class="page-heading"><div><span class="eyebrow">CERTIFICATE MODULE</span><h1>Add certificate</h1><p>Select the webinar for which you want to create a certificate.</p></div><a class="btn btn-light" href="{{ route('admin.certificates.index') }}">Back</a></div>
<div class="panel-card form-panel"><label>Webinar / client<select class="form-select" required onchange="if(this.value) window.location.href=this.value"><option value="">Choose webinar</option>@foreach($webinars as $webinar)<option value="{{ route('admin.certificates.edit',$webinar) }}">{{ $webinar->title }}</option>@endforeach</select><small class="text-muted mt-2 d-block">The certificate designer opens immediately after selection.</small></label></div>
@endsection
