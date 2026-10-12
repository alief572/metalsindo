<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/report_lhp_slitting.css') ?>">
<div class="lhp-report">
    <div class="lhp-shell">
        <section class="lhp-panel" aria-labelledby="lhp-filter-title">
            <div class="lhp-panel-heading">
                <div><span class="lhp-eyebrow">Production report</span><h3 id="lhp-filter-title">Filter laporan</h3><p>Pilih periode produksi atau cari hasil slitting yang dibutuhkan.</p></div>
                <span class="lhp-badge">LHP Slitting Finishgood</span>
            </div>
            <form id="lhp-filters">
                <div class="filter-row">
                    <div class="form-group"><label for="lhp-start">Tanggal produksi mulai</label><input class="form-control" type="date" id="lhp-start" name="start_date"></div>
                    <div class="form-group"><label for="lhp-end">Tanggal produksi sampai</label><input class="form-control" type="date" id="lhp-end" name="end_date"></div>
                    <div class="form-group search-field"><label for="lhp-search">Pencarian</label><input class="form-control" type="search" id="lhp-search" name="search" placeholder="SPK produksi/marketing, customer, product, lot slitting"></div>
                    <div class="filter-actions"><button class="btn lhp-primary" type="submit">Tampilkan <span class="lhp-button-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span></button><button class="btn lhp-secondary" id="lhp-reset" type="button">Reset</button></div>
                </div>
            </form>
            <p class="lhp-filter-hint">Kosongkan tanggal untuk menampilkan semua periode.</p>
        </section>
    </div>
    <div id="lhp-error" class="alert alert-danger" role="alert" style="display:none"></div>
    <div class="lhp-shell">
        <section class="lhp-panel lhp-results" aria-labelledby="lhp-results-title">
            <div class="report-toolbar"><div><h3 id="lhp-results-title">Hasil slitting <span id="lhp-count" class="lhp-count" aria-live="polite">Memuat data...</span></h3><div id="lhp-period">Semua periode</div></div><button class="btn lhp-export" id="lhp-export" type="button" disabled>Export Excel <span class="lhp-button-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 16v5h14v-5"/></svg></span></button></div>
            <div class="lhp-table-caption"><span>Detail hasil produksi</span><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h16M8 8l-4 4 4 4m8-8 4 4-4 4"/></svg> Geser untuk melihat 19 kolom</span></div>
            <div role="region" aria-label="Tabel Report LHP Slitting" tabindex="0">
                <table id="lhp-table" class="table table-bordered table-striped" style="width:100%" aria-describedby="lhp-note">
                    <thead><tr class="column-groups"><th colspan="5" scope="colgroup">A. Info</th><th colspan="4" scope="colgroup">B. Produksi</th><th colspan="9" scope="colgroup">C. Summary Produksi &amp; Sisa</th><th scope="colgroup">D. Penimbangan Mother Coil</th></tr><tr>
                    <?php foreach ($headers as $label): ?><th scope="col"><?= htmlspecialchars($label,ENT_QUOTES,'UTF-8') ?></th><?php endforeach; ?>
                    </tr></thead><tbody></tbody>
                </table>
            </div>
            <div id="lhp-note" class="report-note"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/></svg><p><strong>Catatan pembacaan</strong><br>Satu baris = satu hasil slitting. Nilai Summary Produksi &amp; Sisa dan Penimbangan Mother Coil diulang pada setiap baris dalam LHP yang sama; jangan dijumlahkan langsung antarbaris.</p></div>
        </section>
    </div>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(function () {
    var form = $('#lhp-filters'), errorBox = $('#lhp-error');
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
            $('#lhp-start').focus(); return;
        }
        activeFilters = next;
        table.ajax.reload();
    }
    $('#lhp-table').on('preXhr.dt', function () {
        exportReady = false; $('#lhp-export').prop('disabled', true);
        errorBox.hide(); $('#lhp-count').text('Memuat data...');
        var period = activeFilters.start_date || activeFilters.end_date
            ? (activeFilters.start_date || 'Awal') + ' s/d ' + (activeFilters.end_date || 'Akhir') : 'Semua periode';
        $('#lhp-period').text(period + (activeFilters.search ? ' · Pencarian: ' + activeFilters.search : ''));
    }).on('xhr.dt', function (_, settings, json) {
        if (!json || json.error) {
            errorBox.text(json && json.error ? json.error : 'Data gagal dimuat. Coba tampilkan laporan kembali.').show();
            $('#lhp-count').text('Data gagal dimuat');
            return;
        }
        exportReady = true; $('#lhp-export').prop('disabled', false);
        $('#lhp-count').text(json.recordsFiltered + ' hasil slitting');
    });
    var table = $('#lhp-table').DataTable({
        processing:true, serverSide:true, scrollX:true, searching:false,
        pageLength:25, lengthMenu:[10,25,50,100], order:[[3,'desc']], orderMulti:false,
        columnDefs:[{targets:[5,6,8,9,10,11,12,13,14,15,16,17,18],className:'lhp-number'}, {targets:[0,1,7],className:'lhp-document'}, {targets:3,className:'lhp-date'}],
        ajax:{url:siteurl+'report_lhp_slitting/get_data', type:'POST', data:function (data) { $.extend(data,activeFilters); }},
        language:{emptyTable:'Belum ada data LHP Slitting.', zeroRecords:'Tidak ada hasil sesuai filter.', processing:'Memuat hasil slitting...', lengthMenu:'Tampilkan _MENU_ baris', info:'Menampilkan _START_–_END_ dari _TOTAL_ hasil', infoEmpty:'Menampilkan 0 hasil', infoFiltered:'(dari _MAX_ hasil)', paginate:{first:'Awal',last:'Akhir',next:'Berikutnya',previous:'Sebelumnya'}}
    });
    form.on('submit', function (event) { event.preventDefault(); apply(); });
    $('#lhp-reset').on('click', function () { form[0].reset(); apply(); });
    $('#lhp-export').on('click', function () {
        if (!exportReady) return;
        var params = $.extend({},activeFilters), order = table.order()[0];
        params['order[0][column]'] = order[0]; params['order[0][dir]'] = order[1];
        window.location.href = siteurl+'report_lhp_slitting/export_excel?'+$.param(params);
    });
});
</script>
