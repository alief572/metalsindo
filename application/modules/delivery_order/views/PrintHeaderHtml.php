<html>

<head>
    <title>Delivery Order</title>
    <style type="text/css">
        .header_style_company {
            padding: 15px;
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 20px;
            line-spacing: 15px;
            vertical-align: bottom;
        }

        .header_style_company2 {
            padding: 15px;
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 15px;
            line-spacing: 15px;
            vertical-align: top;
        }

        .header_style_alamat {
            padding: 10px;
            color: black;
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 12px;
            line-spacing: 15px;
        }

        table.gridtable {
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 12px;
            line-spacing: 15px;


        }



        table.gridtable2 {
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 13px;
            line-spacing: 15px;

        }

        table.gridtable2 td {
            border-width: 1px;

        }

        table.gridtable2 td.cols {
            border-width: 1px;

        }

        table.gridtableX {
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 12px;
            line-spacing: 15px;

        }

        table.gridtableX td {
            border-width: 1px;
            padding: 2px;
        }

        table.gridtableX td.cols {
            border-width: 1px;

        }

        table.gridtableX2 {
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 12px;
            line-spacing: 15px;

        }

        table.gridtableX2 td {
            border-width: 1px;
            padding: 2px;
        }

        table.gridtableX2 td.cols {
            border-width: 1px;
            padding: 2px;
        }

        @print {
            @page :footer {
                color: #fff
            }

            @page :header {
                color: #fff
            }
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


    <table border="0" width='100%'>
        <tr width="100%">
            <td align="left">
                <img src='https://sentral.dutastudy.com/metalsindo/assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
            </td>
            <td style='width: 50%; vertical-align:center;'>
                <h4 style="text-align: center;"><b>PT METALSINDO PACIFIC</b></h4>
            </td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right" width="3000px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td align="right">
                <img src='https://sentral.dutastudy.com/metalsindo/assets/img/logo_iso.jpg' alt="" height='30' width='60'>
            </td>
        </tr>
    </table>
    <div style='display:block; border-color:none; background-color:#c2c2c2; font-family: Tahoma, Verdana, Segoe, sans-serif;' align='center'>
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
                <td style='width: 50%; vertical-align:top;'>
                    To :<br>
                </td>
            </tr>
            <tr>
                <td style='width: 50%; vertical-align:top; border-collapse: collapse; border-width: 0.5px; border:solid;'>
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
            <td style='width: 50%; vertical-align:top;'>
                Supir : <?= $header->driver ?>
            </td>
            <td style='width: 50%; vertical-align:top;'>
                No Kendaraan : <?= $header->nopol ?>
            </td>
        </tr>
    </table>

    <br>

    <table class='gridtable' border="1" width='100%' align="center" cellpadding="2" cellspacing="0">
        <tr>
            <td rowspan='2' align="center" valign="middle" width="8">NO</td>
            <td rowspan='2' align="center" valign="middle" width="110">GOOD OF MERCHANDISE</td>
            <td rowspan='2' align="center" valign="middle" width="55">SPEC</td>
            <td rowspan='2' align="center" valign="middle" width="135">LOT NO ALLOY</td>
            <td colspan='3' align="center" valign="middle" width="65">QUANTITY</td>
            <td rowspan='2' align="center" valign="middle" width="60">REMARK PALLET NO</td>
        </tr>
        <tr>
            <td width="20" align="center">COIL'S</td>
            <td width="25" align="center">SHEET'S</td>
            <td width="20" align="center">KG'S</td>
        </tr>
        <?php


        $dt = $this->db->query("SELECT *  FROM dt_delivery_order_child WHERE id_delivery_order ='$header->id_delivery_order' AND bantuan='0'")->result();
        ?>

        <?php
        $id_material = "";
        $width       = "";
        $lengthh = "";
        $i = 0;

        $loop = 0;
        foreach ($dt as $dtl) {

            if ($i > 0) {

                if ($id_material != $dtl->id_material) {

        ?>

                    <tr>
                        <td bgcolor="#c9c9c9"></td>
                        <td bgcolor="#c9c9c9"></td>
                        <td bgcolor="#c9c9c9"></td>
                        <td bgcolor="#c9c9c9"></td>
                        <td bgcolor="#c9c9c9" align="center"><?= number_format($qty, 2) ?> </td>
                        <td bgcolor="#c9c9c9"></td>
                        <td bgcolor="#c9c9c9" align="center"><?= number_format($berat, 2) ?></td>
                        <td bgcolor="#c9c9c9"></td>
                    </tr>

                    <?php
                    $qty = 0;
                    $berat = 0;
                } else {
                    if ($width != $dtl->width || $length != $dtl->length) {
                    ?>

                        <tr>
                            <td bgcolor="#c9c9c9"></td>
                            <td bgcolor="#c9c9c9"></td>
                            <td bgcolor="#c9c9c9"></td>
                            <td bgcolor="#c9c9c9"></td>
                            <td bgcolor="#c9c9c9" align="center"><?= number_format($qty, 2) ?> </td>
                            <td bgcolor="#c9c9c9"></td>
                            <td bgcolor="#c9c9c9" align="center"><?= number_format($berat, 2) ?></td>
                            <td bgcolor="#c9c9c9"></td>
                        </tr>

            <?php

                        $qty = 0;
                        $berat = 0;
                    }
                }
            }
            $i++;
            $loop++;

            $SUMKG += $dtl->weight_mat;
            $SUMQTY += $dtl->qty_mat;



            $bentuk = $dtl->bentuk;
            $length = $dtl->length;
            if ($dtl->length <= 0) {
                $length = 'C';
            }
            $coil = 'C';



            $spec = number_format($dtl->thickness, 2) . ' x ' . floatval($dtl->width) . ' x ' . $length;
            ?>
            <tr>
                <td width="8" align="center"><?= $loop ?></td>
                <td width="180"><?= $dtl->nm_material . ',' . $dtl->part_number ?></td>
                <td width="55"><?= $spec ?></td>
                <td width="145" align="left"><?= $dtl->lotno ?></td>
                <td width="20" align="center"><?= number_format($dtl->qty_mat, 2); ?></td>
                <td width="25" align="right"></td>
                <td width="20" align="center"><?= number_format($dtl->weight_mat, 2); ?></td>
                <td width="60" align="center"><?= $dtl->remark ?></td>
            </tr>


        <?php

            $width = $dtl->width;
            $lengthh = $dtl->length;
            $id_material = $dtl->id_material;
            $qty = $qty + $dtl->qty_mat;
            $berat = $berat + $dtl->weight_mat;
        }
        ?>
        <tr>
            <td bgcolor="#c9c9c9"></td>
            <td bgcolor="#c9c9c9"></td>
            <td bgcolor="#c9c9c9"></td>
            <td bgcolor="#c9c9c9"></td>
            <td bgcolor="#c9c9c9" align="center"><?= number_format($qty, 2) ?> </td>
            <td bgcolor="#c9c9c9"></td>
            <td bgcolor="#c9c9c9" align="center"><?= number_format($berat, 2) ?></td>
            <td bgcolor="#c9c9c9"></td>
        </tr>
        <tr>
            <td bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="8"></td>
            <td bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="110">TOTAL</td>
            <td bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="55"></td>
            <td bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="135"></td>
            <td bgcolor="#c9c9c9" width="20" align="center"><?= number_format($SUMQTY, 2) ?></td>
            <td bgcolor="#c9c9c9" width="25" align="center"></td>
            <td bgcolor="#c9c9c9" width="20" align="center"><?= number_format($SUMKG, 2) ?></td>
            <td bgcolor="#c9c9c9" rowspan='2' align="center" valign="middle" width="60"></td>
        </tr>

    </table>
    <br>
    <br>
    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
        <tr>
            <td style='width: 33%; vertical-align:top;text-align:center; '></td>
            <td style='width: 33%; vertical-align:top;text-align:center; '></td>
            <td style='width: 34%; vertical-align:top;text-align:center; '>Cikarang,<?php echo date('d-m-Y', strtotime($header->tanggal)) ?></td>
        </tr>
    </table>

    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
        <tr>
            <td style='width: 33%; vertical-align:top;text-align:center; '>YANG MENERIMA<br><i>(RECEIVED BY)</i></td>
            <td style='width: 33%; vertical-align:top;text-align:center; '>DIPERIKSA<br><i>(CHECKED BY)</i></td>
            <td style='width: 34%; vertical-align:top;text-align:center; '>HORMAT KAMI<br><i>(YOURS FAITHFULLY)</i></td>
        </tr>
        <tr>
            <td height='70' align='center'></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style='width: 33%; vertical-align:top;text-align:center; '>STEMPEL/NAMA JELAS</td>
            <td style='width: 33%; vertical-align:top;text-align:center; '>POS JAGA</td>
            <td style='width: 34%; vertical-align:top;text-align:center; '>GUDANG JADI</td>
        </tr>
    </table>


    <script type="text/javascript">
        window.print();
    </script>