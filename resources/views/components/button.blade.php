@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'btn btn-' . $variant . ($size !== 'md' ? ' btn-' . $size : '') . ($loading ? ' is-loading' : '')]) }}
    @if ($loading) disabled @endif
>
    @if ($loading)
        <span class="btn-spinner"></span>
    @elseif ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>