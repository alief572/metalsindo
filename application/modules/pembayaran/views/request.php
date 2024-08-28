 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
		<div class="form-group row" >
			 <table class="table table-bordered" width="100%" id="list_item_stok">
              <thead>
                  <tr>
				      <th width="30%">Code</th>
                      <th width="30%">No Request</th>
                      <th width="30%">Nama Supplier</th>
                      <th width="30%">Request</th>
					   <th width="30%">PPN</th>
					   <th width="30%">Sisa Request</th>
                      <th width="2%" class="text-center">Aksi</th>  
                  </tr>
              </thead>
              <tbody>
                  <?php	
				 
				  $cust = $results['detail'];
				  
                  $invoice = $this->db->query("SELECT a.*, b.name_suplier as nm_suplier FROM tr_request a
				                      INNER JOIN master_supplier b ON a.id_suplier=b.id_suplier WHERE a.id_suplier ='$cust' AND (a.sisa_invoice_idr >'0')")->result();
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->id ?></td>
							  <td><?php echo $vs->no_request ?></td>
							  <td><center><?php echo $vs->nm_suplier ?></center></td>
							  <td><center><?php echo number_format($vs->nilai_invoice) ?></center></td>
							  <td><center><?php echo number_format($vs->nilai_ppn) ?></center></td>
							  <td><center><?php echo number_format($vs->sisa_invoice_idr) ?></center></td>
							  <td>
							 
								<center>
									<button id="btn-<?php echo $vs->id?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->id?>', '<?php echo $vs->no_request?>','<?php echo addslashes($vs->nm_suplier) ?>','<?php echo $vs->nilai_invoice?>','<?php echo $vs->nilai_ppn?>','<?php echo $vs->sisa_invoice_idr?>','<?php echo $vs->sisa_invoice_kurs?>')">
										Pilih
									</button>
								</center>
							 
							  </td>
						  </tr>
                  <?php 
						}
					  }				  
				  ?>
              </tbody>
          </table>
		</div>
			</div>
				 </div>
			</div>
		</form>		  
	</div>
</div>	
	
				  
				  

	
	
	
</script>