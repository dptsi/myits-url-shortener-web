/* Default DataTables mengikuti komponen DataTables myITS Design System */
$.extend(true, $.fn.dataTable.defaults, {
    language: {
        searchPlaceholder: 'Cari',
        sSearch: '',
        lengthMenu: '_MENU_',
        processing: 'Memuat...',
        zeroRecords: 'Tidak ada data',
        emptyTable: 'Belum ada data',
        info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 data',
        infoFiltered: '(disaring dari _MAX_ data)',
        paginate: {
            first: '<span class="ms-rounded tx-20">keyboard_double_arrow_left</span>',
            last: '<span class="ms-rounded tx-20">keyboard_double_arrow_right</span>',
            next: '<span class="ms-rounded tx-20">chevron_forward</span>',
            previous: '<span class="ms-rounded tx-20">chevron_backward</span>'
        }
    },
    dom: '<"d-flex justify-content-between align-items-center gap-3 mg-b-15"lf>' +
         '<"table-responsive scrollbar-rounded"rt>' +
         '<"d-flex align-items-center gap-2 flex-wrap pd-b-20"pi>',
    initComplete: function () {
        var $length = $(this.api().table().container()).find('.dataTables_length');
        $length.find('select').select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $length
        });
    }
});
