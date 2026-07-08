import $ from 'jquery';
import 'datatables.net-bs5';

export function initDataTables() {
  document.querySelectorAll('[data-datatable]').forEach((table) => {
    if ($.fn.DataTable.isDataTable(table)) return;

    $(table).DataTable({
      responsive: true,
      pageLength: 10,
      language: {
        search: '',
        searchPlaceholder: 'Cari...',
        paginate: { previous: '‹', next: '›' },
        info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
        lengthMenu: 'Tampilkan _MENU_ baris',
        zeroRecords: 'Data tidak ditemukan',
      },
    });
  });
}