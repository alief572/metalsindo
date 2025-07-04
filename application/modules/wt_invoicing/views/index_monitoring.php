<?php
$ENABLE_ADD     = has_permission('Invoicing.Add');
$ENABLE_MANAGE  = has_permission('Invoicing.Manage');
$ENABLE_VIEW    = has_permission('Invoicing.View');
$ENABLE_DELETE  = has_permission('Invoicing.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.min.css">

<div class="box">
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>No.Invoice</th>
					<th>Nama Customer</th>
					<th>Marketing</th>
					<th>Top</th>
					<th>Payment</th>
					<th>Nilai<br>Invoice</th>
					<th>Total<br>Bayar</th>
					<th>Tanggal<br>Invoice</th>
					<th>Janji<br>Bayar</th>
					<th>Umur<br>Piutang</th>
					<th width="10%">Action</th>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {


					$numb = 0;
					foreach ($results as $record) {
						$numb++;

						$tgl_terima = (!empty($record->tgl_terima)) ? date('d-F-Y', strtotime($record->tgl_terima)) : '-';
						$tgl_janji = (!empty($record->tgl_janji_bayar)) ? date('d-F-Y', strtotime($record->tgl_janji_bayar)) : date('d-F-Y', strtotime($record->jatuh_tempo));

						$tgl1 = strtotime($tgl_terima);
						$tgl2 = strtotime(date('Y-m-d'));

						$jarak = $tgl2 - $tgl1;
						if ($tgl1 != '') {
							$umur = $jarak / 60 / 60 / 24;
						} else {
							$umur = 0;
						}

				?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->no_surat ?></td>
							<td><?= strtoupper($record->name_customer) ?></td>
							<td><?= $record->nama_sales ?></td>
							<td><?= $record->nama_top ?></td>
							<td><?= $record->payment ?></td>
							<td><?= number_format($record->nilai_invoice) ?></td>
							<td><?= number_format($record->total_bayar) ?></td>
							<td><?= date('d-F-Y', strtotime($record->tgl_invoice)) ?></td>
							<td><?= $tgl_janji ?></td>
							<td><?= $umur ?></td>

							<td>
								<?php if ($ENABLE_VIEW) : ?>
									<a class="btn btn-primary btn-sm history" href="#" title="Riwayat Follow UP" data-no_invoice="<?= $record->no_invoice ?>"><i class="fa fa-history"></i>
									</a>
								<?php endif; ?>
								<?php if ($ENABLE_MANAGE) : ?>
									<a class="btn btn-success btn-sm" href="<?= base_url('/wt_invoicing/FollowUp/' . $record->no_invoice) ?>" title="Follow UP" data-no_inquiry="<?= $record->no_inquiry ?>"><i class="fa fa-check"></i>
									</a>
								<?php endif; ?>
								<?php if ($ENABLE_MANAGE) : ?>
									<a class="btn btn-warning btn-sm tutup" href="#" title="Close Invoice" data-no_invoice="<?= $record->no_invoice ?>"><i class="fa fa-close"></i>
									</a>
								<?php endif; ?>
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
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;History Follow Up </h4>
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

<!-- modal -->
<div class="modal modal-default fade" id="ModalViewX" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id='head_title'>Closing Penawaran</h4>
			</div>
			<div class="modal-body" id="viewX">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" id='close_penawaran'>Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).on('click', '.history', function(e) {
		var id = $(this).data('no_invoice');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>History Follow UP</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'wt_invoicing/historyfu/' + id,
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
		var id = $(this).data('no_penawaran');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/ViewHeader/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});



	// CLOSE PENAWARAN
	$(document).on('click', '.close_penawaran', function(e) {
		e.preventDefault();
		var id = $(this).data('no_penawaran');

		$("#head_title").html("Closing Penawaran");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_closing_penawaran/' + id,
			success: function(data) {
				$("#ModalViewX").modal();
				$("#viewX").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(function() {

		$("#form-area").hide();
	});

	function DataTables() {
		var DataTables = $('#example1').dataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_monitoring_invoice',
				dataType: 'json'
			},
			column: [{
					data: 'no'
				},
				{
					data: 'no_invoice'
				},
				{
					data: 'nama_customer'
				},
				{
					data: 'marketing'
				},
				{
					data: 'top'
				},
				{
					data: 'payment'
				},
				{
					data: 'nilai_invoice'
				},
				{
					data: 'total_bayar'
				},
				{
					data: 'tanggal_invoice'
				},
				{
					data: 'janji_bayar'
				},
				{
					data: 'umur_piutang'
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


	$(document).on('click', '.tutup', function(e) {
		e.preventDefault()
		//var id = $('#id_spkproduksi').val();
		// alert(id);
		var id = $(this).data('no_invoice');
		swal({
				title: "Anda Yakin?",
				text: "Close Invoice",
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
					url: siteurl + 'wt_invoicing/closeInvoice',
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
</script>