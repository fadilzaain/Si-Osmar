// Accordion generic buat halaman yang datanya udah di-render server-side
// (gak perlu fetch, cukup toggle class .open). Dipakai di Bezetting SDM,
// dan bisa dipakai ulang di halaman lain yang butuh accordion sejenis
// tanpa lazy-load — cukup tandai wrapper-nya dengan [data-accordion].
export function initAccordion() {
    document.querySelectorAll('[data-accordion]').forEach((list) => {
        list.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const item = trigger.parentElement;
                item.classList.toggle('open');
            });
        });
    });
}