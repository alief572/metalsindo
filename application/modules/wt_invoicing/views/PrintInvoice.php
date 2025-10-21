<style>
	.header_style_company {
		padding: 15px;
		color: black;
		font-size: 20px;
		vertical-align: bottom;
	}

	.header_style_company2 {
		padding: 15px;
		color: black;
		font-size: 15px;
		vertical-align: top;
	}

	.header_style_alamat {
		padding: 10px;
		color: black;
		font-size: 10px;
	}

	table.default {
		font-family: arial, sans-serif;
		font-size: 9px;
		padding: 0px;
	}

	p {
		font-family: arial, sans-serif;
		font-size: 14px;
	}

	.font {
		font-family: arial, sans-serif;
		font-size: 14px;
	}

	table.gridtable {
		font-family: arial, sans-serif;
		font-size: 10px;
		color: #333333;
		border: 1px solid #808080;
		border-collapse: collapse;
	}

	table.gridtable th {
		padding: 6px;
		background-color: #f7f7f7;
		color: black;
		border-color: #808080;
		border-style: solid;
		border-width: 1px;
	}

	table.gridtable th.head {
		padding: 6px;
		background-color: #f7f7f7;
		color: black;
		border-color: #808080;
		border-style: solid;
		border-width: 1px;
	}

	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #808080;
	}

	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #808080;
	}


	table.gridtable2 {
		font-family: arial, sans-serif;
		font-size: 13px;
		color: #333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}

	table.gridtable2 td {
		border-width: 1px;
		padding: 1px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}

	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 1px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}

	table.gridtableX {
		font-family: arial, sans-serif;
		font-size: 12px;
		color: #333333;
		border: none;
		border-collapse: collapse;
		margin-top: 10px;
		vertical-align: top;
	}

	table.gridtableX td {
		border-width: 1px;
		padding: 2px;
	}

	table.gridtableX td.cols {
		border-width: 1px;
		padding: 2px;
	}

	table.gridtableX2 {
		font-family: arial, sans-serif;
		font-size: 12px;
		color: #333333;
		border: none;
		border-collapse: collapse;
	}

	table.gridtableX2 td {
		border-width: 1px;
		padding: 2px;
	}

	table.gridtableX2 td.cols {
		border-width: 1px;
		padding: 2px;
	}

	#testtable {
		width: 100%;
	}

	.noneborder {
		border: none;
	}

	.nonebordercst {
		border-top: none;
		border-bottom: none;
	}
</style>


<?php
foreach ($header as $header) {
}
// $jumlahdetail = $this->db->query("SELECT COUNT(no_penawaran) as no_penawaran FROM child_penawaran WHERE no_penawaran = '".$header->no_penawaran."' ")->result();
// $jumlahdata = $jumlahdetail[0]->no_penawaran;
// $tinggi = 300/$jumlahdata ;

if ($header->type == 'slitting') {
	$tipe = 'Jasa Slitting';
	$headline = 'Slitting Service';
} else {
	$tipe = '';
	$headline = 'Sales';
}
?>

<table border="0" width='100%'>
	<tr>
		<td align="left">
			<?php
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/metalsindo/assets/images/logo_metalsindo.jpeg')) {
			?>
				<img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
			<?php
			} else {
			?>
				<img src='./assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
			<?php
			}
			?>
		</td>
		<td align="left">
			<h5 style="text-align: left;">PT METALSINDO PACIFIC</h5>
		</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right">
			<?php
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/metalsindo/assets/img/logo_iso.jpg')) {
			?>
				<img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/img/logo_iso.jpg' alt="" height='30' width='60'>
			<?php
			} else {
			?>
				<img src='./assets/img/logo_iso.jpg' alt="" height='30' width='60'>
			<?php
			}
			?>
		</td>


	</tr>
</table>
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
	<tbody>
		<tr>
			<td>Jl. Jababeka XIV, Blok J no. 10 H <br>
				Cikarang Industrial Estate, Bekasi 17530 <br>
				Phone : (62-21) 89831726734, Fax : (62-21) 89831866<br>
				NPWP : 21.098.204.7-414.000
			</td>
			<td align="left">
			</td>
			<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td align="right">
			</td>
			<td align="right" width="100">
				<h3 style="text-align: right;">INVOICE</h3>(<?= $headline ?>)
			</td>
		</tr>

	</tbody>
</table>
<hr>

<?php
$customer = $this->db->query("SELECT * FROM master_customers WHERE id_customer='$header->id_customer'")->row();
$pic = $this->db->query("SELECT * FROM child_customer_pic WHERE id_customer='$header->id_customer'")->row();
$top = $this->db->query("SELECT * FROM ms_top WHERE id_top='$header->top'")->row();

$dp = $this->db->query("SELECT * FROM wt_plan_tagih WHERE no_so='$header->no_so' AND persentase='30' AND keterangan='dp' AND status_invoice='1'")->row();

