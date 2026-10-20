<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Adjustment_balance_model extends CI_Model
{
    public function amounts($input)
    {
        $qty = $this->number(isset($input['total_qty']) ? $input['total_qty'] : null, 'Qty', 0);
        $kg = $this->number(isset($input['total_jumlah_berat']) ? $input['total_jumlah_berat'] : null, 'Total berat', 2);
        if ($qty > 2147483647 || $kg > 999999.99 || ($qty == 0 && $kg == 0)) {
            throw new InvalidArgumentException('Qty/berat melewati batas atau keduanya nol.');
        }
        return array((int)$qty, $kg);
    }

    private function number($value, $label, $decimals)
    {
        $pattern = $decimals ? '/^\d+(?:\.\d{1,'.$decimals.'})?$/D' : '/^\d+$/D';
        if (!is_scalar($value) || is_bool($value) || !preg_match($pattern, (string)$value)
            || ($decimals === 0 && strpos((string)$value, '.') !== false) || !is_finite((float)$value)) {
            throw new InvalidArgumentException($label.' harus angka nonnegatif dengan maksimal '.$decimals.' desimal.');
        }
        return (float)$value;
    }

    private function text($input, $key, $max, $required = true)
    {
        $value = isset($input[$key]) && is_scalar($input[$key]) ? trim((string)$input[$key]) : '';
        if (($required && $value === '') || strlen($value) > $max) {
            throw new InvalidArgumentException('Isian '.$key.' tidak valid.');
        }
        return $value;
    }

    private function identifier($input, $key, $required = true)
    {
        $value = $this->text($input,$key,13,$required);
        if ($value !== '' && (!ctype_digit($value) || (float)$value <= 0 || (float)$value > 2147483647)) {
            throw new InvalidArgumentException('ID '.$key.' tidak valid.');
        }
        return $value;
    }

    private function unit($cents, $qty, $label)
    {
        if (!$qty) return 0.0;
        $unit = (int)round($cents / $qty);
        if ($unit * $qty != $cents) {
            throw new InvalidArgumentException($label.' tidak dapat dibagi ke Qty akhir dengan presisi 2 desimal. Sesuaikan Qty/berat.');
        }
        return $unit / 100;
    }

    public function balance($lot, $deltaQty, $deltaKg)
    {
        foreach (array('qty'=>'Qty','weight'=>'Berat/Qty','totalweight'=>'Berat gross','sisa_spk'=>'Berat operasional') as $key=>$label) {
            if (!isset($lot[$key]) || !is_numeric($lot[$key]) || !is_finite((float)$lot[$key]) || $lot[$key] < 0) {
                throw new InvalidArgumentException('Saldo lot '.$label.' kosong/tidak valid. Periksa data stok terlebih dahulu.');
            }
        }
        if ($lot['qty'] != floor($lot['qty']) || $lot['qty'] <= 0 || $lot['qty'] > 2147483647) {
            throw new InvalidArgumentException('Qty awal lot harus bilangan bulat positif.');
        }
        if ($lot['totalweight'] > 999999.99 || $lot['sisa_spk'] * $lot['qty'] > 999999.99) {
            throw new InvalidArgumentException('Saldo lot melewati kapasitas saldo gudang. Rekonsiliasi diperlukan.');
        }
        $qty = (int)$lot['qty'] + $deltaQty;
        $gross = (int)round($lot['totalweight'] * 100) + (int)round($deltaKg * 100);
        $operational = (int)round($lot['sisa_spk'] * $lot['qty'] * 100) + (int)round($deltaKg * 100);
        if ($qty < 0 || $qty > 2147483647 || $gross < 0 || $operational < 0 || (!$qty && ($gross || $operational))) {
            throw new InvalidArgumentException('Saldo akhir Qty/berat tidak valid atau pengurangan melebihi stok.');
        }
        $result = array('qty'=>$qty, 'totalweight'=>$gross / 100,
            'weight'=>$this->unit($gross, $qty, 'Berat gross'),
            'sisa_spk'=>$this->unit($operational, $qty, 'Berat operasional'),
            'aktif'=>($qty || $gross || $operational) ? 'Y' : 'N');
        foreach (array('booking','delivery_book') as $key) {
            $value = isset($lot[$key]) ? $lot[$key] : 0;
            if (!is_numeric($value) || !is_finite((float)$value) || $value < 0) throw new InvalidArgumentException('Booking lot tidak valid.');
            if ($value * $lot['qty'] > 999999.99) throw new InvalidArgumentException('Booking lot melewati kapasitas saldo gudang.');
            $cents = (int)round($value * $lot['qty'] * 100);
            if (!$qty && $cents) throw new InvalidArgumentException('Lot masih memiliki booking.');
            $result[$key] = $this->unit($cents, $qty, 'Booking');
        }
        return $result;
    }

    private function rows($sql, $params = array())
    {
        $query = $this->db->query($sql, $params);
        if ($query === false) throw new RuntimeException('Query adjustment gagal.');
        return $query->result_array();
    }

    private function one($sql, $params, $label)
    {
        $rows = $this->rows($sql, $params);
        if (count($rows) !== 1) throw new InvalidArgumentException($label.' tidak ditemukan atau memiliki data ganda.');
        return $rows[0];
    }

    public function material($id)
    {
        $material = $this->one('SELECT id_category3,nama,id_bentuk,thickness FROM ms_inventory_category3 WHERE id_category3=? AND deleted=0', array($id), 'Material');
        $shape = $this->one('SELECT nm_bentuk FROM ms_bentuk WHERE id_bentuk=?', array($material['id_bentuk']), 'Bentuk material');
        $material['nm_bentuk'] = $shape['nm_bentuk'];
        return $material;
    }

    public function options($id)
    {
        $material = $this->material($id);
        $warehouses = $this->rows('SELECT g.id_gudang,g.nama_gudang FROM ms_gudang g WHERE EXISTS (SELECT 1 FROM ms_warehouse w WHERE w.id=g.id_gudang) ORDER BY g.id_gudang');
        $lots = $this->rows('SELECT id_stock,id_category3,lotno,id_gudang,qty,weight,totalweight,sisa_spk,booking,delivery_book,width,length,thickness,id_bentuk,customer,aktif FROM stock_material WHERE id_category3=? AND lotno IS NOT NULL AND lotno<>? ORDER BY lotno,id_stock', array($id,''));
        return array('material'=>$material, 'warehouses'=>$warehouses, 'lots'=>$lots);
    }

    private function write($sql, $params)
    {
        if ($this->db->query($sql, $params) === false) throw new RuntimeException('Penyimpanan adjustment gagal.');
    }

    private function insert($table, $data)
    {
        $columns = array_keys($data);
        $this->write('INSERT INTO '.$table.' (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', array_fill(0, count($columns), '?')).')', array_values($data));
    }

    private function updateLot($id, $balance, $user, $now)
    {
        $balance['modified_by'] = $user;
        $balance['modified_on'] = $now;
        $columns = array();
        foreach ($balance as $key=>$value) $columns[] = '`'.$key.'`=?';
        $values = array_values($balance);
        $values[] = $id;
        $this->write('UPDATE stock_material SET '.implode(',', $columns).' WHERE id_stock=?', $values);
    }

    private function warehouse($id)
    {
        $g = $this->one('SELECT id_gudang,nama_gudang FROM ms_gudang WHERE id_gudang=?', array($id), 'Gudang');
        $w = $this->one('SELECT wh_name FROM ms_warehouse WHERE id=?', array($id), 'Referensi gudang');
        $g['wh_name'] = $w['wh_name'];
        return $g;
    }

    private function lot($id, $material)
    {
        return $this->one('SELECT * FROM stock_material WHERE id_stock=? AND id_category3=? AND aktif=? FOR UPDATE', array($id,$material,'Y'), 'Lot stok');
    }

    private function compatible($source, $target)
    {
        foreach (array('id_category3','lotno','id_bentuk','width','length','thickness','customer') as $key) {
            if ((string)$source[$key] !== (string)$target[$key]) throw new InvalidArgumentException('Lot tujuan berbeda material, lot number, dimensi, bentuk atau customer.');
        }
    }

    private function ledger($material, $warehouse, $delta, $code, $type, $user, $now, $from, $to)
    {
        $rows = $this->rows('SELECT * FROM warehouse_stock WHERE id_material=? AND id_gudang=? FOR UPDATE', array($material['id_category3'],$warehouse['id_gudang']));
        if (count($rows) > 1) throw new InvalidArgumentException('Saldo gudang ganda untuk material ini. Rekonsiliasi saldo diperlukan sebelum adjustment.');
        if (!$rows && ($type === 'Adjustment Minus' || $type === 'Adjustment Mutasi (-)')) throw new InvalidArgumentException('Saldo gudang asal belum tersedia.');
        $old = $rows ? $rows[0]['qty_stock'] : 0;
        if (!is_numeric($old) || $old < 0) throw new InvalidArgumentException('Saldo gudang kosong/tidak valid.');
        $new = round($old + $delta, 4);
        if ($new < 0 || $new > 999999.9999) throw new InvalidArgumentException('Saldo gudang tidak cukup atau melewati kapasitas kolom.');
        $data = array('id_material'=>$material['id_category3'],'idmaterial'=>$material['id_category3'],
            'nm_material'=>$material['nama'],'id_gudang'=>$warehouse['id_gudang'],'kd_gudang'=>$warehouse['wh_name'],
            'qty_stock'=>$new,'update_by'=>$user,'update_date'=>$now);
        if ($rows) $this->write('UPDATE warehouse_stock SET qty_stock=?,update_by=?,update_date=? WHERE id=?', array($new,$user,$now,$rows[0]['id']));
        else $this->insert('warehouse_stock', $data);
        $booking = $rows ? $rows[0]['qty_booking'] : 0;
        $history = array('id_material'=>$material['id_category3'],'idmaterial'=>$material['id_category3'],'nm_material'=>$material['nama'],
            'id_gudang'=>$warehouse['id_gudang'],'kd_gudang'=>$warehouse['wh_name'],
            'qty_stock_awal'=>$old,'qty_stock_akhir'=>$new,'qty_booking_awal'=>$booking,'qty_booking_akhir'=>$booking,
            'no_ipp'=>$code,'jumlah_mat'=>abs($delta),'ket'=>$type,'jenis_transaksi'=>$type,'update_by'=>$user,'update_date'=>$now);
        if ($from) { $history['id_gudang_dari']=$from['id_gudang']; $history['kd_gudang_dari']=$from['wh_name']; }
        if ($to) { $history['id_gudang_ke']=$to['id_gudang']; $history['kd_gudang_ke']=$to['wh_name']; }
        $this->insert('warehouse_history', $history);
    }

    public function save($input, $user)
    {
        list($qty,$kg) = $this->amounts($input);
        $type = $this->text($input, 'adjustment', 10);
        if (!in_array($type, array('PLUS','MINUS','MUTASI'), true)) throw new InvalidArgumentException('Jenis adjustment tidak valid.');
        $id = $this->text($input, 'id_material', 15);
        $note = $this->text($input, 'note', 200);
        $sourceId = $this->identifier($input, 'id_stock', false);
        if ($type === 'PLUS' && isset($input['plus_lot'])) {
            $mode = $this->text($input,'plus_lot',10);
            if (!in_array($mode,array('new','existing'),true) || ($mode === 'new' && $sourceId !== '') || ($mode === 'existing' && $sourceId === '')) {
                throw new InvalidArgumentException('Pilihan lot PLUS tidak sesuai.');
            }
        }
        $new = $type === 'PLUS' && $sourceId === '';
        if ($new && (!$qty || !$kg)) throw new InvalidArgumentException('Lot baru wajib memiliki Qty dan berat positif. Pilih lot existing untuk koreksi berat saja.');
        $lock = 'adjstock:'.$this->db->database;
        $got = $this->one('SELECT GET_LOCK(?,10) AS acquired', array($lock), 'Kunci adjustment');
        if ((int)$got['acquired'] !== 1) throw new InvalidArgumentException('Adjustment lain sedang diproses. Silakan coba lagi.');
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            if ($this->db->trans_begin() === false) throw new RuntimeException('Tidak dapat memulai transaksi.');
            $material = $this->material($id);
            $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            $stamp = $now->format('Y-m-d H:i:s');
            $prefix = 'A'.$now->format('ym');
            $sequence = $this->rows('SELECT id_transaksi FROM adjustment_stock WHERE id_transaksi LIKE ? ORDER BY id_transaksi DESC LIMIT 1', array($prefix.'%'));
            $counter = $sequence ? ((int)substr($sequence[0]['id_transaksi'],5) + 1) : 1;
            if ($counter > 99999) throw new InvalidArgumentException('Nomor adjustment bulanan telah penuh.');
            $code = $prefix.str_pad($counter,5,'0',STR_PAD_LEFT);
            if ($new) {
                $warehouse = $this->warehouse($this->identifier($input,'id_gudang'));
                $lotno = $this->text($input,'lotno',50);
                $existing = $this->rows('SELECT id_stock FROM stock_material WHERE id_category3=? AND id_gudang=? AND lotno=? AND aktif=? FOR UPDATE', array($id,$warehouse['id_gudang'],$lotno,'Y'));
                if ($existing) throw new InvalidArgumentException('Lot sudah ada di gudang ini. Pilih lot existing.');
                $width = $this->number(isset($input['width']) ? $input['width'] : null,'Width',2);
                $length = $this->number(isset($input['length']) ? $input['length'] : null,'Length',2);
                $thickness = $this->number($material['thickness'],'Thickness master',2);
                $source = array('id_category3'=>$id,'nama_material'=>$material['nama'],'id_bentuk'=>$material['id_bentuk'],
                    'lotno'=>$lotno,'id_gudang'=>$warehouse['id_gudang'],'width'=>$width,'length'=>$length,'thickness'=>$thickness,
                    'customer'=>null,'qty'=>0,'weight'=>0,'totalweight'=>0,'sisa_spk'=>0,'booking'=>0,'delivery_book'=>0);
                $created = $source;
                $created['qty']=$qty; $created['weight']=$this->unit((int)round($kg*100),$qty,'Berat lot baru');
                $created['totalweight']=$kg; $created['sisa_spk']=$created['weight'];
                $created['aktif']='Y'; $created['created_by']=$user; $created['created_on']=$stamp;
                $this->insert('stock_material',$created);
                $sourceId = $this->db->insert_id();
            } else {
                $source = $this->lot($sourceId,$id);
                $warehouse = $this->warehouse($source['id_gudang']);
                if ($type !== 'PLUS' && ((float)$source['booking'] > 0 || (float)$source['delivery_book'] > 0)) {
                    throw new InvalidArgumentException('Lot masih memiliki booking produksi/delivery. Selesaikan atau lepaskan booking terlebih dahulu.');
                }
                $sign = $type === 'PLUS' ? 1 : -1;
                $balance = $this->balance($source,$sign*$qty,$sign*$kg);
                $this->updateLot($sourceId,$balance,$user,$stamp);
            }
            $targetId = null; $destination = null;
            if ($type === 'MUTASI') {
                $destination = $this->warehouse($this->identifier($input,'id_gudang_baru'));
                if ($destination['id_gudang'] == $warehouse['id_gudang']) throw new InvalidArgumentException('Gudang tujuan harus berbeda dari gudang asal.');
                $targetId = $this->identifier($input,'id_stock_tujuan',false);
                if ($targetId !== '') {
                    $target = $this->lot($targetId,$id);
                    if ($target['id_gudang'] != $destination['id_gudang']) throw new InvalidArgumentException('Lot tujuan tidak berada di gudang tujuan.');
                    $this->compatible($source,$target);
                    $this->updateLot($targetId,$this->balance($target,$qty,$kg),$user,$stamp);
                } else {
                    if (!$qty || !$kg) throw new InvalidArgumentException('Mutasi Qty/berat nol wajib menggunakan lot tujuan existing.');
                    $candidates = $this->rows('SELECT * FROM stock_material WHERE id_category3=? AND id_gudang=? AND lotno=? AND aktif=? FOR UPDATE',array($id,$destination['id_gudang'],$source['lotno'],'Y'));
                    foreach ($candidates as $candidate) {
                        $matches = true;
                        foreach (array('id_bentuk','width','length','thickness','customer') as $key) {
                            if ((string)$candidate[$key] !== (string)$source[$key]) $matches = false;
                        }
                        if ($matches) throw new InvalidArgumentException('Lot yang sesuai sudah ada di gudang tujuan. Pilih lot tujuan existing.');
                    }
                    $target = array();
                    foreach (array('id_category3','nama_material','id_bentuk','lotno','width','length','thickness','customer','no_po','id_incoming','lot_slitting','no_surat','id_roll','tipe_material') as $key) {
                        if (array_key_exists($key,$source)) $target[$key]=$source[$key];
                    }
                    $target['id_gudang']=$destination['id_gudang']; $target['qty']=$qty;
                    $target['weight']=$this->unit((int)round($kg*100),$qty,'Berat mutasi');
                    $target['totalweight']=$kg; $target['sisa_spk']=$target['weight'];
                    $target['booking']=0; $target['delivery_book']=0; $target['costbook']=0;
                    $target['aktif']='Y'; $target['created_by']=$user; $target['created_on']=$stamp;
                    $target['modified_by']=null; $target['modified_on']=null;
                    $this->insert('stock_material',$target);
                    $targetId=$this->db->insert_id();
                }
                $this->ledger($material,$warehouse,-$kg,$code,'Adjustment Mutasi (-)',$user,$stamp,$warehouse,$destination);
                $this->ledger($material,$destination,$kg,$code,'Adjustment Mutasi (+)',$user,$stamp,$warehouse,$destination);
            } else {
                $this->ledger($material,$warehouse,($type==='PLUS' ? $kg : -$kg),$code,'Adjustment '.ucfirst(strtolower($type)),$user,$stamp,null,$warehouse);
            }
            $history = array('id_transaksi'=>$code,'id_material'=>$id,'nama_material'=>$material['nama'],
                'id_bentuk'=>$material['id_bentuk'],'nama_bentuk'=>$material['nm_bentuk'],'adjustment'=>$type,
                'id_stock'=>$sourceId,'lotno'=>$source['lotno'],'note'=>$note.($targetId ? ' [Tujuan #'.$targetId.']' : ''),
                'length'=>$source['length'],'width'=>$source['width'],'thickness'=>$source['thickness'],
                'mother_qty'=>$source['qty'],'mother_berat'=>$source['weight'],'mother_jumlah_berat'=>$source['totalweight'],
                'total_qty'=>$qty,'total_berat'=>$qty ? round($kg/$qty,2) : null,'total_jumlah_berat'=>$kg,
                'id_gudang'=>$warehouse['id_gudang'],'nama_gudang'=>$warehouse['nama_gudang'],
                'tanggal_transaksi'=>$now->format('Y-m-d'),'user'=>$user);
            if ($destination) { $history['id_gudang_baru']=$destination['id_gudang']; $history['nama_gudang_baru']=$destination['nama_gudang']; }
            $this->insert('adjustment_stock',$history);
            if ($this->db->trans_status() === false || $this->db->trans_commit() === false) throw new RuntimeException('Commit adjustment gagal.');
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        } finally {
            $this->db->query('SELECT RELEASE_LOCK(?)',array($lock));
            $this->db->db_debug=$debug;
        }
        return $code;
    }
}
