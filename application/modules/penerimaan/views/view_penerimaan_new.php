<?php
$data_header = $data_header;
$data_detail = $data_detail;
$data_cn = $data_cn;
$datbank = $datbank;
$pphpenjualan = $pphpenjualan;
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<form id="form-header-mutasi" method="post">
	<div class="nav-tabs-salesorder">
		<div class="tab-content">
			<div class="tab-pane active" id="salesorder">
				<div class="box box-primary">
					<div class="box-body">
						<div class="col-sm-6 form-horizontal">
							<div class="row">
								<div class="form-group ">
									<label for="tgl_bayar" class="col-sm-4 control-label">Tgl Bayar :</label>
									<div class="col-sm-6">
										<input type="hidden" name="kd_pembayaran" id="kd_pembayaran" value="<?= $data_header->kd_pembayaran ?>" class="form-control input-sm" readonly>
										<input type="date" name="tgl_bayar" id="tgl_bayar" class="form-control input-sm tanggal" value="<?= $data_header->tgl_pembayaran ?>" readonly>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-group">
									<label for="ket_bayar" class="col-sm-4 control-label">Keterangan Pembayaran </label>
									<div class="col-sm-6">
										<textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar" readonly><?= $data_header->keterangan ?></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div hidden class="form-group">
									<label for="jenis_invoice" class="col-sm-4 control-label">Jenis PPH</label>
									<div class="col-sm-6">
										<?php
										$pphpenjualan[0]	= 'Select An Option';
										echo form_dropdown('jenis_pph', $pphpenjualan, (isset($data_header) ? $data_header->jenis_pph : '1102-01-03'), array('id' => 'jenis_pph', 'class' => 'form-control', 'disabled' => 'disabled'));
										?>
									</div>
								</div>
							</div>
							<div class="form-group ">
								<label for="tgldo" class="col-sm-4 control-label"> </label>
								<div class="col-sm-6">
									<input type="hidden" name="kurs" class="form-control input-sm" id="kurs" value="<?= $data_header->kurs_bayar ?>" readonly>
								</div>
							</div>
						</div>
						<div class="col-sm-6 form-horizontal">
							<div class="row">
								<div class="form-group">
									<label class="col-sm-4 control-label">Nama Customer </label>
									<div class="col-sm-6">
										<input type="text" name="nm_customer" id="nm_customer" class="form-control input-sm" value="<?= $data_header->nm_customer ?>" readonly>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="jenis_invoice" class="col-sm-4 control-label">Pilih Bank </label>
								<div class="col-sm-6">
									<select class="form-control input-sm" name="bank" id="bank" disabled>
										<option value="">Pilih Bank</option>
										<?php
										foreach($datbank as $kb=>$vb){
											$selected = ($kb == $data_header->kd_bank) ? 'selected' : '';
											echo "<option value='".$kb."' ".$selected.">".$vb."</option>";
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group ">
								<label class="col-sm-4 control-label">Penerimaan Bank</label>
								<div class="col-sm-6">
									<input type="text" name="total_bank" class="form-control input-sm divide" id="total_bank" value="<?= number_format($data_header->jumlah_bank) ?>" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="box box-default ">
		<div class="box-body">
			<table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
				<thead>
					<tr class="bg-blue">
						<th class="text-center">Code</th>
						<th class="text-center">No Invoice</th>
						<th class="text-center">Nama Customer</th>
						<th class="text-center">Total Invoice</th>
						<th class="text-center">Total Bayar</th>
						<th class="text-center">PPH 23</th>
					</tr>
				</thead>
				<tbody id="list_item_mutasi">
					<?php foreach ($data_detail as $det) { 
						$is_cn = false;
						foreach ($data_cn as $cn) {
							if ($cn->id_retur == $det->no_invoice) {
								$is_cn = true;
								break;
							}
						}
						// Alternatif cek: jika total bayar < 0, biasanya itu CN (karena pengurangan/potongan retur)
						if ($det->total_bayar_idr < 0 || $is_cn) {
							$badge_class = "bg-yellow";
							$badge_text = "CN";
							$q = $this->db->query("SELECT no_cn FROM tr_retur_penjualan WHERE id_retur = '".$det->no_invoice."'")->row();
							$display_no = $q ? $q->no_cn : $det->no_invoice;
						} else {
							$badge_class = "bg-green";
							$badge_text = "Invoice";
							$q = $this->db->query("SELECT no_surat FROM tr_invoice WHERE no_invoice = '".$det->no_invoice."'")->row();
							$display_no = $q ? $q->no_surat : $det->no_invoice;
						}
					?>
						<tr>
							<td class="text-center">
								<span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
							</td>
							<td><?= $display_no ?></td>
							<td><?= $det->nm_customer ?></td>
							<td class="text-right"><?= number_format($det->total_invoice_idr) ?></td>
							<td class="text-right"><?= number_format($det->total_bayar_idr) ?></td>
							<td class="text-right"><?= number_format($det->total_pph_idr) ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="text-right">
		<div class="box active">
			<div class="box-body">
				<div class="row">
					<div class="col-lg-6">
					</div>
					<div class="col-lg-6">
						<div class="form-group ">
							<label class="col-sm-4 control-label">Total Bayar Invoice</label>
							<div class="col-sm-6">
								<input type="text" name="total_invoice" class="form-control input-sm divide" id="total_invoice" value="<?= number_format($data_header->jumlah_piutang) ?>" readonly>
							</div>
						</div>
						<div class="form-group ">
							<label class="col-sm-4 control-label">Selisih</label>
							<div class="col-sm-6">
								<input type="text" name="selisih" class="form-control input-sm divide" id="selisih" value="<?= number_format((float)$data_header->selisih) ?>" readonly>
							</div>
						</div>
						<div class="form-group ">
							<label class="col-sm-4 control-label">Biaya Administrasi </label>
							<div class="col-sm-6">
								<input type="text" name="biaya_adm" class="form-control input-sm divide" id="biaya_adm" value="<?= number_format($data_header->biaya_admin) ?>" readonly>
							</div>
						</div>
						<div class="form-group ">
							<label class="col-sm-4 control-label">PPH </label>
							<div class="col-sm-6">
								<input type="text" name="biaya_pph" class="form-control input-sm divide" id="biaya_pph" value="<?= number_format($data_header->biaya_pph) ?>" readonly>
							</div>
						</div>
						<div class="form-group" id="pakailebihbayar">
							<label for="pakai_lebih_bayar" class="col-sm-4 control-label">Pakai Lebih Bayar</label>
							<div class="col-sm-6">
								<input type="text" name="pakai_lebih_bayar" class="form-control input-sm divide" id="pakai_lebih_bayar" value="<?= number_format($data_header->lebih_bayar) ?>" readonly>
							</div>
						</div>
						<div class="form-group ">
							<label for="tambah_lebih_bayar" class="col-sm-4 control-label">Lebih Bayar</label>
							<div class="col-sm-6">
								<input type="text" name="tambah_lebih_bayar" class="form-control input-sm divide" id="tambah_lebih_bayar" value="<?= number_format($data_header->tambah_lebih_bayar) ?>" readonly>
							</div>
						</div>
						<div class="form-group ">
							<label class="col-sm-4 control-label">Kontrol</label>
							<div class="col-sm-6">
								<input type="text" name="control" class="form-control input-sm divide" id="control" value="0" readonly>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-lg-12">
						<a href="<?= base_url('penerimaan') ?>" class="btn btn-danger">
							<i class="fa fa-refresh"></i><b> Kembali</b>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>