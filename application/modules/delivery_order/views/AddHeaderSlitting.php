<?php
    $ENABLE_ADD     = has_permission('Trans_inquiry.Add');
    $ENABLE_MANAGE  = has_permission('Trans_inquiry.Manage');
    $ENABLE_VIEW    = has_permission('Trans_inquiry.View');
    $ENABLE_DELETE  = has_permission('Trans_inquiry.Delete');
	$tanggal = date('Y-m-d');
?>
<input type='hidden' id='urut' value='0'>
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete='off'>
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
			<div class="row">
		<center><label for="customer" ><h3>Delivery Order</h3></label></center>
		<div class="col-sm-12">
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="no_spk">Customer</label>
				</div>
				<div class="col-md-8">
					<select id="id_customerx" name="id_customerx" class="form-control select customerx" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['customer'] as $penawaran){?>
						<option value="<?= $penawaran->id_customer?>"><?= strtoupper(strtolower($penawaran->name_customer))?></option>
							<?php } ?>
					</select>
				</div>
			</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row" >
			<div class="col-md-4">
					<label for="no_spk">No. Spk Marketing</label>
				</div>
				<div class="col-md-8">
					<select id="no_spk" name="no_spk" class="form-control select" onchange="get_produk()" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['penawaran'] as $penawaran){?>
						<option value="<?= $penawaran->id_spkmarketing?>"><?= $penawaran->no_surat?></option>
							<?php } ?>
					</select>
				</div>
			<div class="col-md-8" hidden>
				<input type="text" class="form-control" id="nama_customer" onkeyup required name="nama_customer" readonly >
			    <input type="text" class="form-control" id="no_surat" onkeyup required name="no_surat" readonly >
				<input type="text" class="form-control" id="id_customer" onkeyup required name="id_customer" readonly >
			</div>
		</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
				<label for="reff">Reff</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="reff" onkeyup required name="reff" >
			</div>
			</div>
		</div>
		</div>
		
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row" id="driver">
			<div class="col-md-4">
				<label for="driver">Driver</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="driver" name="driver" value='Anwar'>
			</div>
			
		</div>
		</div>
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="driver">No Kendaraan</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="nopol" name="nopol" value='B 9685 FDC'>
			</div>
		</div>
		</div>
		
		</div>
		
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row"> 
			<div class="col-md-4">
				<label for="driver">Tanggal Cetak</label>
			</div>
			<div class="col-md-8">
				<input type="date" class="form-control" id="tanggal" name="tanggal">
			</div>
			
		</div>
		</div>
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				
			</div>
			<div class="col-md-8">
				
			</div>
		</div>
		</div>
		
		</div>
		

		<div class="col-sm-12">
			<div class="form-group row" >
				<table class='table table-bordered table-striped' id='MyTable'>
					<thead>
						<tr class='bg-blue'>
						    <th class='text-center' width='10%'>Id Material</th>
							<th class='text-center' width='30%'>Material</th>
							<th class='text-center' width='7%'>Width</th>
							<th class='text-center' width='7%'>Length</th>
							<th class='text-center' width='7%'>Qty Order</th>
							<th class='text-center' width='5%'>Action</th>
							<th class='text-center' width='20%'>Lot</th>
							<th class='text-center' width='5%' >Qty</th>
							<th class='text-center' width='5%'>Berat</th>
							<th class='text-center' width='10%'>Remarks</th>
							<th class='text-center' width='4%'>#</th>
						</tr>
					</thead>
					<tbody id="list_penawaran_slot"></tbody>
					<tbody id='list_penawaran_slot_2'></tbody>
					
					<tfoot>
					<tr class='bg-grey'>
						    <th class='text-center' width='10%'></th>
							<th class='text-center' width='30%'></th>
							<th class='text-center' width='7%'></th>
							<th class='text-center' width='7%'></th>
							<th class='text-center' width='7%'></th>
							<th class='text-center' width='5%'></th>
							<th class='text-center' width='20%'>Total</th>
							<th class='text-center' width='5%' ><input type="text" class="form-control" id="total_item" name="total_item" readonly></th>
							<th class='text-center' width='10%'><input type="text" class="form-control" id="total_berat" name="total_berat" readonly></th>
							<th class='text-center' colspan='2' width='20%'></th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		
		<!--SCRAP-->
		
		<center><label for="customer" ><h3>SCRAP</h3></label></center>
		      <div id='po_1'>
					<input type='hidden' id='pancingan_1' value='1'>
					<div class='col-md-4'>
								<label >No. SPK PRODUKSI</label>
					</div>
					<div class='col-md-4'>
							<select id='dt_nopo' name='dt_nopo' class='form-control input-md select' onchange='return TambahMaterial(1)' required>
									<option value="">--Pilih--</option>
									<?php foreach ($results['produksi'] as $produksi){?>
									<option value="<?= $produksi->id_tr_spk_produksi?>"><?= $produksi->no_surat?></option>
										<?php } ?>
					</select>
					</div>
					<br>
					<div class='form-group row' >
						<table class='table table-bordered table-striped'>
						<thead>
						<tr class='bg-blue'>
						    <th class='text-center' width='10%'>Id Material</th>
							<th class='text-center' width='30%'>Material</th>
							<th class='text-center' width='20%'>Lot</th>
							<th class='text-center' width='20%'>Qty</th>
							<th class='text-center' width='5%'>Berat (Kg)</th>
							<th class='text-center' width='10%'>Remarks</th>
							<th class='text-center' width='4%'>#</th>
						</tr>
					   </thead>
						<tbody id='data_request'>
						</tbody>
						</table>
					</div>
				</div>
			 <!-- END SCRAP -->
			<center>
				<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com">Simpan</button>
				<a class="btn btn-danger btn-sm" href="<?= base_url('/delivery_order/') ?>"  title="Edit">Kembali</a>
			</center>
			</div>
		</div>
		</form>		  
	</div>
