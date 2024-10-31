<?php
$ENABLE_ADD     = has_permission('Receive_Invoice_AP.Add');
$ENABLE_MANAGE  = has_permission('Receive_Invoice_AP.Manage');
$ENABLE_VIEW    = has_permission('Receive_Invoice_AP.View');
$ENABLE_DELETE  = has_permission('Receive_Invoice_AP.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr>
					<th class="text-center">No.</th>
					<th class="text-center">No. Incoming</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Supplier</th>
					<th class="text-center">Nominal Invoice</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Create Receive Invoice</h4>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">
					<input type="hidden" name="id_incoming">
					<div class="form-group">
						<label for="">Receive Date</label>
						<input type="date" class="form-control form-control-sm" name="receive_date" id="" required>
					</div>
					<div class="form-group">
						<label for="">No. Invoice</label>
						<input type="text" class="form-control form-control-sm" name="no_invoice" id="">
					</div>
					<div class="form-group">
						<label for="">Total Invoice</label>
						<input type="text" class="form-control form-control-sm text-right auto_num" name="total_invoice" id="">
					</div>
					<div class="form-group">
						<label for="">Nilai PPN</label>
						<input type="text" class="form-control form-control-sm text-right auto_num" name="nilai_ppn" id="">
					</div>
					<div class="form-group">
						<label for="">No. Faktur Pajak</label>
						<input type="text" class="form-control form-control-sm" name="no_faktur_pajak" id="">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-secondary" onclick="$('#dialog-popup').modal('hide')">Cancel</button>
					<button type="submit" class="btn btn-sm btn-success">Create</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('asset/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	

	$(document).ready(function() {
		DataTables();

		$('.auto_num').autoNumeric();
	});

	$(document).on('click', '.create_rec_inv', function() {
		var id_incoming = $(this).data('id_incoming');

		$('input[name="id_incoming"]').val(id_incoming);

		$('#dialog-popup').modal('show');

		$('input[name="receive_date"]').val('');
		$('input[name=""]').val('');
		$('input[name="no_invoice"]').val('');
		$('input[name="total_invoice"]').val('');
		$('input[name="nilai_ppn"]').val('');
		$('input[name="no_faktur_pajak"]').val('');
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		swal({
			type: 'warning',
			title: 'Are you sure ?',
			text: 'This will make receive invoice data !',
			showCancelButton: true
		}, function(next) {
			if(next) {
				var formData = $('#frm-data').serialize();
			}
		});
	});

	function DataTables(costcenter = null, product = null) {
		var dataTable = $('#example1').DataTable({
			ajax: {
				url: siteurl + active_controller + 'get_data_incoming',
				type: "POST",
				dataType: "JSON",
				data: function(d) {

				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_incoming'
				},
				{
					data: 'no_po'
				},
				{
					data: 'no_invoice'
				},
				{
					data: 'supplier'
				},
				{
					data: 'nominal_invoice'
				},
				{
					data: 'status'
				},
				{
					data: 'option'
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
</script>