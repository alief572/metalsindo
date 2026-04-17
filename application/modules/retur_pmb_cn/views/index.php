<?php
$ENABLE_ADD     = has_permission('Retur_Credit_Note.Add');
$ENABLE_MANAGE  = has_permission('Retur_Credit_Note.Manage');
$ENABLE_VIEW    = has_permission('Retur_Credit_Note.View');
$ENABLE_DELETE  = has_permission('Retur_Credit_Note.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-body">
		<a href="<?= base_url('retur_pmb_cn/list_retur') ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Add CN</a>
		<br><br>
		<table id="table_retur_dn" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">No. CN</th>
                    <th class="text-center">Nama Supplier</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Alasan Retur</th>
					<th class="text-center">Action</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style='width:70%;'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"></h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
	});

	function DataTables() {
		// 1. Simpan ke variabel supaya bisa dipanggil (misal: table.draw())
		var table = $('#table_retur_dn').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			autoWidth: false, // Tambahan: Biar layout gak berantakan pas loading

			ajax: {
				url: siteurl + active_controller + 'get_datatable_dn',
				type: 'GET',
				cache: false,
				dataType: 'json',
				// 2. Error Handling: Biar gak muncul alert "DataTables warning" yang ganggu user
				error: function(xhr, error, code) {
					console.log("DataTable Error: ", xhr.responseText);
					// Bisa tambahin notifikasi toastr di sini kalau mau
				}
			},

			// 3. Centralized Column Definitions
			columns: [{
					data: 'no',
					sClass: 'text-center',
					width: '5%'
				},
				{
					data: 'no_cn'
				},
				{
					data: 'nama_supplier'
				},
				{
					data: 'nomor_po'
				},
				{
					data: 'alasan_retur'
				},
				{
					data: 'action',
					sClass: 'text-center',
					orderable: false, // Tombol action gak perlu di-sorting
					searchable: false // Tombol action gak perlu di-search
				}
			],

			// 4. Default Sorting (Misal berdasarkan data terbaru)
			order: [
				[1, 'desc']
			],

			// 5. Callback setelah data berhasil di-load
			drawCallback: function(settings) {
				console.log('Table redrawn!');
				// Kalau lo pake tooltip Bootstrap di tombol action, init lagi di sini
				// $('[data-toggle="tooltip"]').tooltip();
			}
		});
	}
</script>