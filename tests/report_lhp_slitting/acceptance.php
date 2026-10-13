<?php
require __DIR__.'/controller.php';
// Distinct values across all numeric columns detect accidentally swapped mappings.
$db->query("INSERT INTO tr_spk_aktual_fg (id_spk_aktual,id_dt_spkaktualdetail,no_surat_produksi,date_production,tgl_produksi,nama_material,bfinishgood,lsisa_aktual,bsisa_aktual,bscrap_aktual,lebar_ng_in,berat_ng_in,lebar_ng_ek,berat_ng_ek,tweightslitting,ng_packinglist)
    VALUES ('L4',40,'PROD-D','2026-02-31','2026-05-09','Fallback D',90,91,92,93,94,95,96,97,98,-99),
           ('L5',50,'PROD-E','0000-00-00','0000-00-00','E',0,0,0,0,0,0,0,0,0,0)");
$db->query("INSERT INTO dt_spk_aktual_detail_fg (id,id_spk_aktual,id_dt_spkaktualdetail,no_surat,namacustomer,nmmaterial,weight,width,lot_slitting,totalaktual)
    VALUES (6,'L4',40,'MKT-D','Customer D','   ',21,22,'LOT-D',23),(7,'L5',50,'NONSPK','','E',0,0,'LOT-E',0)");
$mapped=$model->format_row($model->get_rows($model->normalize_filters(array('search'=>'PROD-D')),0,null)[0]);
check($mapped===array('PROD-D','MKT-D','Customer D','2026-05-09','Fallback D',21.0,22.0,'LOT-D',23.0,90.0,91.0,92.0,93.0,94.0,95.0,96.0,97.0,98.0,-99.0),'Complete 19-column independent expected values');
$invalidDates=$model->get_rows($model->normalize_filters(array('search'=>'PROD-E')),0,null);
check($model->format_row($invalidDates[0])[3]===null,'Both dates invalid becomes NULL');
check($model->count_rows($model->normalize_filters(array('start_date'=>'2026-05-09','end_date'=>'2026-05-09')))===1,'Impossible date fallback also used by filter');
check($model->get_rows($all,0,null,5,'asc')[5]['detail_id']==='6' && $model->get_rows($all,0,null,5,'desc')[0]['detail_id']==='6','Numeric sorting direction uses raw values');
$controller->auth->allowed=true;
$controller->input->values=array('order'=>array(array('column'=>'999 UNION SELECT','dir'=>'drop')),'start'=>array('invalid'),'length'=>array('invalid'),'draw'=>array('invalid'));
$controller->get_data(); $response=json_decode($controller->output->json,true);
check($response['draw']===0 && count($response['data'])===6,'Malformed table controls cannot change SQL');
function base_url($path) { return '/metalsindo_2/'.$path; }
$headers=Report_lhp_slitting_model::headers();
ob_start(); require dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/views/index.php'; $html=ob_get_clean();
check(substr_count($html,'scope="col"')===19 && strpos($html,'colspan="9"')!==false,'Rendered PHP view column structure');
check(strpos($html,'$.extend({},activeFilters)')!==false && strpos($html,"orderMulti:false")!==false,'Export uses applied filters and one sorting key');
check(strpos($html,'Tabel Report Weight Production')!==false && strpos($html,'Hasil produksi')!==false,'Report uses the new display name');
check(Report_lhp_slitting_model::headers()[5]==='Width' && Report_lhp_slitting_model::headers()[8]==='Actual Weight'
    && Report_lhp_slitting_model::headers()[17]==='Total Weight','Production column labels omit Slitting');
echo "PASS acceptance: complete mapping, impossible dates/fallback, malformed controls, PHP view structure\n";
