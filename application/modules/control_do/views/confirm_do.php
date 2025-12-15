<input type="hidden" name="id_do" value="<?= $do_header->id_delivery_order ?>">
<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">No.</th>
            <th class="text-center">Nama Barang</th>
            <th class="text-center">Thickness</th>
            <th class="text-center">Width</th>
            <th class="text-center">Length</th>
            <th class="text-center">Lot No.</th>
            <th class="text-center">Qty DO</th>
            <th class="text-center">Qty Terima</th>
            <th class="text-center">Qty NG</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($do_detail as $item) {
            $no++;

            echo '<tr>';

            echo '<td class="text-center">';
            echo '<input type="hidden" name="detail[' . $no . '][id_detail]" value="' . $item->id . '">';
            echo  $no;
            echo '</td>';
            echo '<td class="text-left">' . $item->nm_material . '</td>';
            echo '<td class="text-right">' . number_format($item->thickness) . '</td>';
            echo '<td class="text-right">' . number_format($item->width) . '</td>';
            echo '<td class="text-right">' . number_format($item->length) . '</td>';
            echo '<td class="text-left">' . $item->lotno . '</td>';
            echo '<td class="text-right">' . number_format($item->weight_mat, 2) . '</td>';
            echo '<td>';
            echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_in]" value="' . $item->weight_mat . '">';
            echo '</td>';
            echo '<td>';
            echo '<input type="text" class="form-control form-control-sm auto_num" name="detail[' . $no . '][qty_ng]" value="0">';
            echo '</td>';

            echo '</tr>';
        }
        ?>
    </tbody>
</table>

<script src="assets/js/autoNumeric.js"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');
    })
</script>