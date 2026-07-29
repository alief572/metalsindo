<?php
$ENABLE_ADD     = has_permission('Trans_inquiry.Add');
$ENABLE_MANAGE  = has_permission('Trans_inquiry.Manage');
$ENABLE_VIEW    = has_permission('Trans_inquiry.View');
$ENABLE_DELETE  = has_permission('Trans_inquiry.Delete');
$tanggal = date('Y-m-d');
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>SPK MARKETING SLITTING</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">NO.SPK</label>
									</div>
									<div class="col-md-8" hidden>
										<input type="text" class="form-control" id="id_spkmarketing" required name="id_spkmarketing" readonly placeholder="No.CRCL">
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="No.SPK">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" id="tgl_penawaran" value="<?= $tanggal ?>" onkeyup required name="tgl_penawaran" readonly>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">Customer</label>
									</div>
									<div class="col-md-8">
										<select id="id_customerx" name="id_customerx" class="form-control select customerx" required>
											<option value="">--Pilih--</option>
											<?php foreach ($results['customer'] as $penawaran) { ?>
												<option value="<?= $penawaran->id_customer ?>"><?= strtoupper(strtolower($penawaran->name_customer)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row" id="slot_customer">
									<div class="col-md-4">
										<label for="customer">Customer</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="nama_customer" onkeyup required name="nama_customer" readonly>
									</div>
									<div class="col-md-8" hidden>
										<input type="text" class="form-control" id="id_customer" onkeyup required name="id_customer" readonly>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">No. Penawaran</label>
									</div>
									<div class="col-md-8">
										<select id="no_penawaran" name="no_penawaran" class="form-control select" onchange="get_produk()" required>
											<option value="">--Pilih--</option>
											<?php foreach ($results['penawaran'] as $penawaran) { ?>
												<option value="<?= $penawaran->no_penawaran ?>"><?= strtoupper(strtolower($penawaran->no_surat)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">No PO</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_po" required name="no_po">
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">Sample</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="sample" required name="sample">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">Tgl PO</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control datepicker" id="tgl_po" required name="tgl_po" readonly>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">Date Plan By Customer</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control datepicker" id="plan_cust" required name="plan_cust" readonly>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="no_penawaran">Note</label>
									</div>
									<div class="col-md-8">
										<textarea class="form-control" id="note" required name="note" rows='2'></textarea>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row" id="slot_tipe">
									<div class="col-md-4">
										<label for="no_penawaran">Tipe</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control datepicker" id="tipe" required name="tipe" readonly>
									</div>
								</div>
							</div>
						</div>


						<div class="col-sm-12">
							<div class="form-group row">
								<div style="overflow-x: auto; width: 100%;">
									<table class='table table-bordered table-striped' style='min-width: 1400px; white-space: nowrap;'>
										<thead>
											<tr class='bg-blue'>
												<th style="min-width:250px;">Nama Material</th>
												<th style="min-width:80px;">Thickness</th>
												<th style="min-width:80px;">Width</th>
												<th style="min-width:80px;">Length</th>
												<th style="min-width:100px;">Part Number</th>
												<th style="min-width:120px;">Harga Penawaran</th>
												<th style="min-width:120px;">Harga Deal / Kg</th>
												<th style="min-width:100px;">Disc</th>
												<th style="min-width:80px;">Qty (KG)</th>
												<th style="min-width:80px;" hidden>Weight / Coil</th>
												<th style="min-width:80px;" hidden>Total Wight</th>
												<th style="min-width:130px;">Total Harga</th>
												<th style="min-width:120px;">Delivery Date</th>
												<th style="min-width:100px;">CRCL</th>
												<th style="min-width:120px;">Keterangan</th>
												<th style="min-width:50px;">Deal</th>
											</tr>
										</thead>
										<tbody id="list_penawaran_slot">
										</tbody>
										<tfoot>
											<tr>
												<td colspan="11" style="text-align:right;"><strong>Total Harga</strong></td>
												<td colspan="5">
													<input type="text" class="form-control" id="sum_total_harga" readonly value="0">
												</td>
											</tr>
											<tr>
												<td colspan="11" style="text-align:right;"><strong>Discount</strong></td>
												<td colspan="5">
													<input type="text" class="form-control" id="footer_discount" name="total_discount" value="0" onchange="onFooterDiscountInput()">
												</td>
											</tr>
											<tr>
												<td colspan="11" style="text-align:right;"><strong>Grand Total</strong></td>
												<td colspan="5">
													<input type="text" class="form-control" id="grand_total" readonly value="0">
												</td>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
						<center>
							<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
							<a class="btn btn-danger btn-sm" href="<?= base_url('/spk_marketing/') ?>" title="Edit">Kembali</a>
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
		$('.select').select2({
			width: '100%'
		});
		$('.datepicker').datepicker();

		$(document).on('change', '#id_customerx', function(e) {
			e.preventDefault();
			$.ajax({
				url: siteurl + 'spk_marketing/get_penawaran_slitting',
				cache: false,
				type: "POST",
				data: "id=" + this.value,
				dataType: "json",
				success: function(data) {
					$("#no_penawaran").html(data.option).trigger("chosen:updated");
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

		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID			
		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var image = $('#image').val();
			var idtype = $('#inventory_1').val();

			if ($('[type=checkbox]:checked').length == 0) {
				swal({
					title: "Warning!",
					text: "Centang deal terlebih dahulu !",
					type: "warning",
					timer: 3000
				});
				return false;
			}

			// Validasi diskon tidak boleh melebihi total harga
			var sumTotalHarga = 0;
			$('[id^="dp_tharga_"]').each(function() {
				var val = parseFloat($(this).val()) || 0;
				sumTotalHarga += val;
			});
			var totalDiscount = unformatNominal($('#footer_discount').val());
			if (totalDiscount > sumTotalHarga) {
				swal({
					title: "Warning!",
					text: "Total discount tidak boleh melebihi total harga!",
					type: "warning",
					timer: 5000
				});
				return false;
			}

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
						// Unformat semua nominal sebelum submit
						$('#footer_discount').val(unformatNominal($('#footer_discount').val()));
						$('.nominal-format').each(function() {
							$(this).val(unformatNominal($(this).val()));
						});
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + 'spk_marketing/SaveNewHeader';
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

	function get_produk() {
		var no_penawaran = $("#no_penawaran").val();

		$.ajax({
			type: "GET",
			url: siteurl + 'spk_marketing/GetCustomer',
			data: "no_penawaran=" + no_penawaran,
			success: function(html) {
				$("#slot_customer").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'spk_marketing/GetPenawaran',
			data: "no_penawaran=" + no_penawaran,
			success: function(html) {
				$("#list_penawaran_slot").html(html);
			}
		});

		$.ajax({
			type: "GET",
			url: siteurl + 'spk_marketing/GetTipe',
			data: "no_penawaran=" + no_penawaran,
			success: function(html) {
				$("#slot_tipe").html(html);
			}
		});
	}

	function get_lebar() {
		var id_produk = $("#id_produk").val();
		var lebar_coil = $("#lebar_coil").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetStock',
			data: "id_produk=" + id_produk + "&lebar_coil=" + lebar_coil,
			success: function(html) {
				$("#stock_slot").html(html);
			}
		});
	}

	var discountMode = ''; // 'per_item' or 'keseluruhan'

	function formatNominal(angka) {
		var number_string = angka.toString().replace(/[^,\d]/g, ''),
			split = number_string.split(','),
			sisa = split[0].length % 3,
			rupiah = split[0].substr(0, sisa),
			ribuan = split[0].substr(sisa).match(/\d{3}/gi);
		if (ribuan) {
			var separator = sisa ? '.' : '';
			rupiah += separator + ribuan.join('.');
		}
		rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
		return rupiah;
	}

	function unformatNominal(str) {
		if (!str) return 0;
		str = str.toString().trim();
		var hasComma = str.indexOf(',') > -1;
		var hasDot = str.indexOf('.') > -1;
		if (hasComma && hasDot) {
			return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
		} else if (hasComma && !hasDot) {
			return parseFloat(str.replace(',', '.')) || 0;
		} else if (hasDot && !hasComma) {
			var parts = str.split('.');
			if (parts.length == 2 && parts[1].length <= 2) {
				return parseFloat(str) || 0;
			} else {
				return parseFloat(str.replace(/\./g, '')) || 0;
			}
		}
		return parseFloat(str) || 0;
	}

	function AksiDetail(id) {
		var hgdeal = unformatNominal($('#dp_hgdeal_' + id).val());
		var qty = unformatNominal($('#dp_qty_' + id).val());
		var weight = $('#dp_weight_' + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'spk_marketing/totalw',
			data: "hgdeal=" + hgdeal + "&qty=" + qty + "&weight=" + weight + "&id=" + id,
			success: function(html) {
				$('#total_weight_' + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'spk_marketing/totalhg',
			data: "hgdeal=" + hgdeal + "&qty=" + qty + "&weight=" + weight + "&id=" + id,
			success: function(html) {
				$('#total_harga_' + id).html(html);
				recalculateFooter();
			}
		});
	}

	function onItemDiscountInput(id) {
		var el = $('#dp_discount_' + id);
		var raw = el.val().replace(/[^0-9]/g, '');
		el.val(formatNominal(raw));
		discountMode = 'per_item';
		var totalDiscount = 0;
		$('[id^="dp_discount_"]').each(function() {
			var val = unformatNominal($(this).val());
			totalDiscount += val;
		});
		$('#footer_discount').val(formatNominal(totalDiscount.toString()));
		recalculateFooter();
	}

	function onFooterDiscountInput() {
		var el = $('#footer_discount');
		var raw = el.val().replace(/[^0-9]/g, '');
		el.val(formatNominal(raw));
		discountMode = 'keseluruhan';
		$('[id^="dp_discount_"]').each(function() {
			$(this).val('0');
		});
		recalculateFooter();
	}

	function recalculateFooter() {
		var sumTotalHarga = 0;
		$('[id^="dp_tharga_"]').each(function() {
			var val = parseFloat($(this).val()) || 0;
			sumTotalHarga += val;
		});
		$('#sum_total_harga').val(formatNominal(sumTotalHarga.toFixed(2).replace('.', ',')));
		var discount = unformatNominal($('#footer_discount').val());
		var grandTotal = sumTotalHarga - discount;
		$('#grand_total').val(formatNominal(grandTotal.toFixed(2).replace('.', ',')));
	}

	// Auto-format semua input nominal saat user ketik
	$(document).on('keyup', '.nominal-format:not([readonly])', function() {
		var val = this.value.replace(/[^0-9,]/g, '');
		var parts = val.split(',');
		parts[0] = parts[0].replace(/\./g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
		this.value = parts.join(',');
	});

	function HitungPisau(id) {
		var qty = $('#stok_qty_' + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/HitungPisau',
			data: "qty=" + qty + "&id=" + id,
			success: function(html) {
				$('#pisau_' + id).html(html);
			}
		});
	}

	function TambahItem(id) {
		var idstk = $('#stok_idstk_' + id).val();
		var lotno = $('#stok_lotno_' + id).val();
		var namamaterial = $('#stok_namamaterial_' + id).val();
		var weight = $('#stok_weight_' + id).val();
		var density = $('#stok_density_' + id).val();
		var hasilpanjang = $('#stok_hasilpanjang_' + id).val();
		var width = $('#stok_width_' + id).val();
		var lebarcc = $('#stok_lebarcc_' + id).val();
		var jumlahcc = $('#stok_jumlahcc_' + id).val();
		var sisapotongan = $('#stok_sisapotongan_' + id).val();
		var qtystock = $('#stok_qty_' + id).val();
		var jumlahpisau = $('#stok_jmlpisau_' + id).val();
		var total_panjang = $("#total_panjang").val();
		var jml_pisau = $("#jml_pisau").val();
		var jml_mother = $("#jml_mother").val();
		var total_berat = $("#total_berat").val();
		var thickness = $("#thickness").val();
		var qty = $("#qty").val();
		var jumlah = $('#used_slot').find('tr').length;
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/HitungTPanjang',
			data: "hasilpanjang=" + hasilpanjang + "&total_panjang=" + total_panjang,
			success: function(html) {
				$("#tpanjang_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/HitungJPisau',
			data: "jumlahpisau=" + jumlahpisau + "&jml_pisau=" + jml_pisau,
			success: function(html) {
				$("#jpisau_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/HitungJmother',
			data: "jml_mother=" + jml_mother,
			success: function(html) {
				$("#mother_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/HitungTBerat',
			data: "hasilpanjang=" + hasilpanjang + "&total_panjang=" + total_panjang + "&thickness=" + thickness + "&lebarcc=" + lebarcc + "&density=" + density,
			success: function(html) {
				$("#tberat_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetUsed',
			data: "idstk=" + idstk + "&lotno=" + lotno + "&namamaterial=" + namamaterial + "&jumlah=" + jumlah + "&weight=" + weight + "&density=" + density + "&hasilpanjang=" + hasilpanjang + "&width=" + width + "&lebarcc=" + lebarcc + "&jumlahcc=" + jumlahcc + "&sisapotongan=" + sisapotongan + "&qtystock=" + qtystock + "&jumlahpisau=" + jumlahpisau,
			success: function(html) {
				$("#used_slot").append(html);
			}
		});
	}

	function get_properties() {
		var id_produk = $("#id_produk").val();
		var lebar_coil = $("#lebar_coil").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetMaterial',
			data: "id_produk=" + id_produk,
			success: function(html) {
				$("#material_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetThickness',
			data: "id_produk=" + id_produk,
			success: function(html) {
				$("#thickness_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetDensity',
			data: "id_produk=" + id_produk,
			success: function(html) {
				$("#density_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetSurface',
			data: "id_produk=" + id_produk,
			success: function(html) {
				$("#surface_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetPotongan',
			data: "id_produk=" + id_produk,
			success: function(html) {
				$("#potongan_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'penawaran_shearing/GetStock',
			data: "id_produk=" + id_produk + "&lebar_coil=" + lebar_coil,
			success: function(html) {
				$("#stock_slot").html(html);
			}
		});

	}

	function DelItem(id) {
		$('#data_barang #tr_' + id).remove();

	}
</script>