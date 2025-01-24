<?php
$tanggal = date('Y-m-d');
foreach ($results['header'] as $header) {
}
?>

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
									<div class="col-md-8">
										<?= $header->no_surat ?>
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
										<?= $header->tanggal ?>
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
										<?= $header->requestor ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group row">
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='addmaterial();'><i class='fa fa-plus'></i>Add</button>

							</div>
							<div class="form-group row">
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<th>Material</th>
											<th>Bentuk</th>
											<th>ID</th>
											<th>OD</th>
											<th hidden>Qty (Unit)</th>
											<th hidden>Weight (Unit)</th>
											<th>Qty Sheet</th>
											<th>Weight Sheet</th>
											<th>Total Weight</th>
											<th>Width</th>
											<th>Length</th>
											<th>Supplier</th>
											<th>Tanggal Dibutuhkan</th>
											<th>Keterangan</th>
										</tr>
									</thead>
									<tbody id="data_request">
										<?php foreach ($results['detail'] as $detail) {

											$qty_sheet = ($detail->bentuk == 'SHEET') ? number_format($detail->qty_sheet) : '';
											$weight_sheet = ($detail->bentuk == 'SHEET') ? number_format($detail->weight_sheet, 2) : '';

											$suplier = $this->db->query("SELECT * FROM master_supplier WHERE id_suplier = '" . $detail->suplier . "' ")->result();
											echo "
			<tr>
			<td>" . $detail->nama_material . "</td>
			<td>" . $detail->bentuk . "</td>
			<td class='text-right'>" . number_format($detail->idameter, 2) . "</td>
			<td class='text-right'>" . number_format($detail->odameter, 2) . "</td>
			<td hidden>" . $detail->qty . "</td>
			<td hidden>" . $detail->weight . "</td>
			<td class='text-right'>" . $qty_sheet . "</td>
			<td class='text-right'>" . $weight_sheet . "</td>
			<td class='text-right'>" . number_format($detail->totalweight, 2) . "</td>
			<td class='text-right'>" . number_format($detail->width, 2) . "</td>
			<td class='text-right'>" . number_format($detail->length, 2) . "</td>
			<td>" . $suplier[0]->name_suplier . "</td>
			<td>" . $detail->tanggal . "</td>
			<td>" . $detail->keterangan . "</td>
			</tr>
			";
										} ?>
									</tbody>
								</table>
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
			}
		});
	}

	function CariProperties(id) {
		var idmaterial = $("#dt_idmaterial_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariBentuk',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#bentuk_" + id).html(html);
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
			}
		});
	}

	function HapusItem(id) {
		$('#data_request #tr_' + id).remove();

	}
</script>