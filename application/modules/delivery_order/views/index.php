<?php
$ENABLE_ADD     = has_permission('Delivery_Order.Add');
$ENABLE_MANAGE  = has_permission('Delivery_Order.Manage');
$ENABLE_VIEW    = has_permission('Delivery_Order.View');
$ENABLE_DELETE  = has_permission('Delivery_Order.Delete');

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
		<?php if ($ENABLE_VIEW) : ?>
			<a class="btn btn-success btn-sm" href="<?= base_url('/delivery_order/addHeader/') ?>" title="Tambah"><i class="fa fa-plus">&nbsp;</i>Add</i></a>
			<a class="btn btn-success btn-sm" href="<?= base_url('/delivery_order/addHeaderSlitting/') ?>" title="Delivery Slitting"><i class="fa fa-plus">&nbsp;</i>Slitting</i></a>
		<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Tanggal DO</th>
					<th>No. DO</th>
					<th>SPK Marketing</th>
					<th>Custommer</th>
					<th>Total FG</th>
					<th>Total Scrap</th>
					<th>Total Berat</th>
					<th>Total Sheet</th>
					<th>Type</th>
					<th>Action</th>
				</tr>
			</thead>

			<tbody></tbody>
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
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Delivery Order</h4>
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

	$(document).on('click', '.view', function() {
		var id = $(this).data('id_spkmarketing');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail DO</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'delivery_order/ViewHeader/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.print', function() {
		var id = $(this).data('id_spkmarketing');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'delivery_order/PrintHeader/' + id,
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
	$(document).on('click', '.release', function(e) {
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Release Delivery Order ?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'delivery_order/ReleaseDO',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Success, Thanks !!!",
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
		// Initialize DataTable with enhanced configuration
		const table = $('#example1').DataTable({
			ajax: {
				url: `${siteurl}${active_controller}get_delivery_order`,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: function(d) {

				}
			},
			processing: true,
			serverSide: true,
			paging: true,
			deferRender: true, // Improves rendering speed for large datasets
			scrollY: 400, // Enables vertical scrolling
			scrollCollapse: true,
			order: [
				[0, 'asc']
			], // Default ordering
			columns: [{
					data: 'no'
				},
				{
					data: 'tanggal_do'
				},
				{
					data: 'no_do'
				},
				{
					data: 'no_spk_marketing'
				},
				{
					data: 'nm_customer'
				},
				{
					data: 'total_fg',
					className: 'text-right'
				},
				{
					data: 'total_scrap',
					className: 'text-right'
				},
				{
					data: 'total_berat',
					className: 'text-right'
				},
				{
					data: 'total_sheet',
					className: 'text-right'
				},
				{
					data: 'tipe'
				},
				{
					data: 'action',
					orderable: false,
					searchable: false,
					className: 'text-center',
					// Render action buttons safely
					render: function(data, type, row) {
						return data;
					}
				}
			],
			language: {
				processing: "Loading...",
				zeroRecords: "No matching records found",
				info: "Showing _START_ to _END_ of _TOTAL_ entries",
				infoEmpty: "No entries to show",
				infoFiltered: "(filtered from _MAX_ total entries)",
				search: "Search:",
				paginate: {
					first: "First",
					last: "Last",
					next: "Next",
					previous: "Prev"
				}
			},
			// Reuse the same table instance after Ajax reload
			drawCallback: function() {
				$('.edit, .view, .print, .release').off('click').on('click', function(e) {
					// Delegated handlers will be attached here if needed
				});
			}
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