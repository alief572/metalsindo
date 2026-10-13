<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/report_production_time.css') ?>">
<div id="production-time-report" class="production-time-report" data-endpoint="<?= htmlspecialchars(base_url('report_production_time/'),ENT_QUOTES,'UTF-8') ?>">
    <section class="box box-primary" aria-labelledby="pt-filter-title">
        <div class="box-header"><h2 class="box-title" id="pt-filter-title">Filter laporan</h2></div>
        <div class="box-body">
            <form id="pt-filters" class="pt-filters">
                <div class="form-group"><label for="pt-start">Tanggal produksi mulai</label><input type="date" class="form-control" id="pt-start" name="start_date"></div>
                <div class="form-group"><label for="pt-end">Tanggal produksi sampai</label><input type="date" class="form-control" id="pt-end" name="end_date"></div>
                <div class="form-group pt-search"><label for="pt-search">Pencarian</label><input type="search" class="form-control" id="pt-search" name="search" placeholder="SPK produksi/marketing, customer, produk"></div>
                <div class="pt-actions"><button class="btn btn-primary" type="submit">Tampilkan laporan</button><button class="btn btn-default" id="pt-reset" type="button">Reset</button></div>
            </form>
            <p class="help-block">Tanggal mengikuti LHP aktual terbaru per SPK. Kosongkan tanggal untuk semua periode, termasuk SPK tanpa tanggal aktual.</p>
        </div>
    </section>
    <div id="pt-error" class="alert alert-danger" role="alert" hidden></div>
    <section class="box box-primary" aria-labelledby="pt-results-title">
        <div class="box-header pt-toolbar">
            <div><h2 class="box-title" id="pt-results-title">Waktu pekerjaan produksi</h2><p id="pt-period">Semua periode</p></div>
            <button class="btn btn-success" id="pt-export" type="button" disabled>Export Excel</button>
        </div>
        <div class="box-body">
            <p id="pt-note" class="pt-note">Satu kelompok = satu SPK produksi. Total menggunakan menit tersimpan di LHP. SPK dengan lot sama dapat memiliki catatan waktu yang sama; jangan menjumlahkannya sebagai total waktu mesin.</p>
            <div class="pt-controls"><label for="pt-length">Tampilkan <select id="pt-length" class="form-control"><option>10</option><option selected>25</option><option>50</option><option>100</option></select> SPK</label><span>Geser tabel untuk melihat 18 kolom</span></div>
            <div class="pt-table-scroll" role="region" tabindex="0" aria-label="Tabel Report Production Time" aria-describedby="pt-note">
                <table id="pt-table" class="table table-bordered" aria-busy="true">
                    <thead><tr class="pt-column-groups"><th colspan="10" scope="colgroup">Info</th><th colspan="6" scope="colgroup">Progress Pekerjaan</th><th colspan="2" scope="colgroup">Summary</th></tr><tr>
                    <?php foreach ($headers as $label): ?><th scope="col"><?= htmlspecialchars($label,ENT_QUOTES,'UTF-8') ?></th><?php endforeach; ?>
                    </tr></thead>
                    <tbody><tr><td colspan="18">Memuat data...</td></tr></tbody>
                </table>
            </div>
            <div class="pt-pagination"><span id="pt-count" aria-live="polite">Memuat data...</span><div><button class="btn btn-default" id="pt-prev" type="button" disabled>Sebelumnya</button><button class="btn btn-default" id="pt-next" type="button" disabled>Berikutnya</button></div></div>
        </div>
    </section>
</div>
<script src="<?= base_url('assets/js/report_production_time.js') ?>"></script>
