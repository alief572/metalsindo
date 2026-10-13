/* global jQuery */
(function ($) {
    'use strict';
    $(function () {
        var root = $('#production-time-report');
        if (!root.length) return;
        var endpoint = root.attr('data-endpoint'), form = $('#pt-filters'), table = $('#pt-table');
        var active = {start_date: '', end_date: '', search: ''};
        var start = 0, length = 25, filtered = 0, request = null, revision = 0, ready = false;
        function cell(row, value, className, span) {
            var td = $('<td>').text(value === null || value === '' ? '—' : value);
            if (className) td.addClass(className);
            if (span) td.attr('rowspan', span);
            row.append(td);
        }
        function number(value) {
            return value === null ? null : Number(value).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        function date(value) { return value ? value.slice(8, 10) + '-' + value.slice(5, 7) + '-' + value.slice(0, 4) : null; }
        function render(groups) {
            var body = table.find('tbody').empty();
            if (!groups.length) {
                $('<tr>').append($('<td colspan="18">').text('Tidak ada catatan waktu sesuai filter. Ubah periode atau pencarian, atau klik Reset.')).appendTo(body);
                return;
            }
            groups.forEach(function (group, index) {
                var span = group.progress.length;
                group.progress.forEach(function (progress, stage) {
                    var row = $('<tr>').toggleClass('pt-alternate', index % 2 === 1).toggleClass('pt-group-start', stage === 0);
                    if (stage === 0) group.info.forEach(function (value, column) {
                        cell(row, column >= 5 ? number(value) : (column === 2 ? date(value) : value),
                            column >= 5 ? 'pt-number' : (column === 4 || column === 3 ? 'pt-description' : 'pt-document'), span);
                    });
                    cell(row, stage + 1, 'pt-number'); cell(row, progress.progress);
                    cell(row, progress.start); cell(row, progress.finish);
                    cell(row, progress.invalid_total ? 'Tidak valid: ' + progress.raw_total : number(progress.total), progress.invalid_total ? 'pt-invalid' : 'pt-number');
                    cell(row, progress.keterangan, 'pt-description');
                    if (stage === 0) group.summary.forEach(function (value) {
                        cell(row, group.invalid_total ? 'Tidak tersedia' : number(value), group.invalid_total ? 'pt-invalid' : 'pt-number', span);
                    });
                    body.append(row);
                });
            });
        }
        function load() {
            var current = ++revision;
            if (request) request.abort();
            ready = false; $('#pt-export, #pt-prev, #pt-next').prop('disabled', true);
            $('#pt-error').prop('hidden', true); $('#pt-count').text('Memuat data...');
            table.attr('aria-busy', 'true');
            table.find('tbody').empty().append($('<tr>').append($('<td colspan="18">').text('Memuat data...')));
            $('#pt-period').text((active.start_date || active.end_date ? (active.start_date || 'Awal') + ' s/d ' + (active.end_date || 'Akhir') : 'Semua periode') + (active.search ? ' · ' + active.search : ''));
            request = $.ajax({url: endpoint + 'get_data', type: 'POST', dataType: 'json', data: $.extend({}, active, {start: start, length: length})})
                .done(function (response) {
                    if (current !== revision) return;
                    if (response.error || !Array.isArray(response.data)) { fail(response.error || 'Respons laporan tidak valid.'); return; }
                    filtered = response.recordsFiltered;
                    if (start >= filtered && start > 0) { start = 0; load(); return; }
                    render(response.data); ready = true;
                    $('#pt-export').prop('disabled', false);
                    $('#pt-prev').prop('disabled', start === 0); $('#pt-next').prop('disabled', start + length >= filtered);
                    $('#pt-count').text('Menampilkan ' + (filtered ? start + 1 : 0) + '–' + Math.min(start + response.data.length, filtered) + ' dari ' + filtered + ' SPK');
                }).fail(function (_, status) {
                    if (current === revision && status !== 'abort') fail('Data gagal dimuat. Klik Tampilkan laporan untuk mencoba kembali.');
                }).always(function () { if (current === revision) table.attr('aria-busy', 'false'); });
        }
        function fail(message) {
            ready = false;
            $('#pt-error').text(message).prop('hidden', false); $('#pt-count').text('Data gagal dimuat');
            table.find('tbody').empty().append($('<tr>').append($('<td colspan="18">').text('Laporan belum dapat ditampilkan.')));
        }
        form.on('submit', function (event) {
            event.preventDefault();
            var next = {};
            $.each(form.serializeArray(), function (_, field) { next[field.name] = $.trim(field.value); });
            if (next.start_date && next.end_date && next.start_date > next.end_date) {
                $('#pt-error').text('Tanggal awal tidak boleh melebihi tanggal akhir.').prop('hidden', false); $('#pt-start').focus(); return;
            }
            active = next; start = 0; load();
        });
        $('#pt-reset').on('click', function () { form[0].reset(); active = {start_date: '', end_date: '', search: ''}; start = 0; load(); });
        $('#pt-length').on('change', function () { length = Number(this.value); start = 0; load(); });
        $('#pt-prev').on('click', function () { start = Math.max(0, start - length); load(); });
        $('#pt-next').on('click', function () { start += length; load(); });
        $('#pt-export').on('click', function () { if (ready) window.location.href = endpoint + 'export_excel?' + $.param(active); });
        load();
    });
}(jQuery));
