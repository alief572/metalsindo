<?php
require __DIR__.'/bootstrap.php';
$db=new TimeDatabase();
$db->shadow(array('tr_spk_produksi','dt_tr_spk_produksi','dt_spk_produksi_lph','dt_spk_produksi','tr_spk_aktual','tr_spk_aktual_fg'));
$db->query("INSERT INTO tr_spk_produksi (id_spkproduksi,no_surat,id_tr_spk_produksi,id_stock,nama_material,weight,thickness,density,panjang,width,type) VALUES
    ('P1','PROD-A','P1-1','LOT','Old Copper',1,2,3,4,5,'reguler'),
    ('P2','PROD-B','P2-1','LOT','Copper',0,0,0,0,0,'slitting'),
    ('P3','PROD-C','P3-1','OTHER','=SUM(1,2)',NULL,NULL,NULL,NULL,NULL,'reguler'),
    ('P4','NO-PROGRESS','P4-1','OTHER','Hidden',0,0,0,0,0,'reguler')");
$db->query("INSERT INTO dt_tr_spk_produksi (id_spkproduksi,id_tr_spk_produksi,nama_material,weight,thickness,density,panjang,width) VALUES
    ('P1','P1-1','Stage Copper',571.56,0.5,8.94,387.64,400),('P1','P1-2','Wrong Stage',9,9,9,9,9)");
$db->query("INSERT INTO dt_spk_produksi (id_spkproduksi,no_surat,namacustomer,checked) VALUES
    ('P1','MKT-A','Customer A','1'),('P1','MKT-B','Customer B','1'),('P1','MKT-A','Customer A','1'),
    ('P1','HIDDEN','Hidden','0'),('P2','MKT-C','Customer C','1'),('P3','=SUM(1,2)','<script>','1')");
$db->query("INSERT INTO tr_spk_aktual (id_spk_aktual,id_spkproduksi,date_production,tgl_produksi) VALUES
    ('A1','P1','2026-09-23','2026-09-24'),('A2','P1','0000-00-00','2026-09-25'),('A3','P2','2026-02-31','2026-09-22')");
$db->query("INSERT INTO tr_spk_aktual_fg (id_spk_aktual,id_spkproduksi,date_production,tgl_produksi) VALUES
    ('F1','P1','2026-09-26','2026-10-01'),('F2','P3','0000-00-00','0000-00-00')");
$db->query("INSERT INTO dt_spk_produksi_lph (id_spk_aktual,id_proses,namaproses,start,finish,total,keterangan) VALUES
    ('P1','SLITTING1','SLITTING PROCESS','08:00','10:00','120','OK'),('P1','MATL1','MATL CHANGE','07:00','07:00','0',''),
    ('P1','EXTRA','Extra','10:00','07:00','0.5','=Extra'),('P2','SLITTING1','SLITTING PROCESS','08:00','10:00','120',''),
    ('P3','MATL1','MATL CHANGE','07:00','07:00','','')");
$model=new Report_production_time_model($db); $all=$model->normalize_filters(array());
$groups=$model->get_groups($all);
check($model->count_groups($all)===3 && array_column($groups,'id')===array('P1','P2','P3'),'Group count/date order, exclude no progress');
check($groups[0]['info']===array('PROD-A',"MKT-A\nMKT-B",'2026-09-26',"Customer A\nCustomer B",'Stage Copper',571.56,0.5,8.94,387.64,400.0),'Complete info mapping and matching input LHP stage');
check(count($groups[0]['progress'])===12 && $groups[0]['summary'][1]===120.5,'Children do not multiply total; extras preserved');
check($groups[1]['summary'][1]===120.0 && $groups[1]['info'][2]==='2026-09-22','Same lot stays separate, impossible actual date fallback');
check($groups[2]['info'][2]===null && $groups[2]['summary']===array(null,null),'No date or durations remains blank');
$paged=array_merge($model->get_groups($all,0,1),$model->get_groups($all,1,1),$model->get_groups($all,2,1));
check($paged===$groups && $model->get_groups($all,99,25)===array(),'Pagination keeps full groups and export order');
foreach (array('PROD-A'=>1,'MKT-B'=>1,'Customer B'=>1,'Copper'=>2,'Stage Copper'=>1,'Old Copper'=>0,'HIDDEN'=>0,"' OR 1=1 --"=>0,'%_!'=>0) as $term=>$count)
    check($model->count_groups($model->normalize_filters(array('search'=>$term)))===$count,'Bound search '.$term);
$filter=$model->normalize_filters(array('start_date'=>'2026-09-22','end_date'=>'2026-09-26'));
check($model->count_groups($filter)===2,'Inclusive dates exclude missing actual date');
check($model->count_groups($model->normalize_filters(array('start_date'=>'2026-09-26','end_date'=>'2026-09-26')))===1,'Date filter uses same latest actual date');
$db->query("UPDATE dt_spk_produksi_lph SET total='abc' WHERE id_spk_aktual='P2'");
check($model->get_groups($all)[1]['invalid_total'] && $model->get_groups($all)[1]['summary']===array(null,null),'Invalid text not silently zero');
echo "PASS regression: SQL joins/mapping, actual dates, marketing pairs, same lot, paging, filters, invalid totals\n";
