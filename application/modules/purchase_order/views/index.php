<?php
$ENABLE_ADD     = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Order.View');
$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');

$total_po       = isset($stats['total_po']) ? $stats['total_po'] : 0;
$count_waiting  = isset($stats['count_waiting']) ? $stats['count_waiting'] : 0;
$count_approved = isset($stats['count_approved']) ? $stats['count_approved'] : 0;
$count_closed   = isset($stats['count_closed']) ? $stats['count_closed'] : 0;
?>

<!-- Modern DataTables 2.3.2 CSS with Bootstrap Integration -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap-2.3.2.min.css'); ?>">

<style type="text/css">
	/* KPI Stat Cards */
	.po-kpi-card {
		background: #ffffff;
		border-radius: 8px;
		padding: 16px 20px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
		border: 1px solid #e2e8f0;
		margin-bottom: 20px;
		display: flex;
		align-items: center;
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}
	.po-kpi-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
	}
	.po-kpi-icon {
		width: 48px;
		height: 48px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 22px;
		margin-right: 16px;
		flex-shrink: 0;
	}
	.kpi-blue { background: #e0f2fe; color: #0284c7; }
	.kpi-amber { background: #fef3c7; color: #d97706; }
	.kpi-emerald { background: #d1fae5; color: #059669; }
	.kpi-slate { background: #f1f5f9; color: #64748b; }
	.po-kpi-info h4 { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1.2; }
	.po-kpi-info p { margin: 3px 0 0 0; font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

	/* Container & Table Styling */
	.po-card {
		background: #ffffff;
		border-radius: 8px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
		border: 1px solid #e2e8f0;
		margin-bottom: 30px;
	}
	.po-card-header {
		padding: 16px 20px;
		background: #ffffff;
		border-bottom: 1px solid #f1f5f9;
		display: flex;
		justify-content: space-between;
		align-items: center;
		border-radius: 8px 8px 0 0;
	}
	.po-card-header h3 {
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: #1e293b;
	}
	.po-card-body {
		padding: 20px;
	}
	#example2 thead th {
		background: #205072 !important;
		color: #ffffff !important;
		font-weight: 600 !important;
		font-size: 12px !important;
		border: none !important;
		padding: 12px 10px !important;
		vertical-align: middle !important;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}
	#example2 tbody td {
		vertical-align: middle !important;
		font-size: 13px;
		padding: 10px !important;
	}
	#example2 tbody tr:hover {
		background-color: #f8fafc !important;
	}

	/* Modern DataTables 2 Layout & Elements */
	.dt-container {
		font-size: 13px;
	}
	.dt-container div.dt-layout-row {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		margin-bottom: 16px !important;
		flex-wrap: wrap;
		gap: 12px;
	}
	.dt-container div.dt-layout-row:last-child {
		margin-top: 16px !important;
		margin-bottom: 0 !important;
		padding-top: 12px;
		border-top: 1px solid #f1f5f9;
	}
	.dt-container div.dt-length label,
	div.dataTables_length label {
		font-weight: 500;
		color: #475569;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		margin: 0;
	}
	.dt-container div.dt-length select,
	div.dataTables_length select {
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		padding: 6px 12px !important;
		background-color: #ffffff !important;
		color: #334155 !important;
		font-size: 13px !important;
		outline: none !important;
		transition: border-color 0.2s ease, box-shadow 0.2s ease;
	}
	.dt-container div.dt-length select:focus,
	div.dataTables_length select:focus {
		border-color: #205072 !important;
		box-shadow: 0 0 0 3px rgba(32, 80, 114, 0.12) !important;
	}
	.dt-container div.dt-search label,
	div.dataTables_filter label {
		font-weight: 500;
		color: #475569;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		margin: 0;
	}
	.dt-container div.dt-search input,
	div.dataTables_filter input {
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		padding: 7px 14px !important;
		font-size: 13px !important;
		width: 260px !important;
		background-color: #ffffff !important;
		color: #334155 !important;
		outline: none !important;
		transition: all 0.2s ease;
		box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
	}
	.dt-container div.dt-search input:focus,
	div.dataTables_filter input:focus {
		border-color: #205072 !important;
		box-shadow: 0 0 0 3px rgba(32, 80, 114, 0.15) !important;
		width: 290px !important;
	}
	.dt-container div.dt-info,
	div.dataTables_info {
		color: #64748b;
		font-size: 13px;
		padding: 6px 0;
	}
	.dt-container div.dt-paging ul.pagination,
	div.dataTables_paginate ul.pagination {
		margin: 0;
		display: flex;
		gap: 3px;
	}
	.dt-container div.dt-paging ul.pagination > li > a,
	div.dataTables_paginate ul.pagination > li > a {
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		color: #475569 !important;
		padding: 6px 12px !important;
		font-size: 12px !important;
		font-weight: 500 !important;
		background: #ffffff !important;
		transition: all 0.15s ease-in-out;
	}
	.dt-container div.dt-paging ul.pagination > li > a:hover,
	div.dataTables_paginate ul.pagination > li > a:hover {
		background: #f1f5f9 !important;
		border-color: #cbd5e1 !important;
		color: #0f172a !important;
	}
	.dt-container div.dt-paging ul.pagination > li.active > a,
	.dt-container div.dt-paging ul.pagination > li.active > a:focus,
	.dt-container div.dt-paging ul.pagination > li.active > a:hover,
	div.dataTables_paginate ul.pagination > li.active > a,
	div.dataTables_paginate ul.pagination > li.active > a:focus,
	div.dataTables_paginate ul.pagination > li.active > a:hover {
		background: #205072 !important;
		border-color: #205072 !important;
		color: #ffffff !important;
		font-weight: 600 !important;
		box-shadow: 0 2px 6px rgba(32, 80, 114, 0.25) !important;
	}
	.dt-container div.dt-paging ul.pagination > li.disabled > a,
	div.dataTables_paginate ul.pagination > li.disabled > a {
		color: #cbd5e1 !important;
		background: #f8fafc !important;
		border-color: #f1f5f9 !important;
	}
	.dt-container div.dt-processing,
	div.dataTables_processing {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: rgba(255, 255, 255, 0.96) !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 8px !important;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
		z-index: 1050;
		padding: 14px 26px !important;
	}

	/* Status Badge Pills */
	.label-status {
		display: inline-block;
		padding: 4px 10px;
		border-radius: 20px;
		font-size: 11px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.3px;
	}
	.label-waiting {
		background-color: #fef3c7;
		color: #b45309;
		border: 1px solid #fde68a;
	}
	.label-approved {
		background-color: #d1fae5;
		color: #047857;
		border: 1px solid #a7f3d0;
	}
	.label-closed {
		background-color: #f1f5f9;
		color: #475569;
		border: 1px solid #e2e8f0;
	}

	/* Action Buttons Group */
	.btn-group-action {
		white-space: nowrap;
		display: inline-flex;
		gap: 4px;
	}
	.btn-action {
		padding: 4px 8px !important;
		border-radius: 4px !important;
		font-size: 12px !important;
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
		transition: all 0.15s ease-in-out;
		border: none !important;
	}
	.btn-action:hover {
		transform: translateY(-1px);
		box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12);
	}
</style>

<!-- Alert placeholder -->
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<!-- 1. KPI Metric Cards -->
<div class="row">
	<div class="col-md-3 col-sm-6 col-xs-12">
		<div class="po-kpi-card">
			<div class="po-kpi-icon kpi-blue">
				<i class="fa fa-file-text-o"></i>
			</div>
			<div class="po-kpi-info">
				<h4><?= number_format($total_po) ?></h4>
				<p>Total Purchase Order</p>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12">
		<div class="po-kpi-card">
			<div class="po-kpi-icon kpi-amber">
				<i class="fa fa-clock-o"></i>
			</div>
			<div class="po-kpi-info">
				<h4><?= number_format($count_waiting) ?></h4>
				<p>Menunggu Approval</p>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12">
		<div class="po-kpi-card">
			<div class="po-kpi-icon kpi-emerald">
				<i class="fa fa-check-circle-o"></i>
			</div>
			<div class="po-kpi-info">
				<h4><?= number_format($count_approved) ?></h4>
				<p>PO Disetujui (Approved)</p>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12">
		<div class="po-kpi-card">
			<div class="po-kpi-icon kpi-slate">
				<i class="fa fa-archive"></i>
			</div>
			<div class="po-kpi-info">
				<h4><?= number_format($count_closed) ?></h4>
				<p>PO Selesai / Closed</p>
			</div>
		</div>
	</div>
</div>

<!-- 2. Main Data Table Card -->
<div class="po-card">
	<div class="po-card-header">
		<h3><i class="fa fa-list-alt" style="color: #205072; margin-right: 8px;"></i>Daftar Purchase Order</h3>
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#dialog-select-tipe" style="border-radius: 4px; font-weight: 600; padding: 6px 14px;">
					<i class="fa fa-plus"></i>&nbsp; Create PO
				</button>
			<?php endif; ?>
		</div>
	</div>
	<div class="po-card-body">
		<div class="table-responsive">
			<table id="example2" class="table table-bordered table-striped" width="100%">
				<thead>
					<tr>
						<th width="30" class="text-center">#</th>
						<th>No PO</th>
						<th>Tanggal PO</th>
						<th>Supplier</th>
						<th width="110" class="text-center">Status PO</th>
						<th width="140" class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal Pilih Bentuk Material untuk PO Baru -->
<div class="modal fade" id="dialog-select-tipe" tabindex="-1" role="dialog" aria-labelledby="modalSelectTipeLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
			<div class="modal-header" style="background: #205072; color: #ffffff; padding: 15px 20px;">
				<button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.8;">&times;</button>
				<h4 class="modal-title" id="modalSelectTipeLabel" style="font-weight: 600; font-size: 16px;">
					<i class="fa fa-cubes"></i>&nbsp; Buat Purchase Order Baru
				</h4>
			</div>
			<div class="modal-body" style="padding: 24px;">
				<p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">
					Silakan pilih bentuk material yang akan dipesan untuk menentukan format formulir PO:
				</p>
				<div class="form-group">
					<label for="tipe_pr_select" style="font-weight: 600; color: #334155; margin-bottom: 8px;">Bentuk Material <span class="text-danger">*</span></label>
					<select class="form-control input-md tipe_pr" id="tipe_pr_select" style="height: 42px; border-radius: 4px;">
						<option value="">-- Pilih Bentuk Material --</option>
						<?php
						foreach ($list_bentuk as $item) :
							echo '<option value="' . $item['id_bentuk'] . '">' . strtoupper($item['nm_bentuk']) . '</option>';
						endforeach;
						?>
					</select>
				</div>
			</div>
			<div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
				<button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
					<i class="fa fa-times"></i> Batal
				</button>
				<button type="button" class="btn btn-success add_po" style="border-radius: 4px; font-weight: 600;">
					<i class="fa fa-arrow-right"></i> Lanjutkan Proses
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal View Popup -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="dialogPopupLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 92%;">
		<div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
			<div class="modal-header" style="background: #205072; color: #ffffff; padding: 14px 20px;">
				<button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.8;">&times;</button>
				<h4 class="modal-title" id="head_title" style="font-weight: 600; font-size: 16px;">
					<i class="fa fa-file-text-o"></i>&nbsp; Detail Purchase Order
				</h4>
			</div>
			<div class="modal-body" id="ModalView" style="padding: 0; background: #f8fafc; max-height: 80vh; overflow-y: auto;">
				<!-- Content dynamically loaded via AJAX -->
			</div>
		</div>
	</div>
</div>

<!-- Modern DataTables 2.3.2 & Helper Scripts -->
<script src="<?= base_url('assets/plugins/datatables/dataTables-2.3.2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap-2.3.2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
	});

	// View Detail PO
	$(document).on('click', '.view', function() {
		var id = $(this).data('no_po');
		$("#head_title").html("<i class='fa fa-file-text-o'></i>&nbsp; <b>Detail Purchase Order: " + id + "</b>");
		$("#ModalView").html('<div style="text-align:center; padding: 40px;"><i class="fa fa-spinner fa-spin fa-2x" style="color:#205072;"></i><p style="margin-top:10px; color:#64748b;">Memuat data Purchase Order...</p></div>');
		$("#dialog-popup").modal('show');
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_order/Lihat/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#ModalView").html(data);
			},
			error: function() {
				$("#ModalView").html('<div class="alert alert-danger" style="margin:20px;">Gagal memuat detail data Purchase Order.</div>');
			}
		});
	});

	// Proses Lanjut Buat PO
	$(document).on('click', '.add_po', function() {
		var bentuk_material = $('.tipe_pr').val();

		if (bentuk_material == '') {
			swal({
				type: 'warning',
				title: 'Peringatan !',
				text: 'Silakan pilih bentuk material terlebih dahulu!',
				showConfirmButton: false,
				timer: 2500
			});
		} else {
			if (bentuk_material == 'B2000002') {
				window.location.href = siteurl + 'purchase_order/add_sheet';
			} else {
				window.location.href = siteurl + 'purchase_order/add';
			}
		}
	});

	// Approve PO
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault();
		var id = $(this).data('no_po');
		swal({
				title: "Anda Yakin?",
				text: "Purchase Order akan disetujui (Approve)!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_order/Approved',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Purchase Order berhasil diapprove.",
									type: "success",
									timer: 2000,
									showConfirmButton: false
								},
								function() {
									window.location.reload(true);
								});
						} else {
							swal({
								title: "Error",
								text: result.pesan || "Gagal menyetujui Purchase Order.",
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Gagal memproses request ke server.",
							type: "error"
						});
					}
				});
			});
	});

	// Delete PO
	$(document).on('click', '.delete', function(e) {
		e.preventDefault();
		var id = $(this).data('no_po');
		swal({
				title: "Anda Yakin?",
				text: "Data Purchase Order ini akan dihapus permanen!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_order/delete_po',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: result.pesan || "Data PO berhasil dihapus.",
									type: "success",
									timer: 2000,
									showConfirmButton: false
								},
								function() {
									window.location.reload(true);
								});
						} else {
							swal({
								title: "Gagal",
								text: result.pesan || "Gagal menghapus data Purchase Order.",
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Gagal request ke server.",
							type: "error"
						});
					}
				});
			});
	});

	// Inisialisasi DataTable Versi 2 (Modern)
	function DataTables() {
		$('#example2').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			responsive: true,
			order: [[1, 'desc']],
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_po',
				dataType: 'json',
				cache: false
			},
			columns: [
				{ data: 'no', className: 'text-center', orderable: false },
				{ data: 'no_po' },
				{ data: 'tanggal_po' },
				{ data: 'supplier' },
				{ data: 'progress_pr', className: 'text-center' },
				{ data: 'action', className: 'text-center', orderable: false }
			],
			language: {
				processing: '<div style="padding: 8px 16px;"><i class="fa fa-spinner fa-spin fa-lg" style="color: #205072; margin-right: 8px;"></i> Memuat data...</div>',
				search: '<i class="fa fa-search" style="color: #64748b;"></i>',
				searchPlaceholder: 'Cari No. PO / Supplier...',
				lengthMenu: 'Tampilkan _MENU_ data',
				info: 'Menampilkan <b>_START_</b> - <b>_END_</b> dari <b>_TOTAL_</b> data',
				infoEmpty: 'Menampilkan 0 data',
				infoFiltered: '(disaring dari _MAX_ total data)',
				zeroRecords: '<div style="padding: 30px; color: #94a3b8; text-align: center;"><i class="fa fa-folder-open-o fa-2x" style="margin-bottom: 8px;"></i><br>Tidak ada data Purchase Order yang cocok</div>',
				paginate: {
					first: '<i class="fa fa-angle-double-left"></i>',
					previous: '<i class="fa fa-angle-left"></i>',
					next: '<i class="fa fa-angle-right"></i>',
					last: '<i class="fa fa-angle-double-right"></i>'
				}
			},
			drawCallback: function() {
				$('[data-toggle="tooltip"]').tooltip();
			}
		});
	}
</script>