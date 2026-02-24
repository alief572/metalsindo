<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
<div class="box box-primary">
  <div class="box-header">
    <h3 class="box-title"><b>HISTORY <?= get_name('warehouse', 'nm_gudang', 'id', $gudang); ?></b></h3><br>
    <h3 class="box-title" style="color:#c85b0e;"><b><?= strtoupper(get_name('ms_inventory_category3', 'nama', 'id_category3', $material)); ?></b></h3>
    <br>
    <a href="kartu_stock/export_excel/<?= $material ?>/<?= $gudang ?>" class="btn btn-sm btn-success">
      <i class="fa fa-download"></i> Download Excel
    </a>
  </div>
  <div class="box-body tableFixHead" style="height:500px;">
    <table class="table table-striped table-bordered table-hover table-condensed" id="table_history" width="100%">
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
          if ($item['qty_stock_awal'] > $item['qty_stock_akhir']) {
            $qty_transaksi = ($item['jumlah_mat'] * -1);
          }

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
                <td class="text-center">' . number_format($qty_stock_awal, 2) . '</td>
                <td class="text-center">' . number_format($qty_booking_awal, 2) . '</td>
                <td class="text-center">' . number_format($qty_free_stock_awal, 2) . '</td>
                <td class="text-center">' . number_format($qty_transaksi, 2) . '</td>
                <td class="text-center">' . number_format(0, 2) . '</td>
                <td class="text-center">' . number_format($qty_stock_akhir, 2) . '</td>
                <td class="text-center">' . number_format($qty_booking_akhir, 2) . '</td>
                <td class="text-center">' . number_format($qty_free_stock_akhir, 2) . '</td>
              </tr>
            ';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
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
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script>
  $(document).ready(function() {
    $('#table_history').dataTable();
  })
</script>