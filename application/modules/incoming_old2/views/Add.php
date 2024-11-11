<?php
	$tanggal = date('Y-m-d');
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
			<div class="row">
		<center><label for="customer" ><h3>Incoming</h3></label></center>
				<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">NO.Dokumen</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="id_incoming"  required name="id_incoming" readonly placeholder="No.Dokumen">
			</div>
		</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">Supplier</label>
				</div>
				<div class="col-md-8"> 
					<select id="id_suplier" name="id_suplier" class="form-control input-md chosen-select" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['suplier'] as $suplier){?> 
						<option value="<?= $suplier->id_suplier?>" ><?= ucfirst(strtolower($suplier->name_suplier))?></option>
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
				<label for="customer">Tanggal Kedatangan</label>
			</div>
			<div class="col-md-8">
				<input type="date" class="form-control" value="<?= $tanggal?>" id="tanggal"  required name="tanggal">
			</div>
		</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">Gudang</label>
				</div>
				<div class="col-md-8"> 
					<select id="id_gudang" name="id_gudang" class="form-control input-md chosen-select" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['gudang'] as $gudang){?> 
						<option value="<?= $gudang->id_gudang?>" ><?= ucfirst(strtolower($gudang->nama_gudang))?></option>
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
				<label for="customer">PIC</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control"  id="pic"  required name="pic" required>
			</div>
		</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">Keterangan</label>
				</div>
			<div class="col-md-8">
				<input type="text" class="form-control"  id="ket"  required name="ket">
			</div>
			</div>
		</div>
		</div>
				<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">PIB</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control"  id="pib"  required name="pib" required>
			</div>
		</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">No. nvoice</label>
				</div>
			<div class="col-md-8">
				<input type="text" class="form-control"  id="no_invoice"  required name="no_invoice">
			</div>
			</div>
		</div>
		</div>
		<div class="form-group row" >
		<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
			<thead>
			<tr class='bg-blue'>
			<th width='10%' >Nomor PO</th>
			<th width='20%'>Material</th>
			<th width='7%'>Tanggal</th>		
			<th width='7%'>Length</th>
			<th width='7%'>Width</th>
			<th width='7%'>Weight Per Coil</th>
			<th width='7%'>Qty Order (Coil)</th>
			<th width='7%'>Qty Recive (Coil)</th>
			<th width='7%'>Lot. No</th>
			<th width='7%'>Action</th>
			</tr>
			</thead>
			<tbody id='tabelForm'>
			</tbody>
						<thead>
			</thead>
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
	
				  
				  
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js');?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>		
<script>
	$(function() {
		$('.chosen-select').select2({ width: '100%' });
		
		$('#tanggal').datepicker({
			format : 'yyyy-mm-dd'
			// minDate: 0
		});
    });

</script>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
		$(document).on('click', '.addPart', function(){
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];
			var id_suplier 		=$('#id_suplier').val(); 
		if(id_suplier == '' || id_suplier == null){
			swal("Warning", "Supplier Tidak Boleh Kosong", "error");
			return false;	
		}else{
			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_add/'+id+'/'+id_suplier,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.maskM').maskMoney();
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		}
		});

		//add part
		$(document).on('click', '.addSubPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 			= split_id[1];
			var id2 		= parseInt(split_id[2])+1;
			var id_bef 		= split_id[2];
			var no_po 		=$('#dt_nopo_'+id).val();
				if(no_po == '' || no_po == null){
			swal("Cancelled", "No PO Tidak Boleh Kosong", "error");
			return false;	
		}else{
			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_add_sub/'+id+'/'+id2+'/'+no_po,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id+"_"+id_bef).before(data.header);
					$("#add_"+id+"_"+id_bef).remove();
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		}
		});

		//delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});
		
		$(document).on('click', '.hapusPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			var split_id	= get_id.split('_');
			var id 			= split_id[1];
			var id2 		= parseInt(split_id[2]);
			var total_harga_penawaran=$('#total_harga_penawaran').val();
		var total_panjang=$('#total_panjang').val();
		var jml_pisau=$('#jml_pisau').val();
		var jml_mother=$('#jml_mother').val();
		var total_berat=$('#total_berat').val();
		var hargadeal=$('#dt_hargadeal_'+id).val();
		var qty=$('#dt_qty_'+id).val();
		var panjang=$('#dt_panjang_'+id).val();
		var jmlpisaudt=$('#dt_jmlpisau_'+id).val();
		var berat=$('#dt_berat_'+id).val();
		
		
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/MinHarga',
            data:"hargadeal="+hargadeal+"&total_harga_penawaran="+total_harga_penawaran,
            success:function(html){
               $('#slot_total_peawaran').html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/MinPanjang',
            data:"panjang="+panjang+"&total_panjang="+total_panjang+"&qty="+qty,
            success:function(html){
               $('#tpanjang_slot').html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/MinPisau',
            data:"jmlpisaudt="+jmlpisaudt+"&jml_pisau="+jml_pisau,
            success:function(html){
               $('#jpisau_slot').html(html);
            }
        });
		$.ajax({ 
            type:"GET",
            url:siteurl+'penawaran_slitting/MinMother',
            data:"qty="+qty+"&jml_mother="+jml_mother,
            success:function(html){
               $('#mother_slot').html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/MinBerat',
            data:"berat="+berat+"&total_berat="+total_berat+"&qty="+qty,
            success:function(html){
               $('#tberat_slot').html(html);
			   
            }
        });
			$("."+get_id).remove();
		});

		$(document).on('click', '.delSubPart', function(){
			var get_id 		= $(this).parent().parent('tr').html();
			$(this).parent().parent('tr').remove();
		});
		$(document).on('click', '.cancelSubPart', function(){
			var get_id 		= $(this).parent().parent('tr').html();
			var split_id	= get_id.split('_');
			var id 			= split_id[1];
			var id2 		= parseInt(split_id[2]);
			$(this).parent().parent('tr').remove();
		});
    //add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});
		$('#save').click(function(e){
			e.preventDefault();
			var produk		= $('#produk').val();
			var costcenter	= $('.costcenter').val();
			var process		= $('.process').val();

			if(produk == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(costcenter == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Costcenter empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(process == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Process name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}

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
						var baseurl=siteurl+'penawaran_slitting/save_cycletime';
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
		});

});
	
	
</script>