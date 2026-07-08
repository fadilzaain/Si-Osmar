@props(['menu' => null])

@php
    $menu = $menu ?? [
        ['label' => 'Dashboard', 'icon' => 'fa-solid fa-gauge-high', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Kompetensi', 'icon' => 'fa-solid fa-graduation-cap', 'route' => null, 'active' => false],
        ['label' => 'Pelatihan', 'icon' => 'fa-solid fa-chalkboard-user', 'route' => null, 'active' => false],
        ['label' => 'Monitoring', 'icon' => 'fa-solid fa-chart-line', 'route' => null, 'active' => false],
        ['label' => 'Mutasi', 'icon' => 'fa-solid fa-right-left', 'route' => null, 'active' => false],
        ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-lines', 'route' => null, 'active' => false],
        ['label' => 'Pengaturan', 'icon' => 'fa-solid fa-gear', 'route' => null, 'active' => false],
    ];
@endphp

<aside class="app-sidebar">
    <div class="sidebar-brand">
    <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="sidebar-brand-logo">
    <span>
        SI-OSMAR
        <small>RSUD Jombang</small>
    </span>
</div>

    <nav class="sidebar-nav">
        @foreach ($menu as $item)
            <a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="sidebar-link {{ $item['active'] ? 'active' : '' }}" data-label="{{ $item['label'] }}">
                <i class="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="sidebar-profile">
        <div class="sidebar-profile-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
        <div>
            <div class="sidebar-profile-name">{{ auth()->user()->name ?? 'Admin SDM' }}</div>
            <div class="sidebar-profile-role">Super Admin</div>
        </div>
    </div>

    <button class="sidebar-toggle" data-sidebar-toggle type="button" aria-label="Toggle sidebar">
        <i class="fa-solid fa-angles-left"></i>
    </button>
</aside>