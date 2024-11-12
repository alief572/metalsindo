<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post">
            <div class="form-group row">
                <table class="table table-bordered" width="100%" id="list_item_stokk">
                    <thead>
                        <tr>
                            <th class="text-center">No. Incoming</th>
                            <th class="text-center">No. PO</th>
                            <th class="text-center">Nama Supplier</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 0;
                        foreach ($results as $item) {

                            $no++;

                            $no_po = (!empty($list_no_po[$item->id_incoming])) ? $list_no_po[$item->id_incoming] : '';

                            $total_incoming = 0;

                            $this->db->select('b.width_recive, a.hargasatuan');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('dt_incoming b', 'b.id_dt_po = a.id_dt_po AND b.id_material = a.idmaterial');
                            $this->db->where('b.id_incoming', $item->id_incoming);
                            $get_total_incoming = $this->db->get()->result();

                            foreach ($get_total_incoming as $item_incoming) {
                                $total_incoming += ($item_incoming->hargasatuan * $item_incoming->width_recive);
                            }

                            echo '<tr>';

                            echo '<td class="text-center">' . $item->id_incoming . '</td>';
                            echo '<td class="text-center">' . $no_po . '</td>';
                            echo '<td class="text-center">' . $item->name_suplier . '</td>';
                            echo '<td class="text-right">' . number_format($total_incoming, 2) . '</td>';
                            echo '<td class="text-center">';
                            echo '<button type="button" class="btn btn-sm btn-warning add_incoming add_incoming_' . $no . '" data-id_incoming="' . $item->id_incoming . '" data-no_po="' . $no_po . '" data-id_suplier="'.$item->id_suplier.'" data-name_suplier="' . $item->name_suplier . '" data-nilai="' . $total_incoming . '" data-no="' . $no . '">';
                            echo '<i class="fa fa-plus"></i> Add';
                            echo '</button>';
                            echo '</td>';

                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#list_item_stokk').DataTable();
    });
</script>