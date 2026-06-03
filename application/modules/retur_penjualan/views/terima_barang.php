<?php
$tanggal = date('Y-m-d');
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="row">
					<center><label>
							<h3>Retur Penjualan</h3>
						</label></center>

					<!-- Row 1: No. Dokumen & Tanggal -->
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>No. Dokumen</label>
								</div>
								<div class="col-md-8">
									<input type="text" class="form-control" id="no_dokumen" value="(Auto Generate)" readonly>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>Tanggal Retur</label>
								</div>
								<div class="col-md-8">
									<input type="date" class="form-control" id="tgl_penawaran" value="<?= $tanggal ?>" name="tgl_penawaran" required>
								</div>
							</div>
						</div>
					</div>

					<!-- Row 2: Customer & No. Penawaran -->
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>Customer</label>
								</div>
								<div class="col-md-8">
									<select id="id_customerx" name="id_customerx" class="form-control select" required>
										<option value="">--Pilih--</option>
										<?php foreach ($results['customer'] as $cust) { ?>
											<option value="<?= $cust->id_customer ?>"><?= strtoupper(strtolower($cust->name_customer)) ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>No. DO</label>
								</div>
								<div class="col-md-8">
									<select id="id_do" name="id_delivery_order" class="form-control select" required>
										<option value="">--Pilih--</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Hidden inputs for customer & DO -->
					<input type="hidden" id="id_customer" name="id_customer" value="">
					<input type="hidden" id="nama_customer" name="nama_customer" value="">
					<input type="hidden" id="no_do" name="no_do" value="">

					<!-- Row 3: No. PO & Kompensasi -->
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>No. PO</label>
								</div>
								<div class="col-md-8">
									<input type="text" class="form-control" id="no_po" name="no_po">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>Kompensasi</label>
								</div>
								<div class="col-md-8">
									<select id="kompensasi" name="kompensasi" class="form-control select" required>
										<!-- <option value="brg">Ganti Barang</option> -->
										<option value="htg">Potong Hutang</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Row 4: Keterangan Retur & Ganti Material -->
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>Keterangan Retur</label>
								</div>
								<div class="col-md-8">
									<textarea class="form-control" id="note" name="note" rows="2"></textarea>
								</div>
							</div>
						</div>
						<!-- <div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label>Ganti Material</label>
								</div>
								<div class="col-md-8">
									<select id="ganti" name="ganti" class="form-control select">
										<option value="" selected>None</option>
										<option value="finisgood">Finishgood</option>
										<option value="produksi">Produksi</option>
									</select>
								</div>
							</div>
						</div> -->
					</div>

					<!-- Tabel Material (Langsung Tampil setelah pilih DO) -->
					<div class="col-sm-12" style="margin-top:15px; overflow-x:auto;">
						<table class='table table-bordered table-striped'>
							<thead>
								<tr class='bg-blue'>
									<th width='5%'>ID Material</th>
									<th width='10%'>No. DO</th>
									<th width='15%'>Nama Material</th>
									<th width='10%'>Lot Number</th>
									<th width='10%'>Gudang</th>
									<th width='10%'>Customer Titipan</th>
									<th width='10%'>Harga Deal</th>
									<th width='10%'>Total Kirim (Kg)</th>
									<th width='10%'>Qty Sheet</th>
									<th width='5%'>Retur<br><input type='checkbox' id='chk_retur_all' onclick='checkAllRetur()'></th>
									<th width='5%'>Action</th>
								</tr>
							</thead>
							<tbody id='data_material'>
								<tr>
									<td colspan="11" class="text-center">Silakan pilih DO terlebih dahulu.</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Footer: Total Retur -->
					<div class="col-sm-12" style="margin-top:15px;">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label><strong>Total Retur (Kg)</strong></label>
								</div>
								<div class="col-md-8">
									<input type="text" class="form-control" id="total_retur" value="0" readonly>
								</div>
							</div>
						</div>
					</div>

					<!-- Buttons: Simpan & Kembali -->
					<div class="col-sm-12" style="margin-top:10px;">
						<center>
							<button type="submit" class="btn btn-success btn-sm" id="simpan-com"><i class="fa fa-save"></i> Simpan</button>
							<a class="btn btn-danger btn-sm" href="<?= base_url('/retur_penjualan/incoming_retur/') ?>">Kembali</a>
						</center>
					</div>

				</div>
			</div>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	var spk_counter = 0;

	$(document).ready(function() {
		$('.select').select2();

		// Event: Customer change → update No. DO dropdown
		$(document).on('change', '#id_customerx', function(e) {
			e.preventDefault();
			var id_customer = this.value;
			var selected_text = $('#id_customerx option:selected').text();
			$('#id_customer').val(id_customer);
			$('#nama_customer').val(selected_text.trim());

			// Reset DO and table
			$("#id_do").html('<option value="">--Pilih--</option>');
			$('#no_do').val('');
			$('#data_material').html('<tr><td colspan="11" class="text-center">Silakan pilih DO terlebih dahulu.</td></tr>');
			hitungTotalRetur();

			if (id_customer) {
				$.ajax({
					url: siteurl + 'retur_penjualan/get_do_by_customer',
					cache: false,
					type: "POST",
					data: "id_customer=" + id_customer,
					dataType: "json",
					success: function(data) {
						$("#id_do").html(data.option).trigger("change");
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Timed Out ...',
							type: "warning",
							timer: 5000
						});
					}
				});
			}
		});

		// Event: DO change → load materials
		$(document).on('change', '#id_do', function(e) {
			var id_do = $(this).val();
			if (id_do) {
				var text_do = $('#id_do option:selected').text();
				$('#no_do').val(text_do);
				TambahMaterialRetur(id_do, text_do);
			} else {
				$('#no_do').val('');
				$('#data_material').html('<tr><td colspan="11" class="text-center">Silakan pilih DO terlebih dahulu.</td></tr>');
				hitungTotalRetur();
			}
		});

		// Event: Total Kirim change/keyup → recalculate Total Retur
		$(document).on('change keyup', '.total_kirim', function() {
			hitungTotalRetur();
		});

		// Event: Checkbox Retur change → recalculate Total Retur
		$(document).on('change', '.chk_retur', function() {
			hitungTotalRetur();
		});

		// Event: Gudang Select2 change → enable/disable Customer Titipan
		$(document).on('change', '.select2_gudang', function() {
			var $row = $(this).closest('tr');
			var gudang_val = $(this).val();
			var $customer = $row.find('.select2_customer');
			if (gudang_val == '3') {
				$customer.prop('disabled', false);
			} else {
				$customer.val('').prop('disabled', true);
			}
			// Re-trigger Select2 to reflect disabled state
			$customer.trigger('change.select2');
		});

		// Event: Simpan button click → validate and save
		$('#simpan-com').click(function(e) {
			e.preventDefault();

			// Validasi 1: Cek apakah ada checkbox yang dicentang
			if ($('.chk_retur:checked').length == 0) {
				swal({
					title: "Warning!",
					text: "Centang barang yang akan diretur terlebih dahulu!",
					type: "warning",
					timer: 3000
				});
				return false;
			}

			// Validasi 2: Cek field header wajib
			var customer = $('#id_customerx').val();
			var tanggal = $('#tgl_penawaran').val();
			if (!customer || !tanggal) {
				swal({
					title: "Warning!",
					text: "Form Tidak Boleh Kosong (Customer & Tanggal wajib diisi)",
					type: "warning",
					timer: 3000
				});
				return false;
			}

			// Validasi 3: Cek Qty Sheet untuk material sheet yang dicentang
			var sheet_empty = false;
			$('.chk_retur:checked').each(function() {
				var row = $(this).closest('tr');
				var qty_sheet_input = row.find('.qty_sheet');
				if (qty_sheet_input.length > 0) {
					var val = qty_sheet_input.val();
					if (!val || val == '' || val == '0') {
						sheet_empty = true;
					}
				}
			});
			if (sheet_empty) {
				swal({
					title: "Warning!",
					text: "Input QTY Sheet untuk barang Sheet masih ada yang kosong!",
					type: "warning",
					timer: 3000
				});
				return false;
			}

			// Semua validasi lolos → konfirmasi
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
						var baseurl = siteurl + 'retur_penjualan/SaveRetur';
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
									window.location.href = base_url + active_controller + '/incoming_retur';
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

	// Function: Tambah Material - load material rows for selected DO
	function TambahMaterialRetur(id_do, text_do) {
		$.ajax({
			url: siteurl + 'retur_penjualan/TambahMaterialRetur',
			type: "GET",
			data: {
				id_delivery_order: id_do
			},
			success: function(html) {
				if(html.trim() == '') {
					$('#data_material').html('<tr><td colspan="11" class="text-center">Tidak ada material untuk diretur.</td></tr>');
				} else {
					$('#data_material').html(html);
					// Set No DO text for all loaded rows
					$('#data_material .text-do').text(text_do);
					// Initialize Select2 for gudang and customer dropdowns
					$('#data_material .select2_gudang').select2({ width: '100%' });
					$('#data_material .select2_customer').select2({ width: '100%' });
					// Initialize autoNumeric
					$('#data_material .autoNumeric').autoNumeric('init');
				}
				hitungTotalRetur();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000
				});
			}
		});
	}

	// Function: Hapus material row
	function HapusRow(no) {
		$('#tr_material_' + no).remove();
		hitungTotalRetur();
	}

	// Function: Gudang change → enable/disable Customer Titipan
	function gudangChange(no) {
		var gudang_val = $('#dp_gudang_' + no).val();
		if (gudang_val == '3') {
			$('#dp_customer_' + no).prop('disabled', false);
		} else {
			$('#dp_customer_' + no).prop('disabled', true).val('').trigger('change.select2');
		}
	}

	// Function: Hitung Total Retur (sum of checked rows' total_kirim)
	function hitungTotalRetur() {
		var total = 0;
		$('.chk_retur:checked').each(function() {
			var row = $(this).closest('tr');
			var total_kirim_str = row.find('.total_kirim').val() || '0';
			var total_kirim = parseFloat(total_kirim_str.replace(/,/g, '')) || 0;
			total += total_kirim;
		});
		$('#total_retur').val(total.toFixed(2));
	}

	// Function: Check/Uncheck all retur checkboxes
	function checkAllRetur() {
		var isChecked = $('#chk_retur_all').is(':checked');
		$('#data_material .chk_retur').prop('checked', isChecked);
		hitungTotalRetur();
	}
</script>