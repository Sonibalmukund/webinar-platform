@props(['isAdmin'])
@php
    $items = App\Support\SidebarNavigation::forPortal($isAdmin);
    $section = App\Support\SidebarNavigation::sectionLabel($isAdmin);
@endphp
<aside class="sidebar" id="sidebar">
    <button class="icon-btn sidebar-close d-lg-none" id="sidebarClose" type="button" aria-label="Close navigation"><i class="bi bi-x-lg"></i></button>
    <x-site-brand light class="sidebar-uploaded-brand" />
    <div class="sidebar-label">{{ $section }}</div>
    <nav class="sidebar-nav">
        @php($currentGroup = null)
        @foreach($items as $item)
            @if(($item['section'] ?? null) !== $currentGroup)
                @php($currentGroup = $item['section'] ?? null)
                @if($currentGroup)<div class="sidebar-section-title">{{ $currentGroup }}</div>@endif
            @endif
            @if(($item['type'] ?? '') === 'group')
                <button class="sidebar-group-toggle {{ ($item['active'] ?? false) ? 'open' : '' }}" type="button" data-sidebar-group-toggle="{{ $item['id'] }}"><span><i class="bi bi-{{ $item['icon'] }}"></i> {{ $item['label'] ?? $item['title'] }}</span><i class="bi bi-chevron-down"></i></button>
                <div class="sidebar-submenu {{ ($item['active'] ?? false) ? 'open' : '' }}" id="{{ $item['id'] }}">@foreach($item['children'] as $child)<a class="{{ ($child['active'] ?? false) ? 'active' : '' }}" href="{{ $child['url'] ?? ($child['route'] ?? '#') }}">{{ $child['label'] ?? $child['title'] }}</a>@endforeach</div>
            @else
                @php($label = $item['label'] ?? ($item['title'] ?? ''))
                @php($url = $item['url'] ?? ($item['route'] ?? '#'))
                <a class="{{ ($item['active'] ?? false) ? 'active' : '' }}" href="{{ $url }}">@if(!empty($item['icon']))<i class="bi bi-{{ $item['icon'] }}"></i>@endif {{ $label }}</a>
            @endif
        @endforeach
    </nav>
</aside>
