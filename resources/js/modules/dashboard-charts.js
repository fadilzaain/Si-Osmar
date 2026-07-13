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
        textMuted: token('--color-text-muted'),
        border: token('--color-border'),
    };
}

const baseFont = { fontFamily: 'Inter, sans-serif' };

function baseOptions(colors) {
    return {
        chart: {
            background: 'transparent',
            toolbar: { show: false },
            fontFamily: baseFont.fontFamily,
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
        },
        colors,
        grid: { borderColor: token('--color-border'), strokeDashArray: 4 },
        tooltip: { theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light' },
        legend: { show: false },
    };
}

function renderLine(el, data, p) {
    const options = {
        ...baseOptions([p.success, p.mint]),
        chart: { ...baseOptions([]).chart, type: 'line', height: 90, sparkline: { enabled: false } },
        series: data.series,
        xaxis: {
            categories: data.categories,
            labels: { style: { colors: p.textMuted, fontSize: '9px' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { show: false },
        stroke: { curve: 'smooth', width: 2.5 },
        markers: { size: 0 },
    };
    return new ApexCharts(el, options).render();
}

function renderDonut(el, data, p) {
    const colorMap = { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger };
    const options = {
        ...baseOptions([colorMap.success, colorMap.warning, colorMap.danger]),
        chart: { ...baseOptions([]).chart, type: 'donut', height: 90 },
        series: data.series,
        labels: data.labels,
        dataLabels: { enabled: false },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '68%' } } },
    };
    return new ApexCharts(el, options).render();
}

function renderDonutSingle(el, data, p) {
    const colorMap = { success: p.success, primary: p.primary, warning: p.warning, danger: p.danger };
    const color = colorMap[data.color] || p.success;
    const options = {
        chart: { type: 'radialBar', height: 96, sparkline: { enabled: true }, animations: { enabled: true, speed: 600 } },
        series: [data.persen],
        colors: [color],
        plotOptions: {
            radialBar: {
                hollow: { size: '62%' },
                track: { background: p.border },
                dataLabels: {
                    name: { show: false },
                    value: {
                        fontSize: '16px',
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

function renderBar(el, data, p) {
    const colors = data.series[0].data.map((_, i) =>
        data.highlight_index === i ? p.primary : p.mint
    );
    const options = {
        ...baseOptions([p.mint]),
        chart: { ...baseOptions([]).chart, type: 'bar', height: 90 },
        series: data.series,
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '55%',
                distributed: data.highlight_index !== undefined,
            },
        },
        colors: data.highlight_index !== undefined ? colors : [p.success],
        dataLabels: { enabled: false },
        xaxis: {
            categories: data.categories,
            labels: { show: false },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { show: false },
    };
    return new ApexCharts(el, options).render();
}

const renderers = {
    line: renderLine,
    donut: renderDonut,
    'donut-single': renderDonutSingle,
    bar: renderBar,
};

let activeCharts = [];
let observerAttached = false;

function renderAll() {
    activeCharts.forEach((c) => c.then((inst) => inst.destroy()).catch(() => {}));
    activeCharts = [];

    const p = palette();
    document.querySelectorAll('[data-chart-type]').forEach((el) => {
        const type = el.dataset.chartType;
        const renderer = renderers[type];
        if (!renderer) return;
        const data = JSON.parse(el.dataset.chart);
        el.innerHTML = '';
        activeCharts.push(renderer(el, data, p));
    });
}

// Panggil ini dari app.js, sama kayak modul lain (initTheme, initSidebar, dst).
// Otomatis no-op kalau halaman gak punya elemen [data-chart-type].
export function initDashboardCharts() {
    if (!document.querySelector('[data-chart-type]')) return;

    renderAll();

    // Re-render pas toggle theme (theme.js ganti atribut data-theme di <html>),
    // biar warna chart ikut ganti tanpa perlu ubah theme.js sama sekali.
    if (!observerAttached) {
        const themeObserver = new MutationObserver((mutations) => {
            if (mutations.some((m) => m.attributeName === 'data-theme')) {
                renderAll();
            }
        });
        themeObserver.observe(document.documentElement, { attributes: true });
        observerAttached = true;
    }
}