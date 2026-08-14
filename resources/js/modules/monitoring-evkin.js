// Halaman Capaian Kinerja
const ROW_STAGGER_MS = 30;
const ROW_STAGGER_MAX_MS = 300;

function revealRows(unit) {
    const bodyInner = unit.querySelector('.mek-body-inner');
    if (!bodyInner) return;

    const rows = bodyInner.querySelectorAll('.mek-table tbody tr:not([hidden])');
    rows.forEach((row, i) => {
        row.style.animationDelay = `${Math.min(i * ROW_STAGGER_MS, ROW_STAGGER_MAX_MS)}ms`;
    });

    // Reflow trick biar animasi CSS-nya keputer ulang tiap kali unit dibuka,
    // bukan cuma sekali pas render pertama.
    bodyInner.classList.remove('mek-reveal');
    void bodyInner.offsetWidth;
    bodyInner.classList.add('mek-reveal');
}


function syncUnitHeader(unit, totalPegawai, visibleCount, isFiltered) {
    const totalEl = unit.querySelector('[data-mek-total]');
    if (totalEl) {
        totalEl.textContent = isFiltered
            ? `${visibleCount} dari ${totalPegawai} pegawai`
            : `${totalPegawai} pegawai`;
    }

    const baikBadge = unit.querySelector('[data-mek-baik-badge]');
    if (baikBadge) baikBadge.hidden = isFiltered;

    const belumBadge = unit.querySelector('[data-mek-belum-badge]');
    if (belumBadge) belumBadge.hidden = isFiltered;
}

function initToolbar(page) {
    const unitList = page.querySelector('[data-accordion]');
    if (!unitList) return;

    const units = Array.from(unitList.querySelectorAll('.mek-unit'));
    const searchInput = page.querySelector('#mekSearch');
    const pills = page.querySelectorAll('[data-filter]');
    const emptyState = page.querySelector('[data-empty-filter]');

    let activeFilter = 'semua';
    let query = '';

    function applyFilters() {
        let visiblePegawaiTotal = 0;
        const isFiltered = activeFilter !== 'semua' || !!query;

        units.forEach((unit) => {
            const rows = Array.from(unit.querySelectorAll('[data-mek-pegawai]'));
            const unitNameMatches = !query || unit.dataset.search.includes(query);

            let visibleInUnit = 0;
            rows.forEach((row) => {
                const matchPredikat = activeFilter === 'semua' || row.dataset.predikat === activeFilter;
                const matchSearch = !query || unitNameMatches || row.dataset.search.includes(query);
                const show = matchPredikat && matchSearch;
                row.hidden = !show;
                if (show) visibleInUnit += 1;
            });

            syncUnitHeader(unit, rows.length, visibleInUnit, isFiltered);

            const showUnit = visibleInUnit > 0;
            unit.hidden = !showUnit;
            if (showUnit) visiblePegawaiTotal += visibleInUnit;

            // Auto-buka unit yang lagi dicari biar hasil langsung kelihatan
            if (query && showUnit && !unit.classList.contains('open')) {
                unit.classList.add('open');
                revealRows(unit);
            }
        });

        if (emptyState) emptyState.hidden = visiblePegawaiTotal !== 0;
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

    // Animasi stagger tiap kali unit dibuka lewat klik header.
    unitList.addEventListener('accordion:toggle', (e) => {
        if (e.detail?.open) revealRows(e.target);
    });

    page.querySelectorAll('[data-bulk]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const shouldOpen = btn.dataset.bulk === 'expand';
            units.forEach((unit) => {
                if (unit.hidden) return; // cuma yang lagi kelihatan sesuai filter aktif
                const wasOpen = unit.classList.contains('open');
                unit.classList.toggle('open', shouldOpen);
                if (shouldOpen && !wasOpen) revealRows(unit);
            });
        });
    });
}

export function initMonitoringEvkin() {
    const page = document.querySelector('[data-monitoring-evkin]');
    if (!page) return;

    initToolbar(page);
}