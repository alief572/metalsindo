<html>
<head>
  <title>Cetak PDF</title>
<style>
    #tables td, th {
		border: 1px solid #000000;
        padding: 2 px;
		font-size: 11px;
		border-collapse: collapse;
    }
	.clearth{
		border: 0px;
		border-collapse: collapse;
	}
</style>
</head>
<body>
<?php
	foreach($header as $header){
	}
?>
<div id='wrapper'>
<table border="0" width='100%' align="center">
<tr>
	<td width="700" align="center">
		<h5 style="text-align: left;">PT METALSINDO PASIFIC</h5>
	</td>
</tr>
</table>
<table id="tables" border="0" width='100%' align="center">
<tr>
	<td width="700" align="left">
		<h3 style="text-align: center;">PURCHASE ORDER</h3>
	</td>
</tr>
</table>
<table border="0" width='100%' align="center">
<tr>
	<td width="350" align="left">
	<table>
	<tr><td align='left'>Address</td></tr>
	<tr><td align='left'>Jl. Jababeka XIV, Blok J no. 10 H</td></tr>
	<tr><td align='left'>Cikarang Industrial Estate, Bekasi 17530</td></tr>
	<tr><td align='left'>PHONE:(62-21)89831726734,FAX(62-21)89831866</td></tr>
	</table>
	</td>
	<td width="350" align="right">
	<table>
	<tr><td align='left'>Quote no</td><td align='left'>:</td><td align='left' border="0" cellspacing="0"><?= $header->no_surat ?></td></tr>
	</table>
	</td>
</tr>
</table>
<table border="1px" cellspacing="0" width='100%' align="center">
<tr>
	<td width="380" align="center">
	<table width='380' align="center">
	<tr><td width='70' align="left">Supplier</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->name_suplier ?></td></tr>
	<tr><td width='70' align="left">Address</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->address_office ?></td></tr>
	<tr><td width='70' align="left">Phone</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->telephone ?></td></tr>
	<tr><td width='70' align="left">FAX</td><td width='10' align="left">:</td><td width='300' align="left"><?if(empty($header->fax)){echo"-";}else{echo"$header->fax";}  ?></td></tr>
	</table>
	</td>
</tr>
</table>
<br>
		<table id="tables" border="0" width='100%' align="center" cellpadding="2" cellspacing="0">
			<tr>
			<td align="center" width="70">Material</td>
			<td align="center" width="100">Weight</td>
			<td align="center" width="100">Qty (Unit)</td>
			<td align="center" width="80">Total Weight</td>
			<td align="center" width="80">Unit Price</td>
			<td align="center" width="80">Amount</td>
			<td align="center" width="80">Remarks</td>
			</tr>
			<?php	foreach($detail as $detail){
			echo"	
			<tr>
			<td align='center'>".$detail->namamaterial."</td>
			<td align='center'>".$detail->width."</td>
			<td align='center'>".$detail->qty."</td>
			<td align='center'>".$detail->totalwidth."</td>
			<td align='center'>".$detail->hargasatuan."</td>
			<td align='center'>".$detail->jumlahharga."</td>
			<td align='center'>".$detail->description."</td>
			</tr>";
			 } ?>
			<tr>
			<td align="center">Total</td>
			<td align="center"><?= $detailsum->width ?></td>
			<td align="center"><?= $detailsum->totalwidth ?></td>
			<td align="center"><?= $detailsum->totalwidth ?></td>
			<td align="center"><?= $detailsum->hargasatuan ?></td>
			<td align="center"><?= $detailsum->jumlahharga ?></td>
			<td align="center"><?= $detailsum->note ?></td>
			</tr>

	</table>
</div>