</div>	
	
				  
				  
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>			  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	
	$(document).ready(function(){	
	  	
		var max_fields2      = 10; //maximum input boxes allowed
		var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2      = $(".add_field_button2"); //Add button ID	
		$('.select').select2();	

		$(document).on('change', '#id_customerx', function(e){
			e.preventDefault();
			$.ajax({
				url: siteurl+'delivery_order/get_penawaran',
				cache: false,
				type: "POST",
				data: "id="+this.value,
				dataType: "json",
				success: function(data){
					$("#no_spk").html(data.option).trigger("chosen:updated");
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Timed Out ...',
						type				: "warning",
						timer				: 5000
					});
				}
			});
		});
		
		
		
		$('#add-payment').click(function(){
			var jumlah	=$('#list_payment').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_payment tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1; 
			}
			Template	='<tr id="tr_'+loop+'">';
			Template	+='<td align="left">';
					Template	+='<select id="id_material" name="data1['+loop+'][id_material]" id="data1_'+loop+'_id_material" class="form-control select2" required>';
					Template	+='<option value="">-- Pilih Material --</option>';
					Template	+='<?php foreach ($results["material"] as $material){?>';
					Template	+='<option value="<?= $material->id_category3?>"><?= ucfirst(strtolower($material->nama))?></option>';
					Template	+='<?php } ?>';
					Template	+='</select>';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][width]" id="data1_'+loop+'_width" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][berat_coil]" id="data1_'+loop+'_berat_coil" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][lotno]" id="data1_'+loop+'_lotno" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<select id="gudang" name="data1['+loop+'][gudang]" id="data1_'+loop+'_gudang" class="form-control select2" required>';
					Template	+='<option value="">-- Pilih Gudang --</option>';
					Template	+='<?php foreach ($results["gudang"] as $gudang){?>';
					Template	+='<option value="<?= $gudang->id_gudang?>"><?= ucfirst(strtolower($gudang->nama_gudang))?></option>';
					Template	+='<?php } ?>';
					Template	+='</select>';
			Template	+='</td>';
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_payment').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			
			 $('.select2').select2({width:'100%'});
			 
			 
			});

		$('#simpan-com').click(function(e){
			e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			var idtype	= $('#inventory_1').val();
			var tanggal	= $('#tanggal').val();
			// alert(tanggal);
			// return false;
			
			if(tanggal ==''){
					swal("Warning", "Tanggal Tidak Boleh Kosong :)", "error");
					return false;
			}else{
				
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
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'delivery_order/SaveNewHeaderSlitting';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false, 
							contentType	: false,				
							success		: function(data){								
								if(data.status == 1){											
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{
									
									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}
									
								}
							},
							error: function() {
								
								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',						
								  type				: "warning",								  
								  timer				: 7000,
								  showCancelButton	: false,
								  showConfirmButton	: false,
								  allowOutsideClick	: false
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

	function get_produk(){ 
        var no_penawaran=$("#no_spk").val();
		
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/GetCustomer',
            data:"no_penawaran="+no_penawaran,
			dataType	: 'json',
            success:function(html){
               $("#nama_customer").val(html.name_customer);
               $("#no_surat").val(html.nosurat);
               $("#id_customer").val(html.id_customer);
               $("#reff").val(html.reff);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/GetPenawaran',
            data:"no_penawaran="+no_penawaran,
            success:function(html){
               $("#list_penawaran_slot").html(html);
			   $('.select').select2();
			   $('.autoNumeric').autoNumeric();
			   
			   SumDel();
            }
        });
		
		
    }

	function get_lebar(){ 
        var id_produk=$("#id_produk").val();
		var lebar_coil=$("#lebar_coil").val();
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetStock',
            data:"id_produk="+id_produk+"&lebar_coil="+lebar_coil,
            success:function(html){
               $("#stock_slot").html(html);
            }
        });
    }

	function AksiDetail(id){
	    var hgdeal=$('#dp_hgdeal_'+id).val();
		var qty=$('#dp_qty_'+id).val();
		var weight=$('#dp_weight_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/totalw',
            data:"hgdeal="+hgdeal+"&qty="+qty+"&weight="+weight+"&id="+id,
            success:function(html){
               $('#total_weight_'+id).html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/totalhg',
            data:"hgdeal="+hgdeal+"&qty="+qty+"&weight="+weight+"&id="+id,
            success:function(html){
               $('#total_harga_'+id).html(html);
            }
        });
	}

	function HitungPisau(id){
	    var qty=$('#stok_qty_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/HitungPisau',
            data:"qty="+qty+"&id="+id,
            success:function(html){
               $('#pisau_'+id).html(html);
            }
        });
	}

	function TambahItem(id){
	   	var idstk=$('#stok_idstk_'+id).val();
		var lotno=$('#stok_lotno_'+id).val();
		var namamaterial=$('#stok_namamaterial_'+id).val();
		var weight=$('#stok_weight_'+id).val();
		var density=$('#stok_density_'+id).val();
		var hasilpanjang=$('#stok_hasilpanjang_'+id).val();
		var width=$('#stok_width_'+id).val();
		var lebarcc=$('#stok_lebarcc_'+id).val();
		var jumlahcc=$('#stok_jumlahcc_'+id).val();
		var sisapotongan=$('#stok_sisapotongan_'+id).val();
		var qtystock=$('#stok_qty_'+id).val();
		var jumlahpisau=$('#stok_jmlpisau_'+id).val();
		var total_panjang=$("#total_panjang").val();
		var jml_pisau=$("#jml_pisau").val();
		var jml_mother=$("#jml_mother").val();
		var total_berat=$("#total_berat").val();
		var thickness=$("#thickness").val();
		var qty=$("#qty").val();
		var jumlah	=$('#used_slot').find('tr').length;
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/HitungTPanjang',
            data:"hasilpanjang="+hasilpanjang+"&total_panjang="+total_panjang,
            success:function(html){
               $("#tpanjang_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/HitungJPisau',
            data:"jumlahpisau="+jumlahpisau+"&jml_pisau="+jml_pisau,
            success:function(html){
               $("#jpisau_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/HitungJmother',
            data:"jml_mother="+jml_mother,
            success:function(html){
               $("#mother_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/HitungTBerat',
           data:"hasilpanjang="+hasilpanjang+"&total_panjang="+total_panjang+"&thickness="+thickness+"&lebarcc="+lebarcc+"&density="+density,
            success:function(html){
               $("#tberat_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetUsed',
            data:"idstk="+idstk+"&lotno="+lotno+"&namamaterial="+namamaterial+"&jumlah="+jumlah+"&weight="+weight+"&density="+density+"&hasilpanjang="+hasilpanjang+"&width="+width+"&lebarcc="+lebarcc+"&jumlahcc="+jumlahcc+"&sisapotongan="+sisapotongan+"&qtystock="+qtystock+"&jumlahpisau="+jumlahpisau,
            success:function(html){
               $("#used_slot").append(html);
            }
        });
	}

	function get_properties(){
        var id_produk=$("#id_produk").val();
		var lebar_coil=$("#lebar_coil").val();
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_shearing/GetMaterial',
            data:"id_produk="+id_produk,
            success:function(html){
               $("#material_slot").html(html);
            }
        });
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

	function DelItem(id){
		$('#data_barang #tr_'+id).remove();
	}
	
	function CariProperties(id){	
        var idpr=$("#dt_lot_"+id).val();
		 $.ajax({
            type:"GET",
            url:siteurl+'delivery_order/CariIdMaterial',
            data:"idpr="+idpr+"&id="+id,
            success:function(html){
               $("#idmaterial_"+id).html(html); 
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/CariWeightMaterial',
            data:"idpr="+idpr+"&id="+id,
            success:function(html){
               $("#weightmaterial_"+id).html(html); 
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/CariBentukMaterial',
            data:"idpr="+idpr+"&id="+id,
            success:function(html){
               $("#bentuk_"+id).html(html); 
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/CariNolotMaterial',
            data:"idpr="+idpr+"&id="+id,
            success:function(html){
               $("#nolot_"+id).html(html); 
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/CariNoslitMaterial',
            data:"idpr="+idpr+"&id="+id,
            success:function(html){
               $("#noslit_"+id).html(html); 
            }
        });
				// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariPanjangMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#panjang_"+id).html(html); 
            // }
        // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariLebarMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#lebar_"+id).html(html); 
            // }
        // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariDescripitionMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#description_"+id).html(html);
            // }
        // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariQtyMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#qty_"+id).html(html);
            // }
        // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariweightMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#width_"+id).html(html);
            // }
        // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/CariTweightMaterial',
            // data:"idpr="+idpr+"&id="+id,
            // success:function(html){
               // $("#totalwidth_"+id).html(html);
            // }
        // });
    }
	
	function addmaterial(id){ 
	    
		var jumlah	=$("#list_penawaran_slot_"+id).find('tr').length; 
	    var no_penawaran=$("#no_spk").val();
		var id_dt=$("#dp_id_dt_spkmarketing_"+id).val();

        var urut = parseFloat($("#urut").val()) + 1;

        $("#urut").val(urut);
		// console.log(jumlah)
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/GetCustomer',
            data:"no_penawaran="+no_penawaran,
            success:function(html){
               $("#slot_customer").html(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddMaterial1',
            data:"jumlah="+jumlah+"&no_penawaran="+no_penawaran+"&id_dt="+id_dt+"&nomor="+id+"&urut="+urut,
            success:function(html){
               $("#det_lot1_"+id).append(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddMaterial2',
            data:"jumlah="+jumlah+"&no_penawaran="+no_penawaran+"&id_dt="+id_dt+"&nomor="+id+"&urut="+urut,
            success:function(html){
               $("#det_lot2_"+id).append(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddMaterial3',
            data:"jumlah="+jumlah+"&no_penawaran="+no_penawaran+"&id_dt="+id_dt+"&nomor="+id+"&urut="+urut,
            success:function(html){
               $("#det_lot3_"+id).append(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddMaterial4',
            data:"jumlah="+jumlah+"&no_penawaran="+no_penawaran+"&id_dt="+id_dt+"&nomor="+id+"&urut="+urut,
            success:function(html){
               $("#det_lot4_"+id).append(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddMaterial5',
            data:"jumlah="+jumlah+"&no_penawaran="+no_penawaran+"&id_dt="+id_dt+"&nomor="+id+"&urut="+urut,
            success:function(html){
               $("#det_lot5_"+id).append(html);
            }
        });
		
		// var jumlah	=$('#data_request').find('tr').length;
		// var angka =jumlah+1;
		
		// $.ajax({
            // type:"GET",
            // url:siteurl+'delivery_order/AddMaterial',
            // data:"jumlah="+jumlah,
			 // data:"jumlah="+jumlah+"&id_suplier="+id_suplier+"&loi="+loi,
            // success:function(html){
               // $("#list_penawaran_slot2").append(html);
			   // $(".bilangan-desimal").maskMoney();
			   // $(".chosen-select").select2({ width: '100%' });
            //}
       // });
		// $.ajax({
            // type:"GET",
            // url:siteurl+'purchase_order/UbahImport',
            // data:"loi="+loi,
            // success:function(html){
               // $("ubahloi").html(html);
            // }
        // });
		
    }
	
	function HapusItem(id){
		$('.del'+id).remove();
	}
	
	//ARWANT
	$(document).on('click','.plus', function(){
		var no 		= $(this).data('id');
		var kolom	= parseFloat($(this).parent().parent().find("td:nth-child(1)").attr('rowspan')) + 1;
		
		$(this).parent().parent().find("td:nth-child(6)").attr('rowspan', kolom);
		let html_list = $('#list_'+no).html().replaceAll('selected', '');

		var Rows	= "<tr>";
			Rows	+= "<td align='left'>";
			Rows	+= 		"<select name='dp["+no+"][detail]["+kolom+"][lot]' class='form-control select changeLot'>"+html_list+"</select>";
			Rows	+= 		"<input type='hidden' value='1' name='dp["+no+"][detail]["+kolom+"][bantuan]' readonly>";
			Rows	+= "</td>";
			Rows	+= "<td align='center'><input type='text' name='dp["+no+"][detail]["+kolom+"][qty]' data-no='"+no+"' class='form-control input-sm text-right autoNumeric qty' readonly></td>";
			Rows	+= "<td align='center'><input type='text' name='dp["+no+"][detail]["+kolom+"][weight]' data-no='"+no+"' class='form-control input-sm text-right autoNumeric weight' readonly></td>";
			Rows	+= "<td align='center'><input type='text' name='dp["+no+"][detail]["+kolom+"][qty_mat]' data-no='"+no+"' class='form-control input-sm text-right autoNumeric' placeholder='Qty'></td>";
			Rows	+= "<td align='center'><input type='text' name='dp["+no+"][detail]["+kolom+"][weight_mat]' data-no='"+no+"' class='form-control input-sm text-right autoNumeric' placeholder='Weight'></td>";
			Rows	+= "<td align='center'><input type='text' name='dp["+no+"][detail]["+kolom+"][remarks]' data-no='"+no+"' class='form-control input-sm text-left' placeholder='Remarks'></td>";
			Rows	+= "<td align='center'>";
			Rows	+= "<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-id='"+no+"'><i class='fa fa-trash'></i></button>";
			Rows	+= "</td>";
			Rows	+= "</tr>"; 
		// alert(Rows);
		$(this).parent().parent().after(Rows);
		
		$('.autoNumeric').autoNumeric();
		$('.select').select2();
	});

	$(document).on('click','.delete', function(){
		var no 		= $(this).data('id');
		var kolom	= parseFloat($(".baris_"+no).find("td:nth-child(1)").attr('rowspan')) - 1;
		$(".baris_"+no).find("td:nth-child(1), td:nth-child(2), td:nth-child(3), td:nth-child(4), td:nth-child(5)").attr('rowspan', kolom);
		
		$(this).parent().parent().remove();
	});

	$(document).on('change','.changeLot', function(){
		let lot = $(this).val();
		let change = $(this);
		var no 		= $(this).data('id');
		var totalqty   = $('#totalqty'+no).val();
	    var totalberat = $('#totalberat'+no).val();
		var baris      = $(this).data('baris');
		
		console.log(baris);
		
		$.ajax({
            type:"POST",
            url:siteurl+'delivery_order/getStockLot',
			data: {
				"lot" 	: lot
			},
			cache		: false,
			dataType	: 'json',
            success:function(data){
               change.parent().parent().find('.qty').val(data.qty);
               change.parent().parent().find('.weight').val(data.berat);
			   change.parent().parent().find('.numberlot').val(data.lot);
			   
			   var ttl_qty =parseFloat(totalqty)+parseFloat(data.qty);
			   var ttl_brt =parseFloat(totalberat)+parseFloat(data.berat);
			   
			   $('#totalqty'+no).val(ttl_qty);
			   $('#totalberat'+no).val(number_format(ttl_brt,2));
			  
			   
            }
        });
	});
	
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
		
		
		
	function delRow1(row,no){
					 
	   $('.baris_'+row).remove();	   	
		
		
	}
	
		
	function cancel(row,no,idstok){
		
				console.log(idstok);
				
		
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
						$.ajax({ 
						type:"POST",
						url:siteurl+'delivery_order/update_stok_spk',
						data:"idstok="+idstok,
						success:function(data){
						  delRow(row,no);
						}
						});
				  }else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
				  
				});
		}
	
	
	
	
	function delRow(row,no){
		
		
		var qty 	= $('#dp_qty_mat_'+row).val().split(",").join("");
		var berat 	= $('#dp_weight_mat_'+row).val().split(",").join("");
		
		
		
		var totalqty 	= $('#totalqty'+no).val().split(",").join("");
		var totalberat 	= $('#totalberat'+no).val().split(",").join("");
		
		
		
		
		var ttl_qty =parseFloat(totalqty)-parseFloat(qty);
		var ttl_brt =parseFloat(totalberat)-parseFloat(berat);
		
					   
       $('#totalqty'+no).val(ttl_qty);
	   $('#totalberat'+no).val(ttl_brt);
			 
	   $('.baris_'+row).remove();
	   
	   
	   
	   SumDel();		
		
		
	}
	
	
	function SumDel(){
		var SUM_QTY = 0
		var SUM_BERAT = 0
		var SUM_JML = 0
		
		$(".qty" ).each(function() {
			SUM_QTY += Number($(this).val().split(",").join(""));
		});

		$(".berat" ).each(function() {
			SUM_BERAT += Number($(this).val().split(",").join(""));
		});
		
		SUM_JML = parseFloat(SUM_QTY)*parseFloat(SUM_BERAT);
		


		$("#total_item").val(number_format(SUM_QTY,2));
		$("#total_berat").val(number_format(SUM_BERAT,2));
		$("#subtotal_berat").val(number_format(SUM_JML,2));
		

    }
	
	$(document).on('click','.pluss', function(){
		
		var no 		        = $(this).data('id');
		var jumlah          = $("#tambah_"+no).find('tr').length; 
		
		var idMaterial 		= $('#id_material_'+no).val();
		var nmMaterial 		= $('#material_'+no).val();
		var width    		= $('#width_'+no).val().split(",").join("");
		var length  		= $('#length_'+no).val().split(",").join("");
		var order  		    = $('#qty_'+no).val().split(",").join("");
		
		 var no_penawaran=$("#no_spk").val();
		
		$.ajax({ 
            type:"GET",
            url:siteurl+'delivery_order/AddGetPenawaran',
            data:"id_material="+idMaterial+"&nm_material="+nmMaterial+"&width="+width+"&length="+length+"&qty_order="+order+"&nomor="+no+"&jumlah="+jumlah+"&no_penawaran="+no_penawaran,
            success:function(html){
               $("#tambah_"+no).append(html);
            }
        });
	});
	
	function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
	
	
	function TambahMaterial(id){ 
		var nomor = Number(id);
		var nopo=$("#dt_nopo").val(); 
		//var no=$("#pancingan_"+nomor).val();
		var jumlah	=$('#data_request').find('tr').length;
		var no =jumlah+1;
		// console.log(nomor)
		$.ajax({
            type:"GET",
            url:siteurl+'delivery_order/TambahData',
            data:"nopo="+nopo+"&id="+nomor+"&no="+no,
            success:function(html){
			$("#data_request").append(html);
			   $(".chosen-select").select2({ width: '100%' });
			   $('.autoNumeric').autoNumeric();
            }
        });
    }
	
	$(document).on('click','.cancelSubPart', function(){
				var no1 = $(this).data('no1');
				var no2 = $(this).data('no2');
				var nomor = $('#no_sebelumnya'+no1+'_'+no2).val();
				$('#trmaterial_'+no1+'_'+no2).remove();
				
				$("#tombol"+nomor).show();
			});
</script>