	<form id="data-form" method="post">
	
	<div class="box-body">
	
	<input type='hidden' class='form-control' name='sisaweight' id='sisaweight'	value='0' readonly>
	
	
</div>

<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>No LOT</th>
			<th>Length <br>Mother Coil</th>
			<th>Width <br>Mother Coil</th>
			<th>Weight <br>Packing List</th>
			
		
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results['header'])){
		}else{
			
			$numb=0; foreach($results['header'] AS $record){ $numb++; 
			$id_spkproduksi  = $record->id_spkproduksi;
						
		?>
		<tr>
		    <td><?= $numb; ?></td>
			<td>
			<input type="hidden" class="form-control"	value="<?= $record->id_material?>"readonly id="id_material<?=$numb?>" required name='id_material'>
			<input type="hidden" class="form-control"	value="<?= $record->thickness?>"readonly id="thickness" required name='thickness'>
			<input type="hidden" class="form-control"	value="<?= $record->density?>"readonly id="density" required name='density'>
			<input type="hidden" class="form-control"	value="<?= $record->id_stock?>"readonly id="stock" required name='stock'>
			
			<input type="text" class="form-control"	value="<?= $record->lotno?>"readonly id="lotno" required name='lotno'>
			</td>
			<td><input type="text" class="form-control"	value="<?= $record->length?>"readonly id="length" required name='length'></td>
			<td><input type="text" class="form-control"	value="<?= $record->width?>"readonly id="width" required name='width'></td>
			<td><input type="text" class="form-control"	value="<?= $record->weight?>"readonly id="weight" required name='weight'></td>
		

		</tr>
		<?php } }  ?>
		</tbody>
		</table>
		
					
	</div>
	<!-- /.box-body -->		

					<div class="col-sm-12">
						<div class="col-sm-12">
					
							<div class="form-group row" >
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<th width='3%'>No</th>
											<th width='20%'>SPK Marketing</th>
											<th width='15%'>Customer</th>
											<th width='7%'>Weight SPK</th>
											<th width='7%'>Weight<br>Packing List</th>
											<th width='7%'>Weight Proses</th>
											<th width='7%'>Selisih</th>
											<th width='7%'>Action</th>
																					
										
										</tr>
									</thead>
									<tbody>
									
									<?php if(empty($results['detail'])){
									}else{
										
										$numb1=0; foreach($results['detail'] AS $record1){ $numb1++; ?>
									<tr>
										<td><?= $numb1; ?></td>
										<td>
										
										<input type="text" class="form-control"	value="<?= $record1->no_spkmarketing?>"readonly id="no_spk" required name='no_spk'>
										</td>
										<td>
										<input type="text" class="form-control"	value="<?= $record1->name_customer?>"readonly id="customer" required name='customer'>
										</td>
										<td>
										<input type="text" class="form-control"	value="<?= $record1->weight_order?>"readonly id="weight_spk" required name='weight_spk'>
										</td>
										<td>
										<input type="text" class="form-control"	value="<?= $record1->weightmaterial?>"readonly id="weight_mat" required name='weight_mat'>
										</td>
										<td>
										<input type="text" class="form-control"	value="<?= $record1->weight_proses?>"readonly id="weight_proses" required name='weight_proses'>
										</td>
										<td>
										<input type="text" class="form-control"	value="<?= $record1->selisih_proses?>"readonly id="selisih_proses" required name='selisih_proses'>
										</td>
									

									</tr>
									<?php } }  ?>
																			
									</tbody>
									
								</table>
							</div>
						</div>
					</div>	
					
					<center>
					    <a class="btn btn-success btn-sm" href="<?= base_url('/spk_aktual_sheet/printSPKProduksi/'.$id_spkproduksi) ?>" target="_blank"  title="Print SPK Produksi"><i class="fa fa-print">&nbsp;</i>Print SPK Produksi</i></a>
						<a class="btn btn-success btn-sm" href="<?= base_url('/spk_aktual_sheet/PrintHeader2/'.$id_spkproduksi) ?>" target="_blank"  title="Cetak"><i class="fa fa-print">&nbsp;</i>Cetak Laporan Produksi</i></a>
						<a class="btn btn-danger btn-sm" href="<?= base_url('/spk_aktual_sheet/') ?>"  title="Edit">Kembali</a>
					</center>
				
		</form>		  				
					
