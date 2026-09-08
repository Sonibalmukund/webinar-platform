@extends('layouts.portal')
@section('title',ucfirst($type).' · '.$webinar->title)
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ strtoupper($type) }} LOG</span><h1>{{ $webinar->title }}</h1><p>{{ $items->count() }} {{ $type }} received</p></div><a href="{{ route('admin.'.$type.'.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="audience-voice-list">
@forelse($items as $item)
    <article><span class="chat-avatar">{{ Str::of($item->user_name?:'G')->substr(0,1)->upper() }}</span><div class="voice-content"><div><span><strong>{{ $item->user_name?:'Guest user' }}</strong><small>{{ $item->user_email }} · {{ Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small></span>@if($type==='feedback')<div class="voice-stars">@for($i=1;$i<=5;$i)<i class="bi bi-star{{ $i<=($item->rating??0)?'-fill':'' }}"></i>@endfor</div>@endif</div><p>{{ $type==='feedback'?$item->message:$item->comment }}</p><footer><span class="status-badge {{ ($item->status??'visible')==='new'?'scheduled':'active' }}">{{ ucfirst($item->status??'visible') }}</span>@if($type==='comments')<form method="POST" action="{{ route('admin.comments.destroy',[$webinar,$item->id]) }}" onsubmit="return confirm('Remove this comment?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash3"></i> Remove</button></form>@else<form method="POST" action="{{ route('admin.feedback.update',[$webinar,$item->id]) }}">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">@foreach(['new','reviewed','resolved'] as $status)<option value="{{ $status }}" @selected($item->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select></form>@endif</footer></div></article>
@empty
    <div class="panel-card report-empty"><i class="bi bi-inbox"></i><strong>No {{ $type }} yet</strong><span>Audience responses will appear here automatically.</span></div>
@endforelse
</div>
@endsection
