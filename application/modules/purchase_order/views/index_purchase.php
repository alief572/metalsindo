<?php
$ENABLE_ADD     = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Order.View');
$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
		<span class="pull-left">

		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">#</th>
					<th>No PO</th>
					<th>Tanggal PO</th>
					<th>Supplier</th>
					<th>Progress PR</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th>Action</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {

					$numb = 0;
					foreach ($results as $record) {
						$numb++; ?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->no_surat ?></td>
							<td><?= date('d-M-Y', strtotime($record->tanggal)) ?></td>
							<td><?= $record->namesup ?></td>
							<?php if ($record->status == '1') {
								echo "<td><span class='badge bg-blue'>Waiting</span></td>";
							} elseif ($record->status == '2') {
								echo "<td><span class='badge bg-green'>Approved</span></td>";
							} else {
								echo "<td><span class='badge bg-red'>Closed</span></td>";
							}
							?>

							<td style="padding-left:20px">
								<?php if ($ENABLE_MANAGE) :
									if ($record->status == '2') { ?>
										<a class="btn btn-info btn-sm" href="<?= base_url('/purchase_order/request/' . $record->no_po) ?>" title="Request Pembayaran UM"><i class="fa fa-edit"></i></a>
								<?php }
								endif; ?>
							</td>

						</tr>
				<?php }
				}  ?>
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
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Penawaran</h4>
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
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/EditHeader/' + id,
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
		var id = $(this).data('no_po');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_order/Lihat/' + id,
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
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/addHeader',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault()
		var id = $(this).data('no_po');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "P.R. Akan Dihapus.",
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
					url: siteurl + 'purchase_order/Approved',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "P.R Approved.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal Approve data",
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

		var table = $('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
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