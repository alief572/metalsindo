<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_raw_material_model extends CI_Model
{
    public function today()
    {
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        return $now->format('Y-m-d');
    }

    public function filters($input)
    {
        if (!is_array($input)) throw new InvalidArgumentException('Filter stok tidak valid.');
        $date = isset($input['stock_date']) ? $input['stock_date'] : '';
        if ($date === '') $date = $this->today();
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)
            || !checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4)) || $date > $this->today())
            throw new InvalidArgumentException('Tanggal stok harus valid dan tidak melebihi hari ini.');
        $width = isset($input['series']) ? $input['series'] : '';
        if (!is_scalar($width) || ($width !== '' && (!is_numeric($width) || (float)$width < 0)))
            throw new InvalidArgumentException('Width harus berupa angka positif atau nol.');
        if (isset($input['search']) && !is_array($input['search'])) throw new InvalidArgumentException('Pencarian tidak valid.');
        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if (!is_scalar($search)) throw new InvalidArgumentException('Pencarian tidak valid.');
        return array('stock_date'=>$date,'width'=>(string)$width,'search'=>(string)$search);
    }

    public function resolve($filters)
    {
        $date = $filters['stock_date'];
        $meta = array('stock_date'=>$date,'snapshot_at'=>null,'available'=>true,'source'=>'stock_material');
        if ($date === $this->today()) return $meta;
        $next = new DateTime($date, new DateTimeZone('Asia/Jakarta'));
        $next->modify('+1 day');
        $row = $this->db->query('SELECT MAX(tgl) AS snapshot_at FROM stock_material_backup WHERE tgl >= ? AND tgl < ?',
            array($date.' 00:00:00',$next->format('Y-m-d').' 00:00:00'))->row_array();
        $meta['source'] = 'stock_material_backup';
        $meta['snapshot_at'] = $row['snapshot_at'];
        $meta['available'] = $row['snapshot_at'] !== null;
        return $meta;
    }

    private function relation($filters, $meta, $filtered, $customer = false)
    {
        $sql = ' FROM '.$meta['source'].' a';
        if ($customer || ($filtered && $filters['search'] !== ''))
            $sql .= ' LEFT JOIN (SELECT id_customer,MAX(NULLIF(name_customer,\'\')) name_customer FROM master_customers GROUP BY id_customer) g ON g.id_customer=a.customer';
        $sql .= ' WHERE a.id_gudang=\'1\' AND a.aktif=\'Y\' AND a.sisa_spk>0 AND a.sisa_spk>COALESCE(a.delivery_book,0)';
        $bindings = array();
        if ($meta['source'] === 'stock_material_backup') {
            $sql .= ' AND a.tgl=?'; $bindings[] = $meta['snapshot_at'];
        }
        if ($filtered) {
            list($predicate,$filterBindings) = $this->filterPredicate($filters);
            $sql .= ' AND '.$predicate;
            $bindings = array_merge($bindings,$filterBindings);
        }
        return array($sql,$bindings);
    }

    private function filterPredicate($filters)
    {
        $conditions=array(); $bindings=array();
        if ($filters['width'] !== '') { $conditions[]='a.width=?'; $bindings[]=$filters['width']; }
        if ($filters['search'] !== '') {
            $parts = array();
            foreach (array('a.lot_slitting','a.lotno','a.nama_material','a.id_category3','a.no_surat','a.customer') as $field) {
                $parts[] = $field." LIKE ? ESCAPE '!'";
                $bindings[] = '%'.$this->db->escape_like_str($filters['search']).'%';
            }
            $parts[] = "g.name_customer LIKE ? ESCAPE '!'";
            $bindings[] = '%'.$this->db->escape_like_str($filters['search']).'%';
            $parts[] = "'GUDANG RAW MATERIAL' LIKE ? ESCAPE '!'";
            $bindings[] = '%'.$this->db->escape_like_str($filters['search']).'%';
            $conditions[] = '('.implode(' OR ',$parts).')';
        }
        return array($conditions ? implode(' AND ',$conditions) : '1=1',$bindings);
    }

    public function summary($filters, $meta, $filtered = true)
    {
        if (!$meta['available']) return array('n'=>0,'total'=>0,'weight'=>0);
        list($sql,$bindings) = $this->relation($filters,$meta,false,$filtered && $filters['search'] !== '');
        if (!$filtered || ($filters['width'] === '' && $filters['search'] === ''))
            return $this->db->query('SELECT COUNT(*) total,COUNT(*) n,COALESCE(SUM(a.sisa_spk*a.qty),0) weight'.$sql,$bindings)->row_array();
        // Both filtered and unfiltered counts share one scan of the selected stock batch.
        list($predicate,$filterBindings) = $this->filterPredicate($filters);
        return $this->db->query('SELECT COUNT(*) total,COALESCE(SUM(CASE WHEN '.$predicate.' THEN 1 ELSE 0 END),0) n,
            COALESCE(SUM(CASE WHEN '.$predicate.' THEN a.sisa_spk*a.qty ELSE 0 END),0) weight'.$sql,
            array_merge($filterBindings,$filterBindings,$bindings))->row_array();
    }

    public function rows($filters, $meta, $start, $length, $direction = 'asc')
    {
        if (!$meta['available']) return array();
        list($sql,$bindings) = $this->relation($filters,$meta,true,true);
        $direction = $direction === 'desc' ? 'DESC' : 'ASC';
        $page='SELECT a.id_stock,a.id_category3,a.lotno,a.nama_material,a.width,a.qty,a.sisa_spk,a.no_surat,COALESCE(g.name_customer,a.customer) customer'.$sql
            .' ORDER BY a.id_stock '.$direction.' LIMIT '.max(0,(int)$start).','.max(1,min(1000,(int)$length));
        // LIMIT precedes master enrichment; duplicate legacy master keys cannot multiply stock rows.
        return $this->db->query('SELECT a.id_stock,a.id_category3,a.lotno,a.nama_material,a.width,a.qty,a.sisa_spk,a.no_surat,
            c.maker,c.id_bentuk,b.nm_bentuk,a.customer FROM ('.$page.') a
            LEFT JOIN (SELECT id_category3,MAX(maker) maker,MAX(id_bentuk) id_bentuk FROM ms_inventory_category3 GROUP BY id_category3) c ON c.id_category3=a.id_category3
            LEFT JOIN (SELECT id_bentuk,MAX(nm_bentuk) nm_bentuk FROM ms_bentuk GROUP BY id_bentuk) b ON b.id_bentuk=c.id_bentuk
            ORDER BY a.id_stock '.$direction,$bindings)->result_array();
    }

    public function values($row, $number, $meta)
    {
        $qty = $row['id_bentuk'] === 'B2000002' ? '' : ($row['qty'] === null ? null : number_format($row['qty'],2).' '.$row['nm_bentuk']);
        return array($number,$row['id_category3'],$row['lotno'],$row['nama_material'],$row['maker'],
            $row['width'] === null ? null : (float)$row['width'],$qty,
            $row['sisa_spk'] === null ? null : (float)$row['sisa_spk'],
            $row['qty'] === null || $row['sisa_spk'] === null ? null : $row['sisa_spk']*$row['qty'],
            $row['no_surat'],'GUDANG RAW MATERIAL',$row['customer'],$meta['stock_date']);
    }

    public function table($input)
    {
        $filters = $this->filters($input); $meta = $this->resolve($filters);
        $summary = $this->summary($filters,$meta);
        $start = isset($input['start']) && is_scalar($input['start']) ? max(0,(int)$input['start']) : 0;
        $length = isset($input['length']) && is_scalar($input['length']) ? max(1,min(250,(int)$input['length'])) : 10;
        $direction = isset($input['order']) && is_array($input['order']) && isset($input['order'][0])
            && is_array($input['order'][0]) && isset($input['order'][0]['dir']) && $input['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
        $data = array();
        foreach ($this->rows($filters,$meta,$start,$length,$direction) as $index=>$row) {
            $number = $direction === 'desc' ? $summary['n']-$start-$index : $start+$index+1;
            $cells = array();
            foreach ($this->values($row,$number,$meta) as $column=>$value) {
                if (in_array($column,array(5,7,8),true) && $value !== null) $value = number_format($value,2);
                $cells[] = htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
            }
            $data[] = $cells;
        }
        return array_merge($meta,array('draw'=>isset($input['draw']) && is_scalar($input['draw']) ? max(0,(int)$input['draw']) : 0,
            'recordsTotal'=>(int)$summary['total'],'recordsFiltered'=>(int)$summary['n'],'recordsAset'=>(float)$summary['weight'],'data'=>$data));
    }
}