<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Material</h4>
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
					

	
<!-- page script -->
<script type="text/javascript">


      $(document).ready(function(){	
		$('.autoNumeric').autoNumeric();
		$('.select').select2();

		

		$('#simpan-com').click(function(e){
			e.preventDefault();
			
			
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
						var baseurl=siteurl+'spk_produksi_sheet/SaveNewHeader';
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
    
					
		$(document).on('click', '.add', function(){
		var id = $(this).data('no_penawaran');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Data</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'spk_produksi_sheet/addMaterial/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();				
				$("#ModalView").html(data);
				
				$('.select').select2({
				dropdownParent: $('#ModalView'),
				width: '100%'
				});
				
				
				
			}
			})
		});
		
		
		// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('no_penawaran');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'spk_produksi_sheet/deleteMaterial/'+id, 
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data berhasil dihapus.",
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
		
	});
		
		function GetSpk(id){ 
		var jumlah	=$('#list_spk').find('tr').length;
		var id_stock=$("#id_stock").val();
		var thickness=$("#thickness").val();
		var nama_material=$("#nama_mat").val();
		var id_material=$('#id_material'+id).val();
		
		console.log(id);
		console.log(id_material);
	
		
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi_sheet/GetSpk',
            data:"jumlah="+jumlah+"&id_stock="+id_stock+"&id_material="+id_material+"&thickness="+thickness+"&nama_material="+nama_material,
            success:function(html){
               $("#list_spk").append(html);
			   $('.select').select2({
				   width:'100%'
			   });
            }
        });
        }
		
		function HapusItem(id){
		$('#list_spk #tr_'+id).remove();
		changeChecked();
	}
	
	function CariDetail(id){
		var width = getNum($('#width').val().split(",").join(""));
		var id_marketing=$('#used_no_surat_'+id).val();
		
		var id_stock=$('#stock').val();
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
            url:siteurl+'spk_produksi_sheet/CariNamaCustomer',
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
            url:siteurl+'spk_produksi_sheet/CariWeightSPK',
            data:"id_marketing="+id_marketing+"&id="+id,
            success:function(html){
               $('#qtyproduk_'+id).html(html);
			   $('.autoNumeric').autoNumeric();
            }
        });
		
			$.ajax({
            type:"GET", 
            url:siteurl+'spk_produksi_sheet/CariWeightMaterial',
            data:"id_stock="+id_stock+"&id="+id,
            success:function(html){
               $('#weightmat_'+id).html(html);
				
            }
        });
		
		$.ajax({
            type:"GET", 
            url:siteurl+'spk_produksi_sheet/CariIdMaterial',
            data:"id_stock="+id_stock+"&id="+id,
            success:function(html){
               $('#idmat_'+id).html(html);
				
            }
        });
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
		
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariW2material',
            data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
            success:function(html){
               $('#width2_'+id).html(html);
				
            }
        });
	
		
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi/CariTotalorder',
            data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
            success:function(html){
               $('#order_'+id).html(html);
				
            }
        });
		$.ajax({
            type:"GET", 
            url:siteurl+'spk_produksi/CariTotalproduksi',
            data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
            success:function(html){
               $('#produksi_'+id).html(html);
				
            }
        });
		
		$.ajax({
            type:"GET", 
            url:siteurl+'spk_produksi/CariStokfg',
            data:"id_marketing="+id_marketing+"&id="+id+"&width="+width+"&kg_process="+kg_process,
            success:function(html){
               $('#stok_fg_'+id).html(html);
				
            }
        });
		
		

    }
	
	function HitungTotalCoil(id){
	    var beratMat=$('#weightmaterial_'+id).val();
		var beratSpk=$('#weight_proses_'+id).val();
		var idbefore = parseInt(id)-1;
		var selisih  = $('#selisih_proses_'+idbefore).val();
		var selisihEnd  = $('#selisih_proses_'+id).val();
		$.ajax({
            type:"GET",
            url:siteurl+'spk_produksi_sheet/HitungTotalselisih',
            data:"beratMat="+beratMat+"&beratSpk="+beratSpk+"&id="+id+"&selisih="+selisih,
            success:function(html){
               $('#tselisih_'+id).html(html);
			    sisaweight(id);
            }
        });
	}
	
	function sisaweight(id){
	var selisihEnd  = $('#selisih_proses_'+id).val();
	//console.log(selisihEnd);
	$("#sisaweight").val(number_format(selisihEnd,2));	
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
	
