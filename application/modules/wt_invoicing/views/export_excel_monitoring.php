<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Monitoring Invoice - " . $tgl_awal . " - " . $tgl_akhir . ".xls");
?>
<h2>Monitoring Invoice <?= ($tgl_awal && $tgl_akhir) ? $tgl_awal . ' - ' . $tgl_akhir : '' ?></h2>

<table width="100%" border="1">
    <thead>
        <tr>
            <th style="text-align: center;">No.</th>
            <th style="text-align: center;">No. Invoice</th>
            <th style="text-align: center;">Nama Customer</th>
            <th style="text-align: center;">Marketing</th>
            <th style="text-align: center;">Top</th>
            <th style="text-align: center;">Payment</th>
            <th style="text-align: center;">Nilai Invoice</th>
            <th style="text-align: center;">Total Bayar</th>
            <th style="text-align: center;">Tanggal Invoice</th>
            <th style="text-align: center;">Janji Bayar</th>
            <th style="text-align: center;">Umur Piutang</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($data_monitoring as $item) {
            $no++;

            $this->db->select('a.*');
            $this->db->from('tr_invoice_detail a');
            $this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_category3');
            $this->db->where('a.no_invoice', $item['no_invoice']);
            $this->db->where('b.id_bentuk', 'B2000002');
            $get_detail_sheet = $this->db->get()->result();

            $tipe_sheet = (count($get_detail_sheet) > 0) ? '1' : '0';

            if ($tipe_sheet == '1') {
                $nilai_invoice = 0;

                foreach ($get_detail_sheet as $item_sheet) {
                    $this->db->select('a.qty_sheet');
                    $this->db->from('stock_material a');
                    $this->db->join('dt_delivery_order_child b', 'b.lotno = a.lotno');
                    $this->db->join('tr_delivery_order c', 'c.id_delivery_order = b.id_delivery_order');
                    $this->db->where('c.no_surat', $item['no_do']);
                    $this->db->where('b.id_material', $item_sheet->id_category3);
                    $this->db->where('a.no_kirim', $item['id_do']);
                    // $this->db->group_by('a.id_stock');
                    $get_qty_sheet = $this->db->get()->result();

                    $qty_sheet = 0;
                    foreach ($get_qty_sheet as $item_qty_sheet) {
                        $qty_sheet += $item_qty_sheet->qty_sheet;
                    }

                    $nilai_invoice += ($item_sheet->harga_satuan * $qty_sheet) + (($item_sheet->harga_satuan * $qty_sheet) * 11 / 100);
                }
            } else {
                $this->db->select('SUM(a.qty_invoice * a.harga_satuan) as ttl_harga');
                $this->db->from('tr_invoice_detail a');
                $this->db->where('a.no_invoice', $item['no_invoice']);
                $get_total_invoice = $this->db->get()->row();

                $ttl_harga = $get_total_invoice->ttl_harga;

                $dpp_nilai_lain = ceil(11 / 12 * $ttl_harga);
                $ppn = ($dpp_nilai_lain * 12 / 100);
                $grand_total = ($ttl_harga + $ppn);

                $nilai_invoice = $grand_total;
            }

            $tgl_terima = (!empty($item['tgl_terima'])) ? date('d-F-Y', strtotime($item['tgl_terima'])) : '-';
            $tgl_janji = (!empty($item['tgl_janji_bayar'])) ? date('d-F-Y', strtotime($item['tgl_janji_bayar'])) : date('d-F-Y', strtotime($item['jatuh_tempo']));

            $tgl1 = strtotime($tgl_terima);
            $tgl2 = strtotime(date('Y-m-d'));

            $jarak = $tgl2 - $tgl1;
            if ($tgl1 != '') {
                $umur = $jarak / 60 / 60 / 24;
            } else {
                $umur = 0;
            }

            echo '
                    <tr>
                        <td style="text-align: center;">' . $no . '</td>
                        <td style="text-align: center;">' . $item['no_surat'] . '</td>
                        <td style="text-align: left;">' . $item['name_customer'] . '</td>
                        <td style="text-align: left;">' . $item['nama_sales'] . '</td>
                        <td style="text-align: center;">' . $item['nm_top'] . '</td>
                        <td style="text-align: center;">' . $item['payment'] . '</td>
                        <td style="text-align: right;">' . $nilai_invoice . '</td>
                        <td style="text-align: right;">' . $item['total_bayar'] . '</td>
                        <td style="text-align: center;">' . $item['tgl_invoice'] . '</td>
                        <td style="text-align: center;">' . $tgl_janji . '</td>
                        <td style="text-align: center;">' . $umur . '</td>
                    </tr>
                ';
        }
        ?>
    </tbody>
</table>