<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_incoming_model extends BF_Model
{
    private $customerSessionReady=false;

    public static function headers()
    {
        return array('#','No. PR','No. PO','Tanggal PO','Supplier','Material','Width','Qty Order (Kg)',
            'Date Incoming','Kg / Lot Number','Total Qty Receive (Kg)','Balance','Status','No. Incoming',
            'PIC','No. Surat Jalan (Supplier)','Gudang','Lot.No','Customer');
    }

    public static function valid_date($value)
    {
        return is_string($value) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D',$value,$m)
            && (int)$m[1]>0 && checkdate((int)$m[2],(int)$m[3],(int)$m[1]);
    }

    public function normalize_filters($input)
    {
        $filters=array('start_date'=>'','end_date'=>'','search'=>'');
        foreach($filters as $key=>$default) {
            if(isset($input[$key])) {
                if(!is_scalar($input[$key])) throw new InvalidArgumentException('Filter tidak valid.');
                $filters[$key]=trim((string)$input[$key]);
            }
        }
        foreach(array('start_date','end_date') as $key)
            if($filters[$key]!=='' && !self::valid_date($filters[$key]))
                throw new InvalidArgumentException('Tanggal harus valid dengan format YYYY-MM-DD.');
        if($filters['start_date']!=='' && $filters['end_date']!=='' && $filters['start_date']>$filters['end_date'])
            throw new InvalidArgumentException('Tanggal awal tidak boleh melebihi tanggal akhir.');
        return $filters;
    }

    private function date_expression($field)
    {
        return 'CASE WHEN YEAR('.$field.') > 0 AND MONTH('.$field.') > 0
            AND DAY('.$field.') BETWEEN 1 AND DAY(LAST_DAY('.$field.')) THEN '.$field.' ELSE NULL END';
    }

    private function columns()
    {
        $verified='h.id_data IS NOT NULL AND h.pic_variants<=1 AND h.sj_variants<=1';
        return array('d.id_dt_incoming','po.no_pr','po.no_surat',$this->date_expression('po.tanggal'),
            'supplier.name_suplier','d.nama_material','d.width','p.totalwidth',
            $this->date_expression('d.tgl_datang'),'d.width_recive','p.berat_terima',
            '(p.totalwidth - p.berat_terima)',"CASE p.close_po WHEN 'N' THEN 'Open' WHEN 'Y' THEN 'Closed' ELSE NULL END",
            'd.id_incoming',"CASE WHEN $verified THEN h.pic ELSE 'Belum terverifikasi' END",
            "CASE WHEN $verified THEN h.sj_supplier ELSE 'Belum terverifikasi' END",
            'warehouse.nama_gudang','d.lotno','customers.names');
    }

    private function relations()
    {
        // Session-only setting; no transaction/stock/master data is changed. Avoid the MySQL 1024-byte default.
        if(!$this->customerSessionReady) {
            $this->db->query('SET SESSION group_concat_max_len = 16777216');
            $this->customerSessionReady=true;
        }
        // Materialize the unindexed legacy PO key once, with only unambiguous item records.
        // Ambiguous PO keys produce empty PO fields instead of inventing a record or duplicating detail.
        return " FROM dt_incoming d
            LEFT JOIN (SELECT id_dt_po,MIN(no_po) no_po,MIN(totalwidth) totalwidth,
                MIN(berat_terima) berat_terima,MIN(close_po) close_po
                FROM dt_trans_po GROUP BY id_dt_po HAVING COUNT(*)=1) p
                ON p.id_dt_po=d.id_dt_po AND d.id_dt_po<>''
            LEFT JOIN tr_purchase_order po ON po.no_po=p.no_po
            LEFT JOIN (SELECT id_suplier,
                CASE WHEN COUNT(DISTINCT COALESCE(NULLIF(TRIM(name_suplier),''),''))<=1 THEN MIN(NULLIF(TRIM(name_suplier),'')) END name_suplier
                FROM master_supplier GROUP BY id_suplier) supplier ON supplier.id_suplier=po.id_suplier
            LEFT JOIN (SELECT id_gudang,
                CASE WHEN COUNT(DISTINCT COALESCE(NULLIF(TRIM(nama_gudang),''),''))<=1 THEN MIN(NULLIF(TRIM(nama_gudang),'')) END nama_gudang
                FROM ms_gudang GROUP BY id_gudang) warehouse ON warehouse.id_gudang=d.id_gudang
            LEFT JOIN (SELECT id_data,id_incoming,tanggal,id_suplier,
                MIN(NULLIF(TRIM(pic),'')) pic, MIN(NULLIF(TRIM(sj_supplier),'')) sj_supplier,
                COUNT(DISTINCT COALESCE(NULLIF(TRIM(pic),''),'')) pic_variants,
                COUNT(DISTINCT COALESCE(NULLIF(TRIM(sj_supplier),''),'')) sj_variants
                FROM tr_incoming GROUP BY id_data,id_incoming,tanggal,id_suplier) h
                ON h.id_data=d.id_data AND h.id_incoming=d.id_incoming AND h.tanggal=d.tgl_datang
                AND h.id_suplier=po.id_suplier AND d.id_data<>'' AND d.id_incoming<>''
            LEFT JOIN (SELECT id_incoming,no_po,id_category3,lotno,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(customer),'') ORDER BY NULLIF(TRIM(customer),'') SEPARATOR '; ') names
                FROM stock_material WHERE NULLIF(TRIM(customer),'') IS NOT NULL
                GROUP BY id_incoming,no_po,id_category3,lotno) customers
                ON customers.id_incoming=d.id_data AND customers.no_po=d.id_dt_po
                AND customers.id_category3=d.id_material AND customers.lotno=d.lotno
                AND d.id_data<>'' AND d.id_dt_po<>'' AND d.id_material<>'' AND d.lotno<>''";
    }

    private function where_sql($filters,&$bindings)
    {
        $columns=$this->columns(); $sql=' WHERE 1=1'; $bindings=array();
        foreach(array('start_date'=>'>=','end_date'=>'<=') as $key=>$operator)
            if($filters[$key]!=='') { $sql.=' AND ('.$columns[8].') '.$operator.' ?'; $bindings[]=$filters[$key]; }
        if($filters['search']!=='') {
            $term='%'.str_replace(array('!','%','_'),array('!!','!%','!_'),$filters['search']).'%'; $parts=array();
            foreach(array(1,2,4,5,13,14,15,16,17,18) as $index) {
                $parts[]=$columns[$index]." LIKE ? ESCAPE '!'"; $bindings[]=$term;
            }
            $sql.=' AND ('.implode(' OR ',$parts).')';
        }
        return $sql;
    }

    public function count_rows($filters)
    {
        // No lookup changes cardinality; skip lookup materialization when there is no text filter.
        $from=$filters['search']==='' ? ' FROM dt_incoming d' : $this->relations();
        $sql='SELECT COUNT(*) total'.$from.$this->where_sql($filters,$bindings);
        $row=$this->db->query($sql,$bindings)->row_array();
        return (int)$row['total'];
    }

    private function query_rows($filters,$start,$length,$column,$direction)
    {
        $columns=$this->columns(); $column=isset($columns[$column]) ? (int)$column : 8;
        $direction=$direction==='asc' ? 'ASC' : 'DESC';
        $select=array('d.id_dt_incoming detail_id');
        foreach($columns as $i=>$expression) $select[]=$expression.' AS c'.$i;
        $sql='SELECT '.implode(', ',$select).$this->relations().$this->where_sql($filters,$bindings);
        $sql.=' ORDER BY '.$columns[$column].' '.$direction.', d.id_dt_incoming ASC';
        if($length!==null) $sql.=' LIMIT '.max(1,min(100,(int)$length)).' OFFSET '.max(0,(int)$start);
        return $this->db->query($sql,$bindings);
    }

    public function get_rows($filters,$start=0,$length=null,$column=8,$direction='desc')
    {
        return $this->query_rows($filters,$start,$length,$column,$direction)->result_array();
    }

    public function export_rows($filters,$column=8,$direction='desc')
    {
        $result=$this->query_rows($filters,0,null,$column,$direction);
        try {
            while($row=$result->unbuffered_row('array')) yield $row;
        } finally { $result->free_result(); }
    }

    public function format_row($row,$number=1)
    {
        $values=array((int)$number);
        for($i=1;$i<19;$i++) {
            $value=$row['c'.$i];
            if($value!==null && is_string($value) && trim($value)==='') $value=null;
            $values[]=$value===null ? null : (in_array($i,array(6,7,9,10,11),true) ? (float)$value : (string)$value);
        }
        return $values;
    }
}
