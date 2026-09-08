@extends(auth()->check()?'layouts.portal':'layouts.public')
@section('title','Discover Webinars')
@section('content')
@guest<div class="container py-5">@endguest
<div class="page-heading"><div><span class="eyebrow">EXPLORE & LEARN</span><h1>Discover webinars</h1><p>Published webinars loaded from the database.</p></div></div>
<div class="row g-4">@forelse($webinars as $webinar)<div class="col-md-6 col-xl-4"><a href="{{ route('webinars.show',$webinar) }}" class="card-link"><x-webinar-card :title="$webinar->title" :speaker="$webinar->creator?->name ?? 'Webinar host'" :date="$webinar->starts_at?->timezone($webinar->timezone)->format('M d, Y · g:i A') ?? 'Schedule pending'" :live="$webinar->status==='live'" /></a></div>@empty<div class="col-12"><div class="panel-card text-center py-5 text-muted">No published webinars are available.</div></div>@endforelse</div>
<div class="mt-4">{{ $webinars->links() }}</div>
@guest</div>@endguest
@endsection
