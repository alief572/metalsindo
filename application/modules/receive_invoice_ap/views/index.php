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
						<label for="">Total Invoice + PPN</label>
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

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	$(document).on('click', '.create_rec_inv', function() {
		var id_incoming = $(this).data('id_incoming');

		$('input[name="id_incoming"]').val(id_incoming);

		$('#dialog-popup').modal('show');

		$('input[name="receive_date"]').val('');
		$('input[name="receive_date"]').attr('readonly', false);
		$('input[name="no_invoice"]').val('');
		$('input[name="no_invoice"]').attr('readonly', false);
		$('input[name="total_invoice"]').val('');
		$('input[name="total_invoice"]').attr('readonly', false);
		$('input[name="nilai_ppn"]').val('');
		$('input[name="nilai_ppn"]').attr('readonly', false);
		$('input[name="no_faktur_pajak"]').val('');
		$('input[name="no_faktur_pajak"]').attr('readonly', false);
	});

	$(document).on('click', '.view_inv', function() {
		var id_incoming = $(this).data('id_incoming');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'view_inv',
			data: {
				'id_incoming': id_incoming
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('input[name="receive_date"]').val(result.data_incoming.receive_date);
				$('input[name="receive_date"]').attr('readonly', true);

				$('input[name="no_invoice"]').val(result.data_incoming.no_invoice_rec_ap);
				$('input[name="no_invoice"]').attr('readonly', true);

				$('input[name="total_invoice"]').val(number_format(result.data_incoming.nilai_invoice, 2));
				$('input[name="total_invoice"]').attr('readonly', true);

				$('input[name="nilai_ppn"]').val(number_format(result.data_incoming.nilai_ppn));
				$('input[name="nilai_ppn"]').attr('readonly', true);

				$('input[name="no_faktur_pajak"]').val(result.data_incoming.no_faktur_pajak);
				$('input[name="no_faktur_pajak"]').attr('readonly', true);


				$('#dialog-popup').modal('show');

				$('.auto_num').autoNumeric();
			},
			error: function(result) {
				swal({
					type: 'error',
					title: 'Error !',
					text: 'Please try again later !'
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		swal({
			type: 'warning',
			title: 'Are you sure ?',
			text: 'This will make receive invoice data !',
			showCancelButton: true
		}, function(next) {
			if (next) {
				var formData = $('#frm-data').serialize();

				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'save_receive_invoice',
					data: formData,
					cache: false,
					dataType: 'JSON',
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
								text: result.pesan
							}, function(lanjut) {
								$('#dialog-popup').modal('hide');

								DataTables();
							});
						} else {
							swal({
								type: 'warning',
								title: 'Faield !',
								text: result.pesan
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again later !'
						});
					}
				})
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