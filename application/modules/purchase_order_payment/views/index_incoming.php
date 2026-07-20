<?php
$ENABLE_ADD     = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Order.View');
$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');

?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<style type="text/css">
	thead input {
		width: 100%;
	}
	.swal2-popup {
		font-size: 1.5rem !important;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<!-- /.box-header -->
	<div class="box-body">
		<b>Receive Invoice DP</b>
		<p>Select Request Payment</p>

		<input type="radio" name="checkbx" id="" class="checkbx" value="dp" checked> Receive Invoice <br>
		<!-- <input type="radio" name="checkbx" id="" class="checkbx" value="inc"> Receive Invoice Incoming <br> -->
		<input type="radio" name="checkbx" id="" class="checkbx" value="pro"> Receive Invoice Progress <br>
		<!-- <input type="radio" name="checkbx" id="" class="checkbx" value="ret"> Receive Invoice Retensi <br> -->

		<div class="dic">

		</div>


	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>Receive Invoice Down Payment (DP)</h4>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- page script -->
<script>
	$(document).ready(function() {
		checkbx('dp');
	});

	$(document).on('click', '.checkbx', function() {
		var val = $(this).val();
		checkbx(val);
	});

	function checkbx(tipe) {
		$('.dic').html('');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'checkbx',
			data: {
				'checkbx': tipe
			},
			cache: false,
			success: function(result) {
				$('.dic').html(result);
			},
			error: function(result) {
				Swal.fire({
					title: 'Gagal!',
					text: 'Terjadi kesalahan, silakan coba lagi nanti!',
					icon: 'error'
				});
			}
		});
	}

	$(document).on('click', '.req_app', function() {
		var no_surat = $(this).data('no_po');
		var id_top = $(this).data('id_top');
		var tipe = $(this).data('tipe');

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'req_app',
			data: {
				'no_po': no_surat,
				'id_top': id_top,
				'tipe': tipe
			},
			cache: false,
			success: function(result) {
				if (tipe == 'dp') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Down Payment (DP)');
				}
				if (tipe == 'pro') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Progress');
				}
				if (tipe == 'ret') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Retensi');
				}
				$('.save_btn_modal').show();
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.req_inc_app', function() {
		var no_surat = $(this).data('kode_trans');
		var tipe_incoming = $(this).data('tipe_incoming');

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'req_inc_app',
			data: {
				'no_po': no_surat,
				'tipe_incoming': tipe_incoming
			},
			cache: false,
			success: function(result) {
				$('.save_btn_modal').show();
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('id');
		var id_top = $(this).data('id_top');
		var tipe = $(this).data('tipe');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'view',
			data: {
				'id': id,
				'id_top': id_top,
				'tipe': tipe
			},
			cache: false,
			success: function(result) {
				if (tipe == 'dp') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Down Payment (DP)');
				}
				if (tipe == 'pro') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Progress');
				}
				if (tipe == 'ret') {
					$('.modal-title').html('<i class="fa fa-users"></i> Receive Invoice Retensi');
				}
				$('.save_btn_modal').hide();
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.list_pro_inv', function() {
		var no_po = $(this).data('no_po');
		var id_top = $(this).data('id_top');
		var tipe = $(this).data('tipe');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'list_pro_inv',
			data: {
				'no_po': no_po,
				'id_top': id_top,
				'tipe': tipe
			},
			cache: false,
			success: function(result) {
				$('.modal-title').html('<i class="fa fa-list"></i> List Invoice Progress');
				$('.save_btn_modal').hide();
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.view_pro_detail', function() {
		var id = $(this).data('id');
		var tipe = $(this).data('tipe');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'view',
			data: {
				'id': id,
				'tipe': tipe
			},
			cache: false,
			success: function(result) {
				$('.modal-title').html('<i class="fa fa-users"></i> View Detail Invoice Progress');
				$('.save_btn_modal').hide();
				$('#ModalView').html(result);
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.view_inc', function() {
		var id = $(this).data('id');

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'view_inc',
			data: {
				'id': id
			},
			cache: false,
			success: function(result) {

				$('.modal-title').html('Receive Invoice Incoming');

				$('.save_btn_modal').hide();
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.list_dp', function() {
		var no_po = $(this).data('no_po');

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'list_dp',
			data: {
				'no_po': no_po
			},
			cache: false,
			dataType: "JSON",
			success: function(result) {

			},
			error: function(result) {

			}
		})
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		// check if 
		var invoice_date = $('input[name="invoice_date"]').val();
		var invoice_date_real = $('input[name="invoice_date_real"]').val();
		var no_invoice = $('input[name="nomor_invoice"]').val();
		var kurs = $('input[name="kurs"]').val();
		var no_faktur_pajak = $('input[name="nomor_faktur_pajak"]').val();
		var bank = $('input[name="bank"]').val();
		var no_bank = $('input[name="no_bank"]').val();
		var nm_acc_bank = $('input[name="nm_acc_bank"]').val();
		var upload_invoice = $('input[name="upload_invoice"]').val();

		if (
			!invoice_date || invoice_date.trim() === "" ||
			!invoice_date_real || invoice_date_real.trim() === "" ||
			!no_invoice || no_invoice.trim() === "" ||
			!kurs || kurs.trim() === "" ||
			!no_faktur_pajak || no_faktur_pajak.trim() === "" ||
			!bank || bank.trim() === "" ||
			!no_bank || no_bank.trim() === "" ||
			!nm_acc_bank || nm_acc_bank.trim() === "" ||
			!upload_invoice || upload_invoice.trim() === ""
		) {
			Swal.fire({
				title: 'Peringatan!',
				text: 'Maaf, pastikan semua form input telah terisi dengan lengkap!',
				icon: 'warning'
			});
			return false;
		}


		Swal.fire({
				title: "Peringatan!",
				text: "Invoice PO akan dibuat!",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Ya, Buat!",
				cancelButtonText: "Batal"
			}).then((result) => {
				if (result.isConfirmed) {

					var formdata = new FormData($('#frm-data')[0]);
					$.ajax({
						type: 'POST',
						url: siteurl + active_controller + '/save_invoice',
						data: formdata,
						cache: false,
						dataType: 'json',
						processData: false,
						contentType: false,
						success: function(result) {
							if (result.status == 1) {
								Swal.fire({
									title: 'Berhasil!',
									text: 'Invoice PO berhasil disimpan!',
									icon: 'success'
								}).then(() => {
									location.reload();
								});
							} else {
								Swal.fire({
									title: 'Gagal!',
									text: 'Invoice PO gagal disimpan!',
									icon: 'error'
								});
							}
						},
						error: function(result) {
							Swal.fire({
								title: 'Gagal!',
								text: 'Terjadi kesalahan, silakan coba lagi nanti!',
								icon: 'error'
							});
						}
					});
				}
			});
	});
</script>