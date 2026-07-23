import ApexCharts from 'apexcharts';

// Ambil warna dari CSS variable aktif (otomatis ikut toggle light/dark)
function token(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

function palette() {
    return {
        primary: token('--color-primary'),
        success: token('--color-success'),
        warning: token('--color-warning'),
        danger: token('--color-danger'),
        info: token('--color-info'),
        mint: token('--color-mint'),
        text: token('--color-text'),
        surface: token('--color-surface'),
        border: token('--color-border'),
    };
}

// Peta nama tone ("success", "warning", dst — dikirim controller lewat
// payload chart) ke warna CSS variable aktif. Dipakai bareng oleh semua
// renderer di bawah biar gak ada 3 salinan objek yang sama tiap ganti tema.
function toneColors(p) {
    return { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger, info: p.info };
}

// Satu-satunya tipe chart yang dipakai di dashboard: radial mini (sparkline, ukuran terkunci, gak pernah overflow)
function renderRadial(el, data, p) {
    const color = toneColors(p)[data.color] || p.success;
    const size = data.size || 96;
    const options = {
        chart: { type: 'radialBar', height: size, sparkline: { enabled: true }, animations: { enabled: true, speed: 600 } },
        series: [data.persen],
        colors: [color],
        plotOptions: {
            radialBar: {
                hollow: { size: '62%' },
                track: { background: p.border },
                dataLabels: {
                    name: { show: false },
                    value: {
                        fontSize: data.size && data.size < 90 ? '13px' : '16px',
                        fontWeight: 600,
                        color: p.text,
                        formatter: (val) => val + '%',
                    },
                },
            },
        },
        stroke: { lineCap: 'round' },
    };
    return new ApexCharts(el, options).render();
}

function renderDonutMulti(el, data, p) {
    const colors = (data.colors || []).map((c) => toneColors(p)[c] || p.success);
    const size = data.size || 128;

    const options = {
        chart: { type: 'donut', height: size, animations: { enabled: true, speed: 700, easing: 'easeinout' } },
        series: data.series,
        colors,
        labels: data.labels || [],
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 3, colors: [p.surface] },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        value: { fontSize: '18px', fontWeight: 600, color: p.text, offsetY: -2 },
                        total: {
                            show: true,
                            label: data.totalLabel || 'Baik+',
                            fontSize: '10px',
                            color: p.text,
                            formatter: () => data.totalValue ?? '',
                        },
                    },
                },
            },
        },
        tooltip: { enabled: false },
    };
    return new ApexCharts(el, options).render();
}

