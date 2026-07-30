// Halaman Bezetting SDM: toolbar (search, filter status, bulk expand/
// collapse) + auto-refresh data tiap 1 menit biar user gak perlu manual
// reload buat lihat data terbaru dari SIKAWAN 
import { initAccordion } from './accordion';
import { initDashboardCharts } from './dashboard-charts';
import { initCountUp } from './count-up';

const DATA_URL = '/sdm-bezetting/data';
const REFRESH_INTERVAL_MS = 60_000; 

let pollTimer = null;
let isFetching = false;

// Toolbar: search, filter status, sort default by severity, bulk expand/
// collapse. Dipisah dari initSdmBezetting() supaya bisa dipanggil ulang
// tiap abis auto-refresh (DOM-nya diganti, listener lama otomatis ilang
// bareng elemen lama, jadi harus di-bind ulang ke elemen yang baru).
function initToolbar(page) {
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

// Ambil HTML terbaru dari server, lalu ganti isi halaman dengan hasilnya.
// Endpoint /sdm-bezetting/data sengaja mengembalikan HTML yang sudah siap
// ditampilkan, jadi gk perlu merender ulang tabel, chart, atau
// accordion di JavaScript. Tinggal replace isi DOM, lalu panggil kembali
// fungsi inisialisasi yang sudah ada.
async function refreshLiveData() {
    if (isFetching || document.visibilityState === 'hidden') return;

    const currentPage = document.querySelector('[data-sdm-bezetting]');
    if (!currentPage) return;

    isFetching = true;
    try {
        const response = await fetch(DATA_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const html = await response.text();
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const freshPage = template.content.firstElementChild;
        if (!freshPage) return;

        currentPage.replaceWith(freshPage);

        // Re-bind semua behavior yang datanya baru diganti. Manggil ulang
        // init yang udah ada (bukan nulis ulang) biar gak ada 2 sumber kebenaran
        initToolbar(freshPage);
        initAccordion();
        initDashboardCharts();
        initCountUp();
    } catch (err) {
        console.error('Auto-refresh Bezetting SDM gagal, coba lagi menit berikutnya:', err);
    } finally {
        isFetching = false;
    }
}

function startLivePolling() {
    if (pollTimer) return; // udah jalan (re-init ganda), jangan dobel timer
    pollTimer = setInterval(refreshLiveData, REFRESH_INTERVAL_MS);

    // otomatis langsung refresh sekali biar user gak perlu nunggu sampai interval berikutnya.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshLiveData();
    });
}

export function initSdmBezetting() {
    const page = document.querySelector('[data-sdm-bezetting]');
    if (!page) return;

    initToolbar(page);
    startLivePolling();
}