<?php
    $ENABLE_ADD     = has_permission('Trans_inquiry.Add');
    $ENABLE_MANAGE  = has_permission('Trans_inquiry.Manage');
    $ENABLE_VIEW    = has_permission('Trans_inquiry.View');
    $ENABLE_DELETE  = has_permission('Trans_inquiry.Delete');
	$tanggal = date('Y-m-d');

	$header = $results['header'];

	$id_spkproduksi = (!empty($header))?$header[0]->id_tr_spk_produksi:'';
	$no_surat = (!empty($header))?$header[0]->no_surat:'';
	$tgl_spk_produksi = (!empty($header))?$header[0]->tgl_spk_produksi:'';
	$id_material = (!empty($header))?$header[0]->id_material:'';
	$density = (!empty($header))?$header[0]->density:'';
	$thickness = (!empty($header))?$header[0]->thickness:'';
	$width = (!empty($header))?$header[0]->width:'';
	$weight = (!empty($header))?$header[0]->weight:'';
	$lpegangan = (!empty($header))?$header[0]->lpegangan:'';
	$lotno = (!empty($header))?$header[0]->lotno:'';
	$panjang = (!empty($header))?$header[0]->panjang:'';
	$nama_material = (!empty($header))?$header[0]->nama_material:'';

	$process = (!empty($header))?$header[0]->process:'';
	$kg_process = (!empty($header))?$header[0]->kg_process:'';
	$length = (!empty($header))?$header[0]->length:'';
	$count_m = (!empty($header))?$header[0]->count_m:'';

	$id_stock = (!empty($header))?$header[0]->id_stock:'';

	$qcoil = (!empty($header))?$header[0]->qcoil:'';
	$jpisau = (!empty($header))?$header[0]->jpisau:'';
	$used = (!empty($header))?$header[0]->used:'';
	$sisa = (!empty($header))?$header[0]->sisa:'';
	//$id_spkproduksi = (!empty($header))?$header[0]->id_tr_spk_produksi:'';
	$keterangan = (!empty($header))?$header[0]->keterangan:'';
	
	$in_lebar 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->in_lebar):array();
	$in_berat 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->in_berat):array();
	$in_ket 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->in_ket):array();
	$in_upload 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->in_upload):array();

	$ek_lebar 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->ek_lebar):array();
	$ek_berat 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->ek_berat):array();
	$ek_ket 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->ek_ket):array();
	$ek_upload 	= (!empty($results['aktual']))?json_decode($results['aktual'][0]->ek_upload):array();
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="row">
					<center><label for="customer" ><h3>SPK PRODUKSI</h3></label></center>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="customer">NO.SPK</label>
								</div>
								<div class="col-md-8" hidden>
									<input type="text" class="form-control" id="id_spkmarketing"  required name="id_spkmarketing" readonly placeholder="No.CRCL" value='<?=$id_spkproduksi;?>'>
								</div>
								<div class="col-md-8">
									<input type="text" class="form-control" id="no_surat"  required name="no_surat" readonly placeholder="No.SPK"value='<?=$no_surat;?>'>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="customer">Tanggal</label>
								</div>
								<div class="col-md-8">
									<input type="date" class="form-control" id="tgl_penawaran" value="<?= $tanggal ?>" onkeyup required name="tgl_penawaran" readonly value='<?=$tgl_spk_produksi;?>'>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class='col-md-4'>
									<label for='customer'>Material</label>
								</div>
								<div class="col-md-8">
										<select id="id_material" name="id_material" class="form-control select" onchange="Caristock()" disabled>
											<option value="">--Pilih--</option>
												<?php foreach ($results['material'] as $material){
													$selx = ($id_material == $material->id_category3)?'selected':'';
													?>
											<option value="<?= $material->id_category3?>" <?=$selx;?>><?= ucfirst(strtolower($material->nama))?>|<?= ucfirst(strtolower($material->maker))?></option>
												<?php } ?>
										</select>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Lot Number</label>
								</div>
								<div class="col-md-8" id='lotnumbe_slot'>
									<select id="id_stock" name="id_stock" class="form-control select" onchange="get_produk()" disabled>
										<option value="">--Pilih--</option>
										<?php foreach ($results['stok'] as $val => $stockx){
											
											// print_r($results['stok']);
											// exit;
											
											$selx = ($id_stock == $stockx['id_stock'])?'selected':'';
											?>
											<option value="<?= $stockx['id_stock']?>" <?=$selx;?>><?= strtoupper(strtolower($stockx['lotno']))?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="customer">Density</label>
								</div>
								<div class="col-md-8" id="slot_density">
									<input type='number' class='form-control' id='density' onkeyup required name='density' readonly value='<?=$density;?>' >
								</div>
							</div>
							
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Thickness</label>
								</div>
								<div class="col-md-8" id="slot_thickness">
									<input type='number' class='form-control' id='thickness' onkeyup required name='thickness' readonly  value='<?=$thickness;?>'>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Width (mm)</label>
								</div>
								<div class="col-md-8" id="slot_width">
									<input type='number' class='form-control' id='width'  required name='width' readonly value='<?=$width;?>'>
									<input type='hidden' class='form-control' id='lotno'  required name='lotno' readonly value='<?=$lotno;?>' >
									<input type='hidden' class='form-control' id='panjang'  required name='panjang' readonly value='<?=$panjang;?>' >
									<input type='hidden' class='form-control' id='id_spkproduksi'  required name='id_spkproduksi' readonly value='<?=$id_spkproduksi;?>' >
									<input type='hidden' class='form-control' id='nama_material'  required name='nama_material' readonly value='<?=$nama_material;?>' >
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Process | Kg Process</label>
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control autoNumeric' id='process' name='process' readonly  value='<?=$process;?>'>
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control' id='kg_process' name='kg_process' readonly   value='<?=$kg_process;?>'>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Weight</label>
								</div>
								<div class="col-md-8" id="slot_weight">
									<input type='number' class='form-control' id='weight' onkeyup required name='weight' readonly  value='<?=$weight;?>'>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Length (m) | Count (m)</label>
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control' id='length' name='length' readonly  value='<?=$length;?>'>
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control' id='count_m' name='count_m' readonly  value='<?=$count_m;?>' >
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Lebar Pegangan</label>
								</div>
								<div class="col-md-8" id="slot_width">
									<input type='text' class='form-control autoNumeric' id='lpegangan' name='lpegangan' readonly  value='<?=$lpegangan;?>'>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							
						</div>
					</div>

					<div class="col-sm-12">
						<div class="col-sm-12">
						<?php if(empty($results['view'])){ ?>
							<div class="form-group row">
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='GetSpk();'><i class='fa fa-plus'></i>Add</button>
							</div>
						<?php } ?>
							<div class="form-group row" >
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<th width='3%'>No</th>
											<th width='15%'>SPK Marketing</th>
											<th>Customer</th>
											<th>Nomor Alloy</th>
											<th width='5%'>Width</th>
											<th width='5%'>@KG</th>
											<th width='5%'>@Coil</th>
											<th width='5%'>Total Kg</th>
											<th>Delivery Date</th>
											<?php if(empty($results['view'])){ ?>
											<th width='5%'>Include</th>
											<th width='5%'>Aksi</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody id="list_spk">
									<?php $loop=0;
									foreach ($results['detail'] as $dt_spk){$loop++; 
										$nosurat = $this->db->query("SELECT a.*, b.no_surat as no_surat FROM dt_spkmarketing as a INNER JOIN tr_spk_marketing as b ON a.id_spkmarketing = b.id_spkmarketing WHERE a.id_material='$id_material' ")->result();
										$cheked = ($dt_spk->checked == '1')?'checked':'';
										$readonly = ($dt_spk->no_surat == 'nonspk')?'':'readonly';
										echo "
										<tr id='tr_$loop'>
											<th>$loop</th>
											<th><select id='used_no_surat_$loop' name='dt[$loop][no_surat]' onchange='CariDetail($loop)' class='form-control select' disabled>
											<option value='nonspk'>Non SPK</option>";
											foreach($nosurat as $nosurat){
												$select = $dt_spk->no_surat == $nosurat->id_dt_spkmarketing ? 'selected' : '';
											echo"<option value='$nosurat->id_dt_spkmarketing' $select>$nosurat->no_surat</option>";
											}
										echo"</select></th>
											<th id='idcust_$loop' hidden><input type='text' class='form-control' 	value='$dt_spk->idcustomer' 	readonly id='used_idcustomer_$loop' required name='dt[$loop][idcustomer]'></th>
											<th id='nmcust_$loop' ><input type='text' class='form-control'  	   	value='$dt_spk->namacustomer' 	readonly id='used_namacustomer_$loop' required name='dt[$loop][namacustomer]'></th>
											<th><input type='text' class='form-control' 		value='$dt_spk->nmmaterial'  	readonly id='used_nmmaterial_$loop' required name='dt[$loop][nmmaterial]'></th>
											<th id='weight_$loop'>
												<input type='hidden' class='form-control' 		value='$dt_spk->thickness' 		readonly id='used_thickness_$loop' required name='dt[$loop][thickness]'>
												<input type='text' class='form-control change_weightc autoNumeric'   	readonly	value='$dt_spk->weight'  id='used_weight_$loop' required name='dt[$loop][weight]'>
											</th>
											<th id='width2_$loop'><input type='text' class='form-control'   		value='$dt_spk->width' 			readonly id='used_weight2_$loop' required name='dt[$loop][weight2]'></th>
											<th id='qtyproduk_$loop'><input type='text' class='form-control change_weightc autoNumeric'  	readonly	value='$dt_spk->qtycoil' 		 id='used_qtycoil_$loop' name='dt[$loop][qtycoil]'></th>
											<th id='twidth_$loop'><input type='text' class='form-control'  		value='$dt_spk->totalwidth' 	readonly id='used_totalwidth_$loop' required name='dt[$loop][totalwidth]'></th>
											<th id='delivery_$loop'><input type='date' class='form-control'   		value='$dt_spk->delivery' 	 id='used_delivery_$loop'  name='dt[$loop][delivery]' readonly></th>";
											if(empty($results['view'])){
											echo "<th align='center'>
												<input type='checkbox' class='chk_personal' id='ch_$loop' name='dt[$loop][id]' value='$loop' $cheked>
											</th>
											<th><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button></th>";
											}
											echo "</tr>
										";
											}
											?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="col-sm-12 ngIN">
						<?php
						$nomor = 0;
						foreach($in_lebar AS $val => $valx){ $nomor++;
							if(!empty($in_lebar[$val])){
						?>
						<div class="col-sm-12 ngINApp">
							<div class="form-group row">
								<div class="col-md-2">
									<label class="text-red"><?php if($nomor == 1){echo"NG INTERNAL";}?></label>
								</div>
								<div class="col-md-2">
									<input type='text' class='form-control autoNumeric' name='ngin_lebar[]' disabled placeholder='Lebar' value='<?=$in_lebar[$val];?>'>
								</div>
								<div class="col-md-2">
									<input type='text' class='form-control autoNumeric' name='ngin_berat[]' disabled  placeholder='Berat' value='<?=$in_berat[$val];?>' >
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control ket' name='ngin_ket[]' disabled  placeholder='Keterangan' value='<?=$in_ket[$val];?>' >
								</div>
								<div class="col-md-2">
									<?php if(!empty($in_upload[$val])){ ?>
									<a href='<?=base_url($in_upload[$val]);?>' target='_blank'>Download</a>
									<?php } else {echo"Tidak ada file";} ?>
								</div>
							</div>
						</div>
						<?php }} ?>
					</div>
					<div class="col-sm-12 ngEK">
						<?php
						$nomor = 0;
						foreach($ek_lebar AS $val => $valx){ $nomor++;
							if(!empty($ek_lebar[$val])){
						?>
						<div class="col-sm-12 ngEKApp">
							<div class="form-group row">
								<div class="col-md-2">
									<label class="text-red"><?php if($nomor == 1){echo"NG EKSTERNAL";}?></label>
								</div>
								<div class="col-md-2">
									<input type='text' class='form-control autoNumeric' name='ngek_lebar[]' disabled placeholder='Lebar' value='<?=$ek_lebar[$val];?>'>
								</div>
								<div class="col-md-2">
									<input type='text' class='form-control autoNumeric' name='ngek_berat[]' disabled placeholder='Berat' value='<?=$ek_berat[$val];?>' >
								</div>
								<div class="col-md-4">
									<input type='text' class='form-control' name='ngek_ket[]' disabled placeholder='Keterangan' value='<?=$ek_ket[$val];?>' >
								</div>
								<div class="col-md-2">
									<?php if(!empty($ek_upload[$val])){ ?>
									<a href='<?=base_url($ek_upload[$val]);?>' target='_blank'>Download</a>
									<?php } else {echo"Tidak ada file";} ?>
								</div>
							</div>
						</div>
						<?php }} ?>
					</div>
					
					<table width="100%" border="0">
					<th width="50%">
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Total Coil</label>
								</div>
								<div class="col-md-8" id="slot_qcoil">
									<input type='text' class='form-control' id='qcoil' onkeyup required name='qcoil' readonly value='<?=$qcoil;?>'>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Jumlah Pisau</label>
								</div>
								<div class="col-md-8" id="slot_jpisau">
									<input type='text' class='form-control' id='jpisau' onkeyup required name='jpisau' readonly value='<?=$jpisau;?>' >
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="no_penawaran">Sisa Potongan</label>
								</div>
								<div class="col-md-8" id="slot_sisa">
									<input type='hidden'  class='form-control' id='used' onkeyup required name='used' readonly value='<?=$used;?>' >
									<input type='text' class='form-control' id='sisa' onkeyup required name='sisa' readonly value='<?=$sisa;?>' >
								</div>
							</div>
						</div>
					</div>
				    </th>	
					
					<th valign="top" align="left" width="50%">
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="labelketerangan">Keterangan</label>
								</div>
								<div class="col-md-12" id="keterangan">
									<textarea class='form-control input-sm' id='keterangan' name='keterangan'><?=$keterangan;?></textarea>
								</div>
							</div>
						</div>
					</div>
					</th>
				</table>
					
					<center>
						<a class="btn btn-success btn-sm" href="<?= base_url('/spk_aktual_booking/printSPKProduksi/'.$id_spkproduksi) ?>" target="_blank"  title="Print SPK Produksi"><i class="fa fa-print">&nbsp;</i>Print SPK Produksi</i></a>
						<a class="btn btn-success btn-sm" href="<?= base_url('/spk_aktual_booking/PrintHeader2/'.$id_spkproduksi) ?>" target="_blank"  title="Cetak"><i class="fa fa-print">&nbsp;</i>Cetak Laporan Produksi</i></a>
						<a class="btn btn-danger btn-sm" href="<?= base_url('/spk_aktual_booking/') ?>"  title="Edit">Kembali</a>
					</center>
				</div>
			</div>
		</form>		  
	</div>
</div>	
	
				  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	$(document).ready(function(){	
		$('.autoNumeric').autoNumeric();
		$('.select').select2();

		var max_fields2      = 10; //maximum input boxes allowed
		var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2      = $(".add_field_button2"); //Add button ID	

		
	});

	function get_produk(){ 
        var id_stock=$("#id_stock").val();
		
		$.ajax({
			url:siteurl+'spk_produksi/getDetailMaterial',
			cache: false,
			type: "POST",
			data: {
				'id_stock' : id_stock
			},
			dataType: "json",
			success: function(data){
				// $("#slot_material").html(data.nama_material_html);
				$('#nama_material').val(data.nama_material);
				$('#lotno').val(data.nama_material);
				$('#weight').val(data.weight);
				$('#density').val(data.density);
				$('#thickness').val(data.thickness);
				$('#panjang').val(data.length);
				$('#used').val(0);
				$('#sisa').val(data.totalsisa);
				$('#width').val(data.width);
				$('#lpegangan').val(data.lpegangan);


				// $("#slot_weight").html(data.weight_html);
				// $("#slot_density").html(data.density_html);
				// $("#slot_thickness").html(data.thickness_html);
				// $("#slot_panjang").html(data.length_html);
				// $("#slot_sisa").html(data.totalsisa_html);
				// $("#slot_width").html(data.width_html);
				// $("#hiden_slot").html(data.material_hid_html);
				// $("#slot_pegangan").html(data.pegangan_html);

				calHeader();
			}
		});

    }
	function GetSpk(){ 
		var jumlah	=$('#list_spk').find('tr').length;
		var id_stock=$("#id_stock").val();
		var thickness=$("#thickness").val();
		var nama_material=$("#nama_material").val();
		var id_material=$("#id_material").val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/GetSpk',
            data:"jumlah="+jumlah+"&id_stock="+id_stock+"&id_material="+id_material+"&thickness="+thickness+"&nama_material="+nama_material,
            success:function(html){
               $("#list_spk").append(html);
			   $('.select').select2();
            }
        });
    }
	function CancelItem(id){
		var nmmaterial=$('#used_nmmaterial_'+id).val();
		var dtno_surat=$('#used_no_surat_'+id).val();
		var idcustomer=$('#used_idcustomer_'+id).val();
		var namacustomer=$('#used_namacustomer_'+id).val();
		var thickness=$('#used_thickness_'+id).val();
		var weight=$('#used_weight_'+id).val();
		var qtycoil=$('#used_qtycoil_'+id).val();
		var width=$('#used_width_'+id).val();
		var totalwidth=$('#used_totalwidth_'+id).val();
		var delivery=$('#used_delivery_'+id).val();
		var qcoil=$('#qcoil').val();
		var lpegangan=$('#lpegangan').val();
		var jpisau=$('#jpisau').val();
		var sisa=$('#sisa').val();
		var widthmother=$('#width').val();
		var used=$('#used').val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/MinusQtyCoil',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_qcoil").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/MinusJPisau',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_jpisau").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/MinusSisa',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_sisa").html(html);
            }
        });

		$('#list_spk #tr_'+id).remove();
    }

	function TambahItem(id){
		var nmmaterial=$('#used_nmmaterial_'+id).val();
		var dtno_surat=$('#used_no_surat_'+id).val();
		var idcustomer=$('#used_idcustomer_'+id).val();
		var namacustomer=$('#used_namacustomer_'+id).val();
		var thickness=$('#used_thickness_'+id).val();
		var weight=$('#used_weight_'+id).val();
		var qtycoil=$('#used_qtycoil_'+id).val();
		var width=$('#used_width_'+id).val();
		var totalwidth=$('#used_totalwidth_'+id).val();
		var delivery=$('#used_delivery_'+id).val();
		var id_material=$('#id_material').val();
		var qcoil=$('#qcoil').val();
		var lpegangan=$('#lpegangan').val();
		var jpisau=$('#jpisau').val();
		var sisa=$('#sisa').val();
		var widthmother=$('#width').val();
		var used=$('#used').val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariQtyCoil',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_qcoil").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CarijPisau',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_jpisau").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariSisa',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $("#slot_sisa").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/LockSpk',
            data:"id="+id+"&used="+used+"&widthmother="+widthmother+"&id_material="+id_material+"&namacustomer="+namacustomer+"&idcustomer="+idcustomer+"&dtno_surat="+dtno_surat+"&qcoil="+qcoil+"&lpegangan="+lpegangan+"&jpisau="+jpisau+"&sisa="+sisa+"&nmmaterial="+nmmaterial+"&thickness="+thickness+"&weight="+weight+"&qtycoil="+qtycoil+"&width="+width+"&totalwidth="+totalwidth+"&delivery="+delivery,
            success:function(html){
               $('#list_spk #tr_'+id).html(html);
            }
        });
    }

	function AksiDetail(id){
	    var hgdeal=$('#dp_hgdeal_'+id).val();
		var qty=$('#dp_qty_'+id).val();
		var weight=$('#dp_weight_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_marketing/totalw',
            data:"hgdeal="+hgdeal+"&qty="+qty+"&weight="+weight+"&id="+id,
            success:function(html){
               $('#total_weight_'+id).html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_marketing/totalhg',
            data:"hgdeal="+hgdeal+"&qty="+qty+"&weight="+weight+"&id="+id,
            success:function(html){
               $('#total_harga_'+id).html(html);
            }
        });
	}
	function HitungTotalCoil(id){
	    var qtycoil=$('#used_qtycoil_'+id).val();
		var width=$('#used_width_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/HitungTotalWidth',
            data:"qtycoil="+qtycoil+"&width="+width+"&id="+id,
            success:function(html){
               $('#twidth_'+id).html(html);
            }
        });
	}
	function Caristock(){
        var id_material=$("#id_material").val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/FindingStock',
            data:"id_material="+id_material,
            success:function(html){
               $("#lotnumbe_slot").html(html);
			   $('.select').select2();
            }
        });

    }
	function CariDetail(id){
		var width = getNum($('#width').val().split(",").join(""));
		var kg_process = getNum($('#kg_process').val().split(",").join(""));

        var id_marketing=$('#used_no_surat_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariIdCustomer',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#idcust_'+id).html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariNamaCustomer',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#nmcust_'+id).html(html);
				if(id_marketing == 'nonspk'){
					$("#used_namacustomer_"+id).removeAttr("readonly");
				}else{
					$("#used_namacustomer_"+id).attr("readonly","readonly");
				}
			   
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariW1material',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#weight_'+id).html(html);
			   $('.autoNumeric').autoNumeric();
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariQrollmaterial',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#qtyproduk_'+id).html(html);
			   $('.autoNumeric').autoNumeric();
            }
        });
				$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariW2material',
            data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
            success:function(html){
               $('#width2_'+id).html(html);
				
            }
        });
		// $.ajax({
        //     type:"GET",
        //     url:siteurl+'spk_produksi/CariTW2material',
        //     data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
        //     success:function(html){
        //        $('#twidth_'+id).html(html);
        //     }
        // });
				$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariDelivermaterial',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#delivery_'+id).html(html);
			   if(id_marketing == 'nonspk'){
					$("#used_delivery_"+id).removeAttr("required");
				}else{
					$("#used_delivery_"+id).attr("required","required");
				}
            }
        });

    }
	function get_properties(){
        var id_produk=$("#id_produk").val();
		var lebar_coil=$("#lebar_coil").val();
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetThickness',
            data:"id_produk="+id_produk,
            success:function(html){
               $("#thickness_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetDensity',
            data:"id_produk="+id_produk,
            success:function(html){
               $("#density_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetSurface',
            data:"id_produk="+id_produk,
            success:function(html){
               $("#surface_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetPotongan',
            data:"id_produk="+id_produk,
            success:function(html){
               $("#potongan_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetStock',
            data:"id_produk="+id_produk+"&lebar_coil="+lebar_coil,
            success:function(html){
               $("#stock_slot").html(html);
            }
        });

    }

	function HapusItem(id){
		$('#list_spk #tr_'+id).remove();
		changeChecked();
	}

	//tambahan arwant 
	$(document).on('keyup','#process',function(){
		calHeader();
	});

	$(document).on('keyup','#lpegangan',function(){
		changeChecked();
	});

	$(document).on('keyup','.change_weightc',function(){
		var id = $(this).attr('id');
		var a = id.split('_');
		calDetail(a[2]);
	});

	const calHeader = () => {
		let dataX = {
			weight 		: getNum($('#weight').val().split(",").join("")),
			process 	: getNum($('#process').val().split(",").join("")),
			density 	: getNum($('#density').val().split(",").join("")),
			width 		: getNum($('#width').val().split(",").join("")),
			thickness 	: getNum($('#thickness').val().split(",").join(""))
		};

		let kg_process 	= dataX.weight/dataX.process;
		let length 		= dataX.weight * 1000 / (dataX.width * dataX.density * dataX.thickness);
		let count_m 	= (kg_process * length) / dataX.weight;

		$('#kg_process').val(number_format(kg_process,2));
		$('#length').val(number_format(length,2));
		$('#count_m').val(number_format(count_m,2));

		changeChecked();
	}

	const calDetail = (a) => {
		let dataX = {
			used_weight : getNum($('#used_weight_'+a).val().split(",").join("")),
			width 		: getNum($('#width').val().split(",").join("")),
			kg_process 	: getNum($('#kg_process').val().split(",").join("")),
			used_qtycoil: getNum($('#used_qtycoil_'+a).val().split(",").join(""))
		};

		let hasil_kg 	= dataX.used_weight * dataX.kg_process / dataX.width;
		let total 		= dataX.used_qtycoil * hasil_kg;

		$('#used_weight2_'+a).val(number_format(hasil_kg,2));
		$('#used_totalwidth_'+a).val(number_format(total,2));

		changeChecked();
	}

	$(document).on('click','.chk_personal', function(){
		if ($(this).is(':checked')) {
			changeChecked();
		}
		else{
			changeChecked();
		}
	});

	const changeChecked = () => {
		let dataX = {
			width 		: getNum($('#width').val().split(",").join("")),
			lpegangan 	: getNum($('#lpegangan').val().split(",").join(""))
		};

		let SUM_COIL = 0;
		let SUM_TOT = 0
		$(".chk_personal" ).each(function() {
			if ($(this).is(':checked')) {
				let id 		= $(this).val();
				
				SUM_COIL 	+= Number($('#used_qtycoil_'+id).val().split(",").join(""));
				SUM_TOT 	+= Number($('#used_weight_'+id).val().split(",").join("")) * Number($('#used_qtycoil_'+id).val().split(",").join(""));
			}
		});

		let jumlah_pisau = SUM_COIL * 2 + 2;
		let sisa_potongan = dataX.width - dataX.lpegangan - SUM_TOT;
		
		$('#qcoil').val(number_format(SUM_COIL,2));
		$('#jpisau').val(number_format(jumlah_pisau,2));
		$('#sisa').val(number_format(sisa_potongan,2));
	}

	function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
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