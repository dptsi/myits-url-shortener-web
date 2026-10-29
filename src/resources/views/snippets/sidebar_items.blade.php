@foreach ($menu as $item)
<div class="sidebar-item @if ($item['active']) active @endif">
    <a href="{{ $item['url'] }}" class="sidebar-link">
        <span class="sidebar-item-icon ms-rounded">{{ $item['icon'] }}</span>
        <span class="sidebar-item-label tx-figtree tx-medium text-truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
    </a>
</div>
@endforeach
