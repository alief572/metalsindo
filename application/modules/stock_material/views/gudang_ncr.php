<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_warehouse.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material_date.css') ?>">

<div class="stock-material-container stock-dated-wrapper stock-warehouse-wrapper">
	<section class="stock-panel" aria-labelledby="warehouse-title">
		<header class="stock-header">
			<div>
				<h1 class="stock-header-title" id="warehouse-title">Stock Material NCR</h1>
				<p class="stock-header-desc">Persediaan material NCR per lot di Gudang NCR.</p>
			</div>
			<dl class="warehouse-summary" aria-live="polite">
				<div><dt>Berat total hasil filter</dt><dd><span id="warehouse-total-weight">Memuat…</span> <small>kg</small></dd></div>
				<div><dt>Baris stok hasil filter</dt><dd id="warehouse-total-rows">Memuat…</dd></div>
			</dl>
		</header>
		<form id="warehouse-filter-form" class="warehouse-toolbar" role="search">
			<?php $this->load->view('stock_material/date_filter',array('date_prefix'=>'warehouse')); ?>
			<div class="stock-form-group warehouse-search">
				<label class="stock-label" for="warehouse-search">Cari persediaan</label>
				<input class="stock-input" type="search" id="warehouse-search" placeholder="Kode material, nama, lot, dokumen, atau customer" autocomplete="off">
			</div>
			<div class="stock-date-actions">
				<button class="btn-stock btn-stock-primary" type="button" id="warehouse-date-apply">Terapkan</button>
				<button class="btn-stock btn-stock-reset" type="button" id="warehouse-reset">Reset filter</button>
				<button class="btn-stock btn-stock-export" type="button" id="warehouse-export" disabled>Export Excel</button>
			</div>
		</form>
		<p id="warehouse-snapshot-status" class="stock-date-status" role="status" aria-live="polite"></p>
		<div class="warehouse-table-heading">
			<h2>Rincian persediaan</h2>
			<p id="warehouse-table-help">Berat dalam kg · Geser tabel untuk melihat seluruh kolom.</p>
		</div>
		<div id="warehouse-error" class="warehouse-error" role="alert" hidden>
			<span>Data stok gagal dimuat. Coba muat ulang data.</span>
			<button class="btn-stock btn-stock-reset" type="button" id="warehouse-retry">Coba lagi</button>
		</div>
		<div id="warehouse-table-host" data-stock-date-host="true" data-prefix="warehouse" data-warehouse="6" data-title="Stock NCR" data-source="<?= htmlspecialchars(site_url('stock_material/data_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>" data-export-url="<?= htmlspecialchars(site_url('stock_material/export_warehouse_date'), ENT_QUOTES, 'UTF-8') ?>">
			<table id="example1" class="stock-table" aria-label="Stock Material NCR" aria-describedby="warehouse-table-help">
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
					<th colspan="8" class="warehouse-total-label">Total berat hasil filter <span>Seluruh halaman</span></th>
					<th class="total-value" id="warehouse-footer-weight">-</th>
					<th colspan="4"></th>
				</tr></tfoot>
			</table>
		</div>
	</section>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/stock_material_date.js') ?>"></script>
