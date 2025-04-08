<?php
    $ENABLE_ADD     = has_permission('Spk_produksi.Add');
    $ENABLE_MANAGE  = has_permission('Spk_produksi.Manage');
    $ENABLE_VIEW    = has_permission('Spk_produksi.View');
    $ENABLE_DELETE  = has_permission('Spk_produksi.Delete');
	
	$reg 	= ($selTab == 'reg')?'active':'';
	$reg2 	= ($selTab == '')?'active':'';
	$book 	= ($selTab == 'book')?'active':'';

	// print_r($_SESSION['JSON_Filter']);
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-body">
		<div>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="<?=$reg;?> <?=$reg2;?>"><a href="#mat1" onclick="changeTab1()" class='mat1' aria-controls="mat1" role="tab" data-toggle="tab">Reguler</a></li>
				<li role="presentation" class="<?=$book;?>"><a href="#mat2" onclick="changeTab2()" class='mat2' aria-controls="mat2" role="tab" data-toggle="tab">Booking</a></li>
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane <?=$reg;?> <?=$reg2;?>" id="mat1">
					<div class="box-header">
						<?php if($ENABLE_VIEW) : ?>
						<a class="btn btn-success btn-sm" href="<?= base_url('/spk_produksi/addHeader/') ?>"  title="Tambah"><i class="fa fa-plus">&nbsp;</i>Add</i></a>
						<?php endif; ?>
						<span class="pull-right"></span>
					</div>
					<div class="box-body">
						<table id="example2" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th width='4%'>#</th>
									<th width='12%'>No SPK Produksi</th>
									<th width='24%'>Customer</th>
									<th width='14%'>Nama Material</th>
									<th width='12%'>Action</th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>
					<!-- End Reguler -->
				</div>
				<!-- Booking -->
				<div role="tabpanel" class="tab-pane <?=$book;?>" id="mat2">
					<div class="box-header">
						<?php if($ENABLE_VIEW) : ?>
						<a class="btn btn-success btn-sm" href="<?= base_url('/spk_produksi/addHeaderBooking/') ?>"  title="Tambah"><i class="fa fa-plus">&nbsp;</i>Add</i></a>
						<?php endif; ?>
						<span class="pull-right"></span>
					</div>
					<div class="box-body">
						<table id="example3" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th width='4%'>#</th>
									<th width='12%'>No SPK Produksi</th>
									<th width='24%'>Customer</th>
									<th width='14%'>Nama Material</th>
									<th width='9%'>Status</th>
									<th>Alasan</th>
									<th width='12%'>Action</th>
								</tr>
							</thead>
							<tbody>
								
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
      </div>
      <div class="modal-body" id="MyModalBody">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Close</button>
        </div>
    </div>
  </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;SPK Produksi</h4>
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

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.edit', function(e){
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'spk_marketing/EditHeader/'+id,
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});
	
	$(document).on('click', '.cetak', function(e){
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'xtes/cetak'+id,
			success:function(data){
				
				
			}
		})
	});

	// $(document).on('click', '#bookid', function(){
	// 	changeTab1()
	// });
	
	// $(document).on('click', '#regid', function(){
	// 	changeTab2()
	// });
	
	const changeTab1 = () => {
		$.ajax({
			url			: siteurl+'spk_produksi/channgeSes',
			type		: "POST",
			data: {
				"tab" 	: 'reg',
			}
		})

	}

	const changeTab2 = () => {
		$.ajax({
			url			: siteurl+'spk_produksi/channgeSes',
			type		: "POST",
			data: {
				"tab" 	: 'book',
			}
		})
	}
	
	// $(document).on('click', '.view', function(){
	// 	var id = $(this).data('id_spkproduksi');
	// 	// alert(id);
	// 	$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
	// 	$.ajax({
	// 		type:'POST',
	// 		url:siteurl+'spk_produksi/ViewHeader/'+id,
	// 		data:{'id':id},
	// 		success:function(data){
	// 			$("#dialog-popup").modal();
	// 			$("#ModalView").html(data);
				
	// 		}
	// 	})
	// });

	
	
	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id_spkproduksi');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data Inventory akan di Approve.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Approve!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'spk_produksi/Approve',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data Inventory berhasil diApprove.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : "Data error. Gagal hapus data",
					  type  : "error"
					})
					
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			  }
		  })
		});
		
	})

	function Datatables() {
		
	}

  	$(function() {
	    var datatables = $('#example2').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			language: {
				loadingRecords: 'Loading - Please wait ...'
			},
			ajax: {
				url: siteurl + active_controller + 'get_data_spk_produksi_reguler',
				type: 'POST',
				dataType: 'json'
			},
			columns: [
				{
					data: 'no'
				},
				{
					data: 'no_spk_produksi'
				},
				{
					data: 'customer'
				},
				{
					data: 'nama_material'
				},
				{
					data: 'action'
				}
			]
		});

		var datatables = $('#example3').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			language: {
				loadingRecords: 'Loading - Please wait ...'
			},
			ajax: {
				url: siteurl + active_controller + 'get_data_spk_produksi_booking',
				type: 'POST',
				dataType: 'json'
			},
			columns: [
				{
					data: 'no'
				},
				{
					data: 'no_spk_produksi'
				},
				{
					data: 'customer'
				},
				{
					data: 'nama_material'
				},
				{
					data: 'status'
				},
				{
					data: 'alasan'
				},
				{
					data: 'action'
				}
			]
		});
    	// $("#form-area").hide();
  	});

	
	
	
	//Delete

	function PreviewPdf(id)
	{
		param=id;
		tujuan = 'customer/print_request/'+param;

	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap()
	{
		tujuan = 'customer/rekap_pdf';
	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>
