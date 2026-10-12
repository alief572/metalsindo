<?php
require __DIR__.'/bootstrap.php';
$db->temporary_tables(array('dt_incoming','tr_incoming','dt_trans_po','tr_purchase_order','master_supplier','ms_gudang','stock_material'));
function incoming_insert($table,$values) {
    global $db;
    $db->query('INSERT INTO '.$table.' (`'.implode('`,`',array_keys($values)).'`) VALUES ('.implode(',',array_fill(0,count($values),'?')).')',array_values($values));
}
incoming_insert('master_supplier',array('id_suplier'=>'S1','name_suplier'=>'PT.MICS STEEL INDONESIA'));
incoming_insert('master_supplier',array('id_suplier'=>'S2','name_suplier'=>'Supplier 2'));
incoming_insert('ms_gudang',array('id_gudang'=>3,'nama_gudang'=>'Gudang Finish Good'));
incoming_insert('tr_purchase_order',array('no_po'=>'P1','no_pr'=>'P2600999','no_surat'=>'PO-MP/26/IX/0263','tanggal'=>'2026-09-11','id_suplier'=>'S1'));
incoming_insert('tr_purchase_order',array('no_po'=>'P2','no_pr'=>'PR2','no_surat'=>'PO2','tanggal'=>'0000-00-00','id_suplier'=>'S2'));
incoming_insert('tr_purchase_order',array('no_po'=>'P3','no_pr'=>'PR3','no_surat'=>'PO3','tanggal'=>'2026-09-01','id_suplier'=>'MISSING'));
incoming_insert('dt_trans_po',array('id_dt_po'=>'P1-1','no_po'=>'P1','totalwidth'=>264,'berat_terima'=>265,'close_po'=>'N'));
incoming_insert('dt_trans_po',array('id_dt_po'=>'P2-1','no_po'=>'P2','totalwidth'=>100,'berat_terima'=>0,'close_po'=>'Y'));
incoming_insert('dt_trans_po',array('id_dt_po'=>'P3-1','no_po'=>'P3','totalwidth'=>null,'berat_terima'=>null,'close_po'=>null));
$headers=array(
 array('IC_119_MP_09_2026','IC-120/MP-09/2026','2026-09-23','S1','ASEP','001088/MICS/09/26'),
 array('IC_119_MP_09_2026','IC-120/MP-09/2026','2026-09-23','S1','ASEP','001088/MICS/09/26'),
 array('IC_119_MP_09_2026','IC-120/MP-09/2026','2026-09-23','S2','WRONG SUPPLIER','WRONG'),
 array('IC_119_MP_09_2026','IC-120/MP-09/2026','2026-09-22','S1','WRONG DATE','WRONG'),
 array('OLD','IC-OLD','2026-08-20','S1','OLD PIC','OLD SJ'),
 array('ZERO','IC-ZERO','0000-00-00','S2',null,''),
 array('CONFLICT','IC-CONFLICT','2026-09-24','MISSING','PIC A','SJ'),
 array('CONFLICT','IC-CONFLICT','2026-09-24','MISSING','PIC B','SJ')
);
foreach($headers as $h) incoming_insert('tr_incoming',array_combine(array('id_data','id_incoming','tanggal','id_suplier','pic','sj_supplier'),$h));
$details=array(
 array(1,'IC_119_MP_09_2026','IC-120/MP-09/2026','P1-1','2026-09-23','CPD0661B-230926-12380-1',380,265,999,'SHEET COLD ROLLED SCGA270C-0-None-1.20-380-370-COLD ROLLED 0'),
 array(2,'OLD','IC-OLD','P1-1','2026-08-20','OLDER',380,100,500,'Older receipt'),
 array(3,'ZERO','IC-ZERO','P2-1','0000-00-00','ZERO-LOT',0,0,0,'Zero receipt'),
 array(4,'CONFLICT','IC-CONFLICT','P3-1','2026-09-24','=1+1',null,null,null,'<script>'),
 array(5,'NOHEADER','IC-NOHEADER','NOPO','2026-09-25','MISSING',null,10,null,'Missing lookup'),
 array(6,'IC_119_MP_09_2026','IC-120/MP-09/2026','P1-1','2026-09-23','MULTI%_!',380,20,700,'Multiple customer'),
 array(7,'ZERO','IC-ZERO','P2-1','2026-02-31','IMPOSSIBLE',1,1,1,'Impossible date')
);
foreach($details as $d) {
    $v=array_combine(array('id_dt_incoming','id_data','id_incoming','id_dt_po','tgl_datang','lotno','width','width_recive','actual_berat','nama_material'),$d);
    $v['id_material']='M1'; $v['id_gudang']=3;
    incoming_insert('dt_incoming',$v);
}
foreach(array(
 array('IC_119_MP_09_2026','P1-1','CPD0661B-230926-12380-1','PT. TRI CENTRUM FORTUNA Plant II ( TCF )'),
 array('IC_119_MP_09_2026','P1-1','MULTI%_!','Customer B'),
 array('IC_119_MP_09_2026','P1-1','MULTI%_!','Customer A'),
 array('IC_119_MP_09_2026','P1-1','MULTI%_!','Customer A'),
 array('OTHER','P1-1','MULTI%_!','WRONG INCOMING'),
 array('IC_119_MP_09_2026','OTHER','MULTI%_!','WRONG PO')
) as $s) incoming_insert('stock_material',array('id_incoming'=>$s[0],'no_po'=>$s[1],'lotno'=>$s[2],'customer'=>$s[3],'id_category3'=>'M1','deleted'=>1,'aktif'=>'N'));
