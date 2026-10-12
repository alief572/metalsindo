<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/report_incoming.css') ?>">
<div class="incoming-report">
    <div class="incoming-shell">
        <section class="incoming-panel" aria-labelledby="incoming-filter-title">
            <div class="incoming-panel-heading">
                <div><h2 id="incoming-filter-title">Filter laporan</h2><p>Laporan penerimaan material supplier. Pilih periode incoming atau cari dokumen, supplier, material, dan lot.</p></div>
            </div>
            <form id="incoming-filters">
                <div class="filter-row">
                    <div class="form-group"><label for="incoming-start">Tanggal incoming mulai</label><input class="form-control" type="date" id="incoming-start" name="start_date" aria-describedby="incoming-filter-hint"></div>
                    <div class="form-group"><label for="incoming-end">Tanggal incoming sampai</label><input class="form-control" type="date" id="incoming-end" name="end_date" aria-describedby="incoming-filter-hint"></div>
                    <div class="form-group search-field"><label for="incoming-search">Pencarian</label><input class="form-control" type="search" id="incoming-search" name="search" placeholder="PR, PO, supplier, material, Incoming, lot, customer"></div>
                    <div class="filter-actions"><button class="btn incoming-primary" type="submit">Tampilkan laporan</button><button class="btn incoming-secondary" id="incoming-reset" type="button">Reset</button></div>
                </div>
            </form>
            <p id="incoming-filter-hint" class="incoming-filter-hint">Kosongkan tanggal untuk menampilkan semua periode.</p>
        </section>
    </div>
    <div id="incoming-error" class="alert alert-danger" role="alert" style="display:none"></div>
    <div class="incoming-shell">
        <section class="incoming-panel incoming-results" aria-labelledby="incoming-results-title">
            <div class="report-toolbar">
                <div class="incoming-result-heading"><h2 id="incoming-results-title">Hasil incoming</h2><div class="incoming-result-meta"><span id="incoming-count" class="incoming-count" aria-live="polite">Memuat data...</span><span id="incoming-period">Semua periode</span></div></div>
                <div class="incoming-table-actions"><span class="incoming-scroll-hint">Geser tabel untuk melihat 19 kolom</span><button class="btn incoming-export" id="incoming-export" type="button" disabled><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 16v5h14v-5"/></svg>Export Excel</button></div>
            </div>
            <div id="incoming-note" class="report-note">
                <p><strong>Perhatikan progress PO:</strong> satu baris adalah satu detail penerimaan/lot. Total Qty Receive dan Balance adalah progress item PO terkini yang diulang pada setiap lot; jangan dijumlahkan antarbaris. Filter tanggal tidak mengubah total receive.</p>
                <p><strong>Keterangan data:</strong> PIC/Surat Jalan yang ambigu ditandai <strong>Belum terverifikasi</strong>. Customer mengikuti data stok historis dan dapat berubah.</p>
            </div>
            <div role="region" aria-label="Tabel Report Incoming" tabindex="0">
                <table id="incoming-table" class="table table-bordered table-striped" style="width:100%" aria-describedby="incoming-note">
                    <thead><tr class="column-groups"><th colspan="8" scope="colgroup">A. Purchase Order</th><th colspan="5" scope="colgroup">B. Penerimaan</th><th colspan="6" scope="colgroup">C. Dokumen dan Lot</th></tr><tr>
                    <?php foreach ($headers as $label): ?><th scope="col"><?= htmlspecialchars($label,ENT_QUOTES,'UTF-8') ?></th><?php endforeach; ?>
                    </tr></thead><tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(function () {
    var form = $('#incoming-filters'), errorBox = $('#incoming-error');
    var activeFilters = {start_date:'', end_date:'', search:''};
    var exportReady = false;
    function filters() {
        var values = {};
        $.each(form.serializeArray(), function (_, field) { values[field.name] = $.trim(field.value); });
        return values;
    }
    function apply() {
        var next = filters();
        if (next.start_date && next.end_date && next.start_date > next.end_date) {
            errorBox.text('Tanggal awal tidak boleh melebihi tanggal akhir.').show();
            $('#incoming-start').focus(); return;
        }
        activeFilters = next;
        table.ajax.reload();
    }
    $('#incoming-table').on('preXhr.dt', function () {
        exportReady = false; $('#incoming-export').prop('disabled', true);
        errorBox.hide(); $('#incoming-count').text('Memuat data...');
        var period = activeFilters.start_date || activeFilters.end_date
            ? (activeFilters.start_date || 'Awal') + ' s/d ' + (activeFilters.end_date || 'Akhir') : 'Semua periode';
        $('#incoming-period').text(period + (activeFilters.search ? ' · Pencarian: ' + activeFilters.search : ''));
    }).on('xhr.dt', function (_, settings, json) {
        if (json && Number(json.draw) !== settings.iDraw) return;
        if (!json || json.error) {
            errorBox.text(json && json.error ? json.error : 'Data gagal dimuat. Coba tampilkan laporan kembali.').show();
            $('#incoming-count').text('Data gagal dimuat');
            return;
        }
        exportReady = true; $('#incoming-export').prop('disabled', false);
        $('#incoming-count').text(json.recordsFiltered + ' penerimaan');
    });
    var table = $('#incoming-table').DataTable({
        processing:true, serverSide:true, scrollX:true, searching:false,
        pageLength:25, lengthMenu:[10,25,50,100], order:[[8,'desc']], orderMulti:false,
        columnDefs:[{targets:0,orderable:false},{targets:[6,7,9,10,11],className:'incoming-number'}, {targets:[1,2,13,17],className:'incoming-document'}, {targets:[3,8],className:'incoming-date'}],
        ajax:{url:siteurl+'report_incoming/get_data', type:'POST', data:function (data) { $.extend(data,activeFilters); }},
        language:{emptyTable:'Belum ada data Incoming material supplier. Pastikan penerimaan material supplier sudah dicatat pada modul Incoming.', zeroRecords:'Tidak ada hasil sesuai filter. Ubah periode atau kata pencarian, atau klik Reset untuk melihat semua hasil.', processing:'Memuat penerimaan...', lengthMenu:'Tampilkan _MENU_ baris', info:'Menampilkan _START_–_END_ dari _TOTAL_ hasil', infoEmpty:'Menampilkan 0 hasil', infoFiltered:'(dari _MAX_ hasil)', paginate:{first:'Awal',last:'Akhir',next:'Berikutnya',previous:'Sebelumnya'}}
    });
    form.on('submit', function (event) { event.preventDefault(); apply(); });
    $('#incoming-reset').on('click', function () { form[0].reset(); apply(); });
    $('#incoming-export').on('click', function () {
        if (!exportReady) return;
        var params = $.extend({},activeFilters), order = table.order()[0];
        params['order[0][column]'] = order[0]; params['order[0][dir]'] = order[1];
        window.location.href = siteurl+'report_incoming/export_excel?'+$.param(params);
    });
});
</script>

