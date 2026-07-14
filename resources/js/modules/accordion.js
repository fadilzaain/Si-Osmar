/**
 * Accordion generic — murni buka/tutup visual, tanpa fetch/AJAX.
 * Dipakai buat halaman yang datanya udah di-render lengkap dari server
 * (semua panel udah ada di DOM), tinggal di-toggle.
 *
 * Markup yang dibutuhkan:
 * <div data-accordion>
 *   <div class="...">
 *     <button data-accordion-trigger>...</button>
 *     <div data-accordion-panel>...</div>
 *   </div>
 * </div>
 *
 * Kelas "open" ditambahkan ke elemen parent trigger (biasanya .xxx-unit),
 * biar CSS yang atur animasi expand/collapse-nya (grid-template-rows trick),
 * bukan JS yang ngatur height secara manual.
 */
export function initAccordion(root = document) {
    root.querySelectorAll('[data-accordion]').forEach((group) => {
        group.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                // Konvensi: trigger & panel adalah sibling langsung di dalam
                // "item wrapper" (misal .bzs-unit) — jadi cukup toggle class
                // "open" di parent-nya trigger.
                trigger.parentElement.classList.toggle('open');
            });
        });
    });
}
