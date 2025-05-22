<?php
$tanggal = date('Y-m-d');
?>

<style>
	input,
	select {
		mind-width: 200px;
	}
</style>
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Purchase Request</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">NO.PR</label>
									</div>
									<div class="col-md-8" hidden>
										<input type="text" class="form-control" id="no_pr" required name="no_pr" readonly placeholder="ID PR">
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="No.PR">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal PR</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control datepicker" id="tanggal" value="<?= $tanggal ?>" onkeyup required name="tanggal" readonly>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Requestor</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="requestor" required name="requestor">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group row">
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='addmaterial();'><i class='fa fa-plus'></i>Add</button>

							</div>
							<div class="form-group row ">
								<div class="table-responsive">
									<table class='table table-bordered table-striped'>
										<thead>
											<tr id="tr_thead" class='bg-blue'>
												<th width="300px">Material</th>
												<th width="220px">Bentuk</th>
												<th width="100px">ID</th>
												<th width="100px">OD</th>
												<th width="150px">Total Weight</th>
												<th width="150px">Width</th>
												<th width="150px">Length</th>
												<th width="180px">Supplier</th>
												<th width="150px">Tanggal Dibutuhkan</th>
												<th width="250px">Keterangan</th>
												<th width="100px">Aksi</th>
											</tr>
										</thead>
										<tbody id="data_request">
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<center>
							<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
						</center>
					</div>
				</div>
		</form>
	</div>
</div>

<style>
	.select2 {
		width: 250px !important;
	}

	.datepicker {
		cursor: pointer;
	}
</style>

<script src="<?php echo base_url('assets/js/jquery.maskMoney.js'); ?>"></script>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	$(document).ready(function() {
		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID			
		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var image = $('#image').val();
			var idtype = $('#inventory_1').val();

			var data, xhr;
			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Process it!",
					cancelButtonText: "No, cancel process!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + 'purchase_request/SaveNew';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
									window.location.href = base_url + active_controller;
								} else {

									if (data.status == 2) {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									}

								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

	});

	function addmaterial() {
		var jumlah = $('#data_request').find('tr').length;
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/AddMaterial',
			data: "jumlah=" + jumlah,
			success: function(html) {
				$("#data_request").append(html);

				$('.select2').select2();
				$('.autoNumeric').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true,
				});
			}
		});
	}

	function HitungTweight(id) {
		var dt_qty = $("#dt_qty_" + id).val();
		var dt_weight = $("#dt_weight_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/HitungTwight',
			data: "dt_weight=" + dt_weight + "&dt_qty=" + dt_qty + "&id=" + id,
			success: function(html) {
				$("#HasilTwight_" + id).html(html);
			}
		});
	}

	function cariSheet() {
		var no = 1;

		var sts_sheet = 0;
		$('#data_request').each(function() {

			var bentuk = $('#dt_bentuk_' + no).val();
			if (bentuk == 'SHEET' && sts_sheet == 0) {
				sts_sheet = 1;
			}

			no++;
		});

		return sts_sheet;
	}

	function CariProperties(id) {
		var idmaterial = $("#dt_idmaterial_" + id).val();
		var sts_sheet = cariSheet();

		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariBentuk',
			data: "idmaterial=" + idmaterial + "&id=" + id + "&sts_sheet=" + sts_sheet,
			dataType: 'json',
			success: function(result) {
				$("#bentuk_" + id).html(result.html);
				if (result.id_bentuk == 'B2000002') {

					var qty_sheet_col = $('#tr_thead th:nth-child(6)').text();
					var weight_sheet_col = $('#tr_thead th:nth-child(7)').text();
					if (qty_sheet_col !== 'Qty Sheet' && weight_sheet_col !== 'Weight / Sheet') {
						var newTh = $('<th width="250px">').text('Qty Sheet');
						var newTh2 = $('<th width="250px">').text('Weight / Sheet');

						$('#tr_thead th:nth-child(6)').before(newTh);
						$('#tr_thead th:nth-child(7)').before(newTh2);

					}
					$('#tr_' + id).each(function() {
						$(this).find('td').eq(3).css('visibility', 'hidden');
						$(this).find('td').eq(4).css('visibility', 'hidden');
						if ($(this).find('#dt_qtysheet_' + result.no).length < 1) {
							$(this).find('td').eq(5).after('<td>');
							$(this).find('td').eq(6).html(result.input_qty_sheet);
							$(this).find('td').eq(6).after('<td>');
							$(this).find('td').eq(7).html(result.input_weight_sheet);
						}
					});
				} else {
					if (cariSheet() < 1) {
						$('th:contains("Qty Sheet")').remove();
						$('th:contains("Weight / Sheet")').remove();
					}

					$('#tr_' + id).each(function() {
						$(this).find('td').eq(3).css('visibility', 'visible');
						$(this).find('td').eq(4).css('visibility', 'visible');
						if (sts_sheet > 0) {
							$(this).find('td').eq(5).after('<td>');
							$(this).find('td').eq(6).html(result.input_qty_sheet);
							$(this).find('td').eq(6).after('<td>');
							$(this).find('td').eq(7).html(result.input_weight_sheet);
						}
					});

				}

				$('#dt_width_' + id).autoNumeric('set', result.width);
				$('#dt_length_' + id).autoNumeric('set', result.length);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariIdBentuk',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#idbentuk_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariSupplier',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#supplier_" + id).html(html);
				$('.select2').select2();
			}
		});
	}

	function HapusItem(id) {
		$('#data_request #tr_' + id).remove();

		if (cariSheet() < 1) {
			$('th:contains("Qty Sheet")').remove();
			$('th:contains("Weight / Sheet")').remove();
		}

		var bentuk = $('#dt_bentuk_' + id).val();
		if (bentuk !== 'SHEET') {
			$(this).find('td').eq(3).css('visibility', 'visible');
			$(this).find('td').eq(4).css('visibility', 'visible');
			if ($(this).find('#dt_qtysheet_' + result.no).length > 0) {
				$('#tr_' + id + ' td:nth-child(7), #tr_' + id + ' td:nth-child(8)').remove();
			}
		}
	}

	function get_num(nilai = null) {
		if (nilai !== '' && nilai !== null) {
			nilai = nilai.split(',').join('');
			if (isNaN(nilai)) {
				nilai = 0;
			} else {
				nilai = parseFloat(nilai);
			}
		} else {
			nilai = 0;
		}

		return nilai;
	}

	function hitung_sheet(id) {
		var qty_sheet = get_num($('#dt_qtysheet_' + id).val());
		var weight_sheet = get_num($('#dt_weightsheet_' + id).val());

		var total_weight = (weight_sheet * qty_sheet);

		$('#dt_totalweight_' + id).autoNumeric('set', total_weight);
	}
</script>