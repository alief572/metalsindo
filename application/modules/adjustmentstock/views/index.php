<?php
$ENABLE_ADD     = has_permission('Adjustment_stock.Add');
$ENABLE_MANAGE  = has_permission('Adjustment_stock.Manage');
$ENABLE_VIEW    = has_permission('Adjustment_stock.View');
$ENABLE_DELETE  = has_permission('Adjustment_stock.Delete');
$id_bentuk = $this->uri->segment(3);
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="nav-tabs-supplier">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#history" data-toggle="tab" aria-expanded="true">History Adjustment Stock</a></li>
		<li><a href="#adjust" data-toggle="tab" aria-expanded="true">Add Adjust</a></li>
	</ul>
</div>

<div class="tab-content">
	<div class="tab-pane active" id="history">
		<div class="box">

			<div class="box-body">
				<table id="exampleaa" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>No Transaksi</th>
							<th>Tanggal</th>
							<th>Material</th>
							<th>Adjust</th>
							<th>Gudang</th>
							<th>Keterangan</th>
							<th>Jumlah Stock</th>
						</tr>
					</thead>
					<tbody>
						<!-- DataTables -->
					</tbody>
				</table>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<div class="tab-pane" id="adjust">
		<div class="box">

			<div class="box-body">
				<table id="examplebb" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th width="5">#</th>
							<th>FERROUS / NON FERROUS</th>
							<th>ID</th>
							<th>Detail Nama Material</th>
							<th>Bentuk Material</th>
							<th>Supplier</th>
							<th>Jumlah Stok</th>
							<?php if ($ENABLE_MANAGE) : ?>
								<th width="13%">Action</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
			<!-- /.box-body -->
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
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Adjustment Stock</h4>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		datatable_history();
		datatable_adjust();

		$('.select2').select2({
			width: '100%'
		});
	});

	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('id_inventory3');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'adjustmentstock/AdjustNow/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.copy', function(e) {
		var id = $(this).data('id_inventory3');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Copy Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'inventory_4/copyInventory/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('id_inventory3');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'inventory_4/viewInventory/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add', function() {
		var id = $(this).data('id_bentuk');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'stock_material/AddStock/' + id,
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
		var id = $(this).data('id_inventory3');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Inventory akan di hapus.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},

			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'inventory_4/deleteInventory',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Inventory berhasil dihapus.",
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

		// var table = $('#exampleaa').DataTable({
		// 	orderCellsTop: true,
		// 	fixedHeader: true
		// });
		$("#form-area").hide();
	});

	function datatable_history() {
		$('#exampleaa').DataTable({
			serverSide: true,
			processing: true,
			orderCellsTop: true,
			fixedHeader: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				url: siteurl + 'adjustmentstock/history_datatable',
				type: 'POST',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_transaksi'
				},
				{
					data: 'tanggal_transaksi'
				},
				{
					data: 'nama_material'
				},
				{
					data: 'adjustment'
				},
				{
					data: 'gudang'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'jumlah_stock'
				}
			]
		});
	}

	function datatable_adjust() {
		$('#examplebb').DataTable({
			serverSide: true,
			processing: true,
			orderCellsTop: true,
			fixedHeader: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				url: siteurl + 'adjustmentstock/adjust_datatable',
				type: 'POST',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'ferrous_nonferrous'
				},
				{
					data: 'id'
				},
				{
					data: 'detail_nama_material'
				},
				{
					data: 'bentuk_material'
				},
				{
					data: 'supplier'
				},
				{
					data: 'jumlah_stok'
				},
				{
					data: 'action'
				}
			]
		});
	}

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

		// var table = $('#examplebb').DataTable({
		// 	orderCellsTop: true,
		// 	fixedHeader: true
		// });
		$("#form-area").hide();
	});
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