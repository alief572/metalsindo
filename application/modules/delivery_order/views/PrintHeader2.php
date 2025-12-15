<html>

<head>
    <title>Delivery Order</title>
    <style type="text/css">
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
            border: 1px solid;
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
    </style>
</head>

<body>
    <?php
    foreach ($header as $header) {
    }
    $no_surat        = $header->no_spk_marketing;
    $tgl            = $this->db->query("SELECT * FROM tr_spk_marketing WHERE no_surat like '%%$no_surat%%' ")->row();
    // print_r($tgl);
    // exit;
    ?>

    <b>
        <table border="0" width='100%'>
            <tr width="100%">
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
                <td align="center">
                    <h4 style="text-align: center;"><b>PT METALSINDO PACIFIC</b></h4>
                </td>
                <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
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
        <div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
            <h4>DELIVERY ORDER</h4>
        </div>
        <table class='gridtableX' border="0" width='100%' align="center" cellpadding='0' cellspacing='0'>
            <tr>
                <td>
                    <p style="text-align: center;"><b>NO : <?= $header->no_surat ?></b></p>
                </td>
            </tr>
        </table>
        <table class='gridtableX' width='100%' cellpadding='0' cellspacing='0' border='0'>
            <tbody>
                <tr>
                    <td style='width: 50%;' rowspan='2'>
                        Address <br>
                        Jl. Jababeka XIV, Blok J no. 10 H <br>
                        Cikarang Industrial Estate, Bekasi 17530<br>
                        Phone: (62-21) 89831726734<br>
                        Fax: (62-21) 89831866<br>
                        NPWP: 21.098.204.7-414.000
                    </td>
                    <td style='width: 50%; vertical-align:top; border-collapse: collapse; border-width: 1px; border-bottom:solid;'>
                        To :<br>
                    </td>
                </tr>
                <tr>
                    <td style='width: 50%; vertical-align:top; border-collapse: collapse; border-width: 1px; border:solid;'>
                        <?= strtoupper($header->name_customer) ?><br>
                        Address :<br>
                        <?= $header->address_office ?>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td style='width: 50%; vertical-align:top;'>
                        REFF : <u><?= $header->reff ?></u> / <?= date('d-M-Y', strtotime($tgl->tgl_po)) ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class='gridtableX' border="0" width='100%' align="left">
            <tr>
                <td align="left">
                    Harap barang-barang tersebut dibawah ini supaya diterima dengan baik sesuai dengan surat pesanan.<br>
                    <i>(Please receive this good mentioned with gently care as an order)</i>
                </td>
            </tr>
        </table>
        <br>

        <table class='gridtableX' border="1" width='50%' align="center" cellpadding="4" cellspacing="0">
            <tr>
                <td style='width: 50%; vertical-align:top; font-weight:bold;'>
                    Supir : <?= $header->driver ?>
                </td>
                <td style='width: 50%; vertical-align:top; font-weight:bold;'>
                    No Kendaraan : <?= $header->nopol ?>
                </td>
            </tr>
        </table>

        <br>

        <table class='gridtable' border="1" width='100%' align="center" cellpadding="2" cellspacing="0">
            <tr>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="8">NO</th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="100">GOOD OF MERCHANDISE</th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="55">SPEC</th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="135">LOT NO ALLOY</th>
                <th bgcolor="#c9c9c9" colspan='3' align="center" valign="middle" width="65">QUANTITY</th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="60">REMARK PALLET NO</th>
            </tr>
            <tr>
                <th bgcolor="#c9c9c9" width="20" align="center">COIL'S</th>
                <th bgcolor="#c9c9c9" width="25" align="center">SHEET'S</th>
                <th bgcolor="#c9c9c9" width="20" align="center">KG'S</th>
            </tr>
            <?php


            $dt = $this->db->query("SELECT *  FROM dt_delivery_order_child WHERE id_delivery_order ='$header->id_delivery_order' AND bantuan='0'")->result();
            ?>

            <?php
            $id_material = "";
            $width       = "";
            $length      = "";
            $i = 0;
            $loop = 0;
            foreach ($dt as $dtl) {
                $get_material_bentuk = $this->db->get_where('ms_inventory_category3', array('id_category3' => $dtl->id_material))->row();

                if ($i > 0) {
                    if ($id_material != $dtl->id_material) {



            ?>


                        <?php
                        $qty = 0;
                        $qty_sheet = 0;
                        $berat = 0;
                    } else {
                        if ($width != $dtl->width || $length != $dtl->length) {
                        ?>



                <?php

                            $qty = 0;
                            $qty_sheet = 0;
                            $berat = 0;
                        }
                    }
                }
                $i++;

                $sheet_qty = 0;

                if ($get_material_bentuk->id_bentuk == 'B2000002') {
                    $get_qty_sheet = $this->db->get_where('stock_material', ['lotno' => $dtl->lotno])->row_array();

                    if (!empty($get_qty_sheet)) {
                        if ($get_qty_sheet['qty_sheet'] > 0) {
                            $sheet_qty = $get_qty_sheet['qty_sheet'];
                        } else {
                            $sheet_qty = $dtl->qty_order;
                        }
                    } else {
                        $sheet_qty = $dtl->qty_order;
                    }
                }

                $SUMKG += $dtl->qty_in;
                $SUMQTY += ($get_material_bentuk->id_bentuk == 'B2000001') ? $dtl->qty_order : 0;
                $SUMQTY_SHEET += $sheet_qty;



                $bentuk = $dtl->bentuk;
                $length = $dtl->length;
                if ($dtl->length <= 0) {
                    $length = 'C';
                }
                $coil = 'C';





                $spec = number_format($dtl->thickness, 2) . ' x ' . floatval($dtl->width) . ' x ' . $length;
                $loop++;
                ?>
                <tr>
                    <td width="8" align="center"><?= $loop ?></td>
                    <td width="180"><?= $dtl->nm_material . ',' . $dtl->part_number ?></td>
                    <td width="55"><?= $spec ?></td>
                    <td width="145" align="left"><?= $dtl->lotno ?></td>
                    <td width="20" align="center"><?= ($get_material_bentuk->id_bentuk == 'B2000001') ? number_format($dtl->qty_order, 0) : '' ?></td>
                    <td width="25" align="center"><?= number_format($sheet_qty) ?></td>
                    <td width="20" align="right"><?= $dtl->qty_in; ?></td>
                    <td width="60" align="center"><?= $dtl->remark ?></td>
                </tr>


            <?php

                $width = $dtl->width;
                $length = $dtl->length;
                $id_material = $dtl->id_material;
                $qty += ($get_material_bentuk->id_bentuk == 'B2000001') ? $dtl->qty_order : 0;
                $qty_sheet += $sheet_qty;
                $berat = $berat + $dtl->qty_in;
            }
            ?>

            <tr>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="8"></th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="110">TOTAL</th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="55"></th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="135"></th>
                <th bgcolor="#c9c9c9" width="20" align="center"><?= $SUMQTY ?></th>
                <th bgcolor="#c9c9c9" width="20" align="center"><?= $SUMQTY_SHEET ?></th>
                <th bgcolor="#c9c9c9" width="20" align="center"><?= $SUMKG ?></th>
                <th bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="60"></th>
            </tr>

        </table>
        <br>
        <br>
        <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
            <tr>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'></td>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'></td>
                <td style='width: 34%; vertical-align:top;text-align:center; border-bottom:none;'>Cikarang,<?php echo date('d-m-Y', strtotime($header->tanggal)) ?></td>
            </tr>
        </table>

        <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
            <tr>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'>YANG MENERIMA<br><i>(RECEIVED BY)</i></td>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'>DIPERIKSA<br><i>(CHECKED BY)</i></td>
                <td style='width: 34%; vertical-align:top;text-align:center; border-bottom:none;'>HORMAT KAMI<br><i>(YOURS FAITHFULLY)</i></td>
            </tr>
            <tr>
                <td height='70' align='center'></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'>STEMPEL/NAMA JELAS</td>
                <td style='width: 33%; vertical-align:top;text-align:center; border-bottom:none;'>POS JAGA</td>
                <td style='width: 34%; vertical-align:top;text-align:center; border-bottom:none;'>GUDANG JADI</td>
            </tr>
        </table>

    </b>