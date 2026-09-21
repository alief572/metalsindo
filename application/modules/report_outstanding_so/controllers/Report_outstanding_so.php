<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_outstanding_so extends Admin_Controller
{
    protected $viewPermission = 'Report_Outstanding_SO.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_outstanding_so/Report_outstanding_so_model', 'outstanding_model');
        $this->template->title('Report Outstanding SO');
        $this->template->page_icon('fa fa-bar-chart');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $customers = $this->outstanding_model->get_customers();
        $data = [
            'customers' => $customers,
            'current_date' => date('d F Y')
        ];

        $this->template->set($data);
        $this->template->render('index');
    }

    /**
     * AJAX endpoint for DataTables server-side processing
     */
    public function get_data()
    {
        $this->auth->restrict($this->viewPermission);

        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length <= 0) $length = 10;

        $customer_id   = $this->input->post('customer_id');
        $status_filter = $this->input->post('status_filter');
        $start_date    = $this->input->post('start_date');
        $end_date      = $this->input->post('end_date');
        
        $search = $this->input->post('search');
        $search_val = isset($search['value']) ? trim($search['value']) : '';

        $order = $this->input->post('order');
        $order_col_idx = isset($order[0]['column']) ? intval($order[0]['column']) : 3;
        $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

        $col_map = [
            1 => 'no_spk',
            2 => 'name_customer',
            3 => 'tgl_spk_marketing',
            4 => 'sisa_qty',
            5 => 'total_nilai_spk',
            6 => 'umur_hari'
        ];
        $order_col = isset($col_map[$order_col_idx]) ? $col_map[$order_col_idx] : 'tgl_spk_marketing';

        $res = $this->outstanding_model->get_datatables_data(
            $customer_id, 
            $status_filter, 
            $start_date, 
            $end_date, 
            $search_val, 
            $start, 
            $length, 
            $order_col, 
            $order_dir
        );

        $data_rows = [];
        foreach ($res['data'] as $row) {
            $total_qty_spk = (float)$row['total_qty_spk'];
            $total_qty_do  = (float)$row['total_qty_do'];
            $total_nilai   = (float)$row['total_nilai_spk'];

            $sisa_qty = max(0, $total_qty_spk - $total_qty_do);

            if ($total_qty_do <= 0) {
                $status_label = '<span class="label label-danger" style="font-size: 11px; padding: 4px 8px;">Belum dikirim</span>';
                $nilai_out = $total_nilai;
            } elseif ($total_qty_do < $total_qty_spk && $sisa_qty > 0) {
                $status_label = '<span class="label label-warning" style="font-size: 11px; padding: 4px 8px; background-color: #d97706 !important;">Kirim sebagian</span>';
                $nilai_out = ($total_qty_spk > 0) ? ($sisa_qty / $total_qty_spk) * $total_nilai : 0;
            } else {
                $status_label = '<span class="label label-success" style="font-size: 11px; padding: 4px 8px;">Selesai kirim</span>';
                $sisa_qty = 0;
                $nilai_out = 0;
            }

            $expand_btn = '<button type="button" class="btn btn-xs btn-default btn-expand" data-no_spk="' . htmlspecialchars($row['no_spk']) . '" title="Lihat detail DO">'
                        . '<i class="fa fa-chevron-right text-primary"></i>'
                        . '</button>';

            $data_rows[] = [
                'expand' => $expand_btn,
                'no_spk' => '<strong>' . htmlspecialchars($row['no_spk']) . '</strong>',
                'customer' => htmlspecialchars($row['name_customer']),
                'tgl_spk' => date('d/m/y', strtotime($row['tgl_spk_marketing'])),
                'sisa_qty' => number_format($sisa_qty, 2, ',', '.'),
                'nilai_outstanding' => 'Rp ' . number_format($nilai_out, 0, ',', '.'),
                'umur_hari' => $row['umur_hari'],
                'status' => $status_label,
                'raw_no_spk' => $row['no_spk']
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $res['recordsTotal'],
            'recordsFiltered' => $res['recordsFiltered'],
            'data' => $data_rows
        ]);
    }

    /**
     * AJAX endpoint for KPI summary counters
     */
    public function get_summary()
    {
        $this->auth->restrict($this->viewPermission);

        $customer_id   = $this->input->post('customer_id');
        $status_filter = $this->input->post('status_filter');
        $start_date    = $this->input->post('start_date');
        $end_date      = $this->input->post('end_date');
        $search_val    = $this->input->post('search');

        $kpi = $this->outstanding_model->get_kpi_summary($customer_id, $status_filter, $start_date, $end_date, $search_val);

        // Format Nilai Outstanding: e.g. Rp 1,86 M or Rp 1.860.000.000
        $val = $kpi['total_nilai_outstanding'];
        if ($val >= 1000000000) {
            $formatted_val = 'Rp ' . number_format($val / 1000000000, 2, ',', '.') . ' M';
        } elseif ($val >= 1000000) {
            $formatted_val = 'Rp ' . number_format($val / 1000000, 2, ',', '.') . ' Jt';
        } else {
            $formatted_val = 'Rp ' . number_format($val, 0, ',', '.');
        }

        echo json_encode([
            'status' => 1,
            'total_so' => number_format($kpi['total_so']),
            'total_nilai_formatted' => $formatted_val,
            'total_nilai_exact' => 'Rp ' . number_format($val, 0, ',', '.'),
            'total_belum_dikirim' => number_format($kpi['total_belum_dikirim']),
            'total_kirim_sebagian' => number_format($kpi['total_kirim_sebagian']),
            'total_selesai_kirim' => number_format($kpi['total_selesai_kirim'])
        ]);
    }

    /**
     * AJAX endpoint to fetch DO details for child row expansion
     */
    public function get_detail_do()
    {
        $this->auth->restrict($this->viewPermission);

        $no_spk = $this->input->post('no_spk');
        if (empty($no_spk)) {
            echo '<div class="alert alert-danger">Nomor SPK tidak valid.</div>';
            return;
        }

        $dos = $this->outstanding_model->get_detail_do($no_spk);

        $html = '<div style="padding: 10px 20px; background-color: #f8f9fa; border-left: 4px solid #3c8dbc;">';
        $html .= '<h5 style="margin-top: 0; font-weight: bold; color: #333;"><i class="fa fa-truck"></i> Delivery order untuk ' . htmlspecialchars($no_spk) . '</h5>';

        if (empty($dos)) {
            $html .= '<p class="text-muted" style="margin-bottom: 5px;"><em>Belum ada Delivery Order yang diterbitkan untuk SPK ini.</em></p>';
        } else {
            $html .= '<table class="table table-condensed table-bordered" style="background-color: #fff; margin-bottom: 5px;">';
            $html .= '<thead><tr style="background-color: #eef2f5;">'
                  . '<th style="width: 30px;">#</th>'
                  . '<th>No DO</th>'
                  . '<th>Tgl Kirim</th>'
                  . '<th class="text-right">Qty Terkirim</th>'
                  . '<th class="text-center">Status</th>'
                  . '</tr></thead><tbody>';

            $no = 1;
            $total_qty_do = 0;
            foreach ($dos as $do) {
                $qty = (float)$do['qty_kirim'];
                $total_qty_do += $qty;
                $status_badge = ($do['status_approve'] == '1')
                    ? '<span class="label label-success">Terkirim</span>'
                    : '<span class="label label-warning">Draft</span>';

                $html .= '<tr>'
                      . '<td>' . $no++ . '</td>'
                      . '<td><strong>' . htmlspecialchars($do['no_do']) . '</strong></td>'
                      . '<td>' . date('d/m/Y', strtotime($do['tgl_delivery_order'])) . '</td>'
                      . '<td class="text-right">' . number_format($qty, 2, ',', '.') . '</td>'
                      . '<td class="text-center">' . $status_badge . '</td>'
                      . '</tr>';
            }

            $html .= '<tr style="font-weight: bold; background-color: #fafafa;">'
                  . '<td colspan="3" class="text-right">Total Terkirim:</td>'
                  . '<td class="text-right">' . number_format($total_qty_do, 2, ',', '.') . '</td>'
                  . '<td></td>'
                  . '</tr>';

            $html .= '</tbody></table>';
        }
        $html .= '</div>';

        echo $html;
    }

    /**
     * Export report data to Excel (.xls / .xlsx compatibility)
     */
    public function export_excel()
    {
        $this->auth->restrict($this->viewPermission);

        $customer_id   = $this->input->get('customer_id');
        $status_filter = $this->input->get('status_filter');
        $start_date    = $this->input->get('start_date');
        $end_date      = $this->input->get('end_date');
        $search_val    = $this->input->get('search');

        $so_list = $this->outstanding_model->get_export_data($customer_id, $status_filter, $start_date, $end_date, $search_val);
        $kpi = $this->outstanding_model->get_kpi_summary($customer_id, $status_filter, $start_date, $end_date, $search_val);

        $filename = "Report_Outstanding_SO_" . date('Ymd_His') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; }
                .title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
                .subtitle { font-size: 11px; color: #555; margin-bottom: 15px; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
                th { background-color: #2c3e50; color: #ffffff; padding: 6px; border: 1px solid #95a5a6; font-size: 11px; }
                td { padding: 5px; border: 1px solid #bdc3c7; font-size: 11px; }
                .kpi-table th { background-color: #f2f4f6; color: #333; text-align: left; }
                .kpi-table td { font-weight: bold; }
                .header-so { background-color: #ecf0f1; font-weight: bold; }
                .do-row td { background-color: #ffffff; color: #444; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
            </style>
        </head>
        <body>
            <div class="title">REPORT OUTSTANDING SALES ORDER (SPK MARKETING)</div>
            <div class="subtitle">Per Tanggal: <?= date('d F Y') ?></div>

            <table class="kpi-table" style="width: 70%;">
                <tr>
                    <th width="20%">Total SO Outstanding</th>
                    <td width="30%"><?= number_format($kpi['total_so']) ?></td>
                    <th width="20%">Total Nilai Outstanding</th>
                    <td width="30%">Rp <?= number_format($kpi['total_nilai_outstanding'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>Belum Dikirim</th>
                    <td style="color: #c0392b;"><?= number_format($kpi['total_belum_dikirim']) ?></td>
                    <th>Kirim Sebagian</th>
                    <td style="color: #d35400;"><?= number_format($kpi['total_kirim_sebagian']) ?></td>
                </tr>
                <tr>
                    <th>Selesai Kirim</th>
                    <td style="color: #27ae60;"><?= number_format($kpi['total_selesai_kirim']) ?></td>
                    <td colspan="2"></td>
                </tr>
            </table>

            <br>
            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="18%">No SO / SPK</th>
                        <th width="24%">Customer</th>
                        <th width="10%">Tgl SO</th>
                        <th width="10%">Sisa Qty</th>
                        <th width="14%">Nilai Outstanding</th>
                        <th width="8%">Umur (Hari)</th>
                        <th width="12%">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (empty($so_list)) {
                    echo '<tr><td colspan="8" class="text-center">Tidak ada data ditemukan.</td></tr>';
                } else {
                    $no = 1;
                    foreach ($so_list as $so) {
                        ?>
                        <tr class="header-so">
                            <td class="text-center"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($so['no_spk']) ?></strong></td>
                            <td><?= htmlspecialchars($so['name_customer']) ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($so['tgl_spk_marketing'])) ?></td>
                            <td class="text-right"><?= number_format($so['calc_sisa_qty'], 2, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format($so['calc_nilai_outstanding'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= $so['umur_hari'] ?></td>
                            <td class="text-center"><?= $so['calc_status'] ?></td>
                        </tr>
                        <?php
                        if (!empty($so['delivery_orders'])) {
                            ?>
                            <tr>
                                <td></td>
                                <td colspan="7" style="padding-left: 20px; background-color: #fafbfc;">
                                    <table style="margin-top: 5px; margin-bottom: 5px;">
                                        <thead>
                                            <tr style="background-color: #7f8c8d;">
                                                <th style="background-color: #7f8c8d;" width="5%">#</th>
                                                <th style="background-color: #7f8c8d;" width="35%">No Delivery Order</th>
                                                <th style="background-color: #7f8c8d;" width="25%">Tgl Kirim</th>
                                                <th style="background-color: #7f8c8d;" width="20%">Qty Kirim</th>
                                                <th style="background-color: #7f8c8d;" width="15%">Status DO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $d_idx = 1;
                                        foreach ($so['delivery_orders'] as $do) {
                                            ?>
                                            <tr class="do-row">
                                                <td class="text-center"><?= $d_idx++ ?></td>
                                                <td><?= htmlspecialchars($do['no_do']) ?></td>
                                                <td class="text-center"><?= date('d/m/Y', strtotime($do['tgl_delivery_order'])) ?></td>
                                                <td class="text-right"><?= number_format($do['qty_kirim'], 2, ',', '.') ?></td>
                                                <td class="text-center"><?= $do['status_do'] ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
    }
}
