<?php
$ENABLE_ADD     = has_permission('Control_DO.Add');
$ENABLE_MANAGE  = has_permission('Control_DO.Manage');
$ENABLE_VIEW    = has_permission('Control_DO.View');
$ENABLE_DELETE  = has_permission('Control_DO.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}

	.swal2-popup {
		font-size: 15px;
		/* Sesuaikan dengan ukuran yang lebih besar */
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example2" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th class="text-center">Tanggal DO</th>
					<th class="text-center">No. DO</th>
					<th class="text-center">SPK Marketing</th>
					<th class="text-center">Customer</th>
					<th class="text-center">Qty Order</th>
					<th class="text-center">Qty Delivery</th>
					<th class="text-centre">Balance</th>
					<th class="text-center">Option</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="head_title"><span class="fa fa-users"></span></h4>
			</div>
			<form action="" id="frm_data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Cancel</button>
					<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Confirm</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- page script -->
<script type="text/javascript">
	$(function() {
		var table = $('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
		$("#form-area").hide();

		datatables();
	});

	$(document).on('click', '.confirm_do', function() {
		var id = $(this).data('id');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'confirm_do',
			data: {
				'id': id
			},
			cache: false,
			success: function(result) {
				$('#head_title').html('<i class="fa fa-check"></i> Confirm DO');
				$('#ModalView').html(result);

				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				Swal.fire({
					icon: 'error',
					title: 'Error !',
					text: 'Please try again later !',
					showConfirmButton: false,
					showCancelButton: false,
					allowOutsideClick: false,
					timer: 3000
				});
			}
		});
	})

	$(document).on('submit', '#frm_data', function(e) {
		e.preventDefault();

		Swal.fire({
			icon: 'warning',
			title: 'Are you sure ?',
			text: 'This data will be confirmed, stock will be deducted and Invoice will be created !',
			showCancelButton: true,
			showConfirmButton: true,
			allowOutsideClick: false
		}).then((next) => {
			if (next.isConfirmed) {
				var formdata = $('#frm_data').serialize();

				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'save_confirm_data',
					data: formdata,
					cache: false,
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: result.msg,
								showConfirmButton: false,
								showCancelButton: false,
								allowOutsideClick: false,
								timer: 3000
							}).then(() => {
								Swal.close();
								datatables();

								$('#dialog-popup').modal('hide');
							});
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Failed !',
								text: result.msg,
								showConfirmButton: false,
								showCancelButton: false,
								allowOutsideClick: false,
								timer: 3000
							});
						}
					},
					error: function(result) {
						Swal.fire({
							icon: 'error',
							title: 'Error !',
							text: 'Please try again later !',
							showConfirmButton: false,
							showCancelButton: false,
							allowOutsideClick: false,
							timer: 3000
						})
					}
				});
			}
		});
	})

	function datatables() {
		var datatables = $('#example2').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_control_do',
				cache: false,
				dataType: 'json'
			},
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
					data: 'spk_marketing'
				},
				{
					data: 'customer'
				},
				{
					data: 'qty_order'
				},
				{
					data: 'qty_delivery'
				},
				{
					data: 'balance'
				},
				{
					data: 'option'
				}
			]
		});
	}
</script>