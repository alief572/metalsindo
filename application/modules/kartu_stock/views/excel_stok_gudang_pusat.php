<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Kartu Stock Material - " . $results['data_gudang']->wh_name . " (" . $results['tanggal'] . ").xls");
?>
<div style="width: 100%; text-align: center;">
    <h2>Kartu Stock Material - <?= $results['data_gudang']->wh_name ?> - <?= date('d F Y', strtotime($results['tanggal'])) ?></h2>
</div>
<table width="100%" border="1">
    <thead>
        <tr>
            <th class="text-center">No.</th>
            <th class="text-center">ID Material</th>
            <th class="text-center">Nama Material</th>
            <th class="text-center">Supplier</th>
            <th class="text-center">Stock Unit</th>
            <th class="text-center">Stock Booking</th>
            <th class="text-center">Stock Available</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($results['list_stock'] as $item) {
            $no++;

            echo '
                <tr>
                    <td class="text-center">' . $no . '</td>
                    <td class="text-center">' . $item->id_material . '</td>
                    <td class="text-left">' . $item->nm_material . '</td>
                    <td class="text-left">' . $item->nm_supplier . '</td>
                    <td class="text-right">' . $item->qty_stock . '</td>
                    <td class="text-right">' . $item->qty_booking . '</td>
                    <td class="text-right">' . $item->qty_free . '</td>
                </tr>
            ';
        }
        ?>
    </tbody>
</table>