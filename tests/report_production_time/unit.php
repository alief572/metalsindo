<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__);
class BF_Model {}
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/models/Report_production_time_model.php';
check(count(Report_production_time_model::headers()) === 18, '18 columns');
$model = new Report_production_time_model();
$progress = array(
    array('id'=>1,'id_proses'=>'SLITTING1','namaproses'=>'SLITTING PROCESS','start'=>'08:00:00','finish'=>'10:00:00','total'=>'120','keterangan'=>'OK'),
    array('id'=>2,'id_proses'=>'MATL1','namaproses'=>'MATL CHANGE','start'=>'07:00:00','finish'=>'07:00:00','total'=>'0','keterangan'=>''),
    array('id'=>3,'id_proses'=>'EXTRA','namaproses'=>'Extra','start'=>null,'finish'=>null,'total'=>'0.5','keterangan'=>'')
);
$group = $model->format_group(array('id_spkproduksi'=>'P1','production_no'=>'PROD-A','production_date'=>'2026-09-23','nama_material'=>'Copper','weight'=>'571.56','thickness'=>'0.5','density'=>'8.94','panjang'=>'387.64','width'=>'400'), array(), $progress);
check(count($group['progress']) === 12 && $group['progress'][0]['total'] === 0.0 && $group['progress'][6]['total'] === 120.0, 'Stage IDs distinguish repeated labels and keep extras');
check($group['summary'] === array(120.5/60,120.5), 'Stored totals, no recalculation');
check($group['progress'][1]['total'] === null && $group['progress'][6]['start'] === '08:00', 'Missing stage and time format');
$blank = $model->format_group(array(), array(), array());
check($blank['summary'] === array(null,null), 'All blanks are not zero');
$progress[0]['total'] = '-2';
$invalid = $model->format_group(array(), array(), $progress);
check($invalid['summary'] === array(null,null) && $invalid['invalid_total'] && $invalid['progress'][6]['invalid_total'], 'Invalid duration suppresses summary');
foreach (array(array('start_date'=>'2026-02-30'),array('start_date'=>'2026-10-01','end_date'=>'2026-09-01'),array('search'=>array('x'))) as $input) {
    $failed=false; try { $model->normalize_filters($input); } catch (InvalidArgumentException $e) { $failed=true; }
    check($failed,'Invalid filter rejected');
}
echo "PASS unit: headers, stage order/extras, stored totals, blanks/zero/invalid, filter validation\n";
