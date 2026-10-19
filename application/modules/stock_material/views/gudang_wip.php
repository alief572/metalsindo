<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_wip.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_date.css') ?>">

<div class="stock-material-container stock-dated-wrapper stock-wip-wrapper">
	<section class="stock-panel" aria-labelledby="wip-title">
		<header class="stock-header">
			<div>
				<h1 class="stock-header-title" id="wip-title">Stock WIP</h1>
				<p class="stock-header-desc">Persediaan material dalam proses produksi per lot di Gudang WIP.</p>
			</div>
			<dl class="wip-summary" aria-live="polite">
				<div><dt>Berat total hasil filter</dt><dd><span id="wip-total-weight">Memuat…</span> <small>kg</small></dd></div>
				<div><dt>Baris stok hasil filter</dt><dd id="wip-total-rows">Memuat…</dd></div>
			</dl>
		</header>
		<form id="wip-filter-form" class="wip-toolbar" role="search">
			<?php $this->load->view('stock_material/date_filter',array('date_prefix'=>'wip')); ?>
			<div class="stock-form-group wip-search">
				<label class="stock-label" for="wip-search">Cari persediaan</label>
				<input class="stock-input" type="search" id="wip-search" placeholder="Kode material, nama, lot, dokumen, atau customer" autocomplete="off">
			</div>
			<div class="stock-form-group wip-width">
				<label class="stock-label" for="series">Lebar / width (mm)</label>
				<input class="stock-input" type="number" id="series" name="width" min="0" step="any" placeholder="Semua lebar" inputmode="decimal">
			</div>
			<div class="stock-date-actions">
				<button class="btn-stock btn-stock-primary" type="button" id="wip-date-apply">Terapkan</button>
				<button class="btn-stock btn-stock-reset" type="button" id="wip-reset">Reset filter</button>
				<button class="btn-stock btn-stock-export" type="button" id="wip-export" disabled>Export Excel</button>
			</div>
		</form>
		<p id="wip-snapshot-status" class="stock-date-status" role="status" aria-live="polite"></p>
		<div class="wip-table-heading">
			<h2>Rincian persediaan</h2>
			<p id="wip-table-help">Berat dalam kg · Geser tabel untuk melihat seluruh kolom.</p>
		</div>
		<div id="wip-error" class="wip-error" role="alert" hidden>
			<span>Data stok gagal dimuat. Coba muat ulang data.</span>
			<button class="btn-stock btn-stock-reset" type="button" id="wip-retry">Coba lagi</button>
		</div>
		<div id="wip-table-host" data-stock-date-host="true" data-prefix="wip" data-warehouse="2" data-title="Stock WIP" data-source="<?= htmlspecialchars(site_url('stock_material/data_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>" data-export-url="<?= htmlspecialchars(site_url('stock_material/export_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>">
			<table id="example1" class="stock-table" aria-label="Persediaan WIP per lot" aria-describedby="wip-table-help">
				<thead><tr>
					<th scope="col">No.</th>
					<th scope="col" class="no-sort">ID material</th>
					<th scope="col" class="no-sort">Lot number</th>
					<th scope="col" class="no-sort">Nama material</th>
					<th scope="col" class="no-sort">Supplier</th>
					<th scope="col" class="no-sort text-right">Width <span>(mm)</span></th>
					<th scope="col" class="no-sort text-right">Jumlah item</th>
					<th scope="col" class="no-sort text-right">Berat satuan <span>(kg)</span></th>
					<th scope="col" class="no-sort text-right">Berat total <span>(kg)</span></th>
					<th scope="col" class="no-sort">Keterangan</th>
					<th scope="col" class="no-sort">Gudang</th>
					<th scope="col" class="no-sort">Customer</th>
					<th scope="col" class="no-sort">Tanggal Stok</th>
				</tr></thead>
				<tbody></tbody>
				<tfoot><tr>
					<th colspan="8" class="wip-total-label">Total berat hasil filter <span>Seluruh halaman</span></th>
					<th class="total-value" id="wip-footer-weight">-</th>
					<th colspan="4"></th>
				</tr></tfoot>
			</table>
		</div>
	</section>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/stock_material_date.js') ?>"></script>
