<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">No.</th>
            <th class="text-center">Nama Barang</th>
            <th class="text-center">Thickness</th>
            <th class="text-center">Width</th>
            <th class="text-center">Length</th>
            <th class="text-center">Lot No.</th>
            <?php
            if ($type_sheet > 0) {
                echo '
                        <th class="text-center">Qty DO (Kg)</th>
                        <th class="text-center">Qty DO (Sheet)</th>
                        <th class="text-center">Qty OK (Kg)</th>
                        <th class="text-center">Qty OK (Sheet)</th>
                        <th class="text-center">QTY FG (Kg)</th>
                        <th class="text-center">QTY FG (Sheet)</th>
                        <th class="text-center">Qty NG (Kg)</th>
                        <th class="text-center">Qty NG (Sheet)</th>
                    ';
            } else {
                echo '
                    <th class="text-center">Qty DO</th>
                    <th class="text-center">Qty OK</th>
                    <th class="text-center">QTY FG</th>
                    <th class="text-center">Qty NG</th>
                ';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($do_detail as $item) {
            $no++;

            $get_stock = $this->db->get_where('stock_material', ['id_stock' => $item->id_stock])->row();

            $qty_sheet = (!empty($get_stock->qty_sheet)) ? $get_stock->qty_sheet : 0;

            echo '<tr>';

            echo '<td class="text-center">';
            echo '<input type="hidden" name="detail[' . $no . '][id_detail]" value="' . $item->id . '">';
            echo  $no;
            echo '</td>';
            echo '<td class="text-left">' . $item->nm_material . '</td>';
            echo '<td class="text-right">' . number_format($item->thickness, 2) . '</td>';
            echo '<td class="text-right">' . number_format($item->width, 2) . '</td>';
            echo '<td class="text-right">' . number_format($item->length, 2) . '</td>';
            echo '<td class="text-left">' . $item->lotno . '</td>';
            if ($type_sheet > 0) {
                echo '<td class="text-right">';
                echo number_format($item->weight_mat, 2);
                echo '<input type="hidden" name="detail[' . $no . '][qty_do]" value="' . $item->weight_mat . '">';
                echo '</td>';
                echo '<td class="text-right">';
                echo number_format($qty_sheet, 2);
                echo '<input type="hidden" name="detail[' . $no . '][qty_do]" value="' . $qty_sheet . '">';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_in]" value="' . $item->qty_in . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_in_sheet]" value="' . $item->qty_in_sheet . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_fg]" value="' . $item->qty_fg . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_fg_sheet]" value="' . $item->qty_fg_sheet . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_ng]" value="' . $item->qty_ng . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_ng_sheet]" value="' . $item->qty_ng_sheet . '" readonly>';
                echo '</td>';
            } else {
                echo '<td class="text-right">';
                echo number_format($item->weight_mat, 2);
                echo '<input type="hidden" name="detail[' . $no . '][qty_do]" value="' . $item->weight_mat . '">';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_in]" value="' . $item->qty_in . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_fg]" value="' . $item->qty_fg . '" readonly>';
                echo '</td>';
                echo '<td>';
                echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_ng]" value="' . $item->qty_ng . '" readonly>';
                echo '</td>';
            }

            echo '</tr>';
        }
        ?>
    </tbody>
</table>

<input type="hidden" name="no" value="<?= $no ?>">

<script src="assets/js/autoNumeric.js"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');
    })
</script>