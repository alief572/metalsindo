<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_fg.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_date.css') ?>">

<div class="stock-material-container stock-dated-wrapper stock-fg-wrapper">
	<section class="stock-panel" aria-labelledby="fg-title">
		<header class="stock-header">
			<div>
				<h1 class="stock-header-title" id="fg-title">Stock Finish Good</h1>
				<p class="stock-header-desc">Persediaan barang jadi per lot di Gudang Finish Good.</p>
			</div>
			<dl class="fg-summary" aria-live="polite">
				<div><dt>Berat total hasil filter</dt><dd><span id="fg-total-weight">Memuat…</span> <small>kg</small></dd></div>
				<div><dt>Baris stok hasil filter</dt><dd id="fg-total-rows">Memuat…</dd></div>
			</dl>
		</header>
		<form id="fg-filter-form" class="fg-toolbar" role="search">
			<?php $this->load->view('stock_material/date_filter',array('date_prefix'=>'fg')); ?>
			<div class="stock-form-group fg-search">
				<label class="stock-label" for="fg-search">Cari persediaan</label>
				<input class="stock-input" type="search" id="fg-search" placeholder="Kode material, nama, lot, dokumen, atau customer" autocomplete="off">
			</div>
			<div class="stock-form-group fg-width">
				<label class="stock-label" for="series">Lebar / width (mm)</label>
				<input class="stock-input" type="number" id="series" name="width" min="0" step="any" placeholder="Semua lebar" inputmode="decimal">
			</div>
			<div class="stock-date-actions">
				<button class="btn-stock btn-stock-primary" type="button" id="fg-date-apply">Terapkan</button>
				<button class="btn-stock btn-stock-reset" type="button" id="fg-reset">Reset filter</button>
				<button class="btn-stock btn-stock-export" type="button" id="fg-export" disabled>Export Excel</button>
			</div>
		</form>
		<p id="fg-snapshot-status" class="stock-date-status" role="status" aria-live="polite"></p>
		<div class="fg-table-heading">
			<h2>Rincian persediaan</h2>
			<p id="fg-table-help">Berat dalam kg · Geser tabel untuk melihat seluruh kolom.</p>
		</div>
		<div id="fg-error" class="fg-error" role="alert" hidden>
			<span>Data stok gagal dimuat. Coba muat ulang data.</span>
			<button class="btn-stock btn-stock-reset" type="button" id="fg-retry">Coba lagi</button>
		</div>
		<div id="fg-table-host" data-stock-date-host="true" data-prefix="fg" data-warehouse="3" data-title="Stock Finish Good" data-source="<?= htmlspecialchars(site_url('stock_material/data_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>" data-export-url="<?= htmlspecialchars(site_url('stock_material/export_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>">
			<table id="example1" class="stock-table" aria-label="Persediaan Finish Good per lot" aria-describedby="fg-table-help">
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
					<th scope="col" class="no-sort text-right">Total sheet</th>
					<th scope="col" class="no-sort">Keterangan</th>
					<th scope="col" class="no-sort">Gudang</th>
					<th scope="col" class="no-sort">Customer</th>
					<th scope="col" class="no-sort">Tanggal Stok</th>
				</tr></thead>
				<tbody></tbody>
				<tfoot><tr>
					<th colspan="8" class="fg-total-label">Total berat hasil filter <span>Seluruh halaman</span></th>
					<th class="total-value" id="fg-footer-weight">-</th>
					<th colspan="5"></th>
				</tr></tfoot>
			</table>
		</div>
	</section>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/stock_material_date.js') ?>"></script>
