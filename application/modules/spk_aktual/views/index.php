<?php
$ENABLE_ADD     = has_permission('Spk_produksi_aktual.Add');
$ENABLE_MANAGE  = has_permission('Spk_produksi_aktual.Manage');
$ENABLE_VIEW    = has_permission('Spk_produksi_aktual.View');
$ENABLE_DELETE  = has_permission('Spk_produksi_aktual.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.min.css">

<div class="nav-tabs-supplier">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#proces" data-toggle="tab" aria-expanded="true">List Produksi</a></li>
		<li><a href="#history" data-toggle="tab" aria-expanded="true">History Produksi</a></li>
	</ul>
</div>

<div class="tab-content">
	<div class="tab-pane active" id="proces">
		<div class="box">
			<div class="box-body">
				<table id="example2" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>No SPK Produksi</th>
							<th>Customer</th>
							<th>Nama Material</th>
							<th>Tanggal Produksi</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="tab-pane" id="history">
		<div class="box">
			<div class="box-body">
				<table id="exampleoo" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>No SPK Produksi</th>
							<th>Customer</th>
							<th>Product</th>
							<th>Tanggal Produksi</th>
							<th>Tanggal Input</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>
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
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;SPK AKTUAL</h4>
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

<form action="#" method="POST" id="form_proses">
	<div class="modal fade" id="ModalView2">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id='head_title'>Default Modal</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="view">
					<div class='form-group row'>
						<div class='col-sm-12'>
							<label for="tahunx">Alasan Reject <span class='text-red'>*</span></label>
							<input type="hidden" id='id_spkproduksi' name='id_spkproduksi' class='form-control input-sm'>
							<textarea name="reason_reject" id="reason_reject" class='form-control input-sm' rows="3"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer text-right">
					<button type="button" class="btn btn-success" id='proccess_reject'><i class="fa fa-save"></i> Process</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
</form>
<!-- /.modal-dialog -->




<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).on('click', '.reject', function(e) {
		e.preventDefault();
		let id_spkproduksi = $(this).data('id_spkproduksi');
		$('#id_spkproduksi').val(id_spkproduksi)
		$("#head_title").html("Reject LHP");
		$("#ModalView2").modal();
	});

	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'spk_marketing/EditHeader/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.cetak', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'xtes/cetak' + id,
			success: function(data) {


			}
		})
	});

	// DELETE DATA
	$(document).on('click', '#proccess_reject', function(e) {
		e.preventDefault()
		var id = $('#id_spkproduksi').val();
		var reason_reject = $('#reason_reject').val();
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Kembalikan ke SPK Produksi",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'spk_aktual/RejectToProduksi',
					dataType: "json",
					data: {
						'id': id,
						'reason_reject': reason_reject
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: result.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: result.pesan,
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						})
					}
				})
			});

	})

	//APPROVE
	$(document).on('click', '.approve', function(e) {
		e.preventDefault()
		//var id = $('#id_spkproduksi').val();
		// alert(id);
		var id = $(this).data('id_spkproduksi');
		swal({
				title: "Anda Yakin?",
				text: "Approve LHP Produksi",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'spk_aktual/ApproveLHPProduksi',
					dataType: "json",
					data: {
						'id': id,
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: result.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: result.pesan,
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						})
					}
				})
			});

	})

	$(function() {

		$("#form-area").hide();
	});

	$(function() {
		DataTables();
		DataTables_history();
		$("#form-area").hide();
	});

	function DataTables() {
		var DataTables = $('#example2').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_list_produksi',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_spk_produksi'
				},
				{
					data: 'customer'
				},
				{
					data: 'nama_material'
				},
				{
					data: 'tanggal_produksi'
				},
				{
					data: 'action'
				}
			]
		});
	}

	function DataTables_history() {
		var DataTables = $('#exampleoo').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_history_produksi',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'kode'
				},
				{
					data: 'customer'
				},
				{
					data: 'product'
				},
				{
					data: 'tanggal_produksi'
				},
				{
					data: 'tanggal_input'
				},
				{
					data: 'action'
				}
			]
		});
	}


	//Delete

	function PreviewPdf(id) {
		param = id;
		tujuan = 'customer/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'customer/rekap_pdf';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>