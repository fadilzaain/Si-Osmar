// Toolbar Bezetting SDM: search, filter status, sort default by severity,
// dan bulk expand/collapse. Semua client-side — data udah lengkap di DOM
// dari Blade, gak perlu fetch tambahan.
export function initSdmBezetting() {
    const page = document.querySelector('[data-sdm-bezetting]');
    if (!page) return;

    const list = page.querySelector('[data-accordion]');
    if (!list) return;

    const units = Array.from(list.querySelectorAll('.bzs-unit'));
    const searchInput = page.querySelector('#bzsSearch');
    const pills = page.querySelectorAll('[data-filter]');
    const emptyState = page.querySelector('[data-empty-filter]');

    // Urutkan default: unit paling kritis (kekurangan terbesar) di paling atas.
    units
        .slice()
        .sort((a, b) => Number(b.dataset.severity) - Number(a.dataset.severity))
        .forEach((unit) => list.appendChild(unit));

    // Isi counter pill Sesuai & Lebih dari DOM (Semua & Kurang udah dari server).
    const countByStatus = (status) => units.filter((u) => u.dataset.status === status).length;
    const countSesuaiEl = page.querySelector('[data-count-for="SESUAI"]');
    const countLebihEl = page.querySelector('[data-count-for="LEBIH"]');
    if (countSesuaiEl) countSesuaiEl.textContent = countByStatus('SESUAI');
    if (countLebihEl) countLebihEl.textContent = countByStatus('LEBIH');

    let activeFilter = 'semua';
    let query = '';

    function applyFilters() {
        let visibleCount = 0;
        units.forEach((unit) => {
            const matchStatus = activeFilter === 'semua' || unit.dataset.status === activeFilter;
            const matchSearch = !query || unit.dataset.search.includes(query);
            const show = matchStatus && matchSearch;
            unit.hidden = !show;
            if (show) visibleCount += 1;
        });
        if (emptyState) emptyState.hidden = visibleCount !== 0;
    }

    searchInput?.addEventListener('input', (e) => {
        query = e.target.value.trim().toLowerCase();
        applyFilters();
    });

    pills.forEach((pill) => {
        pill.addEventListener('click', () => {
            pills.forEach((p) => p.classList.remove('active'));
            pill.classList.add('active');
            activeFilter = pill.dataset.filter;
            applyFilters();
        });
    });

    page.querySelectorAll('[data-bulk]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const shouldOpen = btn.dataset.bulk === 'expand';
            units.forEach((unit) => {
                if (unit.hidden) return; // cuma yang lagi kelihatan sesuai filter aktif
                unit.classList.toggle('open', shouldOpen);
            });
        });
    });

    // Lompat & buka card detail unit di bagian drill-down bawah. Dipicu dari
    // dua tempat: tombol [data-scroll-to] biasa, dan klik bar di chart
    // "Unit paling kritis" (lewat custom event dari dashboard-charts.js).
    function openAndScrollToUnit(slug) {
        const target = document.getElementById('unit-' + slug);
        if (!target) return;

        target.hidden = false;
        target.classList.add('open');
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    page.querySelectorAll('[data-scroll-to]').forEach((btn) => {
        btn.addEventListener('click', () => openAndScrollToUnit(btn.dataset.scrollTo));
    });

    page.querySelector('[data-unit-kritis-chart]')?.addEventListener('chart:point-click', (e) => {
        openAndScrollToUnit(e.detail.id);
    });
}