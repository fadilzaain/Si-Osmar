// Accordion generic buat halaman yang datanya udah di-render server-side
// (gak perlu fetch, cukup toggle class .open). Dipakai di Bezetting SDM
// dan Monitoring Dokumen, bisa dipakai ulang di halaman lain yang butuh
// accordion sejenis — cukup tandai wrapper-nya dengan [data-accordion].
//
// Setiap toggle nembak custom event 'accordion:toggle' (bubbling) di
// elemen item-nya, isinya { open: true/false } — dipakai halaman lain
// buat trigger animasi lanjutan pas detail dibuka (lihat monitoring-dokumen.js).
export function initAccordion() {
    document.querySelectorAll('[data-accordion]').forEach((list) => {
        list.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const item = trigger.parentElement;
                const isOpen = item.classList.toggle('open');
                item.dispatchEvent(new CustomEvent('accordion:toggle', {
                    detail: { open: isOpen },
                    bubbles: true,
                }));
            });
        });
    });
}