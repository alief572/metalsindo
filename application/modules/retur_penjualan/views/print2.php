<html>
<head>
<style type="text/css">
        .header_style_company{
            padding: 15px;
            color: black;
            font-size: 20px;
            vertical-align:bottom;
        }
        .header_style_company2{
            padding: 15px;
            color: black;
            font-size: 15px;
            vertical-align:top;
        }

        .header_style_alamat{
            padding: 10px;
            color: black;
            font-size: 10px;
        }

        table.default {
            font-family: arial,sans-serif;
            font-size:9px;
            padding: 0px;
        }

        p{
            font-family: arial,sans-serif;
            font-size:14px;
        }
        
        .font{
            font-family: arial,sans-serif;
            font-size:14px;
        }

        table.gridtable {
            font-family: arial,sans-serif;
            font-size:10px;
            color:#333333;
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
            font-family: arial,sans-serif;
            font-size:13px;
            color:#333333;
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
            font-family: arial,sans-serif;
            font-size:10px;
            color:#333333;
            border: none;
            border-collapse: collapse;
            margin-top:10px;
        }
        table.gridtableX td {
            border-width: 1px;
            padding: 4px;
        }
        table.gridtableX td.cols {
            border-width: 1px;
            padding: 4px;
        }

        table.gridtableX2 {
            font-family: arial,sans-serif;
            font-size:12px;
            color:#333333;
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

        #testtable{
            width: 100%;
        }

        .noneborder{
            border:none;
        }
        .nonebordercst{
            border-top:none;
            border-bottom:none;
        }
    </style>
</head>
<body>

<table border="0" width='100%'>
    <tr>
        <td align="left">
            <img src='<?=$_SERVER['DOCUMENT_ROOT'];?>/metalsindo/assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
        </td>
        <td align="left">
            <h5 style="text-align: left;">PT METALSINDO PACIFIC</h5>
        </td>
    </tr>
</table>
<div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
    <h3>NOTA RETUR PENJUALAN<br>NO : <?=$header[0]->no_retur;?></h3>
</div>
<table class='gridtableX' width='100%' cellpadding='0' cellspacing='0' border='0'>
    <tbody>
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td><?=strtoupper($header[0]->nama_customer);?></td>
        </tr>
        <tr>
            <td>No SPK Marketing</td>
            <td>:</td>
            <td><?=strtoupper($header[0]->no_surat);?></td>
        </tr>
        <tr>
            <td>Kompensasi</td>
            <td>:</td>
			<?php
			if($header[0]->kompensasi =='brg' ){
				$komp = 'Ganti Barang';
			} elseif($header[0]->kompensasi =='htg'){
				$komp = 'Potong Hutang / Jadi Deposit';
			}
			?>
            <td><?=strtoupper($komp);?></td>
        </tr>
    </tbody>
</table>

    <?php
    $matauang = (!empty($header->matauang))?"<br>(".strtoupper($header->matauang).")":'';
    ?>
	
	 <table class='gridtable' width='100%' border='1'>
			<thead>
			<tr class='bg-blue'>
				<th width='10%'>Nama Material</th>
				<th width='10%'>Lot Number</th>
				<th width='10%'>Lebar</th>
				<th width='10%'>Berat</th>
				<th width='10%'>Thickness</th>
				<th width='10%'>Harga Deal / Kg</th>
				<th width='10%'>Total Harga</th>
				<th width='10%'>PPN</th>
				<th width='10%'>Total Harga</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$loop=0;
			$SUM=0;
			$thg=0;
		foreach($detail AS $dt ){
			 $SUM += $dt->total_harga;
			 
		
			  
			$thg = number_format($dt->total_harga,2);
			$loop++;

         ?>
		 
		    <tr>
            <td><?=$dt->item?></td>
			<td><?=$dt->lotno?></td>
            <td><?=$dt->width?></td>
			<td><?=$dt->weight?></td>
			<td><?=$dt->thickness?></td>
			<td><?=number_format($dt->harga_deal,0)?></td>
			<td><?=number_format($dt->total_harga,0)?></td>
			<td><?=number_format($dt->total_ppn,0)?></td>
			<td><?=number_format($dt->total_harga+$dt->total_ppn,0)?></td>
			</tr>
		 

			<?php
			
		    }
		     ?>
			</tbody>
			
		</table>
			
	<br>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' >
        <tr>
            <td width='340'>NOTE</td>
            <td width='30' class='nonebordercst' ></td>
            <td width='120' align="center" >DITERIMA OLEH</td>
        </tr>
        <tr>
            <td  width='340' height='50'  style='border-bottom:none; vertical-align:top;'><?=strtoupper($header[0]->note);?></td>
            <td class='nonebordercst' ></td>
            <td></td>
        </tr>
        <tr>
            <td style='border-top:none;'></td>
            <td class='nonebordercst' ></td>
            <td align="center" >WAREHOUSE</td>
        </tr>
    </table>

    
</body>
</html>