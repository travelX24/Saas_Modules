@props([
  'items' => [],
])

<div class="nav-links">
    @foreach($items as $item)
        <div class="nav-item">
            @if(!empty($item['disabled']))
                <div class="nav-link" style="opacity:.55; cursor:not-allowed;">
                    <div class="nav-icon">{!! $item['icon'] !!}</div>
                    <span class="nav-text">{{ $item['label'] }}</span>
                </div>
            @else
                <a href="{{ $item['href'] }}"
                   class="nav-link {{ $item['active'] ? 'active' : '' }}">
                    <div class="nav-icon">{!! $item['icon'] !!}</div>
                    <span class="nav-text">{{ $item['label'] }}</span>
                </a>
            @endif
        </div>
    @endforeach
</div>
