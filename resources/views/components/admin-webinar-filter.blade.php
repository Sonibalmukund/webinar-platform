@props(['webinars', 'selected' => null, 'search' => null, 'placeholder' => 'Search listing...', 'showSearch' => true])
@php($currentSearch = $search ?? request('search', ''))
@php($currentSelected = $selected ?? request('webinar_id'))
<form class="filter-bar module-filter-bar mb-4" method="GET" action="{{ url()->current() }}">
    @if($showSearch)
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input name="search" value="{{ $currentSearch }}" placeholder="{{ $placeholder }}" aria-label="Search listing">
        </div>
    @endif
    @if(isset($webinars))
        <select class="form-select" name="webinar_id" onchange="this.form.submit()" aria-label="Filter by webinar">
            <option value="">All webinars</option>
            @foreach($webinars as $event)
                <option value="{{ $event->id }}" @selected((int)$currentSelected === $event->id)>{{ $event->title }}</option>
            @endforeach
        </select>
    @endif
    <button class="btn btn-light" type="submit"><i class="bi bi-funnel"></i> Filter</button>
    @if(request()->hasAny(['search', 'webinar_id', 'status']) || $currentSelected || $currentSearch)
        <a class="btn btn-outline-secondary" href="{{ url()->current() }}">Reset</a>
    @endif
</form>
