@props(['label', 'value', 'icon' => 'graph-up', 'tone' => 'purple', 'meta' => null, 'live' => false])
<div {{ $attributes->class(['stat-card dashboard-stat-card','is-live' => $live && (int) $value > 0]) }}>
    <span class="stat-icon tone-{{ $tone }}"><i class="bi bi-{{ $icon }}"></i></span>
    <div class="stat-card-copy"><div class="stat-value-line"><h3 data-stat>{{ $value }}</h3>@if($live)<span class="live-blink-dot {{ (int) $value > 0 ? 'active' : 'inactive' }}"></span>@endif</div><small>{{ $label }}</small>@if($meta)<span class="stat-meta">{{ $meta }}</span>@endif</div>
</div>