// Bar horizontal — dipakai buat ranking (mis. top pegawai pemakaian cuti
// tertinggi). Satu series, warna tunggal, dataLabel nunjukin nilainya
// langsung di ujung bar biar gak perlu legend terpisah.
function renderBarHorizontal(el, data, p) {
    // Multi-warna per bar (mis. tiap predikat punya warna beda) dipicu kalau
    // controller kirim `colors` (array, satu tone per bar) alih-alih `color`
    // tunggal. Dipakai buat chart "Distribusi Predikat" yang tadinya donut.
    const isDistributed = Array.isArray(data.colors) && data.colors.length > 0;
    const colors = isDistributed
        ? data.colors.map((c) => toneColors(p)[c] || p.primary)
        : [toneColors(p)[data.color] || p.danger];
    const height = data.height || 260;

    // Default persen (dipakai duluan buat "% Terpakai" cuti). Kalau data
    // bukan persen (mis. jumlah orang), controller kirim `suffix` & `max`
    // sendiri lewat payload chart-nya.
    const suffix = data.suffix ?? '%';
    const maxVal = data.max ?? (suffix === '%' ? 100 : undefined);
    const format = (val) => val + suffix;

    // hideAxis: tiap bar di sini udah punya dataLabel sendiri di ujungnya,
    // jadi skala angka + gridline di bawah itu berlebihan — dan gampang
    // numpuk kalau suffix-nya panjang (mis. " orang") di card yang sempit.
    // Kalau di-set, chart-nya juga dikasih gradient + sudut lebih rounded
    // biar tampil lebih premium/modern. Dipakai di chart "Unit paling kritis".
    const hideAxis = data.hideAxis === true;

    // Opsional: kalau ada `ids` (satu per bar, urutan sama kayak `labels`),
    // klik bar dispatch custom event ke elemen chart-nya sendiri. Modul
    // halaman yang butuh (mis. sdm-bezetting.js) tinggal listen event ini —
    // dashboard-charts.js gak perlu tau apa yang terjadi setelah diklik.
    const clickable = Array.isArray(data.ids);
    if (clickable) el.classList.add('is-clickable');

    const xaxis = {
        categories: data.labels || [],
        labels: { show: !hideAxis, style: { colors: p.text, fontSize: '11px' }, formatter: format },
        axisBorder: { show: !hideAxis, color: p.border },
        axisTicks: { show: !hideAxis, color: p.border },
    };
    if (maxVal !== undefined) xaxis.max = maxVal;

    const options = {
        chart: {
            type: 'bar',
            height,
            toolbar: { show: false },
            animations: { enabled: true, speed: 500, easing: 'easeinout' },
            events: clickable
                ? {
                      dataPointSelection: (event, ctx, opts) => {
                          const id = data.ids[opts.dataPointIndex];
                          if (id) el.dispatchEvent(new CustomEvent('chart:point-click', { detail: { id } }));
                      },
                  }
                : {},
        },
        series: [{ name: data.seriesName || 'Nilai', data: data.series || [] }],
        colors,
        legend: { show: false },
        fill: hideAxis
            ? {
                  type: 'gradient',
                  gradient: {
                      type: 'horizontal',
                      shade: 'light',
                      shadeIntensity: 0.35,
                      gradientToColors: [colors[0]],
                      opacityFrom: 0.75,
                      opacityTo: 1,
                      stops: [0, 100],
                  },
              }
            : { type: 'solid' },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: hideAxis ? 8 : 4,
                borderRadiusApplication: 'end',
                barHeight: hideAxis ? '60%' : '55%',
                distributed: isDistributed,
            },
        },
        xaxis,
        yaxis: { labels: { style: { colors: p.text, fontSize: '12px', fontWeight: 500 } } },
        grid: {
            show: !hideAxis,
            borderColor: p.border,
            xaxis: { lines: { show: !hideAxis } },
            yaxis: { lines: { show: false } },
            padding: hideAxis ? { top: -12, bottom: -8, left: 0, right: 16 } : { top: 0, right: 0, bottom: 0, left: 0 },
        },
        dataLabels: {
            enabled: true,
            formatter: format,
            style: { fontSize: '11px', fontWeight: 700, colors: [p.text] },
            offsetX: 24,
            dropShadow: { enabled: false },
        },
        tooltip: { theme: 'dark', y: { formatter: format } },
    };
    return new ApexCharts(el, options).render();
}

const renderers = {
    'donut-single': renderRadial,
    'donut-multi': renderDonutMulti,
    'bar-horizontal': renderBarHorizontal,
};

let activeCharts = [];
let observerAttached = false;

// Sebelumnya destroy chart lama dan render chart baru dilakukan "berbarengan"
// (destroy() dipanggil tapi gak ditunggu selesai sebelum instance baru dibuat
// di elemen yang sama). Kalau renderAll() kepanggil dua kali beruntun dalam
// jarak dekat (mis. tema toggle cepat, atau halaman lama masih hidup gara-gara
// HMR cuma reload CSS bukan reload penuh), ApexCharts bisa nabrak state
// internalnya sendiri di elemen yang sama → muncul error
// "Cannot read properties of undefined (reading 'beforeMount')" dan chart-nya
// gagal mount (kosong, gak ada fallback). Fix-nya: tunggu semua instance lama
// beneran selesai di-destroy dulu, baru render yang baru.
function renderAll() {
    const toDestroy = activeCharts;
    activeCharts = [];

    Promise.allSettled(toDestroy.map((c) => c.then((inst) => inst.destroy()))).then(() => {
        const p = palette();
        document.querySelectorAll('[data-chart-type]').forEach((el) => {
            const renderer = renderers[el.dataset.chartType];
            if (!renderer) return;

            let data;
            try {
                data = JSON.parse(el.dataset.chart);
            } catch (err) {
                console.error('Data chart gak valid buat', el.dataset.chartType, err);
                return;
            }

            el.innerHTML = '';
            const chartPromise = renderer(el, data, p);
            chartPromise.catch((err) => {
                console.error('Gagal render chart', el.dataset.chartType, err);
                el.innerHTML = '<div class="chart-render-error">Grafik gagal dimuat. Coba reload halaman.</div>';
            });
            activeCharts.push(chartPromise);
        });
    });
}

// Panggil dari app.js
export function initDashboardCharts() {
    if (!document.querySelector('[data-chart-type]')) return;

    renderAll();

    if (!observerAttached) {
        const themeObserver = new MutationObserver((mutations) => {
            if (mutations.some((m) => m.attributeName === 'data-theme')) renderAll();
        });
        themeObserver.observe(document.documentElement, { attributes: true });
        observerAttached = true;
    }
}