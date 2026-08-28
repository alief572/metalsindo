<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post">
            <input type="hidden" id="id_suplier_modal" value="<?= $id_suplier ?>">
            <div class="form-group row">
                <table class="table table-bordered" width="100%" id="list_item_stokk">
                    <thead>
                        <tr>
                            <th class="text-center">No. Incoming</th>
                            <th class="text-center">No. PO</th>
                            <th class="text-center">No. SJ Supplier</th>
                            <th class="text-center">Tgl Incoming</th>
                            <th class="text-center">Nama Supplier</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#list_item_stokk').DataTable({
            "processing": true,
            "serverSide": true,
            "searchDelay": 500,
            "ajax": {
                "url": siteurl + active_controller + 'server_side_request',
                "type": "POST",
                "data": function(d) {
                    d.id_suplier = $('#id_suplier_modal').val();
                }
            },
            "columns": [{
                    "data": 0
                },
                {
                    "data": 1
                },
                {
                    "data": 2
                },
                {
                    "data": 3
                },
                {
                    "data": 4
                },
                {
                    "data": 5
                },
                {
                    "data": 6,
                    "orderable": false
                }
            ]
        });
    });
</script>