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
            <th style="text-align: center;">Date Incoming</th>
            <th style="text-align: center;">Kg / Lot Number</th>
            <th style="text-align: center;">Total Qty Receive (Kg)</th>
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

                $this->db->select('tgl_datang, SUM(width_recive) as kg_recive');
                $this->db->from('dt_incoming');
                $this->db->where('id_dt_po', $item['id_dt_po']);
                $this->db->where('width_recive >', 0);
                $this->db->group_by('tgl_datang');
                $this->db->order_by('tgl_datang', 'ASC');
                $incoming_list = $this->db->get()->result_array();

                $count_inc = count($incoming_list);
                $rowspan = ($count_inc > 1) ? ' rowspan="' . $count_inc . '"' : '';

                $total_received = 0;
                if ($count_inc > 0) {
                    foreach ($incoming_list as $inc) {
                        $total_received += $inc['kg_recive'];
                    }
                }

                $status = 'Open';
                if ($item['close_po'] == 'Y') {
                    $status = 'Closed';
                }

                $balance = $item['qty_po'] - $total_received;

                if ($count_inc > 0) :
                    for ($i = 0; $i < $count_inc; $i++) :
                        $tgl_inc = (!empty($incoming_list[$i]['tgl_datang']) && $incoming_list[$i]['tgl_datang'] != '0000-00-00') ? date('d-M-Y', strtotime($incoming_list[$i]['tgl_datang'])) : '-';

                        echo '<tr>';
                        if ($i === 0) :
                            echo '<td' . $rowspan . ' style="text-align: center; vertical-align: top;">' . $no . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: center; vertical-align: top;">' . $item['no_pr'] . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: center; vertical-align: top;">' . $item['no_surat_po'] . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: center; vertical-align: top;">' . date('d-m-Y', strtotime($item['tanggal_po'])) . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: left; vertical-align: top;">' . $item['name_suplier'] . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: left; vertical-align: top;">' . $item['nama_material'] . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: right; vertical-align: top;">' . number_format($item['width_po'], 2) . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: right; vertical-align: top;">' . number_format($item['qty_po'], 2) . '</td>';
                        endif;

                        echo '<td style="text-align: center;">' . $tgl_inc . '</td>';
                        echo '<td style="text-align: right;">' . number_format($incoming_list[$i]['kg_recive'], 2) . '</td>';

                        if ($i === 0) :
                            echo '<td' . $rowspan . ' style="text-align: right; vertical-align: top;">' . number_format($total_received, 2) . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: right; vertical-align: top;">' . number_format($balance, 2) . '</td>';
                            echo '<td' . $rowspan . ' style="text-align: center; vertical-align: top;">' . $status . '</td>';
                        endif;
                        echo '</tr>';
                    endfor;
                else :
                    echo '<tr>';
                    echo '<td style="text-align: center;">' . $no . '</td>';
                    echo '<td style="text-align: center;">' . $item['no_pr'] . '</td>';
                    echo '<td style="text-align: center;">' . $item['no_surat_po'] . '</td>';
                    echo '<td style="text-align: center;">' . date('d-m-Y', strtotime($item['tanggal_po'])) . '</td>';
                    echo '<td style="text-align: left;">' . $item['name_suplier'] . '</td>';
                    echo '<td style="text-align: left;">' . $item['nama_material'] . '</td>';
                    echo '<td style="text-align: right;">' . number_format($item['width_po'], 2) . '</td>';
                    echo '<td style="text-align: right;">' . number_format($item['qty_po'], 2) . '</td>';
                    echo '<td style="text-align: center;"></td>';
                    echo '<td style="text-align: right;"></td>';
                    echo '<td style="text-align: right;">' . number_format(0, 2) . '</td>';
                    echo '<td style="text-align: right;">' . number_format($item['qty_po'], 2) . '</td>';
                    echo '<td style="text-align: center;">' . $status . '</td>';
                    echo '</tr>';
                endif;
            endforeach;
        endif;
        ?>
    </tbody>
</table>