$dp2 = $this->db->query("SELECT * FROM wt_plan_tagih WHERE no_so='$header->no_so' AND persentase='40' AND status_invoice='1'")->row();

?>

<table border="0" width='100%' align="left">
	<tr>

		<td width="100" align="left" border='0.5'>
			<table>
				<tr>
					<td width="380" align="left">
						<table width='380' align="center">
							<tr>
								<td width='70' align="left">Sold To</td>
								<td width='10' align="left">:</td>
								<td width='300' align="left"><?= $customer->name_customer ?></td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='310' align="left"><?= $customer->address_office ?></td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='300' align="left">Telp. <?= $customer->telephone ?> Fax.<? if (empty($header->fax)) {
																										echo "-";
																									} else {
																										echo "$header->fax";
																									}  ?></td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='300' align="left">Att.&nbsp; Keuangan</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td width="100" align="left" border='0'>
			<table>
				<tr>
					<td width="150" align="left">
						<table width='150' align="center">
							<tr>
								<td width='100' align="left">Invoice No</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= $header->no_surat ?></td>
							</tr>
							<tr>
								<td width='100' align="left">Invoice Date</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= date('d-F-Y', strtotime($header->tgl_invoice)) ?></td>
							</tr>
							<tr>
								<td width='100' align="left">Our Delivery No</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= $header->no_do ?></td>
							</tr>
							<tr>
								<td width='100' align="left">Delivery Date</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= date('d-F-Y', strtotime($header->tgl_do)) ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>

	</tr>
</table>

<table border="0" width='100%' align="left">
	<tr>
		<td width="100" align="left" border='0.5'>
			<table>
				<tr>
					<td width="380" align="left">
						<table width='380' align="center">
							<tr>
								<td width='70' align="left">Delivered To</td>
								<td width='10' align="left">:</td>
								<td width='300' align="left">As An Order</td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='310' align="left"></td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='300' align="left"></td>
							</tr>
							<tr>
								<td width='70' align="left"></td>
								<td width='10' align="left"></td>
								<td width='300' align="left"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td width="100" align="left" border='0'>
			<table>
				<tr>
					<td width="150" align="left">
						<table width='150' align="center">
							<tr>
								<td width='100' align="left">Your Order No</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= $header->no_po ?></td>
							</tr>
							<tr>
								<td width='100' align="left">Your Order Date</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= $header->tgl_po ?></td>
							</tr>
							<tr>
								<td width='100' align="left">Terms</td>
								<td width='10' align="left">:</td>
								<td width='150' align="left"><?= $header->note ?></td>
							</tr>
							<tr>
								<td width='100' align="left"></td>
								<td width='10' align="left"></td>
								<td width='150' align="left"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>

	</tr>
</table>

