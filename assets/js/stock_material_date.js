(function ($) {
    'use strict';
    $(function () {
        var $host = $('[data-stock-date-host]');
        if (!$host.length) return;
        var prefix = $host.attr('data-prefix');
        var warehouse = $host.attr('data-warehouse');
        var columns = warehouse === '3' ? 14 : 13;
        var $date = $('#' + prefix + '-stock-date');
        var today = $date.attr('max');
        var appliedDate = today;
        var timer;
        var totalWeight = null;
        var loaded = false;
        function element(suffix) { return $('#' + prefix + '-' + suffix); }
        function format(value, decimals) {
            return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        }
        function validation(message) { element('date-error').text(message).prop('hidden', !message); }
        function filters() {
            clearTimeout(timer);
            var width = document.getElementById('series');
            if (width && !width.checkValidity()) { validation('Width harus berupa angka positif atau nol.'); return; }
            validation('');
            table.search(element('search').val()).draw();
        }
        $host.on('processing.dt', function (event, settings, processing) {
            $host.attr('aria-busy', processing ? 'true' : 'false');
            element('export').prop('disabled', processing || !loaded);
        });
        var table = $('#example1').DataTable({
            serverSide: true, processing: true, stateSave: true, autoWidth: false,
            order: [[0, 'asc']], pageLength: 10, lengthMenu: [10, 20, 50, 100, 150],
            dom: 'r<"' + prefix + '-table-scroll"t><"' + prefix + '-table-controls"lip>',
            language: {
                lengthMenu: 'Tampilkan _MENU_ baris', info: '_START_–_END_ dari _TOTAL_ baris stok',
                infoEmpty: '0 baris stok', infoFiltered: '', processing: 'Memuat persediaan…', loadingRecords: 'Memuat persediaan…',
                emptyTable: 'Belum ada persediaan pada tanggal ini. Coba pilih tanggal lain.',
                zeroRecords: 'Tidak ada hasil yang cocok. Ubah tanggal, pencarian, width, atau Reset filter.',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            },
            columnDefs: [
                { targets: 'no-sort', orderable: false }, { targets: 0, className: 'text-center' },
                { targets: 1, className: prefix + '-code' }, { targets: 2, className: prefix + '-lot' },
                { targets: warehouse === '3' ? [3, 4, 10, 12] : [3, 4, 9, 11], className: prefix + '-description' },
                { targets: warehouse === '3' ? [5, 6, 7, 9] : [5, 6, 7], className: prefix + '-number' },
                { targets: 8, className: prefix + '-number ' + prefix + '-weight' },
                { targets: warehouse === '3' ? 11 : 10, className: prefix + '-location' }
            ],
            stateSaveParams: function (settings, data) { data.stockDateWidth = $('#series').val() || ''; },
            stateLoadParams: function (settings, data) {
                if (!data.columns || data.columns.length !== columns) return false;
                $('#series').val(data.stockDateWidth || '');
                element('search').val(data.search ? data.search.search : '');
            },
            ajax: {
                url: $host.attr('data-source'), type: 'POST',
                data: function (data) {
                    loaded = false; element('export').prop('disabled', true);
                    data.gudang = warehouse; data.stock_date = appliedDate; data.series = $('#series').val() || '';
                },
                dataSrc: function (data) {
                    if (table && Number(data.draw) < table.settings()[0].iDraw) return data.data;
                    totalWeight = Number(data.recordsAset || 0); loaded = !!data.available;
                    element('error').prop('hidden', true); validation('');
                    element('export').prop('disabled', !loaded);
                    element('total-weight').text(format(totalWeight, 2)); element('total-rows').text(format(data.recordsFiltered, 0));
                    element('snapshot-status').text(!data.available ? 'Snapshot stok tanggal ' + data.stock_date + ' belum tersedia.' :
                        'Tanggal stok: ' + data.stock_date + (data.snapshot_at ? ' | Snapshot: ' + data.snapshot_at + ' WIB' : ' | Stok aktif saat ini'));
                    table.settings()[0].oLanguage.sEmptyTable = !data.available ? 'Snapshot stok tanggal ' + data.stock_date + ' belum tersedia.' :
                        'Belum ada stok yang sesuai pada tanggal ini. Coba ubah tanggal, filter, atau Reset filter.';
                    return data.data;
                },
                error: function (xhr) {
                    loaded = false; totalWeight = null;
                    element('error').prop('hidden', false);
                    element('error').find('span').text(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Data stok gagal dimuat. Coba muat ulang data.');
                    element('export').prop('disabled', true);
                    element('total-weight').add(element('total-rows')).add(element('footer-weight')).text('Tidak tersedia');
                    element('snapshot-status').text('Data stok gagal diperbarui.');
                    $('#example1 tbody').empty().append($('<tr>').append($('<td>').attr('colspan', columns).text('Data stok gagal dimuat. Klik Coba lagi.')));
                    $host.find('.dataTables_processing').hide();
                }
            },
            drawCallback: function () { if (totalWeight !== null) element('footer-weight').text(format(totalWeight, 2)); },
            initComplete: function () {
                element('search').val(this.api().search());
                $host.find('.' + prefix + '-table-scroll').attr({ tabindex: '0', role: 'region', 'aria-label': 'Tabel stok, dapat digeser' });
            }
        });
        element('date-apply').on('click', function () {
            if (!$date[0].checkValidity()) { validation('Pilih tanggal stok yang valid dan tidak melebihi hari ini.'); $date[0].focus(); return; }
            appliedDate = $date.val(); filters();
        });
        element('search').add($('#series')).on('input', function () { clearTimeout(timer); timer = setTimeout(filters, 450); });
        element('filter-form').on('submit', function (event) { event.preventDefault(); element('date-apply').trigger('click'); });
        element('reset').on('click', function () {
            clearTimeout(timer); $date.val(today); appliedDate = today;
            element('search').val(''); $('#series').val(''); validation('');
            table.search('').order([[0, 'asc']]).draw();
        });
        element('retry').on('click', function () { table.ajax.reload(null, false); });
        element('export').on('click', function () {
            if (!loaded) return;
            window.location.href = $host.attr('data-export-url') + '?' + $.param({
                gudang: warehouse, stock_date: appliedDate, series: $('#series').val() || '',
                search: { value: table.search() }, direction: table.order()[0][1]
            });
        });
    });
})(jQuery);
