<?php
$retur = $results['retur'];
$detail = $results['detail'];
$no_cn = isset($results['no_cn']) ? $results['no_cn'] : ($retur->no_cn ?: '');
$persen_ppn = isset($results['persen_ppn']) ? $results['persen_ppn'] : 11;
$tanggal = date('Y-m-d');
$is_closed = ($retur->status_cn == 'CLOSED');
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<input type="hidden" name="id_retur" id="id_retur" value="<?= $retur->id_retur ?>">
			<div class="col-sm-12">
				<div class="row">
					<center>
						<label>
							<h3>Credit Note - Input Harga Retur</h3>
						</label>
					</center>

					<!-- Header Info -->
					<div class="col-sm-12" style="margin-bottom: 20px;">
						<div class="row">
							<div class="col-sm-6">
								<table class="table table-condensed">
									<tr>
										<th width="30%">No. Retur</th>
										<td>: <?= $retur->no_retur ?></td>
									</tr>
									<tr>
										<th style="vertical-align: middle;">No. Credit Note</th>
										<td>
											<div class="input-group input-group-sm" style="max-width: 250px;">
												<input type="text" class="form-control" name="no_cn" id="no_cn" value="<?= $no_cn ?>" <?= $is_closed ? 'readonly' : '' ?> style="font-weight: bold;">
												<?php if (!$is_closed) { ?>
													<span class="input-group-btn">
														<button type="button" class="btn btn-default" title="Default System" onclick="resetDefaultCN('<?= $no_cn ?>')"><i class="fa fa-refresh"></i></button>
													</span>
												<?php } ?>
											</div>
										</td>
									</tr>
									<tr>
										<th>Tanggal Retur</th>
										<td>: <?= date('d F Y', strtotime($retur->tgl_retur)) ?></td>
									</tr>
									<tr>
										<th>Customer</th>
										<td>: <?= $retur->nama_customer ?></td>
									</tr>
								</table>
							</div>
							<div class="col-sm-6">
								<table class="table table-condensed">
									<tr>
										<th width="30%">No. DO</th>
										<td>: <?= $retur->no_do ?: '-' ?></td>
									</tr>
									<tr>
										<th>No. SJ Customer</th>
										<td>: <?= $retur->no_po ?: '-' ?></td>
									</tr>
									<tr>
										<th>Kompensasi</th>
										<td>: <?= ($retur->kompensasi == 'brg') ? 'Ganti Barang' : 'Potong Hutang' ?></td>
									</tr>
									<tr>
										<th>Keterangan</th>
										<td>: <?= $retur->note ?: '-' ?></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<!-- Tabel Detail Material -->
					<div class="col-sm-12" style="overflow-x:auto;">
						<table class='table table-bordered table-striped'>
							<thead>
								<tr class='bg-blue'>
									<th>ID Material</th>
									<th>Nama Material</th>
									<th>Lot Number</th>
									<th>Thickness</th>
									<th>Width</th>
									<th>Length</th>
									<th>Total Kirim (Kg)</th>
									<th>Qty Sheet</th>
									<th width="12%">Harga Invoice (Ref)</th>
									<th width="12%">Harga Deal CN</th>
									<th width="14%">Total Harga</th>
								</tr>
							</thead>
							<tbody id='data_material'>
								<?php
								$grand_total = 0;
								$grand_ppn = 0;

								foreach ($detail as $dt) {
									$is_sheet = ($dt->total_sheet > 0);
									$qty_used = $is_sheet ? $dt->total_sheet : $dt->weight;
									$total_harga = $qty_used * $dt->harga_deal;
									$grand_total += $total_harga;
								?>
									<tr>
										<td><?= $dt->id_material ?></td>
										<td><?= $dt->nama ?></td>
										<td><?= $dt->lotno ?></td>
										<td><?= $dt->thickness ?></td>
										<td><?= $dt->width ?></td>
										<td><?= $dt->length ?></td>
										<td>
											<span class="val_total_kirim"><?= number_format($dt->weight, 2) ?></span>
											<input type="hidden" class="input_total_kirim" name="dt[<?= $dt->id ?>][total_kirim]" value="<?= $dt->weight ?>">
										</td>
										<td>
											<span class="val_qty_sheet"><?= $is_sheet ? number_format($dt->total_sheet) : '-' ?></span>
											<input type="hidden" class="input_qty_sheet" name="dt[<?= $dt->id ?>][qty_sheet]" value="<?= $dt->total_sheet ?>">
											<input type="hidden" class="is_sheet" value="<?= $is_sheet ? 1 : 0 ?>">
										</td>
										<td class="text-right">
											<span class="lbl_harga_invoice" style="color: #666; font-weight: 500;">
												<?= ((float)$dt->harga_invoice > 0) ? number_format($dt->harga_invoice, 2) : '-' ?>
											</span>
										</td>
										<td>
											<input type="text" class="form-control input-sm autoNumeric text-right input_harga_deal" name="dt[<?= $dt->id ?>][harga_deal]" value="<?= $dt->harga_deal ?>" <?= $is_closed ? 'readonly' : '' ?>>
										</td>
										<td class="text-right">
											<span class="lbl_total_harga" style="font-weight: bold;"><?= number_format($total_harga, 2) ?></span>
										</td>
									</tr>
								<?php } 
								$grand_ppn = ($grand_total * $persen_ppn) / 100;
								$grand_all = $grand_total + $grand_ppn;
								?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="10" class="text-right">Subtotal / Total Retur:</th>
									<th class="text-right" id="grand_total"><?= number_format($grand_total, 2) ?></th>
								</tr>
								<tr>
									<th colspan="10" class="text-right" style="vertical-align: middle;">
										<div class="form-inline pull-right">
											<label style="margin-right: 8px;">PPN (%):</label>
											<div class="input-group input-group-sm" style="width: 100px;">
												<input type="text" class="form-control text-right" id="input_persen_ppn" name="persen_ppn" value="<?= $persen_ppn ?>" <?= $is_closed ? 'readonly' : '' ?>>
												<span class="input-group-addon">%</span>
											</div>
										</div>
									</th>
									<th class="text-right" style="vertical-align: middle;" id="grand_ppn"><?= number_format($grand_ppn, 2) ?></th>
								</tr>
								<tr>
									<th colspan="10" class="text-right" style="font-size: 14px;">Grand Total Keseluruhan:</th>
									<th class="text-right" style="font-size: 14px;" id="grand_all"><?= number_format($grand_all, 2) ?></th>
								</tr>
							</tfoot>
						</table>
					</div>

					<!-- Buttons -->
					<div class="col-sm-12" style="margin-top:20px;">
						<center>
							<?php if (!$is_closed) { ?>
								<button type="button" class="btn btn-success" id="btn-simpan"><i class="fa fa-save"></i> Simpan Harga</button>
								<button type="button" class="btn btn-primary" id="btn-confirm"><i class="fa fa-check"></i> Confirm / Close CN</button>
							<?php } ?>
							<a class="btn btn-danger" href="<?= base_url('/retur_penjualan/list_retur_penjualan') ?>">Kembali</a>
						</center>
					</div>

				</div>
			</div>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var siteurl = '<?= base_url() ?>';
	var active_controller = 'retur_penjualan/';

	$(document).ready(function() {
		$('.autoNumeric').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});

		// Trigger kalkulasi saat input harga atau persentase PPN berubah
		$(document).on('keyup change', '.input_harga_deal', function() {
			hitungBaris($(this).closest('tr'));
			hitungTotal();
		});

		$(document).on('keyup change', '#input_persen_ppn', function() {
			hitungTotal();
		});

		$('#btn-simpan').click(function(e) {
			e.preventDefault();
			var no_cn = $('#no_cn').val().trim();
			if (no_cn == '') {
				swal("Warning!", "No. Credit Note tidak boleh kosong!", "warning");
				return false;
			}
			saveData('save_cn_price', 'menyimpan data harga');
		});

		$('#btn-confirm').click(function(e) {
			e.preventDefault();
			var no_cn = $('#no_cn').val().trim();
			if (no_cn == '') {
				swal("Warning!", "No. Credit Note tidak boleh kosong!", "warning");
				return false;
			}

			// Validasi sebelum confirm
			var valid = true;
			$('.input_harga_deal').each(function() {
				var val = parseFloat($(this).val().replace(/,/g, '')) || 0;
				if (val <= 0) valid = false;
			});

			if (!valid) {
				swal("Warning!", "Semua Harga Deal harus lebih dari 0 sebelum melakukan Confirm!", "warning");
				return false;
			}

			swal({
					title: "Are you sure?",
					text: "Setelah Confirm, data harga akan TERKUNCI dan CN bisa diprint!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-primary",
					confirmButtonText: "Ya, Confirm!",
					cancelButtonText: "Batal",
					closeOnConfirm: false
				},
				function(isConfirm) {
					if (isConfirm) {
						saveData('confirm_cn', 'melakukan Confirm');
					}
				}
			);
		});
	});

	function resetDefaultCN(val) {
		$('#no_cn').val(val);
	}

	function hitungBaris(tr) {
		var harga_deal = parseFloat(tr.find('.input_harga_deal').val().replace(/,/g, '')) || 0;
		var is_sheet = parseInt(tr.find('.is_sheet').val()) || 0;
		
		var qty = 0;
		if (is_sheet == 1) {
			qty = parseFloat(tr.find('.input_qty_sheet').val().replace(/,/g, '')) || 0;
		} else {
			qty = parseFloat(tr.find('.input_total_kirim').val().replace(/,/g, '')) || 0;
		}

		var total_harga = qty * harga_deal;
		tr.find('.lbl_total_harga').text(number_format(total_harga, 2));
	}

	function hitungTotal() {
		var grand_total = 0;
		var persen_ppn = parseFloat($('#input_persen_ppn').val().replace(/,/g, '')) || 0;
		
		$('#data_material tr').each(function() {
			var $tr = $(this);
			if($tr.find('.input_harga_deal').length > 0) {
				var harga_deal = parseFloat($tr.find('.input_harga_deal').val().replace(/,/g, '')) || 0;
				var is_sheet = parseInt($tr.find('.is_sheet').val()) || 0;
				
				var qty = 0;
				if (is_sheet == 1) {
					qty = parseFloat($tr.find('.input_qty_sheet').val().replace(/,/g, '')) || 0;
				} else {
					qty = parseFloat($tr.find('.input_total_kirim').val().replace(/,/g, '')) || 0;
				}

				grand_total += (qty * harga_deal);
			}
		});

		var grand_ppn = (grand_total * persen_ppn) / 100;
		var grand_all = grand_total + grand_ppn;

		$('#grand_total').text(number_format(grand_total, 2));
		$('#grand_ppn').text(number_format(grand_ppn, 2));
		$('#grand_all').text(number_format(grand_all, 2));
	}

	function saveData(action, context) {
		var formData = new FormData($('#data-form')[0]);
		$.ajax({
			url: siteurl + active_controller + action,
			type: "POST",
			data: formData,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function(data) {
				if (data.status == 1) {
					swal({
						title: "Berhasil!",
						text: data.pesan,
						type: "success",
						timer: 3000
					});
					setTimeout(function() {
						window.location.reload();
					}, 1500);
				} else {
					swal({
						title: "Gagal!",
						text: data.pesan,
						type: "error"
					});
				}
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Terjadi kesalahan sistem saat ' + context,
					type: "warning"
				});
			}
		});
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
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
</script>
