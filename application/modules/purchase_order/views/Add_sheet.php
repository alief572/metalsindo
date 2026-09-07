<?php
$tanggal = date('Y-m-d');
?>

<style type="text/css">
	.po-form-banner {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px 20px;
		margin-bottom: 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-shadow: 0 2px 6px rgba(0,0,0,0.04);
	}
	.po-form-banner h3 {
		margin: 0;
		font-size: 18px;
		font-weight: 700;
		color: #205072;
	}
	.po-form-banner p {
		margin: 3px 0 0 0;
		font-size: 12px;
		color: #64748b;
	}
	.po-section-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		margin-bottom: 20px;
		box-shadow: 0 2px 6px rgba(0,0,0,0.04);
		overflow: hidden;
	}
	.po-section-header {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
		padding: 12px 20px;
	}
	.po-section-header h4 {
		margin: 0;
		font-size: 13px;
		font-weight: 700;
		color: #205072;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}
	.po-section-body {
		padding: 20px;
	}
	.form-group label {
		font-weight: 600;
		color: #334155;
		font-size: 12px;
	}
	.table-custom thead th {
		background: #205072 !important;
		color: #ffffff !important;
		font-size: 11px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		padding: 10px 8px !important;
		border: none !important;
		vertical-align: middle !important;
	}
	.table-custom tbody td {
		padding: 8px !important;
		vertical-align: middle !important;
	}
	.po-summary-box {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 18px;
	}
	.po-summary-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 10px;
		font-size: 13px;
		color: #475569;
	}
	.po-summary-item label {
		margin: 0;
		font-weight: 500;
	}
	.po-summary-item.total {
		border-top: 2px dashed #cbd5e1;
		margin-top: 12px;
		padding-top: 12px;
		font-size: 15px;
		font-weight: 700;
		color: #205072;
	}
	.po-form-actions {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-shadow: 0 2px 6px rgba(0,0,0,0.04);
		margin-bottom: 30px;
	}
</style>

<div class="po-form-banner">
	<div class="po-form-banner-title">
		<h3><i class="fa fa-pencil-square-o" style="margin-right: 8px;"></i>Buat Purchase Order (Sheet)</h3>
		<p>Silakan isi informasi supplier, detail material sheet, dan ketentuan PO di bawah ini.</p>
	</div>
	<div>
		<a href="<?= base_url('purchase_order') ?>" class="btn btn-default btn-sm" style="border-radius: 4px; font-weight: 600;">
			<i class="fa fa-arrow-left"></i>&nbsp; Kembali ke Daftar PO
		</a>
	</div>
</div>

