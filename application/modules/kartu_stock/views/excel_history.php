<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="HISTORY ' . get_name('ms_warehouse', 'wh_name', 'id', $gudang) . ' - ' . strtoupper(get_name('ms_inventory_category3', 'nama', 'id_category3', $material)) . '.xls"');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HISTORY <?= get_name('ms_warehouse', 'wh_name', 'id', $gudang) ?> - <?= strtoupper(get_name('ms_inventory_category3', 'nama', 'id_category3', $material)) ?></title>
</head>
<style media="screen">
    /* JUST COMMON TABLE STYLES... */
    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .td {
        background: #fff;
        padding: 8px 16px;
    }

    .tableFixHead {
        overflow: auto;
        height: 300px;
        position: sticky;
        top: 0;
    }

    .thead .th {
        position: sticky;
        top: 0;
        z-index: 9999;
        background: #a0a0a0;
    }
</style>

<body>
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title"><b>HISTORY <?= get_name('ms_warehouse', 'wh_name', 'id', $gudang); ?></b></h3><br>
            <h3 class="box-title" style="color:#c85b0e;"><b><?= strtoupper(get_name('ms_inventory_category3', 'nama', 'id_category3', $material)); ?></b></h3>
            <br>
        </div>
        <div class="box-body tableFixHead" style="height:500px;">
            <table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">#</th>
                        <th rowspan="2" class="text-center">Tgl Transaksi</th>
                        <th rowspan="2" class="text-center">No. Transaksi</th>
                        <th rowspan="2" class="text-center">Jenis Transaksi</th>
                        <th rowspan="2" class="text-center">ID Material</th>
                        <th rowspan="2" class="text-center">Material</th>
                        <th colspan="3">Awal</th>
                        <th colspan="2">Transaksi</th>
                        <th colspan="3">Akhir</th>
                    </tr>
                    <tr>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Booking</th>
                        <th class="text-center">Free Stock</th>
                        <th class="text-center">In/Out</th>
                        <th class="text-center">Booking</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Booking</th>
                        <th class="text-center">Free Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;
                    foreach ($data as $item) {
                        $no++;

                        $qty_stock_awal = (!empty($item['qty_stock_awal'])) ? $item['qty_stock_awal'] : 0;
                        $qty_booking_awal = (!empty($item['qty_booking_awal'])) ? $item['qty_booking_awal'] : 0;
                        $qty_free_stock_awal = ($qty_stock_awal - $qty_booking_awal);

                        $qty_transaksi = (!empty($item['jumlah_mat'])) ? $item['jumlah_mat'] : 0;

                        $qty_stock_akhir = (!empty($item['qty_stock_akhir'])) ? $item['qty_stock_akhir'] : 0;
                        $qty_booking_akhir = (!empty($item['qty_booking_akhir'])) ? $item['qty_booking_akhir'] : 0;
                        $qty_free_stock_akhir = ($qty_stock_akhir - $qty_booking_akhir);

                        echo '
                            <tr>
                                <td class="text-center">' . $no . '</td>
                                <td class="text-center">' . date('d F Y', strtotime($item['update_date'])) . '</td>
                                <td class="text-center">' . $item['no_ipp'] . '</td>
                                <td class="text-center">' . $item['jenis_transaksi'] . '</td>
                                <td class="text-center">' . $item['id_material'] . '</td>
                                <td class="text-center">' . $item['nm_material'] . '</td>
                                <td class="text-center">' . ($qty_stock_awal) . '</td>
                                <td class="text-center">' . ($qty_booking_awal) . '</td>
                                <td class="text-center">' . ($qty_free_stock_awal) . '</td>
                                <td class="text-center">' . ($qty_transaksi) . '</td>
                                <td class="text-center">' . (0) . '</td>
                                <td class="text-center">' . ($qty_stock_akhir) . '</td>
                                <td class="text-center">' . ($qty_booking_akhir) . '</td>
                                <td class="text-center">' . ($qty_free_stock_akhir) . '</td>
                            </tr>
                            ';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>