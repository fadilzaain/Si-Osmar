@props(['title' => 'Dashboard'])

<header class="app-navbar">
    <div class="navbar-left">
        <button class="navbar-icon-btn d-flex" data-sidebar-toggle type="button" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="navbar-search d-none d-md-flex">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari...">
        </div>
    </div>

    <div class="navbar-right">
        <button class="navbar-icon-btn" type="button" aria-label="Notifikasi">
            <i class="fa-regular fa-bell"></i>
        </button>

        <button class="navbar-icon-btn" data-theme-toggle type="button" aria-label="Ganti tema">
            <i class="fa-solid fa-circle-half-stroke"></i>
        </button>

        <button class="navbar-profile" type="button">
            <span class="navbar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
            <span class="d-none d-md-inline text-muted">{{ auth()->user()->name ?? 'Admin' }}</span>
        </button>
    </div>
</header>