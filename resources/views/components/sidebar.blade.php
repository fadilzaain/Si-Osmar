<aside class="app-sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="sidebar-brand-logo">
        <span>
            SI-OSMAR
            <small>RSUD Jombang</small>
        </span>
    </div>

    <div class="sidebar-utility">
        <div class="sidebar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari...">
        </div>
        <div class="sidebar-utility-icons">
            <button class="sidebar-icon-btn has-tooltip" type="button" aria-label="Notifikasi" data-tooltip="Notifikasi terbaru">
                <i class="fa-regular fa-bell"></i>
            </button>
            <button class="sidebar-icon-btn has-tooltip" data-theme-toggle type="button" aria-label="Ganti tema" data-tooltip="Ganti tema terang/gelap">
                <i class="fa-solid fa-circle-half-stroke"></i>
            </button>
        </div>
    </div>

   <nav class="sidebar-nav">

        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Utama</div>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-label="Dashboard">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Urutan & label di grup ini sengaja disamain persis sama 5 card di dashboard --}}
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Menu Dashboard</div>
            <a href="{{ route('monitoring-str-sip.index') }}" class="sidebar-link {{ request()->routeIs('monitoring-str-sip.*') ? 'active' : '' }}" data-label="Monitoring Dokumen">
                <i class="fa-solid fa-file-shield"></i>
                <span>Monitoring Dokumen</span>
            </a>
            <a href="{{ route('coming-soon', 'ekinerja') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'ekinerja' ? 'active' : '' }}" data-label="Capaian Kinerja">
                <i class="fa-solid fa-chart-line"></i>
                <span>Capaian Kinerja</span>
                <span class="dxg-badge-pill tone-neutral sidebar-link-badge">Segera</span>
            </a>
            <a href="{{ route('sdm-bezetting.index') }}" class="sidebar-link {{ request()->routeIs('sdm-bezetting.*') ? 'active' : '' }}" data-label="SDM">
                <i class="fa-solid fa-users"></i>
                <span>SDM</span>
            </a>
            <a href="{{ route('monitoring-cuti.index') }}" class="sidebar-link {{ request()->routeIs('monitoring-cuti.*') ? 'active' : '' }}" data-label="Cuti">
                <i class="fa-solid fa-umbrella-beach"></i>
                <span>Cuti</span>
            </a>
            <a href="{{ route('coming-soon', 'pelatihan') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'pelatihan' ? 'active' : '' }}" data-label="Pelatihan">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Pelatihan</span>
                <span class="dxg-badge-pill tone-neutral sidebar-link-badge">Segera</span>
            </a>
        </div>

        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label"></div>
            <!-- <a href="{{ route('coming-soon', 'laporan-evaluasi') }}" class="sidebar-link {{ request()->routeIs('coming-soon') && request()->route('module') === 'laporan-evaluasi' ? 'active' : '' }}" data-label="Laporan & Evaluasi">
                <i class="fa-solid fa-file-lines"></i>
                <span>Laporan & Evaluasi</span>
            </a> -->
            <!-- <a href="#" class="sidebar-link" data-label="Pengaturan">
                <i class="fa-solid fa-gear"></i>
                <span>Pengaturan</span>
            </a> -->
        </div>

    </nav>

    <button class="sidebar-toggle has-tooltip" type="button" data-sidebar-toggle aria-label="Ciutkan sidebar" data-tooltip="Ciutkan / perluas sidebar">
        <i class="fa-solid fa-angles-left"></i>
    </button>

    <div class="sidebar-profile-wrapper">
        <button class="sidebar-profile" type="button" data-action="toggle-profile-menu" aria-haspopup="true" aria-expanded="false">
            <div class="sidebar-profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="sidebar-profile-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="sidebar-profile-role">{{ auth()->user()->email ?? 'Super Admin' }}</div>
            </div>
            <i class="fa-solid fa-chevron-up sidebar-profile-caret"></i>
        </button>

        <div class="sidebar-profile-menu" data-profile-menu>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-profile-menu-item sidebar-profile-menu-danger">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="sidebar-backdrop" data-action="close-sidebar"></div>