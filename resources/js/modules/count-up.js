// Animasi hitung naik buat semua .stat-card-value di halaman manapun
// (Dashboard, Bezetting SDM, Monitoring Dokumen — dipakai bareng karena
// stat-card adalah komponen bersama). Cuma jalan kalau isinya beneran
// angka; kalau bukan (teks biasa), dibiarkan apa adanya.
const DURATION = 900;

function animateValue(el) {
    el.dataset.counted = '1';

    const raw = el.textContent.trim();
    const match = raw.match(/\d[\d.,]*/);
    if (!match) return;

    const numeric = match[0].replace(/,/g, '');
    const target = parseFloat(numeric);
    if (Number.isNaN(target)) return;

    const prefix = raw.slice(0, match.index);
    const suffix = raw.slice(match.index + match[0].length);
    const decimals = numeric.includes('.') ? numeric.split('.')[1].length : 0;

    const start = performance.now();

    function tick(now) {
        const progress = Math.min((now - start) / DURATION, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic — cepat di awal, halus di akhir
        const current = (target * eased).toFixed(decimals);
        el.textContent = prefix + current + suffix;

        if (progress < 1) {
            requestAnimationFrame(tick);
        } else {
            el.textContent = prefix + target.toFixed(decimals) + suffix;
        }
    }

    requestAnimationFrame(tick);
}

export function initCountUp() {
    const targets = document.querySelectorAll('.stat-card-value, .chart-headline-value');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach(animateValue);
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            animateValue(entry.target);
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.3 });

    targets.forEach((el) => observer.observe(el));
}

// Animasi "tumbuh" buat .dist-bar (lihat distribution-bar.blade.php):
// segmen di-render dulu di flex-grow 0, baru sesudah 2 frame di-set ke
// nilai aslinya — browser butuh "before" state ke-render dulu sebelum
// transition flex-grow beneran kepicu.
export function initDistributionBars() {
    const bars = document.querySelectorAll('[data-dist-bar]');
    if (!bars.length) return;

    bars.forEach((bar) => {
        const segments = bar.querySelectorAll('[data-dist-target]');
        segments.forEach((seg) => seg.style.setProperty('--dist-flex', 0));

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                segments.forEach((seg) => {
                    seg.style.setProperty('--dist-flex', seg.dataset.distTarget);
                });
            });
        });
    });
}