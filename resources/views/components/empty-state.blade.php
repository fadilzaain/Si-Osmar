@props(['icon' => 'fa-solid fa-inbox', 'title' => 'Belum ada data', 'description' => null])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon"><i class="{{ $icon }}"></i></div>
    <div class="empty-state-title">{{ $title }}</div>
    @if ($description)
        <div class="empty-state-desc">{{ $description }}</div>
    @endif
    @isset($slot)
        {{ $slot }}
    @endisset
</div>