<?php
    $ENABLE_ADD     = has_permission('Trans_inquiry.Add');
    $ENABLE_MANAGE  = has_permission('Trans_inquiry.Manage');
    $ENABLE_VIEW    = has_permission('Trans_inquiry.View');
    $ENABLE_DELETE  = has_permission('Trans_inquiry.Delete');
	$tanggal = date('Y-m-d');
		foreach ($results['head'] as $head){
	}	
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
			<div class="row">
		<center><label for="customer" ><h3>Penawaran</h3></label></center>
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">NO.Penawaran</label>
			</div>
			<div class="col-md-8" hidden>
				<input type="text" class="form-control" id="no_penawaran" value="<?= $head->no_penawaran ?>"  required name="no_penawaran" readonly placeholder="No.CRCL">
			</div>
			<div class="col-md-8">
			<input type="text" class="form-control" id="no_surat" value="<?= $head->no_surat ?>"  required name="no_surat" readonly placeholder="No.CRCL">
			</div>
		</div>
		</div>
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">Tanggal</label>
			</div>
			<div class="col-md-8">
				<input type="date" class="form-control" id="tanggal" value="<?= $head->tgl_penawaran ?>" onkeyup required name="tanggal" readonly >
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
					<select id="id_customer" name="id_customer" readonly class="form-control select" onchange="get_customer()" required>
						<option value="">--Pilih--</option>
							<?php foreach ($results['customers'] as $customers){
								$select = $head->id_customer == $customers->id_customer ? 'selected' : '';?>
						<option value="<?= $customers->id_customer?>" <?= $select ?>><?= ucfirst(strtolower($customers->name_customer))?></option>
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
					<input type='text' class='form-control' id='nama_sales' value="<?= $head->nama_sales?>"   required name='nama_sales' readonly placeholder='Sales Marketing'>
				</div>
				<div class='col-md-8'>
					<input type='text' class='form-control' id='id_sales' value="<?= $head->id_sales?>"   required name='id_sales' readonly placeholder='Sales Marketing'>
				</div>
				</div>
			</div>
		</div>
		
		</div>
		</br>
		<div class="col-sm-12">
		<div class="form-group row" >
		<div class="col-md-12">
		<div class='col-sm-6'>
		<div class='form-group row'>
			<div class='col-md-4'>
				<label for='email_customer'>EMAIL</label>
			</div>
			<div class='col-md-8' id="email_slot">
				<input type='email' class='form-control' id='email_customer' readonly value="<?= $head->email_customer?>"  required name='email_customer' >
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
						<?php $kategory3 = $this->db->query("SELECT * FROM child_customer_pic WHERE id_customer = '$head->id_customer' ")->result();
						foreach ($kategory3 as $pic){
							$select = $head->pic_customer == $pic->name_pic ? 'selected' : '';?>
						<option value="<?= $pic->name_pic?>"  <?= $select ?>><?= ucfirst(strtolower($pic->name_pic))?></option>
							<?php } ?>
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
			<div class='col-md-8' id="email_slot">
				<select id="mata_uang" name="mata_uang" class="form-control select" required>
						<?php
						if($head->mata_uang == 'IDR'){
							echo"
						<option value=''>--Pilih--</option>
						<option value='IDR' selected>IDR(Rupiah)</option>
						<option value='USD'>USD(Dolar)</option>";
						}if($head->mata_uang == 'USD'){
							echo"
						<option value=''>--Pilih--</option>
						<option value='IDR' >IDR(Rupiah)</option>
						<option value='USD' selected>USD(Dolar)</option>";
						}else{
							echo"
						<option value=''>--Pilih--</option>
						<option value='IDR'>IDR(Rupiah)</option>
						<option value='USD'>USD(Dolar)</option>";
						}
						?>
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
				<input type='date' class='form-control' id='valid_until' value="<?= $head->valid_until ?>" required name='valid_until' >
			</div>
		</div>
		</div>
		<div class='col-sm-4'>
			<div class='form-group row'>
				<div class='col-md-4'>
					<label for='id_category_supplier'>Term Of Payment (Days)</label>
				</div>
				<div class='col-md-8' id="pic_slot" >
					<input type='number' class='form-control' id='terms_payment' value="<?= $head->terms_payment ?>"  required name='terms_payment' >
				</div>
			</div>
		</div>
		<div class='col-sm-4'>
			<div class='form-group row'>
				<div class='col-md-4'>
					<label for='id_category_supplier'>Exclude vat (%)</label>
				</div>
				<div class='col-md-8' >
					<input type='number' class='form-control' id='exclude_vat' value="<?= $head->exclude_vat ?>"  required name='exclude_vat' >
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
				<textarea id="note" name="note" class='form-control col-md-12' rows="4" cols="2000"><?= $head->note ?></textarea>
			</div>
		</div>
		</div>
		</div>
		</div>
			</div>
			  <center>
                    <a class="btn btn-primary btn-sm ajukan" id='ajukan' title="Ajukan">Request Approval</a>
                    </center>
				 </div>
			</div>
		</form>		  
	</div>
</div>	
	
				  
				  
<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Penawaran</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Close</button>
        </div>
    </div>
  </div>
</div>
	
				  
				  
				  
<script type="text/javascript">

$(document).on('click', '.ajukan', function(){
		var id = $('#no_penawaran').val();
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Data Penawaran</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'penawaran/FormApproval/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});

</script>
	
	
</script>