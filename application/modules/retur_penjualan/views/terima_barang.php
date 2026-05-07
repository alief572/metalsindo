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
									<label>No. Penawaran</label>
								</div>
								<div class="col-md-8">
									<select id="no_penawaran" name="no_penawaran" class="form-control select" required>
										<option value="">--Pilih--</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Hidden inputs for customer -->
					<input type="hidden" id="id_customer" name="id_customer" value="">
					<input type="hidden" id="nama_customer" name="nama_customer" value="">

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
										<option value="brg">Ganti Barang</option>
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
						<div class="col-sm-6">
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
						</div>
					</div>

					<!-- Add SPK Button -->
					<div class="col-sm-12" style="margin-top:10px; margin-bottom:10px;">
						<button type="button" class="btn btn-sm btn-success" id="btn_add_spk"><i class="fa fa-plus"></i> Add SPK</button>
					</div>

					<!-- SPK Container (AJAX content area) -->
					<div class="col-sm-12" id="Form_Spk">
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

<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	var spk_counter = 0;

	$(document).ready(function() {
		$('.select').select2();

		// Event: Customer change → update No. Penawaran dropdown
		$(document).on('change', '#id_customerx', function(e) {
			e.preventDefault();
			var id_customer = this.value;
			var selected_text = $('#id_customerx option:selected').text();
			$('#id_customer').val(id_customer);
			$('#nama_customer').val(selected_text.trim());

			$.ajax({
				url: siteurl + 'spk_marketing/get_penawaran',
				cache: false,
				type: "POST",
				data: "id=" + id_customer,
				dataType: "json",
				success: function(data) {
					$("#no_penawaran").html(data.option).trigger("change");
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
		});

		// Event: Add SPK button click
		$('#btn_add_spk').click(function() {
			// Validasi: Customer harus sudah dipilih
			var id_customer = $('#id_customerx').val();
			if (!id_customer || id_customer == '') {
				swal({
					title: "Warning!",
					text: "Pilih Customer terlebih dahulu!",
					type: "warning",
					timer: 3000
				});
				return;
			}

			spk_counter++;
			var current_counter = spk_counter;
			$.ajax({
				url: siteurl + 'retur_penjualan/FormSpk',
				type: "GET",
				data: {
					id: current_counter,
					id_customer: id_customer
				},
				success: function(html) {
					$('#Form_Spk').append(html);
					// Reinitialize Select2 and bind change event
					var $select = $('#dt_spk_' + current_counter);
					$select.select2().on('change', function() {
						TambahMaterial(current_counter);
					});

					// Auto-select jika hanya ada 1 SPK (selain option --Pilih--)
					var options = $select.find('option[value!=""]');
					if (options.length == 1) {
						$select.val(options.first().val()).trigger('change');
					}
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

	// Function: Tambah Material - load material rows for selected SPK
	function TambahMaterial(id) {
		var id_spkmarketing = $('#dt_spk_' + id).val();
		if (!id_spkmarketing || id_spkmarketing == '') {
			// Kosongkan tabel jika tidak ada SPK dipilih
			$('#data_material_' + id).html('');
			hitungTotalRetur();
			return;
		}

		$.ajax({
			url: siteurl + 'retur_penjualan/TambahMaterialRetur',
			type: "GET",
			data: {
				id_spkmarketing: id_spkmarketing,
				id: id
			},
			success: function(html) {
				$('#data_material_' + id).html(html);
				// Initialize Select2 for gudang and customer dropdowns
				$('#data_material_' + id + ' .select2_gudang').select2({
					width: '100%'
				});
				$('#data_material_' + id + ' .select2_customer').select2({
					width: '100%'
				});
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

	// Function: Hapus SPK block
	function HapusSpk(id) {
		$('#spk_' + id).remove();
		hitungTotalRetur();
	}

	// Function: Hapus material row
	function HapusRow(id, no) {
		$('#tr_material_' + id + '_' + no).remove();
		hitungTotalRetur();
	}

	// Function: Gudang change → enable/disable Customer Titipan
	function gudangChange(id, no) {
		var gudang_val = $('#dp_gudang_' + id + '_' + no).val();
		if (gudang_val == '3') {
			$('#dp_customer_' + id + '_' + no).prop('disabled', false);
		} else {
			$('#dp_customer_' + id + '_' + no).prop('disabled', true).val('');
		}
	}

	// Function: Hitung Total Retur (sum of checked rows' total_kirim)
	function hitungTotalRetur() {
		var total = 0;
		$('.chk_retur:checked').each(function() {
			var row = $(this).closest('tr');
			var total_kirim = parseFloat(row.find('.total_kirim').val()) || 0;
			total += total_kirim;
		});
		$('#total_retur').val(total.toFixed(2));
	}

	// Function: Check/Uncheck all retur checkboxes in a SPK block
	function checkAllRetur(id) {
		var isChecked = $('.chk_retur_all[data-spk="' + id + '"]').is(':checked');
		$('#data_material_' + id + ' .chk_retur').prop('checked', isChecked);
		hitungTotalRetur();
	}
</script>