<?php
$ENABLE_ADD     = has_permission('Control_PO.Add');
$ENABLE_MANAGE  = has_permission('Control_PO.Manage');
$ENABLE_VIEW    = has_permission('Control_PO.View');
$ENABLE_DELETE  = has_permission('Control_PO.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?php echo base_url('assets/css/select2.css'); ?>">

<div class="box">
	<div class="box-header">
		<div class="row">
			<div class="col-md-3">
				<select name="no_po" id="" class="form-control form-control-sm select2">
					<option value="">- Select No. PO -</option>
					<?php foreach ($list_po as $row) { ?>
						<option value="<?= $row->no_po ?>"><?= $row->no_surat ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-3">
				<select name="suplier" id="" class="form-control form-control-sm select2">
					<option value="">- Select Supplier -</option>
					<?php foreach ($list_suplier as $row) { ?>
						<option value="<?= $row->id_suplier ?>"><?= $row->name_suplier ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-3">
				<select name="barang" id="" class="form-control form-control-sm select2">
					<option value="">- Select Barang -</option>
					<?php foreach ($list_barang as $row) { ?>
						<option value="<?= $row->idmaterial ?>"><?= $row->idmaterial ?> - <?= $row->nama_material ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-3">
				<button type="button" class="btn btn-sm btn-primary search"><i class="fa fa-search"></i> Search</button>
				<button type="button" class="btn btn-sm btn-danger reset"><i class="fa fa-close"></i> Reset</button>
				<button type="button" class="btn btn-sm btn-success download_excel" title="Download Excel"><i class="fa fa-download"></i> Excel</button>
			</div>
		</div>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>No.PR</th>
					<th>No.PO</th>
					<th>Supplier</th>
					<th>Material</th>
					<th>Width</th>
					<th>Qty Order (Kg)</th>
					<th>Qty Receive (Kg)</th>
					<th>Balance</th>
					<th>Status</th>
					<th>Option</th>
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
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/select2.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$('.select2').select2({
		width: '100%'
	});

	$(document).on('click', '.search', function(e) {
		e.preventDefault();
		DataTables();
	});

	$(document).on('click', '.reset', function(e) {
		e.preventDefault();
		$('select[name="no_po"]').val('').trigger('change');
		$('select[name="suplier"]').val('').trigger('change');
		$('select[name="barang"]').val('').trigger('change');
		DataTables();
	});

	$(document).on('click', '.download_excel', function(e) {
		e.preventDefault();
		var no_po = $('select[name="no_po"]').val();
		var suplier = $('select[name="suplier"]').val();
		var barang = $('select[name="barang"]').val();

		var query = $.param({
			no_po: no_po,
			suplier: suplier,
			barang: barang
		});

		var url = siteurl + 'control_po/download_excel?' + query;
		window.location.href = url;
	});

	function DataTables() {
		var no_po = $('select[name="no_po"]').val();
		var suplier = $('select[name="suplier"]').val();
		var barang = $('select[name="barang"]').val();

		var DataTables = $('#example1').dataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_control_po',
				dataType: 'json',
				data: function(d) {
					d.no_po = no_po;
					d.suplier = suplier;
					d.barang = barang;
				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_pr'
				},
				{
					data: 'no_po'
				},
				{
					data: 'supplier'
				},
				{
					data: 'material'
				},
				{
					data: 'width'
				},
				{
					data: 'qty_order'
				},
				{
					data: 'qty_receive'
				},
				{
					data: 'balance'
				},
				{
					data: 'status'
				},
				{
					data: 'option'
				}
			]
		});
	}
	$(function() {
		DataTables();
		$("#form-area").hide();
	});

	$(document).on('click', '.detail', function(e) {
		var id_detail = $(this).data('id_po');
		$("#head_title").html("<b>Detial View</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'control_po/modal_detail/' + id_detail,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
			}
		})
	});

	$(document).on('click', '.checked', function(e) {
		e.preventDefault()
		var id_po = $(this).data('id_po');

		swal({
				title: "Anda Yakin?",
				text: "Close PO ?",
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
					url: siteurl + 'control_po/close_po',
					dataType: "json",
					data: {
						'id_po': id_po
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "PO Closed.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								});
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal Approve data",
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						});
					}
				})
			});
	});
</script>