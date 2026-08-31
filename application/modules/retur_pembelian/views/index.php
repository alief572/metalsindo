<?php
$ENABLE_ADD     = has_permission('Input Retur Pembelian.Add');
$ENABLE_MANAGE  = has_permission('Input Retur Pembelian.Manage');
$ENABLE_VIEW    = has_permission('Input Retur Pembelian.View');
$ENABLE_DELETE  = has_permission('Input Retur Pembelian.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
<style type="text/css">
	.table-retur thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		vertical-align: middle !important;
		font-weight: 600;
	}
	.table-retur tbody td {
		vertical-align: middle !important;
	}
	.btn-action-group .btn {
		margin-right: 3px;
		border-radius: 4px;
	}
	.badge-status {
		font-size: 11px;
		padding: 5px 10px;
		border-radius: 12px;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-undo"></i> Daftar Retur Pembelian</h3>
		<div class="box-tools pull-right">
			<?php if ($ENABLE_ADD) : ?>
				<a href="<?= base_url('retur_pembelian/add') ?>" class="btn btn-sm btn-success btn-flat">
					<i class="fa fa-plus-circle"></i> Tambah Retur
				</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="box-body">
		<div class="table-responsive">
			<table id="table_retur_pembelian" class="table table-bordered table-striped table-hover table-retur" style="width: 100%;">
				<thead>
					<tr>
						<th class="text-center" style="width: 4%;">#</th>
						<th class="text-center" style="width: 13%;">No. Retur</th>
						<th class="text-center" style="width: 13%;">No. Invoice / PO</th>
						<th class="text-center" style="width: 18%;">Nama Supplier</th>
						<th class="text-center" style="width: 11%;">Tgl Retur</th>
						<th class="text-center" style="width: 13%;">No. Ref Invoice</th>
						<th class="text-center" style="width: 11%;">Tgl Invoice</th>
						<th class="text-center" style="width: 8%;">Status</th>
						<th class="text-center" style="width: 9%;">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
	});

	$(document).on('click', '.del_retur', function() {
		var id = $(this).data('id');

		Swal.fire({
			icon: 'warning',
			title: 'Anda yakin?',
			text: 'Data retur ini akan dihapus!',
			showConfirmButton: true,
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
			cancelButtonText: 'Batal',
			allowEscapeKey: false,
			allowOutsideClick: false
		}).then((next) => {
			if (next.isConfirmed) {
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'del_retur',
					data: {
						'id': id
					},
					cache: false,
					dataType: 'json',
					success: function(result) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil!',
							text: result.msg,
							showConfirmButton: false,
							timer: 2000
						}).then(() => {
							DataTables();
						});
					},
					error: function(xhr, status, error) {
						var response = xhr.responseText;
						var message = 'Terjadi kesalahan sistem.';

						try {
							var data = JSON.parse(response);
							if (data.msg) {
								message = data.msg;
							}
						} catch (e) {
							console.error("Gagal parse JSON error:", e);
						}

						Swal.fire({
							icon: 'error',
							title: 'Gagal!',
							text: message
						});
					}
				});
			}
		});
	});

	function DataTables() {
		$('#table_retur_pembelian').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			autoWidth: false,
			ajax: {
				url: siteurl + active_controller + 'get_datatable_retur',
				type: 'GET',
				cache: false,
				dataType: 'json',
				error: function(xhr, error, code) {
					console.log("DataTable Error: ", xhr.responseText);
				}
			},
			columns: [
				{
					data: 'no',
					sClass: 'text-center',
					width: '4%'
				},
				{
					data: 'no_retur',
					sClass: 'text-bold'
				},
				{
					data: 'no_po'
				},
				{
					data: 'nama_supplier'
				},
				{
					data: 'tanggal_retur',
					sClass: 'text-center'
				},
				{
					data: 'no_ref_invoice'
				},
				{
					data: 'tanggal_invoice',
					sClass: 'text-center'
				},
				{
					data: 'status',
					sClass: 'text-center'
				},
				{
					data: 'action',
					sClass: 'text-center btn-action-group',
					orderable: false,
					searchable: false
				}
			],
			order: [
				[1, 'desc']
			],
			drawCallback: function(settings) {
				$('[data-toggle="tooltip"]').tooltip();
			}
		});
	}
</script>