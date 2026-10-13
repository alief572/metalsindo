<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/report_spk_marketing.css') ?>">
<div class="spk-report is-compact">
    <section class="spk-panel-shell" aria-labelledby="spk-filter-title">
    <div class="spk-panel">
        <div class="spk-section-heading"><h2 id="spk-filter-title">Filter laporan</h2><p>Telusuri item SPK, spesifikasi produk, dan rencana pengiriman berdasarkan periode, customer, atau status approval.</p></div>
        <form id="spk-report-filters">
            <div class="spk-filter-grid">
                <div class="form-group"><label for="spk-start">Tanggal SPK mulai</label><input id="spk-start" name="start_date" type="date" class="form-control" aria-describedby="spk-filter-hint spk-report-error"></div>
                <div class="form-group"><label for="spk-end">Tanggal SPK sampai</label><input id="spk-end" name="end_date" type="date" class="form-control" aria-describedby="spk-filter-hint"></div>
                <div class="form-group spk-filter-customer"><label for="spk-customer">Customer</label><select id="spk-customer" name="customer_id" class="form-control">
                    <option value="">Semua Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= htmlspecialchars($customer['id_customer'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($customer['name_customer'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="form-group"><label for="spk-status">Status approval</label><select id="spk-status" name="status" class="form-control" aria-describedby="spk-filter-hint"><option value="approved">Approved</option><option value="unapproved">Belum Approved</option><option value="all">Semua</option></select></div>
            </div>
            <div class="spk-filter-footer">
                <div class="form-group spk-filter-search"><label for="spk-search">Nomor dokumen</label><input id="spk-search" name="search" type="search" class="form-control" placeholder="Cari nomor SPK atau PO..."></div>
                <div class="spk-filter-actions">
                    <button type="button" id="spk-reset" class="btn spk-button-reset">Reset filter</button>
                    <button type="submit" class="btn spk-button-primary">Tampilkan laporan</button>
                </div>
            </div>
        </form>
        <p id="spk-filter-hint" class="spk-filter-hint">Kosongkan tanggal untuk semua periode. Reset filter mengembalikan status ke Approved.</p>
    </div>
    </section>
    <div id="spk-report-error" class="alert alert-danger" role="alert" style="display:none"></div>
    <section class="spk-panel-shell spk-results" aria-labelledby="spk-results-title">
    <div class="spk-panel">
        <div class="spk-results-heading">
            <div class="spk-result-heading"><h2 id="spk-results-title">Detail SPK</h2><div class="spk-result-meta"><span id="spk-result-count" class="spk-count" aria-live="polite">Memuat...</span><p id="spk-applied-filters" class="spk-applied-filters">Semua periode · Semua Customer · Approved</p></div></div>
            <button type="button" id="spk-export" class="btn spk-button-export"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 15v5h14v-5"/></svg>Export Excel</button>
        </div>
        <div id="spk-report-note" class="spk-report-note"><p><strong>Perhatikan total per SPK:</strong> Total Qty Product (KG) diulang pada tiap item SPK yang sama; jangan dijumlahkan antaritem. Delivery Date mengikuti rencana delivery per item SPK.</p></div>
        <div class="spk-table-toolbar"><div class="spk-table-status"><span id="spk-load-status" role="status">Menyiapkan laporan...</span><span class="spk-scroll-hint">Geser tabel untuk melihat 17 kolom</span></div></div>
        <div class="spk-table-region" role="region" aria-label="Tabel detail SPK Marketing" tabindex="0">
        <table id="spk-report-table" class="table" style="width:100%" aria-describedby="spk-report-note">
            <thead><tr class="spk-column-groups"><th colspan="6" scope="colgroup">Informasi SPK</th><th colspan="10" scope="colgroup">Spesifikasi produk / item</th><th colspan="1" scope="colgroup">Ringkasan</th></tr><tr>
                <?php foreach ($headers as $label): ?><th scope="col"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?>
            </tr></thead><tbody></tbody>
        </table>
        </div>
    </div>
    </section>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(function () {
    var form = $('#spk-report-filters');
    var errorBox = $('#spk-report-error');
    var activeFilters = readFilters();
    var requestFailed = false;
    var loadStatus = $('#spk-load-status');
    var resultCount = $('#spk-result-count');
    function readFilters() {
        var filters = {};
        $.each(form.serializeArray(), function (_, field) { filters[field.name] = field.value; });
        return filters;
    }
    function updateFilterSummary() {
        var period = 'Semua periode';
        if (activeFilters.start_date && activeFilters.end_date) period = activeFilters.start_date + ' s/d ' + activeFilters.end_date;
        else if (activeFilters.start_date) period = 'Mulai ' + activeFilters.start_date;
        else if (activeFilters.end_date) period = 'Sampai ' + activeFilters.end_date;
        // Read labels from the applied values, not from inputs that may have pending edits.
        var customer = $('#spk-customer option').filter(function () { return this.value === activeFilters.customer_id; }).text();
        var status = $('#spk-status option').filter(function () { return this.value === activeFilters.status; }).text();
        var summary = [period, customer, status];
        if (activeFilters.search) summary.push('Dokumen: ' + activeFilters.search);
        $('#spk-applied-filters').text(summary.join(' · '));
    }
    function applyFilters() {
        var filters = readFilters();
        if (filters.start_date && filters.end_date && filters.start_date > filters.end_date) {
            errorBox.text('Tanggal awal tidak boleh melebihi tanggal akhir.').show();
            $('#spk-start').attr('aria-invalid', 'true').focus();
            return false;
        }
        $('#spk-start').removeAttr('aria-invalid');
        errorBox.hide();
        activeFilters = filters;
        return true;
    }
    $('#spk-report-table').on('preXhr.dt', function () {
        requestFailed = false;
        $('.spk-table-region').attr('aria-busy', 'true');
        loadStatus.text('Memuat laporan...');
        resultCount.text('Memuat...');
        updateFilterSummary();
    });
    var table = $('#spk-report-table').DataTable({
        processing: true, serverSide: true, scrollX: true, searching: false,
        pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [[2, 'desc']],
        ajax: {
            url: <?= json_encode(site_url('report_spk_marketing/get_data')) ?>, type: 'POST',
            data: function (data) { $.extend(data, activeFilters); },
            dataSrc: function (json) {
                requestFailed = !!json.error;
                if (json.error) errorBox.text(json.error).show(); else errorBox.hide();
                return json.data || [];
            },
            error: function () {
                requestFailed = true;
                $('.spk-table-region').attr('aria-busy', 'false');
                errorBox.text('Data report gagal dimuat. Silakan klik Tampilkan laporan untuk mencoba kembali.').show();
                loadStatus.text('Laporan gagal dimuat');
                resultCount.text('Tidak tersedia');
            }
        },
        drawCallback: function () {
            var info = this.api().page.info();
            $('.spk-table-region').attr('aria-busy', 'false');
            resultCount.text(requestFailed ? 'Tidak tersedia' : info.recordsDisplay.toLocaleString('id-ID') + ' item');
            loadStatus.text(requestFailed ? 'Laporan gagal dimuat' : (info.recordsDisplay ? 'Laporan siap ditinjau' : 'Tidak ada hasil untuk filter ini'));
        },
        columnDefs: [{targets: [6, 11, 12, 13, 14, 16], className: 'text-right'}],
        language: {
            emptyTable: 'Tidak ada data SPK Marketing untuk filter ini. Ubah periode, customer, status approval, atau nomor dokumen. Reset filter kembali ke status Approved.',
            zeroRecords: 'Tidak ada item yang sesuai. Ubah periode, customer, status approval, atau nomor dokumen. Reset filter kembali ke status Approved.',
            processing: 'Memuat laporan...', lengthMenu: 'Tampilkan _MENU_ item per halaman',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ item', infoEmpty: 'Menampilkan 0 item',
            infoFiltered: '(dari _MAX_ item keseluruhan)',
            paginate: {previous: 'Sebelumnya', next: 'Berikutnya', first: 'Pertama', last: 'Terakhir'},
            aria: {sortAscending: ': urutkan naik', sortDescending: ': urutkan turun'}
        }
    });
    form.on('submit', function (event) { event.preventDefault(); if (applyFilters()) table.ajax.reload(); });
    $('#spk-reset').on('click', function () {
        form[0].reset(); activeFilters = readFilters(); errorBox.hide();
        $('#spk-start').removeAttr('aria-invalid');
        table.order([[2, 'desc']]).ajax.reload();
    });
    $('#spk-export').on('click', function () {
        // Apply current inputs to both the visible table and the export.
        if (!applyFilters()) return;
        table.ajax.reload();
        var params = $.extend({}, activeFilters, {order: [{column: table.order()[0][0], dir: table.order()[0][1]}]});
        window.location.href = <?= json_encode(site_url('report_spk_marketing/export_excel')) ?> + '?' + $.param(params);
    });
});
</script>
