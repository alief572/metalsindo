<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Control DO - Scrap.xls");
header("Pragma: no-cache");
header("Expires: 0");

$fields = ['total_do', 'total_sheet', 'total_delivered', 'total_delivered_sheet', 'total_fg', 'total_fg_sheet', 'total_ng', 'total_ng_sheet'];
$f_tahun = $this->input->get('tahun', true);
$f_bulan = $this->input->get('bulan', true);
$periode = ($f_bulan ? date('F', mktime(0, 0, 0, (int) $f_bulan, 1)) . ' ' : '') . ($f_tahun ? $f_tahun : '');
?>
<h2>Control DO - Scrap</h2>
<p>Periode: <?= $periode !== '' ? htmlspecialchars($periode) : 'Semua' ?> | Dicetak: <?= date('d F Y') ?></p>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal DO</th>
            <th>No DO</th>
            <th>SPK Marketing</th>
            <th>Customer</th>
            <th>Qty Order (Kg)</th>
            <th>Qty Order (Sheet)</th>
            <th>Qty OK (Kg)</th>
            <th>Qty OK (Sheet)</th>
            <th>Qty FG (Kg)</th>
            <th>Qty FG (Sheet)</th>
            <th>Qty NG (Kg)</th>
            <th>Qty NG (Sheet)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($data_control_do as $do) {
            $t = isset($arr_totals[$do->id_delivery_order]) ? $arr_totals[$do->id_delivery_order] : null;

            echo '<tr>';
            echo '<td>' . $no . '</td>';
            echo '<td>' . date('d F Y', strtotime($do->tgl_delivery_order)) . '</td>';
            echo '<td>' . htmlspecialchars($do->no_surat) . '</td>';
            echo '<td>' . htmlspecialchars($do->no_spk_marketing) . '</td>';
            echo '<td>' . htmlspecialchars($do->name_customer) . '</td>';
            foreach ($fields as $f) {
                $val = ($t !== null && isset($t->$f)) ? $t->$f : 0;
                echo '<td style="text-align:right;">' . number_format($val, 2) . '</td>';
            }
            echo '</tr>';
            $no++;
        }
        ?>
    </tbody>
</table>
