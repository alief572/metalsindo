<style>
    .main-table {
        width: 100%;
        border-collapse: collapse;
        /* Ini kunci biar garis nyambung */
        font-size: 13px;
    }

    .main-table td {
        border: 1px solid black;
        /* Kasih border ke semua dulu, nanti tinggal atur mana yang kosong */
        padding: 5px;
    }

    .no-border {
        border: none !important;
    }

    .main-table_2 {
        width: 100%;
        border-collapse: collapse;
        /* Ini kunci biar garis nyambung */
        font-size: 13px;
    }

    .bold {
        font-weight: bold;
    }

    .middle {
        vertical-align: middle;
    }

    .center {
        text-align: center;
    }

    /* Sembunyiin footernya pas di layar biasa */
    .footer-print {
        display: none;
    }

    @media print {
        .footer-print {
            display: block;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: left;
            font-size: 10pt;
            border-top: 1px solid #ccc;
        }

        /* Kasih margin di body biar konten nggak ketutup footer */
        body {
            margin-bottom: 50px;
        }
    }
</style>

<table border="0" width="100%" style="border-collapse: collapse;">
    <tr>
        <th width="11%" style="padding: 5px;">
            <img src="<?= base_url('assets/images/logo_metalsindo.jpeg') ?>" style="display: block; width: 100%;" alt="">
        </th>
        <th style="text-align: left; vertical-align: middle;">
            <h3 style="margin: 0; padding: 0;">PT. METALSINDO PACIFIC</h3>
        </th>
        <th style="text-align: right; vertical-align: middle;">
            <h2 style="margin: 0; padding: 0;">DEBIT NOTE</h2>
        </th>
    </tr>
</table>
<table border="0" width="100%">
    <tr>
        <td width="15%" style="vertical-align: top;">
            Address :
        </td>
        <td width="45%" style="text-align: justify;">
            Kawasan Industri Jababeka <br>
            Jl. Jababeka XIV Blok J No. 10H <br>
            Cikarang Utara - Bekasi 17530
        </td>
        <td></td>
    </tr>
    <tr>
        <td width="10%">
        <td width="65%">
            Phone : (62-21) 89831726/34 Fax No : (62-21) 89831866
        </td>
        <td></td>
    </tr>
</table>
<br>
<table class="main-table">
    <tr>
        <td width="15%" style="border-right: none; vertical-align: top;">
            <span style="font-weight: bold;">SOLD TO :</span>
        </td>
        <td width="38%" style="border-left: none;">
            <span style="font-weight: bold;"><?= $header->nm_supplier ?></span> <br>
            <?= $supplier->address_office ?> <br>
            <span>Telp : <?= $supplier->telephone ?> / Fax : <?= $supplier->fax ?></span> <br>
            <span>ATTN : FINANCE</span>
        </td>
        <td style="text-align: right; border-top: none; border-bottom: none;">
            DEBIT NOTE NUMBER <br>
            DEBIT NOTE DATE <br>
            INVOICE NUMBER REF <br>
            INVOICE NUMBER DATE REF
        </td>
        <td style="font-weight: bold; border-top: none; border-bottom: none; border-right: none;">
            <?= $header->no_surat ?> <br>
            <?= date('d-M-Y', strtotime($header->input_date)) ?> <br>
            <?= $retur_header->no_ref_invoice ?> <br>
            <?= date('d-M-Y', strtotime($retur_header->tgl_invoice)) ?>
        </td>
    </tr>
</table>
<br>
<table class="main-table" border="0">
    <tr>
        <td style="border-right: none; vertical-align: top;" width="17%">
            <span style="font-weight: bold;">DELIVERED TO :</span>
        </td>
        <td width="36%" style="text-align: center; border-left: none; vertical-align: middle; height: 100px; line-height: 100px;">
            AS AN ORDER
        </td>
        <td class="no-border"></td>
        <td class="no-border"></td>
        <td class="no-border"></td>
        <td class="no-border"></td>
    </tr>
</table>
<br>

<table class="main-table_2">
    <thead>
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align: center;">QUANTITY</th>
            <th style="border: 1px solid black; text-align: center;">DESCRIPTION</th>
            <th style="border: 1px solid black; text-align: center;">PER Piece</th>
            <th style="border: 1px solid black; text-align: center;">AMOUNT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border-left: 1px solid black;"></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="border-left: 1px solid black; height: 60px;">
                <span style="font-weight: bold; text-decoration: underline;">RETURN MATERIAL</span>
            </td>
            <td style="border-left: 1px solid black;"></td>
            <td style="border-right: 1px solid black; border-left: 1px solid black;"></td>
        </tr>
        <?php
        $no = 0;
        $grand_ttl = 0;
        foreach ($detail as $item_detail) {
            $no++;

            $get_material = $this->db->get_where('ms_inventory_category3', ['id_category3' => $item_detail->id_material])->row();

            if ($get_material->id_bentuk == 'B2000002') {
                $satuan = 'SHEETS';
            } else {
                $satuan = 'KGS';
            }

            echo '<tr>
                    <td style="border-left: 1px solid black; text-align: right">' . number_format($item_detail->qty, 2) . '</td>
                    <td style="border-left: 1px solid black; text-align: center">' . $satuan . '</td>
                    <td style="border-left: 1px solid black; text-align: left;">' . wordwrap($item_detail->nama_material, 45, "<br>\n", true) . '</td>
                    <td style="border-left: 1px solid black; text-align: right;">' . strtoupper($po->matauang) . ' ' . number_format($item_detail->unit_price) . '</td>
                    <td style="border-left: 1px solid black; border-right: 1px solid black; text-align: right;">' . strtoupper($po->matauang) . ' ' . number_format($item_detail->grand_total) . '</td>
                </tr>';

            $grand_ttl += $item_detail->grand_total;
        }
        ?>
        <tr>
            <td style="border-left: 1px solid black; height: 120px;"></td>
            <td style="border-left: 1px solid black; height: 120px;"></td>
            <td style="border-left: 1px solid black; height: 120px;"></td>
            <td style="border-left: 1px solid black; height: 120px;"></td>
            <td style="border-right: 1px solid black; border-left: 1px solid black; height: 120px;"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" style="border: 1px solid black; text-align: center;">PAY THIS SIDE AMOUNT</th>
            <th style="border: 1px solid black; text-align: right;"><?= strtoupper($po->matauang) . ' ' . number_format($grand_ttl, 2) ?></th>
        </tr>
    </tfoot>
</table>

<br>

<table width="100%" border="0" style="font-size: 13px;">
    <tr>
        <td rowspan="2" style="vertical-align: top;">
            <span class="bold">PLEASE TRANSFER TO OUR BANK ACCOUNT :</span> <br>
            <span>PT. BANK OCBC NISP On Behalf of PT. METALSINDO PACIFIC</span> <br>
            <span>Ruko Plaza Menteng B/1, Thamrin, Lippo Cikarang</span> <br>
            <span>A/C: 103810048480 (Multi Currency)</span>
        </td>
        <td class="">
            BEST REGARDS,
        </td>
    </tr>
    <tr>
        <td style="text-align: left; height: 120px; vertical-align: bottom;">
            <span>Devi Riana</span> <br>
            <span>Finance & Accounting Dept</span>
        </td>
    </tr>
</table>

<div class="footer-print">
    <span>No. Doc : RC - PP - 07 - 02</span> <br>
    <span>Code : F</span> <br>
</div>

<script>
    window.print();
</script>