<br>
<table id="tables" class='gridtableX' border="1px">
	<thead>
		<tr height='60'>
			<th align="center" width="20">No</th>
			<th align="center" width="60">Quantity</th>
			<th align="center" width="60">Units</th>
			<th align="center" width="300">Description</th>
			<th align="center" width="60">Per Unit</th>
			<th align="center" width="110">Amount (IDR)</th>
		</tr>
		<tr></tr>

	</thead>
	<tbody>
		<?php

		$no = 0;
		foreach ($detail as $detail) {
			$no++;



			$get_inventory = $this->db->get_where('ms_inventory_category3', array('id_category3' => $detail->id_category3))->row_array();
			if ($get_inventory['id_bentuk'] == 'B2000002') :
				$this->db->select('a.price_sheet, a.qty_sheet');
				$this->db->from('child_penawaran a');
				$this->db->join('tr_spk_marketing b', 'b.no_penawaran = a.no_penawaran');
				$this->db->join('tr_invoice c', 'c.no_so = b.id_spkmarketing');
				$this->db->where('c.no_so', $detail->no_so);
				$this->db->where('a.id_category3', $detail->id_category3);
				$get_sheets_detail = $this->db->get()->row_array();

				if ($get_sheets_detail['price_sheet'] > 0) :
					$satuan = 'Sheets';
				else :
					$satuan = 'Kgs';
				endif;
			else :
				$satuan = 'Kgs';
			endif;

			$harga_satuan = $detail->harga_satuan;
			$qty_invoice = $detail->qty_invoice;
			if ($get_inventory['id_bentuk'] == 'B2000002') {

				$get_inventory = $this->db->get_where('ms_inventory_category3', array('id_category3' => $detail->id_category3))->row();

				$density = $get_inventory->density;
				$thickness = $get_inventory->thickness;
				$width = 0;
				$length = 0;

				$get_nilai_other = $this->db->get_where('child_inven_dimensi', array('id_category3' => $detail->id_category3))->result();
				foreach ($get_nilai_other as $item_nilai_other) {
					if ($item_nilai_other->id_dimensi == '32') {
						$width = $item_nilai_other->nilai_dimensi;
					}
					if ($item_nilai_other->id_dimensi == '33') {
						$length = $item_nilai_other->nilai_dimensi;
					}
				}

				$this->db->select('a.qty_produk');
				$this->db->from('dt_spkmarketing a');
				$this->db->where('a.id_spkmarketing', $detail->no_so);
				$this->db->where('a.id_material', $detail->id_category3);
				$get_detail_spkmkt = $this->db->get()->row();

				// $qty_invoice = round(($detail->qty_invoice / round($get_inventory->total_weight)));
				$qty_invoice = round(($get_detail_spkmkt->qty_produk));

				$totqty += $qty_invoice;
				$totharga += ($detail->harga_satuan * $qty_invoice);

				$harga_satuan = $detail->harga_satuan;
				// $qty_invoice = $get_sheets_detail['qty_sheet'];
			} else {
				$totqty += $detail->qty_invoice;
				$totharga += $detail->total_harga;
			}

		?>
			<tr>
				<td align="left">&nbsp;<?= $no ?></td>
				<td align="center"><?= number_format($qty_invoice, 2) ?></td>
				<td align="center"><?= $satuan ?></td>
				<td align="left" width="350">&nbsp;<?= $tipe . ' ' . $detail->nama_produk . ', ' . $detail->tobe_size . ', ' . $detail->part_number ?></td>
				<td align="right"><?= number_format($harga_satuan, 2) ?></td>
				<td align="right"><?= number_format(($harga_satuan * $qty_invoice), 2) ?></td>
			</tr>
		<? } ?>
	</tbody>
	<tfoot>

		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th align="right" width="110">&nbsp;</th>
		</tr>

		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th align="right" width="110">&nbsp;</th>
		</tr>
		<?php if ($header->nilai_ppn > 0) { ?>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th align="center">Total</th>
				<th align="right" width="110"><?= number_format($totharga, 2) ?></th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th align="center">DPP Nilai Lain</th>
				<th align="right" width="110"><?= number_format(ceil((11 / 12 * $totharga))) ?></th>
			</tr>

			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th align="center">PPN</th>
				<th align="right" width="110"><?= number_format(((11 / 12 * $totharga) * 12 / 100)) ?></th>
			</tr>

			<tr>
				<th></th>
				<th align="center"><?= number_format($totqty, 2) ?></th>
				<th align="center"><?= $satuan ?></th>
				<th></th>
				<th align="center">Grand Total</th>
				<th align="right" width="110"><?= number_format(($totharga + ((11 / 12 * $totharga) * 12 / 100))) ?></th>
			</tr>

		<?php } else {
		?>

			<tr>
				<th></th>
				<th align="center"><?= number_format($totqty, 2) ?></th>
				<th align="center"><?= $satuan ?></th>
				<th></th>
				<th align="center">Grand Total</th>
				<th align="right" width="110"><?= number_format($totharga) ?></th>
			</tr>

		<?php
		} ?>


	</tfoot>

</table>

<br><br>
<table border="0" width='100%' align="left">

	<tr>

		<td width="100" align="left" border='0'>
			<table>
				<tr>
					<td align='left'><b>TRANSFER TO OUR BANK ACCOUNT:</b></td>
				</tr>
				<tr>
					<td align='left'><b>PT. BANK OCBC NISP On Behalf of PT. METALSINDO PACIFIC</b></td>
				</tr>
				<tr>
					<td align='left'><b>Ruko Plaza Menteng B/1, Thamrin, Lippo Cikarang</b></td>
				</tr>
				<tr>
					<td align='left'><b>A/C: 103810048480 (Multi Currency)</b></td>
				</tr>
			</table>
		</td>

		<td width="100" align="left" border='0'>
			<table>
				<tr>
					<td align='left'><b></b></td>
				</tr>
				<tr>
					<td align='left'><b></b></td>
				</tr>
				<tr>
					<td align='left'><b></b></td>
				</tr>
				<tr>
					<td align='left'><b></b></td>
				</tr>
			</table>
		</td>

		<td width="100" align="left" border='0'>
			<table>
				<tr>
					<td align='center'><b>BEST REGARDS,</b></td>
				</tr>
				<tr>
					<td align='left'><b>&nbsp;</b></td>
				</tr>
				<tr>
					<td align='left'><b>&nbsp;</b></td>
				</tr>
				<tr>
					<td align='left'><b>&nbsp;</b></td>
				</tr>
				<tr>
					<td align='left'><b>&nbsp;</b></td>
				</tr>
				<tr>
					<td align='left'><b>&nbsp;</b></td>
				</tr>
				<tr>
					<td align='center'><b>FINANCE & ACCOUNTING DEPT</b></td>
				</tr>
			</table>
		</td>

	</tr>
</table>