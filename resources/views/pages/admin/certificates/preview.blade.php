@extends('layouts.portal')
@section('title','Certificate Preview')
@section('content')
@php
    $design=$template?->design ?? [];
    $positions=data_get($design,'positions',[]);
    $image=data_get($design,'template_image');
    $signature=data_get($design,'signature_image');
@endphp
<div class="page-heading">
    <div><span class="eyebrow">CERTIFICATE PREVIEW</span><h1>{{ $webinar->title }}</h1><p>This is how the configured certificate will look with sample attendee data.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-light" href="{{ route('admin.certificates.index') }}"><i class="bi bi-arrow-left"></i> Back</a><a class="btn btn-gradient" href="{{ route('admin.certificates.edit',$webinar) }}"><i class="bi bi-pencil"></i> Edit design</a></div>
</div>
@if(!$template)
    <div class="panel-card text-center py-5"><i class="bi bi-award display-5 text-muted"></i><h2 class="mt-3">No certificate template configured</h2><p class="text-muted">Add a template before opening its preview.</p><a class="btn btn-gradient" href="{{ route('admin.certificates.edit',$webinar) }}">Add template</a></div>
@else
    @if(data_get($design,'font_file'))<style>@font-face{font-family:CustomCertificateFont;src:url('{{ data_get($design,'font_file') }}')}</style>@endif
    <section class="panel-card certificate-preview-shell"><div class="certificate-preview certificate-readonly">
        <div class="cert-inner draggable-certificate" style="--cert-accent:#ee1f2d;background-image:{{ $image?"url('$image')":'none' }};font-family:{{ data_get($design,'font_family','Manrope') }}">
            @unless($image)<small>VIRTUALPORTAL CERTIFICATE</small><h2>{{ data_get($design,'headline','Certificate of Participation') }}</h2>@endunless
            <div class="certificate-drag-item recipient" style="left:{{ data_get($positions,'recipient.x',50) }}%;top:{{ data_get($positions,'recipient.y',44) }}%;width:{{ data_get($positions,'recipient.width',55) }}%;--element-scale:{{ data_get($positions,'recipient.scale',100)/100 }}">Sample Attendee</div>
            <div class="certificate-drag-item webinar" style="left:{{ data_get($positions,'webinar.x',50) }}%;top:{{ data_get($positions,'webinar.y',61) }}%;width:{{ data_get($positions,'webinar.width',55) }}%;--element-scale:{{ data_get($positions,'webinar.scale',100)/100 }}">{{ $webinar->title }}</div>
            <div class="certificate-drag-item meta" style="left:{{ data_get($positions,'date.x',20) }}%;top:{{ data_get($positions,'date.y',84) }}%;width:{{ data_get($positions,'date.width',25) }}%;--element-scale:{{ data_get($positions,'date.scale',100)/100 }}">{{ now()->format('F d, Y') }}</div>
            @if($signature)<div class="certificate-drag-item signature-image" style="left:{{ data_get($positions,'signature.x',80) }}%;top:{{ data_get($positions,'signature.y',76) }}%;width:{{ data_get($positions,'signature.width',22) }}%;--element-scale:{{ data_get($positions,'signature.scale',100)/100 }}"><img src="{{ $signature }}" alt="Signature"></div>@endif
            <div class="certificate-drag-item meta" style="left:{{ data_get($positions,'signatory.x',80) }}%;top:{{ data_get($positions,'signatory.y',86) }}%;width:{{ data_get($positions,'signatory.width',30) }}%;--element-scale:{{ data_get($positions,'signatory.scale',100)/100 }}">{{ data_get($design,'signatory','Authorized Signatory') }}</div>
        </div>
    </div></section>
@endif
@endsection
