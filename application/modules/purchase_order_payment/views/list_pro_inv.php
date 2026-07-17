<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered table-striped">
            <thead class="bg-blue">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor Invoice</th>
                    <th class="text-center">Tanggal Invoice</th>
                    <th class="text-center">Persentase</th>
                    <th class="text-center">Nominal (Value DP)</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($list_invoices as $item) {
                    echo "<tr>";
                    echo "<td class='text-center'>" . $no++ . "</td>";
                    echo "<td class='text-center'>" . $item->invoice_no . "</td>";
                    echo "<td class='text-center'>" . date('d F Y', strtotime($item->invoice_date)) . "</td>";
                    echo "<td class='text-center'>" . number_format($item->persen_dp, 2) . "%</td>";
                    echo "<td class='text-right'>" . number_format($item->value_dp, 2) . "</td>";
                    echo "<td class='text-center'>
                            <button type='button' class='btn btn-sm btn-info view_pro_detail' data-id='" . $item->id . "' data-tipe='" . $tipe . "' title='View Detail'><i class='fa fa-eye'></i></button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
