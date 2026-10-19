<?php
$ENABLE_ADD     = has_permission('Warehouse_material.Add');
$ENABLE_MANAGE  = has_permission('Warehouse_material.Manage');
$ENABLE_VIEW    = has_permission('Warehouse_material.View');
$ENABLE_DELETE  = has_permission('Warehouse_material.Delete');
$id_bentuk = $this->uri->segment(3);
$stock_today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$stock_today = $stock_today->format('Y-m-d');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/stock_material.css') ?>">

<div id="alert_edit" class="alert alert-success alert-dismissable" style="display: none;"></div>

<div class="stock-material-container">
	<div class="stock-panel">
		<!-- Header & Warehouse Meta Summary -->
		<div class="stock-header">
			<div>
				<h1 class="stock-header-title">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
					</svg>
					Stock Raw Material
				</h1>
				<p class="stock-header-desc">
					Monitoring stok material mentah (Coil/Sheet) aktif pada Gudang Raw Material (Gudang 1).
				</p>
			</div>
			<div class="stock-meta-chips">
				<div class="stock-chip">
					<span class="stock-chip-label">Lokasi:</span>
					<span class="stock-chip-value">GUDANG RAW MATERIAL</span>
				</div>
				<div class="stock-chip highlight">
					<span class="stock-chip-label">Total Berat:</span>
					<span class="stock-chip-value" id="stat-total-berat">Memuat...</span>
				</div>
				<div class="stock-chip">
					<span class="stock-chip-label">Total Lot:</span>
					<span class="stock-chip-value" id="stat-total-lot">Memuat...</span>
				</div>
			</div>
		</div>

		<!-- Filter Toolbar -->
		<div class="stock-toolbar">
			<form id="stock-filter-form" class="stock-filters-form" onsubmit="return false;">
				<div class="stock-form-group date-group">
					<label for="stock-date" class="stock-label">Tanggal Stok</label>
					<input type="date" id="stock-date" class="stock-input" value="<?= $stock_today ?>" max="<?= $stock_today ?>" required>
				</div>
				<div class="stock-form-group width-group">
					<label for="series" class="stock-label">Filter Width</label>
					<input type="text" id="series" name="width" class="stock-input" placeholder="Cth: 400.00" autocomplete="off">
					<input type="hidden" id="komponen" name="panjang" value="">
				</div>
				<div class="stock-form-group search-group">
					<label for="stock-live-search" class="stock-label">Pencarian Cepat</label>
					<div class="stock-input-wrapper">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<circle cx="11" cy="11" r="8"/>
							<path d="M21 21l-4.35-4.35"/>
						</svg>
						<input type="search" id="stock-live-search" class="stock-input" placeholder="Cari Lot, Material, Supplier, Customer..." autocomplete="off">
					</div>
				</div>
				<div class="stock-actions">
					<button type="button" id="btn-apply-date" class="btn-stock btn-stock-primary">Terapkan</button>
					<button type="button" id="btn-reset-filter" class="btn-stock btn-stock-reset" title="Kembalikan semua filter ke awal">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
							<path d="M3 3v5h5"/>
						</svg>
						Reset
					</button>
					<button type="button" id="btn-export-excel" class="btn-stock btn-stock-export" title="Ekspor data ke file Excel">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
							<polyline points="14 2 14 8 20 8"/>
							<line x1="8" y1="13" x2="16" y2="13"/>
							<line x1="8" y1="17" x2="16" y2="17"/>
							<polyline points="10 9 9 9 8 9"/>
						</svg>
						Export Excel
					</button>
				</div>
			</form>
		</div>
		<p id="stock-snapshot-status" role="status" aria-live="polite"></p>
		<p id="stock-filter-error" class="stock-filter-error" role="alert" hidden></p>

		<!-- Table Region -->
		<div class="stock-table-region" tabindex="0" role="region" aria-label="Tabel Stock Raw Material">
			<table id="example1" class="stock-table table-striped">
				<thead>
					<tr>
						<th class="text-center" style="width: 45px;">#</th>
						<th class="no-sort text-center">Id Material</th>
						<th class="no-sort text-center">Lot Number</th>
						<th class="no-sort text-center">Nama Material</th>
						<th class="no-sort text-center">Supplier</th>
						<th class="no-sort text-right">Width</th>
						<th class="no-sort text-right">Jumlah Item</th>
						<th class="no-sort text-right">Berat Satuan (kg)</th>
						<th class="no-sort text-right">Berat Total (kg)</th>
						<th class="no-sort text-center">Keterangan</th>
						<th class="no-sort text-center">Gudang</th>
						<th class="no-sort text-center">Customer</th>
						<th class="no-sort text-center">Tanggal Stok</th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot>
					<tr>
						<th colspan="7" class="total-label">TOTAL KESELURUHAN</th>
						<th></th>
						<th class="total-value"></th>
						<th colspan="4"></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<!-- DataTables & Plugins -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		var total_aset = 0;
		var today = <?= json_encode($stock_today) ?>;
		var appliedDate = today;
		var snapshotAvailable = false;
		function showError(message) {
			$('#stock-filter-error').text(message).prop('hidden', !message);
		}

		var dataTable = $('#example1').DataTable({
			"serverSide": true,
			"stateSave": true,
			"stateLoadParams": function(settings, data) {
				if (!data.columns || data.columns.length !== 13) return false;
			},
			"initComplete": function() {
				$('#stock-live-search').val(this.api().search());
			},
			"bAutoWidth": false,
			"destroy": true,
			"responsive": true,
			"searching": true,
			"oLanguage": {
				"sLengthMenu": "Tampilkan _MENU_ baris",
				"sInfo": "Menampilkan <b>_START_</b>–<b>_END_</b> dari <b>_TOTAL_</b> data",
				"sInfoFiltered": "(difilter dari total _MAX_ baris)",
				"sZeroRecords": '<div class="stock-empty-box"><div class="stock-empty-title">Tidak ada stok yang cocok</div><p class="stock-empty-desc">Coba ubah tanggal, width, pencarian, atau klik Reset.</p></div>',
				"sEmptyTable": '<div class="stock-empty-box"><svg viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><div class="stock-empty-title">Belum ada stok material</div><p class="stock-empty-desc">Saat ini belum ada data stok tercatat pada Gudang Raw Material.</p></div>',
				"sLoadingRecords": "Memuat data stok...",
				"sProcessing": "Memuat data...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
			"aaSorting": [
				[0, "asc"]
			],
			"columnDefs": [
				{
					"targets": 'no-sort',
					"orderable": false
				},
				{
					"targets": 0,
					"className": 'text-center'
				},
				{
					"targets": 1,
					"className": 'col-id-material'
				},
				{
					"targets": 2,
					"className": 'col-lot'
				},
				{
					"targets": [5, 6, 7],
					"className": 'col-numeric'
				},
				{
					"targets": 8,
					"className": 'col-total-weight'
				},
				{
					"targets": 9,
					"className": 'col-keterangan text-center'
				}
			],
			"dom": 't<"stock-bottom-bar"<"stock-bottom-left"li>p>',
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 25, 50, 100, 250],
				[10, 25, 50, 100, 250]
			],
			"ajax": {
				"url": siteurl + 'stock_material/data_side_material_grw',
				"type": "post",
				"data": function(d) {
					d.gudang = '1';
					d.series = $('#series').val();
					d.komponen = $('#komponen').val();
					d.stock_date = appliedDate;
					snapshotAvailable = false;
					$('#btn-export-excel').prop('disabled', true);
				},
				"cache": false,
				"error": function(xhr) {
					showError(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Gagal memuat stok. Klik Terapkan untuk mencoba lagi.');
					$('#stat-total-berat, #stat-total-lot').text('Tidak tersedia');
					$('#stock-snapshot-status').text('Data stok gagal diperbarui.');
					$('#example1 tbody').empty().append($('<tr>').append($('<td>').attr('colspan', 13).text('Gagal memuat data stok. Klik Terapkan untuk mencoba lagi.')));
					$('#example1 tfoot .total-value').text('Tidak tersedia');
				},
				"dataSrc": function(data) {
					if (data.draw < dataTable.settings()[0].iDraw) return data.data;
					showError('');
					snapshotAvailable = data.available;
					$('#btn-export-excel').prop('disabled', !snapshotAvailable);
					$('#stock-snapshot-status').text(!data.available ? 'Snapshot stok tanggal ' + data.stock_date + ' belum tersedia.' :
						'Tanggal stok: ' + data.stock_date + (data.snapshot_at ? ' | Snapshot: ' + data.snapshot_at + ' WIB' : ' | Stok aktif saat ini'));
					dataTable.settings()[0].oLanguage.sEmptyTable = !data.available ? 'Snapshot stok tanggal ' + data.stock_date + ' belum tersedia.' :
						'Tidak ada stok yang cocok pada tanggal ini. Coba ubah tanggal, width, pencarian, atau Reset filter.';
					total_aset = data.recordsAset || 0;
					var recordsCount = (typeof data.recordsFiltered !== 'undefined') ? data.recordsFiltered : (data.data ? data.data.length : 0);
					$('#stat-total-berat').text(number_format(total_aset, 2) + ' kg');
					$('#stat-total-lot').text(number_format(recordsCount, 0) + ' lot');
					return data.data;
				}
			},
			"drawCallback": function(settings) {
				var api = this.api();
				$(api.column(8).footer()).html("<div align='right'>" + number_format(total_aset, 2) + "</div>");

				// Wrap status / keterangan with badge style
				$('#example1 tbody tr').each(function() {
					var $ketCell = $(this).find('td').eq(9);
					var text = $.trim($ketCell.text());
					if (text && !$ketCell.find('.stock-badge').length) {
						$ketCell.empty().append($('<span>').addClass('stock-badge').text(text));
					}
				});
			}
		});

		// Debounce filter inputs for responsive, lag-free live search
		var searchTimer;
		$('#stock-live-search').on('input keyup', function() {
			clearTimeout(searchTimer);
			var query = $(this).val();
			searchTimer = setTimeout(function() {
				dataTable.search(query).draw();
			}, 300);
		});

		var widthTimer;
		$('#series').on('input keyup', function() {
			clearTimeout(widthTimer);
			widthTimer = setTimeout(function() {
				dataTable.ajax.reload();
			}, 300);
		});

		// Reset Button
		$('#btn-apply-date').on('click', function() {
			var input = document.getElementById('stock-date');
			if (!input.checkValidity()) {
				showError('Pilih tanggal stok yang valid dan tidak melebihi hari ini.');
				input.focus();
				return;
			}
			appliedDate = input.value;
			showError('');
			dataTable.ajax.reload();
		});
		$('#btn-reset-filter').on('click', function(e) {
			e.preventDefault();
			clearTimeout(searchTimer);
			clearTimeout(widthTimer);
			$('#series').val('');
			$('#stock-live-search').val('');
			$('#stock-date').val(today);
			appliedDate = today;
			showError('');
			dataTable.search('').ajax.reload();
		});

		// Export Excel Trigger
		$('#btn-export-excel').on('click', function(e) {
			e.preventDefault();
			if (!snapshotAvailable) return;
			var params = {stock_date: appliedDate, series: $('#series').val(), search: {value: dataTable.search()}, direction: dataTable.order()[0][1]};
			window.location.href = siteurl + 'stock_material/export_raw_material?' + $.param(params);
		});
	});

	function number_format(number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>
