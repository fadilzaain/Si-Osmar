<div {{ $attributes->merge(['class' => 'card-base filter-panel']) }}>
    {{ $slot }}

    @isset($actions)
        <div class="filter-actions">{{ $actions }}</div>
    @endisset
</div>