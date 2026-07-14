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
        mint: token('--color-mint'),
        text: token('--color-text'),
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

const renderers = { 'donut-single': renderRadial };

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