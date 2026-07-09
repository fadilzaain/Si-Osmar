<aside class="app-sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="sidebar-brand-logo">
        <span>
            SI-OSMAR
            <small>RSUD Jombang</small>
        </span>
    </div>

    <nav class="sidebar-nav">

        {{-- Grup: Utama --}}
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Utama</div>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-label="Dashboard">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Grup: Kompetensi & Karier --}}
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Kompetensi & Karier</div>
            <a href="{{ route('coming-soon', 'profil-sdm') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'profil-sdm' ? 'active' : '' }}" data-label="Profil SDM">
                <i class="fa-solid fa-id-card-clip"></i>
                <span>Profil SDM</span>
            </a>
            <a href="{{ route('coming-soon', 'pemetaan-kompetensi') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'pemetaan-kompetensi' ? 'active' : '' }}" data-label="Pemetaan Kompetensi">
                <i class="fa-solid fa-award"></i>
                <span>Pemetaan Kompetensi</span>
            </a>
            <a href="{{ route('monitoring-str-sip.index') }}" class="sidebar-link {{ request()->routeIs('monitoring-str-sip.*') ? 'active' : '' }}" data-label="Monitoring STR & SIP">
                <i class="fa-solid fa-file-shield"></i>
                <span>Monitoring STR & SIP</span>
            </a>
        </div>

        {{-- Grup: Distribusi & Beban Kerja --}}
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Distribusi & Beban Kerja</div>
            <a href="{{ route('coming-soon', 'distribusi-sdm') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'distribusi-sdm' ? 'active' : '' }}" data-label="Distribusi SDM">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Distribusi SDM</span>
            </a>
            <a href="{{ route('coming-soon', 'analisis-beban-kerja') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'analisis-beban-kerja' ? 'active' : '' }}" data-label="Analisis Beban Kerja">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>Analisis Beban Kerja</span>
            </a>
        </div>

        {{-- Grup: Lainnya --}}
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Lainnya</div>
            <a href="{{ route('coming-soon', 'laporan-evaluasi') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'laporan-evaluasi' ? 'active' : '' }}" data-label="Laporan & Evaluasi">
                <i class="fa-solid fa-file-lines"></i>
                <span>Laporan & Evaluasi</span>
            </a>
            <a href="#" class="sidebar-link" data-label="Pengaturan">
                <i class="fa-solid fa-gear"></i>
                <span>Pengaturan</span>
            </a>
        </div>

    </nav>

    <button class="sidebar-toggle" type="button" data-action="toggle-sidebar" aria-label="Ciutkan sidebar">
        <i class="fa-solid fa-angles-left"></i>
    </button>

    <div class="sidebar-profile">
        <div class="sidebar-profile-avatar">A</div>
        <div>
            <div class="sidebar-profile-name">Admin SDM</div>
            <div class="sidebar-profile-role">Super Admin</div>
        </div>
    </div>
</aside>

<div class="sidebar-backdrop" data-action="close-sidebar"></div>