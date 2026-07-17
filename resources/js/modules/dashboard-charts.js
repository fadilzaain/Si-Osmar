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

// Satu-satunya tipe chart yang dipakai di dashboard: radial mini (sparkline, ukuran terkunci, gak pernah overflow)
function renderRadial(el, data, p) {
    const colorMap = { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger };
    const color = colorMap[data.color] || p.success;
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
    const colorMap = { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger, info: p.info };
    const colors = (data.colors || []).map((c) => colorMap[c] || p.success);
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
    const colorMap = { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger, info: p.info };
    const color = colorMap[data.color] || p.danger;
    const height = data.height || 260;

    // Default persen (dipakai duluan buat "% Terpakai" cuti). Kalau data
    // bukan persen (mis. jumlah orang), controller kirim `suffix` & `max`
    // sendiri lewat payload chart-nya.
    const suffix = data.suffix ?? '%';
    const maxVal = data.max ?? (suffix === '%' ? 100 : undefined);
    const format = (val) => val + suffix;

    // Opsional: kalau ada `ids` (satu per bar, urutan sama kayak `labels`),
    // klik bar dispatch custom event ke elemen chart-nya sendiri. Modul
    // halaman yang butuh (mis. sdm-bezetting.js) tinggal listen event ini —
    // dashboard-charts.js gak perlu tau apa yang terjadi setelah diklik.
    const clickable = Array.isArray(data.ids);
    if (clickable) el.classList.add('is-clickable');

    const xaxis = {
        categories: data.labels || [],
        labels: { style: { colors: p.text, fontSize: '11px' }, formatter: format },
        axisBorder: { color: p.border },
        axisTicks: { color: p.border },
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
                : undefined,
        },
        series: [{ name: data.seriesName || 'Nilai', data: data.series || [] }],
        colors: [color],
        plotOptions: {
            bar: { horizontal: true, borderRadius: 4, barHeight: '55%', distributed: false },
        },
        xaxis,
        yaxis: { labels: { style: { colors: p.text, fontSize: '12px' } } },
        grid: { borderColor: p.border, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
        dataLabels: {
            enabled: true,
            formatter: format,
            style: { fontSize: '11px', fontWeight: 600, colors: [p.text] },
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

function renderAll() {
    activeCharts.forEach((c) => c.then((inst) => inst.destroy()).catch(() => {}));
    activeCharts = [];

    const p = palette();
    document.querySelectorAll('[data-chart-type]').forEach((el) => {
        const renderer = renderers[el.dataset.chartType];
        if (!renderer) return;
        const data = JSON.parse(el.dataset.chart);
        el.innerHTML = '';
        activeCharts.push(renderer(el, data, p));
    });
}

// Panggil ini dari app.js, sama kayak modul lain (initTheme, initSidebar, dst).
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