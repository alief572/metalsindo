 <div class="box box-primary">
 	<div class="box-body">
 		<form id="data-form" method="post">
 			<div class="form-group row">
 				<table class="table table-bordered" width="100%" id="list_item_stok">
 					<thead>
 						<tr>
 							<th width="30%">Code</th>
 							<th width="30%">No Invoice</th>
 							<th width="30%">Nama Customer</th>
 							<th width="30%">Total Invoice</th>
 							<th width="30%">Sisa Invoice</th>
 							<th width="2%" class="text-center">Aksi</th>
 						</tr>
 					</thead>
 					<tbody>
 						<?php

							$cust = $results['detail'];

							$invoice = $this->db->query("SELECT a.*, b.name_customer as nm_customer FROM tr_invoice a
				                      INNER JOIN master_customers b ON a.id_customer=b.id_customer WHERE a.id_customer ='$cust' AND (a.sisa_invoice_idr >'0')")->result();
							if ($invoice) {
								foreach ($invoice as $ks => $vs) {

									$this->db->select('a.*');
									$this->db->from('tr_invoice_detail a');
									$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_category3');
									$this->db->where('a.no_invoice', $vs->no_invoice);
									$this->db->where('b.id_bentuk', 'B2000002');
									$get_detail_sheet = $this->db->get()->result();

									$tipe_sheet = (count($get_detail_sheet) > 0) ? '1' : '0';

									if ($tipe_sheet == '1') {
										$nilai_invoice = 0;

										foreach ($get_detail_sheet as $item_sheet) {
											$this->db->select('SUM(a.qty_sheet) as ttl_qty_sheet');
											$this->db->from('stock_material a');
											$this->db->join('dt_delivery_order_child b', 'b.lotno = a.lotno');
											$this->db->join('tr_delivery_order c', 'c.id_delivery_order = b.id_delivery_order');
											$this->db->where('c.no_surat', $vs->no_do);
											$this->db->where('b.id_material', $item_sheet->id_category3);
											$this->db->where('a.no_kirim', $vs->id_do);
											// $this->db->group_by('a.id_stock');
											$get_qty_sheet = $this->db->get()->row();

											$qty_sheet = (!empty($get_qty_sheet->ttl_qty_sheet)) ? $get_qty_sheet->ttl_qty_sheet : 0;
											// foreach ($get_qty_sheet as $item_qty_sheet) {
											// 	$qty_sheet += $item_qty_sheet->qty_sheet;
											// }

											$nilai_invoice += ($item_sheet->harga_satuan * $qty_sheet) + (($item_sheet->harga_satuan * $qty_sheet) * 11 / 100);
										}
										$sisa_invoice_idr = ($nilai_invoice - $vs->total_bayar);
									} else {
										$this->db->select('SUM(a.qty_invoice * a.harga_satuan) as ttl_invoice');
										$this->db->from('tr_invoice_detail a');
										$this->db->where('a.no_invoice', $vs->no_invoice);
										$get_ttl_invoice = $this->db->get()->row();

										$nilai_invoice = (!empty($get_ttl_invoice->ttl_invoice)) ? $get_ttl_invoice->ttl_invoice : 0;
										$sisa_invoice_idr = $vs->sisa_invoice_idr;
									}
									if ($sisa_invoice_idr > 0) {
							?>
 									<tr>
 										<td><?php echo $vs->no_invoice ?></td>
 										<td><?php echo $vs->no_surat ?></td>
 										<td>
 											<center><?php echo $vs->nm_customer ?></center>
 										</td>
 										<td>
 											<center><?php echo number_format($nilai_invoice) ?></center>
 										</td>
 										<td>
 											<center><?php echo number_format($sisa_invoice_idr) ?></center>
 										</td>
 										<td>
 											<?php
												if ($vs->printed_on == null) {
													echo 'Belum cetak invoice';
												} else {
												?>
 												<center>
 													<button id="btn-<?php echo $vs->no_invoice ?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->no_invoice ?>', '<?php echo $vs->no_surat ?>','<?php echo addslashes($vs->nm_customer) ?>','<?php echo $vs->nilai_invoice ?>','<?php echo $vs->sisa_invoice_idr ?>')">
 														Pilih
 													</button>
 												</center>
 											<?php
												}
												?>
 										</td>
 									</tr>
 						<?php
									}
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