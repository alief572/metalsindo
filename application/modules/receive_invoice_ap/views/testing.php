<?php
$no = 0;
$total = 0;
foreach ($detail as $item_detail) :
    $no++;

    $harga_per_sheet = ($item_detail->hargasatuan * $item_detail->total_weight);

    echo $no . '. ' . $item_detail->lotno . '-' . $item_detail->nama . ' - ' . $item_detail->qty_sheet . ' - ' . $harga_per_sheet . ' - ' . ($item_detail->qty_sheet * $harga_per_sheet) . '<br>';

    $total += ($item_detail->qty_sheet * $harga_per_sheet);
endforeach;

echo "TOTAL : Rp. " . number_format($total, 2);
