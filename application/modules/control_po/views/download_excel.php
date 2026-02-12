<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Control PO.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<h2>Control PO (<?= date('d F Y') ?>)</h2>
<table width="100%" border="1">
    <thead>
        <tr>
            <th style="text-align: center;">#</th>
            <th style="text-align: center;">No. PR</th>
            <th style="text-align: center;">No. PO</th>
            <th style="text-align: center;">Tanggal PO</th>
            <th style="text-align: center;">Supplier</th>
            <th style="text-align: center;">Material</th>
            <th style="text-align: center;">Width</th>
            <th style="text-align: center;">Qty Order (Kg)</th>
            <th style="text-align: center;">Qty Receive (Kg)</th>
            <th style="text-align: center;">Balance</th>
            <th style="text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($data_control_po)) :
            $no = 0;
            foreach ($data_control_po as $item) :
                $no++;

                // $get_incoming = $this->db->get_where('dt_incoming', array('id_dt_po' => $item['id_dt_po']))->row();

                $this->db->select('COALESCE(SUM(a.width_recive), 0) as total_received');
                $this->db->from('dt_incoming a');
                $this->db->where('a.id_dt_po', $item['id_dt_po']);
                $get_incoming = $this->db->get()->row();

                $incoming = (!empty($get_incoming->total_received)) ? $get_incoming->total_received : 0;

                $status = 'Open';
                if ($item['close_po'] == 'Y') {
                    $status = 'Closed';
                }

                echo '<tr>';
                echo '<td style="text-align: center;">' . $no . '</td>';
                echo '<td style="text-align: center;">' . $item['no_pr'] . '</td>';
                echo '<td style="text-align: center;">' . $item['no_surat_po'] . '</td>';
                echo '<td style="text-align: center;">' . date('d-m-Y', strtotime($item['tanggal_po'])) . '</td>';
                echo '<td style="text-align: left;">' . $item['name_suplier'] . '</td>';
                echo '<td style="text-align: left;">' . $item['nama_material'] . '</td>';
                echo '<td style="text-align: right;">' . number_format($item['width_po'], 2) . '</td>';
                echo '<td style="text-align: right;">' . number_format($item['qty_po'], 2) . '</td>';
                echo '<td style="text-align: right;">' . number_format($incoming, 2) . '</td>';
                echo '<td style="text-align: right;">' . number_format(($item['qty_po'] - $incoming), 2) . '</td>';
                echo '<td style="text-align: center;">' . $status . '</td>';
                echo '</tr>';
            endforeach;
        endif;
        ?>
    </tbody>
</table>
