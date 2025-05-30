<?php
$ENABLE_ADD     = has_permission('Material_Planing.Add');
$ENABLE_MANAGE  = has_permission('Material_Planing.Manage');
$ENABLE_VIEW    = has_permission('Material_Planing.View');
$ENABLE_DELETE  = has_permission('Material_Planing.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-body">
		<div>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#mat1" class='mat1' aria-controls="mat1" role="tab" data-toggle="tab">Material Planning</a></li>
				<!--<li role="presentation" class=""><a href="#mat2" class='mat2' aria-controls="mat2" role="tab" data-toggle="tab">Closing SPK Produksi</a></li>-->
			</ul>

			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="mat1">
					<br>
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>No. SPK</th>
								<th>Custommer</th>
								<th>Kode Material</th>
								<th>No. Aloy</th>
								<th>Thickness</th>
								<th>Width</th>
								<th>Length</th>
								<th>Delivery Date</th>
								<th>Total Weight</th>
								<th>Total Sheet</th>
								<th>Total SPK</th>
								<th>FG</th>
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
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
	});
	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('id_dt_spkmarketing');
		var id_material = $(this).data('id_material');
		var width = $(this).data('width');
		var view = $(this).data('view');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'material_planing/EditHeader',
			data: {
				'id': id,
				'id_material': id_material,
				'width': width,
				'view': view
			},
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

	$(document).on('click', '.view', function() {
		var id = $(this).data('id_material');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'material_planing/ViewStock/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});



	// DELETE DATA
	$(document).on('click', '.delete', function(e) {
		e.preventDefault()
		var id = $(this).data('id_dt_spkmarketing');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Material Planing akan di approve.",
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
					url: siteurl + 'material_planing/Approve',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Inventory berhasil diapprove.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal approve data",
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



	// DELETE DATA
	$(document).on('click', '.tutup', function(e) {
		e.preventDefault()
		var id = $(this).data('id_dt_spkmarketing');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Material Planing akan di close.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Close!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'material_planing/Tutup',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Inventory berhasil diclose.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal close data",
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


	// DELETE DATA
	$(document).on('click', '.hapus', function(e) {
		e.preventDefault()
		var id = $(this).data('id_dt_spkmarketing');
		// alert(id); 
		swal({
				title: "Anda Yakin?",
				text: "Data Material Planing akan di Close.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Close!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'material_planing/Close',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Material Planing berhasil di close.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal hapus data",
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
		// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
		// $('#example1 thead tr:eq(1) th').each( function (i) {
		// var title = $(this).text();
		//alert(title);
		// if (title == "#" || title =="Action" ) {
		// $(this).html( '' );
		// }else{
		// $(this).html( '<input type="text" />' );
		// }

		// $( 'input', this ).on( 'keyup change', function () {
		// if ( table.column(i).search() !== this.value ) {
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }else{
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }
		// } );
		// } );



		var table = $('#example2').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});

		$("#form-area").hide();
	});


	//Delete

	function DataTables() {
		// var dataTables = $('#table_penawaran').dataTable();
		// dataTables.destroy();

		var dataTables = $('#example1').dataTable({
			ajax: {
				url: siteurl + active_controller + 'get_data_material_planning',
				type: "POST",
				dataType: "JSON",
				data: function(d) {

				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_spk'
				},
				{
					data: 'customer'
				},
				{
					data: 'kode_material'
				},
				{
					data: 'no_aloy'
				},
				{
					data: 'thickness'
				},
				{
					data: 'width'
				},
				{
					data: 'length'
				},
				{
					data: 'delivery_date'
				},
				{
					data: 'total_weight'
				},
				{
					data: 'total_sheet'
				},
				{
					data: 'total_spk'
				},
				{
					data: 'fg'
				},
				{
					data: 'action'
				}
			],
			responsive: true,
			processing: true,
			serverSide: true,
			stateSave: true,
			destroy: true,
			paging: true
		});
	}

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