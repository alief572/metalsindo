    <html>

    <head>
        <style type="text/css">
            @media print {

                table,
                div {
                    break-inside: avoid;
                }
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group;
            }

            tr {
                page-break-after: always !important;
                page-break-before: always !important;
                page-break-inside: auto !important;

            }

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
        $detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po_non_material WHERE no_po = '" . $header->no_po . "' ")->result();
        $jumlahdetail = $this->db->query("SELECT COUNT(no_po) as no_po FROM dt_trans_po_non_material WHERE no_po = '" . $header->no_po . "' ")->result();
        $jumlahdata = $jumlahdetail[0]->no_po;
        $tinggi = 300 / $jumlahdata;
        if (empty($header->negara)) {
            $cou = 'Indonesia';
        } else {
            // $findnegara = $this->db->query("SELECT * FROM negara WHERE id_negara = '" . $header->negara . "' ")->result();
            // $cou = $findnegara[0]->nm_negara;
        }
        $findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '" . $header->id_suplier . "' ")->result();
        $namapic = $findpic[0]->name_pic;

        // print_r($_SERVER['DOCUMENT_ROOT'] . '/origa_live/assets/images/ori_logo.jpg');
        // exit;
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
        <!-- <table class='gridtable2' width='100%' border='1' align='left' cellpadding='0' cellspacing='0'>
            <tr>
                <td width="300" align="center">
                    <table width='300' align="center">
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">NPWP</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;">013911771028000</td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Phone No</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;">0215525582</td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Email</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;">purchoriga@ori.co.id</td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">To</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $data_supplier->nama ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Contact Person</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $data_supplier->contact_person ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Address</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $data_supplier->address ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Phone No</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $data_supplier->telp ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Email</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $data_supplier->email ?></td>
                        </tr>
                    </table>
                </td>
                <td width="300" align="center">
                    <table width='300' align="center" border="0">
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Departement</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $nm_depart ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Currency</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $header->matauang ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">No. PO</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $header->no_surat ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Date</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= date('d F Y', strtotime($header->tanggal)) ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">PR</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $no_pr ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Delivery Date</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= date('d F Y', strtotime($header->delivery_date)) ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">PIC</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;">Purchasing</td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Payment Term</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;"><?= $header->term ?></td>
                        </tr>
                        <tr>
                            <td width='100' align="left" style="vertical-align: top;">Ship To</td>
                            <td width='10' align="left" style="vertical-align: top;">:</td>
                            <td width='200' align="left" style="vertical-align: top;">Jl. Pembangunan 2 No. 34 Kecamatan Batucepet, Keluarahan Batusari. Kota Tanggerang Banten 15122, Indonesia</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <?php
        $matauang = (!empty($header->matauang)) ? "<br>(" . strtoupper($header->matauang) . ")" : '';

        if (strtolower($header->matauang) == 'usd') {
            $kode = '$';
        }
        if (strtolower($header->matauang) == 'idr') {
            $kode = 'Rp';
        }
        ?> -->

        <table class='gridtableX' width='100%' cellpadding='2' border='0'>
            <tbody>
                <tr>
                    <td style='width: 50%;'>
                        <p style="font-size: 10px;">
                            Address<br>
                            Jl. Jababeka XIV, Blok J no. 10 H<br>
                            Cikarang Industrial Estate, Bekasi 17530<br>
                            Phone : (62-21) 89831726734<br>
                            Fax : (62-21) 89831866<br>
                        </p>
                    </td>
                    <td style='width: 50%; text-align:right; vertical-align:top;'>
                        <p style="font-size: 10px;">
                            PO No : <?= $header->no_surat ?> <br><br>
                            PR No : <?= $no_pr ?>
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
                            <td width='70' align="left" style="font-size: 10px;">Supplier</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= $data_supplier->name_suplier ?></td>
                        </tr>
                        <tr>
                            <td width='70' align="left" style="font-size: 10px;">Address</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= $data_supplier->address_office ?></td>
                        </tr>
                        <tr>
                            <td width='70' align="left" style="font-size: 10px;">Country</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= $cou ?></td>
                        </tr>
                        <tr>
                            <td width='70' align="left" style="font-size: 10px;">PIC</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= $namapic ?></td>
                        </tr>
                        <tr>
                            <td width='70' align="left" style="font-size: 10px;">Phone</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= $data_supplier->telephone ?></td>
                        </tr>
                        <tr>
                            <td width='70' align="left" style="font-size: 10px;">Fax</td>
                            <td width='10' align="left" style="font-size: 10px;">:</td>
                            <td width='300' align="left" style="font-size: 10px;"><?= (empty($data_supplier->fax)) ?
                                                                                        "-"
                                                                                        :
                                                                                        $data_supplier->fax
                                                                                    ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class='gridtable' width="100%" cellpadding='0' cellspacing='0' style='margin-top: 10px; vertical-align:top;min-width: 400px !important; max-width: 750px !important;'>
            <tbody>
                <tr style='vertical-align:middle; background-color:#c2c2c2; font-weight:bold;'>
                    <td align="center">Code</td>
                    <td align="center">Description</td>
                    <td align="center">Unit Measurement</td>
                    <td align="center">Qty Pack</td>
                    <td align="center">Unit Packing</td>
                    <td align="center">Price </td>
                    <td align="center">Qty</td>
                    <td align="center">Discount </td>
                    <td align="center">Total </td>
                </tr>
                <?php
                $CIF = "<br>" . $header->cif . "<br><br><br><br>";
                $TOT_PPH = 0;
                $TTL = 0;
                foreach ($detail as $detail) {
                    $kategory = $detail->idmaterial;
                    $barang  = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 ='$kategory' ")->row();

                    $TOT_PPH += $detail->jumlahharga * $detail->pajak / 100;
                    $HS = number_format($detail->hargasatuan, 2);
                    $JH = number_format($detail->jumlahharga, 2);
                    $JHS = $detail->jumlahharga;
                    if (strtolower($header->loi) == 'lokal') {
                        $HS = number_format($detail->hargasatuan, 2);
                        $JH = number_format($detail->jumlahharga, 2);
                        $JHS = $detail->jumlahharga;
                    }

                    $satuan = $detail->satuan;
                    $satuan_packing = $detail->satuan_packing;
                    if ($detail->tipe == '' || $detail->tipe == null) {
                        $check_code = $this->db->get_where('accessories', ['id' => $detail->idmaterial])->num_rows();

                        if ($check_code4 < 1) {
                            $this->db->select('b.code as satuan, c.code as satuan_packing');
                            $this->db->from('accessories a');
                            $this->db->join('ms_satuan b', 'b.id = a.id_unit', 'left');
                            $this->db->join('ms_satuan c', 'c.id = a.id_unit_gudang', 'left');
                            $this->db->where('a.id', $detail->idmaterial);
                            $data_material = $this->db->get()->row();

                            $satuan = $data_material->satuan;
                            $satuan_packing = $data_material->satuan_packing;
                        }
                    }

                    $detail_code = str_split($detail->code, 35);
                    $final_detail_code = implode("<br>", $detail_code);

                    $detail_nama = str_split($detail->nama, 35);
                    $final_detail_nama = implode("<br>", $detail_nama);

                    if ($jumlahdata <= '30') {
                        $konversi = ($detail->konversi > 0) ? $detail->konversi : 1;

                        echo "	
                        <tr >
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . $final_detail_code . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . $final_detail_nama . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='center'>" . ucfirst($satuan) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . number_format($detail->qty / $konversi) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . ucfirst($satuan_packing) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $header->matauang . " " . $HS . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $detail->qty . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $header->matauang . " " . number_format($detail->nilai_disc) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $header->matauang . " " . $JH . "</td>
                        </tr>";
                        $CIF = "";
                    } else {
                        $konversi = ($detail->konversi > 0) ? $detail->konversi : 1;

                        echo "	
                        <tr >
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . $final_detail_code . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . $final_detail_nama . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='center'>" . ucfirst($satuan) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . number_format($detail->qty / $konversi, 2) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;'>" . ucfirst($satuan_packing) . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $header->matauang . " " . $HS . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $detail->qty . "</td>
                            <td style='font-size: 8px; max-width: 250px !important; word-wrap: break-word;' align='right'>" . $header->matauang . " " . $JH . "</td>
                        </tr>";
                        $CIF = "";
                    }

                    $TTL += $JHS;
                } ?>

                <?php
                if ($header->loi == 'Lokal') {
                ?>

                <?php
                }
                if ($header->loi == 'Import') {
                    $TOT_PPH = 0;
                }

                $TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 2);
                if (strtolower($header->loi) == 'lokal') {
                    $TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 2);
                }
                ?>
                <tr>
                    <td align="right" colspan='8' style="font-size: 8px;">Total </td>
                    <td align="right" style="font-size: 8px;"><?= $header->matauang . ' ' . number_format($TTL, 2) ?></td>

                </tr>
                <tr>
                    <td align="right" colspan='8' style="font-size: 8px;">Biaya Kirim </td>
                    <td align="right" style="font-size: 8px;"><?= $header->matauang . ' ' . number_format($header->taxtotal, 2) ?></td>

                </tr>

                <tr>
                    <td align="right" colspan='8' style="font-size: 8px;">Discount </td>
                    <td align="right" style="font-size: 8px;"><?= $header->matauang . ' ' . number_format($header->nilai_disc, 2) ?></td>
                </tr>

                <tr>
                    <td align="right" colspan='8' style="font-size: 8px;">PPN </td>
                    <td align="right" style="font-size: 8px;"><?= $header->matauang . ' ' . number_format($header->total_ppn) ?></td>
                </tr>

                <tr>
                    <td align="right" colspan='8' style="font-size: 8px;">Grand Total </td>
                    <td align="right" style="font-size: 8px;"><?= $header->matauang . ' ' . number_format($header->subtotal) ?></td>

                </tr>

            </tbody>
            <tfoot>
                <tr>
                    <td align="center" colspan="2">DELIVERY TO :</td>
                    <td align="center" colspan="2">DELIVERY DATE :</td>
                    <td align="center" colspan="2">PAYMENT TERMS :</td>
                    <td align="center" colspan="3">DATE REQUIRED :</td>
                </tr>
                <tr>
                    <td align="center" colspan="2">PT. METALSINDO PACIFIC, CIKARANG</td>
                    <td align="center" colspan="2"><?= date('d-M-y', strtotime($header->delivery_date)) ?></td>
                    <td align="center" colspan="2"><?= $terms ?></td>
                    <td align="center" colspan="3"><?= (!empty($tgl_dibutuhkan)) ? date('d-M-y', strtotime($tgl_dibutuhkan)) : ''; ?></td>
                </tr>
            </tfoot>
        </table>

        <br>

        <table width="100%" border="1" cellpadding='0' cellspacing='0'>
            <tr>
                <td width="620" style="font-size: 8px;">
                    <b>Syarat & Ketentuan :</b> <br>
                    1. Cantumkan nomor PO ini dalam surat jalan, faktur/kwitansi, dan semua dokumen yang berkaitan. <br>
                    2. Cantumkan nomor & nama rekening untuk pembayaran secara lengkap (beserta nama bank) pada invoice. <br>
                    3. Perusahaan kami berhak membatalkan PO ini jika terjadi : <br>
                    &nbsp;&nbsp;&nbsp; - Barang yang dikirim tidak sesuai dengan spesifikasi yang terdapat dalam PO ini. <br>
                    &nbsp;&nbsp;&nbsp; - Keterlambatan pengiriman melebihi 1 (satu) minggu tanpa alasan yang dapat diterima. <br>
                    4. Penjual wajib bertanggung jawab untuk mengganti semua barang jika terdapat kesalahan dalam material (tanpa adanya biaya tambahan) <br>
                    5. Penjual wajib mengirimkan konfirmasi atas PO ini dengan mengirimkan kembali PO yang sudah di tanda tangan dan distample pihak penjual keapda perusaahaan kami (maks. 2 (dua) hari kerja). <br>
                    Penjual wajib mematuhi semua syarat dan ketentuan perusahaan kami. <br>
                    6. Penjual wajib mematuhi semua syarat dan ketentuan perusahaan kami. <br>
                    7. Aturan perpajakan berlaku sesuai aturan perpajakan Indonesia <br>
                    8. Dokumen tagihan dibutuhkan setelah penerimaan barang dan/atau jasa <br>
                    9. Jangka waktu pembayaran terhitung sejak faktur kami terima (disertasi bukti tanda terima dari perusahaan kami). <br>
                    10. Tukar faktur dilakukan setiap hari Selasa (09.00 - 12.00 & 13.00 - 15.00). <br>
                </td>
            </tr>
        </table>

        <br>

        <table class="gridtable2" width="620" border="1" cellpadding='0' cellspacing='0'>
            <tr>
                <td width="620" align="left" style="font-size: 10px; overflow: auto !important; border-top: 1px solid black; border-right: 1px solid black; border-left: 1px solid black; ">
                    <table width="620" align="left" border="0">
                        <tr>
                            <td width="620" height="100%">
                                <b>Keterangan :</b> <br>
                                <?= wordwrap($header->note, 90, '<br>', true) ?>
                            </td>
                        </tr>
                    </table>
                </td>
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