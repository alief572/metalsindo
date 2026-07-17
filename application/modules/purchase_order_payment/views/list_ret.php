<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">
<div class="req_payment_dp" style="margin-top: 2vh;">
    <b>Receive Invoice Retensi</b>
    <div class="row">
        <div class="col-md-4" style="margin-top: 20px;">
            <label for="">Supplier</label>
            <select name="supplier" id="select_supplier" class="form-control">
                <option value="">- Pilih Supplier -</option>
                <?php
                foreach ($list_supplier as $item_supp) {
                    echo '<option value="' . $item_supp->id_suplier . '">' . $item_supp->name_suplier . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-2" style="margin-top: 20px;">
            <button type="button" class="btn btn-sm btn-primary search_ret" style="margin-top: 20px;">
                <i class="fa fa-search"></i> Cari
            </button>
        </div>
    </div>
    <div class="col_table">
        <table class="table table-bordered table_req_pay_ret">
            <thead class="bg-red">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">No. Purchase Invoice</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">No. Payment</th>
                    <th class="text-center">Nama Supplier</th>
                    <th class="text-center">Tanggal PO</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script>
    var table_ret;
    $(document).ready(function() {
        table_ret = $('.table_req_pay_ret').DataTable({
            ajax: {
                url: siteurl + active_controller + "search_ret",
                type: "POST",
                data: function(d) {
                    d.kode_supplier = $('#select_supplier').val();
                }
            },
            columns: [
                { data: 'no' },
                { data: 'no_po' },
                { data: 'no_purchase_invoice' },
                { data: 'no_invoice' },
                { data: 'no_payment' },
                { data: 'nm_supplier' },
                { data: 'tanggal' },
                { data: 'keterangan' },
                { data: 'status' },
                { data: 'action' }
            ]
        });

        $('#select_supplier').chosen();
    });

    $(document).on('click', '.search_ret', function() {
        table_ret.ajax.reload();
    });
</script>