// Halaman Monitoring Pelatihan
// Semua data udah lengkap di DOM dari Blade, jadi murni client-side, gak ada fetch tambahan.

function initToolbar(page) {
    const unitList = page.querySelector('[data-accordion]');
    if (!unitList) return;

    const units = Array.from(unitList.querySelectorAll('.plh-unit'));
    const searchInput = page.querySelector('#plhSearch');
    const emptyState = page.querySelector('[data-empty-filter]');

    let query = '';

    function applyFilter() {
        let visibleTotal = 0;

        units.forEach((unit) => {
            const cards = Array.from(unit.querySelectorAll('[data-plh-pegawai]'));
            const unitNameMatches = !query || unit.dataset.search.includes(query);

            let visibleInUnit = 0;
            cards.forEach((card) => {
                const show = !query || unitNameMatches || card.dataset.search.includes(query);
                card.hidden = !show;
                if (show) visibleInUnit += 1;
            });

            const showUnit = visibleInUnit > 0;
            unit.hidden = !showUnit;
            if (showUnit) visibleTotal += visibleInUnit;

            // Auto buka unit yang lagi dicari biar hasil langsung kelihatan,
            // jangan paksa nutup unit yang manual dibuka user pas gak lagi nyari.
            if (query && showUnit) unit.classList.add('open');
        });

        if (emptyState) emptyState.hidden = visibleTotal !== 0;
    }

    searchInput?.addEventListener('input', (e) => {
        query = e.target.value.trim().toLowerCase();
        applyFilter();
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
}

export function initPelatihan() {
    const page = document.querySelector('[data-monitoring-pelatihan]');
    if (!page) return;

    initToolbar(page);
}