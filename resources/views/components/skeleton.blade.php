@props(['type' => 'text', 'width' => '100%', 'count' => 1])

@for ($i = 0; $i < $count; $i++)
    <div class="skeleton skeleton-{{ $type }}" style="width: {{ $width }}"></div>
@endfor