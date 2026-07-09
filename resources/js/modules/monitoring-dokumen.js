const statusColor = {
    berlaku: 'var(--color-success)',
    akan_kadaluarsa: 'var(--color-warning)',
    kadaluarsa: 'var(--color-danger)',
    tidak_ada: 'var(--color-text-muted)',
};

const statusLabel = {
    berlaku: 'Berlaku',
    akan_kadaluarsa: 'Segera kadaluarsa',
    kadaluarsa: 'Kadaluarsa',
    tidak_ada: 'Tidak ada data',
};

function renderRow(pegawai) {
    const runways = Object.entries(pegawai.dokumen).map(([jenis, d]) => `
        <div class="mds-runway">
            <span class="mds-runway-label">${jenis}</span>
            <div class="mds-runway-track">
                <div class="mds-runway-fill" data-w="${d.status === 'tidak_ada' ? 0 : 100}"
                     style="background:${statusColor[d.status]}"></div>
            </div>
            <span class="mds-runway-status">${statusLabel[d.status]}</span>
        </div>
    `).join('');

    return `
        <div class="mds-row">
            <div class="mds-person">
                <div class="mds-avatar">${pegawai.nama.split(' ').map(w => w[0]).slice(0, 2).join('')}</div>
                <div>
                    <div class="mds-pname">${pegawai.nama}</div>
                    <div class="mds-prole">${pegawai.jabatan}</div>
                </div>
            </div>
            <div class="mds-runways">${runways}</div>
        </div>
    `;
}

async function loadDetail(unitEl) {
    const ruangan = unitEl.dataset.ruangan;
    const inner = unitEl.querySelector('.mds-rows-inner');
    if (inner.dataset.loaded) return;

    const res = await fetch(`/monitoring-str-sip/detail/${encodeURIComponent(ruangan)}`);
    const pegawaiList = await res.json();

    inner.innerHTML = pegawaiList.map(renderRow).join('');
    inner.dataset.loaded = '1';

    requestAnimationFrame(() => {
        inner.querySelectorAll('.mds-runway-fill').forEach(el => {
            el.style.width = el.dataset.w + '%';
        });
    });
}

export function initMonitoringDokumen() {
    document.querySelectorAll('.mds-unit').forEach(unit => {
        unit.querySelector('.mds-unit-head').addEventListener('click', () => {
            const isOpen = unit.classList.toggle('open');
            if (isOpen) loadDetail(unit);
        });
    });

    if (window.__mdsRuanganAktif) {
        const target = document.querySelector(`.mds-unit[data-ruangan="${window.__mdsRuanganAktif}"]`);
        if (target) {
            target.classList.add('open');
            loadDetail(target);
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}