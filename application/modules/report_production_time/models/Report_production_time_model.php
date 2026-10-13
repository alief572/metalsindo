<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_production_time_model extends BF_Model
{
    public static function headers()
    {
        return array('No SPK Produksi','SPK Marketing','Tanggal Produksi','Customer','Nama Produk',
            'Berat Mother Coil','Thickness','Density','Panjang Master Coil','Width Mother Coil',
            'No','Progress','Start','Finish','Total Waktu (Menit)','Keterangan',
            'Total Waktu Keseluruhan (Jam)','Total Waktu Keseluruhan (Menit)');
    }

    public static function stages()
    {
        return array('MATL1'=>'MATL CHANGE','SETTING1'=>'SETTING KNIVE','SETTUP1'=>'SETT UP',
            'QC1'=>'QC CHECK SETTING','SETTUP2'=>'SETT UP','QC2'=>'QC CHECK AWAL',
            'SLITTING1'=>'SLITTING PROCESS','QC3'=>'QC CHECK TENGAH','SLITTING2'=>'SLITTING PROCESS',
            'QC4'=>'QC CHECK AKHIR','HANDLING1'=>'HANDLING');
    }

    public static function valid_date($value)
    {
        return is_string($value) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D',$value,$m)
            && (int)$m[1]>0 && checkdate((int)$m[2],(int)$m[3],(int)$m[1]);
    }

    public function normalize_filters($input)
    {
        $filters=array('start_date'=>'','end_date'=>'','search'=>'');
        foreach ($filters as $key=>$default) if (isset($input[$key])) {
            if (!is_scalar($input[$key])) throw new InvalidArgumentException('Filter tidak valid.');
            $filters[$key]=trim((string)$input[$key]);
        }
        foreach (array('start_date','end_date') as $key) if ($filters[$key]!=='' && !self::valid_date($filters[$key]))
            throw new InvalidArgumentException('Tanggal harus valid dengan format YYYY-MM-DD.');
        if ($filters['start_date']!=='' && $filters['end_date']!=='' && $filters['start_date']>$filters['end_date'])
            throw new InvalidArgumentException('Tanggal awal tidak boleh melebihi tanggal akhir.');
        return $filters;
    }

    private function actual_dates_sql()
    {
        $valid=function($field) {
            return 'YEAR('.$field.')>0 AND MONTH('.$field.')>0 AND DAY('.$field.') BETWEEN 1 AND DAY(LAST_DAY('.$field.'))';
        };
        $date='CASE WHEN '.$valid('date_production').' THEN date_production WHEN '.$valid('tgl_produksi').' THEN tgl_produksi ELSE NULL END';
        return '(SELECT id_spkproduksi, MAX(actual_date) AS production_date FROM (
            SELECT id_spkproduksi, '.$date.' AS actual_date FROM tr_spk_aktual
            UNION ALL SELECT id_spkproduksi, '.$date.' AS actual_date FROM tr_spk_aktual_fg
            ) actual_lhp GROUP BY id_spkproduksi)';
    }

    private function filtered_sql($filters, &$bindings)
    {
        $sql=' FROM tr_spk_produksi h LEFT JOIN '.$this->actual_dates_sql().' a ON a.id_spkproduksi=h.id_spkproduksi
            WHERE EXISTS (SELECT 1 FROM dt_spk_produksi_lph p WHERE p.id_spk_aktual=h.id_spkproduksi)';
        $bindings=array();
        foreach (array('start_date'=>'>=','end_date'=>'<=') as $key=>$operator) if ($filters[$key]!=='') {
            $sql.=' AND a.production_date '.$operator.' ?'; $bindings[]=$filters[$key];
        }
        if ($filters['search']!=='') {
            $term='%'.str_replace(array('!','%','_'),array('!!','!%','!_'),$filters['search']).'%';
            $sql.=" AND (h.no_surat LIKE ? ESCAPE '!' OR COALESCE((SELECT s.nama_material FROM dt_tr_spk_produksi s
                WHERE s.id_spkproduksi=h.id_spkproduksi AND s.id_tr_spk_produksi=h.id_tr_spk_produksi ORDER BY s.id ASC LIMIT 1),h.nama_material) LIKE ? ESCAPE '!' OR EXISTS (
                SELECT 1 FROM dt_spk_produksi d WHERE d.id_spkproduksi=h.id_spkproduksi AND d.checked='1'
                AND (d.no_surat LIKE ? ESCAPE '!' OR d.namacustomer LIKE ? ESCAPE '!')))";
            $bindings=array_merge($bindings,array($term,$term,$term,$term));
        }
        return $sql;
    }

    public function count_groups($filters)
    {
        $sql=$this->filtered_sql($filters,$bindings);
        $row=$this->db->query('SELECT COUNT(*) AS total'.$sql,$bindings)->row_array();
        return (int)$row['total'];
    }

    public function get_groups($filters, $start=0, $length=null)
    {
        $sql=$this->filtered_sql($filters,$bindings);
        $headers=$this->db->query('SELECT h.*,h.no_surat AS production_no,a.production_date'.$sql.
            ' ORDER BY a.production_date DESC,h.id_spkproduksi ASC'.($length===null ? '' :
            ' LIMIT '.max(1,min(100,(int)$length)).' OFFSET '.max(0,(int)$start)),$bindings)->result_array();
        $groups=array();
        // Batch children independently: marketing rows must never multiply progress durations.
        foreach (array_chunk($headers,200) as $batch) {
            $ids=array_column($batch,'id_spkproduksi');
            $placeholders=implode(',',array_fill(0,count($ids),'?'));
            $details=$this->db->query("SELECT id_spkproduksi,no_surat,namacustomer FROM dt_spk_produksi
                WHERE checked='1' AND id_spkproduksi IN (".$placeholders.') ORDER BY id ASC',$ids)->result_array();
            $progress=$this->db->query('SELECT * FROM dt_spk_produksi_lph WHERE id_spk_aktual IN ('.$placeholders.') ORDER BY id ASC',$ids)->result_array();
            $stages=$this->db->query('SELECT * FROM dt_tr_spk_produksi WHERE id_spkproduksi IN ('.$placeholders.') ORDER BY id ASC',$ids)->result_array();
            $byDetail=array(); $byProgress=array(); $byStage=array();
            foreach ($details as $row) $byDetail[$row['id_spkproduksi']][]=$row;
            foreach ($progress as $row) $byProgress[$row['id_spk_aktual']][]=$row;
            foreach ($stages as $row) $byStage[$row['id_spkproduksi']][]=$row;
            foreach ($batch as $header) {
                $id=$header['id_spkproduksi'];
                if (isset($byStage[$id])) foreach ($byStage[$id] as $stage) {
                    if ($stage['id_tr_spk_produksi']!==$header['id_tr_spk_produksi']) continue;
                    foreach (array('nama_material','weight','thickness','density','panjang','width') as $field)
                        if ($stage[$field]!==null) $header[$field]=$stage[$field];
                    break;
                }
                $groups[]=$this->format_group($header,isset($byDetail[$id])?$byDetail[$id]:array(),isset($byProgress[$id])?$byProgress[$id]:array());
            }
        }
        return $groups;
    }

    private function progress_row($row, $label)
    {
        $raw=isset($row['total']) ? trim((string)$row['total']) : '';
        $invalid=$raw!=='' && !preg_match('/^[0-9]+(?:\.[0-9]+)?$/D',$raw);
        $time=function($value) { return is_string($value) && preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D',$value) ? substr($value,0,5) : null; };
        return array('progress'=>$label,'start'=>$time(isset($row['start'])?$row['start']:null),
            'finish'=>$time(isset($row['finish'])?$row['finish']:null),'total'=>$raw==='' || $invalid ? null : (float)$raw,
            'invalid_total'=>$invalid,'raw_total'=>$raw,'keterangan'=>isset($row['keterangan'])?(string)$row['keterangan']:'');
    }

    public function format_group($header, $details, $rows)
    {
        $marketing=array(); $customers=array(); $pairs=array();
        foreach ($details as $detail) {
            $m=trim((string)$detail['no_surat']); $c=trim((string)$detail['namacustomer']);
            $key=serialize(array($m,$c));
            if (isset($pairs[$key])) continue;
            $pairs[$key]=true; $marketing[]=$m; $customers[]=$c;
        }
        $info=array(isset($header['production_no'])?$header['production_no']:null,implode("\n",$marketing),
            isset($header['production_date'])?$header['production_date']:null,implode("\n",$customers),
            isset($header['nama_material'])?$header['nama_material']:null);
        foreach (array('weight','thickness','density','panjang','width') as $field)
            $info[]=isset($header[$field])?(float)$header[$field]:null;
        $ordered=array(); $used=array();
        foreach (self::stages() as $id=>$label) {
            $found=false;
            foreach ($rows as $key=>$row) if ($row['id_proses']===$id && !$found) {
                $ordered[]=$this->progress_row($row,$label); $used[$key]=true; $found=true;
            }
            if (!$found) $ordered[]=$this->progress_row(array(),$label);
        }
        foreach ($rows as $key=>$row) if (!isset($used[$key]))
            $ordered[]=$this->progress_row($row,(string)$row['namaproses']);
        $sum=0.0; $hasTotal=false; $invalid=false;
        foreach ($ordered as $row) {
            $invalid=$invalid || $row['invalid_total'];
            if ($row['total']!==null) { $sum+=$row['total']; $hasTotal=true; }
        }
        return array('id'=>isset($header['id_spkproduksi'])?$header['id_spkproduksi']:null,
            'info'=>$info,'progress'=>$ordered,'summary'=>$invalid || !$hasTotal ? array(null,null) : array($sum/60,$sum),
            'invalid_total'=>$invalid);
    }
}
