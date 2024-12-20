<html>

<head>
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
            font-size: 11px;
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
            font-size: 12px;
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
            padding: 6px;
        }

        table.gridtableX td.cols {
            border-width: 1px;
            padding: 6px;
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
    </style>
</head>

<body>
    <?php
    foreach ($header as $header) {
    }
    $detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po WHERE no_po = '" . $header->no_po . "' ")->result();
    $jumlahdetail = $this->db->query("SELECT COUNT(no_po) as no_po FROM dt_trans_po WHERE no_po = '" . $header->no_po . "' ")->result();
    $jumlahdata = $jumlahdetail[0]->no_po;
    $tinggi = 300 / $jumlahdata;
    if (empty($header->negara)) {
        $cou = 'Indonesia';
    } else {
        $findnegara = $this->db->query("SELECT * FROM negara WHERE id_negara = '" . $header->negara . "' ")->result();
        $cou = $findnegara[0]->nm_negara;
    }
    $findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '" . $header->id_suplier . "' ")->result();
    $namapic = $findpic[0]->name_pic;
    ?>

    <table border="0" width='100%'>
        <tr>
            <td align="left">
                <img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/images/logo_metalsindo.jpeg' alt="" height='30' width='60'>
            </td>
            <td align="left">
                <h5 style="text-align: left;">PT METALSINDO PACIFIC</h5>
            </td>
            <td align="right" width="483">
                <img src='<?= $_SERVER['DOCUMENT_ROOT']; ?>/metalsindo/assets/img/ISO_9001V1.jpg' alt="" height='30' width='60'>
            </td>
        </tr>
    </table>
    <div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
        <h3>PURCHASE ORDER</h3>
    </div>
    <table class='gridtableX' width='100%' cellpadding='2' border='0'>
        <tbody>
            <tr>
                <td style='width: 50%;'>
                    <p>
                        Address<br>
                        Jl. Jababeka XIV, Blok J no. 10 H<br>
                        Cikarang Industrial Estate, Bekasi 17530<br>
                        Phone : (62-21) 89831726734<br>
                        Fax : (62-21) 89831866<br>
                    </p>
                </td>
                <td style='width: 50%; text-align:right; vertical-align:top;'>
                    <p>
                        PO. No : <?= $header->no_surat ?> <br><br>
                        PR. No : <?= (isset($no_pr)) ? implode(',', $no_pr) : '' ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <table class='gridtable2' width='100%' border='1' align='center' cellpadding='0' cellspacing='0'>
        <tr>
            <td width="380" align="center">
                <table width='380' align="center">
                    <tr>
                        <td width='70' align="left">Supplier</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?= $header->name_suplier ?></td>
                    </tr>
                    <tr>
                        <td width='70' align="left">Address</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?= $header->address_office ?></td>
                    </tr>
                    <tr>
                        <td width='70' align="left">Country</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?= $cou ?></td>
                    </tr>
                    <tr>
                        <td width='70' align="left">PIC</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?= $namapic ?></td>
                    </tr>
                    <tr>
                        <td width='70' align="left">Phone</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?= $header->telephone ?></td>
                    </tr>
                    <tr>
                        <td width='70' align="left">Fax</td>
                        <td width='10' align="left">:</td>
                        <td width='300' align="left"><?php if (empty($header->fax)) {
                                                            echo "-";
                                                        } else {
                                                            echo "$header->fax";
                                                        }  ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <?php
    $matauang = (!empty($header->matauang)) ? "<br>(" . strtoupper($header->matauang) . ")" : '';
    ?>
    <table class='gridtable' cellpadding='0' cellspacing='0' style='vertical-align:top;'>
        <tbody>
            <tr style='vertical-align:middle; background-color:#c2c2c2; font-weight:bold;'>
                <td align="center" width='150'>Material</td>
                <td align="center" width='30'>Width</td>
                <td align="center" width='30'>Length</td>
                <td align="center" width='40'>Total Weight</td>
                <td align="center" width='30'>Unit Price<?= $matauang; ?></td>
                <td align="center" width='80'>Amount<?= $matauang; ?><br><?= $header->cif; ?></td>
                <td align="center" width='130'>Remarks</td>
            </tr>
            <?php
            $CIF = "<br>" . $header->cif . "<br><br><br><br>";
            $TOT_PPH = 0;
            foreach ($detail as $detail) {
                $TOT_PPH += $detail->jumlahharga * $detail->pajak / 100;
                $HS = number_format($detail->hargasatuan, 3);
                $JH = number_format($detail->jumlahharga, 3);
                if (strtolower($header->loi) == 'lokal') {
                    $HS = number_format($detail->hargasatuan, 2);
                    $JH = number_format($detail->jumlahharga, 2);
                }
                if ($jumlahdata <= '30') {
                    echo "	
                    <tr >
                        <td width='150'>" . $detail->nama . "</td>
                        <td width='30' align='right'>" . number_format($detail->width, 2) . "</td>
						<td width='30' align='right'>" . number_format($detail->panjang, 2) . "</td>
                        <td width='40' align='right'>" . number_format($detail->totalwidth, 2) . "</td>
                        <td width='30' align='right'>" . $HS . "</td>
                        <td width='80' align='right'>" . $JH . "</td>
                        <td width='130'>" . $detail->description . "</td>
                    </tr>";
                    $CIF = "";
                } else {
                    echo "	
                    <tr >
                        <td width='150'>" . $detail->namamaterial . "</td>
                        <td width='30' align='right'>" . number_format($detail->width, 2) . "</td>
						<td width='30' align='right'>" . number_format($detail->panjang, 2) . "</td>
                        <td width='40' align='right'>" . number_format($detail->totalwidth, 2) . "</td>
                        <td width='30' align='right'>" . $HS . "</td>
                        <td width='80' align='right'>" . $JH . "</td>
                        <td width='130'>" . $detail->description . "</td>
                    </tr>";
                    $CIF = "";
                }
            } ?>

            <?php
            if ($header->loi == 'Lokal') {
            ?>
                <tr>
                    <td align="center" colspan='3'>PPN </td>
                    <td align="right" colspan='2'></td>
                    <td align="right"><?= number_format($TOT_PPH, 2) ?></td>
                    <td align="center"></td>
                </tr>
            <?php
            }
            if ($header->loi == 'Import') {
                $TOT_PPH = 0;
            }

            $TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 3);
            if (strtolower($header->loi) == 'lokal') {
                $TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 2);
            }
            ?>
            <tr>
                <td align="center" colspan='3'>Total </td>
                <td align="right"><?= number_format($detailsum[0]->sumtotalwidth, 2) ?></td>
                <td align="right"></td>
                <td align="right"><?= $TOTHEAD ?></td>
                <td align="center"></td>
            </tr>
            <tr style='vertical-align:middle;'>
                <td colspan='3' align="center">Issued Date</td>
                <td colspan='3' align="center">
                    <?php
                    if ($header->cif == "Destination") {
                        echo "Delivery To :";
                    } else {
                        echo "Delivery To";
                    };
                    ?>
                </td>
                <td colspan=3' align="center" width='40'>Eta Date</td>
            </tr>
            <tr style='vertical-align:middle;'>
                <td colspan='3' align="center"><?= date('d-M-Y', strtotime($header->tanggal)) ?></td>
                <td colspan='3' align="center">PT Metalsindo Pacific<br>Cikarang, Indonesia</td>
                <td colspan='2' rowspan="2" align="center"><?= date('d-M-Y', strtotime($header->expect_tanggal)) ?></td>
            </tr>
            <tr style="vertical-align: middle;">
                <td colspan="3" align="center">Payment Term</td>
                <td colspan="3" align="center"><?= $header->term ?></td>
            </tr>
        </tbody>
    </table>
    <br>

    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='left'>
        <tr>
            <td align='center'>Note : </td>
            <td width='400'><?= $header->note ?></td>
        </tr>
    </table>
    <br>
    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='left'>
        <tr>
            <td width="15"></td>
            <td align='center' width="80">Received</td>
            <td width='400'></td>
            <td align='center'>Approved</td>
        </tr>
        <tr>
            <td width="15"></td>
            <td height='50' align='center'></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td width="15"></td>
            <td><?= ($header->receiving_person !== '' && $header->receiving_person !== null) ? '(' . strtoupper($header->receiving_person) . ')' : '' ?></td>
            <td></td>
            <td align='center'><u>HARRY WIDJAJA</u></td>
        </tr>
        <tr>
            <td width="15"></td>
            <td></td>
            <td></td>
            <td align='center'>President Director</td>
        </tr>
    </table>


</body>

</html>