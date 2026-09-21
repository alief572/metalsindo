<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css" />

<style type="text/css">
    .kpi-card {
        background: #fff;
        border-radius: 8px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .kpi-card.card-red {
        background: #fff5f5;
        border-color: #feb2b2;
    }
    .kpi-card.card-orange {
        background: #fffaf0;
        border-color: #fbd38d;
    }
    .kpi-card.card-green {
        background: #f0fff4;
        border-color: #9ae6b4;
    }
    .kpi-title {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 26px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .card-red .kpi-title { color: #9b2c2c; }
    .card-red .kpi-value { color: #e53e3e; }
    .card-orange .kpi-title { color: #9c4221; }
    .card-orange .kpi-value { color: #dd6b20; }
    .card-green .kpi-title { color: #22543d; }
    .card-green .kpi-value { color: #38a169; }

    .filter-panel {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
    }

    #table_outstanding_so thead th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 600;
        border-bottom: 2px solid #cbd5e1;
        font-size: 12px;
        text-transform: uppercase;
    }
    #table_outstanding_so tbody td {
        vertical-align: middle;
        font-size: 13px;
    }
    .btn-expand {
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #fff;
    }
    .btn-expand:hover {
        background: #f1f5f9;
    }
    tr.shown .btn-expand {
        background: #e2e8f0;
    }
</style>

<div class="box box-solid">
    <div class="box-body" style="padding: 20px;">
        <!-- Header Section -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-8">
                <h3 style="margin: 0; font-weight: 700; color: #1e293b;">Outstanding Sales Order</h3>
                <p class="text-muted" style="margin-top: 5px; font-size: 13px;">
                    <i class="fa fa-calendar"></i> Per <?= $current_date ?> · klik baris untuk detail DO
                </p>
            </div>
            <div class="col-md-4 text-right">
                <button type="button" id="btn_export_excel" class="btn btn-default btn-md" style="font-weight: 600; border: 1px solid #cbd5e1;">
                    <i class="fa fa-download text-success"></i> &nbsp;Ekspor
                </button>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="filter-panel">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Customer</label>
                    <select id="filter_customer" class="form-control select2" style="width: 100%;">
                        <option value="">Semua customer</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= htmlspecialchars($c['id_customer']) ?>"><?= htmlspecialchars($c['name_customer']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Status Pengiriman</label>
                    <select id="filter_status" class="form-control" style="width: 100%;">
                        <option value="">Semua status</option>
                        <option value="belum_dikirim">Belum dikirim</option>
                        <option value="kirim_sebagian">Kirim sebagian</option>
                        <option value="selesai_kirim">Selesai kirim</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Periode Tgl SO (Mulai - Sampai)</label>
                    <div class="input-group">
                        <input type="date" id="filter_start_date" class="form-control input-sm" title="Tanggal Mulai" />
                        <span class="input-group-addon">-</span>
                        <input type="date" id="filter_end_date" class="form-control input-sm" title="Tanggal Selesai" />
                    </div>
                </div>

                <div class="col-md-2 col-sm-6" style="margin-top: 24px;">
                    <button type="button" id="btn_filter" class="btn btn-primary btn-sm btn-flat" style="border-radius: 4px;">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button type="button" id="btn_reset" class="btn btn-default btn-sm btn-flat" style="border-radius: 4px;" title="Reset Filter">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card">
                    <div class="kpi-title">Total SO Outstanding</div>
                    <div class="kpi-value" id="kpi_total_so">0</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card">
                    <div class="kpi-title">Total Nilai Outstanding</div>
                    <div class="kpi-value" id="kpi_total_nilai" title="">Rp 0</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="kpi-card card-red">
                    <div class="kpi-title">Belum Dikirim</div>
                    <div class="kpi-value" id="kpi_belum_dikirim">0</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="kpi-card card-orange">
                    <div class="kpi-title">Kirim Sebagian</div>
                    <div class="kpi-value" id="kpi_kirim_sebagian">0</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="kpi-card card-green">
                    <div class="kpi-title">Selesai Kirim</div>
                    <div class="kpi-value" id="kpi_selesai_kirim">0</div>
                </div>
            </div>
        </div>

        <!-- Main Data Table -->
        <div class="table-responsive" style="margin-top: 10px;">
            <table id="table_outstanding_so" class="table table-hover table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center"></th>
                        <th>No SO</th>
                        <th>Customer</th>
                        <th style="width: 90px;" class="text-center">Tgl SO</th>
                        <th style="width: 110px;" class="text-right">Sisa Qty</th>
                        <th style="width: 140px;" class="text-right">Nilai Outstanding</th>
                        <th style="width: 90px;" class="text-center">Umur (hari)</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Semua customer',
            allowClear: true
        });

        // Initialize DataTables
        var table = $('#table_outstanding_so').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {
                url: siteurl + 'report_outstanding_so/get_data',
                type: 'POST',
                data: function(d) {
                    d.customer_id   = $('#filter_customer').val();
                    d.status_filter = $('#filter_status').val();
                    d.start_date    = $('#filter_start_date').val();
                    d.end_date      = $('#filter_end_date').val();
                }
            },
            columns: [
                {
                    data: 'expand',
                    orderable: false,
                    className: 'text-center'
                },
                { data: 'no_spk' },
                { data: 'customer' },
                { data: 'tgl_spk', className: 'text-center' },
                { data: 'sisa_qty', className: 'text-right' },
                { data: 'nilai_outstanding', className: 'text-right' },
                { data: 'umur_hari', className: 'text-center' },
                { data: 'status', orderable: false, className: 'text-center' }
            ],
            order: [[3, 'desc']],
            drawCallback: function(settings) {
                // Refresh KPI cards when table data updates
                loadSummary();
            },
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ total entri)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Function to load summary cards
        function loadSummary() {
            var customer_id   = $('#filter_customer').val();
            var status_filter = $('#filter_status').val();
            var start_date    = $('#filter_start_date').val();
            var end_date      = $('#filter_end_date').val();
            var search_val    = table.search();

            $.ajax({
                url: siteurl + 'report_outstanding_so/get_summary',
                type: 'POST',
                dataType: 'json',
                data: {
                    customer_id: customer_id,
                    status_filter: status_filter,
                    start_date: start_date,
                    end_date: end_date,
                    search: search_val
                },
                success: function(res) {
                    if (res) {
                        $('#kpi_total_so').text(res.total_so);
                        $('#kpi_total_nilai').text(res.total_nilai_formatted);
                        $('#kpi_total_nilai').attr('title', res.total_nilai_exact);
                        $('#kpi_belum_dikirim').text(res.total_belum_dikirim);
                        $('#kpi_kirim_sebagian').text(res.total_kirim_sebagian);
                        $('#kpi_selesai_kirim').text(res.total_selesai_kirim);
                    }
                }
            });
        }

        // Child row expansion details
        $('#table_outstanding_so tbody').on('click', '.btn-expand', function(e) {
            e.stopPropagation();
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var no_spk = $(this).data('no_spk');
            var icon = $(this).find('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            } else {
                // Loading placeholder
                row.child('<div style="padding: 10px 20px;"><i class="fa fa-spinner fa-spin"></i> Memuat detail Delivery Order...</div>').show();
                tr.addClass('shown');
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');

                // AJAX fetch DO details
                $.ajax({
                    url: siteurl + 'report_outstanding_so/get_detail_do',
                    type: 'POST',
                    data: { no_spk: no_spk },
                    success: function(html) {
                        row.child(html).show();
                    },
                    error: function() {
                        row.child('<div class="alert alert-danger" style="margin: 10px 20px;">Gagal memuat detail Delivery Order.</div>').show();
                    }
                });
            }
        });

        // Click on entire row to toggle expansion (optional convenient UX)
        $('#table_outstanding_so tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('button').length === 0) {
                $(this).find('.btn-expand').trigger('click');
            }
        });

        // Filter button click
        $('#btn_filter').on('click', function() {
            table.ajax.reload();
        });

        // Enter key on date inputs
        $('#filter_start_date, #filter_end_date').on('keypress', function(e) {
            if (e.which === 13) {
                table.ajax.reload();
            }
        });

        // Filter on change for dropdowns
        $('#filter_customer, #filter_status').on('change', function() {
            table.ajax.reload();
        });

        // Reset filter button
        $('#btn_reset').on('click', function() {
            $('#filter_customer').val('').trigger('change');
            $('#filter_status').val('');
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
            table.search('').draw();
        });

        // Export button click
        $('#btn_export_excel').on('click', function() {
            var customer_id   = $('#filter_customer').val() || '';
            var status_filter = $('#filter_status').val() || '';
            var start_date    = $('#filter_start_date').val() || '';
            var end_date      = $('#filter_end_date').val() || '';
            var search_val    = table.search() || '';

            var url = siteurl + 'report_outstanding_so/export_excel?' + $.param({
                customer_id: customer_id,
                status_filter: status_filter,
                start_date: start_date,
                end_date: end_date,
                search: search_val
            });

            window.open(url, '_blank');
        });
    });
</script>