<form id="data-form" method="post" autocomplete='off'>
	<div class="input_fields_wrap2">
		<!-- Section 1: Informasi Header & Supplier -->
		<div class="po-section-card">
			<div class="po-section-header">
				<h4><i class="fa fa-building-o" style="margin-right: 6px;"></i>Informasi Supplier & Dokumen PO</h4>
			</div>
			<div class="po-section-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_suplier">Supplier <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<select id="id_suplier" name="id_suplier" class='form-control input-md chosen-select' onchange="get_lokasi()" required>
									<option value="">-- Pilih Supplier --</option>
									<?php foreach ($results['supplier'] as $supplier) { ?>
										<option value="<?= $supplier->id_suplier ?>"><?= strtoupper(strtolower($supplier->name_suplier)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="no_pr">Pilih PR <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<select id="no_pr" name="no_pr" class='form-control input-md chosen-select' required>
									<option value="0">Pilih Supplier Terlebih Dahulu</option>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="no_surat">No. PO</label>
							</div>
							<div class="col-md-8">
								<input type="hidden" class="form-control" id="no_po" required name="no_po" readonly placeholder="ID PO">
								<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="Auto Generated saat Simpan" style="background:#f1f5f9;">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="tanggal">Tanggal PO <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control datepicker" id="tanggal" value="<?= $tanggal ?>" required name="tanggal">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="expect_tanggal">Expect Date <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control datepicker" id="expect_tanggal" required name="expect_tanggal" placeholder="YYYY-MM-DD">
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="loi">Kategori (Lokal/Import) <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8" id="ubahloi">
								<select id="loi" name="loi" class="form-control select" onchange="get_kurs()" required>
									<option value="">-- Pilih --</option>
									<option value="Import">Import</option>
									<option value="Lokal">Lokal</option>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="matauang">Mata Uang <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<select id="matauang" name="matauang" class='form-control input-md chosen-select' required>
									<?php foreach ($results['matauang'] as $supplier) { ?>
										<option value="<?= $supplier->kode ?>"><?= strtoupper(strtolower($supplier->kode)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="term">Payment Term <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="term" required name="term" placeholder="Contoh: COD / 30 Days">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="cif">Price Method <span class="text-danger">*</span></label>
							</div>
							<div class="col-md-8">
								<select id="cif" name="cif" class="form-control select" required>
									<option value="">-- Pilih --</option>
									<option value="CIF">CIF</option>
									<option value="FOB">FOB</option>
									<option value="LOCO">LOCO</option>
									<option value="DDU">DDU</option>
									<option value="FRANCO">FRANCO</option>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="delivery_date">Delivery Date</label>
							</div>
							<div class="col-md-8">
								<input type="date" name="delivery_date" id="delivery_date" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="receiving_person">PIC Penerima</label>
							</div>
							<div class="col-md-8">
								<input type="text" name="receiving_person" id="receiving_person" class="form-control" placeholder="Nama PIC">
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12" id="input_kurs"></div>
				</div>
			</div>
		</div>

		<!-- Section 2: Referensi Kurs & LME -->
		<div class="po-section-card">
			<div class="po-section-header">
				<h4><i class="fa fa-line-chart" style="margin-right: 6px;"></i>Referensi Kurs & Harga LME</h4>
			</div>
			<div class="po-section-body">
				<div class="row">
					<div class="col-md-6" id="kurs_place">
						<?php
						$hariini = date('Y-m-d');
						$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
						$tendays = date("Y-m-d", $sepuluh_hari);
						$blnnow = date('m');
						$yearnow = date('Y');
						if ($blnnow != '1') {
							$blnkmrn = $blnnow - 1;
							$yearkemaren = $yearnow;
						} else {
							$blnkmrn = "12";
							$yearnow = date('Y');
							$yearkemaren = $yearnow - 1;
						}
						$kurs = $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR'")->result();
						$kurs10hari = $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN '$tendays' AND '$hariini' AND kode_kurs='IDR'")->result();
						$kurs30hari = $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) = '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR'")->result();
						$nomkurs = isset($kurs[0]->kurs) ? $kurs[0]->kurs : 0;
						$nomkurs10 = isset($kurs10hari[0]->nominal) ? $kurs10hari[0]->nominal : 0;
						$nomkurs30 = isset($kurs30hari[0]->nominal) ? $kurs30hari[0]->nominal : 0;
						?>
						<label style="margin-bottom: 8px; font-weight: 600;">Data Kurs IDR</label>
						<table class='table table-bordered table-striped'>
							<thead>
								<tr>
									<th class="text-center">Kurs On The Spot</th>
									<th class="text-center">Kurs 10 Hari</th>
									<th class="text-center">Kurs 30 Hari</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-center"><strong>Rp. <?= number_format($nomkurs, 2) ?></strong></td>
									<td class="text-center"><strong>Rp. <?= number_format($nomkurs10, 2) ?></strong></td>
									<td class="text-center"><strong>Rp. <?= number_format($nomkurs30, 2) ?></strong></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="col-md-6" id="lme_place">
						<label style="margin-bottom: 8px; font-weight: 600;">Data Harga LME</label>
						<table class='table table-bordered table-striped'>
							<thead>
								<tr>
									<th width="30">#</th>
									<th>Komposisi</th>
									<th class="text-right">Rate H-30</th>
									<th class="text-right">Rate H-10</th>
									<th class="text-right">Rate Saat Ini</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($results['comp'])) {
									$numb3 = 0;
									foreach ($results['comp'] as $comp) {
										$numb3++;
										$id_comp = $comp->id_compotition;
										$lme_10hari = $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN '$tendays' AND '$kemarin' AND id_compotition='$id_comp'")->result();
										$lme_30hari = $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) = '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='$id_comp'")->result();
								?>
										<tr>
											<td><?= $numb3 ?></td>
											<td><?= $comp->name_compotition ?></td>
											<td class="text-right">$ <?= number_format(isset($lme_30hari[0]->nominal) ? $lme_30hari[0]->nominal : 0, 2) ?></td>
											<td class="text-right">$ <?= number_format(isset($lme_10hari[0]->nominal) ? $lme_10hari[0]->nominal : 0, 2) ?></td>
											<td class="text-right"><strong>$ <?= number_format($comp->nominal_harga, 2) ?></strong></td>
										</tr>
								<?php }
								} else { ?>
									<tr>
										<td colspan="5" class="text-center" style="color: #94a3b8;">Tidak ada data LME</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Section 3: Daftar Material PO (Sheet) -->
		<div class="po-section-card">
			<div class="po-section-header">
				<h4><i class="fa fa-cubes" style="margin-right: 6px;"></i>Daftar Item Material Purchase Order (Sheet)</h4>
			</div>
			<div class="po-section-body" style="padding: 0;">
				<div class="table-responsive">
					<table class='table table-bordered table-striped table-custom' style="margin-bottom: 0;">
						<thead>
							<tr>
								<th>Item Material</th>
								<th width='8%'>Description</th>
								<th width='7%' class="text-right">Width</th>
								<th width='7%' class="text-right">Length</th>
								<th width='7%' class="text-right">Total Weight</th>
								<th width='7%' class="text-right">Unit Price</th>
								<th width='6%' class="text-center">Disc %</th>
								<th width='6%' class="text-center">Tax %</th>
								<th width='9%' class="text-right">Amount</th>
								<th width='8%'>Note</th>
								<th width='40' class="text-center">#</th>
							</tr>
						</thead>
						<tbody id="data_request">
							<tr>
								<td colspan='11' class="text-center" style="padding: 24px; color: #94a3b8;">
									<i class="fa fa-info-circle"></i> Silakan pilih supplier dan PR di atas untuk memuat daftar material sheet.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Section 4: Catatan & Ringkasan Total Biaya -->
		<div class="row">
			<div class="col-md-7">
				<div class="po-section-card">
					<div class="po-section-header">
						<h4><i class="fa fa-sticky-note-o" style="margin-right: 6px;"></i>Catatan Khusus PO</h4>
					</div>
					<div class="po-section-body">
						<div class="form-group" style="margin-bottom: 0;">
							<label for="note_ket">Keterangan / Instruksi Tambahan</label>
							<textarea class="form-control" id="note_ket" name="note_ket" rows="4" placeholder="Tuliskan catatan khusus untuk supplier di sini jika ada..."></textarea>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-5">
				<div class="po-section-card">
					<div class="po-section-header">
						<h4><i class="fa fa-calculator" style="margin-right: 6px;"></i>Ringkasan Biaya PO</h4>
					</div>
					<div class="po-section-body">
						<div class="po-summary-box">
							<div class="po-summary-item">
								<label for="hargatotal">Sub Total</label>
								<div id="ForHarga" style="width: 170px;">
									<input readonly type="text" class="form-control text-right" id="hargatotal" required name="hargatotal" style="background:#fff; font-weight:600;">
								</div>
							</div>
							<div class="po-summary-item">
								<label for="diskontotal">Total Diskon</label>
								<div id="ForDiskon" style="width: 170px;">
									<input readonly type="text" class="form-control text-right" id="diskontotal" required name="diskontotal" style="background:#fff; color:#dc2626;">
								</div>
							</div>
							<div class="po-summary-item">
								<label for="taxtotal">Total Pajak (PPN)</label>
								<div id="ForTax" style="width: 170px;">
									<input readonly type="text" class="form-control text-right" id="taxtotal" required name="taxtotal" style="background:#fff;">
								</div>
							</div>
							<div class="po-summary-item total">
								<label for="subtotal">TOTAL ORDER</label>
								<div id="ForSum" style="width: 170px;">
									<input readonly type="text" class="form-control text-right" id="subtotal" required name="subtotal" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:15px;">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Action Buttons Footer -->
		<div class="po-form-actions">
			<a href="<?= base_url('purchase_order') ?>" class="btn btn-default" style="border-radius: 4px; font-weight: 600;">
				<i class="fa fa-arrow-left"></i>&nbsp; Batal & Kembali
			</a>
			<button type="submit" class="btn btn-success" name="save" id="simpan-com" style="border-radius: 4px; font-weight: 600; padding: 8px 24px;">
				<i class="fa fa-save"></i>&nbsp; Simpan Purchase Order
			</button>
		</div>
	</div>
</form>

<script type="text/javascript">
    //$('#input-kendaraan').hide();
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
    $(document).ready(function() {
        var max_fields2 = 10; //maximum input boxes allowed
        var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
        var add_button2 = $(".add_field_button2"); //Add button ID	

        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });

        $(document).on('change', '#id_suplier', function() {
            let id_suplier = $('#id_suplier').val();
            $.ajax({
                type: "POST",
                url: siteurl + 'purchase_order/getPR',
                data: {
                    'id_suplier': id_suplier,
                    'id_bentuk': 'B2000002'
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    $('#no_pr').html(data.option).trigger("chosen:updated");
                }
            });
        });

        $(document).on('change', '#no_pr', function() {
            let loi = $('#loi').val();
            let no_pr = $(this).val();
            $.ajax({
                type: "POST",
                url: siteurl + 'purchase_order/AddMaterial_Direct',
                data: {
                    'loi': loi,
                    'no_pr': no_pr,
                    'id_bentuk': 'B2000002'
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    $('#data_request').html(data.list_mat);
                    $(".bilangan-desimal").maskMoney();
                    $('.autoNumeric3').autoNumeric('init', {
                        vMin: 0,
                        mDec: 3
                    });
                    $('.autoNumeric').autoNumeric();
                    //$('#expect_tanggal').val(data.min_date);
                }
            });
        });

        $(document).on('click', '.hapus_baris', function() {
            $(this).parent().parent().remove();
            SumDel();
        });

        $('#simpan-com').click(function(e) {
            e.preventDefault();
            var deskripsi = $('#deskripsi').val();
            var expect_tanggal = $('#expect_tanggal').val();
            var loi = $('#loi').val();
            var term = $('#term').val();
            var cif = $('#cif').val();

            var data, xhr;
            if (expect_tanggal == '' || expect_tanggal == null || loi == '' || loi == null || term == '' || term == null || cif == '' || cif == null) {
                swal("Warning", "Form Tidak Boleh Kosong :)", "error");
                return false;
            } else {
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
                            var baseurl = siteurl + 'purchase_order/SaveNew';
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
            }
        });

    });


    function addmaterial() {
        var jumlah = $('#data_request').find('tr').length;
        var id_suplier = $("#id_suplier").val();
        var loi = $("#loi").val();
        var angka = jumlah + 1;
        if (id_suplier == '' || id_suplier == null || loi == '' || loi == null) {
            swal("Warning", "Silahkan Pilih Supplier Terlebih Dahulu :)", "error");
            return false;
        } else {
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/AddMaterial',
                data: "jumlah=" + jumlah + "&id_suplier=" + id_suplier + "&loi=" + loi,
                success: function(html) {
                    $("#data_request").append(html);
                    $(".bilangan-desimal").maskMoney();
                    $(".chosen-select").select2({
                        width: '100%'
                    });
                    $('.autoNumeric3').autoNumeric('init', {
                        vMin: 0,
                        mDec: 3
                    });
                }
            });
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/UbahImport',
                data: "loi=" + loi,
                success: function(html) {
                    $("ubahloi").html(html);
                }
            });
        }
    }

    function HitungHarga(id) {
        var dt_qty = $("#dt_qty_" + id).val();
        var dt_width = $("#dt_width_" + id).val();
        var dt_hargasatuan = $("#dt_hargasatuan_" + id).val();
        // $.ajax({
        // type:"GET",
        // url:siteurl+'purchase_order/HitungHarga',
        // data:"dt_hargasatuan="+dt_hargasatuan+"&dt_qty="+dt_qty+"&id="+id,
        // success:function(html){
        // $("#jumlahharga_"+id).html(html);
        // }
        // });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/TotalWeight',
            data: "dt_width=" + dt_width + "&dt_qty=" + dt_qty + "&id=" + id,
            success: function(html) {
                $("#totalwidth_" + id).html(html);
            }
        });
    }

    function CariPrice(id) {
        var dt_ratelme = $("#dt_ratelme_" + id).val();
        var dt_idmaterial = $("#dt_idmaterial_" + id).val();
        if (dt_idmaterial == '' || dt_idmaterial == null) {
            swal("Warning", "Silahkan Pilih Material Terlebih Dahulu :)", "error");
            return false;
        } else {
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/CariPrice',
                data: "dt_ratelme=" + dt_ratelme + "&dt_idmaterial=" + dt_idmaterial + "&id=" + id,
                success: function(html) {
                    $("#dt_alloyprice_" + id).val(html);
                }
            });
        }
    }

    function get_kurs() {
        var loi = $("#loi").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/FormInputKurs',
            data: "loi=" + loi,
            success: function(html) {
                $("#input_kurs").html(html);
            }
        });
    }


    function HitungUP(id) {
        var alloyprice = $("#dt_alloyprice_" + id).val();
        var fabcost = $("#dt_fabcost_" + id).val();
        var diskon = $("#dt_diskon_" + id).val();
        var pajak = $("#dt_pajak_" + id).val();
        var qty = $("#dt_qty_" + id).val();
        var hargasatuan = $("#dt_hargasatuan_" + id).val();
        var dt_width = $("#dt_totalwidth_" + id).val();

        var loi = $("#loi").val();
        // console.log(dt_width)
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/HitungUP',
            data: "fabcost=" + fabcost + "&alloyprice=" + alloyprice + "&hargasatuan=" + hargasatuan + "&loi=" + loi,
            success: function(html) {
                // $("#dt_hargasatuan_"+id).val(html); 
                HitAmmount(id)
            }
        });
        // $.ajax({
        // type:"GET",
        // url:siteurl+'purchase_order/Hitjumlah',
        // data:"fabcost="+fabcost+"&alloyprice="+alloyprice+"&pajak="+pajak+"&diskon="+diskon+"&qty="+qty+"&hargasatuan="+hargasatuan+"&loi="+loi+"&dt_width="+dt_width,
        // success:function(html){
        // $("#dt_jumlahharga_"+id).val(html); 
        // }
        // });		
    }

    function HitungUPIm(id) {
        var alloyprice = $("#dt_alloyprice_" + id).val();
        var fabcost = $("#dt_fabcost_" + id).val();
        var diskon = $("#dt_diskon_" + id).val();
        var pajak = $("#dt_pajak_" + id).val();
        var qty = $("#dt_qty_" + id).val();
        var hargasatuan = $("#dt_hargasatuan_" + id).val();
        var dt_width = $("#dt_totalwidth_" + id).val();

        var loi = $("#loi").val();
        // console.log(dt_width)
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/HitungUP',
            data: "fabcost=" + fabcost + "&alloyprice=" + alloyprice + "&hargasatuan=" + hargasatuan + "&loi=" + loi,
            success: function(html) {
                // $("#dt_hargasatuan_"+id).val(html); 
                $('.autoNumeric3').autoNumeric('init', {
                    vMin: 0,
                    mDec: 3
                });
                HitAmmount(id)
            }
        });
        // $.ajax({
        // type:"GET",
        // url:siteurl+'purchase_order/Hitjumlah',
        // data:"fabcost="+fabcost+"&alloyprice="+alloyprice+"&pajak="+pajak+"&diskon="+diskon+"&qty="+qty+"&hargasatuan="+hargasatuan+"&loi="+loi+"&dt_width="+dt_width,
        // success:function(html){
        // $("#dt_jumlahharga_"+id).val(html); 
        // }
        // });		
    }

    function CariProperties(id) {
        var idpr = $("#dt_idpr_" + id).val();
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariIdMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#idmaterial_" + id).html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariNamaMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#namaterial_" + id).html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariPanjangMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#panjang_" + id).html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariLebarMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#lebar_" + id).html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariDescripitionMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#description_" + id).html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariQtyMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#qty_" + id).html(html);
            }
        });
        // $.ajax({
        // type:"GET",
        // url:siteurl+'purchase_order/CariweightMaterial',
        // data:"idpr="+idpr+"&id="+id,
        // success:function(html){
        // $("#width_"+id).html(html);
        // }
        // });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariTweightMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#totalwidth_" + id).html(html);
            }
        });

        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariWidthMaterial',
            data: "idpr=" + idpr + "&id=" + id,
            success: function(html) {
                $("#width_" + id).html(html);
            }
        });

        var a;
        var ArrList = [];
        for (a = 1; a <= 100; a++) {
            var dataid = $('#dt_idpr_' + a).val();
            ArrList.push(dataid);
        }
        $.ajax({
            type: "POST",
            url: siteurl + 'purchase_order/getDateExp',
            data: {
                'id_pr': ArrList
            },
            dataType: 'json',
            success: function(data) {
                $('#expect_tanggal').val(data.minimal)
            }
        });
    }

    function LockMaterial(id) {
        var idpr = $("#dt_idpr_" + id).val();
        var idmaterial = $("#dt_idmaterial_" + id).val();
        var namaterial = $("#dt_namamaterial_" + id).val();
        var description = $("#dt_description_" + id).val();
        var qty = $("#dt_qty_" + id).val();
        var width = $("#dt_width_" + id).val();
        var totalwidth = $("#dt_totalweight_" + id).val();
        var hargasatuan = $("#dt_hargasatuan_" + id).val();
        var diskon = $("#dt_diskon_" + id).val();
        var pajak = $("#dt_pajak_" + id).val();
        var ratelme = $("#dt_ratelme_" + id).val();
        var alloyprice = $("#dt_alloyprice_" + id).val();
        var fabcost = $("#dt_fabcost_" + id).val();
        var panjang = $("#dt_panjang_" + id).val();
        var lebar = $("#dt_lebar_" + id).val();
        var jumlahharga = $("#dt_jumlahharga_" + id).val();
        var note = $("#dt_note_" + id).val();
        var subtotal = $("#subtotal").val();
        var hargatotal = $("#hargatotal").val();
        var diskontotal = $("#diskontotal").val();
        var taxtotal = $("#taxtotal").val();
        if (qty == '' || qty == null || hargasatuan == '' || hargasatuan == null) {
            swal("Warning", "Form Tidak Boleh Kosong :)", "error");
            return false;
        } else {
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/LockMatrial',
                data: "idpr=" + idpr + "&id=" + id + "&idmaterial=" + idmaterial + "&width=" + width + "&ratelme=" + ratelme + "&alloyprice=" + alloyprice + "&fabcost=" + fabcost + "&panjang=" + panjang + "&lebar=" + lebar + "&totalwidth=" + totalwidth + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
                success: function(html) {
                    $("#trmaterial_" + id).html(html);
                }
            });
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/CariTHarga',
                data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
                success: function(html) {
                    $("#ForHarga").html(html);
                }
            });
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/CariTDiskon',
                data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
                success: function(html) {
                    $("#ForDiskon").html(html);
                }
            });
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/CariTPajak',
                data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
                success: function(html) {
                    $("#ForTax").html(html);
                }
            });
            $.ajax({
                type: "GET",
                url: siteurl + 'purchase_order/CariTSum',
                data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
                success: function(html) {
                    $("#ForSum").html(html);
                }
            });
        }
    }

    function CancelItem(id) {
        var idpr = $("#dt_idpr_" + id).val();
        var idmaterial = $("#dt_idmaterial_" + id).val();
        var namaterial = $("#dt_namamaterial_" + id).val();
        var description = $("#dt_description_" + id).val();
        var qty = $("#dt_qty_" + id).val();
        var hargasatuan = $("#dt_hargasatuan_" + id).val();
        var diskon = $("#dt_diskon_" + id).val();
        var pajak = $("#dt_pajak_" + id).val();
        var jumlahharga = $("#dt_jumlahharga_" + id).val();
        var note = $("#dt_note_" + id).val();
        var subtotal = $("#subtotal").val();
        var hargatotal = $("#hargatotal").val();
        var diskontotal = $("#diskontotal").val();
        var taxtotal = $("#taxtotal").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariMinHarga',
            data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
            success: function(html) {
                $("#ForHarga").html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariMinDiskon',
            data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
            success: function(html) {
                $("#ForDiskon").html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariMinPajak',
            data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
            success: function(html) {
                $("#ForTax").html(html);
            }
        });
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariMinSum',
            data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
            success: function(html) {
                $("#ForSum").html(html);
            }
        });
        $('#data_request #trmaterial_' + id).remove();
    }

    function HapusItem(id) {

    }

    function HitungHarga2(id) {
        var dt_qty = $("#dt_qty_" + id).val();
        var dt_width = $("#dt_totalwidth_" + id).val();
        var dt_hargasatuan = $("#dt_hargasatuan_" + id).val();
        console.log(dt_width);
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/HitungHarga',
            data: "dt_hargasatuan=" + dt_hargasatuan + "&dt_qty=" + dt_qty + "&id=" + id + "&dt_width=" + dt_width,
            success: function(html) {
                $("#jumlahharga_" + id).html(html);
            }
        });

    }

    function get_lokasi() {
        var supplier = $("#id_suplier").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'purchase_order/CariLokasi',
            data: "supplier=" + supplier,
            success: function(html) {
                $("#loi").html(html);
                get_kurs();
            }

        });


    }

    function HitAmmount(id) {
        if ($("#dt_alloyprice_" + id).length <= 0) {
            var alloyprice = 0;
        } else {
            var alloyprice = getNum($("#dt_alloyprice_" + id).val().split(",").join(""));
        }

        if ($("#dt_fabcost_" + id).length <= 0) {
            var fabcost = 0;
        } else {
            var fabcost = getNum($("#dt_fabcost_" + id).val().split(",").join(""));
        }
        var diskon = getNum($("#dt_diskon_" + id).val().split(",").join(""));
        var pajak = getNum($("#dt_pajak_" + id).val().split(",").join(""));
        var qty = getNum($("#dt_qty_" + id).val().split(",").join(""));
        var hargasatuan = getNum($("#dt_hargasatuan_" + id).val().split(",").join(""));
        var dt_width = getNum($("#dt_totalweight_" + id).val().split(",").join(""));
        var loi = $("#loi").val();

        if (loi == 'Import') {
            var total = Number(alloyprice) + Number(fabcost);
            var jumlah = total * dt_width;

            if (jumlah == 0) {
                total = hargasatuan;
                jumlah = hargasatuan * dt_width;
            } else {
                $("#dt_hargasatuan_" + id).val(number_format(total, 3));
            }
        } else {
            var total = hargasatuan;
            var jumlah = hargasatuan * dt_width;
        }

        var tot_pajak = pajak / 100 * jumlah;
        var tot_diskon = diskon / 100 * jumlah;
        var tot_jumlah = jumlah - tot_diskon + tot_pajak;



        $("#dt_jumlahharga_" + id).val(number_format(jumlah, 3));

        $("#dt_ch_pajak_" + id).val(tot_pajak);
        $("#dt_ch_diskon_" + id).val(tot_diskon);
        $("#dt_ch_jumlah_" + id).val(tot_jumlah);

        var SUM_JML = 0
        var SUM_DIS = 0
        var SUM_PJK = 0
        var SUM_JMX = 0

        $(".ch_diskon").each(function() {
            SUM_DIS += Number($(this).val());
        });

        $(".ch_pajak").each(function() {
            SUM_PJK += Number($(this).val());
        });

        $(".ch_jumlah").each(function() {
            SUM_JML += Number($(this).val());
        });

        $(".ch_jumlah_ex").each(function() {
            SUM_JMX += Number($(this).val().split(",").join(""));
        });

        $("#hargatotal").val(number_format(SUM_JMX, 2));
        $("#diskontotal").val(number_format(SUM_DIS));
        $("#taxtotal").val(number_format(SUM_PJK));
        $("#subtotal").val(number_format(SUM_JML));

    }

    function SumDel() {
        var SUM_JML = 0
        var SUM_DIS = 0
        var SUM_PJK = 0
        var SUM_JMX = 0

        $(".ch_diskon").each(function() {
            SUM_DIS += Number($(this).val());
        });

        $(".ch_pajak").each(function() {
            SUM_PJK += Number($(this).val());
        });

        $(".ch_jumlah").each(function() {
            SUM_JML += Number($(this).val());
        });

        $(".ch_jumlah_ex").each(function() {
            SUM_JMX += Number($(this).val().split(",").join(""));
        });

        $("#hargatotal").val(number_format(SUM_JMX, 2));
        $("#diskontotal").val(number_format(SUM_DIS));
        $("#taxtotal").val(number_format(SUM_PJK));
        $("#subtotal").val(number_format(SUM_JML));

    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
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
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
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

    function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }
</script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $(function() {
        $('.chosen-select').select2({
            width: '100%'
        });
        $('.select').select2({
            width: '100%'
        });
        $('#tanggal').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });
        $('#expect_tanggal').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });
    });
</script>