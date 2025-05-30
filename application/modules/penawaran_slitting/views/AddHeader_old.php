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
		<center><label for="customer" ><h3>PENAWARAN SLITTING</h3></label></center>
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">NO.Penawaran</label>
			</div>
			<div class="col-md-8" hidden>
				<input type="text" class="form-control" id="no_inquiry"  required name="no_inquiry" readonly placeholder="No.CRCL">
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="no_surat"  required name="no_surat" readonly placeholder="No.Penawaran">
			</div>
		</div>
		</div>
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">Tanggal</label>
			</div>
			<div class="col-md-8">
				<input type="date" class="form-control" id="tanggal" value="<?= $tanggal ?>" onkeyup required name="tanggal" readonly >
			</div>
		</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">CUSTOMER</label>
				</div>
				<div class="col-md-8">
					<select id="id_customer" name="id_customer" class="form-control select" onchange="get_customer()" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['customers'] as $customers){?>
						<option value="<?= $customers->id_customer?>"><?= strtoupper(strtolower($customers->name_customer))?></option>
							<?php } ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_category_supplier">SALES/MARKETING</label>
				</div>
				<div id="sales_slot">
				<div class='col-md-8' hidden>
					<input type='text' class='form-control' id='nama_sales'  required name='nama_sales' readonly placeholder='Sales Marketing'>
				</div>
				<div class='col-md-8'>
					<input type='text' class='form-control' id='id_sales'  required name='id_sales' readonly placeholder='Sales Marketing'>
				</div>
				</div>
			</div>
		</div>
		
		</div>
		<div class="col-sm-12">
		<div class="form-group row" >
		<div class="col-md-12">
		<div class='col-sm-6'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>EMAIL</label>
			</div>
			<div class='col-md-8' id="email_slot">
				<input type='email' class='form-control' id='email_customer' required name='email_customer' >
			</div>
		</div>
		</div>
		<div class='col-sm-6'>
			<div class='form-group row'>
				<div class='col-md-4'>
					<label for='id_category_supplier'>PIC CUSTOMER</label>
				</div>
				<div class='col-md-8' id="pic_slot" >
					<select id='pic_customer' name='pic_customer' class='form-control select' required>
						<option value=''>--Pilih--</option>
					</select>
				</div>
			</div>
		</div>
		</div>
		</div>
		</div>
				<div class="col-sm-12">
		<div class="form-group row" >
		<div class="col-md-12">
		<div class='col-sm-6'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>Kurs</label>
			</div>
			<div class='col-md-8'>
					<select id="mata_uang" name="mata_uang" class="form-control select" required>
							<option value="">--Pilih--</option>
						<option value="IDR">IDR(Rupiah)</option>
						<option value="USD">USD(Dolar)</option>
					</select>
			</div>
								</div>
		</div>
				<div class='col-sm-6'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>Pengiriman</label>
			</div>
			<div class='col-md-8' id="">
					<select id="pengiriman" name="pengiriman" class="form-control select" required>
							<option value="">--Pilih--</option>
						<option value="Franco">Franco</option>
						<option value="Loco">Loco</option>
					</select>
			</div>
		</div>
		</div>
		</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="form-group row" >
		<div class="col-md-12">
		<div class='col-sm-4'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>Valid Until</label>
			</div>
			<div class='col-md-8'>
				<input type='date' class='form-control' id='valid_until' required name='valid_until' >
			</div>
		</div>
		</div>
		<div class='col-sm-4'>
			<div class='form-group row'>
				<div class='col-md-4'>
					<label for='id_category_supplier'>Term Of Payment</label>
				</div>
				<div class='col-md-8' id="pic_slot" >
					<input type='text' class='form-control' id='terms_payment' required name='terms_payment' >
				</div>
			</div>
		</div>
		<div class='col-sm-4'>
			<div class='form-group row'>
				<div class='col-md-4'>
					<label for='id_category_supplier'>Exclude vat (%)</label>
				</div>
				<div class='col-md-8' >
					<input type='number' class='form-control' id='exclude_vat' required name='exclude_vat' >
				</div>
			</div>
		</div>
		</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="form-group row" >
		<div class="col-md-12">
		<div class='col-sm-12'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>Note</label>
			</div>
			<div class='col-md-8'>
				<textarea id="note" name="note" class='form-control col-md-12' rows="4" cols="2000"></textarea>
			</div>
		</div>
		</div>
		</div>
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
	
				  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	$(document).ready(function(){	
			var max_fields2      = 10; //maximum input boxes allowed
			var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
			var add_button2      = $(".add_field_button2"); //Add button ID		

			$('.select').select2({
				width: '100%'
			});
	$('#simpan-com').click(function(e){
			e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			var idtype	= $('#inventory_1').val();
			
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
						var baseurl=siteurl+'penawaran_slitting/SaveNewHeader';
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
									window.location.href = base_url + active_controller +'/detail/'+data.code;
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
	function get_customer(){
        var id_customer=$("#id_customer").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/getemail',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#email_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/getpic',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#pic_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'penawaran_slitting/getsales',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#sales_slot").html(html);
            }
        });
    }
function DelItem(id){
		$('#data_barang #tr_'+id).remove();
		
	}
	
	
	
</script>