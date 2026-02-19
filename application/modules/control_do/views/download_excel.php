<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Control DO.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<h2>Control DO (<?= date('d F Y') ?>)</h2>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal DO</th>
            <th>No DO</th>
            <th>No SPK Marketing</th>
            <th>Customer</th>
            <th>Qty Order</th>
            <th>Qty Delivery</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($data_control_do as $do) {

            $this->db->select('SUM(a.weight_mat) as total_do, SUM(a.qty_in) as total_delivered');
            $this->db->from('dt_delivery_order_child a');
            $this->db->where('a.id_delivery_order', $do->id_delivery_order);
            $get_total = $this->db->get()->row();

            $total_do = (!empty($arr_total_do[$do->id_delivery_order])) ? $arr_total_do[$do->id_delivery_order] : 0;
            $total_delivered = (!empty($arr_total_delivered[$do->id_delivery_order])) ? $arr_total_delivered[$do->id_delivery_order] : 0;

            echo '<tr>';
            echo '<td>' . $no . '</td>';
            echo '<td>' . $do->tgl_delivery_order . '</td>';
            echo '<td>' . $do->no_surat . '</td>';
            echo '<td>' . $do->no_spk_marketing . '</td>';
            echo '<td>' . $do->name_customer . '</td>';
            echo '<td style="text-align:right;">' . number_format($total_do, 2) . '</td>';
            echo '<td style="text-align:right;">' . number_format($total_delivered, 2) . '</td>';
            echo '<td style="text-align:right;">' . number_format($total_do - $total_delivered, 2) . '</td>';
            echo '</tr>';
            $no++;
        }
        ?>
    </tbody>
</table>