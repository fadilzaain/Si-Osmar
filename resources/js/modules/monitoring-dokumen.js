// Halaman Monitoring Dokumen: data sudah lengkap di-render server-side
// (gak ada lagi lazy-load AJAX per unit). JS di sini ngurusin interaksi
// client-side: accordion (lewat modul accordion.js generik + animasi
// stagger baris pas dibuka), search, filter pill, bulk expand/collapse,
// dan deep-link dari card dashboard (?ruangan=...).
const ROW_STAGGER_MS = 30;
const ROW_STAGGER_MAX_MS = 300;

// Kasih animation-delay bertahap ke tiap baris tabel di dalam unit yang
// baru dibuka, lalu retrigger animasi CSS-nya lewat reflow trick
// (remove class -> force reflow -> add class lagi), biar animasinya
// tetap muter ulang setiap kali unit dibuka, bukan cuma sekali doang.
function revealRows(unit) {
    const bodyInner = unit.querySelector('.mds-body-inner');
    if (!bodyInner) return;

    const rows = bodyInner.querySelectorAll('.mds-table tbody tr');
    rows.forEach((row, i) => {
        row.style.animationDelay = `${Math.min(i * ROW_STAGGER_MS, ROW_STAGGER_MAX_MS)}ms`;
    });

    bodyInner.classList.remove('mds-reveal');
    void bodyInner.offsetWidth; // force reflow biar animasi bisa direplay
    bodyInner.classList.add('mds-reveal');
}

export function initMonitoringDokumen() {
    const page = document.querySelector('[data-monitoring-dokumen]');
    if (!page) return;

    const list = page.querySelector('[data-accordion]');
    if (!list) return;

    const units = Array.from(list.querySelectorAll('.mds-unit'));
    const searchInput = page.querySelector('#mdsSearch');
    const pills = page.querySelectorAll('[data-filter]');
    const emptyState = page.querySelector('[data-empty-filter]');

    // Setiap unit bisa punya lebih dari satu tag status (mis. ada pegawai
    // danger DAN warning sekaligus), jadi data-status isinya beberapa
    // kata dipisah spasi — hitung & filter berdasarkan "includes".
    const hasStatus = (unit, status) => unit.dataset.status.split(' ').includes(status);

    pills.forEach((pill) => {
        const counter = page.querySelector(`[data-count-for="${pill.dataset.filter}"]`);
        if (counter) {
            counter.textContent = units.filter((u) => hasStatus(u, pill.dataset.filter)).length;
        }
    });

    let activeFilter = 'semua';
    let query = '';

    function applyFilters() {
        let visibleCount = 0;
        units.forEach((unit) => {
            const matchStatus = activeFilter === 'semua' || hasStatus(unit, activeFilter);
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

    // Animasi stagger tiap kali unit dibuka lewat klik header (bukan pas
    // ditutup — cuma relevan pas "masuk" ke detail).
    list.addEventListener('accordion:toggle', (e) => {
        if (e.detail?.open) {
            revealRows(e.target);
        }
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

    page.querySelectorAll('[data-scroll-to]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.getElementById('unit-' + btn.dataset.scrollTo);
            if (!target) return;

            target.hidden = false;
            target.classList.add('open');
            revealRows(target);
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    // Deep-link dari card dashboard: ?ruangan=<nama unit> -> buka & scroll
    // ke unit itu otomatis.
    if (window.__mdsRuanganAktifSlug) {
        const target = document.getElementById('unit-' + window.__mdsRuanganAktifSlug);
        if (target) {
            target.hidden = false;
            target.classList.add('open');
            revealRows(target);
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}