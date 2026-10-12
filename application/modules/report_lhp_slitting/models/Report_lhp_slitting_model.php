<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_lhp_slitting_model extends BF_Model
{
    public static function headers()
    {
        return array('No SPK Produksi','No SPK Marketing','Customer','Tanggal Produksi','Product',
            'Width Slitting','Weight Slitting','Lot Number Slitting','Actual Weight Slitting',
            'Finishgood - Berat Aktual','Sisa Potong - Lebar Aktual','Sisa Potong - Berat Aktual',
            'Scrap Trimming+Sisa - Berat','NG Internal - Lebar','NG Internal - Berat',
            'NG Eksternal - Lebar','NG Eksternal - Berat','Total Weight Slitting','Selisih Packing List VS Aktual');
    }

    public static function valid_date($value)
    {
        return is_string($value) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $value, $m)
            && (int)$m[1] > 0 && checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
    }

    public function normalize_filters($input)
    {
        $filters = array('start_date'=>'','end_date'=>'','search'=>'');
        foreach ($filters as $key=>$default) {
            if (isset($input[$key])) {
                if (!is_scalar($input[$key])) throw new InvalidArgumentException('Filter tidak valid.');
                $filters[$key] = trim((string)$input[$key]);
            }
        }
        foreach (array('start_date','end_date') as $key) {
            if ($filters[$key] !== '' && !self::valid_date($filters[$key]))
                throw new InvalidArgumentException('Tanggal harus valid dengan format YYYY-MM-DD.');
        }
        if ($filters['start_date'] !== '' && $filters['end_date'] !== '' && $filters['start_date'] > $filters['end_date'])
            throw new InvalidArgumentException('Tanggal awal tidak boleh melebihi tanggal akhir.');
        return $filters;
    }

    private function columns()
    {
        // MySQL legacy data can contain zero or impossible dates; use the same expression for display/filter/sort.
        $valid = function($field) {
            return 'YEAR('.$field.') > 0 AND MONTH('.$field.') > 0 AND DAY('.$field.') BETWEEN 1 AND DAY(LAST_DAY('.$field.'))';
        };
        $date = 'CASE WHEN '.$valid('h.date_production').' THEN h.date_production WHEN '.$valid('h.tgl_produksi').' THEN h.tgl_produksi ELSE NULL END';
        return array('h.no_surat_produksi','d.no_surat','d.namacustomer',$date,
            "COALESCE(NULLIF(TRIM(d.nmmaterial), ''), h.nama_material)",
            'd.weight','d.width','d.lot_slitting','d.totalaktual','h.bfinishgood','h.lsisa_aktual',
            'h.bsisa_aktual','h.bscrap_aktual','h.lebar_ng_in','h.berat_ng_in','h.lebar_ng_ek',
            'h.berat_ng_ek','h.tweightslitting','h.ng_packinglist');
    }

    private function filtered_sql($filters, &$bindings)
    {
        $columns = $this->columns();
        $sql = ' FROM dt_spk_aktual_detail_fg d JOIN tr_spk_aktual_fg h ON h.id_spk_aktual = d.id_spk_aktual
            AND h.id_dt_spkaktualdetail = d.id_dt_spkaktualdetail WHERE 1=1';
        $bindings = array();
        foreach (array('start_date'=>'>=','end_date'=>'<=') as $key=>$operator) {
            if ($filters[$key] !== '') { $sql .= ' AND ('.$columns[3].') '.$operator.' ?'; $bindings[] = $filters[$key]; }
        }
        if ($filters['search'] !== '') {
            $term = '%'.str_replace(array('!','%','_'), array('!!','!%','!_'), $filters['search']).'%';
            $search = array();
            foreach (array(0,1,2,4,7) as $column) { $search[] = $columns[$column]." LIKE ? ESCAPE '!'"; $bindings[] = $term; }
            $sql .= ' AND ('.implode(' OR ', $search).')';
        }
        return $sql;
    }

    public function count_rows($filters)
    {
        $sql = $this->filtered_sql($filters, $bindings);
        $row = $this->db->query('SELECT COUNT(*) AS total'.$sql, $bindings)->row_array();
        return (int)$row['total'];
    }

    public function get_rows($filters, $start = 0, $length = null, $column = 3, $direction = 'desc')
    {
        $columns = $this->columns();
        $column = isset($columns[$column]) ? (int)$column : 3;
        $direction = $direction === 'asc' ? 'ASC' : 'DESC';
        $select = array('d.id AS detail_id');
        foreach ($columns as $i=>$expression) $select[] = $expression.' AS c'.$i;
        $sql = 'SELECT '.implode(', ', $select).$this->filtered_sql($filters, $bindings);
        $sql .= ' ORDER BY '.$columns[$column].' '.$direction.', d.id ASC';
        if ($length !== null) $sql .= ' LIMIT '.max(1,min(100,(int)$length)).' OFFSET '.max(0,(int)$start);
        return $this->db->query($sql, $bindings)->result_array();
    }

    public function format_row($row)
    {
        $values = array();
        for ($i=0;$i<19;$i++) {
            $value = $row['c'.$i];
            $values[] = $value === null ? null : (($i >= 5 && $i !== 7) ? (float)$value : (string)$value);
        }
        return $values;
    }
}
