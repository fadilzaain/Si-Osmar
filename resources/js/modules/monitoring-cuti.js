// Halaman Monitoring Cuti: toolbar (search + filter status + bulk
// expand/collapse) yang bekerja di level PEGAWAI (bukan cuma level unit,
// beda sama Bezetting SDM) — karena yang mau dipantau direktur itu individu
// pegawainya, unit cuma pengelompokan. Semua data udah lengkap di DOM dari
// Blade, jadi murni client-side, gak ada fetch tambahan.
//
// Ditambah: modal rincian cuti per pegawai, diisi dari data-detail yang
// sudah di-embed di setiap kartu pegawai (JSON dari service), biar klik
// kartu langsung nampilin rincian tanpa request baru ke server.

const STATUS_LABEL = {
    KRITIS: 'Kritis — jatah cuti tahunan habis',
    PERHATIAN: 'Perlu perhatian — pemakaian sudah tinggi',
    NORMAL: 'Normal',
};

function renderRincianRow(r) {
    const tone = r.jatah_cuti > 0 && r.sisa_cuti <= 0 ? 'mct-col-danger' : '';
    return `
        <tr>
            <td>${r.jenis_cuti}</td>
            <td class="mct-col-num">${r.jatah_cuti}</td>
            <td class="mct-col-num">${r.cuti_diambil}</td>
            <td class="mct-col-num ${tone}">${r.sisa_cuti}</td>
            <td class="mct-col-num">${r.jatah_cuti > 0 ? r.persen_terpakai + '%' : '—'}</td>
        </tr>
    `;
}

function renderModalBody(pegawai) {
    const rows = (pegawai.rincian || []).map(renderRincianRow).join('');

    return `
        <div class="mct-modal-header">
            <div class="mct-avatar">${pegawai.inisial}</div>
            <div>
                <div class="mct-modal-name">${pegawai.nama}</div>
                <div class="mct-modal-meta">Tahun ${pegawai.tahun} · ${STATUS_LABEL[pegawai.status] || pegawai.status}</div>
            </div>
        </div>
        <div class="mct-table-scroll">
            <table class="mct-modal-table">
                <thead>
                    <tr>
                        <th>Jenis Cuti</th>
                        <th class="mct-col-num">Jatah</th>
                        <th class="mct-col-num">Diambil</th>
                        <th class="mct-col-num">Sisa</th>
                        <th class="mct-col-num">Terpakai</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function initDetailModal(page) {
    const modalEl = document.getElementById('mctDetailModal');
    const body = page.querySelector('[data-mct-modal-body]');
    if (!modalEl || !body || !window.bootstrap) return;

    const modal = new window.bootstrap.Modal(modalEl);

    page.querySelectorAll('[data-mct-pegawai]').forEach((card) => {
        card.addEventListener('click', () => {
            const pegawai = JSON.parse(card.dataset.detail);
            body.innerHTML = renderModalBody(pegawai);
            modal.show();
        });
    });
}

function initToolbar(page) {
    const unitList = page.querySelector('[data-accordion]');
    if (!unitList) return;

    const units = Array.from(unitList.querySelectorAll('.mct-unit'));
    const searchInput = page.querySelector('#mctSearch');
    const pills = page.querySelectorAll('[data-filter]');
    const emptyState = page.querySelector('[data-empty-filter]');

    let activeFilter = 'semua';
    let query = '';

    function applyFilters() {
        let visiblePegawaiTotal = 0;

        units.forEach((unit) => {
            const cards = Array.from(unit.querySelectorAll('[data-mct-pegawai]'));
            const unitNameMatches = !query || unit.dataset.search.includes(query);

            let visibleInUnit = 0;
            cards.forEach((card) => {
                const matchStatus = activeFilter === 'semua' || card.dataset.status === activeFilter;
                const matchSearch = !query || unitNameMatches || card.dataset.search.includes(query);
                const show = matchStatus && matchSearch;
                card.hidden = !show;
                if (show) visibleInUnit += 1;
            });

            const showUnit = visibleInUnit > 0;
            unit.hidden = !showUnit;
            if (showUnit) visiblePegawaiTotal += visibleInUnit;

            // Auto-buka unit yang lagi dicari biar hasil langsung kelihatan,
            // tapi jangan paksa nutup unit yang manual dibuka user pas gak lagi nyari.
            if (query && showUnit) unit.classList.add('open');
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

export function initMonitoringCuti() {
    const page = document.querySelector('[data-monitoring-cuti]');
    if (!page) return;

    initToolbar(page);
    initDetailModal(page);
}
