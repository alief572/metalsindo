<style type="text/css">
    /* CSS disederhanakan agar kompatibel dengan HTML2PDF */
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
    }
    .gridtable {
        font-size: 9px;
        color: #333333;
        border: 1px solid #808080;
    }
    .gridtable td, .gridtable tr {
        padding: 5px;
        border: 1px solid #808080;
    }
    .gridtable2 {
        font-size: 10px;
        border: none;
    }
    p, .font {
        font-size: 10px;
    }
    .header-text {
        font-size: 10px;
    }

    .footer-penomoran {
        position: running(footer);
        width: 100%;
        text-align: right;
        font-size: 10px;
        color: #555;
    }
    
    /* Gunakan selector khusus HTML2PDF */
    page_footer {
        width: 100%;
        text-align: right;
    }
</style>

<page backtop="10mm" backbottom="15mm" backleft="10mm" backright="10mm">
    

    <?php
    foreach ($header as $h) { } // Alias agar lebih pendek
    $detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po_non_material WHERE no_po = '" . $h->no_po . "' ")->result();
    
    // Logic pencarian PIC dsb
    $findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '" . $h->id_suplier . "' ")->result();
    $namapic = (!empty($findpic)) ? $findpic[0]->name_pic : '-';
    $cou = (empty($h->negara)) ? 'Indonesia' : $h->negara;
    ?>

    <table style="width: 100%;">
        <tr>
            <td style="width: 10%;"><img src='assets/images/logo_metalsindo.jpeg' height='30'></td>
            <td style="width: 40%; vertical-align: middle;"><h5>PT METALSINDO PACIFIC</h5></td>
            <td style="width: 50%; text-align: right;"><img src='assets/img/ISO_9001V1.jpg' height='30'></td>
        </tr>
    </table>

    <div style='background-color:#c2c2c2; padding: 5px; text-align: center;'>
        <b style="font-size: 14px;">PURCHASE ORDER</b>
    </div>

    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%; font-size: 10px; line-height: 1.4;">
                Address<br>
                Jl. Jababeka XIV, Blok J no. 10 H<br>
                Cikarang Industrial Estate, Bekasi 17530<br>
                Phone : (62-21) 89831726734 / Fax : (62-21) 89831866
            </td>
            <td style="width: 50%; text-align: right; font-size: 10px; vertical-align: top;">
                <b>PO No :</b> <?= $h->no_surat ?> <br>
                <b>PR No :</b> <?= (is_array($no_pr)) ? implode(', ', $no_pr) : $no_pr; ?>
            </td>
        </tr>
    </table>

    <table class="gridtable" style="width: 60%; margin-top: 10px;">
        <tr>
            <td style="width: 25%; background-color: #f7f7f7;">Supplier</td>
            <td style="width: 75%;"><?= $data_supplier->name_suplier ?></td>
        </tr>
        <tr>
            <td style="background-color: #f7f7f7;">Address</td>
            <td><?= $data_supplier->address_office ?></td>
        </tr>
        <tr>
            <td style="background-color: #f7f7f7;">Country / PIC</td>
            <td><?= $cou ?> / <?= $namapic ?></td>
        </tr>
        <tr>
            <td style="background-color: #f7f7f7;">Phone / Fax</td>
            <td><?= $data_supplier->telephone ?> / <?= ($data_supplier->fax) ? $data_supplier->fax : '-' ?></td>
        </tr>
    </table>

    <table class="gridtable" style="width: 100%; margin-top: 15px;">
        <thead>
            <tr style='background-color:#c2c2c2; font-weight:bold; text-align: center;'>
                <th style="width: 15%;">Code</th>
                <th style="width: 20%;">Description</th>
                <th style="width: 10%;">UM</th>
                <th style="width: 8%;">Qty Pk</th>
                <th style="width: 10%;">Price</th>
                <th style="width: 7%;">Qty</th>
                <th style="width: 10%;">Disc</th>
                <th style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $TTL = 0;
            foreach ($detail as $row) {
                $JH = $row->jumlahharga;
                $TTL += $JH;
                $konversi = ($row->konversi > 0) ? $row->konversi : 1;
                ?>
                <tr>
                    <td style="font-size: 8px;"><?= $row->idmaterial ?></td>
                    <td style="font-size: 8px;"><?= $row->nama ?></td>
                    <td align="center"><?= ucfirst($row->satuan) ?></td>
                    <td align="right"><?= number_format($row->qty / $konversi) ?></td>
                    <td align="right"><?= $h->matauang ?> <?= number_format($row->hargasatuan, 2) ?></td>
                    <td align="right"><?= number_format($row->qty) ?></td>
                    <td align="right"><?= number_format($row->nilai_disc) ?></td>
                    <td align="right"><?= $h->matauang ?> <?= number_format($JH, 2) ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tr style="background-color: #eee;">
            <td colspan="7" align="right"><b>Sub Total</b></td>
            <td align="right"><b><?= $h->matauang ?> <?= number_format($TTL, 2) ?></b></td>
        </tr>
        <tr>
            <td colspan="7" align="right">PPN</td>
            <td align="right"><?= $h->matauang ?> <?= number_format($h->total_ppn, 2) ?></td>
        </tr>
        <tr style="background-color: #c2c2c2;">
            <td colspan="7" align="right"><b>Grand Total</b></td>
            <td align="right"><b><?= $h->matauang ?> <?= number_format($TTL + $h->total_ppn, 2) ?></b></td>
        </tr>
    </table>

    <table class="gridtable" style="width: 100%; margin-top: 10px; text-align: center;">
        <tr style="background-color: #f7f7f7;">
            <td style="width: 25%;">DELIVERY TO</td>
            <td style="width: 25%;">DELIVERY DATE</td>
            <td style="width: 25%;">PAYMENT TERMS</td>
            <td style="width: 25%;">DATE REQUIRED</td>
        </tr>
        <tr>
            <td>PT. METALSINDO PACIFIC</td>
            <td><?= date('d-M-y', strtotime($h->delivery_date)) ?></td>
            <?php 
                $this->db->select('a.keterangan');
                $this->db->from('tr_top_po a');
                $this->db->where('a.no_po', $h->no_po);
                $this->db->order_by('a.created_on', 'asc');
                $this->db->limit(1);
                $get_first_top = $this->db->get()->row();

                $payment_terms = (!empty($get_first_top->keterangan)) ? $get_first_top->keterangan : '';
            ?>
            <td><?= $payment_terms ?></td>
            <td><?= (!empty($date_required)) ? date('d-M-y', strtotime($date_required)) : '-'; ?></td>
        </tr>
    </table>

    <div style="margin-top: 15px; border: 1px solid #808080; padding: 5px; font-size: 8px;">
        <b>Syarat & Ketentuan :</b><br>
        1. Cantumkan nomor PO dalam surat jalan & invoice. 2. Sertakan nomor rekening lengkap. 
        3. Kami berhak membatalkan jika spesifikasi tidak sesuai atau terlambat > 1 minggu. 
        4. Penjual wajib mengganti barang reject tanpa biaya tambahan. 5. Konfirmasi PO maksimal 2 hari kerja.
    </div>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 30%; text-align: center;">
                Received,<br><br><br><br>
                (________________)
            </td>
            <td style="width: 40%;"></td>
            <td style="width: 30%; text-align: center;">
                Approved,<br><br><br><br>
                <u><b>HARRY WIDJAJA</b></u><br>
                President Director
            </td>
        </tr>
    </table>

    <page_footer>
        <table style="width: 100%; padding: 10px;">
            <tr>
                <td style="text-align: left; width: 50%;">PT METALSINDO PACIFIC</td>
                <td style="text-align: right; width: 50%;">Page [[page_cu]] / [[page_nb]]</td>
            </tr>
        </table>
    </page_footer>

</page>