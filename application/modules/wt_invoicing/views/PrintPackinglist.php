<html>

<head>
	<title>Cetak PDF</title>
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
</head>

<body>
	<?php
	foreach ($header as $header) {
	}
	// $jumlahdetail = $this->db->query("SELECT COUNT(no_penawaran) as no_penawaran FROM child_penawaran WHERE no_penawaran = '".$header->no_penawaran."' ")->result();
	// $jumlahdata = $jumlahdetail[0]->no_penawaran;
	// $tinggi = 300/$jumlahdata ;
	?>

	<table border="0" width='100%'>
		<tr>
			<td align="left">
				<img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
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
				<img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/img/logo_iso.jpg' alt="" height='30' width='60'>
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
					<h5 style="text-align: right;">PACKING LIST</h5>
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
									<td width='100' align="left">Packing List No</td>
									<td width='10' align="left">:</td>
									<td width='150' align="left"><?= $header->no_surat ?></td>
								</tr>
								<tr>
									<td width='100' align="left">Invoice Date</td>
									<td width='10' align="left">:</td>
									<td width='150' align="left"><?= $header->tgl_invoice ?></td>
								</tr>
								<tr>
									<td width='100' align="left">Our Delivery No</td>
									<td width='10' align="left">:</td>
									<td width='150' align="left"><?= $header->no_do ?></td>
								</tr>
								<tr>
									<td width='100' align="left">Delivery Date</td>
									<td width='10' align="left">:</td>
									<td width='150' align="left"><?= $header->tgl_do ?></td>
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
				<th align="center" width="300">Description Of Goods</th>
				<th align="center" width="60">Width</th>
				<th align="center" width="60">Quantity</th>
				<th align="center" width="60">Nett Weight (Kgs)</th>
				<th align="center" width="60">Gross Weight (Kgs) </th>
				<th align="center" width="110">Remarks</th>
			</tr>
			<tr></tr>

		</thead>
		<tbody>
			<?php

			$no = 0;
			foreach ($detail as $detail) {
				$no++;
				$material  = $detail->id_material;
				$width     = $detail->width;
				$length    = $detail->length;
				$gabung     = $detail->kode_gabung;
				$nama 		= $this->db->get_where('ms_inventory_category3', array('id_category3' => $detail->id_material))->row();
				$qty		= $this->db->query("SELECT SUM(weight_mat) AS totweightmat, SUM(qty_mat) AS totqty_mat FROM dt_delivery_order_child WHERE id_delivery_order ='$id' AND id_material ='$material' AND width='$width' AND length = '$lenght' AND kode_gabung='$gabung'")->row();



				$totqty1 = $qty->totweightmat + 5;
				$totqtygross += $totqty1;
				$totqty += $qty->totweightmat;
				$totcoil += $qty->totqty_mat;




				if ($detail->length == 0) {

					$bentuk = 'COIL';
					$coil   = 'C';
				} else {

					$bentuk = 'SHEET';
					$coil   = 'S';
				}


				$spek     = $nama->thickness . ' X ' . $detail->width . ' X ' . $coil;


			?>
				<tr>
					<td align="left">&nbsp;<?= $no ?></td>
					<td align="left" width="350">&nbsp;<?= $nama->nama . ',' . $spek ?></td>
					<td align="center" width="60">&nbsp;<?= $detail->width ?></td>
					<td align="right"><?= number_format($qty->totqty_mat, 2) ?></td>
					<td align="right"><?= number_format($qty->totweightmat, 2) ?></td>
					<td align="right"><?= number_format($qty->totweightmat + 5, 2) ?></td>
					<td align="center"><?= $bentuk ?></td>

				</tr>
			<? } ?>
		</tbody>
		<tfoot>


			<tr>
				<th align="center"></th>
				<th align="center">Total</th>
				<th align="center"></th>
				<th align="right"><?= number_format($totcoil, 2) ?></th>
				<th align="right"><?= number_format($totqty, 2) ?></th>
				<th align="right"><?= number_format($totqtygross, 2) ?></th>
				<th align="right"></th>
			</tr>


		</tfoot>

	</table>

	<br><br><br><br><br>
	<table border="0" width='100%' align="left">

		<tr>
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
</body>

</html>