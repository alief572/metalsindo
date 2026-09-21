<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_outstanding_so_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get list of customers for dropdown filter
     */
    public function get_customers()
    {
        $this->db->select('id_customer, name_customer');
        $this->db->from('master_customers');
        $this->db->where('deleted', 0);
        $this->db->order_by('name_customer', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Base SQL query string for aggregated SPK and DO
     */
    private function _build_base_query($customer_id = '', $status_filter = '', $start_date = '', $end_date = '', $search = '')
    {
        $sql = "
            SELECT 
                spk.id_spkmarketing,
                spk.no_surat AS no_spk,
                spk.id_customer,
                c.name_customer,
                spk.tgl_spk_marketing,
                DATEDIFF(CURRENT_DATE(), spk.tgl_spk_marketing) AS umur_hari,
                COALESCE(spk_agg.total_qty, 0) AS total_qty_spk,
                COALESCE(spk_agg.total_nilai, 0) AS total_nilai_spk,
                COALESCE(do_agg.total_qty_do, 0) AS total_qty_do,
                COALESCE(do_agg.count_do, 0) AS count_do
            FROM tr_spk_marketing spk
            JOIN master_customers c ON c.id_customer = spk.id_customer
            LEFT JOIN (
                SELECT id_spkmarketing, 
                       SUM(COALESCE(qty_produk, 0)) AS total_qty, 
                       SUM(COALESCE(total_harga, 0)) AS total_nilai
                FROM dt_spkmarketing
                GROUP BY id_spkmarketing
            ) spk_agg ON spk_agg.id_spkmarketing = spk.id_spkmarketing
            LEFT JOIN (
                SELECT 
                    `do`.no_spk_marketing,
                    COUNT(DISTINCT `do`.id_delivery_order) AS count_do,
                    SUM(COALESCE(CASE WHEN doc.qty_in > 0 THEN doc.qty_in ELSE doc.weight_mat END, 0)) AS total_qty_do
                FROM tr_delivery_order `do`
                JOIN dt_delivery_order_child doc ON doc.id_delivery_order = `do`.id_delivery_order
                WHERE `do`.status_approve = '1' 
                  AND (`do`.deleted IS NULL OR `do`.deleted = '0')
                GROUP BY `do`.no_spk_marketing
            ) do_agg ON do_agg.no_spk_marketing = spk.no_surat
            WHERE spk.status_approve = '1' 
              AND (spk.deleted IS NULL OR spk.deleted = '0')
        ";

        if (!empty($customer_id)) {
            $customer_id_clean = $this->db->escape_str($customer_id);
            $sql .= " AND spk.id_customer = '{$customer_id_clean}' ";
        }

        if (!empty($start_date)) {
            $start_clean = $this->db->escape_str($start_date);
            $sql .= " AND spk.tgl_spk_marketing >= '{$start_clean}' ";
        }

        if (!empty($end_date)) {
            $end_clean = $this->db->escape_str($end_date);
            $sql .= " AND spk.tgl_spk_marketing <= '{$end_clean}' ";
        }

        if (!empty($search)) {
            $search_clean = $this->db->escape_str($search);
            $sql .= " AND (spk.no_surat LIKE '%{$search_clean}%' OR c.name_customer LIKE '%{$search_clean}%') ";
        }

        // Status wrapping subquery if status filter is applied
        if (!empty($status_filter)) {
            $status_clean = strtolower($status_filter);
            $wrapped = "SELECT * FROM ({$sql}) AS sub WHERE 1=1 ";
            if ($status_clean == 'belum dikirim' || $status_clean == 'belum_dikirim') {
                $wrapped .= " AND sub.total_qty_do <= 0 ";
            } elseif ($status_clean == 'kirim sebagian' || $status_clean == 'kirim_sebagian') {
                $wrapped .= " AND sub.total_qty_do > 0 AND (sub.total_qty_spk - sub.total_qty_do) > 0 ";
            } elseif ($status_clean == 'selesai kirim' || $status_clean == 'selesai_kirim') {
                $wrapped .= " AND sub.total_qty_do >= sub.total_qty_spk AND sub.total_qty_spk > 0 ";
            }
            return $wrapped;
        }

        return $sql;
    }

    /**
     * Get summary KPI card numbers
     */
    public function get_kpi_summary($customer_id = '', $status_filter = '', $start_date = '', $end_date = '', $search = '')
    {
        $base_sql = $this->_build_base_query($customer_id, $status_filter, $start_date, $end_date, $search);
        
        $kpi_query = "
            SELECT 
                COUNT(*) AS total_so,
                SUM(
                    CASE 
                        WHEN sub.total_qty_do <= 0 THEN sub.total_nilai_spk
                        WHEN sub.total_qty_do < sub.total_qty_spk AND (sub.total_qty_spk - sub.total_qty_do) > 0 THEN 
                            ((sub.total_qty_spk - sub.total_qty_do) / sub.total_qty_spk) * sub.total_nilai_spk
                        ELSE 0 
                    END
                ) AS total_nilai_outstanding,
                SUM(CASE WHEN sub.total_qty_do <= 0 THEN 1 ELSE 0 END) AS total_belum_dikirim,
                SUM(CASE WHEN sub.total_qty_do > 0 AND (sub.total_qty_spk - sub.total_qty_do) > 0 THEN 1 ELSE 0 END) AS total_kirim_sebagian,
                SUM(CASE WHEN sub.total_qty_do >= sub.total_qty_spk AND sub.total_qty_spk > 0 THEN 1 ELSE 0 END) AS total_selesai_kirim
            FROM ({$base_sql}) AS sub
        ";

        $res = $this->db->query($kpi_query)->row_array();
        return [
            'total_so' => isset($res['total_so']) ? (int)$res['total_so'] : 0,
            'total_nilai_outstanding' => isset($res['total_nilai_outstanding']) ? (float)$res['total_nilai_outstanding'] : 0,
            'total_belum_dikirim' => isset($res['total_belum_dikirim']) ? (int)$res['total_belum_dikirim'] : 0,
            'total_kirim_sebagian' => isset($res['total_kirim_sebagian']) ? (int)$res['total_kirim_sebagian'] : 0,
            'total_selesai_kirim' => isset($res['total_selesai_kirim']) ? (int)$res['total_selesai_kirim'] : 0
        ];
    }

    /**
     * Get paginated DataTables data
     */
    public function get_datatables_data($customer_id = '', $status_filter = '', $start_date = '', $end_date = '', $search = '', $start = 0, $length = 10, $order_col = 'tgl_spk_marketing', $order_dir = 'desc')
    {
        $base_sql = $this->_build_base_query($customer_id, $status_filter, $start_date, $end_date, $search);

        // Count total filtered
        $count_sql = "SELECT COUNT(*) AS total_filtered FROM ({$base_sql}) AS sub";
        $count_res = $this->db->query($count_sql)->row_array();
        $recordsFiltered = isset($count_res['total_filtered']) ? (int)$count_res['total_filtered'] : 0;

        // Count all without search / filters
        $count_all_sql = "
            SELECT COUNT(*) AS total_all 
            FROM tr_spk_marketing 
            WHERE status_approve = '1' AND (deleted IS NULL OR deleted = '0')
        ";
        $count_all_res = $this->db->query($count_all_sql)->row_array();
        $recordsTotal = isset($count_all_res['total_all']) ? (int)$count_all_res['total_all'] : 0;

        // Data query with ordering and limit
        $safe_order_dir = (strtolower($order_dir) === 'asc') ? 'ASC' : 'DESC';
        $valid_cols = [
            'no_spk' => 'sub.no_spk',
            'name_customer' => 'sub.name_customer',
            'tgl_spk_marketing' => 'sub.tgl_spk_marketing',
            'sisa_qty' => '(sub.total_qty_spk - sub.total_qty_do)',
            'umur_hari' => 'sub.umur_hari'
        ];
        $order_by = isset($valid_cols[$order_col]) ? $valid_cols[$order_col] : 'sub.tgl_spk_marketing';

        $data_sql = "
            SELECT sub.* 
            FROM ({$base_sql}) AS sub 
            ORDER BY {$order_by} {$safe_order_dir} 
            LIMIT " . (int)$start . ", " . (int)$length . "
        ";

        $rows = $this->db->query($data_sql)->result_array();
        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows
        ];
    }

    /**
     * Get DO list detail for a specific SO (SPK Marketing)
     */
    public function get_detail_do($no_spk)
    {
        $no_spk_clean = $this->db->escape_str($no_spk);
        $sql = "
            SELECT 
                `do`.id_delivery_order,
                `do`.no_surat AS no_do,
                `do`.tgl_delivery_order,
                `do`.status_approve,
                COALESCE(SUM(CASE WHEN doc.qty_in > 0 THEN doc.qty_in ELSE doc.weight_mat END), 0) AS qty_kirim,
                CASE 
                    WHEN `do`.status_approve = '1' THEN 'Terkirim'
                    ELSE 'Menunggu Approval'
                END AS status_do
            FROM tr_delivery_order `do`
            JOIN dt_delivery_order_child doc ON doc.id_delivery_order = `do`.id_delivery_order
            WHERE `do`.no_spk_marketing = '{$no_spk_clean}'
              AND (`do`.deleted IS NULL OR `do`.deleted = '0')
            GROUP BY `do`.id_delivery_order
            ORDER BY `do`.tgl_delivery_order ASC
        ";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get all data for Excel Export
     */
    public function get_export_data($customer_id = '', $status_filter = '', $start_date = '', $end_date = '', $search = '')
    {
        $base_sql = $this->_build_base_query($customer_id, $status_filter, $start_date, $end_date, $search);
        $data_sql = "
            SELECT sub.* 
            FROM ({$base_sql}) AS sub 
            ORDER BY sub.tgl_spk_marketing DESC
        ";
        $so_list = $this->db->query($data_sql)->result_array();

        // Attach DOs for each SO
        foreach ($so_list as &$item) {
            $sisa_qty = max(0, $item['total_qty_spk'] - $item['total_qty_do']);
            if ($item['total_qty_do'] <= 0) {
                $status = 'Belum dikirim';
                $nilai_out = (float)$item['total_nilai_spk'];
            } elseif ($item['total_qty_do'] < $item['total_qty_spk'] && $sisa_qty > 0) {
                $status = 'Kirim sebagian';
                $nilai_out = ($item['total_qty_spk'] > 0) ? ($sisa_qty / $item['total_qty_spk']) * (float)$item['total_nilai_spk'] : 0;
            } else {
                $status = 'Selesai kirim';
                $sisa_qty = 0;
                $nilai_out = 0;
            }
            $item['calc_status'] = $status;
            $item['calc_sisa_qty'] = $sisa_qty;
            $item['calc_nilai_outstanding'] = $nilai_out;
            $item['delivery_orders'] = $this->get_detail_do($item['no_spk']);
        }
        unset($item);

        return $so_list;
    }
}
