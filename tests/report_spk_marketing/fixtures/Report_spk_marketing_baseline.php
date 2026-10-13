<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_spk_marketing_baseline extends BF_Model
{
    public static function headers()
    {
        return array('No SPK', 'PO (Customer)', 'Date SPK', 'Date PO', 'Delivery Plan by Customer',
            'No PO', 'No', 'Product/Item', 'Aloy', 'Surface', 'Hard', 'Thick', 'Width', 'Length',
            'Qty (KG)', 'Delivery Date', 'Total Qty Product (KG)');
    }

    public function normalize_filters($input)
    {
        $filters = array('start_date'=>'', 'end_date'=>'', 'customer_id'=>'', 'status'=>'approved', 'search'=>'');
        foreach ($filters as $key => $default) {
            if (isset($input[$key])) {
                if (!is_scalar($input[$key])) throw new InvalidArgumentException('Filter tidak valid.');
                $filters[$key] = trim((string) $input[$key]);
            }
        }
        foreach (array('start_date', 'end_date') as $key) {
            if ($filters[$key] !== '' && !self::valid_date($filters[$key])) {
                throw new InvalidArgumentException('Tanggal harus valid dengan format YYYY-MM-DD.');
            }
        }
        if ($filters['start_date'] !== '' && $filters['end_date'] !== '' && $filters['start_date'] > $filters['end_date']) {
            throw new InvalidArgumentException('Tanggal awal tidak boleh melebihi tanggal akhir.');
        }
        if (!in_array($filters['status'], array('approved', 'unapproved', 'all'), true)) {
            throw new InvalidArgumentException('Status approval tidak valid.');
        }
        return $filters;
    }

    public static function valid_date($value)
    {
        return is_string($value) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $value, $m)
            && (int) $m[1] > 0 && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    private function base_sql()
    {
        // Aggregate once per SPK; never sum quantities after joining lookup tables.
        return " FROM tr_spk_marketing h
            JOIN dt_spkmarketing d ON d.id_spkmarketing = h.id_spkmarketing
            LEFT JOIN master_customers c ON c.id_customer = h.id_customer
            LEFT JOIN child_penawaran p ON p.id_child_penawaran = d.id_child_penawaran
            LEFT JOIN ms_inventory_category3 m ON m.id_category3 = COALESCE(NULLIF(p.id_category3, ''), d.id_material)
            LEFT JOIN ms_inventory_category2 product ON product.id_category2 = m.id_category2
            LEFT JOIN ms_surface surface ON surface.id_surface = m.id_surface
            JOIN (SELECT id_spkmarketing, SUM(COALESCE(qty_produk, 0)) AS total_qty
                  FROM dt_spkmarketing WHERE deal = '1' AND (deleted IS NULL OR deleted = '0')
                  GROUP BY id_spkmarketing) totals ON totals.id_spkmarketing = h.id_spkmarketing
            WHERE (h.deleted IS NULL OR h.deleted = '0')
              AND (d.deleted IS NULL OR d.deleted = '0') AND d.deal = '1'";
    }

    private function filtered_sql($filters, &$bindings)
    {
        $sql = $this->base_sql();
        $bindings = array();
        if ($filters['status'] === 'approved') $sql .= " AND h.status_approve = '1'";
        if ($filters['status'] === 'unapproved') $sql .= " AND (h.status_approve IS NULL OR h.status_approve <> '1')";
        foreach (array('start_date'=>'>=', 'end_date'=>'<=') as $key => $operator) {
            if ($filters[$key] !== '') { $sql .= ' AND h.tgl_spk_marketing '.$operator.' ?'; $bindings[] = $filters[$key]; }
        }
        if ($filters['customer_id'] !== '') { $sql .= ' AND h.id_customer = ?'; $bindings[] = $filters['customer_id']; }
        if ($filters['search'] !== '') {
            // Use a fixed escape character so % and _ in document numbers are literal.
            $term = '%'.str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), $filters['search']).'%';
            $sql .= " AND (h.no_surat LIKE ? ESCAPE '!' OR h.no_po LIKE ? ESCAPE '!')";
            $bindings[] = $term; $bindings[] = $term;
        }
        return $sql;
    }

    public function count_rows($filters)
    {
        $sql = $this->filtered_sql($filters, $bindings);
        $row = $this->db->query('SELECT COUNT(*) AS total'.$sql, $bindings)->row_array();
        return (int) $row['total'];
    }

    public function get_rows($filters, $start = 0, $length = null, $column = 2, $direction = 'desc')
    {
        $columns = array('h.no_surat', 'customer', 'h.tgl_spk_marketing', 'h.tgl_po', 'h.plan_cust', 'h.no_po',
            'item_no', 'product.nama', 'm.spek', 'surface.nm_surface', 'm.hardness', 'd.thickness', 'd.width',
            'd.length', 'd.qty_produk', 'd.delivery', 'totals.total_qty');
        $column = isset($columns[$column]) ? (int) $column : 2;
        $direction = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT h.id_spkmarketing, h.no_surat, COALESCE(NULLIF(c.name_customer, ''), h.nama_customer, '') AS customer,
            h.tgl_spk_marketing, h.tgl_po, h.plan_cust, h.no_po, d.id,
            (SELECT COUNT(*) FROM dt_spkmarketing seq WHERE seq.id_spkmarketing = d.id_spkmarketing
             AND seq.deal = '1' AND (seq.deleted IS NULL OR seq.deleted = '0') AND seq.id <= d.id) AS item_no,
            product.nama AS item, m.spek AS aloy, surface.nm_surface, m.hardness, m.id_bentuk,
            d.thickness, d.width, d.length, d.qty_produk, d.delivery, totals.total_qty";
        $sql .= $this->filtered_sql($filters, $bindings);
        $sql .= ' ORDER BY '.$columns[$column].' '.$direction.', h.id_spkmarketing ASC, d.id ASC';
        if ($length !== null) $sql .= ' LIMIT '.max(1, min(100, (int) $length)).' OFFSET '.max(0, (int) $start);
        return $this->db->query($sql, $bindings)->result_array();
    }

    public function get_customers()
    {
        // Include historical customers even when their master record has been removed.
        return $this->db->query("SELECT h.id_customer,
            MAX(COALESCE(NULLIF(c.name_customer, ''), h.nama_customer, '')) AS name_customer
            FROM tr_spk_marketing h LEFT JOIN master_customers c ON c.id_customer = h.id_customer
            WHERE (h.deleted IS NULL OR h.deleted = '0')
            GROUP BY h.id_customer ORDER BY name_customer ASC")->result_array();
    }

    public function format_row($row)
    {
        $values = array($row['no_surat'], $row['customer'], $row['tgl_spk_marketing'], $row['tgl_po'],
            $row['plan_cust'], $row['no_po'], (int) $row['item_no'], $row['item'], $row['aloy'],
            $row['nm_surface'], $row['hardness'], $row['thickness'], $row['width'], $row['length'],
            $row['qty_produk'], $row['delivery'], $row['total_qty']);
        foreach (array(2, 3, 4, 15) as $i) $values[$i] = self::valid_date($values[$i]) ? $values[$i] : '';
        foreach (array(11, 12, 13, 14, 16) as $i) $values[$i] = $values[$i] === null || $values[$i] === '' ? '' : (float) $values[$i];
        if ($row['id_bentuk'] === 'B2000001') $values[13] = 'C';
        foreach ($values as &$value) if ($value === null) $value = '';
        unset($value);
        return $values;
    }
}
