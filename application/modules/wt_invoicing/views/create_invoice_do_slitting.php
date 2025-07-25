<?php
$ENABLE_ADD     = has_permission('Penawaran.Add');
$ENABLE_MANAGE  = has_permission('Penawaran.Manage');
$ENABLE_VIEW    = has_permission('Penawaran.View');
$ENABLE_DELETE  = has_permission('Penawaran.Delete');
$tanggal = date('Y-m-d');
foreach ($results['header'] as $hd) {
}
foreach ($results['headerso'] as $hdso) {
}
$plan = $results['plan'];
$do = $results['do'];
$term = $this->db->query("SELECT payment_term FROM master_customers WHERE id_customer ='$hd->id_customer'")->row();






$date = date('Y-m-d');

$newDate = date('Y-m-d', strtotime($date . ' - 15 days'));

//$scrap = $results['detail2']->total_kirim;

?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Invoice</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">No Invoice</label>
									</div>
									<div class="col-md-8" hidden>
										<input type="text" class="form-control" id="id_plan_tagih" value="<?= $plan->id_plan_tagih ?>" required name="id_plan_tagih" readonly placeholder="No.SO">
										<input type="text" class="form-control" id="no_so" value="<?= $hd->id_spkmarketing ?>" required name="no_so" readonly placeholder="No.SO">
										<input type="text" class="form-control" id="tipe" value="slitting" required name="tipe" readonly placeholder="tipe">

									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="No. Invoice">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tgl Invoice</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" id="tanggal" onkeyup required name="tanggal" min="<?= $newDate ?>" value="<?= date('Y-m-d') ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Customer</label>
									</div>
									<div class="col-md-8">
										<select id="id_customer" name="id_customer" class="form-control select" onchange="get_customer()" disabled required>
											<option value="">--Pilih--</option>
											<?php foreach ($results['customers'] as $customers) {
												$select1 = $hd->id_customer == $customers->id_customer ? 'selected' : '';	?>
												<option value="<?= $customers->id_customer ?>" <?= $select1 ?>><?= strtoupper(strtolower($customers->name_customer)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_category_supplier">Nomor DO</label>
									</div>
									<div id="sales_slot">
										<div class='col-md-8'>
											<input type='text' class='form-control' id='no_do' value="<?= $do->no_surat ?>" required name='no_do' readonly placeholder='Nomor DO'>
										</div>
										<div class='col-md-8' hidden>
											<input type='text' class='form-control' id='id_do' value="<?= $this->uri->segment('3') ?>" required name='id_do' readonly placeholder='Sales Marketing'>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12" hidden>
							<div class='col-sm-6'>
								<div class='form-group row'>
									<div class='col-md-4'>
										<label for='email_customer'>Email</label>
									</div>
									<div class='col-md-8' id="email_slot">
										<input type='email' class='form-control' value="<?= $hd->email_customer ?>" id='email_customer' required name='email_customer' readonly>
									</div>
								</div>
							</div>
							<div class='col-sm-6'>
								<div class='form-group row'>
									<div class='col-md-4'>
										<label for='id_category_supplier'>PIC Customer</label>
									</div>
									<div class='col-md-8' id="pic_slot">
										<select id='pic_customer' name='pic_customer' class='form-control select' required disabled>
											<option value="<?= $hd->pic_customer ?>" selected><?= strtoupper(strtolower($hd->pic_customer)) ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12" hidden>
							<div class='col-sm-6'>
								<div class='form-group row'>
									<div class='col-md-4'>
										<label for='email_customer'>Term Of Payment</label>
									</div>
									<div class='col-md-8'>
										<select id="top" name="top" class="form-control select" required disabled>
											<option value="">--Pilih--</option>
											<?php foreach ($results['top'] as $top) {
												$select2 = $top->id_top == $plan->id_top ? 'selected' : '';	?>
												<option value="<?= $top->id_top ?>" <?= $select2 ?>><?= strtoupper(strtolower($top->nama_top)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<div class='col-sm-6'>
								<div class='form-group row'>
									<div class='col-md-4'>
										<label for='email_customer'>Order Status</label>
									</div>
									<div class='col-md-8' id="">
										<select id="order_sts" name="order_sts" class="form-control select" required disabled>
											<option value="">--Pilih--</option>
											<?php if ($hd->order_status == stk) { ?>
												<option value="stk" selected>Stock</option>
												<option value="ind">Indent</option>
											<?php } else if ($hd->order_status == ind) { ?>
												<option value="stk">Stock</option>
												<option value="ind" selected>Indent</option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12" hidden>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Pembayaran Ke</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="pembayaran" required name="pembayaran" readonly placeholder="Pembayaran" value="<?= $plan->payment ?>">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Keterangan</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="keterangan_top" required name="keterangan_top" readonly placeholder="Keterangan Top" value="<?= $plan->keterangan ?>">
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">No PO Customer</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_po" required name="no_po" value="<?= $hd->no_po ?>" placeholder="No PO">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tgl DO</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" id="tgl_do" required name="tgl_do" readonly placeholder="Tgl DO" value="<?= $do->tgl_delivery_order ?>">
									</div>
								</div>
							</div>

						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tgl PO Customer</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" id="tgl_po" required name="tgl_po" value="<?= $hd->tgl_po ?>" placeholder="No PO">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Terms</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="note" required name="note" value="<?= $term->payment_term ?>" placeholder="Terms">
									</div>
								</div>
							</div>
						</div>






						<div class="box-body">
							<table id="example1" class="table table-bordered table-striped">
								<thead>
									<tr class='bg-blue'>
										<th width='3%'>No</th>
										<th width='5%'>Quantity</th>
										<th>Units</th>
										<th>Width</th>
										<th>Product</th>
										<th>Services</th>
										<th>Original Size</th>
										<th>Slitting To Be</th>
										<th>Per Unit</th>
										<th>Ammount</th>

									</tr>

									</tr>
								</thead>
								<tbody id="list_spk">
									<?php $loop = 0;
									foreach ($results['detail'] as $dt_spk) {
										$loop++;

										$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
										$material = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE a.id_category3 ='$dt_spk->id_material'")->result();
										$mat = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE a.id_category3 ='$dt_spk->id_material'")->row();

										$harga    = $this->db->query("SELECT a.* FROM view_dodt_spkmarketing as a WHERE a.no_spk_marketing='$dt_spk->no_spk_marketing' AND a.id_material='$dt_spk->id_material' AND a.width='$dt_spk->width' AND a.deal='1'")->row();

										$harga_satuan = $harga->harga_deal;
										$total_kirim  = $dt_spk->total_kirim;

										$total 		 = $harga_satuan * $total_kirim;
										$diskon		 += $dt_spk->nilai_diskon;
										$totalharga  += $total;
										$persen      = $plan->persentase;
										$tagih		 = $totalharga - $diskon;
										$totalinv    = number_format(round(($tagih * $persen) / 100));
										$total2      = number_format($total, 2);

										if ($dt_spk->length <= 0) {
											$length = 'C';
										} else {
											$length = number_format($dt_spk->length, 2);
										}

										$thickness = $mat->thickness;
										$width     = $dt_spk->width;

										$spek      = $thickness . ' X ' . $width . ' X ' . $length;

										$spek3     = ' X 0 X ' . $length;



										$nama_produk = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE a.id_category3='$dt_spk->id_material'")->row();

										$original = $this->db->query("SELECT a.* FROM stock_material as a WHERE a.id_category3='$dt_spk->id_material' AND a.id_stock='$dt_spk->id_stock' GROUP BY a.id_category3")->row();

										if ($original->length <= 0) {
											$length2 = 'C';
										} else {
											$length2 = number_format($original->length, 2);
										}

										$thickness2 = $original->thickness;
										$width2     = $original->width;

										$spek2      = $thickness2 . ' X ' . $width2 . ' X ' . $length2;

										echo "
                                        <tr id='tr_$loop'>
                                            <td>$loop</td>
                                          	<td id='id_penawaran_$loop' hidden><input type='text' value='$dt_spk->id_penawaran_detail' class='form-control input-sm' readonly id='used_id_penawaran_$loop' required name='dt[$loop][id_penawaran]'></td>
                                            <td id='nama_produk_$loop' hidden><input type='text' value='$nama_produk->nama' class='form-control input-sm' readonly id='used_nama_produk_$loop' required name='dt[$loop][nama_produk]'></td>
											<td id='qty_so_$loop'><input type='text' class='form-control input-sm' id='used_qty_so_$loop' required name='dt[$loop][qty_so]' onblur='HitungTotal($loop)' value='$total_kirim' readonly></td>
											<td id='qty_$loop' hidden><input type='text' class='form-control input-sm' id='used_qty_$loop' required name='dt[$loop][qty]' onblur='HitungTotal($loop)' value='$total_kirim' readonly></td>
											
											<td id='units_$loop'><input type='text' class='form-control input-sm' id='used_units_$loop' required name='dt[$loop][units]' onblur='HitungTotal($loop)' value='KGS' readonly></td>
											 <td id='width_$loop'><input type='text' class='form-control input-sm' id='used_width_$loop' required name='dt[$loop][width]' onblur='HitungTotal($loop)' value='$dt_spk->width' readonly></td>
											  <td width='200'>
                                                <select id='used_id_material_$loop' name='dt[$loop][id_material]' data-no='$loop' onchange='CariDetail($loop)' class='form-control select' required disabled>
                                                    <option value=''>-Pilih-</option>";
										foreach ($material as $produk) {
											$select = $dt_spk->id_material == $produk->id_category3 ? 'selected' : '';
											echo "<option value='$produk->id_category3' $select>JASA SLITTING $produk->nama , $spek</option>";
										}
										echo	"</select>
                                            </td>
											
											<td width='200'>
											JASA SLITTING ALUMINIUM
											</td>
											<td id='original_size_$loop' align='right'><input type='text' class='form-control input-sm' id='used_original_size_$loop' required name='dt[$loop][original_size]'></td>
											<td id='tobe_size_$loop' align='right'><input type='text' value='$spek' class='form-control input-sm' id='used_tobe_size_$loop' required name='dt[$loop][tobe_size]' readonly></td>
											<td id='harga_satuan_$loop' align='right'><input type='text' value='$harga_satuan' class='form-control input-sm' id='used_harga_satuan_$loop' required name='dt[$loop][harga_satuan]' readonly></td>
                                            <td id='total_harga_$loop' align='right'><input type='text' value='$total2' class='form-control input-sm total' id='used_total_harga_$loop' required name='dt[$loop][total_harga]' readonly></td>
                                            
                                            
                                        </tr>";
									}

									$no = 0;
									$totalhargascrap = 0;
									foreach ($results['detail2'] as $dt_spk2) {
										$no++;
										$sc = $loop + $no;
										$scrap       = $dt_spk2->total_kirim;
										$thickness       = $dt_spk2->thickness;
										$imaterial       = $dt_spk2->id_material;
										$hargascrap1 = $scrap * $harga_satuan;
										$hargascrap  = number_format($hargascrap1, 2);
										$totalhargascrap += $hargascrap1;
										$material2 = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE a.id_category3 ='$imaterial'")->result();
										$select2   = 'selected';

										$nama_produk2 = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE a.id_category3='$imaterial'")->row();

										echo "
                                        <tr id='tr_$loop$no'>
                                            <td>$sc</td>
                                          	<td id='id_penawaran_$loop$no' hidden><input type='text' value='$dt_spk->id_penawaran_detail' class='form-control input-sm' readonly id='used_id_penawaran_$loop$no' required name='dt[$loop$no][id_penawaran]'></td>
                                            <td id='nama_produk_$loop$no' hidden><input type='text' value='$nama_produk2->nama' class='form-control input-sm' readonly id='used_nama_produk_$loop$no' required name='dt[$loop$no][nama_produk]'></td>
											<td id='qty_so_$loop$no'><input type='text' class='form-control input-sm' id='used_qty_so_$loop$no' required name='dt[$loop$no][qty_so]' onblur='HitungTotal($loop$no)' value='$scrap' readonly></td>
											<td id='qty_$loop$no' hidden><input type='text' class='form-control input-sm' id='used_qty_$loop$no' required name='dt[$loop$no][qty]' onblur='HitungTotal($loop$no)' value='$scrap' readonly></td>
											
											<td id='units_$loop$no'><input type='text' class='form-control input-sm' id='used_units_$loop$no' required name='dt[$loop$no][units]' onblur='HitungTotal($loop$no)' value='KGS' readonly></td>
											 <td id='width_$loop$no'><input type='text' class='form-control input-sm' id='used_width_$loop$no' required name='dt[$loop$no][width]' onblur='HitungTotal($loop$no)' value='0' readonly></td>
											  <td width='200'>
                                                <select id='used_id_material_$loop$no' name='dt[$loop$no][id_material]' data-no='$loop$no' onchange='CariDetail($loop$no)' class='form-control select' required disabled>
                                                    <option value=''>-Pilih-</option>";
										foreach ($material2 as $produk2) {
											$select = $dt_spk->id_material == $produk2->id_category3 ? 'selected' : '';
											echo "<option value='$produk2->id_category3' $select2>JASA SLITTING $produk2->nama ,  $thickness.$spek3</option>";
										}
										echo	"</select>
                                            </td>
											
											<td width='200'>
											JASA SLITTING ALUMINIUM
											</td>
											<td id='original_size_$loop$no' align='right'><input type='text' class='form-control input-sm' id='used_original_size_$loop$no' required name='dt[$loop$no][original_size]'></td>
											<td id='tobe_size_$loop$no' align='right'><input type='text' value='$thickness.$spek3' class='form-control input-sm' id='used_tobe_size_$loop$no' required name='dt[$loop$no][tobe_size]' readonly></td>
											<td id='harga_satuan_$loop$no' align='right'><input type='text' value='$harga_satuan' class='form-control input-sm' id='used_harga_satuan_$loop$no' required name='dt[$loop$no][harga_satuan]' readonly></td>
                                            <td id='total_harga_$loop$no' align='right'><input type='text' value='$hargascrap' class='form-control input-sm total' id='used_total_harga_$loop$no' required name='dt[$loop$no][total_harga]' readonly></td>
                                            
                                            
                                        </tr>";
									}

									$invoicescrap = $tagih + $totalhargascrap;
									$totalinvoicescrap = $invoicescrap;
									?>

								</tbody>
								<tfoot>
									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>Amount</b></th>
										<th width='7%'></th>
										<th width='7%'><input type='text' class='form-control' id='totalproduk' name='totalproduk' readonly value="<?= number_format($totalharga + $totalhargascrap, 2) ?>"></th>

									</tr>

									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>Discount</b></th>
										<th width='7%'></th>
										<th width='7%'><input type='text' class='form-control diskon' id='diskon' name='diskon' readonly value="<?= number_format($diskon, 2) ?>"></th>

									</tr>
									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>Total</b></th>
										<th width='7%'></th>
										<th width='7%'><input type='text' class='form-control totalproduk' id='total' name='total' readonly value="<?= number_format($totalinvoicescrap, 2) ?>"></th>

									</tr>

									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>Persentase Invoice %</b></th>
										<th width='7%'><input type='text' class='form-control persentase' id='persentase' name='persentase' value="<?= $plan->persentase ?>" onblur="hitungPpn()"></th>
										<th width='7%'><input type='text' class='form-control grandtotal' id='grandtotal' name='grandtotal' value="<?= $totalinvoicescrap ?>" readonly></th>

									</tr>
									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>DPP</b></th>
										<th width='7%'></th>
										<th width='7%'><input type='text' class='form-control dpp' id='dpp' name='dpp' value="<?= $totalinvoicescrap ?>" readonly></th>

									</tr>
									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>PPN</b></th>
										<th width='7%'><input type='text' class='form-control ppn' id='ppn' name='ppn' onblur='hitungPpn()' value="<?= $hd->ppn ?>"></th>
										<th width='7%'><input type='text' class='form-control totalppn' id='totalppn' name='totalppn' value="<?= $hd->nilai_ppn ?>" readonly></th>

									</tr>

									<tr>
										<th width='3%'></th>
										<th width='5%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'></th>
										<th width='7%'><b>Total Invoice</b></th>
										<th width='7%'></th>
										<th width='7%'><input type='text' class='form-control nilai_tagih' id='nilai_tagih' name='nilai_tagih' readonly></th>

									</tr>

								</tfoot>
							</table>
						</div>
					</div>


					<center>

						<!--<button type="submit" class="btn btn-warning btn-sm" name="save" id="simpan-preview"><i class="fa fa-search"></i>&nbsp;Preview Invoice</button>-->
						<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>&nbsp;Create Invoice</button>
						<a class="btn btn-danger btn-sm" href="<?= base_url('/wt_invoicing/') ?>" title="Edit">Kembali</a>
						<div id="deal"> <button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com2"><i class="fa fa-save"></i>Deal SO</button> </div>
					</center>
		</form>
	</div>
</div>




<script type="text/javascript">
	$('#simpan-com2').hide();
	hitungPpn();

	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	$(document).ready(function() {
		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID		

		$('.select').select2({
			width: '100%'
		});
		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var image = $('#image').val();
			var idtype = $('#inventory_1').val();

			var tanggal = $('#tanggal').val();
			var persentase = $('#persentase').val();
			var ppn = $('#ppn').val();


			$(".select").removeAttr("disabled");

			var data, xhr;

			if (tanggal == '') {
				swal("Warning", "Tanggal Invoice Tidak Boleh Kosong :)", "error");
				return false;
			} else if (persentase == '') {
				swal("Warning", "Persentase Invoice Tidak Boleh Kosong :)", "error");
				return false;
			} else if (ppn == '') {
				swal("Warning", "PPN Invoice Tidak Boleh Kosong :)", "error");
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
							var baseurl = siteurl + 'wt_invoicing/SaveNewInvoice';
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
										window.location.href = base_url + 'wt_invoicing';
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


		$('#simpan-com1').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var image = $('#image').val();
			var idtype = $('#inventory_1').val();

			$(".select").removeAttr("disabled");

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
						var baseurl = siteurl + 'wt_invoicing/SaveNewProformaInvoice';
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
									window.location.href = base_url + 'wt_invoicing/plan_tagih';
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

	$('#simpan-preview').click(function(e) {
		e.preventDefault();
		var deskripsi = $('#deskripsi').val();
		var image = $('#image').val();
		var tanggal = $('#tanggal').val();

		$(".select").removeAttr("disabled");

		var data, xhr;

		if (tanggal == '') {
			swal("Warning", "Tanggal Invoice Tidak Boleh Kosong :)", "error");
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
						var baseurl = siteurl + 'wt_invoicing/SavePreviewInvoice';
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
									window.open(base_url + 'wt_invoicing/PrintPreviewInvoice', '_blank');
								} else {

									if (data.status == 2) {
										swal({
											title: "Preview Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Preview Failed!",
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


	function get_customer() {
		var id_customer = $("#id_customer").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getemail',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#email_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getpic',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#pic_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getsales',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#sales_slot").html(html);
			}
		});
	}

	function DelItem(id) {
		$('#data_barang #tr_' + id).remove();

	}


	function GetProduk() {
		var jumlah = $('#list_spk').find('tr').length;
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/GetProduk',
			data: "jumlah=" + jumlah,
			success: function(html) {
				$("#list_spk").append(html);
				$('.select').select2({
					width: '100%'
				});
			}
		});
	}
	$(document).on('blur', '#upload_po', function() {
		var po = $(this).data('upload_po');
		if (po != '') {
			$('#simpan-com2').show();
		}

	});

	function HapusItem(id) {
		$('#list_spk #tr_' + id).remove();
		changeChecked();
	}

	function CariDetail(id) {

		var id_material = $('#used_no_surat_' + id).val();

		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/CariNamaProduk',
			data: "id_category3=" + id_material + "&id=" + id,
			success: function(html) {
				$('#nama_produk_' + id).html(html);
			}
		});

	}


	function Freight(id) {
		var freight = $('#used_freight_cost_' + id).val();
		$('#used_freight_cost_' + id).val(number_format(freight, 2));

		HitungTotal(id);

	}

	function HitungTotal(id) {
		var qty = $('#used_qty_so_' + id).val();
		var harga = $('#used_harga_satuan_' + id).val();
		var diskon = $('#used_diskon_' + id).val();
		var freight = $('#used_freight_cost_' + id).val().split(",").join("");



		var totalBerat = getNum(qty) * getNum(harga);
		var nilai_diskon = (getNum(diskon) * getNum(totalBerat)) / 100;
		var total_harga = getNum(totalBerat) - getNum(nilai_diskon) + getNum(freight);


		$('#used_total_harga_' + id).val(number_format(total_harga, 2));
		$('#used_nilai_diskon_' + id).val(number_format(nilai_diskon, 2));



		HitungLoss(id);

		totalBalanced();






	}



	function totalBalanced() {

		var SUMx = 0;
		$(".total").each(function() {
			SUMx += Number($(this).val().split(",").join(""));
		});


		$('.totalproduk').val(number_format(SUMx, 2));

		$('#grandtotal').val(number_format(SUMx, 2));

		hitungPpn();


	}


	function hitungPpn() {
		var total = $('.totalproduk').val().split(",").join("");
		var ppn = $('#ppn').val();
		var persentase = $('#persentase').val();


		var grand_total = getNum(total);

		var nilai_tagih = (getNum(grand_total) * getNum(persentase)) / 100;

		var nilai_ppn = (getNum(nilai_tagih) * getNum(ppn)) / 100;

		var nilai_invoice = getNum(nilai_tagih) + getNum(nilai_ppn);

		$('#grandtotal').val(number_format(nilai_tagih, 2));
		$('#dpp').val(number_format(nilai_tagih, 2));

		$('#totalppn').val(number_format(nilai_ppn, 2));

		$('#nilai_tagih').val(number_format(nilai_invoice, 2));



	}


	function HitungLoss(id) {
		var qty = $('#used_qty_' + id).val();
		var stok = $('#used_stok_tersedia_' + id).val();


		var totalstok = getNum(qty) + getNum(stok);
		var totalselisih = getNum(stok) - getNum(qty);
		var total_loss = getNum(totalstok) + getNum(totalselisih);
		var loss_nilai = getNum(totalselisih) * -1;

		if (totalselisih >= 0) {
			var loss = 0;

		} else {
			var loss = loss_nilai;
		}


		if (order_sts == 'ind') {
			$('#used_potensial_loss_' + id).val('0');
		} else {
			$('#used_potensial_loss_' + id).val(number_format(loss, 2));
		}

	}



	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
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
</script>