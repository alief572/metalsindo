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
<link rel="stylesheet" href="<?= base_url('assets/css/select2.css') ?>">

<div class="box">
	<div class="box-header">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#mat1" onclick="changeTab1()" class='mat1' aria-controls="mat1" role="tab" data-toggle="tab">Material</a></li>
			<li role="presentation"><a href="#mat2" onclick="changeTab2()" class='mat2' aria-controls="mat2" role="tab" data-toggle="tab">Scrap</a></li>
		</ul>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<div class="pers1_div">
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_tahun">Tahun</label>
						<select class="form-control form-control-sm select2" name="filter_tahun" id="filter_tahun">
							<option value="">- Pilih Tahun -</option>
							<?php
							for ($i = date('Y'); $i >= 2020; $i--) {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_bulan">Bulan</label>
						<select class="form-control form-control-sm select2" name="filter_bulan" id="filter_bulan">
							<option value="">- Pilih Bulan -</option>
							<?php
							for ($i = 1; $i <= 12; $i++) {
								echo '<option value="' . $i . '">' . date('F', mktime(0, 0, 0, $i, 1)) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_no_do">No. DO</label>
						<select class="form-control form-control-sm select2" name="filter_no_do" id="filter_no_do">
							<option value="">- Select DO -</option>
							<?php
							foreach ($list_do as $do) {
								echo '<option value="' . $do->no_surat . '">' . $do->no_surat . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_no_spk">No. SPK Marketing</label>
						<select class="form-control form-control-sm select2" name="filter_no_spk" id="filter_no_spk">
							<option value="">- Select SPK Marketing -</option>
							<?php
							foreach ($list_spk_marketing as $spk_marketing) {
								echo '<option value="' . $spk_marketing->no_surat . '">' . $spk_marketing->no_surat . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_customer">Customer</label>
						<select class="form-control form-control-sm select2" name="filter_customer" id="filter_customer">
							<option value="">- Select Customer -</option>
							<?php
							foreach ($list_customer as $customer) {
								echo '<option value="' . $customer->id_customer . '">' . $customer->name_customer . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<br>
					<button type="button" class="btn btn-sm btn-primary" onclick="filterData()"><i class="fa fa-search"></i> Search</button>
					<button type="button" class="btn btn-sm btn-danger" onclick="clearFilter()"><i class="fa fa-times"></i> Clear</button>
					<button type="button" class="	btn btn-sm btn-success" onclick="downloadExcel()"><i class="fa fa-download"></i> Download Excel</button>
				</div>
			</div>
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
		<div class="pers2_div" hidden>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_scrap_tahun">Tahun</label>
						<select class="form-control form-control-sm select2" name="filter_scrap_tahun" id="filter_scrap_tahun">
							<option value="">- Pilih Tahun -</option>
							<?php
							for ($i = date('Y'); $i >= 2020; $i--) {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_scrap_bulan">Bulan</label>
						<select class="form-control form-control-sm select2" name="filter_scrap_bulan" id="filter_scrap_bulan">
							<option value="">- Pilih Bulan -</option>
							<?php
							for ($i = 1; $i <= 12; $i++) {
								echo '<option value="' . $i . '">' . date('F', mktime(0, 0, 0, $i, 1)) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_scrap_no_do">No. DO</label>
						<select class="form-control form-control-sm select2" name="filter_scrap_no_do" id="filter_scrap_no_do">
							<option value="">- Select DO -</option>
							<?php
							foreach ($list_do as $do) {
								echo '<option value="' . $do->no_surat . '">' . $do->no_surat . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_scrap_no_spk">No. SPK Marketing</label>
						<select class="form-control form-control-sm select2" name="filter_scrap_no_spk" id="filter_scrap_no_spk">
							<option value="">- Select SPK Marketing -</option>
							<?php
							foreach ($list_spk_marketing as $spk_marketing) {
								echo '<option value="' . $spk_marketing->no_surat . '">' . $spk_marketing->no_surat . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_scrap_customer">Customer</label>
						<select class="form-control form-control-sm select2" name="filter_scrap_customer" id="filter_scrap_customer">
							<option value="">- Select Customer -</option>
							<?php
							foreach ($list_customer as $customer) {
								echo '<option value="' . $customer->id_customer . '">' . $customer->name_customer . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<br>
					<button type="button" class="btn btn-sm btn-primary" onclick="filterDataScrap()"><i class="fa fa-search"></i> Search</button>
					<button type="button" class="btn btn-sm btn-danger" onclick="clearFilterScrap()"><i class="fa fa-times"></i> Clear</button>
					<button type="button" class="	btn btn-sm btn-success" onclick="downloadExcelScrap()"><i class="fa fa-download"></i> Download Excel</button>
				</div>
			</div>
			<table id="example3" class="table table-bordered table-striped">
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

<div class="modal modal-default fade" id="dialog-popup-scrap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="head_title_scrap"><span class="fa fa-users"></span></h4>
			</div>
			<form action="" id="frm_data_scrap">
				<div class="modal-body" id="ModalViewScrap">
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
<script src="<?= base_url('assets/js/select2.js') ?>"></script>


<!-- page script -->
<script type="text/javascript">
	$('.select2').select2({
		width: '100%'
	});
	$(function() {
		var table = $('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
		$("#form-area").hide();

		datatables();
	});

	$(document).on('click', '.pers_1', function() {
		$('.pers_1').toggle('active');
		$('.pers_2').removeClass('active');

		// $('.pers1_div').show();
		// $('.pers2_div').hide();

		datatables();
	});

	$(document).on('click', '.pers_2', function() {
		$('.pers_2').toggle('active');
		$('.pers_1').removeClass('active');

		// $('.pers2_div').show();
		// $('.pers1_div').hide();

		datatables_scrap();
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

	$(document).on('click', '.confirm_do_scrap', function() {
		var id = $(this).data('id');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'confirm_do_scrap',
			data: {
				'id': id
			},
			cache: false,
			success: function(result) {
				$('#head_title_scrap').html('<i class="fa fa-check"></i> Confirm DO Scrap');
				$('#ModalViewScrap').html(result);

				$('#dialog-popup-scrap').modal('show');
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

		var no = $('input[name="no"]').val();
		// alert(no);

		var sts = 1;
		// var sts = 1;
		for (i = 1; i <= no; i++) {
			var qty_do = $('input[name="detail[' + i + '][qty_do]"]').val();
			if (qty_do.length < 1) {
				qty_do = 0;
			} else {
				qty_do = qty_do.split(',').join('');
				qty_do = parseFloat(qty_do);
			}
			var qty_in = $('input[name="detail[' + i + '][qty_in]"]').val();
			if (qty_in.length < 1) {
				qty_in = 0;
			} else {
				qty_in = qty_in.split(',').join('');
				qty_in = parseFloat(qty_in);
			}
			var qty_ng = $('input[name="detail[' + i + '][qty_ng]"]').val();
			if (qty_ng.length < 1) {
				qty_ng = 0;
			} else {
				qty_ng = qty_ng.split(',').join('');
				qty_ng = parseFloat(qty_ng);
			}

			if (sts == 1) {
				if ((qty_in + qty_ng) > qty_do) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning !',
						text: 'Mohon maaf, qty input yang melebihi qty DO !',
						showConfirmButton: false,
						showCancelButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
						timer: 3000
					});
					sts = 0;
					return false;
				}

				if ((qty_in + qty_ng) !== qty_do) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning !',
						text: 'Mohon maaf, total qty input harus sama dengan qty DO !',
						showConfirmButton: false,
						showCancelButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
						timer: 3000
					});
					sts = 0;
					return false;
				}
			}
		}

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

	$(document).on('submit', '#frm_data_scrap', function(e) {
		e.preventDefault();

		var no = $('input[name="no"]').val();
		// alert(no);

		var sts = 1;
		// var sts = 1;
		for (i = 1; i <= no; i++) {
			var qty_do = $('input[name="detail[' + i + '][qty_do]"]').val();
			if (qty_do.length < 1) {
				qty_do = 0;
			} else {
				qty_do = qty_do.split(',').join('');
				qty_do = parseFloat(qty_do);
			}
			var qty_in = $('input[name="detail[' + i + '][qty_in]"]').val();
			if (qty_in.length < 1) {
				qty_in = 0;
			} else {
				qty_in = qty_in.split(',').join('');
				qty_in = parseFloat(qty_in);
			}
			var qty_ng = $('input[name="detail[' + i + '][qty_ng]"]').val();
			if (qty_ng.length < 1) {
				qty_ng = 0;
			} else {
				qty_ng = qty_ng.split(',').join('');
				qty_ng = parseFloat(qty_ng);
			}

			if (sts == 1) {
				if ((qty_in + qty_ng) > qty_do) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning !',
						text: 'Mohon maaf, qty input yang melebihi qty DO !',
						showConfirmButton: false,
						showCancelButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
						timer: 3000
					});
					sts = 0;
					return false;
				}

				if ((qty_in + qty_ng) !== qty_do) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning !',
						text: 'Mohon maaf, total qty input harus sama dengan qty DO !',
						showConfirmButton: false,
						showCancelButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
						timer: 3000
					});
					sts = 0;
					return false;
				}
			}
		}

		Swal.fire({
			icon: 'warning',
			title: 'Are you sure ?',
			text: 'This data will be confirmed, stock will be deducted and Invoice will be created !',
			showCancelButton: true,
			showConfirmButton: true,
			allowOutsideClick: false
		}).then((next) => {
			if (next.isConfirmed) {
				var formdata = $('#frm_data_scrap').serialize();

				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'save_confirm_data_scrap',
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
								datatables_scrap();

								$('#dialog-popup-scrap').modal('hide');
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

	function filterData() {
		datatables();
	}

	function filterDataScrap() {
		datatables_scrap();
	}

	function clearFilter() {
		$('select[name="filter_tahun"]').val('').trigger('change');
		$('select[name="filter_bulan"]').val('').trigger('change');
		$('select[name="filter_no_do"]').val('').trigger('change');
		$('select[name="filter_no_spk"]').val('').trigger('change');
		$('select[name="filter_customer"]').val('').trigger('change');

		datatables();
	}

	function clearFilterScrap() {
		$('select[name="filter_scrap_tahun"]').val('').trigger('change');
		$('select[name="filter_scrap_bulan"]').val('').trigger('change');
		$('select[name="filter_scrap_no_do"]').val('').trigger('change');
		$('select[name="filter_scrap_no_spk"]').val('').trigger('change');
		$('select[name="filter_scrap_customer"]').val('').trigger('change');

		datatables_scrap();
	}

	function changeTab1() {
		$('.pers1_div').show();
		$('.pers2_div').hide();

		datatables();
	}

	function changeTab2() {
		$('.pers2_div').show();
		$('.pers1_div').hide();

		datatables_scrap();
	}

	function downloadExcel() {
		var tahun = $('select[name="filter_tahun"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var no_do = $('select[name="filter_no_do"]').val();
		var no_spk = $('select[name="filter_no_spk"]').val();
		var customer = $('select[name="filter_customer"]').val();

		var url = siteurl + active_controller + 'download_excel?tahun=' + tahun + '&bulan=' + bulan + '&no_do=' + no_do + '&no_spk=' + no_spk + '&customer=' + customer;

		window.open(url, '_blank');
	}

	function downloadExcelScrap() {
		var tahun = $('select[name="filter_scrap_tahun"]').val();
		var bulan = $('select[name="filter_scrap_bulan"]').val();
		var no_do = $('select[name="filter_scrap_no_do"]').val();
		var no_spk = $('select[name="filter_scrap_no_spk"]').val();
		var customer = $('select[name="filter_scrap_customer"]').val();

		var url = siteurl + active_controller + 'download_excel_scrap?tahun=' + tahun + '&bulan=' + bulan + '&no_do=' + no_do + '&no_spk=' + no_spk + '&customer=' + customer;

		window.open(url, '_blank');
	}

	function datatables() {
		var tahun = $('select[name="filter_tahun"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var no_do = $('select[name="filter_no_do"]').val();
		var no_spk = $('select[name="filter_no_spk"]').val();
		var customer = $('select[name="filter_customer"]').val();

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
				dataType: 'json',
				data: function(d) {
					d.tahun = tahun;
					d.bulan = bulan;
					d.no_do = no_do;
					d.no_spk = no_spk;
					d.customer = customer;
				}
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

	function datatables_scrap() {
		var tahun = $('select[name="filter_scrap_tahun"]').val();
		var bulan = $('select[name="filter_scrap_bulan"]').val();
		var no_do = $('select[name="filter_scrap_no_do"]').val();
		var no_spk = $('select[name="filter_scrap_no_spk"]').val();
		var customer = $('select[name="filter_scrap_customer"]').val();

		var datatables = $('#example3').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_control_do_scrap',
				cache: false,
				dataType: 'json',
				data: function(d) {
					d.tahun = tahun;
					d.bulan = bulan;
					d.no_do = no_do;
					d.no_spk = no_spk;
					d.customer = customer;
				}
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