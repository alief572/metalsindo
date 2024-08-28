<?php
	$tanggal = date('Y-m-d');
?>

<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer" ><h3>Incoming Customer</h3></label></center>
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
										<label for="id_customer">Customer</label>
									</div>
									<div class="col-md-8"> 
										<select id="id_customer" name="id_customer" class="form-control input-md select2" required>
											<option value="">--Pilih--</option>
												<?php foreach ($results['customer'] as $customer){?> 
											<option value="<?= $customer->id_customer?>" ><?= $customer->name_customer?></option>
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
								<div class="form-group row" id='pib_label'>
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
										<label id="lbl_inv">Packing List</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control"  id="no_invoice"  required name="no_invoice">
									</div>
								</div>
							</div>
						</div>
					</div>
								
		     <div class="col-sm-12">
						<b></b>
						<div class='box-tool pull-right'>
						<?php
							echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-payment'));
						?>
						</div>
						<table class='table table-bordered table-striped'>
							<thead>
								<tr class='table-bordered table-striped bg-blue'>
									<td align='center'><b>Nama Material</b></td>
									<td align='center'><b>Width</b></td>
									<td align='center'><b>Berat Coil</b></td>
									<td align='center'><b>Nomor Lot</b></td>
									<td align='center'><b>Gudang</b></td>
									<td align='center'><b>Action</b></td>
								</tr>
								
							</thead>
							<tbody id='list_payment'>
								
							</tbody>
						</table>
						</div>		  
		<hr>
		<center>
		<!--<button type="submit" class="btn btn-primary btn-sm add_field_button2" name="save"><i class="fa fa-plus"></i>Add Main Produk</button>
		--><button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button> 
		</center>
		
	  </form>
	</div>			  
</div>

<script type="text/javascript">
	
$(document).ready(function(){
	    $('.select2').select2({width:'100%'});
	    var base_url			= '<?php echo base_url(); ?>';
		var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

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
					Template	+='<option value="<?= $material->id_category3?>"><?= ucfirst(strtolower($material->nama."|".$material->maker."|".$material->negara))?></option>';
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
			var idtype	= $('#inventory_4').val();
			
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
						var baseurl=siteurl+'incoming/saveIncomingCustomer';
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
									window.location.href = base_url + active_controller+'/index_customer';
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
		
		
		$(function() {
		$('.chosen-select').select2({ width: '100%' });
		
		$('#tanggal').datepicker({
			format : 'yyyy-mm-dd'
			// minDate: 0
		});
    });
		
	});



function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
	
	
</script>